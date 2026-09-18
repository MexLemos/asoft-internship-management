-- ========================================================
-- Asoftmedia Internship Management System (AIMS)
-- Database Schema + Seeds Iniciais para Producao (Hostinger)
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `migration` VARCHAR(255) NOT NULL UNIQUE,
    `batch` INT UNSIGNED NOT NULL,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Migration: 001_create_users_roles_permissions_tables.sql
-- --------------------------------------------------------
-- Migration: 001_create_users_roles_permissions_tables.sql

CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    is_system BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    group_name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    avatar VARCHAR(255) NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    login_attempts INT UNSIGNED DEFAULT 0,
    locked_until TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_users_email (email),
    INDEX idx_users_username (username),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('001_create_users_roles_permissions_tables.sql', 1);

-- --------------------------------------------------------
-- Migration: 002_create_institutions_and_interns_tables.sql
-- --------------------------------------------------------
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

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('002_create_institutions_and_interns_tables.sql', 1);

-- --------------------------------------------------------
-- Migration: 003_create_attendance_tables.sql
-- --------------------------------------------------------
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

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('003_create_attendance_tables.sql', 1);

-- --------------------------------------------------------
-- Migration: 004_create_tasks_and_submissions_tables.sql
-- --------------------------------------------------------
-- Migration: 004_create_tasks_and_submissions_tables.sql

CREATE TABLE IF NOT EXISTS task_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    color_badge VARCHAR(30) DEFAULT 'primary',
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    objective TEXT NULL,
    instructions TEXT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    points INT UNSIGNED DEFAULT 100,
    estimated_hours DECIMAL(5,2) DEFAULT 4.00,
    evaluation_criteria TEXT NULL,
    requires_github BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'published', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_tasks_cat FOREIGN KEY (category_id) REFERENCES task_categories (id) ON DELETE RESTRICT,
    CONSTRAINT fk_tasks_creator FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE,
    INDEX idx_tasks_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS task_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    intern_id BIGINT UNSIGNED NOT NULL,
    assigned_by BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('assigned', 'in_progress', 'submitted', 'in_review', 'approved', 'rejected', 'reopened', 'cancelled') DEFAULT 'assigned',
    score DECIMAL(5,2) NULL,
    supervisor_feedback TEXT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assign_task FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE,
    CONSTRAINT fk_assign_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_assign_by FOREIGN KEY (assigned_by) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_assign_reviewer FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_assign_status (status),
    INDEX idx_assign_due (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS task_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id BIGINT UNSIGNED NOT NULL,
    intern_id BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    github_repo_url VARCHAR(255) NULL,
    github_branch VARCHAR(100) NULL,
    github_commit_hash VARCHAR(100) NULL,
    github_pr_url VARCHAR(255) NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    version_number INT UNSIGNED DEFAULT 1,
    CONSTRAINT fk_sub_assign FOREIGN KEY (assignment_id) REFERENCES task_assignments (id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS task_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id BIGINT UNSIGNED NULL,
    submission_id BIGINT UNSIGNED NULL,
    task_id BIGINT UNSIGNED NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_att_assign FOREIGN KEY (assignment_id) REFERENCES task_assignments (id) ON DELETE CASCADE,
    CONSTRAINT fk_att_sub FOREIGN KEY (submission_id) REFERENCES task_submissions (id) ON DELETE CASCADE,
    CONSTRAINT fk_att_task FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE,
    CONSTRAINT fk_att_user FOREIGN KEY (uploaded_by) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS task_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comm_assign FOREIGN KEY (assignment_id) REFERENCES task_assignments (id) ON DELETE CASCADE,
    CONSTRAINT fk_comm_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('004_create_tasks_and_submissions_tables.sql', 1);

-- --------------------------------------------------------
-- Migration: 005_create_academy_and_tests_tables.sql
-- --------------------------------------------------------
-- Migration: 005_create_academy_and_tests_tables.sql

CREATE TABLE IF NOT EXISTS courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NULL,
    cover_image VARCHAR(255) NULL,
    is_mandatory BOOLEAN DEFAULT TRUE,
    status ENUM('published', 'draft') DEFAULT 'published',
    order_index INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS modules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description TEXT NULL,
    order_index INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_modules_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lessons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    order_index INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_lessons_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learning_contents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    content_type ENUM('youtube_video', 'local_video', 'pdf_document', 'article_html', 'external_link', 'code_exercise') NOT NULL,
    content_url_or_path TEXT NOT NULL,
    duration_minutes INT UNSIGNED DEFAULT 10,
    article_body LONGTEXT NULL,
    order_index INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_contents_lesson FOREIGN KEY (lesson_id) REFERENCES lessons (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lesson_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    content_id BIGINT UNSIGNED NOT NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    watch_percentage DECIMAL(5,2) DEFAULT 0.00,
    completed_at DATETIME NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_intern_content (intern_id, content_id),
    CONSTRAINT fk_progress_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_progress_content FOREIGN KEY (content_id) REFERENCES learning_contents (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    passing_score DECIMAL(5,2) NOT NULL DEFAULT 70.00,
    max_attempts INT UNSIGNED NOT NULL DEFAULT 3,
    time_limit_minutes INT UNSIGNED NOT NULL DEFAULT 30,
    shuffle_questions BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tests_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    test_id BIGINT UNSIGNED NOT NULL,
    question_type ENUM('multiple_choice', 'true_false', 'short_answer', 'open_text') DEFAULT 'multiple_choice',
    statement TEXT NOT NULL,
    explanation TEXT NULL,
    score_points DECIMAL(5,2) DEFAULT 10.00,
    order_index INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_questions_test FOREIGN KEY (test_id) REFERENCES tests (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS question_options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id BIGINT UNSIGNED NOT NULL,
    option_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    order_index INT UNSIGNED DEFAULT 1,
    CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS test_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    test_id BIGINT UNSIGNED NOT NULL,
    intern_id BIGINT UNSIGNED NOT NULL,
    attempt_number INT UNSIGNED DEFAULT 1,
    score_achieved DECIMAL(5,2) DEFAULT 0.00,
    percentage DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('in_progress', 'passed', 'failed', 'timed_out', 'under_review') DEFAULT 'in_progress',
    started_at DATETIME NOT NULL,
    submitted_at DATETIME NULL,
    feedback TEXT NULL,
    CONSTRAINT fk_attempts_test FOREIGN KEY (test_id) REFERENCES tests (id) ON DELETE CASCADE,
    CONSTRAINT fk_attempts_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS test_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attempt_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    selected_option_id BIGINT UNSIGNED NULL,
    text_response TEXT NULL,
    is_correct BOOLEAN NULL,
    score_earned DECIMAL(5,2) DEFAULT 0.00,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    CONSTRAINT fk_answers_attempt FOREIGN KEY (attempt_id) REFERENCES test_attempts (id) ON DELETE CASCADE,
    CONSTRAINT fk_answers_question FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE,
    CONSTRAINT fk_answers_option FOREIGN KEY (selected_option_id) REFERENCES question_options (id) ON DELETE SET NULL,
    CONSTRAINT fk_answers_reviewer FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('005_create_academy_and_tests_tables.sql', 1);

-- --------------------------------------------------------
-- Migration: 006_create_competencies_and_evaluations_tables.sql
-- --------------------------------------------------------
-- Migration: 006_create_competencies_and_evaluations_tables.sql

CREATE TABLE IF NOT EXISTS competency_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS competencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    default_weight DECIMAL(5,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_comp_category FOREIGN KEY (category_id) REFERENCES competency_categories (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intern_competencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    competency_id BIGINT UNSIGNED NOT NULL,
    current_level TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=Iniciante, 2=Basico, 3=Intermediario, 4=Avancado, 5=Excelente',
    evaluated_by BIGINT UNSIGNED NOT NULL,
    evidence_notes TEXT NULL,
    evaluated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_intern_competency (intern_id, competency_id),
    CONSTRAINT fk_ic_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_ic_competency FOREIGN KEY (competency_id) REFERENCES competencies (id) ON DELETE CASCADE,
    CONSTRAINT fk_ic_evaluator FOREIGN KEY (evaluated_by) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS final_evaluations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL UNIQUE,
    supervisor_id BIGINT UNSIGNED NOT NULL,
    technical_knowledge_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    work_quality_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    responsibility_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    punctuality_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    teamwork_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    communication_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    proactivity_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    learning_ability_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    problem_solving_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    professional_behavior_score TINYINT UNSIGNED NOT NULL DEFAULT 3,
    average_score DECIMAL(4,2) DEFAULT 3.00,
    general_comments TEXT NULL,
    recommendation_hire BOOLEAN DEFAULT TRUE,
    status ENUM('draft', 'finalized') DEFAULT 'finalized',
    finalized_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_fe_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_fe_supervisor FOREIGN KEY (supervisor_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('006_create_competencies_and_evaluations_tables.sql', 1);

-- --------------------------------------------------------
-- Migration: 007_create_certificates_and_gamification_tables.sql
-- --------------------------------------------------------
-- Migration: 007_create_certificates_and_gamification_tables.sql

CREATE TABLE IF NOT EXISTS certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL UNIQUE,
    certificate_code VARCHAR(50) NOT NULL UNIQUE,
    validation_hash VARCHAR(64) NOT NULL UNIQUE,
    total_hours_completed DECIMAL(6,2) NOT NULL DEFAULT 300.00,
    final_score DECIMAL(5,2) NOT NULL DEFAULT 85.00,
    issue_date DATE NOT NULL,
    completion_date DATE NOT NULL,
    signatory_name VARCHAR(150) NOT NULL DEFAULT 'Direcção Geral Asoftmedia',
    signatory_role VARCHAR(100) NOT NULL DEFAULT 'Director Geral',
    pdf_file_path VARCHAR(255) NULL,
    status ENUM('valid', 'revoked', 'expired') DEFAULT 'valid',
    revocation_reason TEXT NULL,
    revoked_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cert_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    INDEX idx_cert_hash (validation_hash),
    INDEX idx_cert_code (certificate_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificate_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    certificate_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    validated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cl_certificate FOREIGN KEY (certificate_id) REFERENCES certificates (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) NOT NULL,
    icon VARCHAR(100) NOT NULL DEFAULT 'bi-award',
    points_reward INT UNSIGNED DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intern_badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    badge_id BIGINT UNSIGNED NOT NULL,
    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_intern_badge (intern_id, badge_id),
    CONSTRAINT fk_ib_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_ib_badge FOREIGN KEY (badge_id) REFERENCES badges (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intern_gamification_points (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    intern_id BIGINT UNSIGNED NOT NULL,
    points INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    source_type ENUM('task', 'attendance', 'test', 'course', 'bonus') NOT NULL,
    source_id BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_igp_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('007_create_certificates_and_gamification_tables.sql', 1);

-- --------------------------------------------------------
-- Migration: 008_create_audit_logs_and_settings_tables.sql
-- --------------------------------------------------------
-- Migration: 008_create_audit_logs_and_settings_tables.sql

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    module VARCHAR(100) NOT NULL,
    record_id BIGINT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    result ENUM('success', 'failed', 'suspicious') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_audit_action (action),
    INDEX idx_audit_module (module),
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    data_type ENUM('string', 'int', 'float', 'boolean', 'json') DEFAULT 'string',
    group_name VARCHAR(50) NOT NULL DEFAULT 'general',
    is_public BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'danger', 'success') DEFAULT 'info',
    action_url VARCHAR(255) NULL,
    read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    INDEX idx_notif_user_read (user_id, read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('008_create_audit_logs_and_settings_tables.sql', 1);

-- --------------------------------------------------------
-- Migration: 009_refinements_and_privacy_tables.sql
-- --------------------------------------------------------
-- Migration: 009_refinements_and_privacy_tables.sql

-- 1. Task History & Audit Trail
CREATE TABLE IF NOT EXISTS task_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'assigned, started, submitted, approved, rejected, reopened',
    previous_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    score DECIMAL(5,2) NULL,
    comments TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_th_assignment FOREIGN KEY (assignment_id) REFERENCES task_assignments (id) ON DELETE CASCADE,
    CONSTRAINT fk_th_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Content Doubts / Q&A in Study Zone
CREATE TABLE IF NOT EXISTS content_doubts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_id BIGINT UNSIGNED NOT NULL,
    intern_id BIGINT UNSIGNED NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NULL,
    answered_by BIGINT UNSIGNED NULL,
    answered_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cd_content FOREIGN KEY (content_id) REFERENCES learning_contents (id) ON DELETE CASCADE,
    CONSTRAINT fk_cd_intern FOREIGN KEY (intern_id) REFERENCES interns (id) ON DELETE CASCADE,
    CONSTRAINT fk_cd_answerer FOREIGN KEY (answered_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Institution Conversations & Direct Messages
CREATE TABLE IF NOT EXISTS institution_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(255) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    last_message_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_icv_institution FOREIGN KEY (institution_id) REFERENCES institutions (id) ON DELETE CASCADE,
    CONSTRAINT fk_icv_user FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS institution_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_im_conversation FOREIGN KEY (conversation_id) REFERENCES institution_conversations (id) ON DELETE CASCADE,
    CONSTRAINT fk_im_sender FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Privacy & Data Protection Compliance (Angola Lei 22/11)
CREATE TABLE IF NOT EXISTS privacy_consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    policy_version VARCHAR(20) NOT NULL DEFAULT '1.0',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    consent_type VARCHAR(50) DEFAULT 'general_policy',
    accepted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pc_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS privacy_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('access', 'rectification', 'deletion', 'opposition') NOT NULL,
    details TEXT NOT NULL,
    status ENUM('pending', 'under_review', 'fulfilled', 'rejected') DEFAULT 'pending',
    response_notes TEXT NULL,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_pr_resolver FOREIGN KEY (resolved_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Password Resets (Secure Tokens)
CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pwd_reset_token (token_hash),
    INDEX idx_pwd_reset_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('009_refinements_and_privacy_tables.sql', 1);

-- --------------------------------------------------------
-- Migration: 010_sync_institution_users.sql
-- --------------------------------------------------------
-- Migration: 010_sync_institution_users.sql
-- Sincronização automática de contas institucionais (Role 5) com senha padrao 123EstagioAsoft

-- 1. Criar utilizadores para instituicoes que tenham email e ainda nao possuam usuario
INSERT INTO users (name, email, phone, username, password_hash, status)
SELECT 
    COALESCE(i.contact_person, i.name) as name,
    i.email,
    i.phone,
    i.email as username,
    '$2y$10$CeAJdhPV7eyaS4g4NBm0dOeocpCc8dgozfU3SIqvsZ6TY2PsMPP2K' as password_hash,
    'active' as status
FROM institutions i
LEFT JOIN users u ON u.email = i.email OR u.username = i.email
WHERE i.deleted_at IS NULL 
  AND i.email IS NOT NULL 
  AND i.email != ''
  AND u.id IS NULL;

-- 2. Atribuir a Role 5 (institution) aos utilizadores institucionais
INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, 5
FROM institutions i
INNER JOIN users u ON u.email = i.email OR u.username = i.email
WHERE i.deleted_at IS NULL;

-- 3. Vincular instituicao ao utilizador na tabela institution_users
INSERT IGNORE INTO institution_users (institution_id, user_id)
SELECT i.id, u.id
FROM institutions i
INNER JOIN users u ON u.email = i.email OR u.username = i.email
WHERE i.deleted_at IS NULL;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('010_sync_institution_users.sql', 1);

-- --------------------------------------------------------
-- Migration: 011_add_must_change_password_to_users.sql
-- --------------------------------------------------------
-- Migration: 011_add_must_change_password_to_users.sql
-- Adds the must_change_password flag that allows forcing a password change on first login.

ALTER TABLE users
    ADD COLUMN must_change_password TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
    AFTER password_hash;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('011_add_must_change_password_to_users.sql', 1);

-- --------------------------------------------------------
-- Migration: 012_add_missing_columns_users_and_interns.sql
-- --------------------------------------------------------
-- Migration: 012_add_missing_columns_users_and_interns.sql
-- Adds columns to interns table required for registration and portfolio customization.

ALTER TABLE interns
    ADD COLUMN custom_course_name VARCHAR(150) NULL AFTER course,
    ADD COLUMN formation_level VARCHAR(50) NULL DEFAULT '13ª' AFTER custom_course_name,
    ADD COLUMN portfolio_html MEDIUMTEXT NULL,
    ADD COLUMN portfolio_css MEDIUMTEXT NULL,
    ADD COLUMN portfolio_js MEDIUMTEXT NULL,
    ADD COLUMN portfolio_frozen TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('012_add_missing_columns_users_and_interns.sql', 1);

-- --------------------------------------------------------
-- Migration: 013_reset_mock_intern_scores.sql
-- --------------------------------------------------------
-- Migration: 013_reset_mock_intern_scores.sql
-- Resets any mock overall_score to 0.00 for interns who have not started any activities yet.

UPDATE interns i
LEFT JOIN (SELECT intern_id, COUNT(*) as cnt FROM attendance GROUP BY intern_id) a ON a.intern_id = i.id
LEFT JOIN (SELECT intern_id, COUNT(*) as cnt FROM task_assignments GROUP BY intern_id) t ON t.intern_id = i.id
SET i.overall_score = 0.00, i.risk_level = 'normal'
WHERE COALESCE(a.cnt, 0) = 0 AND COALESCE(t.cnt, 0) = 0;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('013_reset_mock_intern_scores.sql', 1);


-- ========================================================
-- Dados Iniciais e Contas Padrao para Producao (AIMS)
-- Senha padrao para todos os usuarios iniciais: Password123!
-- ========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Roles
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_system`) VALUES
(1, 'super_admin', 'Super Administrador', 'Acesso total irrestrito ao sistema', 1),
(2, 'admin', 'Administrador Geral', 'Gestão operacional de instituições, utilizadores e relatórios', 1),
(3, 'supervisor', 'Supervisor de Estágio', 'Gestão direta de estagiários, tarefas, presenças e avaliações', 1),
(4, 'intern', 'Estagiário', 'Acesso ao portal do estagiário, ponto e materiais', 1),
(5, 'institution', 'Representante Institucional', 'Acompanhamento do desempenho dos seus alunos', 1)
ON DUPLICATE KEY UPDATE `display_name` = VALUES(`display_name`);

-- 2. Permissoes Essenciais
INSERT INTO `permissions` (`id`, `slug`, `name`, `group_name`, `description`) VALUES
(1, 'system.manage', 'Gerir Sistema', 'Sistema', 'Acesso às configurações globais e auditoria'),
(2, 'users.manage', 'Gerir Utilizadores', 'Utilizadores', 'Criar, editar e bloquear utilizadores'),
(3, 'institutions.manage', 'Gerir Instituições', 'Instituições', 'Gerir universidades e institutos parceiros'),
(4, 'interns.manage', 'Gerir Estagiários', 'Estagiários', 'Processo de admissão e alocação de estagiários'),
(5, 'attendance.manage', 'Gerir Presenças', 'Presenças', 'Aprovar e auditar registos de assiduidade'),
(6, 'attendance.record', 'Marcar Ponto', 'Presenças', 'Marcar presença via geolocalização / QR'),
(7, 'attendance.view_own', 'Ver Própria Presença', 'Presenças', 'Consultar histórico de assiduidade'),
(8, 'tasks.manage', 'Gerir Tarefas', 'Tarefas', 'Criar e atribuir tarefas práticas'),
(9, 'tasks.submit', 'Submeter Tarefas', 'Tarefas', 'Enviar entregáveis e links de repositórios'),
(10, 'tasks.view', 'Visualizar Tarefas', 'Tarefas', 'Ver catálogo e atribuições de tarefas'),
(11, 'evaluations.manage', 'Gerir Avaliações', 'Avaliações', 'Lançar notas e avaliações periódicas'),
(12, 'academy.manage', 'Gerir Academia', 'Academia', 'Gerir cursos, módulos e testes online'),
(13, 'academy.view_learn', 'Acesso à Aprendizagem', 'Academia', 'Acessar trilhas de cursos e conteúdos'),
(14, 'tests.take', 'Realizar Testes', 'Academia', 'Responder a testes e simulados'),
(15, 'certificates.manage', 'Gerir Certificados', 'Certificados', 'Emitir e revogar certificados de estágio'),
(16, 'certificates.download_own', 'Baixar Certificado', 'Certificados', 'Baixar certificado de conclusão com QR Code'),
(17, 'reports.view', 'Visualizar Relatórios', 'Relatórios', 'Acessar relatórios estatísticos e analíticos'),
(18, 'reports.export', 'Exportar Relatórios', 'Relatórios', 'Exportar relatórios em PDF e Excel'),
(19, 'interns.view_own_institution', 'Ver Alunos da Instituição', 'Instituições', 'Acompanhar alunos da sua própria universidade')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Associar Permissoes aos Roles
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16), (1, 17), (1, 18),
(2, 2), (2, 3), (2, 4), (2, 5), (2, 7), (2, 8), (2, 10), (2, 11), (2, 12), (2, 15), (2, 17), (2, 18),
(3, 4), (3, 5), (3, 7), (3, 8), (3, 10), (3, 11), (3, 12), (3, 17),
(4, 6), (4, 7), (4, 9), (4, 10), (4, 13), (4, 14), (4, 16),
(5, 19), (5, 7), (5, 10), (5, 17), (5, 18);

-- 3. Configuracoes do Sistema
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `data_type`, `group_name`, `is_public`) VALUES
('company_name', 'Asoftmedia', 'string', 'company', 1),
('company_email', 'contacto@asoftmedia.ao', 'string', 'company', 1),
('company_phone', '+244 923 000 000', 'string', 'company', 1),
('company_address', 'Rua Principal de Talatona, Edifício Asoft, Luanda', 'string', 'company', 1),
('company_latitude', '-8.83833000', 'float', 'geolocation', 1),
('company_longitude', '13.23444000', 'float', 'geolocation', 1),
('company_radius_meters', '100', 'int', 'geolocation', 1),
('weight_attendance', '20', 'int', 'evaluation_weights', 1),
('weight_tasks', '30', 'int', 'evaluation_weights', 1),
('weight_tests', '20', 'int', 'evaluation_weights', 1),
('weight_competencies', '15', 'int', 'evaluation_weights', 1),
('weight_behavior', '10', 'int', 'evaluation_weights', 1),
('weight_final_eval', '5', 'int', 'evaluation_weights', 1),
('min_attendance_percentage', '80', 'int', 'completion_rules', 1),
('min_passing_grade', '60', 'int', 'completion_rules', 1),
('enable_gamification', '1', 'boolean', 'gamification', 1)
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 4. Categorias de Tarefas
INSERT INTO `task_categories` (`id`, `name`, `color_badge`, `description`) VALUES
(1, 'Programação', 'primary', 'Desenvolvimento backend, frontend, APIs e lógica de programação'),
(2, 'Redes', 'info', 'Configuração de redes, roteamento, VLANs, subnets e protocolos'),
(3, 'Sistemas', 'success', 'Administração de sistemas operacionais Linux/Windows, serviços e servidores'),
(4, 'Bases de Dados', 'warning', 'Modelagem, queries SQL, triggers, procedures e otimização'),
(5, 'Segurança', 'danger', 'Práticas de cibersegurança, criptografia, sanitização e pentest básico'),
(6, 'Suporte Técnico', 'secondary', 'Helpdesk, diagnóstico de hardware, software e atendimento ao usuário'),
(7, 'Infraestrutura', 'dark', 'Servidores, cloud, virtualização, Docker e ambientes de staging'),
(8, 'Geral', 'light', 'Atividades interdisciplinares, documentação e onboarding')
ON DUPLICATE KEY UPDATE `color_badge` = VALUES(`color_badge`), `description` = VALUES(`description`);

-- 5. Conquistas e Badges
INSERT INTO `badges` (`id`, `slug`, `name`, `description`, `icon`, `points_reward`) VALUES
(1, 'first_task', 'Primeira Tarefa', 'Concluiu a sua primeira tarefa prática com sucesso.', 'bi-flag-fill', 50),
(2, 'git_master', 'Git Master', 'Submeteu 5 tarefas com repositórios GitHub e Pull Requests.', 'bi-git', 100),
(3, 'perfect_attendance', 'Presença de Ferro', '100% de presença e pontualidade no primeiro mês.', 'bi-shield-check', 150),
(4, 'academy_star', 'Mestre da Academia', 'Completou todos os módulos do curso obrigatório.', 'bi-mortarboard-fill', 200),
(5, 'quiz_ace', 'Gênio dos Testes', 'Atingiu nota máxima (100%) em um teste de avaliação.', 'bi-star-fill', 100),
(6, 'problem_solver', 'Solucionador de Problemas', 'Superou nível 4 na competência de resolução analítica.', 'bi-lightning-charge-fill', 120)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 6. Usuarios Padrao Administrativos (Senha: Password123!)
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `username`, `password_hash`, `status`) VALUES
(1, 'Super Administrador Asoft', 'superadmin@asoftmedia.ao', '+244923000001', 'superadmin', '$2y$10$/ycMO/8w/2C03KdUvEigYePiZ4wU0ht3IxMHOw4Vs/Q/5xakCq2oi', 'active'),
(2, 'Administrador Geral', 'admin@asoftmedia.ao', '+244923000002', 'admin', '$2y$10$/ycMO/8w/2C03KdUvEigYePiZ4wU0ht3IxMHOw4Vs/Q/5xakCq2oi', 'active'),
(3, 'Eng. Carlos Silva (Supervisor Dev)', 'carlos.silva@asoftmedia.ao', '+244923000003', 'carlos.silva', '$2y$10$/ycMO/8w/2C03KdUvEigYePiZ4wU0ht3IxMHOw4Vs/Q/5xakCq2oi', 'active'),
(4, 'Eng. Ana Santos (Supervisora Redes)', 'ana.santos@asoftmedia.ao', '+244923000004', 'ana.santos', '$2y$10$/ycMO/8w/2C03KdUvEigYePiZ4wU0ht3IxMHOw4Vs/Q/5xakCq2oi', 'active')
ON DUPLICATE KEY UPDATE `password_hash` = VALUES(`password_hash`);

-- Associar Usuarios aos Roles
INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 3);

SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 1;
