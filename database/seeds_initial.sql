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