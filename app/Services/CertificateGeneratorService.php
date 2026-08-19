<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Intern;
use App\Models\SystemSetting;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Options;

class CertificateGeneratorService
{
    public function checkEligibility(int $internId): array
    {
        $intern = Intern::findById($internId);
        if (!$intern) {
            return ['eligible' => false, 'reasons' => ['Estagiário não encontrado.']];
        }

        $minAttendance = (int)SystemSetting::get('min_attendance_percentage', 80);
        $minGrade = (int)SystemSetting::get('min_passing_grade', 60);

        $attStats = Attendance::getStats($internId);
        $totalDays = $attStats['present_count'] + $attStats['absent_count'];
        $attPercentage = ($totalDays > 0) ? ($attStats['present_count'] / $totalDays) * 100 : 100;

        $reasons = [];
        if ($attPercentage < $minAttendance) {
            $reasons[] = "Presença de " . round($attPercentage, 1) . "% abaixo do mínimo exigido de {$minAttendance}%.";
        }

        if ((float)$intern['overall_score'] < $minGrade) {
            $reasons[] = "Média geral de " . round((float)$intern['overall_score'], 1) . " valores abaixo da nota mínima ({$minGrade} valores).";
        }

        return [
            'eligible' => empty($reasons),
            'reasons' => $reasons,
            'attendance_percentage' => round($attPercentage, 1),
            'overall_score' => (float)$intern['overall_score']
        ];
    }

    public function generateCertificate(int $internId, string $signatoryName = 'Direcção Geral Asoftmedia', string $signatoryRole = 'Director Geral'): array
    {
        $eligibility = $this->checkEligibility($internId);
        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'message' => 'Estagiário não cumpre os requisitos mínimos para emissão de certificado: ' . implode(' ', $eligibility['reasons'])
            ];
        }

        $intern = Intern::findById($internId);
        $totalHours = (float)($intern['total_required_hours'] ?? 300.00);
        $finalScore = (float)$intern['overall_score'];

        $cert = Certificate::issue($internId, $totalHours, $finalScore, $signatoryName, $signatoryRole);

        // Generate QR Code data URL
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/');
        $validationUrl = "{$baseUrl}/validar/{$cert['validation_hash']}";

        $qrOptions = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
        ]);
        $qrcode = (new QRCode($qrOptions))->render($validationUrl);

        return [
            'success' => true,
            'certificate' => $cert,
            'validation_url' => $validationUrl,
            'qr_code_base64' => $qrcode
        ];
    }

    public function renderPdfHtml(array $cert, array $intern, string $qrCodeBase64): string
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <title>Declaração de Estágio - <?= htmlspecialchars($intern['full_name']) ?></title>
            <style>
                @page { margin: 20mm; }
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6; }
                .cert-border { border: 4px double #0d6efd; padding: 30px; text-align: center; border-radius: 8px; }
                .logo { font-size: 28px; font-weight: 800; color: #0d6efd; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 5px; }
                .subtitle { font-size: 14px; color: #64748b; margin-bottom: 30px; text-transform: uppercase; letter-spacing: 1px; }
                .title { font-size: 26px; font-weight: 700; color: #0f172a; margin-bottom: 25px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; display: inline-block; }
                .text { font-size: 15px; text-align: justify; margin-bottom: 25px; line-height: 1.8; }
                .highlight { font-weight: bold; color: #0f172a; }
                .footer-table { width: 100%; margin-top: 40px; }
                .sig-box { text-align: center; width: 60%; }
                .sig-line { border-top: 1px solid #334155; width: 220px; margin: 0 auto 5px auto; }
                .qr-box { text-align: right; width: 40%; }
                .qr-img { width: 110px; height: 110px; }
                .validation-text { font-size: 10px; color: #64748b; margin-top: 5px; }
                .cert-code { font-family: monospace; font-size: 12px; color: #475569; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="cert-border">
                <div class="logo">ASOFTMEDIA</div>
                <div class="subtitle">Tecnologia & Desenvolvimento de Software</div>

                <div class="title">Declaração de Estágio Curricular</div>

                <div class="text">
                    Declaramos para os devidos efeitos que <span class="highlight"><?= htmlspecialchars($intern['full_name']) ?></span>, 
                    portador(a) do Bilhete de Identidade nº <span class="highlight"><?= htmlspecialchars($intern['bi_number']) ?></span>, 
                    estudante da instituição de ensino <span class="highlight"><?= htmlspecialchars($intern['institution_name']) ?></span> 
                    no curso de <span class="highlight"><?= htmlspecialchars($intern['course']) ?></span>, 
                    concluiu com aproveitamento o estágio curricular na área de <span class="highlight"><?= htmlspecialchars($intern['internship_area']) ?></span> 
                    junto da empresa <span class="highlight">Asoftmedia</span>, 
                    no período compreendido entre <span class="highlight"><?= date('d/m/Y', strtotime($intern['start_date'])) ?></span> 
                    e <span class="highlight"><?= date('d/m/Y', strtotime($intern['end_date'])) ?></span>, 
                    com uma carga horária total de <span class="highlight"><?= number_format((float)$cert['total_hours_completed'], 0) ?> horas</span> 
                    e classificação final de <span class="highlight"><?= number_format((float)$cert['final_score'], 1) ?> valores</span> (Excelente Aproveitamento).
                </div>

                <table class="footer-table">
                    <tr>
                        <td class="sig-box">
                            <br><br>
                            <div class="sig-line"></div>
                            <strong><?= htmlspecialchars($cert['signatory_name']) ?></strong><br>
                            <span style="font-size: 12px; color: #64748b;"><?= htmlspecialchars($cert['signatory_role']) ?></span><br>
                            <span style="font-size: 11px; color: #94a3b8;">Luanda, <?= date('d/m/Y', strtotime($cert['issue_date'])) ?></span>
                        </td>
                        <td class="qr-box">
                            <img src="<?= $qrCodeBase64 ?>" class="qr-img" alt="QR Code"><br>
                            <div class="validation-text">
                                Verifique a autenticidade deste documento<br>
                                Cód: <strong><?= htmlspecialchars($cert['certificate_code']) ?></strong>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="cert-code">
                    Código de Validação Digital: <?= htmlspecialchars($cert['validation_hash']) ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
