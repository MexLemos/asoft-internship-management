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

        /* Navbar Blur */
        .landing-nav {
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            background-color: rgba(10, 17, 40, 0.92);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        /* Hero Dark Section */
        .hero-section {
            background: radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.22) 0%, transparent 50%),
                        radial-gradient(circle at 10% 70%, rgba(6, 182, 212, 0.15) 0%, transparent 40%),
                        linear-gradient(180deg, var(--landing-darker) 0%, var(--landing-dark) 100%);
            color: #ffffff;
            position: relative;
            padding-top: 130px;
            padding-bottom: 80px;
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(8px);
            color: #93c5fd;
            font-size: 0.85rem;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        /* Metric Counter Cards */
        .metric-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 1.75rem 1.5rem;
            transition: transform 0.25s ease, border-color 0.25s ease;
        }
        .metric-card:hover {
            transform: translateY(-4px);
            border-color: rgba(96, 165, 250, 0.4);
            background: rgba(255, 255, 255, 0.07);
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
            <a class="navbar-brand d-flex align-items-center gap-2 text-white fw-bold fs-4" href="/">
                <img src="<?= \App\Helpers\asset('images/logo.png') ?>" alt="Asoftmedia" style="width: 34px; height: 34px; object-fit: contain;">
                <span>ASOFTMEDIA</span>
            </a>
            
            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <i class="bi bi-list fs-2 text-white"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-3 text-center">
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-2 fw-medium hover-text-white" href="#tracks">Trilhas de Ingresso</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-2 fw-medium hover-text-white" href="#pillars">Pilares do Programa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-2 fw-medium hover-text-white" href="#metrics">Métricas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50 px-2 fw-medium hover-text-white" href="#faq">Perguntas Frequentes</a>
                    </li>
                </ul>
                
                <div class="d-flex flex-column flex-lg-row align-items-center gap-2 pt-2 pt-lg-0">
                    <a href="/attendance/scan" class="btn btn-outline-light btn-sm px-3 rounded-pill" title="Ler código QR do terminal da sede">
                        <i class="bi bi-qr-code me-1"></i> Bater Ponto
                    </a>

                    <?php if ($isLoggedIn): ?>
                        <a href="<?= $homeRoute ?>" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold">
                            <i class="bi bi-speedometer2 me-1"></i> Aceder ao Painel
                        </a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold btn-glow">
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
