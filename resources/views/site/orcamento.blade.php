@extends('layouts.site')
@section('title', 'Solicitar orçamento — ATITEC')

@section('content')
<section class="max-w-3xl mx-auto px-4 sm:px-6 pt-14 pb-8">
    <p class="text-[#FFFF5D] font-semibold tracking-widest text-xs uppercase">Orçamento em 3 passos</p>
    <h1 class="font-[Montserrat] font-extrabold text-4xl mt-2">Descreva seu projeto</h1>
    <p class="text-gray-400 mt-3">1. Tipo de projeto → 2. Detalhes → 3. Seus contatos. Retornamos com proposta objetiva.</p>
    <p class="mt-3 text-sm"><a class="text-[#FFFF5D] font-semibold hover:underline" href="{{ config('company.whatsapp') }}" target="_blank" rel="noopener noreferrer">Prefere conversar agora? Fale com a ATITEC no WhatsApp ({{ config('company.phone') }}) →</a></p>
    <form method="POST" action="{{ route('site.orcamento.store') }}"
          data-budget-estimator
          data-hourly-rate="{{ $orcamento['hourly_rate'] }}"
          data-min-factor="{{ $orcamento['range']['min_factor'] }}"
          data-max-factor="{{ $orcamento['range']['max_factor'] }}"
          data-round-to="{{ $orcamento['range']['round_to'] }}"
          class="bg-[#1A1D21] border border-white/10 rounded-2xl p-6 mt-6 space-y-4">
        @csrf
        @if($errors->any())<div class="bg-red-900/50 border border-red-500/40 text-sm rounded-lg p-3">{{ $errors->first() }}</div>@endif
        @auth
            @if(auth()->user()->client)
                <p class="rounded-lg border border-[#FFFF5D]/20 bg-[#FFFF5D]/5 px-3 py-2 text-xs text-gray-300">Preenchemos seus dados de contato do workspace. Você pode ajustá-los antes de enviar.</p>
            @endif
        @endauth
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Nome*</label><input name="name" required value="{{ old('name', $prefill['name']) }}" autocomplete="name" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
            <div><label class="text-sm font-medium">Empresa</label><input name="company" value="{{ old('company', $prefill['company']) }}" autocomplete="organization" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">E-mail*</label><input name="email" type="email" required value="{{ old('email', $prefill['email']) }}" autocomplete="email" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
            <div><label class="text-sm font-medium">Telefone/WhatsApp</label><input name="phone" value="{{ old('phone', $prefill['phone']) }}" inputmode="tel" maxlength="15" data-mask="phone" placeholder="(00) 00000-0000" autocomplete="tel" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Tipo de projeto*</label>
                <select name="project_type" required class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm">
                    <option value="">Selecione…</option>
                    @foreach($orcamento['types'] as $type => $baseHours)
                        <option value="{{ $type }}" data-hours="{{ $baseHours }}" @selected(old('project_type')===$type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="text-sm font-medium">Prazo desejado</label>
                <select name="deadline" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm">
                    <option value="">Selecione…</option>
                    @foreach($orcamento['deadlines'] as $key => $deadline)
                        <option value="{{ $key }}" data-multiplier="{{ $deadline['multiplier'] }}" @selected(old('deadline')===$key)>{{ $deadline['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">O que o sistema precisa ter?</label>
            <p class="mt-1 text-xs text-gray-500">Marque tudo o que o projeto deve incluir — isso ajuda a ATITEC a entender o escopo e estimar o investimento.</p>
            <div class="mt-2 grid sm:grid-cols-2 gap-2">
                @foreach($orcamento['scope'] as $key => $item)
                    <label class="flex items-start gap-2 rounded-lg border border-white/10 bg-black/20 px-3 py-2 text-sm cursor-pointer hover:border-[#FFFF5D]/30">
                        <input type="checkbox" name="requirements[]" value="{{ $key }}" data-hours="{{ $item['hours'] }}" data-monthly="{{ $item['monthly'] ?? 0 }}" @checked(in_array($key, old('requirements', []), true)) class="mt-0.5 accent-[#FFFF5D]">
                        <span>{{ $item['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Infraestrutura e serviços</label>
            <p class="mt-1 text-xs text-gray-500">Itens cobrados à parte do desenvolvimento — alguns têm custo de implantação ou mensalidade (ex.: servidor dedicado).</p>
            <div class="mt-2 grid sm:grid-cols-2 gap-2">
                @foreach($orcamento['infrastructure'] as $key => $item)
                    <label class="flex items-start gap-2 rounded-lg border border-white/10 bg-black/20 px-3 py-2 text-sm cursor-pointer hover:border-[#FFFF5D]/30">
                        <input type="checkbox" name="infrastructure[]" value="{{ $key }}" data-setup="{{ $item['setup'] ?? 0 }}" data-monthly="{{ $item['monthly'] ?? 0 }}" @checked(in_array($key, old('infrastructure', []), true)) class="mt-0.5 accent-[#FFFF5D]">
                        <span>{{ $item['label'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">Detalhes do projeto*</label>
            <div class="mt-1 rounded-lg border border-white/10 bg-black/40 focus-within:border-[#FFFF5D]/40" data-markdown-editor>
                <div class="flex flex-wrap items-center gap-1 border-b border-white/10 px-2 py-1.5">
                    <button type="button" data-md-action="bold" title="Negrito" class="px-2 py-1 rounded text-xs font-bold text-gray-300 hover:bg-white/10 hover:text-[#FFFF5D]">B</button>
                    <button type="button" data-md-action="italic" title="Itálico" class="px-2 py-1 rounded text-xs italic text-gray-300 hover:bg-white/10 hover:text-[#FFFF5D]">I</button>
                    <button type="button" data-md-action="heading" title="Título de seção" class="px-2 py-1 rounded text-xs font-semibold text-gray-300 hover:bg-white/10 hover:text-[#FFFF5D]">Título</button>
                    <button type="button" data-md-action="ul" title="Lista com marcadores" class="px-2 py-1 rounded text-xs font-semibold text-gray-300 hover:bg-white/10 hover:text-[#FFFF5D]">• Lista</button>
                    <button type="button" data-md-action="ol" title="Lista numerada" class="px-2 py-1 rounded text-xs font-semibold text-gray-300 hover:bg-white/10 hover:text-[#FFFF5D]">1. Lista</button>
                    <button type="button" data-md-action="quote" title="Citação" class="px-2 py-1 rounded text-xs font-semibold text-gray-300 hover:bg-white/10 hover:text-[#FFFF5D]">&gt; Citação</button>
                    <button type="button" data-md-action="code" title="Trecho em destaque (código)" class="px-2 py-1 rounded text-xs font-mono text-gray-300 hover:bg-white/10 hover:text-[#FFFF5D]">código</button>
                    <button type="button" data-md-action="link" title="Inserir link" class="px-2 py-1 rounded text-xs font-semibold text-gray-300 hover:bg-white/10 hover:text-[#FFFF5D]">Link</button>
                </div>
                <textarea name="details" rows="6" required placeholder="O que o sistema deve fazer? Quantos usuários? Tem prazo?" class="w-full bg-transparent px-3 py-2.5 text-sm outline-none resize-y">{{ old('details') }}</textarea>
            </div>
            <p class="mt-1 text-xs text-gray-500">Aceita formatação Markdown: use os botões acima ou escreva <strong>**negrito**</strong>, listas com <strong>-</strong> ou títulos com <strong>##</strong>.</p>
        </div>

        <div data-estimate-panel class="hidden rounded-xl border border-[#FFFF5D]/30 bg-[#FFFF5D]/5 p-4">
            <p class="text-xs uppercase tracking-widest text-[#FFFF5D]">Estimativa preliminar do investimento</p>
            <p class="text-2xl font-extrabold mt-1" data-estimate-range>—</p>
            <p class="text-sm text-gray-300 mt-2" data-estimate-breakdown></p>
            <p class="text-sm text-gray-300 mt-1 hidden" data-estimate-monthly></p>
            <p class="mt-2 text-xs text-gray-500">Valores de referência calculados com hora de desenvolvimento a R$ {{ number_format($orcamento['hourly_rate'], 0, ',', '.') }} e infraestrutura cobrada à parte (um servidor dedicado parte de R$ {{ number_format($orcamento['infrastructure']['servidor']['setup'], 0, ',', '.') }} de implantação). A proposta final é fechada após entendermos o escopo com você.</p>
        </div>

        <div>
            <label class="text-sm font-medium">Faixa de investimento</label>
            <select name="budget_range" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm">
                <option value="">Selecione…</option>
                <option @selected(old('budget_range')==='até R$ 5 mil')>até R$ 5 mil</option>
                <option @selected(old('budget_range')==='R$ 5–15 mil')>R$ 5–15 mil</option>
                <option @selected(old('budget_range')==='R$ 15–50 mil')>R$ 15–50 mil</option>
                <option @selected(old('budget_range')==='acima de R$ 50 mil')>acima de R$ 50 mil</option>
                <option @selected(old('budget_range')==='mensalidade/suporte')>mensalidade/suporte</option>
                <option @selected(old('budget_range')==='Ainda não sei, quero discutir')>Ainda não sei, quero discutir</option>
            </select>
            <p class="mt-1 text-xs text-gray-500" data-budget-hint>Ao marcar as opções acima, as faixas são sugeridas com base na estimativa do seu projeto.</p>
        </div>

        <button class="w-full bg-[#FFFF5D] text-black font-semibold py-3 rounded-lg hover:bg-[#FFD400]">Enviar pedido de orçamento</button>
    </form>
</section>
@endsection
