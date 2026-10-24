@extends('layouts.site')

@section('title', 'ATITEC — Tecnologia que move seu negócio')
@section('description', 'ATITEC — Software sob medida, consultoria comercial e automatização de atendimento. Tecnologia que move o seu negócio, com código próprio e suporte de verdade.')

@php
    $categoryBySlug = [
        'atitec' => 'negocios',
        'agricultura-familiar' => 'agro',
        'gestao-de-estacionamento' => 'negocios',
        'porcos-crioulos' => 'agro',
        'simposio-do-leite' => 'eventos',
        'sistema-ovinos' => 'agro',
        'aquichopp' => 'negocios',
    ];

    $artMap = [
        'atitec' => [
            'class' => 'art-0',
            'label' => 'Workspace do cliente',
            'icon' => '<rect height="7" rx="2" width="7" x="3" y="3"></rect><rect height="7" rx="2" width="7" x="14" y="3"></rect><rect height="7" rx="2" width="7" x="3" y="14"></rect><rect height="7" rx="2" width="7" x="14" y="14"></rect>',
        ],
        'agricultura-familiar' => [
            'class' => 'art-1',
            'label' => 'Gestão & comercialização',
            'icon' => '<path d="M20 3C6 2 2 10 6 16s14 1 14-13ZM4 21l11-12"></path>',
        ],
        'porcos-crioulos' => [
            'class' => 'art-2',
            'label' => 'Manejo & genealogia',
            'icon' => '<path d="M20 3C6 2 2 10 6 16s14 1 14-13ZM4 21l11-12"></path>',
        ],
        'gestao-de-estacionamento' => [
            'class' => 'art-3',
            'label' => 'Controle & mobilidade',
            'icon' => '<path d="m4 9 2-5h12l2 5M3 10h18v8H3zM5 18v3m14-3v3M6 14h2m8 0h2"></path>',
        ],
        'simposio-do-leite' => [
            'class' => 'art-4',
            'label' => 'Inscrições & certificados',
            'icon' => '<rect height="16" rx="3" width="18" x="3" y="5"></rect><path d="M7 2v6m10-6v6M3 11h18m-13 5h3"></path>',
        ],
        'sistema-ovinos' => [
            'class' => 'art-5',
            'label' => 'Identidade & rastreabilidade',
            'icon' => '<path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z"></path><path d="m8 12 3 3 5-6"></path>',
        ],
        'aquichopp' => [
            'class' => 'art-6',
            'label' => 'Pedidos & entregas',
            'icon' => '<path d="M5 4h11v14a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V4Zm11 3h4v7h-4M8 7v10m5-10v10"></path>',
        ],
    ];

    $fallbackIcon = $artMap['atitec']['icon'];
@endphp

@section('content')
    <div class="atitec-home">
    <section aria-labelledby="hero-title" class="hero">
        <div aria-hidden="true" class="ambient-background">
            <span class="ambient-aura aura-one"></span>
            <span class="ambient-aura aura-two"></span>
            <span class="ambient-scan"></span>
            <canvas aria-hidden="true" class="network-canvas"></canvas>
        </div>
        <div aria-hidden="true" class="hero-grid"></div>
        <div aria-hidden="true" class="hero-glow"></div>

        <div class="container hero-layout">
            <div class="hero-copy">
                <p class="eyebrow"><span class="status-dot"></span> SEU NEGÓCIO. NOSSA PRÓXIMA SOLUÇÃO.</p>
                <h1 id="hero-title">Tecnologia<br>que <span class="accent">move seu<br>negócio<span class="period">.</span></span></h1>
                <p class="hero-description">Software sob medida, consultoria comercial e automação. <strong>Três frentes conectadas</strong> para transformar desafios em soluções que funcionam.</p>
                <div class="hero-buttons">
                    <a class="button button-primary" href="{{ route('site.orcamento') }}">
                        Tirar meu projeto do papel
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                    </a>
                    <a class="button button-secondary" href="#portfolio">
                        Conhecer os projetos
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg>
                    </a>
                </div>
                <p class="hero-proof">
                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg>
                    Código próprio. Contrato claro. Suporte de verdade.
                </p>
            </div>

            <div aria-label="Ilustração das três frentes da ATITEC: software, consultoria e automação" class="hero-visual">
                <div aria-hidden="true" class="orbit orbit-one"></div>
                <div aria-hidden="true" class="orbit orbit-two"></div>

                <div class="visual-frame">
                    <div class="floating-note note-top">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z"></path><path d="m8 12 3 3 5-6"></path></svg>
                        Feito para a sua operação
                    </div>

                    <div class="system-window">
                        <div class="window-top">
                            <span aria-hidden="true" class="window-dots"><i></i><i></i><i></i></span>
                            <span>atitec / ecossistema digital</span>
                            <span class="window-symbol">⌘</span>
                        </div>

                        <div class="window-body">
                            <div class="window-heading">
                                <span class="micro-label">DA IDEIA À OPERAÇÃO</span>
                                <span class="pill">Sob medida</span>
                            </div>
                            <div class="system-title">Seu negócio.<br><span>Mais conectado.</span></div>

                            <div class="flow-diagram">
                                <div class="flow-node">
                                    <span><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><path d="m8 7-5 5 5 5m8-10 5 5-5 5m-3-14-2 18"></path></svg></span>
                                    <b>Software</b>
                                </div>
                                <div class="flow-node central-node">
                                    <span><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4v16h16M7 15l4-5 4 3 5-8"></path></svg></span>
                                    <b>Consultoria</b>
                                </div>
                                <div class="flow-node">
                                    <span><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><rect height="13" rx="4" width="16" x="4" y="7"></rect><path d="M12 3v4M9 16h6M1 11v5m22-5v5"></path><circle cx="8" cy="12" r="1"></circle><circle cx="16" cy="12" r="1"></circle></svg></span>
                                    <b>Automação</b>
                                </div>
                            </div>

                            <div class="system-row">
                                <div>
                                    <span class="micro-label">UM PARCEIRO. TRÊS FRENTES.</span>
                                    <p>Processos, pessoas e tecnologia.</p>
                                </div>
                                <span class="system-check"><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg></span>
                            </div>

                            <div class="code-block">
                                <span class="code-line"><em>const</em> próximoPasso = {</span>
                                <span class="code-line indent">ideia: <strong>"a sua"</strong>,</span>
                                <span class="code-line indent">solução: <strong>"sob medida"</strong></span>
                                <span class="code-line">};</span>
                                <span aria-hidden="true" class="code-cursor"></span>
                            </div>
                        </div>

                        <div class="window-footer">
                            <span class="status-dot"></span> Conexões que fazem sentido <span>01 — 03</span>
                        </div>
                    </div>

                    <a class="floating-note note-bottom" href="{{ route('site.portfolio') }}">
                        <span class="mini-icon"><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><rect height="7" rx="2" width="7" x="3" y="3"></rect><rect height="7" rx="2" width="7" x="14" y="3"></rect><rect height="7" rx="2" width="7" x="3" y="14"></rect><rect height="7" rx="2" width="7" x="14" y="14"></rect></svg></span>
                        <span><strong>Projetos reais.</strong><small>Sistemas em produção.</small></span>
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg>
                    </a>
                </div>

                <div aria-hidden="true" class="orbit-dot"></div>
                <p class="illustration-label">Representação conceitual das soluções ATITEC</p>
            </div>
        </div>

        <div class="container hero-bottom">
            <a class="scroll-cue" href="#solucoes"><span>↓</span> Explore as possibilidades</a>
            <div class="background-controls">
                <span class="hero-bottom-note">DESENVOLVIMENTO COM PROPÓSITO, DO INÍCIO AO SUPORTE.</span>
                <button aria-label="Pausar animação da abertura" aria-pressed="false" class="animation-toggle" hidden type="button">
                    <span aria-hidden="true" class="pause-icon"></span>
                    <span class="animation-label">Pausar animação</span>
                </button>
            </div>
        </div>
    </section>

    <section aria-label="Diferenciais ATITEC" class="proof-strip">
        <div class="container proof-grid">
            <div><strong>{{ $liveProjectCount }}<span>+</span></strong><p>Sistemas em produção</p></div>
            <div><strong>3</strong><p>Frentes. Um parceiro só.</p></div>
            <div><strong>100<span>%</span></strong><p>Código próprio e documentado</p></div>
            <div class="proof-promise">
                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z"></path><path d="m8 12 3 3 5-6"></path></svg>
                <p>Da primeira conversa<br>ao suporte de verdade.</p>
            </div>
        </div>
    </section>

    <section class="services section" id="solucoes">
        <img alt="" aria-hidden="true" class="watermark" height="507" loading="lazy" src="{{ asset('atitec/assets/images/atitec_marca_agua.png') }}" width="473">
        <div class="container">
            <div class="section-heading reveal">
                <div>
                    <p class="eyebrow">/ 01 — O QUE FAZEMOS</p>
                    <h2>Complexidade para nós.<br><span class="muted">Simplicidade para você.</span></h2>
                </div>
                <p class="section-intro">A tecnologia certa não é a mais complicada.<br>É a que resolve o seu problema.</p>
            </div>

            <div class="services-grid">
                <a class="service-card reveal" href="{{ route('site.servico', 'desenvolvimento-sob-medida') }}">
                    <div class="card-top">
                        <span class="icon-box"><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><path d="m8 7-5 5 5 5m8-10 5 5-5 5m-3-14-2 18"></path></svg></span>
                        <span class="card-number">/01</span>
                    </div>
                    <h3>Software<br>sob medida</h3>
                    <p class="service-lead">Sistemas que se adaptam à sua empresa. Não o contrário.</p>
                    <p class="card-description">Sistemas web personalizados em Laravel + MariaDB, com código próprio e documentado.</p>
                    <div class="tag-list"><span>Código próprio</span><span>Integrações</span><span>Sem mensalidade de plataforma</span></div>
                    <span class="card-link">Conhecer a solução <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span>
                </a>

                <a class="service-card reveal" href="{{ route('site.servico', 'consultoria-comercial') }}">
                    <div class="card-top">
                        <span class="icon-box"><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 4v16h16M7 15l4-5 4 3 5-8"></path></svg></span>
                        <span class="card-number">/02</span>
                    </div>
                    <h3>Consultoria<br>comercial</h3>
                    <p class="service-lead">Menos achismo.<br>Mais método para vender.</p>
                    <p class="card-description">Diagnóstico, funil, indicadores e capacitação para organizar seu comercial.</p>
                    <div class="tag-list"><span>Diagnóstico</span><span>Indicadores</span><span>Capacitação</span></div>
                    <span class="card-link">Conhecer a solução <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span>
                </a>

                <a class="service-card reveal" href="{{ route('site.servico', 'automatizacao-atendimento') }}">
                    <div class="card-top">
                        <span class="icon-box"><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><rect height="13" rx="4" width="16" x="4" y="7"></rect><path d="M12 3v4M9 16h6M1 11v5m22-5v5"></path><circle cx="8" cy="12" r="1"></circle><circle cx="16" cy="12" r="1"></circle></svg></span>
                        <span class="card-number">/03</span>
                    </div>
                    <h3>Automatização<br>de atendimento</h3>
                    <p class="service-lead">Agilidade para o cliente.<br>Tempo para sua equipe.</p>
                    <p class="card-description">Chatbots, WhatsApp e IA integrados ao seu sistema, sem perder o atendimento humano.</p>
                    <div class="tag-list"><span>WhatsApp</span><span>Chatbots e IA</span><span>Integração</span></div>
                    <span class="card-link">Conhecer a solução <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span>
                </a>
            </div>
        </div>
    </section>

    <section class="portfolio section" id="portfolio">
        <div class="container">
            <div class="section-heading reveal">
                <div>
                    <p class="eyebrow">/ 02 — PORTFÓLIO REAL</p>
                    <h2>Não são promessas.<br>São <span class="highlight-text">projetos em produção.</span></h2>
                </div>
                <a class="text-link" href="{{ route('site.portfolio') }}">
                    Ver portfólio completo
                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg>
                </a>
            </div>

            <div class="portfolio-toolbar">
                <p>Negócios diferentes. O mesmo cuidado em cada solução.</p>
                <div aria-label="Filtrar projetos por setor" class="filters" hidden role="group">
                    <button aria-pressed="true" class="filter active" data-filter="all" type="button">Todos</button>
                    <button aria-pressed="false" class="filter" data-filter="agro" type="button">Agro</button>
                    <button aria-pressed="false" class="filter" data-filter="negocios" type="button">Negócios</button>
                    <button aria-pressed="false" class="filter" data-filter="eventos" type="button">Eventos</button>
                </div>
            </div>

            <p aria-live="polite" class="sr-only" id="filter-status" role="status">{{ $projects->count() }} projetos exibidos.</p>

            <div class="projects-grid">
                @foreach($projects as $project)
                    @php
                        $segment = mb_strtolower((string) $project->segment);
                        $category = $categoryBySlug[$project->slug] ?? (
                            str_contains($segment, 'agro') || str_contains($segment, 'cooperativ')
                                ? 'agro'
                                : (str_contains($segment, 'evento') ? 'eventos' : 'negocios')
                        );
                        $art = $artMap[$project->slug] ?? [
                            'class' => 'art-'.($loop->index % 7),
                            'label' => $project->segment ?: 'Projeto ATITEC',
                            'icon' => $fallbackIcon,
                        ];
                    @endphp

                    <article class="project-card reveal" data-category="{{ $category }}">
                        <a aria-label="Ver case {{ $project->title }}" class="project-art {{ $art['class'] }}" href="{{ route('site.case', $project->slug) }}">
                            <div class="art-grid"></div>
                            <span class="art-index">AT / {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="art-icon">
                                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24">{!! $art['icon'] !!}</svg>
                            </span>
                            <div class="art-lines"><i></i><i></i><i></i></div>
                            <span class="art-label">{{ $art['label'] }}</span>
                            <span class="art-arrow"><svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg></span>
                        </a>

                        <div class="project-content">
                            <p class="project-category">{{ $project->segment ?: 'Projeto ATITEC' }}</p>
                            <h3><a href="{{ route('site.case', $project->slug) }}">{{ $project->title }}</a></h3>
                            <p>{{ \Illuminate\Support\Str::limit((string) $project->description, 120) }}</p>
                            <div class="project-links">
                                <a href="{{ route('site.case', $project->slug) }}">
                                    Explorar case
                                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                                </a>
                                @if($project->url)
                                    <a aria-label="Visitar site de {{ $project->title }} (nova aba)" class="visit" href="{{ $project->url }}" rel="noopener noreferrer" target="_blank">
                                        Visitar
                                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <p class="portfolio-caption">Elementos gráficos ilustrativos. Conheça cada sistema nos links dos cases.</p>
        </div>
    </section>

    <section class="process section" id="metodo">
        <div class="container">
            <div class="section-heading reveal">
                <div>
                    <p class="eyebrow">/ 03 — COMO TRABALHAMOS</p>
                    <h2>Seu projeto não precisa<br>ser uma <span class="accent">caixa-preta.</span></h2>
                </div>
                <p class="section-intro">Um processo claro, com você por perto.<br>Da primeira conversa à evolução contínua.</p>
            </div>

            <ol class="process-grid">
                <li class="reveal"><span class="step-no">01</span><h3>Descoberta</h3><strong>Primeiro, o seu negócio.</strong><p>Entendemos processos, dores e metas para definir o caminho.</p></li>
                <li class="reveal"><span class="step-no">02</span><h3>Protótipo</h3><strong>Veja antes de construir.</strong><p>Telas navegáveis para alinhar a experiência antes do código.</p></li>
                <li class="reveal"><span class="step-no">03</span><h3>Desenvolvimento</h3><strong>Evolução a cada entrega.</strong><p>Entregas semanais com homologação e acompanhamento.</p></li>
                <li class="reveal"><span class="step-no">04</span><h3>Entrega</h3><strong>Tudo pronto para começar.</strong><p>Treinamento, preparação dos dados e entrada em produção.</p></li>
                <li class="reveal"><span class="step-no">05</span><h3>Suporte</h3><strong>A parceria continua.</strong><p>SLA, backups e evolução para acompanhar sua operação.</p></li>
            </ol>

            <div class="process-banner reveal">
                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg>
                <p><strong>Você participa. Nós construímos.</strong> Alinhamento antes do código, acompanhamento durante as entregas.</p>
                <a href="{{ route('site.orcamento') }}">Começar uma conversa <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></a>
            </div>
        </div>
    </section>

    <section class="faq section">
        <div class="container faq-layout">
            <div class="reveal">
                <p class="eyebrow">/ 04 — SEM COMPLICAÇÃO</p>
                <h2>Boas perguntas.<br><span class="muted">Respostas claras.</span></h2>
                <p class="section-intro">Quer falar sobre o seu caso?<br>A conversa começa por aqui.</p>
                <a class="text-link" href="{{ config('company.whatsapp') }}" rel="noopener noreferrer" target="_blank">Falar com a ATITEC <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg></a>
            </div>
            <div>
                <details class="faq-item reveal">
                    <summary>O software é realmente sob medida?<span aria-hidden="true">+</span></summary>
                    <p>Sim. A ATITEC desenvolve sistemas web personalizados em Laravel + MariaDB, com código próprio e documentado, para os processos da sua empresa.</p>
                </details>
                <details class="faq-item reveal">
                    <summary>Posso contratar consultoria ou automação?<span aria-hidden="true">+</span></summary>
                    <p>Além de software, a ATITEC oferece consultoria comercial e automatização de atendimento. Você pode conhecer cada frente e solicitar uma proposta para sua necessidade.</p>
                </details>
                <details class="faq-item reveal">
                    <summary>Como acompanho o desenvolvimento?<span aria-hidden="true">+</span></summary>
                    <p>O processo inclui descoberta, protótipo navegável, desenvolvimento com entregas semanais e homologação, seguido de treinamento e entrada em produção.</p>
                </details>
                <details class="faq-item reveal">
                    <summary>Existe suporte depois da entrega?<span aria-hidden="true">+</span></summary>
                    <p>A ATITEC oferece suporte com contrato, SLA, backups e evolução. Os detalhes e o escopo devem ser definidos na proposta e no contrato.</p>
                </details>
            </div>
        </div>
    </section>

    <section class="contact-section" id="contato">
        <div class="container">
            <div class="contact-card reveal">
                <div aria-hidden="true" class="contact-pattern"></div>
                <div class="contact-copy">
                    <p class="eyebrow">O PRÓXIMO PASSO É SEU.</p>
                    <h2>Sua ideia merece<br><span>acontecer.</span></h2>
                    <p>Conte seu projeto em 2 minutos.<br>Vamos transformar o próximo desafio em uma solução.</p>
                    <div class="contact-buttons">
                        <a class="button button-dark" href="{{ route('site.orcamento') }}">
                            Solicitar meu orçamento
                            <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                        </a>
                        <a class="contact-wa" href="{{ config('company.whatsapp') }}" rel="noopener noreferrer" target="_blank">
                            Prefiro falar no WhatsApp
                            <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg>
                        </a>
                    </div>
                </div>
                <div aria-hidden="true" class="contact-art">
                    <span class="big-star">✳</span>
                    <span>IDEIAS EM MOVIMENTO.</span>
                </div>
            </div>
        </div>
    </section>
    </div>
@endsection
