<?php

namespace App\Livewire\Concerns;

use App\Models\Project;

trait ScopedToProject
{
    public int $projectId;

    public Project $project;

    protected function resolveProject(int $projectId): void
    {
        $project = Project::findOrFail($projectId);

        $user = auth()->user();

        // Cliente só enxerga os projetos do próprio cadastro.
        if ($user && $user->role === 'cliente' && $project->client_id !== $user->client_id) {
            abort(403, 'Acesso não autorizado a este projeto.');
        }

        $this->projectId = $project->id;
        $this->project = $project;
    }

    protected function authorizeKanban(string $permission): void
    {
        if (! auth()->user()?->can($permission)) {
            abort(403, 'Sem permissão para esta ação.');
        }
    }
}
