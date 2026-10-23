@php
    $iaUser = auth()->user();
    $iaClient = $iaUser?->client;
    $iaPrefill = [
        'name' => $iaClient?->name ?? $iaUser?->name ?? '',
        'email' => $iaClient?->email ?? $iaUser?->email ?? '',
        'phone' => $iaClient?->phone ?? '',
    ];
    $iaQuickReplies = config('atendimento_ia.quick_replies', []);
@endphp

<div data-atendimento-ia
     data-message-url="{{ route('atendimento-ia.mensagem') }}"
     data-contact-url="{{ route('atendimento-ia.contato') }}"
     data-conversation-base="{{ url('/atendimento-ia/conversa') }}"
     data-welcome="{{ config('atendimento_ia.welcome') }}"
     data-disclaimer="{{ config('atendimento_ia.disclaimer') }}"
     data-quick-replies="{{ json_encode($iaQuickReplies) }}"
     data-prefill="{{ json_encode($iaPrefill) }}"
     data-max-length="{{ (int) config('atendimento_ia.max_message_length', 2000) }}"
     data-whatsapp="{{ config('company.phone') }}"
     data-protocol-label="Protocolo">

    {{-- Botão flutuante: animado quando o chat está fechado. --}}
    <button type="button" data-ia-launcher
            class="fixed bottom-5 right-24 z-[60] flex h-16 w-16 items-center justify-center rounded-full border-2 border-[#FFFF5D] bg-[#101113] shadow-[0_12px_38px_rgba(255,255,93,.32)] transition hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FFFF5D] focus-visible:ring-offset-2 focus-visible:ring-offset-black"
            aria-label="Abrir atendimento virtual da ATITEC" aria-expanded="false" title="Atendimento virtual ATITEC">
        <img data-ia-avatar-idle src="{{ asset('images/atendente-ia-animado.svg') }}" alt="" class="h-14 w-14 object-contain" aria-hidden="true">
        <img data-ia-avatar-active src="{{ asset('images/atendente-ia-estatico.svg') }}" alt="" class="hidden h-14 w-14 object-contain" aria-hidden="true">
        <span data-ia-ping class="absolute -right-0.5 -top-0.5 flex h-3.5 w-3.5">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#FFFF5D] opacity-70"></span>
            <span class="relative inline-flex h-3.5 w-3.5 rounded-full border border-black bg-[#FFFF5D]"></span>
        </span>
    </button>

    {{-- Painel do chat --}}
    <section data-ia-panel
             class="fixed bottom-24 right-4 z-[60] hidden w-[calc(100vw-2rem)] max-w-[24rem] overflow-hidden rounded-2xl border border-white/15 bg-[#0F1113] text-[#F3FFDC] shadow-2xl sm:right-5"
             role="dialog" aria-modal="false" aria-label="Atendimento virtual ATITEC">
        <header class="flex items-center gap-3 border-b border-white/10 bg-[#15181B] px-4 py-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-[#FFFF5D]/50 bg-black/40">
                <img src="{{ asset('images/atendente-ia-estatico.svg') }}" alt="Atendente virtual da ATITEC" class="h-11 w-11 object-contain">
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-[Montserrat] text-sm font-bold leading-tight text-white">Atendente ATITEC</p>
                <p data-ia-status-label class="mt-0.5 truncate text-[11px] font-medium text-[#FFFF5D]">Assistente virtual · online</p>
            </div>
            <button type="button" data-ia-close
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FFFF5D]"
                    aria-label="Fechar atendimento">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>
            </button>
        </header>

        <div data-ia-messages class="space-y-3 overflow-y-auto overflow-x-hidden bg-[#0A0A0B] px-3 py-4" aria-live="polite" aria-relevant="additions"></div>

        <div data-ia-contact class="hidden border-t border-[#FFFF5D]/25 bg-[#15181B] px-4 py-3">
            <p class="text-xs font-semibold text-[#FFFF5D]">Para retornarmos, informe seu contato</p>
            <p data-ia-contact-intro class="mt-1 text-[11px] leading-relaxed text-gray-400">A equipe da ATITEC foi avisada sobre sua dúvida.</p>
            <form data-ia-contact-form class="mt-2 space-y-2">
                <input name="name" required maxlength="120" autocomplete="name" placeholder="Seu nome*"
                       value="{{ $iaPrefill['name'] }}"
                       class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 text-sm text-white placeholder:text-gray-500 focus:border-[#FFFF5D]/60 focus:outline-none">
                <input name="email" type="email" required maxlength="160" autocomplete="email" placeholder="Seu e-mail*"
                       value="{{ $iaPrefill['email'] }}"
                       class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 text-sm text-white placeholder:text-gray-500 focus:border-[#FFFF5D]/60 focus:outline-none">
                <input name="phone" maxlength="15" inputmode="tel" autocomplete="tel" data-mask="phone" placeholder="(00) 00000-0000 (opcional)"
                       value="{{ $iaPrefill['phone'] }}"
                       class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 text-sm text-white placeholder:text-gray-500 focus:border-[#FFFF5D]/60 focus:outline-none">
                <button type="submit" data-ia-contact-submit
                        class="w-full rounded-lg bg-[#FFFF5D] px-3 py-2 text-sm font-semibold text-black transition hover:bg-[#FFD400] disabled:cursor-not-allowed disabled:opacity-60">
                    Enviar para a equipe ATITEC
                </button>
                <button type="button" data-ia-contact-skip class="w-full rounded-lg px-3 py-1 text-[11px] font-medium text-gray-400 transition hover:text-white">
                    Continuar conversando
                </button>
            </form>
        </div>

        <div data-ia-typing class="hidden items-center gap-1.5 border-t border-white/5 px-4 py-2 text-[11px] text-gray-400">
            <span>Atendente digitando</span>
            <span class="h-1 w-1 animate-bounce rounded-full bg-[#FFFF5D] [animation-delay:-.2s]"></span>
            <span class="h-1 w-1 animate-bounce rounded-full bg-[#FFFF5D] [animation-delay:-.1s]"></span>
            <span class="h-1 w-1 animate-bounce rounded-full bg-[#FFFF5D]"></span>
        </div>

        <form data-ia-form class="border-t border-white/10 bg-[#15181B] p-3">
            <div class="flex items-end gap-2">
                <label for="atendimento-ia-input" class="sr-only">Escreva sua dúvida</label>
                <textarea id="atendimento-ia-input" data-ia-input rows="1" maxlength="{{ (int) config('atendimento_ia.max_message_length', 2000) }}"
                          placeholder="Escreva sua dúvida..."
                          class="max-h-28 min-h-[2.5rem] w-full resize-none rounded-xl border border-white/10 bg-black/40 px-3 py-2 text-sm text-white placeholder:text-gray-500 focus:border-[#FFFF5D]/60 focus:outline-none"></textarea>
                <button type="submit" data-ia-send
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#FFFF5D] text-black transition hover:bg-[#FFD400] disabled:cursor-not-allowed disabled:opacity-50"
                        aria-label="Enviar mensagem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/></svg>
                </button>
            </div>
            <p class="mt-2 text-[10px] leading-snug text-gray-500">{{ config('atendimento_ia.disclaimer') }}</p>
        </form>
    </section>
</div>
