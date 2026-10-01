<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Intern;

class InternLifecycleGuardMiddleware
{
    /**
     * Rotas permitidas para o modo Alumni (Estagiários com estágio 'completed')
     */
    private const ALUMNI_ALLOWED_PREFIXES = [
        '/intern/dashboard',
        '/intern/portfolio',
        '/intern/certificate',
        '/profile',
        '/notifications',
        '/politica-privacidade'
    ];

    public function handle(Request $request): ?Response
    {
        $user = Session::get('user');
        if (!$user) {
            return (new Response())->redirect('/login');
        }

        // Super administradores não sofrem restrições de estagiário
        $userRoles = $user['roles'] ?? [];
        if (in_array('super_admin', $userRoles, true)) {
            return null;
        }

        $intern = Intern::findByUserId((int)$user['id']);
        if (!$intern) {
            // Se o perfil for estagiário mas não possuir registo na tabela interns
            Session::destroy();
            Session::flash('error', 'Registo de estagiário não localizado. Contacte a administração.');
            return (new Response())->redirect('/login');
        }

        $status = $intern['status'] ?? 'pending';
        $path = $request->getPath();

        switch ($status) {
            case 'active':
                // Estágio ativo regular - acesso pleno
                return null;

            case 'completed':
                // Modo Alumni (Apenas consulta de histórico, certificado e portfólio)
                $isAllowed = false;
                foreach (self::ALUMNI_ALLOWED_PREFIXES as $prefix) {
                    if (str_starts_with($path, $prefix)) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (!$isAllowed) {
                    if ($request->isAjax() || str_starts_with($path, '/api/')) {
                        return (new Response())->json([
                            'success' => false,
                            'message' => 'Estágio concluído com sucesso. Acesso Alumni limitado ao portfólio e certificado.'
                        ], 403);
                    }

                    Session::flash('info', 'Parabéns! O seu estágio foi concluído com sucesso. O seu acesso está configurado em Modo Alumni (consulta de Certificado e Portfólio).');
                    return (new Response())->redirect('/intern/dashboard');
                }
                return null;

            case 'awaiting_completion':
                // Período concluído, aguarda homologação do supervisor/admin. Não pode marcar presenças.
                if (str_starts_with($path, '/intern/attendance')) {
                    if ($request->isAjax()) {
                        return (new Response())->json([
                            'success' => false,
                            'message' => 'O seu estágio atingiu a data final e aguarda homologação da supervisão. A marcação de ponto está encerrada.'
                        ], 403);
                    }

                    Session::flash('warning', 'O seu estágio terminou a carga horária prevista e aguarda homologação da supervisão. Não é necessário registar novas presenças.');
                    return (new Response())->redirect('/intern/dashboard');
                }
                return null;

            case 'suspended':
                // Suspensão preventiva / disciplinar
                AuditLog::log('suspended_intern_access_attempt', 'intern', (int)$intern['id'], null, ['path' => $path], 'suspicious');

                if ($request->isAjax() || str_starts_with($path, '/api/')) {
                    return (new Response())->json([
                        'success' => false,
                        'message' => 'O seu estágio encontra-se suspenso. Todas as atividades estão temporariamente congeladas.'
                    ], 403);
                }

                if ($path !== '/intern/dashboard') {
                    Session::flash('warning', 'O seu estágio encontra-se suspenso. Para mais informações, contacte o seu supervisor ou a coordenação.');
                    return (new Response())->redirect('/intern/dashboard');
                }
                return null;

            case 'pending':
                // Matrícula pendente de aprovação
                if ($path !== '/intern/dashboard') {
                    Session::flash('info', 'A sua matrícula de estágio encontra-se pendente de início. Aguarde a validação dos seus dados pela coordenação.');
                    return (new Response())->redirect('/intern/dashboard');
                }
                return null;

            case 'dropped_out':
            case 'terminated_anomalous':
            case 'cancelled':
            default:
                // Rompimento contratual ou abandono - Sessão deve ser encerrada
                AuditLog::log('revoked_intern_access_attempt', 'intern', (int)$intern['id'], null, [
                    'status' => $status,
                    'path' => $path
                ], 'suspicious');

                Session::destroy();
                Session::flash('error', 'O seu vínculo de estágio com a Asoftmedia foi encerrado (' . Intern::getStatusLabel($status) . ').');
                return (new Response())->redirect('/login');
        }
    }
}
