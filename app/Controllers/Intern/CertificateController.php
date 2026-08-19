<?php

declare(strict_types=1);

namespace App\Controllers\Intern;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Certificate;
use App\Models\Intern;
use App\Services\CertificateGeneratorService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class CertificateController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Session::get('user');
        $intern = Intern::findByUserId((int)$user['id']);

        if (!$intern) {
            return $this->redirect('/login');
        }

        $internId = (int)$intern['id'];
        $cert = Certificate::findByInternId($internId);
        $certService = new CertificateGeneratorService();
        $eligibility = $certService->checkEligibility($internId);

        $qrCodeBase64 = null;
        $validationUrl = null;

        if ($cert) {
            $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
            $validationUrl = "{$baseUrl}/validar/{$cert['validation_hash']}";

            $qrOptions = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L,
                'scale' => 5,
            ]);
            $qrCodeBase64 = (new QRCode($qrOptions))->render($validationUrl);
        }

        return $this->render('intern.certificate.index', [
            'title' => 'Declaração e Certificado de Estágio - Asoftmedia',
            'intern' => $intern,
            'cert' => $cert,
            'eligibility' => $eligibility,
            'qrCodeBase64' => $qrCodeBase64,
            'validationUrl' => $validationUrl
        ], 'intern');
    }
}
