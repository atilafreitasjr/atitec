@extends('layouts.site')
@section('title', 'ATITEC — Software sob medida, consultoria e automação')

@section('content')
{{-- HERO --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[900px] h-[500px] rounded-full opacity-20 blur-3xl" style="background: radial-gradient(closest-side, #FFFF5D, transparent);"></div>
        <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 44px 44px;"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16 grid gap-12 lg:grid-cols-2 items-center">
        <div>
            <span class="inline-block text-xs font-semibold tracking-widest uppercase bg-white/5 border border-[#FFFF5D]/40 text-[#FFFF5D] px-3 py-1.5 rounded-full">Software sob medida • Consultoria • Automação</span>
            <h1 class="font-[Montserrat] font-extrabold text-4xl sm:text-5xl leading-tight mt-5">Tecnologia sob medida.<br><span class="text-[#FFFF5D]">Consultoria que gera resultado.</span></h1>
            <p class="text-gray-300 mt-5 text-lg">A ATITEC desenvolve sistemas personalizados, organiza o comercial da sua empresa e automatiza atendimentos — com código próprio, contrato claro e suporte de verdade.</p>
            <div class="flex flex-wrap gap-3 mt-8">
                <a href="{{ route('site.orcamento') }}" class="bg-[#FFFF5D] text-black font-semibold px-6 py-3 rounded-lg hover:bg-[#FFD400]">Solicitar orçamento</a>
                <a href="{{ route('site.portfolio') }}" class="border border-white/20 px-6 py-3 rounded-lg hover:border-[#FFFF5D] hover:text-[#FFFF5D]">Ver portfólio</a>
            </div>
            <dl class="grid grid-cols-3 gap-4 mt-10 text-center">
                <div class="bg-white/5 border border-white/10 rounded-xl p-4"><dt class="text-2xl font-[Montserrat] font-extrabold text-[#FFFF5D]">7+</dt><dd class="text-xs text-gray-400 mt-1">sistemas em produção</dd></div>
                <div class="bg-white/5 border border-white/10 rounded-xl p-4"><dt class="text-2xl font-[Montserrat] font-extrabold text-[#FFFF5D]">3</dt><dd class="text-xs text-gray-400 mt-1">frentes: software, consultoria e automação</dd></div>
                <div class="bg-white/5 border border-white/10 rounded-xl p-4"><dt class="text-2xl font-[Montserrat] font-extrabold text-[#FFFF5D]">100%</dt><dd class="text-xs text-gray-400 mt-1">código próprio e documentado</dd></div>
            </dl>
        </div>
        <div class="bg-[#1A1D21] border border-white/10 rounded-2xl p-6 shadow-2xl">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-3 h-3 rounded-full bg-red-500"></span><span class="w-3 h-3 rounded-full bg-yellow-500"></span><span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="ml-2 text-xs text-gray-400">painel do cliente • atitec</span>
            </div>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="bg-black/40 rounded-xl p-4"><p class="text-[#FFFF5D] font-bold text-xl">{{ $featured->count() }}</p><p class="text-xs text-gray-400">cases destaque</p></div>
                <div class="bg-black/40 rounded-xl p-4"><p class="text-[#FFFF5D] font-bold text-xl">{{ $projects->count() }}</p><p class="text-xs text-gray-400">projetos no portfólio</p></div>
                <div class="bg-black/40 rounded-xl p-4"><p class="text-[#FFFF5D] font-bold text-xl">SLA</p><p class="text-xs text-gray-400">suporte com contrato</p></div>
            </div>
            <ul class="mt-5 space-y-3 text-sm">
                @foreach($featured as $p)
                <li class="flex items-center justify-between bg-black/40 border border-white/5 rounded-lg px-4 py-3">
                    <span>{{ $p->title }} <span class="text-gray-500">• {{ $p->segment }}</span></span>
                    <a href="{{ route('site.case', $p->slug) }}" class="text-[#FFFF5D] hover:underline">ver case →</a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- SERVIÇOS --}}
<section class="relative overflow-hidden py-16">
    <img src="{{ asset('images/atitec_marca_agua.svg') }}" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -right-28 top-1/2 -translate-y-1/2 w-[440px] max-w-none opacity-[0.06]">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="text-[#FFFF5D] font-semibold tracking-widest text-xs uppercase">O que fazemos</p>
    <h2 class="font-[Montserrat] font-extrabold text-3xl mt-2">Três pilares, um parceiro só</h2>
    <div class="grid md:grid-cols-3 gap-6 mt-8">
        <a href="{{ route('site.servico', 'desenvolvimento-sob-medida') }}" class="group bg-[#1A1D21] border border-white/10 rounded-2xl p-6 hover:border-[#FFFF5D]/70 hover:-translate-y-1 transition">
            <p class="text-3xl">🛠️</p>
            <h3 class="font-[Montserrat] font-bold text-lg mt-3 group-hover:text-[#FFFF5D]">Software sob medida</h3>
            <p class="text-sm text-gray-400 mt-2">Sistemas web personalizados em Laravel + MariaDB. Sem mensalidade de plataforma, sem gambiarra.</p>
        </a>
        <a href="{{ route('site.servico', 'consultoria-comercial') }}" class="group bg-[#1A1D21] border border-white/10 rounded-2xl p-6 hover:border-[#FFFF5D]/70 hover:-translate-y-1 transition">
            <p class="text-3xl">📈</p>
            <h3 class="font-[Montserrat] font-bold text-lg mt-3 group-hover:text-[#FFFF5D]">Consultoria comercial</h3>
            <p class="text-sm text-gray-400 mt-2">Diagnóstico, funil, indicadores e capacitação para vender com método.</p>
        </a>
        <a href="{{ route('site.servico', 'automatizacao-atendimento') }}" class="group bg-[#1A1D21] border border-white/10 rounded-2xl p-6 hover:border-[#FFFF5D]/70 hover:-translate-y-1 transition">
            <p class="text-3xl">🤖</p>
            <h3 class="font-[Montserrat] font-bold text-lg mt-3 group-hover:text-[#FFFF5D]">Automatização de atendimento</h3>
            <p class="text-sm text-gray-400 mt-2">Chatbots, WhatsApp e IA integrados ao seu sistema. Atenda rápido, sem perder o humano.</p>
        </a>
    </div>
    </div>
</section>

{{-- PORTFÓLIO --}}
<section class="bg-[#F6F7F9] text-gray-900 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-xs font-semibold tracking-widest uppercase text-gray-500">Portfólio real</p>
        <h2 class="font-[Montserrat] font-extrabold text-3xl mt-2">Sistemas em produção, não promessas</h2>
        <div class="grid md:grid-cols-3 gap-6 mt-8">
            @foreach($projects as $p)
            <article class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:-translate-y-1 transition">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500">{{ $p->segment }}</p>
                <h3 class="font-[Montserrat] font-bold text-lg mt-1">{{ $p->title }}</h3>
                <p class="text-sm text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($p->description, 120) }}</p>
                <div class="flex items-center justify-between mt-4">
                    <a href="{{ route('site.case', $p->slug) }}" class="text-sm font-semibold underline underline-offset-4">Ver case</a>
                    @if($p->url)<a href="{{ $p->url }}" target="_blank" class="text-sm text-gray-500 hover:text-black">visitar ↗</a>@endif
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>

{{-- PROCESSO --}}
<section class="relative overflow-hidden py-16">
    <img src="{{ asset('images/atitec_marca_agua.svg') }}" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -left-28 top-1/2 -translate-y-1/2 w-[440px] max-w-none opacity-[0.06]">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <p class="text-[#FFFF5D] font-semibold tracking-widest text-xs uppercase">Como trabalhamos</p>
    <h2 class="font-[Montserrat] font-extrabold text-3xl mt-2">Do discovery ao suporte, com método</h2>
    <ol class="grid md:grid-cols-5 gap-4 mt-8 text-sm">
        @foreach(['Descoberta' => 'Entendemos processo, dores e metas.', 'Protótipo' => 'Telas navegáveis antes do código.', 'Desenvolvimento' => 'Entregas semanais com homologação.', 'Entrega' => 'Treinamento, dados e go-live.', 'Suporte' => 'SLA, backups e evolução.'] as $etapa => $desc)
        <li class="bg-white/5 border border-white/10 rounded-xl p-4"><p class="font-[Montserrat] font-bold text-[#FFFF5D]">{{ $loop->iteration }}. {{ $etapa }}</p><p class="text-gray-400 mt-2">{{ $desc }}</p></li>
        @endforeach
    </ol>
    </div>
</section>

{{-- CTA --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-4">
    <div class="bg-[#FFFF5D] text-black rounded-2xl p-8 md:p-12 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
            <h2 class="font-[Montserrat] font-extrabold text-2xl md:text-3xl">Vamos tirar seu projeto do papel?</h2>
            <p class="mt-2 font-medium">Conte sua ideia em 2 minutos e receba uma proposta objetiva da ATITEC.</p>
        </div>
        <a href="{{ route('site.orcamento') }}" class="bg-black text-[#FFFF5D] font-semibold px-6 py-3 rounded-lg hover:bg-[#1A1D21]">Pedir orçamento</a>
    </div>
</section>
@endsection
