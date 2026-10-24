<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'ATITEC — Consultorias e Tecnologias. Softwares sob medida, consultoria comercial e automatização de atendimentos.')">
    <meta name="theme-color" content="#0A0A0B">
    <title>@yield('title', 'ATITEC — Consultorias e Tecnologias')</title>
    <link rel="icon" type="image/png" href="{{ asset('atitec/assets/images/atitec_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind + JS do projeto (widget de IA, Alpine e scripts compartilhados). --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Layout novo da ATITEC (CSS/JS próprios da entrega de design). --}}
    <link rel="stylesheet" href="{{ asset('atitec/assets/css/style.css') }}">
    <script defer src="{{ asset('atitec/assets/js/main.js') }}"></script>

    <noscript>
        <style>@media(max-width:760px){#mobile-menu[hidden]{display:block!important}.menu-toggle{display:none!important}}</style>
    </noscript>
    <style>[x-cloak]{display:none!important}</style>
    @stack('head')
</head>
<body class="bg-[#0A0A0B] text-[#F3FFDC] font-[Inter] antialiased">
    <a class="atitec-home skip-link" href="#conteudo">Pular para o conteúdo</a>
    <div aria-hidden="true" class="scroll-progress"></div>

    <header class="atitec-home site-header" id="topo">
        <div class="container header-inner">
            <a class="brand" href="{{ route('site.home') }}" aria-label="ATITEC — início">
                <img alt="ATITEC — Consultorias e Tecnologias" height="507" src="{{ asset('atitec/assets/images/atitec_logo.png') }}" width="473">
            </a>

            <nav aria-label="Navegação principal" class="desktop-nav">
                <a href="{{ route('site.home') }}" @if(request()->routeIs('site.home')) aria-current="page" @endif>Início</a>
                <a href="{{ route('site.sobre') }}" @if(request()->routeIs('site.sobre')) aria-current="page" @endif>A ATITEC</a>
                <a href="{{ request()->routeIs('site.home') ? '#solucoes' : route('site.home').'#solucoes' }}" @if(request()->routeIs('site.servico')) aria-current="page" @endif>Soluções</a>
                <a href="{{ request()->routeIs('site.home') ? '#portfolio' : route('site.portfolio') }}" @if(request()->routeIs('site.portfolio', 'site.case')) aria-current="page" @endif>Portfólio</a>
                <a href="{{ route('site.contato') }}" @if(request()->routeIs('site.contato')) aria-current="page" @endif>Contato</a>
            </nav>

            <div class="header-actions">
                @auth
                    <a class="client-link" href="{{ route('dashboard') }}">
                        Minha área
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg>
                    </a>
                @else
                    <a class="client-link" href="{{ route('login') }}">
                        Área do cliente
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg>
                    </a>
                @endauth

                <a class="button button-primary header-cta" href="{{ route('site.orcamento') }}">
                    Vamos conversar
                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                </a>

                <button aria-controls="mobile-menu" aria-expanded="false" aria-label="Abrir menu" class="menu-toggle" type="button">
                    <span></span><span></span>
                </button>
            </div>
        </div>

        <nav aria-label="Navegação para celular" class="mobile-menu" hidden id="mobile-menu">
            <a href="{{ route('site.home') }}">Início</a>
            <a href="{{ route('site.sobre') }}">A ATITEC</a>
            <a href="{{ request()->routeIs('site.home') ? '#solucoes' : route('site.home').'#solucoes' }}">Soluções</a>
            <a href="{{ request()->routeIs('site.home') ? '#portfolio' : route('site.portfolio') }}">Portfólio</a>
            <a href="{{ route('site.contato') }}">Contato</a>
            @auth
                <a href="{{ route('dashboard') }}">Minha área</a>
            @else
                <a href="{{ route('login') }}">Entrar</a>
                <a href="{{ route('register') }}">Criar conta</a>
            @endauth
            <a class="button button-primary" href="{{ route('site.orcamento') }}">
                Solicitar orçamento
                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
            </a>
        </nav>
    </header>

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-900/60 border border-green-500/40 text-green-100 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        </div>
    @endif

    <main id="conteudo" class="{{ request()->routeIs('site.home') ? '' : 'font-[Inter]' }}">@yield('content')</main>

    <footer class="atitec-home site-footer {{ request()->routeIs('site.home') ? '' : 'mt-20' }}">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a aria-label="ATITEC — início" href="{{ route('site.home') }}">
                    <img alt="ATITEC" height="507" loading="lazy" src="{{ asset('atitec/assets/images/atitec_logo.png') }}" width="473">
                </a>
                <p>Tecnologia sob medida.<br>Consultoria que gera resultado.</p>
                <a class="footer-mail" href="mailto:{{ config('company.email') }}">
                    {{ config('company.email') }}
                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg>
                </a>
            </div>

            <div>
                <h2>Soluções</h2>
                <a href="{{ route('site.servico', 'desenvolvimento-sob-medida') }}">Software sob medida</a>
                <a href="{{ route('site.servico', 'consultoria-comercial') }}">Consultoria comercial</a>
                <a href="{{ route('site.servico', 'automatizacao-atendimento') }}">Automatização de atendimento</a>
            </div>

            <div>
                <h2>ATITEC</h2>
                <a href="{{ route('site.sobre') }}">Sobre a empresa</a>
                <a href="{{ route('site.portfolio') }}">Portfólio</a>
                <a href="{{ route('site.contato') }}">Contato</a>
                @auth
                    <a href="{{ route('dashboard') }}">Minha área</a>
                @else
                    <a href="{{ route('login') }}">Entrar na área do cliente</a>
                    <a href="{{ route('register') }}">Criar conta</a>
                @endauth
            </div>

            <div>
                <h2>Vamos conversar</h2>
                <a href="{{ config('company.whatsapp') }}" rel="noopener noreferrer" target="_blank">
                    {{ config('company.phone') }}
                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6h12v12"></path></svg>
                </a>
                <p>
                    {{ config('company.address') }}<br>
                    {{ config('company.neighborhood') }} · {{ config('company.city') }}/{{ config('company.state') }}<br>
                    CEP {{ config('company.zip_code') }}
                </p>
                <a class="text-link" href="{{ route('site.orcamento') }}">
                    Solicitar orçamento
                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                </a>
            </div>
        </div>

        <div class="container footer-bottom">
            <p>
                © {{ date('Y') }} {{ config('company.name') }}.<br>
                {{ config('company.legal_name') }} · CNPJ {{ config('company.cnpj') }}. Todos os direitos reservados.
            </p>
            <a href="#topo">Voltar ao topo ↑</a>
        </div>
    </footer>

    <a aria-label="Falar com a ATITEC no WhatsApp (nova aba)" class="atitec-home whatsapp-float" href="{{ config('company.whatsapp') }}" rel="noopener noreferrer" target="_blank">
        <svg fill="currentColor" height="28" viewBox="0 0 16 16" width="28" xmlns="http://www.w3.org/2000/svg"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"></path></svg>
        <span>Vamos conversar?</span>
    </a>

    @if(config('atendimento_ia.enabled'))
        @include('site.partials.atendimento-ia')
    @endif

    @stack('scripts')
</body>
</html>
