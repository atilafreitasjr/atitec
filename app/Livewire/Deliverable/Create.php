<?php

namespace App\Livewire\Deliverable;

use App\Livewire\Concerns\ScopedToProject;
use App\Models\Deliverable;
use Livewire\Component;

class Create extends Component
{
    use ScopedToProject;

    public $name = '';

    public $description = '';

    public $status = 'planejamento';

    public $start_date = '';

    public $end_date = '';

    public $notes = '';

    public $hours_estimated = '';

    public function mount(int $projectId)
    {
        $this->resolveProject($projectId);
        $this->authorizeKanban('deliverable.create');
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|min:3|max:255',
            'description' => 'nullable',
            'status' => 'required|in:planejamento,andamento,concluido,cancelado',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable',
            'hours_estimated' => 'nullable|numeric|min:0',
        ];
    }

    public function save()
    {
        $this->authorizeKanban('deliverable.create');
        $this->validate();

        Deliverable::create([
            'project_id' => $this->projectId,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'notes' => $this->notes,
            'hours_estimated' => $this->hours_estimated !== '' ? $this->hours_estimated : null,
        ]);

        session()->flash('message', 'Entrega criada com sucesso.');

        return $this->redirect(route('admin.projects.deliverables.index', $this->projectId), navigate: true);
    }

    public function render()
    {
        return view('livewire.deliverable.create');
    }
}
