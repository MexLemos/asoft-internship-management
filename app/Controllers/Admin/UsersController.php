<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AuditLog;
use App\Models\User;

class UsersController extends Controller
{
    public function index(Request $request): Response
    {
        $employees = User::getEmployees();
        $query = trim((string)$request->input('q', ''));
        $roleFilter = trim((string)$request->input('role', ''));
        $statusFilter = trim((string)$request->input('status', ''));

        if ($query !== '' || $roleFilter !== '' || $statusFilter !== '') {
            $employees = array_filter($employees, function ($emp) use ($query, $roleFilter, $statusFilter) {
                $matchesQuery = true;
                if ($query !== '') {
                    $q = mb_strtolower($query);
                    $matchesQuery = str_contains(mb_strtolower($emp['name']), $q)
                        || str_contains(mb_strtolower($emp['email']), $q)
                        || str_contains(mb_strtolower($emp['username']), $q);
                }

                $matchesRole = true;
                if ($roleFilter !== '') {
                    $matchesRole = str_contains($emp['roles_slugs'] ?? '', $roleFilter);
                }

                $matchesStatus = true;
                if ($statusFilter !== '') {
                    $matchesStatus = ($emp['status'] === $statusFilter);
                }

                return $matchesQuery && $matchesRole && $matchesStatus;
            });
        }

        return $this->render('admin.users.index', [
            'title' => 'Gestão de Funcionários - Asoftmedia',
            'employees' => $employees,
            'query' => $query,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        $roles = User::getAssignableRoles();
        return $this->render('admin.users.create', [
            'title' => 'Cadastrar Novo Funcionário - Asoftmedia',
            'roles' => $roles,
        ], 'admin');
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $errors = $this->validate($data, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'username' => 'required|min:3',
            'password' => 'required|min:6',
            'role_id' => 'required',
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect('/admin/users/create');
        }

        // Check if email or username already exists
        if (User::findByEmailOrUsername($data['email']) || User::findByEmailOrUsername($data['username'])) {
            Session::flash('error', 'Já existe um utilizador registado com este email ou nome de utilizador.');
            return $this->redirect('/admin/users/create');
        }

        try {
            $id = User::createEmployee($data);
            AuditLog::log('user_create', 'users', $id, null, [
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => $data['role_id']
            ], 'success');

            Session::flash('success', "Funcionário {$data['name']} cadastrado com sucesso!");
            return $this->redirect('/admin/users');
        } catch (\Throwable $e) {
            Session::flash('error', 'Erro ao cadastrar funcionário: ' . $e->getMessage());
            return $this->redirect('/admin/users/create');
        }
    }

    public function edit(Request $request, string $id = ''): Response
    {
        $targetId = (int)($id !== '' ? $id : $request->input('id', 0));
        $user = User::findById($targetId);

        if (!$user) {
            Session::flash('error', 'Funcionário não encontrado.');
            return $this->redirect('/admin/users');
        }

        $roles = User::getAssignableRoles();
        return $this->render('admin.users.edit', [
            'title' => 'Editar Funcionário - Asoftmedia',
            'user' => $user,
            'roles' => $roles,
        ], 'admin');
    }

    public function update(Request $request, string $id = ''): Response
    {
        $targetId = (int)($id !== '' ? $id : $request->input('id', 0));
        $data = $request->all();

        $errors = $this->validate($data, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'username' => 'required|min:3',
            'role_id' => 'required',
        ]);

        if (!empty($errors)) {
            Session::flash('error', implode(' ', $errors));
            return $this->redirect("/admin/users/{$targetId}/edit");
        }

        try {
            User::updateEmployee($targetId, $data);
            AuditLog::log('user_update', 'users', $targetId, null, [
                'name' => $data['name'],
                'email' => $data['email']
            ], 'success');

            Session::flash('success', 'Dados do funcionário atualizados com sucesso!');
            return $this->redirect('/admin/users');
        } catch (\Throwable $e) {
            Session::flash('error', 'Erro ao atualizar funcionário: ' . $e->getMessage());
            return $this->redirect("/admin/users/{$targetId}/edit");
        }
    }

    public function toggleStatus(Request $request, string $id = ''): Response
    {
        $targetId = (int)($id !== '' ? $id : $request->input('id', 0));
        $currentUser = Session::get('user');

        if ($currentUser && (int)$currentUser['id'] === $targetId) {
            Session::flash('error', 'Não pode alterar o estado da sua própria conta.');
            return $this->redirect('/admin/users');
        }

        $newStatus = User::toggleStatus($targetId);
        AuditLog::log('user_toggle_status', 'users', $targetId, null, ['new_status' => $newStatus], 'success');

        $statusLabel = ($newStatus === 'active') ? 'ativada' : 'bloqueada';
        Session::flash('info', "A conta do funcionário foi {$statusLabel}.");
        return $this->redirect('/admin/users');
    }

    public function delete(Request $request, string $id = ''): Response
    {
        $targetId = (int)($id !== '' ? $id : $request->input('id', 0));
        $currentUser = Session::get('user');

        if ($currentUser && (int)$currentUser['id'] === $targetId) {
            Session::flash('error', 'Não pode eliminar a sua própria conta.');
            return $this->redirect('/admin/users');
        }

        User::deleteEmployee($targetId);
        AuditLog::log('user_delete', 'users', $targetId, null, null, 'success');

        Session::flash('success', 'Funcionário removido com sucesso.');
        return $this->redirect('/admin/users');
    }
}
