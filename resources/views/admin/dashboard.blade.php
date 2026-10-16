@extends('adminlte::page')

@section('title', 'Visão geral')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div><p class="text-uppercase small text-warning fw-bold mb-1" style="letter-spacing:.16em">Central de operação</p><h1 class="mb-0">Visão geral</h1><p class="text-muted mb-0 mt-1">Projetos, relacionamento e saúde financeira da ATITEC.</p></div>
        <div class="d-flex flex-wrap gap-2">
            @can('lead.view')<a href="{{ route('admin.leads.index') }}" class="btn btn-outline-primary"><i class="bi bi-inbox me-1"></i> Orçamentos</a>@endcan
            @can('project.create')<a href="{{ route('admin.projects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Novo projeto</a>@endcan
        </div>
    </div>
@stop

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3"><div class="info-box"><span class="info-box-icon text-bg-primary"><i class="bi bi-kanban"></i></span><div class="info-box-content"><span class="info-box-text">Projetos ativos</span><span class="info-box-number">{{ $activeProjects }}</span><a href="{{ route('admin.projects.index') }}" class="small">Abrir projetos →</a></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="info-box"><span class="info-box-icon text-bg-warning"><i class="bi bi-cash-stack"></i></span><div class="info-box-content"><span class="info-box-text">Saldo a receber</span><span class="info-box-number">R$ {{ number_format($openBalance, 2, ',', '.') }}</span><a href="{{ route('admin.invoices.index') }}" class="small">Ver cobranças →</a></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="info-box"><span class="info-box-icon text-bg-success"><i class="bi bi-graph-up-arrow"></i></span><div class="info-box-content"><span class="info-box-text">Recebido neste mês</span><span class="info-box-number">R$ {{ number_format($receivedThisMonth, 2, ',', '.') }}</span><span class="small text-muted">Pagamentos registrados</span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="info-box"><span class="info-box-icon text-bg-danger"><i class="bi bi-calendar-x"></i></span><div class="info-box-content"><span class="info-box-text">Cobranças vencidas</span><span class="info-box-number">{{ $overdueInvoices }}</span><span class="small text-muted">De {{ $openInvoices }} em aberto</span></div></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3"><div class="small-box"><div class="inner"><h3>{{ $tasksReadyToBill ? 'R$ '.number_format($tasksReadyToBill, 2, ',', '.') : 'R$ 0,00' }}</h3><p>Tarefas prontas para faturar</p></div><div class="icon"><i class="bi bi-receipt-cutoff"></i></div><a href="{{ route('admin.projects.index') }}" class="small-box-footer">Revisar projetos <i class="bi bi-arrow-right"></i></a></div></div>
        <div class="col-sm-6 col-xl-3"><div class="small-box"><div class="inner"><h3>{{ $unlinkedLeads }}</h3><p>Pedidos sem cliente vinculado</p></div><div class="icon"><i class="bi bi-person-plus"></i></div><a href="{{ route('admin.leads.index') }}" class="small-box-footer">Associar orçamento <i class="bi bi-arrow-right"></i></a></div></div>
        <div class="col-sm-6 col-xl-3"><div class="small-box"><div class="inner"><h3>{{ $proposalsWaiting }}</h3><p>Propostas aguardando resposta</p></div><div class="icon"><i class="bi bi-file-earmark-text"></i></div><a href="{{ route('admin.leads.index') }}" class="small-box-footer">Acompanhar propostas <i class="bi bi-arrow-right"></i></a></div></div>
        <div class="col-sm-6 col-xl-3"><div class="small-box"><div class="inner"><h3>{{ $activeClients }}</h3><p>Clientes ativos</p></div><div class="icon"><i class="bi bi-people"></i></div><a href="{{ route('admin.clients.index') }}" class="small-box-footer">Abrir clientes <i class="bi bi-arrow-right"></i></a></div></div>
    </div>

    <div class="row g-3">
        <div class="col-xl-4"><x-adminlte-card title="Projetos recentes" icon="bi bi-kanban">
            <div class="list-group list-group-flush">
                @forelse($latestProjects as $project)
                    <a href="{{ route('admin.projects.show', $project) }}" class="list-group-item list-group-item-action px-0"><div class="d-flex justify-content-between gap-2"><span class="fw-semibold">{{ $project->title }}</span><span class="badge text-bg-secondary">{{ \App\Models\Project::STATUSES[$project->status] ?? ucfirst($project->status) }}</span></div><small class="text-muted">{{ $project->client?->name ?? 'Sem cliente vinculado' }} · {{ $project->progress }}% concluído</small></a>
                @empty <p class="text-muted mb-0">Nenhum projeto cadastrado.</p> @endforelse
            </div>
        </x-adminlte-card></div>
        <div class="col-xl-4"><x-adminlte-card title="Cobranças recentes" icon="bi bi-receipt">
            <div class="list-group list-group-flush">
                @forelse($latestInvoices as $invoice)
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="list-group-item list-group-item-action px-0"><div class="d-flex justify-content-between gap-2"><span class="fw-semibold">{{ $invoice->title }}</span><strong>R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}</strong></div><small class="text-muted">{{ $invoice->client?->name }} · vence {{ $invoice->due_date->format('d/m/Y') }} · {{ ucfirst($invoice->status) }}</small></a>
                @empty <p class="text-muted mb-0">Nenhuma cobrança cadastrada.</p> @endforelse
            </div>
        </x-adminlte-card></div>
        <div class="col-xl-4"><x-adminlte-card title="Pedidos recentes" icon="bi bi-inbox">
            <div class="list-group list-group-flush">
                @forelse($latestLeads as $lead)
                    <a href="{{ route('admin.leads.show', $lead) }}" class="list-group-item list-group-item-action px-0"><div class="d-flex justify-content-between gap-2"><span class="fw-semibold">{{ $lead->name }}</span><span class="badge text-bg-secondary">{{ \App\Models\Lead::STATUSES[$lead->status] ?? $lead->status }}</span></div><small class="text-muted">{{ $lead->project_type ?: 'Contato' }} · {{ $lead->client?->name ?? 'Sem vínculo' }} · {{ $lead->created_at->diffForHumans() }}</small></a>
                @empty <p class="text-muted mb-0">Nenhum pedido recebido.</p> @endforelse
            </div>
        </x-adminlte-card></div>
    </div>
@stop
