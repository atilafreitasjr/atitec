<?php

namespace App\Livewire\Kanban;

use App\Livewire\Concerns\ScopedToProject;
use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\User;
use Livewire\Component;

class Board extends Component
{
    use ScopedToProject;

    public $deliverables;

    public $users;

    public bool $readonly = false;

    public $search = '';

    public $selectedDeliverable = null;

    public $selectedUser = null;

    public $selectedPriority = null;

    public $selectedPhase = null;

    public $showFinished = false;

    public $showModal = false;

    public $editingTask = null;

    public $taskTitle = '';

    public $taskDescription = '';

    public $taskStatus = 'pendente';

    public $taskPriority = 'media';

    public $taskDueDate = '';

    public $taskTags = '';

    public $showNewTaskForm = false;

    public $newTaskTitle = '';

    public function mount(int $projectId, bool $readonly = false)
    {
        $this->resolveProject($projectId);
        $this->readonly = $readonly;

        if ($deliverableId = request()->query('deliverable')) {
            $this->selectedDeliverable = (int) $deliverableId;
        }

        $this->loadDeliverables();
        $this->users = User::orderBy('name')->get();
    }

    public function loadDeliverables()
    {
        $query = Deliverable::where('project_id', $this->projectId)->withCount('tasks');

        if (! $this->showFinished) {
            $query->where('status', '!=', 'concluido');
        }

        $this->deliverables = $query->orderByRaw('start_date IS NULL')->orderBy('start_date')->orderBy('created_at')->get();

        if ($this->selectedDeliverable && ! $this->deliverables->contains('id', $this->selectedDeliverable)) {
            $this->selectedDeliverable = null;
        }
    }

    public function updatedShowFinished(): void
    {
        $this->loadDeliverables();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedDeliverable', 'selectedUser', 'selectedPriority', 'selectedPhase']);
    }

    public function getFilteredTasksQueryProperty()
    {
        return DeliverableTask::query()
            ->where('project_id', $this->projectId)
            ->when($this->selectedDeliverable, fn ($q) => $q->where('deliverable_id', $this->selectedDeliverable))
            ->when($this->selectedUser, fn ($q) => $q->where('user_id', $this->selectedUser))
            ->when($this->selectedPriority, fn ($q) => $q->where('priority', $this->selectedPriority))
            ->when($this->selectedPhase !== null && $this->selectedPhase !== '', fn ($q) => $q->where('phase', (string) $this->selectedPhase))
            ->when(trim((string) $this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('tags', 'like', $term)
                        ->orWhereHas('deliverable', fn ($d) => $d->where('name', 'like', $term))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term));
                });
            });
    }

    /** Tarefas agrupadas por fase do projeto (colunas do Kanban). */
    public function getTasksByPhaseProperty()
    {
        $columns = collect(DeliverableTask::PHASES)
            ->mapWithKeys(fn ($label, $phase) => [(string) $phase => collect()]);

        $columns->put('sem_fase', collect());

        $this->filteredTasksQuery
            ->orderByRaw("FIELD(priority, 'alta', 'media', 'baixa')")
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderBy('order')
            ->get()
            ->each(function (DeliverableTask $task) use ($columns) {
                $phase = $task->phase !== null ? (string) $task->phase : 'sem_fase';
                $columns[$columns->has($phase) ? $phase : 'sem_fase']->push($task);
            });

        return $columns;
    }

    public function hasActiveFilters(): bool
    {
        return trim((string) $this->search) !== ''
            || ! empty($this->selectedDeliverable)
            || ! empty($this->selectedUser)
            || ! empty($this->selectedPriority)
            || ($this->selectedPhase !== null && $this->selectedPhase !== '');
    }

    public function openTaskModal($taskId)
    {
        $this->authorizeKanban('task.edit');

        $task = DeliverableTask::where('project_id', $this->projectId)->findOrFail($taskId);
        $this->editingTask = $task;
        $this->taskTitle = $task->title;
        $this->taskDescription = $task->description ?? '';
        $this->taskStatus = $task->status;
        $this->taskPriority = $task->priority;
        $this->taskDueDate = $task->due_date?->format('Y-m-d') ?? '';
        $this->taskTags = $task->tags ?? '';
        $this->showModal = true;
    }

    public function closeTaskModal()
    {
        $this->showModal = false;
        $this->editingTask = null;
        $this->reset(['taskTitle', 'taskDescription', 'taskStatus', 'taskPriority', 'taskDueDate', 'taskTags']);
    }

    public function saveTask()
    {
        $this->authorizeKanban('task.edit');

        if (! $this->editingTask) {
            return;
        }

        // Revalida o escopo: a tarefa precisa pertencer a este projeto.
        $task = DeliverableTask::where('project_id', $this->projectId)->findOrFail($this->editingTask->id);

        if ($task->invoice_id && $this->taskTitle !== $task->title) {
            $this->addError('taskTitle', 'O título de uma tarefa faturada não pode ser alterado.');

            return;
        }

        $this->validate([
            'taskTitle' => 'required|min:3',
        ]);

        if ($task->invoice_id && $this->taskStatus !== 'concluido') {
            $this->addError('taskStatus', 'Uma tarefa faturada não pode voltar para uma etapa anterior.');

            return;
        }

        $task->update([
            'title' => $this->taskTitle,
            'description' => $this->taskDescription,
            'status' => $this->taskStatus,
            'priority' => $this->taskPriority,
            'due_date' => $this->taskDueDate ?: null,
            'tags' => $this->taskTags,
        ]);

        $this->closeTaskModal();
        session()->flash('message', 'Tarefa atualizada.');
    }

    public function moveTask($taskId, $direction)
    {
        $this->authorizeKanban('task.edit');

        $task = DeliverableTask::where('project_id', $this->projectId)->findOrFail($taskId);
        $phases = array_map('strval', array_keys(DeliverableTask::PHASES));

        if ($task->invoice_id && $direction === 'down') {
            session()->flash('message', 'Esta tarefa já está vinculada a uma fatura e não pode voltar de fase.');

            return;
        }

        $currentIndex = $task->phase !== null ? array_search((string) $task->phase, $phases, true) : -1;
        $currentIndex = $currentIndex === false ? -1 : $currentIndex;

        $newIndex = $direction === 'up' ? $currentIndex + 1 : $currentIndex - 1;

        if ($newIndex < 0 || $newIndex >= count($phases)) {
            return;
        }

        $maxOrder = DeliverableTask::where('project_id', $this->projectId)
            ->where('deliverable_id', $task->deliverable_id)
            ->where('phase', $phases[$newIndex])
            ->max('order') ?? 0;

        $task->update([
            'phase' => $phases[$newIndex],
            'order' => $maxOrder + 1,
        ]);
    }

    public function addTask()
    {
        $this->authorizeKanban('task.create');
        $this->validate(['newTaskTitle' => 'required|min:3']);

        $deliverable = Deliverable::where('project_id', $this->projectId)->findOrFail($this->selectedDeliverable);
        $maxOrder = DeliverableTask::where('deliverable_id', $deliverable->id)->max('order') ?? 0;

        DeliverableTask::create([
            'project_id' => $this->projectId,
            'deliverable_id' => $deliverable->id,
            'title' => $this->newTaskTitle,
            'status' => 'pendente',
            'priority' => 'media',
            'phase' => $this->selectedPhase !== null && $this->selectedPhase !== '' ? (string) $this->selectedPhase : '1',
            'order' => $maxOrder + 1,
        ]);

        $this->newTaskTitle = '';
        $this->showNewTaskForm = false;
        session()->flash('message', 'Tarefa criada.');
    }

    public function deleteTask($taskId)
    {
        $this->authorizeKanban('task.delete');

        $task = DeliverableTask::where('project_id', $this->projectId)->findOrFail($taskId);

        if ($task->invoice_id) {
            session()->flash('message', 'Uma tarefa vinculada a uma fatura não pode ser removida.');

            return;
        }

        $task->delete();
        session()->flash('message', 'Tarefa removida.');
    }

    public function finishDeliverable($deliverableId)
    {
        $this->authorizeKanban('deliverable.edit');

        $deliverable = Deliverable::where('project_id', $this->projectId)->findOrFail($deliverableId);
        $deliverable->update(['status' => 'concluido']);
        $this->loadDeliverables();

        if ($this->selectedDeliverable === $deliverableId) {
            $this->selectedDeliverable = null;
        }

        session()->flash('message', 'Entrega finalizada.');
    }

    public function render()
    {
        return view('livewire.kanban.board', [
            'tasksByPhase' => $this->tasksByPhase,
        ]);
    }
}
