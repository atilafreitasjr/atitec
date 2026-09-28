<?php

namespace App\Livewire\Task;

use App\Livewire\Concerns\ScopedToProject;
use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\User;
use Livewire\Component;

class Edit extends Component
{
    use ScopedToProject;

    public DeliverableTask $task;

    public $deliverable_id;

    public $user_id;

    public $title;

    public $description;

    public $status;

    public $priority;

    public $due_date;

    public $phase;

    public $start_date;

    public $end_date;

    public $hours_estimated;

    public $hours_actual;

    public $hourly_rate;

    public function mount(int $projectId, int $taskId)
    {
        $this->resolveProject($projectId);
        $this->authorizeKanban('task.edit');

        $task = DeliverableTask::where('project_id', $this->projectId)->findOrFail($taskId);
        $this->task = $task;

        $this->deliverable_id = $task->deliverable_id;
        $this->user_id = $task->user_id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->status = $task->status;
        $this->priority = $task->priority;
        $this->due_date = $task->due_date?->format('Y-m-d');
        $this->phase = $task->phase;
        $this->start_date = $task->start_date?->format('Y-m-d');
        $this->end_date = $task->end_date?->format('Y-m-d');
        $this->hours_estimated = (string) ($task->hours_estimated ?? '');
        $this->hours_actual = (string) ($task->hours_actual ?? '');
        $this->hourly_rate = (string) ($task->hourly_rate ?? '100');
    }

    protected function rules(): array
    {
        return [
            'deliverable_id' => 'required|exists:deliverables,id',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|min:3|max:255',
            'description' => 'nullable',
            'status' => 'required|in:pendente,andamento,concluido',
            'priority' => 'required|in:baixa,media,alta',
            'due_date' => 'nullable|date',
            'phase' => 'nullable|in:1,2,3,4',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'hours_estimated' => 'nullable|numeric|min:0',
            'hours_actual' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
        ];
    }

    public function save()
    {
        $this->authorizeKanban('task.edit');
        $this->validate();

        // A entrega de destino precisa pertencer a este projeto (isolamento).
        $deliverable = Deliverable::where('project_id', $this->projectId)->findOrFail($this->deliverable_id);

        $this->task->update([
            'project_id' => $this->projectId,
            'deliverable_id' => $deliverable->id,
            'user_id' => $this->user_id ?: null,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date ?: null,
            'phase' => $this->phase ?: null,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'hours_estimated' => $this->hours_estimated !== '' ? $this->hours_estimated : null,
            'hours_actual' => $this->hours_actual !== '' ? $this->hours_actual : null,
            'hourly_rate' => $this->hourly_rate !== '' ? $this->hourly_rate : 100,
        ]);

        session()->flash('message', 'Tarefa atualizada com sucesso.');

        return $this->redirect(route('admin.projects.tasks.index', $this->projectId), navigate: true);
    }

    public function render()
    {
        return view('livewire.task.edit', [
            'deliverables' => Deliverable::where('project_id', $this->projectId)->orderByRaw('start_date IS NULL')->orderBy('start_date')->orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
