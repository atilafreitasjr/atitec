@extends('adminlte::page')

@section('title', 'Mensagens')

@section('content_header')
    <div><p class="text-uppercase small text-warning fw-bold mb-1" style="letter-spacing:.16em">Workspace ATITEC</p><h1 class="mb-0">Mensagens</h1></div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Atenção">{{ $errors->first() }}</x-adminlte-alert>@endif

    @if($broadcasts->count())
        <x-adminlte-card title="Comunicados gerais da ATITEC" theme="info" icon="bi bi-megaphone">
            @foreach($broadcasts as $b)
                @foreach($b->messages as $m)
                    <p class="mb-1"><strong>{{ $b->subject }}:</strong> {{ $m->body }}</p>
                @endforeach
            @endforeach
        </x-adminlte-card>
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <x-adminlte-card title="Minhas conversas" icon="bi bi-chat-dots">
                <div class="list-group list-group-flush">
                    @forelse($conversations as $c)
                        @php
                            $naoLidas = $c->messages->whereNull('read_at')->where('user_id', '!=', auth()->id())->count();
                            $ativa = $selected && $selected->id === $c->id;
                        @endphp
                        <a href="{{ route('cliente.mensagens', ['conversation' => $c->id]) }}"
                           class="list-group-item list-group-item-action px-2 {{ $ativa ? 'active' : '' }}">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="fw-semibold">{{ $c->subject }}</span>
                                @if($naoLidas > 0)<span class="badge text-bg-danger">{{ $naoLidas }}</span>@endif
                            </div>
                            <small class="{{ $ativa ? '' : 'text-muted' }}">
                                {{ $c->messages->last()?->created_at?->format('d/m/Y H:i') ?? $c->created_at->format('d/m/Y') }}
                                @if($c->closed_at) · encerrada @endif
                            </small>
                        </a>
                    @empty
                        <p class="text-muted mb-0">Nenhuma conversa ainda.</p>
                    @endforelse
                </div>

                <hr>
                <h5>Nova conversa</h5>
                <form method="POST" action="{{ route('cliente.mensagens.store') }}">
                    @csrf
                    <div class="mb-2"><input name="subject" placeholder="Assunto" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><textarea name="body" rows="3" placeholder="Escreva sua mensagem..." class="form-control form-control-sm" required></textarea></div>
                    <button class="btn btn-primary btn-sm w-100">Iniciar conversa</button>
                </form>
            </x-adminlte-card>
        </div>

        <div class="col-md-8">
            <x-adminlte-card>
                @if($selected)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">{{ $selected->subject }}</h4>
                        @if($selected->closed_at)<span class="badge text-bg-secondary">encerrada</span>@endif
                    </div>
                    <div class="overflow-auto mb-3 pe-1" style="max-height:45vh">
                        @foreach($selected->messages as $m)
                            @php $mine = $m->user_id === auth()->id(); @endphp
                            <div class="d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }} mb-2">
                                <div class="chat-bubble {{ $mine ? 'chat-bubble-own' : 'chat-bubble-other' }} rounded p-2 px-3">
                                    <small class="d-block text-muted">{{ $mine ? 'Você' : 'ATITEC' }} · {{ $m->created_at->format('d/m/Y H:i') }}</small>
                                    <span style="white-space:pre-wrap">{{ $m->body }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($selected->closed_at)
                        <p class="text-muted mb-0">Esta conversa foi encerrada pela ATITEC. Inicie uma nova conversa se precisar.</p>
                    @else
                        <form method="POST" action="{{ route('cliente.mensagens.store') }}" class="d-flex gap-2">
                            @csrf
                            <input type="hidden" name="conversation_id" value="{{ $selected->id }}">
                            <textarea name="body" rows="2" placeholder="Responder..." class="form-control" required></textarea>
                            <button class="btn btn-primary align-self-end">Enviar</button>
                        </form>
                    @endif
                @else
                    <p class="text-muted mb-0">Selecione uma conversa ou inicie uma nova.</p>
                @endif
            </x-adminlte-card>
        </div>
    </div>
@stop

@push('css')
    <style>
        .chat-bubble { max-width: 80%; border: 1px solid #303136; }
        .chat-bubble-own { background: rgba(255, 255, 93, .12); border-color: rgba(255, 255, 93, .35); }
        .chat-bubble-other { background: #1c1d20; }
    </style>
@endpush
