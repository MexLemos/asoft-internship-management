<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Certificate;

class CertificateValidationController extends Controller
{
    public function validate(Request $request, string $hash): Response
    {
        $cert = Certificate::findByValidationHash($hash);

        if ($cert) {
            Certificate::logValidation((int)$cert['id'], $request->ip(), $request->userAgent());
        }

        return $this->render('public.certificate_validation', [
            'title' => 'Validação de Declaração de Estágio - Asoftmedia',
            'cert' => $cert,
            'searchedHash' => $hash
        ], 'public');
    }
}
