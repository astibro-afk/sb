-- install_auth.sql — безопасная миграция для системы ролей.
-- Не удаляет и не изменяет существующие таблицы и данные.

CREATE TABLE IF NOT EXISTS sb_users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL DEFAULT '',
    role VARCHAR(16) NOT NULL DEFAULT 'user',
    object_id INT UNSIGNED NULL DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sb_objects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE sb_archive_records ADD COLUMN IF NOT EXISTS owner_user_id INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE sb_archive_records ADD COLUMN IF NOT EXISTS owner_name VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE sb_archive_records ADD COLUMN IF NOT EXISTS object_id INT UNSIGNED NULL DEFAULT NULL;

-- Объекты, уже встречающиеся в архиве, попадают в справочник автоматически.
INSERT IGNORE INTO sb_objects (name)
SELECT DISTINCT object_name FROM sb_archive_records WHERE object_name <> '';
