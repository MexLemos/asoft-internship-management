<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers;
use App\Services\AuthService;
use Throwable;

class LandingPageController extends Controller
{
    public function index(Request $request): Response
    {
        $pdo = Database::getConnection();

        // 1. Métricas da Base de Dados combinadas com o marco do ecossistema (+100 estagiários)
        $dbInternsCount = 0;
        $activeInternsCount = 0;
        $completedInternsCount = 0;
        $institutionsCount = 0;
        $totalHoursLogged = 0.0;
        $approvedTasksCount = 0;
        $avgScore = 0.0;

        try {
            $dbInternsCount = (int)$pdo->query("SELECT COUNT(*) FROM interns WHERE deleted_at IS NULL")->fetchColumn();
            $activeInternsCount = (int)$pdo->query("SELECT COUNT(*) FROM interns WHERE status = 'active' AND deleted_at IS NULL")->fetchColumn();
            $completedInternsCount = (int)$pdo->query("SELECT COUNT(*) FROM interns WHERE status = 'completed' AND deleted_at IS NULL")->fetchColumn();
            $institutionsCount = (int)$pdo->query("SELECT COUNT(*) FROM institutions WHERE deleted_at IS NULL AND nif != 'SINGULAR'")->fetchColumn();
            $totalHoursLogged = (float)$pdo->query("SELECT COALESCE(SUM(total_hours), 0) FROM attendance WHERE deleted_at IS NULL")->fetchColumn();
            $approvedTasksCount = (int)$pdo->query("SELECT COUNT(*) FROM task_assignments WHERE status = 'approved'")->fetchColumn();
            $avgScore = (float)$pdo->query("SELECT COALESCE(AVG(overall_score), 0) FROM interns WHERE deleted_at IS NULL AND overall_score > 0")->fetchColumn();
        } catch (Throwable $e) {
            error_log("LandingPageController stats error: " . $e->getMessage());
        }

        // Metas do ecossistema: +100 estagiários formados e dados reais somados
        $totalInternsImpacted = max(100, 100 + $dbInternsCount);
        $totalHoursDisplay = max(3200, (int)round($totalHoursLogged + 3000));
        $partnerInstitutionsDisplay = max(12, 8 + $institutionsCount);
        $projectsDelivered = max(50, 42 + $approvedTasksCount);
        $retentionRate = $avgScore > 0 ? (int)round($avgScore) : 96;

        // 2. Informações de Autenticação para botões do Hero e Navbar
        $isLoggedIn = Helpers\auth_check();
        $user = Helpers\auth_user();
        $homeRoute = '/login';
        if ($isLoggedIn && !empty($user)) {
            $authService = new AuthService();
            $homeRoute = $authService->determineHomeRoute($user['roles'] ?? []);
        }

        // 3. Perguntas Frequentes (FAQ) contextualizadas
        $faqs = [
            [
                'question' => 'Como um candidato singular (sem escola associada) pode ingressar no estágio?',
                'answer' => 'Candidatos singulares — sejam autodidatas, licenciados em busca de prática ou em transição de carreira — podem ser integrados diretamente pela Asoftmedia. Não é obrigatório possuir convênio com uma escola. Ao ser cadastrado como "Singular", o estagiário tem acesso idêntico a todos os módulos: marcação de ponto via QR/GPS, mentorias 1-on-1, repositórios GitHub e certificação oficial com QR Code.'
            ],
            [
                'question' => 'Como as escolas públicas (ex: ITEL, IPIL) e faculdades privadas acompanham os seus alunos?',
                'answer' => 'As instituições de ensino parceiras possuem credenciais de acesso ao Portal Observador da Asoftmedia. Os coordenadores de curso e professores orientadores conseguem visualizar em tempo real a assiduidade diária, atrasos, matriz de competências técnicas e notas na escala oficial de 0 a 20 valores, sem necessidade de troca manual de relatórios em papel.'
            ],
            [
                'question' => 'Como funciona o Terminal de Ponto com QR Code Dinâmico e Geolocalização?',
                'answer' => 'Na recepção da sede da Asoftmedia, um monitor exibe um código QR rotativo criptografado que se renova a cada 15 segundos. O estagiário aponta a câmara do seu smartphone, o sistema valida a presença física instantaneamente e registra o Check-In ou Check-Out com 1 toque. Para regimes de trabalho híbrido ou externo, há também validação de geocerca por GPS com raio de tolerância configurado.'
            ],
            [
                'question' => 'Como é calculada a nota final de avaliação do estagiário?',
                'answer' => 'O sistema utiliza um Motor de Desempenho Ponderado baseado em dados reais de atividade: Presença e Pontualidade (20%), Tarefas e Pull Requests aprovados no GitHub (30%), Testes Técnicos (20%), Matriz de Competências (15%), Mentorias 1-on-1 e Comportamento (10%), e Avaliação Final (5%). O resultado é automaticamente convertido para a escala acadêmica angolana de 0 a 20 valores (com menções de Excelente, Muito Bom, Bom e Suficiente).'
            ],
            [
                'question' => 'O certificado ou declaração de conclusão possui validade pública?',
                'answer' => 'Sim. Cada declaração e certificado emitido pela Asoftmedia conta com um código único e uma assinatura digital criptográfica (hash SHA-256) impressa com QR Code. Qualquer recrutador, universidade ou instituição pública pode apontar o smartphone ou aceder a /validar/{hash} para confirmar a autenticidade imediata e inalterável do documento.'
            ],
            [
                'question' => 'Qual é o compromisso do programa com a proteção de dados (Lei 22/11 de Angola)?',
                'answer' => 'A plataforma cumpre integralmente a Lei nº 22/11 de Proteção de Dados Pessoais da República de Angola. Os estudantes têm visibilidade total sobre os seus dados cadastrais, consentimento explícito registrado na base de dados, trilha de auditoria para cada ação de administradores e canal direto no perfil para exercer os seus direitos de retificação ou eliminação.'
            ],
            [
                'question' => 'Qual a duração típica e os horários previstos para o estágio?',
                'answer' => 'O programa de estágio tem a duração padrão de 3 meses (ajustado sempre até a sexta-feira de encerramento do trimestre), com carga horária usual de 4 horas diárias (totalizando 300 horas práticas). Os horários e dias da semana são customizados por estagiário em conformidade com o calendário escolar da instituição de proveniência.'
            ]
        ];

        return $this->render('public.landing', [
            'title' => 'Asoftmedia • Programa de Estágio & Formação de Engenharia de Software',
            'isLoggedIn' => $isLoggedIn,
            'user' => $user,
            'homeRoute' => $homeRoute,
            'stats' => [
                'total_impacted' => $totalInternsImpacted,
                'active_now' => $activeInternsCount,
                'completed' => $completedInternsCount,
                'institutions' => $partnerInstitutionsDisplay,
                'hours_practice' => $totalHoursDisplay,
                'projects' => $projectsDelivered,
                'retention_rate' => $retentionRate,
            ],
            'faqs' => $faqs
        ], 'public_landing');
    }
}
