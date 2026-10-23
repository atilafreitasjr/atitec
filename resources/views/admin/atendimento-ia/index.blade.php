@extends('adminlte::page')

@section('title', 'Atendimento IA')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <h1 class="mb-0">Atendimento IA</h1>
            <p class="text-muted mb-0 mt-1">Dúvidas que o assistente do site encaminhou para a equipe.</p>
        </div>
        @can('role.view')
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-shield-lock me-1"></i> Configurar responsáveis
            </a>
        @endcan
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Atenção">{{ $errors->first() }}</x-adminlte-alert>@endif

    @if(! \App\Services\DeepSeekService::configured())
        <x-adminlte-alert theme="warning" title="IA não configurada">
            Defina <code>DEEPSEEK_API_KEY</code> no <code>.env</code> e rode <code>php artisan config:cache</code> em produção.
            Enquanto isso, o widget do site informa que o atendimento por IA está indisponível e sugere WhatsApp/e-mail.
        </x-adminlte-alert>
    @endif

    <div class="row">
        <div class="col-lg-3 col-6"><x-adminlte-info-box title="Aguardando" text="{{ $counts['encaminhado'] }}" icon="bi bi-hourglass-split" theme="warning"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-info-box title="Em atendimento" text="{{ $counts['em_atendimento'] }}" icon="bi bi-person-workspace" theme="primary"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-info-box title="Respondidos" text="{{ $counts['respondido'] }}" icon="bi bi-check2-circle" theme="success"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-info-box title="Total" text="{{ $counts['total'] }}" icon="bi bi-robot" theme="secondary"/></div>
    </div>

    <x-adminlte-card title="Dúvidas encaminhadas" icon="bi bi-robot">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nome, e-mail, telefone ou resumo...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    @foreach($statuses as $key => $label)
                        @if($key !== 'bot')
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i> Filtrar</button>
                <a href="{{ route('admin.atendimento-ia.index') }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Protocolo</th>
                        <th>Visitante</th>
                        <th>Dúvida</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>Última mensagem</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conversation)
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
                        <tr>
                            <td class="fw-semibold">{{ $conversation->protocol }}</td>
                            <td>
                                <div class="fw-semibold">{{ $conversation->visitor_name ?: 'Visitante anônimo' }}</div>
                                <small class="text-muted">{{ $conversation->visitor_email ?: 'Sem e-mail informado' }}</small>
                                @if($conversation->visitor_phone)<small class="d-block text-muted">{{ $conversation->visitor_phone }}</small>@endif
                            </td>
                            <td style="max-width:320px">
                                <span class="d-block text-truncate">{{ $conversation->summary ?: '—' }}</span>
                                <small class="text-muted">
                                    {{ \App\Models\AiConversation::CATEGORIES[$conversation->category] ?? 'Sem categoria' }}
                                    · {{ $conversation->messages_count }} mensagem(ns)
                                </small>
                            </td>
                            <td><span class="badge text-bg-{{ $urgencyTheme }}">{{ \App\Models\AiConversation::URGENCIES[$conversation->urgency] ?? 'Normal' }}</span></td>
                            <td><span class="badge text-bg-{{ $statusTheme }}">{{ $statuses[$conversation->status] ?? $conversation->status }}</span></td>
                            <td class="small text-muted">{{ $conversation->last_message_at?->format('d/m/Y H:i') ?? $conversation->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.atendimento-ia.show', $conversation) }}" class="btn btn-sm btn-primary">
                                    Atender
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Nenhuma dúvida encaminhada pela IA até agora.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $conversations->links() }}</div>
    </x-adminlte-card>
@stop
