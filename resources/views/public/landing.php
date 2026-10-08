<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-badge">
                    <span class="badge bg-primary px-2 py-1 rounded-pill">🇦🇴 Angola Tech</span>
                    <span>Formação Prática de Quadros de Engenharia de Software</span>
                </div>
                
                <h1 class="display-4 fw-extrabold text-white mb-3" style="letter-spacing: -0.03em; line-height: 1.15;">
                    Onde o Talento Angolano Ganha <span style="background: linear-gradient(135deg, #60a5fa 0%, #06b6d4 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Experiência Real</span> de Desenvolvimento.
                </h1>

                <p class="lead text-white-50 mb-4 pe-lg-4" style="font-size: 1.15rem;">
                    Diga adeus aos estágios burocráticos de papel. Na Asoftmedia, cada estagiário atua em código real com Git/GitHub, participa em mentorias técnicas 1-on-1, valida presença via Terminal QR e constrói um histórico profissional auditável.
                </p>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-5">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= $homeRoute ?>" class="btn btn-primary btn-lg px-4 py-3 rounded-pill fw-bold btn-glow">
                            <i class="bi bi-speedometer2 me-2"></i> Continuar para o Meu Painel
                        </a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary btn-lg px-4 py-3 rounded-pill fw-bold btn-glow">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Aceder ao Portal do Estagiário
                        </a>
                    <?php endif; ?>

                    <a href="#tracks" class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill fw-semibold">
                        <i class="bi bi-compass me-2"></i> Ver Trilhas de Ingresso
                    </a>
                </div>

                <!-- Proof Badges -->
                <div class="d-flex flex-wrap align-items-center gap-4 text-white-50 small border-top border-secondary border-opacity-25 pt-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span>Singulares & Escolas Parceiras</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-info fs-5"></i>
                        <span>Conforme a Lei nº 22/11 de Angola</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-award-fill text-warning fs-5"></i>
                        <span>Certificação com Hash SHA-256</span>
                    </div>
                </div>
            </div>

            <!-- Terminal Hero Visual -->
            <div class="col-lg-5">
                <div class="p-4 rounded-4" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.12); backdrop-filter: blur(16px); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom border-white border-opacity-10 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-danger" style="width: 10px; height: 10px;"></div>
                            <div class="rounded-circle bg-warning" style="width: 10px; height: 10px;"></div>
                            <div class="rounded-circle bg-success" style="width: 10px; height: 10px;"></div>
                            <span class="font-mono text-white-50 ms-2 small">asoft-terminal://ponto</span>
                        </div>
                        <span class="badge bg-success-subtle text-success small font-mono">AO VIVO</span>
                    </div>

                    <div class="text-center py-3">
                        <div class="p-3 bg-white rounded-3 d-inline-block shadow mb-3">
                            <img src="<?= \App\Helpers\asset('images/logo.png') ?>" alt="Terminal QR" style="width: 100px; height: 100px; object-fit: contain;">
                        </div>
                        <h6 class="text-white fw-bold mb-1">Terminal de Presença Inteligente</h6>
                        <p class="text-white-50 small mb-3">Leitura instantânea por câmara mobile & rotação a cada 15s</p>

                        <div class="p-2 bg-dark bg-opacity-50 rounded-3 border border-white border-opacity-10 text-start small font-mono text-white-50 mb-3">
                            <div class="text-info">&gt; intern.verifyPhysicalPresence()</div>
                            <div class="text-success">&gt; STATUS: 200 OK • Luanda HQ</div>
                            <div class="text-white-50">&gt; Biometria QR + Geofence GPS ativado</div>
                        </div>

                        <a href="/attendance/scan" class="btn btn-outline-info btn-sm w-100 rounded-pill py-2 fw-semibold">
                            <i class="bi bi-camera me-1"></i> Simular Leitura do Terminal
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Counter Grid in Hero -->
        <div class="row g-3 pt-5 mt-4 border-top border-white border-opacity-10">
            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <span class="text-white-50 small d-block mb-1">Estagiários no Programa</span>
                    <h2 class="display-6 fw-bold text-white mb-0 font-mono text-primary" style="color: #60a5fa !important;">
                        +<?= number_format($stats['total_impacted']) ?>
                    </h2>
                    <span class="text-white-50 small" style="font-size: 0.78rem;">Formados e em Formação</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <span class="text-white-50 small d-block mb-1">Horas Práticas Auditadas</span>
                    <h2 class="display-6 fw-bold text-white mb-0 font-mono" style="color: #34d399 !important;">
                        +<?= number_format($stats['hours_practice']) ?>h
                    </h2>
                    <span class="text-white-50 small" style="font-size: 0.78rem;">Em projetos de produção</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <span class="text-white-50 small d-block mb-1">Instituições Conectadas</span>
                    <h2 class="display-6 fw-bold text-white mb-0 font-mono" style="color: #38bdf8 !important;">
                        <?= number_format($stats['institutions']) ?>
                    </h2>
                    <span class="text-white-50 small" style="font-size: 0.78rem;">Médios e Universidades</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <span class="text-white-50 small d-block mb-1">Taxa de Aproveitamento</span>
                    <h2 class="display-6 fw-bold text-white mb-0 font-mono" style="color: #fbbf24 !important;">
                        <?= $stats['retention_rate'] ?>%
                    </h2>
                    <span class="text-white-50 small" style="font-size: 0.78rem;">Escala 0 a 20 valores</span>
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
