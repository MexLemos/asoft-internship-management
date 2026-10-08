-- Migration 019: Suporte a Estagiários Singulares (sem instituição obrigatória)
-- Permite que institution_id seja nulo para candidatos particulares / singulares
-- e cria a instituição 'Singular (Candidatura Particular)' como registro de suporte.

ALTER TABLE interns MODIFY COLUMN institution_id BIGINT UNSIGNED NULL;

INSERT INTO institutions (name, nif, email, phone, address, city, type, status)
SELECT 'Singular (Candidatura Particular)', 'SINGULAR', 'singular@asoftmedia-ao.com', 'N/D', 'Luanda', 'Luanda', 'other', 'active'
WHERE NOT EXISTS (
    SELECT 1 FROM institutions WHERE name LIKE 'Singular%' OR nif = 'SINGULAR'
);
