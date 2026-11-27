<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Referrer-Policy: no-referrer');
ini_set('session.use_strict_mode','1');
$isHttps=!empty($_SERVER['HTTPS'])&&strtolower((string)$_SERVER['HTTPS'])!=='off';
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Strict']);
session_name('sbapp_session'); session_start();
if (empty($_SESSION['sb_user_id'])) { $pair=(string)($_GET['pair']??''); $suffix=preg_match('/^[a-f0-9]{64}$/',$pair)?'?pair='.$pair:''; header('Location: /index.html'.$suffix); exit; }
?>
<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#f3f7f8"><title>Push-уведомления — СБ</title><link rel="stylesheet" href="site-theme.css"><link rel="stylesheet" href="site-header.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f7f8;color:#18343a;font:14px/1.5 Inter,Arial,sans-serif}.page{max-width:1000px;margin:auto;padding:24px 16px 48px}.panel{background:#fff;border:1px solid #e1eaec;border-radius:14px;padding:20px;margin:14px 0;box-shadow:0 5px 20px rgba(24,52,58,.04)}h1{font-size:24px;margin:0}h2{font-size:17px;margin:0 0 8px}.muted{color:#71858a}.actions{display:flex;gap:9px;flex-wrap:wrap;margin-top:14px}.btn{border:1px solid #dce7e9;background:#fff;color:#18343a;border-radius:9px;padding:10px 14px;text-decoration:none;font-weight:700;cursor:pointer}.btn.primary{background:#08777b;color:#fff;border-color:#08777b}.btn.danger{color:#a52d2d;border-color:#f0d1d1}.btn:disabled{opacity:.55;cursor:wait}.status{white-space:pre-wrap;background:#f6f9fa;border-radius:9px;padding:12px;color:#425b60;overflow-wrap:anywhere}.device{padding:12px 0;border-top:1px solid #edf1f2;display:flex;justify-content:space-between;align-items:center;gap:12px}.device:first-child{border-top:0}.device strong{display:block}.device small{display:block;color:#71858a}.table{width:100%;border-collapse:collapse}.table th,.table td{text-align:left;padding:10px;border-bottom:1px solid #edf1f2;vertical-align:top}.table th{font-size:12px;color:#71858a}@media(max-width:620px){.panel{padding:15px}.device{align-items:flex-start;flex-direction:column}.table{font-size:12px}.table th,.table td{padding:7px}}
</style></head><body>
<header class="sb-site-header"><div class="sb-site-top"><a class="sb-site-brand" href="/index.html"><span class="sb-site-logo">СБ</span><span class="sb-site-brand-text"><strong>Служба безопасности</strong><small>Группа БФ Волгоград 1</small></span></a><div class="sb-site-actions"><div class="sb-site-user" id="sbHeaderUser" hidden><strong id="sbHeaderName"></strong><small id="sbHeaderMeta"></small></div><a class="sb-site-manage" id="sbHeaderAddUser" href="/add-user.php" hidden>Добавить пользователя</a><a class="sb-site-manage" id="sbHeaderManage" href="/users.php" hidden>Управление</a><a class="sb-site-notice" href="/notifications.php">Уведомления <span id="sbHeaderBadge" hidden>0</span></a><button class="sb-site-logout" id="sbHeaderLogout" type="button">Выход</button></div></div><nav class="sb-site-nav" aria-label="Основная навигация"><div class="sb-site-nav-inner"><a href="/index.html">Главная</a><a href="/reports.php">Отчёты</a><a href="/tasks.php">Задачи</a><a href="/employees.php">Сотрудники</a><a href="/notifications.php">Уведомления</a><a class="active" href="/push.php">Push</a></div></nav></header>
<main class="page"><h1>Мобильные push-уведомления</h1><p class="muted">Подключите это устройство, чтобы получать уведомления SB, даже когда сайт не открыт.</p>
<section class="panel"><h2>Подключение устройства</h2><p class="muted">Браузер самостоятельно запросит разрешение. SB не может включить уведомления без вашего согласия.</p><div class="actions"><button class="btn primary" id="enable" type="button">Включить push-уведомления</button><button class="btn" id="disable" type="button" disabled>Отключить это устройство</button></div><p id="message" class="status" aria-live="polite">Проверяем поддержку браузера…</p><div id="ownDevices"></div></section>
<section class="panel" id="pairPanel" hidden><h2>Подключение по приглашению</h2><p id="pairInfo" class="muted">Проверяем одноразовую ссылку…</p><div class="actions"><button class="btn primary" id="pairAccept" type="button" disabled>Подтвердить и включить push</button></div></section>
<section class="panel" id="adminPanel" hidden><h2>Подключение сотрудника по QR-ссылке</h2><p class="muted">Ссылка действует 15 минут и предназначена только для выбранной учётной записи. Сотрудник должен войти в свой аккаунт и лично разрешить уведомления.</p><div class="actions"><select id="pairEmployee" class="btn" aria-label="Сотрудник для подключения"><option value="">Выберите сотрудника</option></select><button class="btn primary" id="pairCreate" type="button">Создать QR-ссылку</button></div><div id="pairResult" hidden style="margin-top:14px"><div id="pairQr" style="display:inline-block;background:#fff;padding:12px;border:1px solid #e1eaec;border-radius:10px"></div><p><input id="pairUrl" readonly style="width:100%;padding:10px;border:1px solid #dce7e9;border-radius:8px;margin:10px 0"></p><div class="actions"><button class="btn" id="pairCopy" type="button">Скопировать ссылку</button><span id="pairExpiry" class="muted"></span></div></div><h2 style="margin-top:22px">Подключённые устройства сотрудников</h2><p class="muted">Администратор может отозвать подписку устройства.</p><div id="adminDevices" class="muted">Загрузка…</div></section>
</main>
<script src="third_party/qrcodejs/qrcode.min.js"></script>
<script>
(function(){
'use strict';
const $=id=>document.getElementById(id);let csrf='',publicKey='',registration=null;const pairToken=new URLSearchParams(location.search).get('pair')||'';
function say(s){$('message').textContent=s;}
function b64urlToBytes(value){const pad='='.repeat((4-value.length%4)%4);const raw=atob((value+pad).replace(/-/g,'+').replace(/_/g,'/'));return Uint8Array.from(raw,c=>c.charCodeAt(0));}
function bytesToHex(buffer){return Array.from(new Uint8Array(buffer)).map(b=>b.toString(16).padStart(2,'0')).join('');}
async function request(action,options){const r=await fetch('api.php?action='+action,Object.assign({credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}},options||{}));const raw=await r.text();let d;try{d=JSON.parse(raw);}catch(e){throw new Error('Некорректный ответ сервера (HTTP '+r.status+').');}if(!r.ok||!d.ok)throw new Error(d.error||'Ошибка запроса.');if(d.csrf)csrf=d.csrf;return d;}
async function getOwn(){const d=await request('push_status');const box=$('ownDevices');box.replaceChildren();if(!d.devices.length){box.textContent='Активных push-устройств для этой учётной записи пока нет.';}else{d.devices.forEach(x=>{const row=document.createElement('div');row.className='device';const info=document.createElement('div');const title=document.createElement('strong');title.textContent=x.user_agent||'Браузер';const date=document.createElement('small');date.textContent='Подключено: '+x.created_at;info.append(title,date);row.append(info);box.append(row);});}}
async function refreshButton(){if(!('serviceWorker'in navigator)||!('PushManager'in window)||!('Notification'in window)){say('Этот браузер не поддерживает необходимые функции Web Push.');$('enable').disabled=true;return;}const reg=await navigator.serviceWorker.getRegistration('/');registration=reg||null;const sub=registration?await registration.pushManager.getSubscription():null;$('disable').disabled=!sub;$('enable').disabled=!!sub;say('Поддержка браузера есть. Разрешение уведомлений: '+Notification.permission+'. '+(sub?'Это устройство подключено.':'Это устройство пока не подключено.'));}
async function enable(){ $('enable').disabled=true;try{if(!window.isSecureContext)throw new Error('Для push требуется HTTPS.');if(!('serviceWorker'in navigator)||!('PushManager'in window)||!('Notification'in window))throw new Error('Браузер не поддерживает Web Push.');const perm=await Notification.requestPermission();if(perm!=='granted')throw new Error('Разрешение не предоставлено. Измените настройки уведомлений браузера и попробуйте снова.');const cfg=await request('push_config');publicKey=cfg.public_key;registration=await navigator.serviceWorker.register('/service-worker.js',{scope:'/'});await navigator.serviceWorker.ready;let sub=await registration.pushManager.getSubscription();if(!sub)sub=await registration.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:b64urlToBytes(publicKey)});await request('push_subscribe',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf,subscription:sub.toJSON()})});say('Устройство подключено. Сервер будет отправлять push после сохранения новых уведомлений. Доставка зависит от доступности push-службы и настроек браузера.');await getOwn();await refreshButton();}catch(e){say('Не удалось подключить устройство: '+e.message);$('enable').disabled=false;}}
async function disable(){ $('disable').disabled=true;try{const reg=await navigator.serviceWorker.getRegistration('/');const sub=reg?await reg.pushManager.getSubscription():null;if(sub){const digest=await crypto.subtle.digest('SHA-256',new TextEncoder().encode(sub.endpoint));const endpoint_hash=bytesToHex(digest);await request('push_unsubscribe',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf,endpoint_hash})});await sub.unsubscribe();}say('Push-уведомления отключены для этого устройства.');await getOwn();await refreshButton();}catch(e){say('Не удалось отключить устройство: '+e.message);$('disable').disabled=false;}}
function esc(s){return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
async function loadAdmin(){const panel=$('adminPanel');try{const d=await request('push_devices_data');panel.hidden=false;const employees=await request('push_employees_data');const sel=$('pairEmployee');sel.replaceChildren(new Option('Выберите сотрудника',''));employees.employees.forEach(u=>sel.add(new Option((u.full_name||u.username)+' ('+u.username+')',u.id)));const box=$('adminDevices');if(!d.devices.length){box.textContent='Подключённых устройств пока нет.';return;}const table=document.createElement('table');table.className='table';table.innerHTML='<thead><tr><th>Сотрудник</th><th>Устройство / браузер</th><th>Подключено</th><th></th></tr></thead>';const tbody=document.createElement('tbody');d.devices.forEach(x=>{const tr=document.createElement('tr');const td1=document.createElement('td');td1.textContent=x.full_name||x.username;const td2=document.createElement('td');td2.textContent=x.user_agent||'Браузер';const td3=document.createElement('td');td3.textContent=x.created_at;const td4=document.createElement('td');const b=document.createElement('button');b.className='btn danger';b.type='button';b.textContent='Отозвать';b.addEventListener('click',async()=>{if(!confirm('Отключить push-подписку этого устройства?'))return;try{await request('push_device_revoke',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf,id:x.id})});await loadAdmin();}catch(e){alert(e.message);}});td4.append(b);tr.append(td1,td2,td3,td4);tbody.append(tr);});table.append(tbody);box.replaceChildren(table);}catch(e){if(!/только администратор/i.test(e.message))boxText(e.message);else panel.hidden=true;}}
function boxText(s){$('adminDevices').textContent=s;}

async function createPairLink(){
 const userId=$('pairEmployee').value;
 if(!userId){alert('Выберите сотрудника.');return;}
 const button=$('pairCreate');button.disabled=true;
 try{
  const d=await request('push_pair_create',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf,user_id:Number(userId)})});
  $('pairResult').hidden=false;$('pairUrl').value=d.url;$('pairExpiry').textContent='Срок действия: '+d.expires_in_minutes+' минут';
  $('pairQr').replaceChildren();
  new QRCode($('pairQr'),{text:d.url,width:220,height:220,colorDark:'#18343a',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M});
 }catch(e){alert(e.message);}finally{button.disabled=false;}
}
async function copyPairLink(){
 const value=$('pairUrl').value;if(!value)return;
 try{await navigator.clipboard.writeText(value);$('pairCopy').textContent='Скопировано';}
 catch(e){$('pairUrl').focus();$('pairUrl').select();alert('Скопируйте выделенную ссылку вручную.');}
}
async function loadPairInvite(){
 if(!/^[a-f0-9]{64}$/.test(pairToken))return;
 const panel=$('pairPanel');panel.hidden=false;
 try{
  const d=await request('push_pair_info&token='+encodeURIComponent(pairToken));
  $('pairInfo').textContent='Приглашение предназначено для учётной записи: '+d.employee+'. Нажмите кнопку ниже, чтобы подключить это устройство.';
  $('pairAccept').disabled=false;
 }catch(e){$('pairInfo').textContent=e.message;}
}
async function acceptPairInvite(){
 const button=$('pairAccept');button.disabled=true;
 try{
  if(!window.isSecureContext)throw new Error('Для push требуется HTTPS.');
  if(!('serviceWorker'in navigator)||!('PushManager'in window)||!('Notification'in window))throw new Error('Этот браузер не поддерживает Web Push.');
  const permission=await Notification.requestPermission();
  if(permission!=='granted')throw new Error('Разрешение на уведомления не предоставлено.');
  const cfg=await request('push_config');publicKey=cfg.public_key;
  registration=await navigator.serviceWorker.register('/service-worker.js',{scope:'/'});
  await navigator.serviceWorker.ready;
  let sub=await registration.pushManager.getSubscription();
  if(!sub)sub=await registration.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:b64urlToBytes(publicKey)});
  await request('push_pair_claim',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({csrf,token:pairToken,subscription:sub.toJSON()})});
  $('pairInfo').textContent='Устройство успешно подключено к учётной записи.';
  $('pairPanel').hidden=true;history.replaceState(null,'','push.php');
  await getOwn();await refreshButton();say('Устройство подключено по одноразовой ссылке. Push-уведомления будут отправляться после сохранения новых уведомлений.');
 }catch(e){$('pairInfo').textContent=e.message;button.disabled=false;}
}

$('enable').addEventListener('click',enable);$('disable').addEventListener('click',disable);$('pairCreate').addEventListener('click',createPairLink);$('pairCopy').addEventListener('click',copyPairLink);$('pairAccept').addEventListener('click',acceptPairInvite);
(async()=>{try{const cfg=await request('push_config');publicKey=cfg.public_key;await getOwn();await refreshButton();await loadAdmin();await loadPairInvite();}catch(e){say(e.message);}})();
})();
</script><script src="site-header.js"></script></body></html>