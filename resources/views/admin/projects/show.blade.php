@extends('adminlte::page')

@section('title', $project->title)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div><p class="text-uppercase small text-muted mb-1">Workspace do projeto</p><h1 class="mb-0">{{ $project->title }}</h1></div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.projects.kanban', $project) }}" class="btn btn-primary"><i class="bi bi-kanban me-1"></i> Kanban</a>
            <a href="{{ route('admin.projects.deliverables.index', $project) }}" class="btn btn-outline-secondary">Entregas</a>
            <a href="{{ route('admin.projects.tasks.index', $project) }}" class="btn btn-outline-secondary">Tarefas</a>
            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-secondary">Editar</a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="Atualizado">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Verifique os dados">{{ $errors->first() }}</x-adminlte-alert>@endif

    @php
        $tasks = $project->deliverables->flatMap(fn ($deliverable) => $deliverable->tasks);
        $completedTasks = $tasks->where('status', 'concluido');
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3"><div class="info-box"><span class="info-box-icon text-bg-primary"><i class="bi bi-list-check"></i></span><div class="info-box-content"><span class="info-box-text">Tarefas concluídas</span><span class="info-box-number">{{ $completedTasks->count() }} / {{ $tasks->count() }}</span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="info-box"><span class="info-box-icon text-bg-info"><i class="bi bi-calculator"></i></span><div class="info-box-content"><span class="info-box-text">Custo estimado</span><span class="info-box-number">R$ {{ number_format($project->estimated_cost, 2, ',', '.') }}</span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="info-box"><span class="info-box-icon text-bg-success"><i class="bi bi-check2-circle"></i></span><div class="info-box-content"><span class="info-box-text">Custo realizado</span><span class="info-box-number">R$ {{ number_format($project->actual_cost, 2, ',', '.') }}</span></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="info-box"><span class="info-box-icon text-bg-warning"><i class="bi bi-receipt"></i></span><div class="info-box-content"><span class="info-box-text">Pronto para faturar</span><span class="info-box-number">R$ {{ number_format($project->unbilled_actual_cost, 2, ',', '.') }}</span></div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <x-adminlte-card title="Dados do projeto" icon="bi bi-info-circle">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Cliente</dt><dd class="col-sm-8">{{ $project->client?->name ?? '—' }}</dd>
                    <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ \App\Models\Project::STATUSES[$project->status] ?? ucfirst($project->status) }} · {{ $project->progress }}%</dd>
                    <dt class="col-sm-4">Prazo</dt><dd class="col-sm-8">{{ $project->deadline?->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-4">Orçamento</dt><dd class="col-sm-8">{{ $project->budget ? 'R$ '.number_format((float) $project->budget, 2, ',', '.') : '—' }}</dd>
                    <dt class="col-sm-4">Entregas</dt><dd class="col-sm-8">{{ $project->deliverables->count() }}</dd>
                </dl>
                @if($project->description)<hr><p class="mb-0 text-break" style="white-space:pre-line">{{ $project->description }}</p>@endif
            </x-adminlte-card>

            <x-adminlte-card title="Documentos compartilhados" icon="bi bi-folder2-open">
                @can('project.edit')
                    <form method="POST" action="{{ route('admin.projects.documents.store', $project) }}" enctype="multipart/form-data" class="rounded-3 border p-3 mb-3">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6"><label class="form-label">Nome do documento</label><input name="name" class="form-control" required placeholder="Contrato de desenvolvimento"></div>
                            <div class="col-md-6"><label class="form-label">Categoria</label><select name="category" class="form-select" required><option value="contrato">Contrato</option><option value="escopo">Escopo / proposta</option><option value="entrega">Material de entrega</option><option value="outro">Outro</option></select></div>
                            <div class="col-12"><label class="form-label">Arquivo (PDF, Word, Excel ou imagem; até 20 MB)</label><input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" required></div>
                        </div>
                        <div class="form-check my-3"><input class="form-check-input" type="checkbox" name="visible_to_client" value="1" id="document_visible" checked><label class="form-check-label" for="document_visible">Disponibilizar no workspace do cliente</label></div>
                        <button class="btn btn-sm btn-primary"><i class="bi bi-upload me-1"></i> Enviar documento</button>
                    </form>
                @endcan
                <div class="list-group list-group-flush">
                    @forelse($project->documents as $document)
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                            <div><a href="{{ route('project-documents.download', [$project, $document]) }}" class="fw-semibold">{{ $document->name }}</a><small class="d-block text-muted">{{ ucfirst($document->category) }} · {{ number_format(($document->size_bytes ?? 0) / 1048576, 2) }} MB · {{ $document->visible_to_client ? 'Visível ao cliente' : 'Interno' }}</small></div>
                            @can('project.edit')<form method="POST" action="{{ route('admin.projects.documents.destroy', [$project, $document]) }}" onsubmit="return confirm('Remover este documento?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Remover documento"><i class="bi bi-trash"></i></button></form>@endcan
                        </div>
                    @empty
                        <p class="small text-muted mb-0">Nenhum documento enviado ainda.</p>
                    @endforelse
                </div>
            </x-adminlte-card>
        </div>

        <div class="col-lg-7">
            <x-adminlte-card title="Custo por entrega e tarefa" icon="bi bi-list-task">
                @forelse($project->deliverables as $deliverable)
                    <div class="mb-3 overflow-hidden rounded-3 border">
                        <div class="d-flex flex-wrap justify-content-between gap-2 bg-body-secondary px-3 py-2"><strong>{{ $deliverable->name }}</strong><span class="small">Previsto R$ {{ number_format($deliverable->estimated_cost, 2, ',', '.') }} · Realizado R$ {{ number_format($deliverable->actual_cost, 2, ',', '.') }}</span></div>
                        <div class="table-responsive"><table class="table table-sm mb-0 align-middle"><thead><tr><th>Tarefa</th><th>Status</th><th class="text-end">h realizadas</th><th class="text-end">Valor/h</th><th class="text-end">Custo realizado</th><th>Fatura</th></tr></thead><tbody>
                            @forelse($deliverable->tasks as $task)
                                <tr><td>{{ $task->title }}</td><td>{{ \App\Models\DeliverableTask::STATUSES[$task->status] ?? ucfirst($task->status) }}</td><td class="text-end">{{ $task->hours_actual !== null ? number_format((float) $task->hours_actual, 2, ',', '.') : '—' }}</td><td class="text-end">R$ {{ number_format((float) $task->hourly_rate, 2, ',', '.') }}</td><td class="text-end">{{ $task->cost_actual !== null ? 'R$ '.number_format($task->cost_actual, 2, ',', '.') : '—' }}</td><td>@if($task->invoice)<a href="{{ route('admin.invoices.show', $task->invoice) }}">#{{ $task->invoice_id }}</a>@elseif($task->status === 'concluido' && $task->cost_actual > 0)<span class="badge text-bg-warning">Não faturada</span>@else — @endif</td></tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Nenhuma tarefa nesta entrega.</td></tr>
                            @endforelse
                        </tbody></table></div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Cadastre entregas e tarefas para acompanhar horas e custos.</p>
                @endforelse
            </x-adminlte-card>

            <x-adminlte-card title="Gerar cobrança pelas tarefas realizadas" icon="bi bi-receipt">
                @if(! $project->client_id)
                    <div class="alert alert-warning mb-0">Associe um cliente ao projeto para emitir cobranças.</div>
                @elseif($billableTasks->isNotEmpty())
                    <p class="text-muted">Marque as tarefas que devem entrar nesta cobrança. Só aparecem aqui as tarefas concluídas, com custo realizado e ainda não faturadas — tarefas já cobradas em faturas anteriores não são listadas novamente.</p>
                    @error('task_ids')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
                    @can('invoice.create')
                        @can('task.view')
                            <form method="POST" action="{{ route('admin.projects.invoices.from-tasks', $project) }}">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead><tr>
                                            <th style="width:36px"><input type="checkbox" class="form-check-input" id="billing-check-all" checked aria-label="Selecionar todas"></th>
                                            <th>Tarefa</th>
                                            <th>Entrega</th>
                                            <th class="text-end">h realizadas</th>
                                            <th class="text-end">Valor/h</th>
                                            <th class="text-end">Custo</th>
                                        </tr></thead>
                                        <tbody>
                                            @foreach($billableTasks as $task)
                                                <tr>
                                                    <td><input type="checkbox" class="form-check-input billing-task" name="task_ids[]" value="{{ $task->id }}" data-cost="{{ number_format((float) $task->cost_actual, 2, '.', '') }}" checked aria-label="Cobrar {{ $task->title }}"></td>
                                                    <td>{{ $task->title }}</td>
                                                    <td>{{ $task->deliverable?->name ?? '—' }}</td>
                                                    <td class="text-end">{{ number_format((float) $task->hours_actual, 2, ',', '.') }}</td>
                                                    <td class="text-end">R$ {{ number_format((float) $task->hourly_rate, 2, ',', '.') }}</td>
                                                    <td class="text-end">R$ {{ number_format((float) $task->cost_actual, 2, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4"><label class="form-label" for="task_invoice_due_date">Vencimento</label><input id="task_invoice_due_date" type="date" name="due_date" class="form-control" min="{{ today()->format('Y-m-d') }}" value="{{ old('due_date', today()->addDays(14)->format('Y-m-d')) }}" required></div>
                                    <div class="col-md-8"><button class="btn btn-primary" id="billing-submit"><i class="bi bi-receipt me-1"></i> Gerar fatura de <span id="billing-total">R$ 0,00</span></button></div>
                                </div>
                            </form>
                            <script>
                            (function () {
                                var boxes = Array.prototype.slice.call(document.querySelectorAll('.billing-task'));
                                var all = document.getElementById('billing-check-all');
                                var totalEl = document.getElementById('billing-total');
                                var button = document.getElementById('billing-submit');
                                function refresh() {
                                    var total = boxes.filter(function (b) { return b.checked; }).reduce(function (sum, b) { return sum + parseFloat(b.dataset.cost || '0'); }, 0);
                                    totalEl.textContent = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    button.disabled = total <= 0;
                                    if (all) { all.checked = boxes.length > 0 && boxes.every(function (b) { return b.checked; }); all.indeterminate = !all.checked && boxes.some(function (b) { return b.checked; }); }
                                }
                                if (all) { all.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = all.checked; }); refresh(); }); }
                                boxes.forEach(function (b) { b.addEventListener('change', refresh); });
                                refresh();
                            })();
                            </script>
                        @endcan
                    @endcan
                @else
                    <p class="text-muted mb-0">Não há tarefas concluídas com custo realizado aguardando faturamento.</p>
                @endif
            </x-adminlte-card>

            <x-adminlte-card title="Cobranças do projeto" icon="bi bi-cash-stack">
                <div class="list-group list-group-flush">
                    @forelse($project->invoices as $invoice)
                        <a class="list-group-item px-0 d-flex justify-content-between" href="{{ route('admin.invoices.show', $invoice) }}"><span>{{ $invoice->title }} <small class="d-block text-muted">Vence {{ $invoice->due_date->format('d/m/Y') }}</small></span><strong>R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }} · {{ ucfirst($invoice->status) }}</strong></a>
                    @empty
                        <p class="small text-muted mb-0">Nenhuma cobrança vinculada.</p>
                    @endforelse
                </div>
            </x-adminlte-card>
        </div>
    </div>
@stop
