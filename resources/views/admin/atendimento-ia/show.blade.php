@extends('adminlte::page')

@section('title', 'Atendimento '.$conversation->protocol)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h1 class="mb-0">Atendimento {{ $conversation->protocol }}</h1>
            <p class="text-muted mb-0 mt-1">Conversa registrada pelo assistente virtual do site.</p>
        </div>
        <a href="{{ route('admin.atendimento-ia.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Atenção">{{ $errors->first() }}</x-adminlte-alert>@endif

    @php
        $statusTheme = match($conversation->status) {
            'encaminhado' => 'warning',
            'em_atendimento' => 'primary',
            'respondido' => 'success',
            default => 'secondary',
        };
        $urgencyTheme = match($conversation->urgency) {
            'alta' => 'danger',
            'baixa' => 'secondary',
            default => 'warning',
        };
    @endphp

    <div class="row">
        <div class="col-lg-8">
            <x-adminlte-card title="Conversa" icon="bi bi-chat-dots">
                <div id="ia-transcript" style="max-height:60vh;overflow-y:auto">
                    @forelse($conversation->messages as $message)
                        @php
                            $isVisitor = $message->role === 'user';
                            $isTeam = $message->role === 'human';
                            $bubble = $isVisitor ? 'bg-body-secondary' : ($isTeam ? 'bg-success-subtle border border-success-subtle' : 'bg-warning-subtle border border-warning-subtle');
                            $author = \App\Models\AiMessage::ROLES[$message->role] ?? 'Atendente';
                        @endphp
                        <div class="d-flex mb-3 {{ $isVisitor ? '' : 'justify-content-end' }}">
                            <div class="p-3 rounded-3 {{ $bubble }}" style="max-width:82%">
                                <p class="mb-1 small fw-bold">
                                    {{ $author }}
                                    <span class="fw-normal text-muted">· {{ $message->created_at->format('d/m/Y H:i') }}</span>
                                    @if($isTeam && $message->user)
                                        <span class="fw-normal text-muted">· {{ $message->user->name }}</span>
                                    @endif
                                </p>
                                <div class="small ia-admin-markdown">{!! \App\Support\Markdown::render($message->content) !!}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nenhuma mensagem registrada.</p>
                    @endforelse
                </div>
            </x-adminlte-card>

            @can('ai_support.reply')
                <x-adminlte-card title="Responder ao visitante" icon="bi bi-reply">
                    <p class="text-muted small">
                        A resposta aparece no chat do site para este visitante e o atendimento fica marcado como respondido.
                        O contato também pode ser feito por e-mail ou telefone, se informados.
                    </p>
                    <form method="POST" action="{{ route('admin.atendimento-ia.reply', $conversation) }}">
                        @csrf
                        <x-adminlte-textarea name="body" rows="4" required placeholder="Escreva a resposta para o visitante..."/>
                        <x-adminlte-button label="Enviar resposta" theme="primary" type="submit" icon="bi bi-send"/>
                    </form>
                </x-adminlte-card>
            @endcan
        </div>

        <div class="col-lg-4">
            <x-adminlte-card title="Contato do visitante" icon="bi bi-person-lines-fill">
                @if($conversation->visitor_name || $conversation->visitor_email || $conversation->visitor_phone)
                    <dl class="mb-0">
                        <dt>Nome</dt>
                        <dd>{{ $conversation->visitor_name ?: 'Não informado' }}</dd>
                        <dt>E-mail</dt>
                        <dd>
                            @if($conversation->visitor_email)
                                <a href="mailto:{{ $conversation->visitor_email }}">{{ $conversation->visitor_email }}</a>
                            @else
                                Não informado
                            @endif
                        </dd>
                        <dt>Telefone / WhatsApp</dt>
                        <dd class="mb-0">
                            @if($conversation->visitor_phone)
                                <a href="{{ $conversation->whatsappUrl() }}" target="_blank" rel="noopener noreferrer">{{ $conversation->visitor_phone }}</a>
                            @else
                                Não informado
                            @endif
                        </dd>
                    </dl>
                @else
                    <x-adminlte-alert theme="warning" title="Sem contato" class="mb-0">
                        O visitante não informou nome, e-mail ou telefone. A conversa fica disponível no site caso ele retorne.
                    </x-adminlte-alert>
                @endif
            </x-adminlte-card>

            <x-adminlte-card title="Resumo do encaminhamento" icon="bi bi-clipboard-data">
                <div class="mb-2">
                    <span class="badge text-bg-{{ $statusTheme }}">{{ $statuses[$conversation->status] ?? $conversation->status }}</span>
                    <span class="badge text-bg-{{ $urgencyTheme }}">Urgência: {{ \App\Models\AiConversation::URGENCIES[$conversation->urgency] ?? 'Normal' }}</span>
                </div>
                <p class="mb-3">{{ $conversation->summary ?: 'Sem resumo gerado.' }}</p>
                <dl class="small mb-0">
                    <dt>Categoria</dt>
                    <dd>{{ \App\Models\AiConversation::CATEGORIES[$conversation->category] ?? 'Não informada' }}</dd>
                    <dt>Encaminhada em</dt>
                    <dd>{{ $conversation->escalated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    <dt>Responsável</dt>
                    <dd class="mb-0">{{ $conversation->handledBy?->name ?? 'Ainda não assumido' }}</dd>
                </dl>
            </x-adminlte-card>

            @can('ai_support.reply')
                <x-adminlte-card title="Status do atendimento" icon="bi bi-flag">
                    <div class="d-flex flex-wrap gap-2">
                        @if($conversation->status !== 'em_atendimento')
                            <form method="POST" action="{{ route('admin.atendimento-ia.status', $conversation) }}">
                                @csrf
                                <input type="hidden" name="status" value="em_atendimento">
                                <button class="btn btn-sm btn-primary"><i class="bi bi-person-check me-1"></i> Assumir atendimento</button>
                            </form>
                        @endif

                        @if($conversation->status !== 'encaminhado')
                            <form method="POST" action="{{ route('admin.atendimento-ia.status', $conversation) }}">
                                @csrf
                                <input type="hidden" name="status" value="encaminhado">
                                <button class="btn btn-sm btn-outline-warning"><i class="bi bi-hourglass-split me-1"></i> Devolver para a fila</button>
                            </form>
                        @endif

                        @if($conversation->status !== 'encerrado')
                            <form method="POST" action="{{ route('admin.atendimento-ia.status', $conversation) }}"
                                  onsubmit="return confirm('Encerrar este atendimento?')">
                                @csrf
                                <input type="hidden" name="status" value="encerrado">
                                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-check2-circle me-1"></i> Encerrar</button>
                            </form>
                        @endif
                    </div>
                </x-adminlte-card>
            @endcan
        </div>
    </div>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const transcript = document.getElementById('ia-transcript');
            if (transcript) transcript.scrollTop = transcript.scrollHeight;
        });
    </script>
@stop
