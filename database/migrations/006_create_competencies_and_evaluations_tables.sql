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
