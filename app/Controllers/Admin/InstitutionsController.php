<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\Institution;

class InstitutionsController extends Controller
{
    public function index(Request $request): Response
    {
        $institutions = Institution::all();
        return $this->render('admin.institutions.index', [
            'title' => 'Instituições de Ensino Parceiras - Asoftmedia',
            'institutions' => $institutions
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.institutions.create', [
            'title' => 'Cadastrar Nova Instituição - Asoftmedia'
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $errors = $this->validate($data, [
            'name' => 'required|min:3',
            'type' => 'required',
            'email' => 'required|email'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect('/admin/institutions/create');
        }

        $id = Institution::create($data);
        AuditLog::log('institution_create', 'institutions', $id, null, ['name' => $data['name']], 'success');

        Session::flash('success', 'Instituição parceira cadastrada com sucesso! Foi gerada automaticamente a conta institucional com palavra-passe padrão.');
        return $this->redirect('/admin/institutions');
    }

    public function syncUsers(Request $request): Response
    {
        $created = Institution::syncMissingInstitutionUsers();
        $count = count($created);
        if ($count > 0) {
            AuditLog::log('institutions_sync_users', 'institutions', null, null, ['total_created' => $count], 'success');
            Session::flash('success', "Foram criadas e associadas com sucesso {$count} conta(s) institucional(ais) com a palavra-passe padrão '123EstagioAsoft'.");
        } else {
            Session::flash('info', 'Todas as instituições registadas já possuem as respetivas contas de utilizador associadas.');
        }
        return $this->redirect('/admin/institutions');
    }

    public function show(Request $request, string $id): Response
    {
        $instId = (int)$id;
        $institution = Institution::findById($instId);

        if (!$institution) {
            Session::flash('error', 'Instituição não encontrada.');
            return $this->redirect('/admin/institutions');
        }

        $interns = Institution::getInternsByInstitution($instId);

        return $this->render('admin.institutions.show', [
            'title' => 'Detalhes da Instituição: ' . $institution['name'],
            'institution' => $institution,
            'interns' => $interns
        ], 'admin');
    }

    public function edit(Request $request, string $id): Response
    {
        $instId = (int)$id;
        $institution = Institution::findById($instId);

        if (!$institution) {
            Session::flash('error', 'Instituição não encontrada.');
            return $this->redirect('/admin/institutions');
        }

        return $this->render('admin.institutions.edit', [
            'title' => 'Editar Instituição: ' . $institution['name'],
            'institution' => $institution
        ], 'admin');
    }

    public function update(Request $request, string $id): Response
    {
        $instId = (int)$id;
        $institution = Institution::findById($instId);

        if (!$institution) {
            Session::flash('error', 'Instituição não encontrada.');
            return $this->redirect('/admin/institutions');
        }

        $data = $request->all();
        $errors = $this->validate($data, [
            'name' => 'required|min:3',
            'type' => 'required',
            'email' => 'required|email'
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect("/admin/institutions/{$instId}/edit");
        }

        Institution::update($instId, $data);
        AuditLog::log('institution_update', 'institutions', $instId, null, ['name' => $data['name']], 'success');

        Session::flash('success', 'Dados da instituição atualizados com sucesso!');
        return $this->redirect('/admin/institutions');
    }

    public function toggleStatus(Request $request, string $id): Response
    {
        $instId = (int)$id;
        $institution = Institution::findById($instId);

        if (!$institution) {
            Session::flash('error', 'Instituição não encontrada.');
            return $this->redirect('/admin/institutions');
        }

        $newStatus = Institution::toggleStatus($instId);
        $statusLabel = ($newStatus === 'active') ? 'ativada' : 'desativada';
        AuditLog::log('institution_toggle_status', 'institutions', $instId, null, ['new_status' => $newStatus], 'success');

        Session::flash('success', "A instituição '{$institution['name']}' foi {$statusLabel} com sucesso.");
        return $this->redirect('/admin/institutions');
    }
}
