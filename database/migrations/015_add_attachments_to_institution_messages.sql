-- Migration: 015_add_attachments_to_institution_messages.sql
-- Adds attachment_path and attachment_name to institution_messages table

ALTER TABLE institution_messages
    ADD COLUMN attachment_path VARCHAR(255) NULL AFTER message,
    ADD COLUMN attachment_name VARCHAR(255) NULL AFTER attachment_path;
