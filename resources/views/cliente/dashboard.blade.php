@extends('adminlte::page')

@section('title', 'Meu painel')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <p class="text-uppercase small text-warning fw-bold mb-1" style="letter-spacing:.16em">Workspace ATITEC</p>
            <h1 class="mb-0">Olá, {{ auth()->user()->name }}</h1>
        </div>
        <span class="badge text-bg-warning fs-6">Área do cliente</span>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif

    <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('cliente.projetos') }}" class="small-box text-decoration-none d-block">
                <div class="inner"><h3>{{ $projects->count() }}</h3><p>Meus projetos</p></div>
                <div class="icon"><i class="bi bi-kanban"></i></div>
                <span class="small-box-footer">Acompanhar projetos <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('cliente.financeiro') }}" class="small-box text-decoration-none d-block">
                <div class="inner"><h3>R$ {{ number_format($openBalance, 2, ',', '.') }}</h3><p>Saldo em aberto</p></div>
                <div class="icon"><i class="bi bi-cash-stack"></i></div>
                <span class="small-box-footer">Ver cobranças <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('cliente.orcamentos') }}" class="small-box text-decoration-none d-block">
                <div class="inner"><h3>{{ $pendingProposals }}</h3><p>Propostas aguardando resposta</p></div>
                <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
                <span class="small-box-footer">Ver orçamentos <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route('cliente.mensagens') }}" class="small-box text-decoration-none d-block">
                <div class="inner"><h3>{{ $conversations->count() }}</h3><p>Conversas recentes</p></div>
                <div class="icon"><i class="bi bi-chat-dots"></i></div>
                <span class="small-box-footer">Abrir mensagens <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>

    @if($proposals->isNotEmpty())
        <x-adminlte-card title="Propostas aguardando sua resposta" theme="warning" icon="bi bi-file-earmark-text">
            <div class="list-group list-group-flush">
                @foreach($proposals as $proposal)
                    <a href="{{ route('cliente.orcamentos') }}" class="list-group-item list-group-item-action px-0">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <span class="fw-semibold">{{ $proposal->proposal_title }}</span>
                            <strong>R$ {{ number_format((float) $proposal->proposal_amount, 2, ',', '.') }}</strong>
                        </div>
                        <small class="text-muted">{{ $proposal->project_type }} · {{ $proposal->proposal_valid_until ? 'Válida até '.$proposal->proposal_valid_until->format('d/m/Y') : 'Sem validade definida' }}</small>
                    </a>
                @endforeach
            </div>
            <a href="{{ route('cliente.orcamentos') }}" class="small">Ver todas as propostas →</a>
        </x-adminlte-card>
    @endif

    <x-adminlte-card title="Seus projetos" icon="bi bi-kanban">
        <p class="text-muted small">Progresso, entregas, custos realizados e documentos de cada projeto.</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Projeto</th><th>Status</th><th>Progresso</th><th class="text-end">Ações</th></tr></thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('cliente.projeto', $project->id) }}" class="fw-semibold">{{ $project->title }}</a>
                                <small class="d-block text-muted">Atualizado {{ $project->updated_at->diffForHumans() }}</small>
                            </td>
                            <td><span class="badge text-bg-secondary">{{ \App\Models\Project::STATUSES[$project->status] ?? str_replace('_', ' ', ucfirst($project->status)) }}</span></td>
                            <td style="min-width:160px">
                                <div class="progress"><div class="progress-bar" style="width:{{ min(100, max(0, $project->progress)) }}%">{{ $project->progress }}%</div></div>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('cliente.projeto', $project->id) }}" class="btn btn-sm btn-outline-warning">Abrir</a>
                                <a href="{{ route('cliente.kanban', $project->id) }}" class="btn btn-sm btn-outline-secondary">Kanban</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Ainda não há projetos associados ao seu workspace.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-adminlte-card>
@stop
