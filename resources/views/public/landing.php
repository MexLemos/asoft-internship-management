<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5 mb-5">
            <div class="col-lg-6">
                <!-- Tag / Code Badge -->
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary-subtle text-primary border border-primary-subtle small fw-semibold mb-3">
                    <i class="bi bi-code-slash"></i>
                    <span>Programa Prático de Engenharia de Software</span>
                </div>
                
                <h1 class="hero-title mb-3">
                    Conheça o Programa de Estágio da <span class="brand-accent">Asoftmedia</span>
                </h1>

                <p class="lead text-secondary mb-4 pe-lg-3" style="font-size: 1.15rem; line-height: 1.65;">
                    Criado para preparar estudantes médios, universitários e candidatos singulares para os desafios reais da tecnologia em Angola, com código prático no GitHub, mentorias 1-on-1 e certificação digital com QR Code.
                </p>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= $homeRoute ?>" class="btn btn-primary btn-lg px-4 py-3 rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-speedometer2 me-2"></i> Continuar para o Meu Painel
                        </a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary btn-lg px-4 py-3 rounded-pill fw-bold shadow-sm" style="background-color: #2563eb; border-color: #2563eb;">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Aceder ao Portal <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    <?php endif; ?>

                    <a href="/validar" class="btn btn-outline-secondary btn-lg px-4 py-3 rounded-pill fw-semibold">
                        <i class="bi bi-patch-check me-2 text-primary"></i> Validar Certificado
                    </a>
                </div>

                <!-- Simple Badges -->
                <div class="d-flex flex-wrap align-items-center gap-3 text-secondary small pt-2">
                    <span class="d-flex align-items-center gap-1">
                        <i class="bi bi-check-circle-fill text-primary"></i> Singulares & Escolas
                    </span>
                    <span class="text-muted">•</span>
                    <span class="d-flex align-items-center gap-1">
                        <i class="bi bi-check-circle-fill text-success"></i> Terminal de Ponto por QR
                    </span>
                    <span class="text-muted">•</span>
                    <span class="d-flex align-items-center gap-1">
                        <i class="bi bi-check-circle-fill text-info"></i> Mentorias 1-on-1
                    </span>
                </div>
            </div>

            <!-- Hero Image with Organic Contour & Floating Accent Badges -->
            <div class="col-lg-6 text-center">
                <div class="hero-frame-wrapper">
                    <div class="hero-contour-mask">
                        <img src="<?= \App\Helpers\asset('images/hero-interns.png') ?>" alt="Estagiários no Laboratório de Tecnologia Asoftmedia" loading="eager">
                    </div>

                    <!-- Floating Badges inspired by Curso em Vídeo reference -->
                    <div class="floating-pill floating-pill-1">
                        <span>🚀</span> Prática Real & Git
                    </div>
                    <div class="floating-pill floating-pill-2">
                        <span>✨</span> Mentorias 1-on-1
                    </div>
                    <div class="floating-pill floating-pill-3">
                        <span>🏆</span> +100 Estagiários
                    </div>
                    <div class="floating-pill floating-pill-4">
                        <span>📍</span> Luanda Tech HQ
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Counter Grid (Light Modern Cards) -->
        <div id="metrics" class="row g-3 pt-3">
            <div class="col-6 col-lg-3">
                <div class="metric-card-light">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-people-fill text-primary fs-5"></i>
                        <span class="text-secondary small fw-medium">Estagiários no Programa</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-1 font-mono" style="color: #2563eb;">
                        +<?= number_format($stats['total_impacted']) ?>
                    </h2>
                    <span class="text-muted small" style="font-size: 0.8rem;">Formados & em Formação</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-card-light">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-clock-history text-success fs-5"></i>
                        <span class="text-secondary small fw-medium">Horas Práticas Auditadas</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-1 font-mono" style="color: #059669;">
                        +<?= number_format($stats['hours_practice']) ?>h
                    </h2>
                    <span class="text-muted small" style="font-size: 0.8rem;">Em Projetos de Produção</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-card-light">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-buildings-fill text-info fs-5"></i>
                        <span class="text-secondary small fw-medium">Instituições Conectadas</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-1 font-mono" style="color: #0284c7;">
                        <?= number_format($stats['institutions']) ?>
                    </h2>
                    <span class="text-muted small" style="font-size: 0.8rem;">Médios & Universidades</span>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="metric-card-light">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-award-fill text-warning fs-5"></i>
                        <span class="text-secondary small fw-medium">Taxa de Aproveitamento</span>
                    </div>
                    <h2 class="display-6 fw-bold mb-1 font-mono" style="color: #d97706;">
                        <?= $stats['retention_rate'] ?>%
                    </h2>
                    <span class="text-muted small" style="font-size: 0.8rem;">Escala Oficial Angolana (0-20)</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TRACKS SECTION: SINGULAR VS ESCOLAS PÚBLICAS VS UNIVERSIDADES PRIVADAS -->
<section id="tracks" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-xl mx-auto mb-5">
            <div class="section-eyebrow">Trilhas Inclusivas de Ingresso</div>
            <h2 class="section-title">Aberto a Singulares, Escolas Públicas e Faculdades</h2>
            <p class="text-muted lead fs-6">
                A Asoftmedia acredita no talento e na meritocracia. Estruturamos caminhos específicos para atender tanto estudantes vinculados a instituições de ensino quanto jovens autodidatas.
            </p>
        </div>

        <div class="row g-4 align-items-stretch">
            <!-- Track 1: Singular (Candidatos Particulares) -->
            <div class="col-lg-4">
                <div class="track-card featured">
                    <div class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill align-self-start mb-3">
                        <i class="bi bi-person-fill me-1"></i> Candidatura Singular
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Estudantes & Autodidatas</h4>
                    <p class="text-muted small mb-4">
                        Para quem busca oportunidade extracurricular, transição de carreira ou estágio prático sem necessidade de convênio com uma escola.
                    </p>

                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4 flex-grow-1">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-primary fs-5 mt-n1"></i>
                            <span><strong>Inscrição Independente:</strong> Não exige vínculo institucional nem protocolo formal prévio.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-primary fs-5 mt-n1"></i>
                            <span><strong>Portfólio com Validação Pública:</strong> Todo código entregue é documentado com link público com QR Code.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-primary fs-5 mt-n1"></i>
                            <span><strong>Mentorias 1-on-1:</strong> Acompanhamento direto com programadores seniores da empresa.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-primary fs-5 mt-n1"></i>
                            <span><strong>Certificado Reconhecido:</strong> Declaração de conclusão com hash digital para comprovação no LinkedIn e CV.</span>
                        </li>
                    </ul>

                    <div class="pt-3 border-top">
                        <a href="/login" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
                            Ingressar como Singular
                        </a>
                    </div>
                </div>
            </div>

            <!-- Track 2: Escolas e Institutos Médios Públicos -->
            <div class="col-lg-4">
                <div class="track-card">
                    <div class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill align-self-start mb-3">
                        <i class="bi bi-building me-1"></i> Escolas Públicas
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Institutos Médios Técnicos</h4>
                    <p class="text-muted small mb-4">
                        Desenhado para estudantes da 13ª classe (ex: ITEL, IPIL, IMEL) que necessitam de estágio curricular obrigatório para obtenção de diploma.
                    </p>

                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4 flex-grow-1">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-success fs-5 mt-n1"></i>
                            <span><strong>Cumprimento Rigoroso de Horas:</strong> Controlo de 300h com ajuste automático até à data de término escolar.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-success fs-5 mt-n1"></i>
                            <span><strong>Portal Observador para Coordenadores:</strong> A escola acede ao sistema e fiscaliza a presença sem burocracia.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-success fs-5 mt-n1"></i>
                            <span><strong>Alerta Imediato de Faltas/Atrasos:</strong> O orientador escolar vê em tempo real o histórico de pontualidade.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-success fs-5 mt-n1"></i>
                            <span><strong>Pauta de Avaliação Oficial:</strong> Emissão de nota na escala de 0 a 20 valores pronta para lançamento escolar.</span>
                        </li>
                    </ul>

                    <div class="pt-3 border-top">
                        <a href="/login" class="btn btn-outline-success w-100 py-2 rounded-pill fw-bold">
                            Área das Escolas Parceiras
                        </a>
                    </div>
                </div>
            </div>

            <!-- Track 3: Universidades e Faculdades Privadas -->
            <div class="col-lg-4">
                <div class="track-card">
                    <div class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-2 rounded-pill align-self-start mb-3">
                        <i class="bi bi-mortarboard-fill me-1"></i> Ensino Superior
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Universidades & Faculdades</h4>
                    <p class="text-muted small mb-4">
                        Para estudantes universitários (ISUTIC, UCAN, UAN, ISPTEC, etc.) em fase de conclusão de licenciatura ou estágio profissional.
                    </p>

                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary mb-4 flex-grow-1">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-info fs-5 mt-n1"></i>
                            <span><strong>Matriz de Competências Técnicas:</strong> Níveis de 1 a 5 em Backend, Frontend, Banco de Dados, DevOps e QA.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-info fs-5 mt-n1"></i>
                            <span><strong>Integração com GitHub:</strong> Pull requests reais avaliados com score automatizado no sistema.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-info fs-5 mt-n1"></i>
                            <span><strong>Aproveitamento Curricular:</strong> Emissão de relatório analítico detalhado para validação na secretaria acadêmica.</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check2 text-info fs-5 mt-n1"></i>
                            <span><strong>Transição para Emprego:</strong> Os melhores desempenhos são encaminhados para contratação na Asoftmedia.</span>
                        </li>
                    </ul>

                    <div class="pt-3 border-top">
                        <a href="/login" class="btn btn-outline-primary w-100 py-2 rounded-pill fw-bold">
                            Aceder Portal Académico
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PILLARS / HOW THE SYSTEM WORKS -->
<section id="pillars" class="py-5 bg-light border-top border-bottom">
    <div class="container py-4">
        <div class="text-center max-w-xl mx-auto mb-5">
            <div class="section-eyebrow">Arquitetura de Qualidade</div>
            <h2 class="section-title">Os 4 Pilares da Metodologia Asoftmedia</h2>
            <p class="text-muted lead fs-6">
                Construímos um ambiente de trabalho que espelha os padrões das melhores empresas de tecnologia internacionais.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="icon-bubble bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Presença por QR & Geofence</h5>
                    <p class="text-muted small mb-0">
                        O terminal de recepção gera tokens dinâmicos renovados a cada 15 segundos. O ponto é batido com a câmara do telemóvel, eliminando fraudes.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="icon-bubble bg-success bg-opacity-10 text-success">
                        <i class="bi bi-chat-heart-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Mentorias 1-on-1 Universais</h5>
                    <p class="text-muted small mb-0">
                        Sessões de alinhamento com qualquer supervisor da empresa. As notas atribuídas de 1 a 5 acumulam diretamente para a média comportamental final.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="icon-bubble bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-github"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Desafios Práticos com GitHub</h5>
                    <p class="text-muted small mb-0">
                        Tarefas resolvidas via Pull Request. O sistema escuta webhooks automáticos do GitHub para rastrear commits, revisões de código e merges.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="icon-bubble bg-info bg-opacity-10 text-info">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Certificação SHA-256</h5>
                    <p class="text-muted small mb-0">
                        Conclusão com emissão de certificado digital e declaração pública. Qualquer entidade valida a autenticidade apontando a câmara para o QR.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STEP BY STEP INTERNSHIP JOURNEY -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="section-eyebrow">Ciclo de Evolução</div>
                <h2 class="section-title mb-3">Da Integração ao Mercado em 4 Etapas Claras</h2>
                <p class="text-muted mb-4">
                    Cada estagiário cumpre uma jornada estruturada de 3 meses, com marcos verificáveis para assegurar a evolução das suas competências de engenharia de software.
                </p>

                <div class="p-4 bg-light rounded-4 border">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-shield-fill-check fs-2 text-primary"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Conformidade Legal & Ética</h6>
                            <span class="text-muted small">Alinhado com a Lei Geral do Trabalho e Proteção de Dados Lei 22/11.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3 p-3 bg-light rounded-3 border">
                        <div class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center p-0 flex-shrink-0" style="width: 36px; height: 36px;">1</div>
                        <div>
                            <h6 class="fw-bold mb-1">Acolhimento & Configuração do Regime</h6>
                            <p class="text-muted small mb-0">Criação da conta, definição do horário semanal, escolha do regime (presencial ou híbrido) e vinculação de dispositivo confiável.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 p-3 bg-light rounded-3 border">
                        <div class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center p-0 flex-shrink-0" style="width: 36px; height: 36px;">2</div>
                        <div>
                            <h6 class="fw-bold mb-1">Sprints de Desenvolvimento & Academia de Estudos</h6>
                            <p class="text-muted small mb-0">Resolução de tarefas em projetos reais da Asoftmedia, estudo de módulos teóricos e esclarecimento de dúvidas técnicas com os supervisores.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 p-3 bg-light rounded-3 border">
                        <div class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center p-0 flex-shrink-0" style="width: 36px; height: 36px;">3</div>
                        <div>
                            <h6 class="fw-bold mb-1">Mentorias 1-on-1 & Matriz de Competências</h6>
                            <p class="text-muted small mb-0">Avaliações contínuas de postura, trabalho em equipa e rigor técnico. As notas das mentorias consolidam-se na nota final ponderada.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 p-3 bg-light rounded-3 border">
                        <div class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center p-0 flex-shrink-0" style="width: 36px; height: 36px;">4</div>
                        <div>
                            <h6 class="fw-bold mb-1">Encerramento, Portfólio Digital & Certificação</h6>
                            <p class="text-muted small mb-0">Atingimento das 300 horas práticas, cálculo da menção oficial na escala de 0 a 20 e geração da declaração oficial com hash criptográfico.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ SECTION -->
<section id="faq" class="py-5 bg-light border-top">
    <div class="container py-4">
        <div class="text-center max-w-xl mx-auto mb-5">
            <div class="section-eyebrow">Dúvidas Frequentes</div>
            <h2 class="section-title">Perguntas Respondidas com Total Clareza</h2>
            <p class="text-muted lead fs-6">
                Reunimos as respostas para as principais dúvidas de estudantes singulares, pais e coordenadores escolares.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="accordion accordion-landing" id="landingFaqAccordion">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="accordion-item shadow-sm">
                            <h2 class="accordion-header" id="faqHeading<?= $index ?>">
                                <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?= $index ?>">
                                    <i class="bi bi-question-circle text-primary me-2"></i> <?= \App\Helpers\e($faq['question']) ?>
                                </button>
                            </h2>
                            <div id="faqCollapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#landingFaqAccordion">
                                <div class="accordion-body">
                                    <?= \App\Helpers\e($faq['answer']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted small mb-2">Ainda tem alguma questão não listada aqui?</p>
                    <a href="/login" class="btn btn-outline-secondary btn-sm px-4 rounded-pill">
                        <i class="bi bi-envelope me-1"></i> Falar com a Equipa de Coordenação
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CALL TO ACTION SECTION -->
<section class="py-5 bg-dark text-white text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, #0a1128 0%, #1c2541 100%);">
    <div class="container py-5 position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-3">Junte-se à Comunidade Asoftmedia</span>
                <h2 class="display-5 fw-bold mb-3">Pronto para Começar a Sua Jornada Prática de Engenharia?</h2>
                <p class="lead text-white-50 mb-4">
                    Quer seja um candidato singular em busca de desafios reais ou uma instituição interessada em formalizar parcerias de estágio para os seus alunos, estamos de portas abertas.
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= $homeRoute ?>" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold btn-glow">
                            <i class="bi bi-speedometer2 me-2"></i> Abrir Meu Painel
                        </a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold btn-glow">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Entrar no Portal
                        </a>
                    <?php endif; ?>

                    <a href="/attendance/scan" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill fw-semibold">
                        <i class="bi bi-qr-code me-2"></i> Terminal de Ponto
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
