<?php

namespace App\Livewire\Deliverable;

use App\Livewire\Concerns\ScopedToProject;
use App\Models\Deliverable;
use Livewire\Component;

class Show extends Component
{
    use ScopedToProject;

    public Deliverable $deliverable;

    public $newTaskTitle = '';

    public function mount(int $projectId, int $deliverableId)
    {
        $this->resolveProject($projectId);
        $this->deliverable = Deliverable::where('project_id', $this->projectId)->findOrFail($deliverableId);
    }

    public function addTask()
    {
        $this->authorizeKanban('task.create');
        $this->validate(['newTaskTitle' => 'required|min:3']);

        $this->deliverable->tasks()->create([
            'project_id' => $this->projectId,
            'title' => $this->newTaskTitle,
            'status' => 'pendente',
            'priority' => 'media',
            'order' => $this->deliverable->tasks()->count() + 1,
        ]);

        $this->newTaskTitle = '';
        session()->flash('message', 'Tarefa adicionada.');
    }

    public function updateTaskStatus($taskId, $status)
    {
        $this->authorizeKanban('task.edit');

        abort_unless(in_array($status, ['pendente', 'andamento', 'concluido'], true), 422);
        $task = $this->deliverable->tasks()->where('project_id', $this->projectId)->findOrFail($taskId);
        $task->update(['status' => $status]);
    }

    public function removeTask($taskId)
    {
        $this->authorizeKanban('task.delete');

        $this->deliverable->tasks()->where('project_id', $this->projectId)->findOrFail($taskId)->delete();
        session()->flash('message', 'Tarefa removida.');
    }

    public function render()
    {
        $deliverable = $this->deliverable->loadCount([
            'tasks as tasks_count',
            'tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'concluido'),
        ]);

        $tasks = $this->deliverable->tasks()->orderBy('order')->get();

        return view('livewire.deliverable.show', [
            'deliverable' => $deliverable,
            'tasks' => $tasks,
        ]);
    }
}
