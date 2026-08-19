-- Migration: 002_create_institutions_and_interns_tables.sql

CREATE TABLE IF NOT EXISTS institutions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    type ENUM('universidade', 'instituto_medio', 'colegio', 'centro_formacao', 'outro') DEFAULT 'instituto_medio',
    nif VARCHAR(50) NULL UNIQUE,
    email VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    website VARCHAR(200) NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(100) DEFAULT 'Luanda',
    province VARCHAR(100) DEFAULT 'Luanda',
    contact_person VARCHAR(150) NULL,
    contact_role VARCHAR(100) NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_institutions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institution_users (
    institution_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (institution_id, user_id),
    CONSTRAINT fk_inst_users_inst FOREIGN KEY (institution_id) REFERENCES institutions (id) ON DELETE CASCADE,
    CONSTRAINT fk_inst_users_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS interns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    institution_id BIGINT UNSIGNED NOT NULL,
    supervisor_id BIGINT UNSIGNED NULL,
    internship_code VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    social_name VARCHAR(150) NULL,
    birth_date DATE NULL,
    gender ENUM('M', 'F', 'O') DEFAULT 'M',
    bi_number VARCHAR(50) NOT NULL UNIQUE,
    bi_issue_date DATE NULL,
    bi_expiry_date DATE NULL,
    photo VARCHAR(255) NULL,
    phone VARCHAR(30) NULL,
    emergency_phone VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(100) DEFAULT 'Luanda',
    province VARCHAR(100) DEFAULT 'Luanda',
    course VARCHAR(150) NOT NULL,
    education_area VARCHAR(150) NULL,
    academic_year VARCHAR(50) NULL,
    student_number VARCHAR(50) NULL,
    academic_advisor VARCHAR(150) NULL,
    internship_area VARCHAR(150) NOT NULL DEFAULT 'Desenvolvimento de Software',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'active', 'suspended', 'completed', 'cancelled') DEFAULT 'active',
    status_reason TEXT NULL,
    overall_score DECIMAL(5,2) DEFAULT 0.00,
    risk_level ENUM('normal', 'attention', 'risk') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_interns_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_interns_inst FOREIGN KEY (institution_id) REFERENCES institutions (id) ON DELETE RESTRICT,
    CONSTRAINT fk_interns_sup FOREIGN KEY (supervisor_id) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_interns_status (status),
    INDEX idx_interns_risk (risk_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intern_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL UNIQUE,
    expected_start_time TIME NOT NULL DEFAULT '08:00:00',
    expected_end_time TIME NOT NULL DEFAULT '12:00:00',
    tolerance_minutes INT UNSIGNED NOT NULL DEFAULT 15,
    daily_hours DECIMAL(4,2) NOT NULL DEFAULT 4.00,
    total_required_hours DECIMAL(6,2) NOT NULL DEFAULT 300.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_schedules_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intern_schedule_days (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_schedule_id BIGINT UNSIGNED NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL COMMENT '1=Segunda, 2=Terca, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sabado, 7=Domingo',
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE KEY uk_schedule_day (intern_schedule_id, day_of_week),
    CONSTRAINT fk_schedule_days_sched FOREIGN KEY (intern_schedule_id) REFERENCES intern_schedules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
