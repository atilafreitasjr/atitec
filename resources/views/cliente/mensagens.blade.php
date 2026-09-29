<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Mensagens</h2></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if(session('success'))<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">{{ $errors->first() }}</div>@endif

        @if($broadcasts->count())
        <div class="bg-blue-50 border border-blue-200 p-5 rounded-xl">
            <h3 class="font-bold mb-2">Comunicados gerais da ATITEC</h3>
            @foreach($broadcasts as $b)
                @foreach($b->messages as $m)
                    <p class="text-sm py-1"><strong>{{ $b->subject }}:</strong> {{ $m->body }}</p>
                @endforeach
            @endforeach
        </div>
        @endif

        <div class="grid md:grid-cols-3 gap-4">
            {{-- Lista de conversas --}}
            <div class="bg-white p-5 rounded-xl shadow">
                <h3 class="font-bold mb-3">Minhas conversas</h3>
                <div class="space-y-2">
                    @forelse($conversations as $c)
                        @php
                            $naoLidas = $c->messages->whereNull('read_at')->where('user_id', '!=', auth()->id())->count();
                            $ativa = $selected && $selected->id === $c->id;
                        @endphp
                        <a href="{{ route('cliente.mensagens', ['conversation' => $c->id]) }}"
                           class="block border rounded-lg p-3 {{ $ativa ? 'border-indigo-500 bg-indigo-50' : 'hover:border-indigo-300' }}">
                            <span class="flex justify-between items-center gap-2">
                                <span class="text-sm font-medium">{{ $c->subject }}</span>
                                @if($naoLidas > 0)<span class="text-xs bg-red-500 text-white rounded-full px-2 py-0.5">{{ $naoLidas }}</span>@endif
                            </span>
                            <span class="block text-xs text-gray-500 mt-1">
                                {{ $c->messages->last()?->created_at?->format('d/m/Y H:i') ?? $c->created_at->format('d/m/Y') }}
                                @if($c->closed_at) · encerrada @endif
                            </span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500">Nenhuma conversa ainda.</p>
                    @endforelse
                </div>

                <hr class="my-4">
                <h3 class="font-bold mb-2">Nova conversa</h3>
                <form method="POST" action="{{ route('cliente.mensagens.store') }}" class="space-y-2">
                    @csrf
                    <input name="subject" placeholder="Assunto" class="w-full border rounded px-3 py-2 text-sm" required>
                    <textarea name="body" rows="3" placeholder="Escreva sua mensagem..." class="w-full border rounded px-3 py-2 text-sm" required></textarea>
                    <button class="bg-indigo-600 text-white px-4 py-2 rounded text-sm w-full">Iniciar conversa</button>
                </form>
            </div>

            {{-- Conversa selecionada --}}
            <div class="md:col-span-2 bg-white p-5 rounded-xl shadow flex flex-col">
                @if($selected)
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold">{{ $selected->subject }}</h3>
                        @if($selected->closed_at)<span class="text-xs bg-gray-200 text-gray-700 rounded-full px-2 py-0.5">encerrada</span>@endif
                    </div>
                    <div class="space-y-3 flex-1 overflow-y-auto mb-4" style="max-height:45vh">
                        @foreach($selected->messages as $m)
                            @php $mine = $m->user_id === auth()->id(); @endphp
                            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                                <div class="p-3 rounded-2xl text-sm {{ $mine ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800' }}" style="max-width:80%">
                                    <p class="text-xs opacity-75 mb-1">{{ $mine ? 'Você' : 'ATITEC' }} · {{ $m->created_at->format('d/m/Y H:i') }}</p>
                                    <p style="white-space:pre-wrap">{{ $m->body }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($selected->closed_at)
                        <p class="text-sm text-gray-500">Esta conversa foi encerrada pela ATITEC. Inicie uma nova conversa se precisar.</p>
                    @else
                        <form method="POST" action="{{ route('cliente.mensagens.store') }}" class="flex gap-2">
                            @csrf
                            <input type="hidden" name="conversation_id" value="{{ $selected->id }}">
                            <textarea name="body" rows="2" placeholder="Responder..." class="flex-1 border rounded px-3 py-2 text-sm" required></textarea>
                            <button class="bg-indigo-600 text-white px-4 py-2 rounded text-sm self-end">Enviar</button>
                        </form>
                    @endif
                @else
                    <p class="text-sm text-gray-500">Selecione uma conversa ou inicie uma nova.</p>
                @endif
            </div>
        </div>
    </div></div>
</x-app-layout>
