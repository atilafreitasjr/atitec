@extends('adminlte::page')

@section('title', $project->title)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <a href="{{ route('cliente.projetos') }}" class="small">← Meus projetos</a>
            <h1 class="mb-0">{{ $project->title }}</h1>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('cliente.kanban', $project->id) }}" class="btn btn-primary"><i class="bi bi-kanban me-1"></i> Acompanhar Kanban</a>
        </div>
    </div>
@stop

@section('content')
    @php
        $tasks = $project->deliverables->flatMap(fn ($deliverable) => $deliverable->tasks);
        $actualCost = $tasks->sum(fn ($task) => $task->cost_actual ?? 0);
        $paidTotal = $project->invoices->sum(fn ($invoice) => $invoice->payments->sum('amount'));
        $openTotal = $project->invoices->sum(fn ($invoice) => $invoice->balance());
    @endphp

    <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
            <div class="info-box"><span class="info-box-icon text-bg-primary"><i class="bi bi-graph-up"></i></span>
                <div class="info-box-content"><span class="info-box-text">Andamento</span><span class="info-box-number">{{ $project->progress }}%</span>
                    <small class="text-muted">Prazo: {{ $project->deadline?->format('d/m/Y') ?? 'A definir' }}</small></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box"><span class="info-box-icon text-bg-info"><i class="bi bi-calculator"></i></span>
                <div class="info-box-content"><span class="info-box-text">Custo realizado nas tarefas</span><span class="info-box-number">R$ {{ number_format($actualCost, 2, ',', '.') }}</span>
                    <small class="text-muted">Horas realizadas × valor/hora</small></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box"><span class="info-box-icon text-bg-success"><i class="bi bi-check2-circle"></i></span>
                <div class="info-box-content"><span class="info-box-text">Total pago no projeto</span><span class="info-box-number">R$ {{ number_format($paidTotal, 2, ',', '.') }}</span></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box"><span class="info-box-icon text-bg-warning"><i class="bi bi-receipt"></i></span>
                <div class="info-box-content"><span class="info-box-text">Cobranças em aberto</span><span class="info-box-number">R$ {{ number_format($openTotal, 2, ',', '.') }}</span>
                    <a href="{{ route('cliente.financeiro') }}" class="small">Ver financeiro →</a></div>
            </div>
        </div>
    </div>

    @if($project->description)
        <x-adminlte-card title="Sobre o projeto" icon="bi bi-info-circle">
            <p class="mb-0 text-break" style="white-space:pre-line">{{ $project->description }}</p>
        </x-adminlte-card>
    @endif

    <x-adminlte-card title="Entregas e tarefas" icon="bi bi-list-task">
        <p class="text-muted small mb-3">Acompanhe o andamento e o custo das atividades realizadas.</p>
        @forelse($project->deliverables as $deliverable)
            <div class="rounded border mb-3 overflow-hidden">
                <div class="d-flex flex-wrap justify-content-between gap-2 bg-body-secondary px-3 py-2">
                    <div>
                        <strong>{{ $deliverable->name }}</strong>
                        @if($deliverable->description)<small class="d-block text-muted">{{ $deliverable->description }}</small>@endif
                    </div>
                    <span class="badge text-bg-secondary align-self-start">{{ \App\Models\Deliverable::STATUSES[$deliverable->status] ?? ucfirst($deliverable->status) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Tarefa</th><th>Status</th><th class="text-end">Horas realizadas</th><th class="text-end">Custo</th><th>Cobrança</th></tr></thead>
                        <tbody>
                            @forelse($deliverable->tasks as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td>{{ \App\Models\DeliverableTask::STATUSES[$task->status] ?? ucfirst($task->status) }}</td>
                                    <td class="text-end">{{ $task->hours_actual !== null ? number_format((float) $task->hours_actual, 2, ',', '.') : '—' }}</td>
                                    <td class="text-end">{{ $task->cost_actual !== null ? 'R$ '.number_format($task->cost_actual, 2, ',', '.') : '—' }}</td>
                                    <td>
                                        @if($task->invoice)
                                            <a href="{{ route('cliente.fatura', $task->invoice) }}">{{ $task->invoice->status === 'pago' ? 'Paga' : 'Fatura #'.$task->invoice->id }}</a>
                                        @elseif($task->cost_actual > 0)
                                            <span class="text-muted">Ainda não faturada</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Nenhuma tarefa cadastrada nesta entrega.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">As entregas do projeto aparecerão aqui.</p>
        @endforelse
    </x-adminlte-card>

    <div class="row g-3">
        <div class="col-lg-6">
            <x-adminlte-card title="Documentos do projeto" icon="bi bi-folder2-open">
                <p class="text-muted small">Contratos, escopo e materiais compartilhados pela ATITEC.</p>
                <div class="list-group list-group-flush">
                    @forelse($project->documents as $document)
                        <a href="{{ route('project-documents.download', [$project, $document]) }}" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center gap-2">
                            <span><i class="bi bi-file-earmark-text me-2"></i><span class="fw-semibold">{{ $document->name }}</span><small class="d-block text-muted ms-4">{{ ucfirst($document->category) }} · {{ $document->created_at->format('d/m/Y') }}</small></span>
                            <span class="small fw-semibold">Baixar ↓</span>
                        </a>
                    @empty
                        <p class="text-muted small mb-0">Nenhum documento compartilhado ainda.</p>
                    @endforelse
                </div>
            </x-adminlte-card>
        </div>
        <div class="col-lg-6">
            <x-adminlte-card title="Cobranças do projeto" icon="bi bi-cash-stack">
                <p class="text-muted small">Pago até agora: R$ {{ number_format($paidTotal, 2, ',', '.') }}</p>
                <div class="list-group list-group-flush">
                    @forelse($project->invoices as $invoice)
                        <a href="{{ route('cliente.fatura', $invoice) }}" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center gap-2">
                            <span><span class="fw-semibold">{{ $invoice->title }}</span><small class="d-block text-muted">Vence {{ $invoice->due_date->format('d/m/Y') }}</small></span>
                            <span class="text-end"><span class="d-block fw-semibold">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}</span><small class="text-muted">{{ ucfirst($invoice->status) }}</small></span>
                        </a>
                    @empty
                        <p class="text-muted small mb-0">Nenhuma cobrança vinculada a este projeto.</p>
                    @endforelse
                </div>
            </x-adminlte-card>
        </div>
    </div>
@stop
