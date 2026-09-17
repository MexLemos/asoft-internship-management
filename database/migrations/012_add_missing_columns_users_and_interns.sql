-- Migration: 012_add_missing_columns_users_and_interns.sql
-- Adds columns to interns table required for registration and portfolio customization.

ALTER TABLE interns
    ADD COLUMN custom_course_name VARCHAR(150) NULL AFTER course,
    ADD COLUMN formation_level VARCHAR(50) NULL DEFAULT '13ª' AFTER custom_course_name,
    ADD COLUMN portfolio_html MEDIUMTEXT NULL,
    ADD COLUMN portfolio_css MEDIUMTEXT NULL,
    ADD COLUMN portfolio_js MEDIUMTEXT NULL,
    ADD COLUMN portfolio_frozen TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
