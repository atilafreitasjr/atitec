<div>
    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2">
                <div class="col-md-6"><input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar tarefas..." class="form-control form-control-sm"></div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select form-select-sm">
                        <option value="">Todos os status</option>
                        <option value="pendente">Pendente</option>
                        <option value="andamento">Em Andamento</option>
                        <option value="concluido">Concluído</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="priorityFilter" class="form-select form-select-sm">
                        <option value="">Todas as prioridades</option>
                        <option value="baixa">Baixa</option>
                        <option value="media">Média</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Título</th><th>Entrega</th><th>Situação</th><th class="text-center">Horas</th><th class="text-end">Custo</th><th></th></tr></thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>{{ $task->title }}</td>
                            <td class="small text-muted">{{ $task->deliverable?->name }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($task->status) }}</span></td>
                            <td class="text-center small text-muted">{{ $task->hours_estimated ? number_format($task->hours_estimated, 1, ',', '.').'h' : '—' }}</td>
                            <td class="text-end small">{{ $task->cost_estimated ? 'R$ '.number_format($task->cost_estimated, 2, ',', '.') : '—' }}</td>
                            <td class="text-end">
                                @can('task.edit')
                                    <a href="{{ route('admin.projects.tasks.edit', [$projectId, $task->id]) }}" class="btn btn-sm btn-outline-primary" wire:navigate>Editar</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Nenhuma tarefa encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $tasks->links() }}</div>
    </div>
</div>
