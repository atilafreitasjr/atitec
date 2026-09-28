<?php

namespace App\Livewire\Deliverable;

use App\Livewire\Concerns\ScopedToProject;
use App\Models\Deliverable;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use ScopedToProject;
    use WithPagination;

    public $search = '';

    public function mount(int $projectId)
    {
        $this->resolveProject($projectId);
    }

    public function render()
    {
        return view('livewire.deliverable.index', [
            'deliverables' => Deliverable::where('project_id', $this->projectId)
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                })
                ->withCount('tasks')
                ->orderByRaw('start_date IS NULL')
                ->orderBy('start_date')
                ->orderBy('created_at')
                ->paginate(10),
        ]);
    }
}
