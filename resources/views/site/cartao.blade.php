@php
    $waUrl = $channels['whatsapp']->url ?? 'https://wa.me/5542999999999';
    $waNumber = $channels['whatsapp']->value ?? '(42) 99999-9999';
    $mailUrl = $channels['email']->url ?? 'mailto:contato@atitec.com.br';
    $mailValue = $channels['email']->value ?? 'contato@atitec.com.br';
    $telUrl = $channels['phone']->url ?? 'tel:+5542999999999';
    $telValue = $channels['phone']->value ?? '(42) 99999-9999';
    $telDigits = preg_replace('/\D/', '', $telUrl);
    $waText = urlencode('Olá '.$card['name'].'! Vi seu cartão digital e quero conversar.');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $card['name'] }} — {{ $card['role'] }} na {{ $card['company'] }}">
    <title>{{ $card['name'] }} — {{ $card['company'] }}</title>
    <link rel="icon" href="{{ asset('images/atitec_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0A0A0B] text-[#F3FFDC] font-[Inter] antialiased min-h-screen">
    <div class="relative overflow-hidden">
        <img src="{{ asset('images/atitec_marca_agua.svg') }}" alt="" aria-hidden="true" class="pointer-events-none select-none absolute -top-24 -right-24 w-[380px] max-w-none opacity-[0.07]">
        <img src="{{ asset('images/atitec_marca_agua.svg') }}" alt="" aria-hidden="true" class="pointer-events-none select-none absolute top-1/2 -left-28 w-[380px] max-w-none opacity-[0.05]">

        <main class="relative max-w-md mx-auto px-5 pt-10 pb-12">
            {{-- Identidade --}}
            <div class="text-center">
                <img src="{{ asset('images/atitec_logo.svg') }}" alt="ATITEC" class="h-32 w-auto mx-auto">
                <img src="{{ asset('images/atila_foto.png') }}" alt="{{ $card['name'] }}" class="w-32 h-32 rounded-full object-cover mx-auto mt-6 ring-4 ring-[#FFFF5D] shadow-[0_0_40px_rgba(255,255,93,0.25)]">
                <h1 class="font-[Montserrat] font-extrabold text-2xl mt-4">{{ $card['name'] }}</h1>
                <p class="inline-block mt-2 text-xs font-semibold tracking-widest uppercase bg-[#FFFF5D] text-black px-3 py-1.5 rounded-full">{{ $card['role'] }}</p>
                <p class="text-sm text-gray-400 mt-3">{{ $card['company'] }}<br>{{ $card['tagline'] }}</p>
            </div>

            @if(session('success'))
                <div class="bg-green-900/60 border border-green-500/40 text-green-100 text-sm px-4 py-3 rounded-xl mt-6">{{ session('success') }}</div>
            @endif

            {{-- Ações diretas --}}
            <div class="grid grid-cols-2 gap-3 mt-8">
                <a href="{{ $waUrl }}?text={{ $waText }}" target="_blank" class="flex items-center gap-3 bg-[#25D366] text-white font-semibold rounded-2xl p-4 hover:brightness-110 active:scale-[0.98] transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/></svg>
                    <span class="text-sm leading-tight">WhatsApp<br><span class="font-normal opacity-80 text-xs">{{ $waNumber }}</span></span>
                </a>
                <a href="{{ $telUrl }}" class="flex items-center gap-3 bg-[#FFFF5D] text-black font-semibold rounded-2xl p-4 hover:bg-[#FFD400] active:scale-[0.98] transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a22 22 0 0 1-7.01-4.42 22 22 0 0 1-4.42-7.009c-.363-1.03-.037-2.137.703-2.877z"/></svg>
                    <span class="text-sm leading-tight">Ligar<br><span class="font-normal opacity-70 text-xs">{{ $telValue }}</span></span>
                </a>
                <a href="{{ $mailUrl }}" class="flex items-center gap-3 bg-white/10 border border-white/15 font-semibold rounded-2xl p-4 hover:border-[#FFFF5D]/60 hover:text-[#FFFF5D] active:scale-[0.98] transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/></svg>
                    <span class="text-sm leading-tight">E-mail<br><span class="font-normal text-gray-400 text-[11px] whitespace-nowrap">{{ $mailValue }}</span></span>
                </a>
                <a href="sms:+{{ $telDigits }}?&body={{ $waText }}" class="flex items-center gap-3 bg-white/10 border border-white/15 font-semibold rounded-2xl p-4 hover:border-[#FFFF5D]/60 hover:text-[#FFFF5D] active:scale-[0.98] transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16"><path d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/></svg>
                    <span class="text-sm leading-tight">Mensagem<br><span class="font-normal text-gray-400 text-xs">SMS / texto</span></span>
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-3">
                <a href="{{ route('site.cartao.vcard') }}" class="text-center text-sm font-semibold border border-white/20 rounded-2xl py-3 hover:border-[#FFFF5D] hover:text-[#FFFF5D] transition">＋ Salvar contato</a>
                <button onclick="if(navigator.share){navigator.share({title:document.title,url:location.href})}else{navigator.clipboard.writeText(location.href);this.textContent='Link copiado ✓';}" class="text-sm font-semibold border border-white/20 rounded-2xl py-3 hover:border-[#FFFF5D] hover:text-[#FFFF5D] transition">⤴ Compartilhar</button>
            </div>

            {{-- Portfólio --}}
            <h2 class="font-[Montserrat] font-bold text-lg mt-10 mb-1">Portfólio de projetos</h2>
            <p class="text-xs text-gray-500 mb-4">Sistemas em produção pela ATITEC — toque para ver o case.</p>
            <div class="space-y-2.5">
                @foreach($projects as $p)
                <a href="{{ route('site.case', $p->slug) }}" class="flex items-center justify-between gap-3 bg-white/5 border border-white/10 rounded-2xl px-4 py-3.5 hover:border-[#FFFF5D]/60 active:scale-[0.99] transition">
                    <span>
                        <span class="block font-semibold text-sm">{{ $p->title }}</span>
                        <span class="block text-[11px] text-gray-500 uppercase tracking-wider mt-0.5">{{ $p->segment }}</span>
                    </span>
                    <span class="text-[#FFFF5D] shrink-0">→</span>
                </a>
                @endforeach
            </div>

            {{-- Deixe seu contato --}}
            <h2 class="font-[Montserrat] font-bold text-lg mt-10 mb-1">Deixe seu contato</h2>
            <p class="text-xs text-gray-500 mb-4">Preencha e eu retorno em breve.</p>
            <form method="POST" action="{{ route('site.cartao.contato') }}" class="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-3.5">
                @csrf
                @if($errors->any())<div class="bg-red-900/50 border border-red-500/40 text-sm rounded-lg p-3">{{ $errors->first() }}</div>@endif
                <div><label class="text-sm font-medium">Nome*</label><input name="name" required value="{{ old('name') }}" placeholder="Seu nome" class="mt-1 w-full bg-black/40 border border-white/10 rounded-xl px-3.5 py-3 text-sm"></div>
                <div><label class="text-sm font-medium">Telefone / WhatsApp*</label><input name="phone" required value="{{ old('phone') }}" placeholder="(00) 90000-0000" class="mt-1 w-full bg-black/40 border border-white/10 rounded-xl px-3.5 py-3 text-sm"></div>
                <div><label class="text-sm font-medium">E-mail</label><input name="email" type="email" value="{{ old('email') }}" placeholder="voce@email.com" class="mt-1 w-full bg-black/40 border border-white/10 rounded-xl px-3.5 py-3 text-sm"></div>
                <div><label class="text-sm font-medium">Mensagem</label><textarea name="details" rows="3" placeholder="Como posso ajudar?" class="mt-1 w-full bg-black/40 border border-white/10 rounded-xl px-3.5 py-3 text-sm">{{ old('details') }}</textarea></div>
                <button class="w-full bg-[#FFFF5D] text-black font-bold py-3.5 rounded-xl hover:bg-[#FFD400] active:scale-[0.99] transition">Enviar contato</button>
            </form>

            <div class="text-center mt-8">
                <a href="{{ route('site.home') }}" class="text-xs text-gray-500 hover:text-[#FFFF5D]">atitec.com.br — site institucional</a>
            </div>
        </main>
    </div>
</body>
</html>
