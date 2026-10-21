@extends('layouts.site')
@section('title', 'Contato — ATITEC')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8 grid gap-10 lg:grid-cols-2">
    <div>
        <p class="text-[#FFFF5D] font-semibold tracking-widest text-xs uppercase">Contato</p>
        <h1 class="font-[Montserrat] font-extrabold text-4xl mt-2">Fale com a ATITEC</h1>
        <p class="text-gray-400 mt-3">Escolha o canal ou envie a mensagem — respondemos rápido em horário comercial.</p>
        <ul class="space-y-3 mt-6">
            @foreach($channels as $c)
            <li><a href="{{ $c->url ?? '#' }}" @if($c->url) target="_blank" rel="noopener noreferrer" @endif class="flex items-center justify-between bg-white/5 border border-white/10 rounded-xl px-4 py-3 hover:border-[#FFFF5D]/60"><span><strong>{{ $c->label }}:</strong> {{ $c->value }}</span><span class="text-[#FFFF5D]">→</span></a></li>
            @endforeach
        </ul>
        <p class="mt-4 text-xs text-gray-500">{{ config('company.legal_name') }} · CNPJ {{ config('company.cnpj') }} · {{ config('company.address') }} — {{ config('company.neighborhood') }}, {{ config('company.city') }}/{{ config('company.state') }}</p>
    </div>
    <form method="POST" action="{{ route('site.contato.store') }}" class="bg-[#1A1D21] border border-white/10 rounded-2xl p-6 space-y-4">
        @csrf
        @if($errors->any())<div class="bg-red-900/50 border border-red-500/40 text-sm rounded-lg p-3">{{ $errors->first() }}</div>@endif
        @auth
            @if(auth()->user()->client)<p class="rounded-lg border border-[#FFFF5D]/20 bg-[#FFFF5D]/5 px-3 py-2 text-xs text-gray-300">Seus dados do workspace já foram preenchidos. Você pode editá-los se necessário.</p>@endif
        @endauth
        <div><label class="text-sm font-medium">Nome*</label><input name="name" required value="{{ old('name', $prefill['name']) }}" autocomplete="name" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">E-mail*</label><input name="email" type="email" required value="{{ old('email', $prefill['email']) }}" autocomplete="email" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
            <div><label class="text-sm font-medium">Telefone/WhatsApp</label><input name="phone" value="{{ old('phone', $prefill['phone']) }}" inputmode="tel" maxlength="15" data-mask="phone" placeholder="(00) 00000-0000" autocomplete="tel" class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm"></div>
        </div>
        <div><label class="text-sm font-medium">Mensagem*</label><textarea name="details" rows="5" required class="mt-1 w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2.5 text-sm">{{ old('details') }}</textarea></div>
        <button class="w-full bg-[#FFFF5D] text-black font-semibold py-3 rounded-lg hover:bg-[#FFD400]">Enviar mensagem</button>
    </form>
</section>
@endsection
