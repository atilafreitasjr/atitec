@extends('layouts.site')
@section('title', 'A ATITEC — Consultorias e Tecnologias')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8">
    <p class="text-[#FFFF5D] font-semibold tracking-widest text-xs uppercase">Sobre nós</p>
    <h1 class="font-[Montserrat] font-extrabold text-4xl mt-2">Seriedade de consultoria.<br>Rigor de engenharia de software.</h1>
    <p class="text-gray-300 mt-5 max-w-3xl">A ATITEC — Consultorias e Tecnologias — desenvolve sistemas sob medida para o agro, serviços, eventos e varejo, organiza processos comerciais e automatiza atendimentos. Trabalhamos com contrato, documentação, backups e suporte: tecnologia que o cliente entende, acompanha e confia.</p>
    <div class="grid md:grid-cols-3 gap-6 mt-10">
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6"><h3 class="font-[Montserrat] font-bold text-[#FFFF5D]">Missão</h3><p class="text-sm text-gray-300 mt-2">Entregar tecnologia útil, compreensível e sustentável para pequenos e médios negócios.</p></div>
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6"><h3 class="font-[Montserrat] font-bold text-[#FFFF5D]">Visão</h3><p class="text-sm text-gray-300 mt-2">Ser a parceira de tecnologia e consultoria das empresas que crescem com método no interior do Brasil.</p></div>
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6"><h3 class="font-[Montserrat] font-bold text-[#FFFF5D]">Valores</h3><p class="text-sm text-gray-300 mt-2">Transparência, código próprio, rastreabilidade, suporte com SLA e compromisso social.</p></div>
    </div>
    <div class="bg-[#1A1D21] border border-white/10 rounded-2xl p-6 mt-8">
        <h3 class="font-[Montserrat] font-bold">Stack principal</h3>
        <p class="text-sm text-gray-400 mt-2">Laravel 13 • MariaDB • AdminLTE 4 • Vite + Tailwind • Docker • Filas e agendador Laravel • Backups automatizados.</p>
    </div>
</section>
@endsection
