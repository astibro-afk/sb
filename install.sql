CREATE TABLE IF NOT EXISTS sb_app_data (
    data_key VARCHAR(100) NOT NULL PRIMARY KEY,
    payload LONGTEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sb_archive_records (
    record_key CHAR(66) NOT NULL PRIMARY KEY,
    report_date VARCHAR(10) NOT NULL DEFAULT '',
    object_name VARCHAR(255) NOT NULL DEFAULT '',
    payload LONGTEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_report_date (report_date),
    INDEX idx_object_name (object_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
