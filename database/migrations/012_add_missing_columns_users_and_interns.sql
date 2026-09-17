-- Migration: 012_add_missing_columns_users_and_interns.sql
-- Adds columns that exist in the codebase but were absent from the original schema.

-- Users table: force password change flag
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS must_change_password TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
    AFTER password_hash;

-- Interns table: custom course name (used when course = 'Outro')
ALTER TABLE interns
    ADD COLUMN IF NOT EXISTS custom_course_name VARCHAR(150) NULL
    AFTER course;

-- Interns table: academic formation level (e.g. "13ª", "Licenciatura")
ALTER TABLE interns
    ADD COLUMN IF NOT EXISTS formation_level VARCHAR(50) NULL DEFAULT '13ª'
    AFTER custom_course_name;
