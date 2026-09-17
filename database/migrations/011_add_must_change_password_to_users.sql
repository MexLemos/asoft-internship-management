-- Migration: 011_add_must_change_password_to_users.sql
-- Adds the must_change_password flag that allows forcing a password change on first login.

ALTER TABLE users
    ADD COLUMN must_change_password TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
    AFTER password_hash;
