<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="ATITEC — Consultorias e Tecnologias. Softwares sob medida, consultoria comercial e automatização de atendimentos.">
    <title>@yield('title', 'ATITEC — Consultorias e Tecnologias')</title>
    <link rel="icon" href="{{ asset('images/atitec_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0A0A0B] text-[#F3FFDC] font-[Inter] antialiased">
    <header class="sticky top-0 z-50 backdrop-blur bg-black/80 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-24 flex items-center justify-between">
            <a href="{{ route('site.home') }}" class="flex items-center">
                <img src="{{ asset('images/atitec_logo.svg') }}" alt="ATITEC — Consultorias e Tecnologias" class="h-20 w-auto">
            </a>
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('site.home') }}" class="hover:text-[#FFFF5D]">Início</a>
                <a href="{{ route('site.sobre') }}" class="hover:text-[#FFFF5D]">A ATITEC</a>
                <a href="{{ route('site.portfolio') }}" class="hover:text-[#FFFF5D]">Portfólio</a>
                <a href="{{ route('site.contato') }}" class="hover:text-[#FFFF5D]">Contato</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-[#FFFF5D]">Minha área</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-[#FFFF5D]">Entrar</a>
                @endauth
                <a href="{{ route('site.orcamento') }}" class="bg-[#FFFF5D] text-black font-semibold px-4 py-2 rounded-lg hover:bg-[#FFD400]">Solicitar orçamento</a>
            </nav>
            <a href="{{ route('site.orcamento') }}" class="md:hidden bg-[#FFFF5D] text-black text-sm font-semibold px-3 py-2 rounded-lg">Orçamento</a>
        </div>
    </header>

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-900/60 border border-green-500/40 text-green-100 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        </div>
    @endif

    <main>@yield('content')</main>

    <footer class="bg-black border-t border-white/10 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid gap-10 md:grid-cols-4">
            <div>
                <img src="{{ asset('images/atitec_logo.svg') }}" alt="ATITEC" class="h-12 w-auto mb-3">
                <p class="text-sm text-gray-400">Tecnologia sob medida. Consultoria que gera resultado.</p>
            </div>
            <div>
                <p class="font-[Montserrat] font-bold text-[#FFFF5D] mb-3">Serviços</p>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a class="hover:text-[#FFFF5D]" href="{{ route('site.servico', 'desenvolvimento-sob-medida') }}">Software sob medida</a></li>
                    <li><a class="hover:text-[#FFFF5D]" href="{{ route('site.servico', 'consultoria-comercial') }}">Consultoria comercial</a></li>
                    <li><a class="hover:text-[#FFFF5D]" href="{{ route('site.servico', 'automatizacao-atendimento') }}">Automatização de atendimento</a></li>
                </ul>
            </div>
            <div>
                <p class="font-[Montserrat] font-bold text-[#FFFF5D] mb-3">Empresa</p>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a class="hover:text-[#FFFF5D]" href="{{ route('site.sobre') }}">Sobre</a></li>
                    <li><a class="hover:text-[#FFFF5D]" href="{{ route('site.portfolio') }}">Portfólio</a></li>
                    <li><a class="hover:text-[#FFFF5D]" href="{{ route('site.orcamento') }}">Orçamento</a></li>
                </ul>
            </div>
            <div>
                <p class="font-[Montserrat] font-bold text-[#FFFF5D] mb-3">Contato</p>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a class="hover:text-[#FFFF5D]" href="https://wa.me/5542999999999" target="_blank">WhatsApp: (42) 99999-9999</a></li>
                    <li><a class="hover:text-[#FFFF5D]" href="mailto:contato@atitec.com.br">contato@atitec.com.br</a></li>
                    <li>Palmeira — Paraná — Brasil</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10 py-4 text-center text-xs text-gray-500">© {{ date('Y') }} ATITEC — Consultorias e Tecnologias. Todos os direitos reservados.</div>
    </footer>

    <a href="https://wa.me/5542999999999?text=Ol%C3%A1!%20Quero%20falar%20com%20a%20ATITEC." target="_blank" aria-label="WhatsApp"
       class="fixed bottom-5 right-5 z-50 bg-[#25D366] text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg hover:scale-105 transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/></svg>
    </a>
</body>
</html>
