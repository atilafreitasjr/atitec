@extends('layouts.site')
@section('title', 'Solicitar orçamento — ATITEC')

@section('content')
<section class="max-w-3xl mx-auto px-4 sm:px-6 pt-14 pb-8">
    <p class="text-[#FFFF5D] font-semibold tracking-widest text-xs uppercase">Orçamento em 3 passos</p>
    <h1 class="font-[Montserrat] font-extrabold text-4xl mt-2">Descreva seu projeto</h1>
    <p class="text-gray-400 mt-3">1. Tipo de projeto → 2. Detalhes → 3. Seus contatos. Retornamos com proposta objetiva.</p>
    <form method="POST" action="{{ route('site.orcamento.store') }}" class="bg-[#1A1D21] border border-white/10 rounded-2xl p-6 mt-6 space-y-4">
        @csrf
        @if($errors->any())<div class="bg-red-900/50 border border-red-500/40 text-sm rounded-lg p-3">{{ $errors->first() }}</div>@endif
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Nome*</label><input name="name" required value="{{ old('name') }}" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
            <div><label class="text-sm font-medium">Empresa</label><input name="company" value="{{ old('company') }}" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">E-mail*</label><input name="email" type="email" required value="{{ old('email') }}" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
            <div><label class="text-sm font-medium">Telefone/WhatsApp</label><input name="phone" value="{{ old('phone') }}" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Tipo de projeto*</label>
                <select name="project_type" required class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm">
                    <option value="">Selecione…</option>
                    <option @selected(old('project_type')==='Software sob medida')>Software sob medida</option>
                    <option @selected(old('project_type')==='Consultoria comercial')>Consultoria comercial</option>
                    <option @selected(old('project_type')==='Automatização de atendimento')>Automatização de atendimento</option>
                    <option @selected(old('project_type')==='Outro')>Outro</option>
                </select>
            </div>
            <div><label class="text-sm font-medium">Faixa de investimento</label>
                <select name="budget_range" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm">
                    <option value="">Selecione…</option>
                    <option @selected(old('budget_range')==='até R$ 5 mil')>até R$ 5 mil</option>
                    <option @selected(old('budget_range')==='R$ 5–15 mil')>R$ 5–15 mil</option>
                    <option @selected(old('budget_range')==='R$ 15–50 mil')>R$ 15–50 mil</option>
                    <option @selected(old('budget_range')==='acima de R$ 50 mil')>acima de R$ 50 mil</option>
                    <option @selected(old('budget_range')==='mensalidade/suporte')>mensalidade/suporte</option>
                </select>
            </div>
        </div>
        <div><label class="text-sm font-medium">Detalhes do projeto*</label><textarea name="details" rows="6" required placeholder="O que o sistema deve fazer? Quantos usuários? Tem prazo?" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm">{{ old('details') }}</textarea></div>
        <button class="w-full bg-[#FFFF5D] text-black font-semibold py-3 rounded-lg hover:bg-[#FFD400]">Enviar pedido de orçamento</button>
    </form>
</section>
@endsection
