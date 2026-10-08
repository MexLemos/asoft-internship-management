<!DOCTYPE html>
<html lang="pt" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Asoftmedia - Programa de Estágio e Formação de Quadros em Engenharia de Software e TI em Luanda, Angola. Prática real, mentoria e certificação criptográfica.">
    <meta name="theme-color" content="#0b132b">
    
    <title><?= \App\Helpers\e($title ?? 'Asoftmedia • Programa de Estágio') ?></title>
    
    <link rel="icon" type="image/png" href="<?= \App\Helpers\asset('images/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= \App\Helpers\asset('images/logo.png') ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --landing-dark: #0a1128;
            --landing-darker: #050a18;
            --landing-navy: #1c2541;
            --landing-accent: #2563eb;
            --landing-accent-light: #60a5fa;
            --landing-teal: #06b6d4;
            --landing-surface: #ffffff;
            --landing-text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            overflow-x: hidden;
            line-height: 1.6;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Navbar Light */
        .landing-nav {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            background-color: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            box-shadow: 0 4px 20px -5px rgba(15, 23, 42, 0.05);
            transition: all 0.3s ease;
        }
        .landing-nav .navbar-brand {
            color: #0f172a !important;
        }
        .landing-nav .nav-link {
            color: #475569 !important;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        .landing-nav .nav-link:hover {
            color: var(--landing-accent) !important;
        }

        /* Hero Light Section (Clean & Friendly, inspired by Curso em Vídeo style) */
        .hero-section {
            background: radial-gradient(circle at 85% 18%, rgba(37, 99, 235, 0.08) 0%, transparent 45%),
                        radial-gradient(circle at 10% 85%, rgba(6, 182, 212, 0.06) 0%, transparent 40%),
                        linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            color: #0f172a;
            position: relative;
            padding-top: 135px;
            padding-bottom: 60px;
            overflow: hidden;
        }

        .hero-title {
            font-size: 3.25rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .hero-title .brand-accent {
            color: #2563eb;
            display: inline-block;
        }

        @media (max-width: 991px) {
            .hero-title {
                font-size: 2.35rem;
            }
        }

        /* Hero Organic Framed Image */
        .hero-frame-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
            max-width: 530px;
            margin: 0 auto;
        }

        .hero-contour-mask {
            position: relative;
            border-radius: 48px 48px 140px 48px;
            padding: 8px;
            background: linear-gradient(135deg, #60a5fa 0%, #2563eb 50%, #06b6d4 100%);
            box-shadow: 0 25px 60px -15px rgba(37, 99, 235, 0.28);
        }

        .hero-contour-mask img {
            border-radius: 42px 42px 134px 42px;
            width: 100%;
            height: 420px;
            object-fit: cover;
            object-position: center 30%;
            display: block;
            background-color: #f1f5f9;
        }

        /* Floating Accent Badges around Hero Photo */
        .floating-pill {
            position: absolute;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9999px;
            padding: 0.5rem 1rem;
            font-size: 0.82rem;
            font-weight: 700;
            color: #0f172a;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12);
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            z-index: 2;
            animation: heroFloat 4s ease-in-out infinite alternate;
        }
        .floating-pill-1 {
            top: -12px;
            right: 18px;
            border-color: #bfdbfe;
        }
        .floating-pill-2 {
            bottom: 25px;
            left: -18px;
            border-color: #bbf7d0;
            animation-delay: 1.5s;
        }
        .floating-pill-3 {
            top: 42%;
            left: -24px;
            border-color: #fef08a;
            animation-delay: 0.8s;
        }
        .floating-pill-4 {
            bottom: -14px;
            right: 32px;
            border-color: #fed7aa;
            animation-delay: 2.2s;
        }

        @keyframes heroFloat {
            0% { transform: translateY(0); }
            100% { transform: translateY(-8px); }
        }

        /* Light Metric Cards */
        .metric-card-light {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 1.5rem 1.25rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            height: 100%;
        }
        .metric-card-light:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px -6px rgba(37, 99, 235, 0.12);
            border-color: #bfdbfe;
        }

        /* Track Cards (Singular vs Public vs Private) */
        .track-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            padding: 2.25rem 2rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .track-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.1);
            border-color: #93c5fd;
        }
        .track-card.featured {
            border: 2px solid var(--landing-accent);
            background: linear-gradient(180deg, #ffffff 0%, #f0f7ff 100%);
        }

        /* Feature Cards */
        .feature-box {
            background: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 18px;
            padding: 2rem;
            transition: all 0.25s ease;
            height: 100%;
        }
        .feature-box:hover {
            border-color: #cbd5e1;
            box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.06);
            transform: translateY(-3px);
        }

        .icon-bubble {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
        }

        /* Interactive FAQ Accordion */
        .accordion-landing .accordion-item {
            border: 1px solid #e2e8f0;
            border-radius: 14px !important;
            margin-bottom: 0.85rem;
            overflow: hidden;
            background: #ffffff;
        }
        .accordion-landing .accordion-button {
            font-weight: 600;
            font-size: 1.05rem;
            color: #0f172a;
            padding: 1.25rem 1.5rem;
            background-color: #ffffff;
            box-shadow: none !important;
        }
        .accordion-landing .accordion-button:not(.collapsed) {
            color: var(--landing-accent);
            background-color: #f8fafc;
        }
        .accordion-landing .accordion-body {
            color: #475569;
            font-size: 0.96rem;
            line-height: 1.7;
            padding: 1.25rem 1.5rem;
            background-color: #ffffff;
        }

        /* Floating action buttons */
        .btn-glow {
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.45);
            transition: all 0.25s ease;
        }
        .btn-glow:hover {
            box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.6);
            transform: translateY(-2px);
        }

        /* Section Headings */
        .section-eyebrow {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            color: var(--landing-accent);
            margin-bottom: 0.5rem;
        }
        .section-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        @media (max-width: 768px) {
            .section-title {
                font-size: 1.85rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg landing-nav fixed-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-4 text-dark" href="/">
                <img src="<?= \App\Helpers\asset('images/logo.png') ?>" alt="Asoftmedia" style="width: 34px; height: 34px; object-fit: contain;">
                <span>ASOFTMEDIA</span>
            </a>
            
            <button class="navbar-toggler border-0 text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <i class="bi bi-list fs-2 text-dark"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-3 text-center">
                    <li class="nav-item">
                        <a class="nav-link px-2 fw-medium" href="#tracks">Trilhas de Ingresso</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-2 fw-medium" href="#pillars">Pilares do Programa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-2 fw-medium" href="#metrics">Métricas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-2 fw-medium" href="/validar">Validação de Certificado</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-2 fw-medium" href="#faq">Perguntas Frequentes</a>
                    </li>
                </ul>
                
                <div class="d-flex flex-column flex-lg-row align-items-center gap-2 pt-2 pt-lg-0">
                    <a href="/attendance/scan" class="btn btn-outline-secondary btn-sm px-3 rounded-pill fw-medium" title="Ler código QR do terminal da sede">
                        <i class="bi bi-qr-code me-1 text-primary"></i> Bater Ponto (QR)
                    </a>

                    <?php if ($isLoggedIn): ?>
                        <a href="<?= $homeRoute ?>" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-speedometer2 me-1"></i> Aceder ao Painel
                        </a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold shadow-sm" style="background-color: #2563eb; border-color: #2563eb;">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar no Portal
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?= $content ?>
    </main>

    <!-- Institutional Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 border-top border-secondary border-opacity-25" style="background-color: var(--landing-darker) !important;">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="<?= \App\Helpers\asset('images/logo.png') ?>" alt="Asoftmedia" style="width: 32px; height: 32px; object-fit: contain;">
                        <span class="fw-bold fs-5 text-white">ASOFTMEDIA</span>
                    </div>
                    <p class="text-white-50 small pe-lg-4 mb-3">
                        Empresa Angolana de Tecnologia & Soluções Digitais. Comprometida com a aceleração profissional de jovens engenheiros de software, estudantes médios, universitários e autodidatas.
                    </p>
                    <div class="small text-white-50">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Luanda, República de Angola
                    </div>
                </div>

                <div class="col-sm-6 col-lg-2">
                    <h6 class="fw-bold text-white text-uppercase small tracking-wider mb-3">Programa</h6>
                    <ul class="list-unstyled small text-white-50 d-flex flex-column gap-2 mb-0">
                        <li><a href="#tracks" class="text-white-50 text-decoration-none">Candidatura Singular</a></li>
                        <li><a href="#tracks" class="text-white-50 text-decoration-none">Escolas Públicas</a></li>
                        <li><a href="#tracks" class="text-white-50 text-decoration-none">Universidades Privadas</a></li>
                        <li><a href="#pillars" class="text-white-50 text-decoration-none">Terminal Biométrico</a></li>
                    </ul>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <h6 class="fw-bold text-white text-uppercase small tracking-wider mb-3">Transparência</h6>
                    <ul class="list-unstyled small text-white-50 d-flex flex-column gap-2 mb-0">
                        <li><a href="/politica-privacidade" class="text-white-50 text-decoration-none">Proteção de Dados (Lei 22/11)</a></li>
                        <li><a href="/attendance/scan" class="text-white-50 text-decoration-none">Terminal de Ponto por QR</a></li>
                        <li><a href="/login" class="text-white-50 text-decoration-none">Portal de Acesso</a></li>
                        <li><a href="#faq" class="text-white-50 text-decoration-none">FAQ de Dúvidas</a></li>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <h6 class="fw-bold text-white text-uppercase small tracking-wider mb-3">Validação Oficial</h6>
                    <p class="text-white-50 small mb-3">
                        Qualquer certificado ou declaração emitida pela Asoftmedia pode ser validado digitalmente com o código de hash criptográfico SHA-256.
                    </p>
                    <div class="d-inline-flex align-items-center gap-2 p-2 bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-3">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span class="small text-white-50">Assinatura Digital Auditável</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-top border-secondary border-opacity-25 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small text-white-50">
                <div>
                    &copy; <?= date('Y') ?> Asoftmedia Tecnologia. Todos os direitos reservados.
                </div>
                <div>
                    Conformidade com a legislação da República de Angola • Lei nº 22/11
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
