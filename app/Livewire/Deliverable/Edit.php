<?php

namespace App\Livewire\Deliverable;

use App\Livewire\Concerns\ScopedToProject;
use App\Models\Deliverable;
use Livewire\Component;

class Edit extends Component
{
    use ScopedToProject;

    public Deliverable $deliverable;

    public $name;

    public $description;

    public $status;

    public $start_date;

    public $end_date;

    public $notes;

    public $hours_estimated;

    public function mount(int $projectId, int $deliverableId)
    {
        $this->resolveProject($projectId);
        $this->authorizeKanban('deliverable.edit');

        $deliverable = Deliverable::where('project_id', $this->projectId)->findOrFail($deliverableId);
        $this->deliverable = $deliverable;

        $this->name = $deliverable->name;
        $this->description = $deliverable->description;
        $this->status = $deliverable->status;
        $this->start_date = $deliverable->start_date?->format('Y-m-d');
        $this->end_date = $deliverable->end_date?->format('Y-m-d');
        $this->notes = $deliverable->notes;
        $this->hours_estimated = (string) ($deliverable->hours_estimated ?? '');
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
        $this->authorizeKanban('deliverable.edit');
        $this->validate();

        $this->deliverable->update([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'notes' => $this->notes,
            'hours_estimated' => $this->hours_estimated !== '' ? $this->hours_estimated : null,
        ]);

        session()->flash('message', 'Entrega atualizada com sucesso.');

        return $this->redirect(route('admin.projects.deliverables.index', $this->projectId), navigate: true);
    }

    public function render()
    {
        return view('livewire.deliverable.edit');
    }
}
