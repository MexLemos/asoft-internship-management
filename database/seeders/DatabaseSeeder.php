<?php

declare(strict_types=1);

namespace Database\Seeders;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;
use Dotenv\Dotenv;
use PDO;

class DatabaseSeeder
{
    private PDO $pdo;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->safeLoad();
        $this->pdo = Database::getConnection();
    }

    public function run(): void
    {
        echo "A iniciar o Seeder da base de dados...\n";

        $this->seedRolesAndPermissions();
        $this->seedSystemSettings();
        $this->seedTaskCategories();
        $this->seedCompetencies();
        $this->seedBadges();
        $this->seedInstitutionsAndUsers();
        $this->seedAcademyAndTests();
        $this->seedTasksAndAssignments();
        $this->seedAttendanceRecords();
        $this->seedCompetencyEvaluations();

        echo "✔ Banco de dados semeado com sucesso com dados completos de demonstração!\n";
    }

    private function seedRolesAndPermissions(): void
    {
        echo "- Semeando Perfis e Permissões...\n";

        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Administrador', 'description' => 'Acesso total e irrestrito a todas as configurações e módulos'],
            ['name' => 'admin', 'display_name' => 'Administrador', 'description' => 'Gestão de estagiários, instituições, conteúdos e relatórios'],
            ['name' => 'supervisor', 'display_name' => 'Supervisor / Orientador', 'description' => 'Acompanhamento, criação/avaliação de tarefas e competências'],
            ['name' => 'intern', 'display_name' => 'Estagiário', 'description' => 'Portal de aprendizagem, marcação de presença e execução de tarefas'],
            ['name' => 'institution', 'display_name' => 'Instituição de Ensino', 'description' => 'Acesso de observador ao progresso e presença de seus alunos'],
        ];

        $stmtRole = $this->pdo->prepare("INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE display_name = VALUES(display_name)");
        foreach ($roles as $r) {
            $stmtRole->execute([$r['name'], $r['display_name'], $r['description']]);
        }

        $permissions = [
            // Users & Settings
            ['slug' => 'users.manage', 'name' => 'Gerir Usuários', 'group' => 'Usuários'],
            ['slug' => 'settings.manage', 'name' => 'Gerir Configurações do Sistema', 'group' => 'Configurações'],
            ['slug' => 'audit.view', 'name' => 'Consultar Logs de Auditoria', 'group' => 'Auditoria'],
            // Institutions
            ['slug' => 'institutions.manage', 'name' => 'Gerir Instituições', 'group' => 'Instituições'],
            ['slug' => 'institutions.view', 'name' => 'Visualizar Instituições', 'group' => 'Instituições'],
            // Interns
            ['slug' => 'interns.create', 'name' => 'Cadastrar Estagiários', 'group' => 'Estagiários'],
            ['slug' => 'interns.edit', 'name' => 'Editar Estagiários', 'group' => 'Estagiários'],
            ['slug' => 'interns.view_all', 'name' => 'Visualizar Todos os Estagiários', 'group' => 'Estagiários'],
            ['slug' => 'interns.view_assigned', 'name' => 'Visualizar Estagiários sob Orientação', 'group' => 'Estagiários'],
            ['slug' => 'interns.view_own_institution', 'name' => 'Visualizar Alunos da Instituição', 'group' => 'Estagiários'],
            // Attendance
            ['slug' => 'attendance.record', 'name' => 'Marcar Presença por GPS', 'group' => 'Presença'],
            ['slug' => 'attendance.manage', 'name' => 'Gerir Presenças e Justificações', 'group' => 'Presença'],
            ['slug' => 'attendance.view_all', 'name' => 'Consultar Presença de Todos', 'group' => 'Presença'],
            ['slug' => 'attendance.view_own', 'name' => 'Consultar Própria Presença', 'group' => 'Presença'],
            // Tasks
            ['slug' => 'tasks.create', 'name' => 'Criar Tarefas', 'group' => 'Tarefas'],
            ['slug' => 'tasks.edit', 'name' => 'Editar Tarefas', 'group' => 'Tarefas'],
            ['slug' => 'tasks.assign', 'name' => 'Atribuir Tarefas', 'group' => 'Tarefas'],
            ['slug' => 'tasks.submit', 'name' => 'Submeter Tarefas', 'group' => 'Tarefas'],
            ['slug' => 'tasks.evaluate', 'name' => 'Avaliar e Corrigir Tarefas', 'group' => 'Tarefas'],
            ['slug' => 'tasks.view', 'name' => 'Visualizar Tarefas', 'group' => 'Tarefas'],
            // Academy & Tests
            ['slug' => 'academy.manage', 'name' => 'Gerir Cursos e Módulos', 'group' => 'Academia'],
            ['slug' => 'academy.view_learn', 'name' => 'Aceder a Cursos e Aulas', 'group' => 'Academia'],
            ['slug' => 'tests.manage', 'name' => 'Gerir Testes e Perguntas', 'group' => 'Testes'],
            ['slug' => 'tests.take', 'name' => 'Realizar Testes Online', 'group' => 'Testes'],
            // Competencies & Evaluations
            ['slug' => 'competencies.evaluate', 'name' => 'Avaliar Competências', 'group' => 'Competências'],
            ['slug' => 'evaluations.final_create', 'name' => 'Emitir Parecer e Avaliação Final', 'group' => 'Avaliações'],
            // Certificates & Reports
            ['slug' => 'certificates.generate', 'name' => 'Gerar e Emitir Declarações', 'group' => 'Declarações'],
            ['slug' => 'certificates.download_own', 'name' => 'Baixar Próprio Certificado', 'group' => 'Declarações'],
            ['slug' => 'reports.view', 'name' => 'Consultar Relatórios Executivos', 'group' => 'Relatórios'],
            ['slug' => 'reports.export', 'name' => 'Exportar Relatórios PDF/CSV', 'group' => 'Relatórios'],
        ];

        $stmtPerm = $this->pdo->prepare("INSERT INTO permissions (slug, name, group_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), group_name = VALUES(group_name)");
        foreach ($permissions as $p) {
            $stmtPerm->execute([$p['slug'], $p['name'], $p['group']]);
        }

        // Fetch IDs
        $roleMap = $this->pdo->query("SELECT name, id FROM roles")->fetchAll(PDO::FETCH_KEY_PAIR);
        $permMap = $this->pdo->query("SELECT slug, id FROM permissions")->fetchAll(PDO::FETCH_KEY_PAIR);

        $rolePermAssignments = [
            'admin' => [
                'institutions.manage', 'institutions.view', 'interns.create', 'interns.edit', 'interns.view_all',
                'attendance.manage', 'attendance.view_all', 'tasks.create', 'tasks.edit', 'tasks.assign', 'tasks.evaluate', 'tasks.view',
                'academy.manage', 'academy.view_learn', 'tests.manage', 'competencies.evaluate', 'evaluations.final_create',
                'certificates.generate', 'reports.view', 'reports.export'
            ],
            'supervisor' => [
                'institutions.view', 'interns.view_assigned', 'attendance.view_all',
                'tasks.create', 'tasks.edit', 'tasks.assign', 'tasks.evaluate', 'tasks.view',
                'academy.view_learn', 'competencies.evaluate', 'evaluations.final_create', 'reports.view'
            ],
            'intern' => [
                'attendance.record', 'attendance.view_own', 'tasks.submit', 'tasks.view',
                'academy.view_learn', 'tests.take', 'certificates.download_own'
            ],
            'institution' => [
                'interns.view_own_institution', 'attendance.view_own', 'tasks.view', 'reports.view', 'reports.export'
            ]
        ];

        $stmtRP = $this->pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($rolePermAssignments as $roleName => $perms) {
            $roleId = $roleMap[$roleName];
            foreach ($perms as $slug) {
                if (isset($permMap[$slug])) {
                    $stmtRP->execute([$roleId, $permMap[$slug]]);
                }
            }
        }
    }

    private function seedSystemSettings(): void
    {
        echo "- Semeando Configurações do Sistema...\n";

        $settings = [
            ['company_name', 'Asoftmedia', 'string', 'company', 1],
            ['company_email', 'contacto@asoftmedia.ao', 'string', 'company', 1],
            ['company_phone', '+244 923 000 000', 'string', 'company', 1],
            ['company_address', 'Rua Principal de Talatona, Edifício Asoft, Luanda', 'string', 'company', 1],
            ['company_latitude', '-8.83833000', 'float', 'geolocation', 1],
            ['company_longitude', '13.23444000', 'float', 'geolocation', 1],
            ['company_radius_meters', '100', 'int', 'geolocation', 1],
            ['weight_attendance', '20', 'int', 'evaluation_weights', 1],
            ['weight_tasks', '30', 'int', 'evaluation_weights', 1],
            ['weight_tests', '20', 'int', 'evaluation_weights', 1],
            ['weight_competencies', '15', 'int', 'evaluation_weights', 1],
            ['weight_behavior', '10', 'int', 'evaluation_weights', 1],
            ['weight_final_eval', '5', 'int', 'evaluation_weights', 1],
            ['min_attendance_percentage', '80', 'int', 'completion_rules', 1],
            ['min_passing_grade', '60', 'int', 'completion_rules', 1],
            ['enable_gamification', '1', 'boolean', 'gamification', 1],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, data_type, group_name, is_public) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($settings as $s) {
            $stmt->execute($s);
        }
    }

    private function seedTaskCategories(): void
    {
        echo "- Semeando Categorias de Tarefas...\n";

        $cats = [
            ['Programação', 'primary', 'Desenvolvimento backend, frontend, APIs e lógica de programação'],
            ['Redes', 'info', 'Configuração de redes, roteamento, VLANs, subnets e protocolos'],
            ['Sistemas', 'success', 'Administração de sistemas operacionais Linux/Windows, serviços e servidores'],
            ['Bases de Dados', 'warning', 'Modelagem, queries SQL, triggers, procedures e otimização'],
            ['Segurança', 'danger', 'Práticas de cibersegurança, criptografia, sanitização e pentest básico'],
            ['Suporte Técnico', 'secondary', 'Helpdesk, diagnóstico de hardware, software e atendimento ao usuário'],
            ['Infraestrutura', 'dark', 'Servidores, cloud, virtualização, Docker e ambientes de staging'],
            ['Geral', 'light', 'Atividades interdisciplinares, documentação e onboarding'],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO task_categories (name, color_badge, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE color_badge = VALUES(color_badge), description = VALUES(description)");
        foreach ($cats as $c) {
            $stmt->execute($c);
        }
    }

    private function seedCompetencies(): void
    {
        echo "- Semeando Categorias e Competências...\n";

        $stmtCat = $this->pdo->prepare("INSERT INTO competency_categories (name, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = VALUES(description)");
        $stmtCat->execute(['Técnicas', 'Conhecimentos práticos e domínio de ferramentas tecnológicas']);
        $catTecId = (int)$this->pdo->lastInsertId() ?: 1;

        $stmtCat->execute(['Comportamentais', 'Habilidades interpessoais, postura profissional e atitude']);
        $catCompId = (int)$this->pdo->lastInsertId() ?: 2;

        $competencies = [
            [$catTecId, 'Programação PHP & MySQL', 'Capacidade de desenvolver lógicas orientadas a objetos, queries seguras e MVC.', 1.20],
            [$catTecId, 'Frontend (HTML5, CSS3, JS & Bootstrap)', 'Criação de layouts responsivos, manipulação de DOM e Fetch API.', 1.00],
            [$catTecId, 'Redes & Subnetting', 'Compreensão de topologias, cálculo de sub-redes e configuração de switches.', 1.00],
            [$catTecId, 'Git & Versionamento', 'Fluxo de trabalho com branches, commits atômicos e Pull Requests.', 1.00],
            [$catTecId, 'Bases de Dados & SQL', 'Normalização, relacionamentos e criação de índices eficazes.', 1.10],
            [$catCompId, 'Trabalho em Equipe', 'Colaboração ativa, respeito e partilha de conhecimento com colegas.', 1.00],
            [$catCompId, 'Comunicação Clara', 'Expressão oral e escrita objetiva, reportes concisos aos supervisores.', 1.00],
            [$catCompId, 'Proatividade & Autonomia', 'Iniciativa para resolver problemas e propor melhorias sem esperar ordens.', 1.10],
            [$catCompId, 'Pontualidade & Compromisso', 'Cumprimento rigoroso de horários de presença e prazos de tarefas.', 1.00],
            [$catCompId, 'Resolução de Problemas', 'Capacidade analítica para investigar causas-raiz e debugar erros.', 1.20],
        ];

        $stmt = $this->pdo->prepare("INSERT INTO competencies (category_id, name, description, default_weight) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE description = VALUES(description)");
        foreach ($competencies as $comp) {
            $stmt->execute($comp);
        }
    }

    private function seedBadges(): void
    {
        echo "- Semeando Conquistas e Badges de Gamificação...\n";

        $badges = [
            ['first_task', 'Primeira Tarefa', 'Concluiu a sua primeira tarefa prática com sucesso.', 'bi-flag-fill', 50],
            ['git_master', 'Git Master', 'Submeteu 5 tarefas com repositórios GitHub e Pull Requests.', 'bi-git', 100],
            ['perfect_attendance', 'Presença de Ferro', '100% de presença e pontualidade no primeiro mês.', 'bi-shield-check', 150],
            ['academy_star', 'Mestre da Academia', 'Completou todos os módulos do curso obrigatório.', 'bi-mortarboard-fill', 200],
            ['quiz_ace', 'Gênio dos Testes', 'Atingiu nota máxima (100%) em um teste de avaliação.', 'bi-star-fill', 100],
            ['problem_solver', 'Solucionador de Problemas', 'Superou nível 4 na competência de resolução analítica.', 'bi-lightning-charge-fill', 120]
        ];

        $stmt = $this->pdo->prepare("INSERT INTO badges (slug, name, description, icon, points_reward) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description)");
        foreach ($badges as $b) {
            $stmt->execute($b);
        }
    }

    private function seedInstitutionsAndUsers(): void
    {
        echo "- Semeando Instituições, Usuários e Estagiários...\n";

        $passwordHash = password_hash('Password123!', PASSWORD_BCRYPT);

        // 1. Super Admin
        $stmtUser = $this->pdo->prepare("
            INSERT INTO users (name, email, phone, username, password_hash, status)
            VALUES (?, ?, ?, ?, ?, 'active')
            ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)
        ");
        $stmtRoleAssign = $this->pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)");

        $roleMap = $this->pdo->query("SELECT name, id FROM roles")->fetchAll(PDO::FETCH_KEY_PAIR);

        // Super Admin
        $stmtUser->execute(['Super Administrador Asoft', 'superadmin@asoftmedia.ao', '+244923000001', 'superadmin', $passwordHash]);
        $superAdminId = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM users WHERE username = 'superadmin'")->fetchColumn();
        $stmtRoleAssign->execute([$superAdminId, $roleMap['super_admin']]);

        // Admin
        $stmtUser->execute(['Administrador Geral', 'admin@asoftmedia.ao', '+244923000002', 'admin', $passwordHash]);
        $adminId = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM users WHERE username = 'admin'")->fetchColumn();
        $stmtRoleAssign->execute([$adminId, $roleMap['admin']]);

        // Supervisors
        $stmtUser->execute(['Eng. Carlos Silva (Supervisor Dev)', 'carlos.silva@asoftmedia.ao', '+244923000003', 'carlos.silva', $passwordHash]);
        $sup1Id = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM users WHERE username = 'carlos.silva'")->fetchColumn();
        $stmtRoleAssign->execute([$sup1Id, $roleMap['supervisor']]);

        $stmtUser->execute(['Eng. Ana Santos (Supervisora Redes)', 'ana.santos@asoftmedia.ao', '+244923000004', 'ana.santos', $passwordHash]);
        $sup2Id = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM users WHERE username = 'ana.santos'")->fetchColumn();
        $stmtRoleAssign->execute([$sup2Id, $roleMap['supervisor']]);

        // Institutions
        $stmtInst = $this->pdo->prepare("
            INSERT INTO institutions (name, type, nif, email, phone, website, address, city, province, contact_person, contact_role, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Luanda', 'Luanda', ?, ?, 'active')
            ON DUPLICATE KEY UPDATE email = VALUES(email)
        ");

        $stmtInst->execute([
            'ISUTIC - Instituto Superior de Tecnologias de Informação e Comunicação',
            'universidade',
            '5000123456',
            'contacto@isutic.gov.ao',
            '+244924111222',
            'https://isutic.gov.ao',
            'Rua do Cuanza Norte, Rangel, Luanda',
            'Dr. Manuel Domingos',
            'Coordenador de Estágios'
        ]);
        $inst1Id = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM institutions WHERE nif = '5000123456'")->fetchColumn();

        $stmtInst->execute([
            'ITEL - Instituto de Telecomunicações',
            'instituto_medio',
            '5000654321',
            'direcao@itel.gov.ao',
            '+244924333444',
            'https://itel.gov.ao',
            'Avenida Deolinda Rodrigues, Luanda',
            'Prof. Teresa Afonso',
            'Directora Pedagógica'
        ]);
        $inst2Id = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM institutions WHERE nif = '5000654321'")->fetchColumn();

        // Institution Representative Users
        $stmtUser->execute(['Dr. Manuel Domingos (ISUTIC)', 'isutic@asoftmedia.ao', '+244924111222', 'isutic_obs', $passwordHash]);
        $instUser1Id = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM users WHERE username = 'isutic_obs'")->fetchColumn();
        $stmtRoleAssign->execute([$instUser1Id, $roleMap['institution']]);
        $this->pdo->exec("INSERT IGNORE INTO institution_users (institution_id, user_id) VALUES ({$inst1Id}, {$instUser1Id})");

        $stmtUser->execute(['Prof. Teresa Afonso (ITEL)', 'itel@asoftmedia.ao', '+244924333444', 'itel_obs', $passwordHash]);
        $instUser2Id = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM users WHERE username = 'itel_obs'")->fetchColumn();
        $stmtRoleAssign->execute([$instUser2Id, $roleMap['institution']]);
        $this->pdo->exec("INSERT IGNORE INTO institution_users (institution_id, user_id) VALUES ({$inst2Id}, {$instUser2Id})");

        // 10 Interns Data
        $internsData = [
            ['João Manuel', 'joao.manuel@asoftmedia.ao', 'joao.manuel', '005423189LA042', 'Engenharia Informática', $inst1Id, $sup1Id, '2026-08-01', '2026-11-30', 'Desenvolvimento de Software', 'normal', 88.50],
            ['Maria Fernandes', 'maria.fernandes@asoftmedia.ao', 'maria.fernandes', '006129845LA041', 'Engenharia Informática', $inst1Id, $sup1Id, '2026-08-01', '2026-11-30', 'Desenvolvimento Fullstack', 'normal', 92.00],
            ['António Costa', 'antonio.costa@asoftmedia.ao', 'antonio.costa', '004781290LA039', 'Redes e Telecomunicações', $inst2Id, $sup2Id, '2026-08-01', '2026-11-30', 'Infraestrutura e Redes', 'normal', 84.00],
            ['Beatriz Miguel', 'beatriz.miguel@asoftmedia.ao', 'beatriz.miguel', '008912344LA045', 'Técnico de Informática', $inst2Id, $sup1Id, '2026-08-01', '2026-11-30', 'Desenvolvimento Frontend', 'attention', 71.00],
            ['Cláudio Neto', 'claudio.neto@asoftmedia.ao', 'claudio.neto', '003291845LA038', 'Engenharia de Redes', $inst1Id, $sup2Id, '2026-08-01', '2026-11-30', 'Segurança e Redes', 'normal', 86.50],
            ['Domingas Sebastião', 'domingas.sebastiao@asoftmedia.ao', 'domingas.sebastiao', '007812390LA043', 'Ciências da Computação', $inst1Id, $sup1Id, '2026-08-01', '2026-11-30', 'Desenvolvimento Backend PHP', 'normal', 94.00],
            ['Edgar Muondo', 'edgar.muondo@asoftmedia.ao', 'edgar.muondo', '002918234LA037', 'Técnico de Redes', $inst2Id, $sup2Id, '2026-08-01', '2026-11-30', 'Suporte Técnico e Redes', 'risk', 55.00],
            ['Fátima Bumba', 'fatima.bumba@asoftmedia.ao', 'fatima.bumba', '009128345LA046', 'Engenharia Informática', $inst1Id, $sup1Id, '2026-08-01', '2026-11-30', 'Bases de Dados e Sistemas', 'normal', 89.00],
            ['Gabriel Kassoma', 'gabriel.kassoma@asoftmedia.ao', 'gabriel.kassoma', '001928374LA036', 'Telecomunicações', $inst2Id, $sup2Id, '2026-08-01', '2026-11-30', 'Infraestrutura de TI', 'normal', 79.50],
            ['Helena Zau', 'helena.zau@asoftmedia.ao', 'helena.zau', '004192837LA040', 'Engenharia Informática', $inst1Id, $sup1Id, '2026-08-01', '2026-11-30', 'Desenvolvimento Web', 'normal', 87.00],
        ];

        $stmtIntern = $this->pdo->prepare("
            INSERT INTO interns (user_id, institution_id, supervisor_id, internship_code, full_name, bi_number, course, internship_area, start_date, end_date, status, overall_score, risk_level, phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?)
            ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), overall_score = VALUES(overall_score), risk_level = VALUES(risk_level)
        ");

        $stmtSched = $this->pdo->prepare("
            INSERT INTO intern_schedules (intern_id, expected_start_time, expected_end_time, tolerance_minutes, daily_hours, total_required_hours)
            VALUES (?, '08:00:00', '12:00:00', 15, 4.00, 300.00)
            ON DUPLICATE KEY UPDATE daily_hours = 4.00
        ");

        $stmtDays = $this->pdo->prepare("
            INSERT INTO intern_schedule_days (intern_schedule_id, day_of_week, is_active)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE is_active = VALUES(is_active)
        ");

        $index = 1;
        foreach ($internsData as $d) {
            $phone = '+244923' . sprintf('%06d', 100 + $index);
            $stmtUser->execute([$d[0], $d[1], $phone, $d[2], $passwordHash]);
            $uId = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM users WHERE username = '{$d[2]}'")->fetchColumn();
            $stmtRoleAssign->execute([$uId, $roleMap['intern']]);

            $code = 'AST-2026-' . sprintf('%03d', $index);
            $stmtIntern->execute([
                $uId, $d[5], $d[6], $code, $d[0], $d[3], $d[4], $d[9], $d[7], $d[8], $d[11], $d[10], $phone
            ]);
            $internId = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM interns WHERE user_id = {$uId}")->fetchColumn();

            $stmtSched->execute([$internId]);
            $schedId = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM intern_schedules WHERE intern_id = {$internId}")->fetchColumn();

            // Schedule: Monday (1), Tuesday (2), Thursday (4), Friday (5)
            $activeDays = [1, 2, 4, 5];
            for ($day = 1; $day <= 7; $day++) {
                $isActive = in_array($day, $activeDays, true) ? 1 : 0;
                $stmtDays->execute([$schedId, $day, $isActive]);
            }

            $index++;
        }
    }

    private function seedAcademyAndTests(): void
    {
        echo "- Semeando Cursos, Módulos, Aulas e Testes Online...\n";

        // Course 1: Fundamentos de Desenvolvimento Web Moderno
        $stmtCourse = $this->pdo->prepare("
            INSERT INTO courses (title, slug, description, is_mandatory, status, order_index)
            VALUES (?, ?, ?, 1, 'published', ?)
            ON DUPLICATE KEY UPDATE description = VALUES(description)
        ");
        $stmtCourse->execute([
            'Fundamentos de Desenvolvimento Web Moderno',
            'fundamentos-web-moderno',
            'Curso preparatório completo abrangendo PHP 8, MySQL, HTML5/CSS3, JavaScript e boas práticas de arquitetura MVC.',
            1
        ]);
        $courseId = (int)$this->pdo->lastInsertId() ?: (int)$this->pdo->query("SELECT id FROM courses WHERE slug = 'fundamentos-web-moderno'")->fetchColumn();

        // Module 1: Arquitetura MVC e PHP 8.x
        $stmtMod = $this->pdo->prepare("
            INSERT INTO modules (course_id, title, slug, description, order_index)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtMod->execute([
            $courseId,
            'Módulo 1: Arquitetura MVC e PHP 8 Avançado',
            'modulo-1-arquitetura-mvc-php-8',
            'Compreenda a separação em camadas, OOP rigorosa, PDO com Prepared Statements e Roteamento limpo.',
            1
        ]);
        $mod1Id = (int)$this->pdo->lastInsertId() ?: 1;

        // Lessons for Module 1
        $stmtLesson = $this->pdo->prepare("
            INSERT INTO lessons (module_id, title, slug, order_index)
            VALUES (?, ?, ?, ?)
        ");
        $stmtLesson->execute([$mod1Id, 'Aula 1: Padrão MVC e Inversão de Controlo', 'aula-1-mvc', 1]);
        $less1Id = (int)$this->pdo->lastInsertId() ?: 1;

        $stmtLesson->execute([$mod1Id, 'Aula 2: Segurança com PDO e Prepared Statements', 'aula-2-pdo-seguranca', 2]);
        $less2Id = (int)$this->pdo->lastInsertId() ?: 2;

        // Contents
        $stmtContent = $this->pdo->prepare("
            INSERT INTO learning_contents (lesson_id, title, content_type, content_url_or_path, duration_minutes, article_body, order_index)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtContent->execute([
            $less1Id,
            'Vídeo: Introdução à Arquitetura MVC em PHP',
            'youtube_video',
            'https://www.youtube.com/watch?v=DuB6UjEsBQk',
            15,
            'Conceitos fundamentais de Model, View e Controller explicados de forma prática.',
            1
        ]);

        $stmtContent->execute([
            $less1Id,
            'Manual em PDF: Boas Práticas de MVC e Clean Code',
            'pdf_document',
            '/assets/docs/manual_mvc_asoftmedia.pdf',
            20,
            'Guia de referência da Asoftmedia para escrita de código limpo e sustentável.',
            2
        ]);

        // Online Test for Module 1
        $stmtTest = $this->pdo->prepare("
            INSERT INTO tests (module_id, title, description, passing_score, max_attempts, time_limit_minutes, shuffle_questions, status)
            VALUES (?, ?, ?, 70.00, 3, 30, 1, 'active')
        ");
        $stmtTest->execute([
            $mod1Id,
            'Teste de Avaliação: Arquitetura MVC & PHP 8',
            'Avaliação de conhecimentos sobre injeção de dependências, PDO, sanitização e fluxo MVC.'
        ]);
        $test1Id = (int)$this->pdo->lastInsertId() ?: 1;

        // Test Questions
        $stmtQ = $this->pdo->prepare("
            INSERT INTO questions (test_id, question_type, statement, explanation, score_points, order_index)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtOpt = $this->pdo->prepare("
            INSERT INTO question_options (question_id, option_text, is_correct, order_index)
            VALUES (?, ?, ?, ?)
        ");

        // Question 1
        $stmtQ->execute([
            $test1Id,
            'multiple_choice',
            'Qual é a principal responsabilidade do Controller no padrão MVC?',
            'O Controller apenas orquestra a requisição do utilizador, chamando os serviços necessários e retornando a View ou JSON apropriado.',
            25.00,
            1
        ]);
        $q1Id = (int)$this->pdo->lastInsertId() ?: 1;
        $stmtOpt->execute([$q1Id, 'Aceder diretamente ao banco de dados via queries SQL puras', 0, 1]);
        $stmtOpt->execute([$q1Id, 'Receber a requisição HTTP, invocar a lógica de negócio e selecionar a View de resposta', 1, 2]);
        $stmtOpt->execute([$q1Id, 'Renderizar estilos CSS e animações no navegador do cliente', 0, 3]);
        $stmtOpt->execute([$q1Id, 'Gerir a criação de tabelas e migrações no MySQL', 0, 4]);

        // Question 2
        $stmtQ->execute([
            $test1Id,
            'true_false',
            'O uso de PDO com Prepared Statements previne vulnerabilidades de SQL Injection de forma eficaz.',
            'Verdadeiro. Prepared Statements separam a estrutura da query dos dados enviados, impedindo a interpretação de comandos maliciosos.',
            25.00,
            2
        ]);
        $q2Id = (int)$this->pdo->lastInsertId() ?: 2;
        $stmtOpt->execute([$q2Id, 'Verdadeiro', 1, 1]);
        $stmtOpt->execute([$q2Id, 'Falso', 0, 2]);

        // Question 3
        $stmtQ->execute([
            $test1Id,
            'multiple_choice',
            'Para que serve a função password_hash() no PHP 8?',
            'password_hash() aplica algoritmos seguros como BCRYPT ou ARGON2ID com salt aleatório embutido.',
            25.00,
            3
        ]);
        $q3Id = (int)$this->pdo->lastInsertId() ?: 3;
        $stmtOpt->execute([$q3Id, 'Criptografar arquivos PDF para download seguro', 0, 1]);
        $stmtOpt->execute([$q3Id, 'Gerar hashes criptográficos seguros e unidirecionais para senhas de utilizador', 1, 2]);
        $stmtOpt->execute([$q3Id, 'Compactar imagens de avatar para economizar espaço em disco', 0, 3]);

        // Question 4
        $stmtQ->execute([
            $test1Id,
            'multiple_choice',
            'Qual é a finalidade de utilizar tokens CSRF em formulários web?',
            'O token CSRF garante que a requisição de alteração de dados foi genuinamente originada pela aplicação e não por um site de terceiros.',
            25.00,
            4
        ]);
        $q4Id = (int)$this->pdo->lastInsertId() ?: 4;
        $stmtOpt->execute([$q4Id, 'Impedir ataques de Cross-Site Request Forgery em requisições de mutação de estado', 1, 1]);
        $stmtOpt->execute([$q4Id, 'Acelerar o carregamento de imagens no frontend', 0, 2]);
        $stmtOpt->execute([$q4Id, 'Converter código JavaScript em PHP automaticamente', 0, 3]);
    }

    private function seedTasksAndAssignments(): void
    {
        echo "- Semeando Tarefas Práticas e Atribuições com GitHub...\n";

        $catMap = $this->pdo->query("SELECT name, id FROM task_categories")->fetchAll(PDO::FETCH_KEY_PAIR);
        $supId = (int)$this->pdo->query("SELECT id FROM users WHERE username = 'carlos.silva'")->fetchColumn();
        $interns = $this->pdo->query("SELECT id, user_id FROM interns LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        $tasks = [
            [
                'title' => 'Implementação de CRUD Seguro com PDO e CSRF',
                'category' => 'Programação',
                'description' => 'Desenvolver um módulo de cadastro com validação de campos, proteção CSRF e queries parametrizadas usando PDO.',
                'objective' => 'Garantir que o estagiário domine a manipulação segura de dados em PHP.',
                'instructions' => '1. Crie o Controller e a View correspondente.\n2. Utilize Prepared Statements.\n3. Envie o link do repositório GitHub e Pull Request para avaliação.',
                'priority' => 'high',
                'points' => 100,
                'hours' => 6.00,
                'github' => 1
            ],
            [
                'title' => 'Configuração de Sub-redes e Roteamento VLAN',
                'category' => 'Redes',
                'description' => 'Calcular plano de endereçamento IP para rede corporativa /24 dividida em 4 departamentos.',
                'objective' => 'Compreender segmentação de redes e cálculo de máscaras VLSM.',
                'instructions' => '1. Calcule os intervalos de IP para cada VLAN.\n2. Submeta o relatório em PDF com a tabela de endereçamento.',
                'priority' => 'medium',
                'points' => 80,
                'hours' => 4.00,
                'github' => 0
            ],
            [
                'title' => 'Otimização de Índices e Queries SQL no MySQL',
                'category' => 'Bases de Dados',
                'description' => 'Analisar plano de execução com EXPLAIN e criar índices compostos para reduzir tempo de resposta.',
                'objective' => 'Dominar performance de consultas relacionais.',
                'instructions' => 'Apresente os testes de benchmark antes e depois da indexação.',
                'priority' => 'medium',
                'points' => 90,
                'hours' => 5.00,
                'github' => 1
            ]
        ];

        $stmtTask = $this->pdo->prepare("
            INSERT INTO tasks (category_id, created_by, title, description, objective, instructions, priority, points, estimated_hours, requires_github, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published')
        ");

        $stmtAssign = $this->pdo->prepare("
            INSERT INTO task_assignments (task_id, intern_id, assigned_by, start_date, due_date, status, score, supervisor_feedback, reviewed_by, reviewed_at, started_at, completed_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtSub = $this->pdo->prepare("
            INSERT INTO task_submissions (assignment_id, intern_id, notes, github_repo_url, github_branch, github_commit_hash, github_pr_url, submitted_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($tasks as $t) {
            $catId = $catMap[$t['category']];
            $stmtTask->execute([
                $catId, $supId, $t['title'], $t['description'], $t['objective'], $t['instructions'], $t['priority'], $t['points'], $t['hours'], $t['github']
            ]);
            $taskId = (int)$this->pdo->lastInsertId();

            // Assign to first few interns
            foreach ($interns as $idx => $intern) {
                $status = ($idx === 0) ? 'approved' : (($idx === 1) ? 'submitted' : 'in_progress');
                $score = ($status === 'approved') ? 95.00 : null;
                $feedback = ($status === 'approved') ? 'Excelente organização do código, cumprimento exemplar dos padrões PSR-12 e testes funcionais.' : null;
                $revBy = ($status === 'approved') ? $supId : null;
                $revAt = ($status === 'approved') ? date('Y-m-d H:i:s') : null;
                $completedAt = ($status === 'approved') ? date('Y-m-d H:i:s', strtotime('-1 day')) : null;

                $stmtAssign->execute([
                    $taskId, $intern['id'], $supId, '2026-08-05', '2026-08-25', $status, $score, $feedback, $revBy, $revAt, '2026-08-06 09:00:00', $completedAt
                ]);
                $assignId = (int)$this->pdo->lastInsertId();

                if ($status === 'approved' || $status === 'submitted') {
                    $stmtSub->execute([
                        $assignId,
                        $intern['id'],
                        'Implementação concluída com sucesso. Todos os testes unitários passando.',
                        'https://github.com/asoftmedia-interns/modulo-crud-seguro',
                        'feature/crud-validation',
                        'a1b2c3d4e5f6',
                        'https://github.com/asoftmedia-interns/modulo-crud-seguro/pull/1',
                        date('Y-m-d H:i:s', strtotime('-2 days'))
                    ]);
                }
            }
        }
    }

    private function seedAttendanceRecords(): void
    {
        echo "- Semeando Histórico de Presença com Geolocalização...\n";

        $interns = $this->pdo->query("SELECT id FROM interns LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);

        $stmtAtt = $this->pdo->prepare("
            INSERT INTO attendance (
                intern_id, date, check_in_time, check_in_lat, check_in_lng, check_in_accuracy, check_in_distance_meters, check_in_ip, check_in_device, check_in_status,
                check_out_time, check_out_lat, check_out_lng, check_out_accuracy, check_out_distance_meters, check_out_ip, check_out_device, check_out_status,
                hours_worked, status
            ) VALUES (
                ?, ?, '08:02:15', -8.83832000, 13.23445000, 12.50, 24.30, '197.149.12.34', 'Chrome Mobile / Android 14', 'on_time',
                '12:05:30', -8.83832500, 13.23444800, 14.20, 28.10, '197.149.12.34', 'Chrome Mobile / Android 14', 'normal',
                4.05, 'present'
            ) ON DUPLICATE KEY UPDATE hours_worked = VALUES(hours_worked)
        ");

        $dates = ['2026-08-04', '2026-08-07', '2026-08-11', '2026-08-14', '2026-08-18'];
        foreach ($interns as $internId) {
            foreach ($dates as $date) {
                $stmtAtt->execute([$internId, $date]);
            }
        }
    }

    private function seedCompetencyEvaluations(): void
    {
        echo "- Semeando Avaliações de Competências...\n";

        $supId = (int)$this->pdo->query("SELECT id FROM users WHERE username = 'carlos.silva'")->fetchColumn();
        $internId = (int)$this->pdo->query("SELECT id FROM interns LIMIT 1")->fetchColumn();
        $comps = $this->pdo->query("SELECT id FROM competencies LIMIT 6")->fetchAll(PDO::FETCH_COLUMN);

        $stmtEval = $this->pdo->prepare("
            INSERT INTO intern_competencies (intern_id, competency_id, current_level, evaluated_by, evidence_notes, evaluated_at)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE current_level = VALUES(current_level)
        ");

        $levels = [4, 4, 3, 5, 4, 4];
        foreach ($comps as $i => $compId) {
            $lvl = $levels[$i] ?? 3;
            $stmtEval->execute([
                $internId, $compId, $lvl, $supId, 'Demonstrou excelente capacidade de síntese e entrega consistente de código.'
            ]);
        }
    }
}

if (php_sapi_name() === 'cli' && isset($argv[0]) && basename($argv[0]) === basename(__FILE__)) {
    $seeder = new DatabaseSeeder();
    $seeder->run();
}
