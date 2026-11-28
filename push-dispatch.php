<?php
declare(strict_types=1);

/**
 * Sends a small batch from the transactional outbox.
 * Called from API shutdown only after the business transaction has committed.
 */
function sb_push_base64url_encode(string $value): string {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function sb_push_dispatch_pending(PDO $pdo, int $limit = 4): void {
    if ($pdo->inTransaction()) return;
    if (function_exists('system_setting') && system_setting($pdo, 'push_enabled', '1') !== '1') return;

    try {
        $due = $pdo->query(
            "SELECT 1 FROM sb_push_queue
             WHERE (status='pending' AND next_attempt_at<=NOW())
                OR (status='sending' AND next_attempt_at<=NOW())
             LIMIT 1"
        )->fetchColumn();
        if (!$due) return;
    } catch (Throwable $e) {
        error_log('SB push queue check error: ' . $e->getMessage());
        return;
    }

    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        error_log('SB push dispatcher: Composer autoload is missing.');
        return;
    }
    require_once $autoload;

    $limit = max(1, min(8, $limit));
    $claimed = [];

    try {
        $pdo->beginTransaction();
        $rows = $pdo->query(
            "SELECT id,notification_id,user_id,attempts
             FROM sb_push_queue
             WHERE (status='pending' AND next_attempt_at<=NOW())
                OR (status='sending' AND next_attempt_at<=NOW())
             ORDER BY id ASC
             LIMIT " . $limit . "
             FOR UPDATE"
        )->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            $pdo->commit();
            return;
        }

        $claim = $pdo->prepare(
            "UPDATE sb_push_queue
             SET status='sending', attempts=attempts+1,
                 next_attempt_at=DATE_ADD(NOW(), INTERVAL 5 MINUTE)
             WHERE id=:id"
        );
        foreach ($rows as $row) {
            $claim->execute([':id' => (int)$row['id']]);
            $row['attempts'] = (int)$row['attempts'] + 1;
            $claimed[(int)$row['id']] = $row;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('SB push queue claim error: ' . $e->getMessage());
        return;
    }

    try {
        $keyRow = $pdo->query(
            'SELECT public_key,private_key_pem FROM sb_push_vapid_keys WHERE id=1'
        )->fetch(PDO::FETCH_ASSOC);
        if (!$keyRow || empty($keyRow['public_key']) || empty($keyRow['private_key_pem'])) {
            throw new RuntimeException('VAPID keys are not configured.');
        }

        $private = openssl_pkey_get_private((string)$keyRow['private_key_pem']);
        if ($private === false) throw new RuntimeException('Stored VAPID private key is invalid.');
        $details = openssl_pkey_get_details($private);
        $secret = $details['ec']['d'] ?? '';
        if (!is_string($secret) || $secret === '') throw new RuntimeException('VAPID private scalar is unavailable.');

        $auth = [
            'VAPID' => [
                'subject' => 'https://sbmagnit.rf.gd',
                'publicKey' => (string)$keyRow['public_key'],
                'privateKey' => sb_push_base64url_encode($secret),
            ],
        ];

        $client = new \GuzzleHttp\Client([
            'timeout' => 5,
            'connect_timeout' => 3,
            'allow_redirects' => false,
        ]);
        $webPush = new \Minishlink\WebPush\WebPush(
            $auth,
            ['TTL' => 86400, 'urgency' => 'normal', 'batchSize' => 20, 'contentType' => 'application/json'],
            $client
        );
        $webPush->setReuseVAPIDHeaders(true);

        $endpointMap = [];
        $outcomes = [];
        foreach ($claimed as $queueId => $queueRow) {
            $st = $pdo->prepare(
                'SELECT id,endpoint_hash,endpoint,p256dh,auth
                 FROM sb_push_subscriptions
                 WHERE user_id=:uid AND is_active=1'
            );
            $st->execute([':uid' => (int)$queueRow['user_id']]);
            $subscriptions = $st->fetchAll(PDO::FETCH_ASSOC);
            $outcomes[$queueId] = [
                'has_subscriptions' => (bool)$subscriptions,
                'success' => false,
                'active_failure' => false,
                'expired' => [],
                'errors' => [],
                'attempts' => (int)$queueRow['attempts'],
            ];

            if (!$subscriptions) continue;

            $n = $pdo->prepare(
                'SELECT title,message,link FROM sb_notifications WHERE id=:id AND user_id=:uid'
            );
            $n->execute([':id' => (int)$queueRow['notification_id'], ':uid' => (int)$queueRow['user_id']]);
            $notice = $n->fetch(PDO::FETCH_ASSOC);
            if (!$notice) continue;

            $target = (string)($notice['link'] ?? '');
            if (!preg_match('~^/?(?:[a-z0-9_/-]+\\.php(?:\\?[a-z0-9_=&%-]*)?|index\\.html)$~i', $target) || strpos($target, '..') !== false) {
                $target = '/notifications.php';
            } else {
                $target = '/' . ltrim($target, '/');
            }

            $payload = json_encode([
                'title' => mb_substr((string)$notice['title'], 0, 120),
                'body' => mb_substr((string)$notice['message'], 0, 220),
                'url' => $target,
                'tag' => 'sb-' . (int)$queueRow['notification_id'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (!is_string($payload)) throw new RuntimeException('Unable to encode push payload.');

            foreach ($subscriptions as $sub) {
                $endpoint = (string)$sub['endpoint'];
                $endpointHash = (string)$sub['endpoint_hash'];
                if (!isset($endpointMap[$endpoint])) $endpointMap[$endpoint] = [];
                $endpointMap[$endpoint][] = ['queue_id' => $queueId, 'subscription_id' => (int)$sub['id'], 'endpoint_hash' => $endpointHash];
                $subscription = \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $endpoint,
                    'keys' => ['p256dh' => (string)$sub['p256dh'], 'auth' => (string)$sub['auth']],
                ]);
                $webPush->queueNotification($subscription, $payload);
            }
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getEndpoint();
            if (empty($endpointMap[$endpoint])) continue;
            $mapped = array_shift($endpointMap[$endpoint]);
            $queueId = (int)$mapped['queue_id'];
            if ($report->isSuccess()) {
                $outcomes[$queueId]['success'] = true;
                $pdo->prepare(
                    'UPDATE sb_push_subscriptions SET last_used_at=NOW() WHERE id=:id'
                )->execute([':id' => (int)$mapped['subscription_id']]);
                continue;
            }

            $response = $report->getResponse();
            $httpStatus = $response ? (int)$response->getStatusCode() : 0;
            if ($report->isSubscriptionExpired() || in_array($httpStatus, [404, 410], true)) {
                $outcomes[$queueId]['expired'][] = (string)$mapped['endpoint_hash'];
                $pdo->prepare(
                    'UPDATE sb_push_subscriptions SET is_active=0,updated_at=CURRENT_TIMESTAMP WHERE id=:id'
                )->execute([':id' => (int)$mapped['subscription_id']]);
            } else {
                $outcomes[$queueId]['active_failure'] = true;
                $outcomes[$queueId]['errors'][] = mb_substr((string)$report->getReason(), 0, 180);
            }
        }

        $finish = $pdo->prepare(
            "UPDATE sb_push_queue
             SET status=:status, sent_at=:sent_at, next_attempt_at=:next_at, last_error=:error
             WHERE id=:id"
        );
        foreach ($outcomes as $queueId => $outcome) {
            $status = 'pending';
            $sentAt = null;
            $nextAt = date('Y-m-d H:i:s');
            $error = '';
            if ($outcome['success'] && !$outcome['active_failure']) {
                $status = 'sent';
                $sentAt = date('Y-m-d H:i:s');
            } elseif (!$outcome['has_subscriptions'] || (!$outcome['active_failure'] && !empty($outcome['expired']))) {
                $status = 'skipped';
                $error = !$outcome['has_subscriptions'] ? 'No active subscriptions.' : 'All subscriptions expired.';
            } elseif ((int)$outcome['attempts'] >= 5) {
                $status = 'failed';
                $error = implode('; ', $outcome['errors']) ?: 'Delivery failed after 5 attempts.';
            } else {
                $delays = [1 => 60, 2 => 300, 3 => 900, 4 => 3600];
                $delay = $delays[(int)$outcome['attempts']] ?? 3600;
                $nextAt = date('Y-m-d H:i:s', time() + $delay);
                $error = implode('; ', $outcome['errors']) ?: 'Temporary delivery failure.';
            }
            $finish->execute([
                ':status' => $status,
                ':sent_at' => $sentAt,
                ':next_at' => $nextAt,
                ':error' => mb_substr($error, 0, 255),
                ':id' => (int)$queueId,
            ]);
        }
    } catch (Throwable $e) {
        error_log('SB push delivery error: ' . $e->getMessage());
        $retry = $pdo->prepare(
            "UPDATE sb_push_queue
             SET status=CASE WHEN attempts>=5 THEN 'failed' ELSE 'pending' END,
                 next_attempt_at=DATE_ADD(NOW(), INTERVAL 5 MINUTE),
                 last_error=:error
             WHERE id=:id AND status='sending'"
        );
        foreach ($claimed as $queueId => $queueRow) {
            try { $retry->execute([':error' => mb_substr($e->getMessage(), 0, 255), ':id' => (int)$queueId]); }
            catch (Throwable $ignored) { error_log('SB push retry update error: ' . $ignored->getMessage()); }
        }
    }
}
