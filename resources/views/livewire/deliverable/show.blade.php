<div>
    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Situação</dt><dd class="col-sm-3"><span class="badge bg-secondary">{{ ucfirst($deliverable->status) }}</span></dd>
                <dt class="col-sm-3">Horas técnicas (HT)</dt><dd class="col-sm-3">{{ $deliverable->hours_estimated ? number_format($deliverable->hours_estimated, 0, ',', '.').' HT' : '—' }}</dd>
                <dt class="col-sm-3">Valor</dt><dd class="col-sm-3">{{ $deliverable->hours_estimated ? 'R$ '.number_format($deliverable->hours_estimated * 100, 2, ',', '.') : '—' }}</dd>
                <dt class="col-sm-3">Início</dt><dd class="col-sm-3">{{ $deliverable->start_date?->format('d/m/Y') ?? '—' }}</dd>
                <dt class="col-sm-3">Término</dt><dd class="col-sm-3">{{ $deliverable->end_date?->format('d/m/Y') ?? '—' }}</dd>
                <dt class="col-sm-3">Progresso</dt><dd class="col-sm-3">{{ $deliverable->tasks_count ? round(($deliverable->completed_tasks_count / max($deliverable->tasks_count, 1)) * 100) : 0 }}%</dd>
            </dl>
            @if($deliverable->description)<p class="mt-3 mb-1"><strong>Descrição:</strong> {{ $deliverable->description }}</p>@endif
            @if($deliverable->notes)<p class="mb-0"><strong>Observações:</strong> {{ $deliverable->notes }}</p>@endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Tarefas</strong></div>
        <div class="card-body">
            @can('task.create')
                <form wire:submit="addTask" class="mb-3 d-flex gap-2">
                    <input type="text" wire:model="newTaskTitle" placeholder="Nova tarefa..." class="form-control">
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </form>
                @error('newTaskTitle') <span class="text-danger small">{{ $message }}</span> @enderror
            @endcan

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Título</th><th>Situação</th><th>Prioridade</th><th></th></tr></thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>{{ $task->title }}</td>
                                <td>
                                    <select wire:change="updateTaskStatus({{ $task->id }}, $event.target.value)" class="form-select form-select-sm" style="width:auto">
                                        <option value="pendente" @selected($task->status === 'pendente')>Pendente</option>
                                        <option value="andamento" @selected($task->status === 'andamento')>Em Andamento</option>
                                        <option value="concluido" @selected($task->status === 'concluido')>Concluído</option>
                                    </select>
                                </td>
                                <td><span class="badge bg-secondary">{{ ucfirst($task->priority) }}</span></td>
                                <td class="text-end"><button wire:click="removeTask({{ $task->id }})" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover tarefa?')">Remover</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Nenhuma tarefa ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
