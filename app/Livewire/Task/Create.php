<?php

namespace App\Livewire\Task;

use App\Livewire\Concerns\ScopedToProject;
use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\User;
use Livewire\Component;

class Create extends Component
{
    use ScopedToProject;

    public $deliverable_id = '';

    public $user_id = '';

    public $title = '';

    public $description = '';

    public $status = 'pendente';

    public $priority = 'media';

    public $due_date = '';

    public $order = 0;

    public $phase = '';

    public $start_date = '';

    public $end_date = '';

    public $hours_estimated = '';

    public $hours_actual = '';

    public $hourly_rate = '100';

    public function mount(int $projectId)
    {
        $this->resolveProject($projectId);
        $this->authorizeKanban('task.create');
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
        $this->authorizeKanban('task.create');
        $this->validate();

        // A entrega precisa pertencer a este projeto (isolamento).
        $deliverable = Deliverable::where('project_id', $this->projectId)->findOrFail($this->deliverable_id);

        DeliverableTask::create([
            'project_id' => $this->projectId,
            'deliverable_id' => $deliverable->id,
            'user_id' => $this->user_id ?: null,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date ?: null,
            'order' => DeliverableTask::where('deliverable_id', $deliverable->id)->count() + 1,
            'phase' => $this->phase ?: null,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'hours_estimated' => $this->hours_estimated !== '' ? $this->hours_estimated : null,
            'hours_actual' => $this->hours_actual !== '' ? $this->hours_actual : null,
            'hourly_rate' => $this->hourly_rate !== '' ? $this->hourly_rate : 100,
        ]);

        session()->flash('message', 'Tarefa criada com sucesso.');

        return $this->redirect(route('admin.projects.tasks.index', $this->projectId), navigate: true);
    }

    public function render()
    {
        return view('livewire.task.create', [
            'deliverables' => Deliverable::where('project_id', $this->projectId)->orderByRaw('start_date IS NULL')->orderBy('start_date')->orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
