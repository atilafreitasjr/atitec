<?php

namespace App\Livewire\Task;

use App\Livewire\Concerns\ScopedToProject;
use App\Models\DeliverableTask;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use ScopedToProject;
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $priorityFilter = '';

    public function mount(int $projectId)
    {
        $this->resolveProject($projectId);
    }

    public function render()
    {
        $query = DeliverableTask::where('project_id', $this->projectId)->with(['deliverable', 'user']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        return view('livewire.task.index', [
            'tasks' => $query->orderByRaw('phase IS NULL')->orderBy('phase')->orderBy('order')->orderBy('created_at')->paginate(10),
        ]);
    }
}
