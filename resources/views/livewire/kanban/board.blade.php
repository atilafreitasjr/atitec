<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="mb-0 text-muted">Projeto: <strong>{{ $project->title }}</strong></p>
        @if(! $readonly)
            @can('task.create')
                @if($selectedDeliverable)
                    <button wire:click="$set('showNewTaskForm', true)" class="btn btn-primary btn-sm">+ Nova Tarefa</button>
                @endif
            @endcan
        @endif
    </div>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    {{-- Barra de filtros --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por título, descrição, tag, entrega ou responsável..." class="form-control form-control-sm" style="min-width:220px;flex:1">

                <select wire:model.live="selectedDeliverable" class="form-select form-select-sm" style="width:auto">
                    <option value="">Todas as Entregas</option>
                    @foreach($deliverables as $deliverable)
                        <option value="{{ $deliverable->id }}">{{ $deliverable->name }} ({{ $deliverable->tasks_count }})</option>
                    @endforeach
                </select>

                <select wire:model.live="selectedUser" class="form-select form-select-sm" style="width:auto">
                    <option value="">Todos os responsáveis</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="selectedPriority" class="form-select form-select-sm" style="width:auto">
                    <option value="">Todas as prioridades</option>
                    <option value="alta">Alta</option>
                    <option value="media">Média</option>
                    <option value="baixa">Baixa</option>
                </select>

                <button wire:click="resetFilters" class="btn btn-sm btn-link text-muted" @unless($this->hasActiveFilters()) disabled @endunless>Limpar</button>
            </div>
        </div>
    </div>

    @if($showNewTaskForm && $selectedDeliverable && ! $readonly)
        <div class="card mb-3 border-primary">
            <div class="card-body py-2">
                <form wire:submit="addTask" class="d-flex gap-2">
                    <input type="text" wire:model="newTaskTitle" placeholder="Título da nova tarefa..." class="form-control form-control-sm">
                    <button type="submit" class="btn btn-sm btn-primary">Criar</button>
                    <button type="button" wire:click="$set('showNewTaskForm', false)" class="btn btn-sm btn-secondary">Cancelar</button>
                </form>
                @error('newTaskTitle') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
        </div>
    @endif

    {{-- Colunas Kanban: fases do projeto --}}
    @php
        $phaseColors = ['1' => 'secondary', '2' => 'info', '3' => 'warning', '4' => 'primary', '5' => 'success', '6' => 'dark'];
    @endphp
    <div class="d-flex gap-3 overflow-auto pb-2">
        @if($tasksByPhase['sem_fase']->isNotEmpty())
            <div class="flex-shrink-0" style="width:290px">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <strong class="small text-uppercase">Sem fase</strong>
                        <span class="badge bg-secondary">{{ $tasksByPhase['sem_fase']->count() }}</span>
                    </div>
                    <div class="card-body p-2" style="max-height:60vh;overflow-y:auto">
                        @forelse($tasksByPhase['sem_fase'] as $task)
                            @include('livewire.kanban._card', ['task' => $task, 'column' => 'sem_fase', 'readonly' => $readonly])
                        @empty
                            <p class="text-center text-muted small py-4">Nenhuma tarefa</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
        @foreach(\App\Models\DeliverableTask::PHASES as $phase => $label)
            @php $column = (string) $phase; @endphp
            <div class="flex-shrink-0" style="width:290px">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <strong class="small text-uppercase">{{ $label }}</strong>
                        <span class="badge bg-{{ $phaseColors[$column] ?? 'secondary' }}">{{ $tasksByPhase[$column]->count() }}</span>
                    </div>
                    <div class="card-body p-2" style="max-height:60vh;overflow-y:auto">
                        @forelse($tasksByPhase[$column] as $task)
                            @include('livewire.kanban._card', ['task' => $task, 'column' => $column, 'readonly' => $readonly])
                        @empty
                            <p class="text-center text-muted small py-4">Nenhuma tarefa</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal de edição --}}
    @if($showModal && $editingTask && ! $readonly)
        <div class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Tarefa</h5>
                        <button type="button" class="btn-close" wire:click="closeTaskModal"></button>
                    </div>
                    <form wire:submit="saveTask">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Título</label>
                                <input type="text" wire:model="taskTitle" class="form-control">
                                @error('taskTitle') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descrição</label>
                                <textarea wire:model="taskDescription" rows="5" class="form-control"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Situação</label>
                                    <select wire:model="taskStatus" class="form-select">
                                        <option value="pendente">Pendente</option>
                                        <option value="andamento">Em Andamento</option>
                                        <option value="concluido">Concluído</option>
                                    </select>
                                    @error('taskStatus') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prioridade</label>
                                    <select wire:model="taskPriority" class="form-select">
                                        <option value="baixa">Baixa</option>
                                        <option value="media">Média</option>
                                        <option value="alta">Alta</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Data de término</label>
                                    <input type="date" wire:model="taskDueDate" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tags (separadas por vírgula)</label>
                                    <input type="text" wire:model="taskTags" class="form-control">
                                </div>
                            </div>
                            @if($editingTask->deliverable)
                                <p class="text-muted small mb-0">Entrega: {{ $editingTask->deliverable->name }}</p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="closeTaskModal" class="btn btn-secondary">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
