@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="mb-0">Dashboard ATITEC</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6"><x-adminlte-info-box title="Projetos ativos" text="{{ $projetosAtivos }}" icon="bi bi-kanban" theme="primary"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-info-box title="Conversas abertas" text="{{ $ticketsAbertos }}" icon="bi bi-chat-dots" theme="info"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-info-box title="Faturas pendentes" text="{{ $faturasPendentes }}" icon="bi bi-receipt" theme="warning"/></div>
        <div class="col-lg-3 col-6"><x-adminlte-info-box title="Faturas vencidas" text="{{ $faturasVencidas }}" icon="bi bi-exclamation-triangle" theme="danger"/></div>
    </div>
    <div class="row">
        <div class="col-md-4"><x-adminlte-info-box title="MRR (mensalidades pendentes)" text="R$ {{ number_format($mrr, 2, ',', '.') }}" icon="bi bi-cash-stack" theme="success"/></div>
        <div class="col-md-4"><x-adminlte-info-box title="Leads novos" text="{{ $leadsNovos }}" icon="bi bi-funnel" theme="secondary"/></div>
        <div class="col-md-4"><x-adminlte-info-box title="Clientes ativos" text="{{ $totalClientes }}" icon="bi bi-people" theme="dark"/></div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <x-adminlte-card title="Últimos projetos" theme="primary" icon="bi bi-kanban">
                <ul class="list-group list-group-flush">
                    @forelse($ultimosProjetos as $p)
                        <li class="list-group-item d-flex justify-content-between">
                            <a href="{{ route('admin.projects.show', $p) }}">{{ $p->title }}</a>
                            <span class="badge bg-secondary">{{ $p->status }}</span>
                        </li>
                    @empty<li class="list-group-item">Nenhum projeto.</li>@endforelse
                </ul>
            </x-adminlte-card>
        </div>
        <div class="col-md-4">
            <x-adminlte-card title="Últimas faturas" theme="warning" icon="bi bi-receipt">
                <ul class="list-group list-group-flush">
                    @forelse($ultimasFaturas as $f)
                        <li class="list-group-item d-flex justify-content-between">
                            <a href="{{ route('admin.invoices.show', $f) }}">{{ $f->title }}</a>
                            <span class="badge bg-{{ $f->status === 'pago' ? 'success' : 'warning' }}">{{ $f->status }}</span>
                        </li>
                    @empty<li class="list-group-item">Nenhuma fatura.</li>@endforelse
                </ul>
            </x-adminlte-card>
        </div>
        <div class="col-md-4">
            <x-adminlte-card title="Últimos leads" theme="info" icon="bi bi-funnel">
                <ul class="list-group list-group-flush">
                    @forelse($ultimosLeads as $l)
                        <li class="list-group-item d-flex justify-content-between">
                            <a href="{{ route('admin.leads.show', $l) }}">{{ $l->name }}</a>
                            <span class="badge bg-secondary">{{ $l->status }}</span>
                        </li>
                    @empty<li class="list-group-item">Nenhum lead.</li>@endforelse
                </ul>
            </x-adminlte-card>
        </div>
    </div>
@stop
