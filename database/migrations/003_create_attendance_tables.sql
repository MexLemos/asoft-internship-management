-- Migration: 003_create_attendance_tables.sql

CREATE TABLE IF NOT EXISTS attendance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    check_in_time TIME NULL,
    check_in_lat DECIMAL(10, 8) NULL,
    check_in_lng DECIMAL(11, 8) NULL,
    check_in_accuracy DECIMAL(8, 2) NULL,
    check_in_distance_meters DECIMAL(8, 2) NULL,
    check_in_ip VARCHAR(45) NULL,
    check_in_device VARCHAR(255) NULL,
    check_in_status ENUM('on_time', 'late', 'early') DEFAULT 'on_time',
    
    check_out_time TIME NULL,
    check_out_lat DECIMAL(10, 8) NULL,
    check_out_lng DECIMAL(11, 8) NULL,
    check_out_accuracy DECIMAL(8, 2) NULL,
    check_out_distance_meters DECIMAL(8, 2) NULL,
    check_out_ip VARCHAR(45) NULL,
    check_out_device VARCHAR(255) NULL,
    check_out_status ENUM('normal', 'early_departure') DEFAULT 'normal',
    
    hours_worked DECIMAL(5, 2) DEFAULT 0.00,
    status ENUM('present', 'absent', 'justified_absence', 'holiday') DEFAULT 'present',
    justification_reason TEXT NULL,
    justification_attachment VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_intern_date (intern_id, date),
    CONSTRAINT fk_attendance_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    INDEX idx_attendance_date (date),
    INDEX idx_attendance_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attendance_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    type ENUM('check_in', 'check_out') NOT NULL,
    attempt_time DATETIME NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    accuracy DECIMAL(8, 2) NULL,
    distance_meters DECIMAL(8, 2) NOT NULL,
    is_within_radius BOOLEAN NOT NULL DEFAULT FALSE,
    status ENUM('success', 'blocked_out_of_range', 'blocked_time_invalid', 'blocked_suspicious') NOT NULL,
    failure_reason VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    device_fingerprint VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_att_attempts_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    INDEX idx_attempts_time (attempt_time),
    INDEX idx_attempts_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
