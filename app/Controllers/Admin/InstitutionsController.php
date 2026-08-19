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

        Session::flash('success', 'Instituição parceira cadastrada com sucesso!');
        return $this->redirect('/admin/institutions');
    }
}
