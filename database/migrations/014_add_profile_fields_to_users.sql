-- Migration: 014_add_profile_fields_to_users.sql
-- Adds profile_photo, linkedin_url, and github_url to users table

ALTER TABLE users
    ADD COLUMN profile_photo VARCHAR(255) NULL AFTER avatar,
    ADD COLUMN linkedin_url VARCHAR(255) NULL AFTER status,
    ADD COLUMN github_url VARCHAR(255) NULL AFTER linkedin_url;
