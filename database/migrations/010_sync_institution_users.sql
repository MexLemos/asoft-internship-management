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
