<?php

namespace Database\Seeders;

use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Registra melhorias recentes no Kanban do projeto interno da ATITEC.
 *
 * As tarefas são concluídas, mas não possuem horas nem custo. O seeder é
 * incremental para poder ser executado no deploy sem duplicar registros.
 */
class AtitecRecentTasksSeeder extends Seeder
{
    private const TASKS = [
        [
            'title' => 'Paginação compartilhada no admin e portal',
            'description' => 'Padronização das paginações Laravel e Livewire com layout ATITEC responsivo, acessível e legível.',
        ],
        [
            'title' => 'Múltiplos clientes por projeto e acesso compartilhado',
            'description' => 'Relacionamento N:N entre clientes e projetos, seleção no admin e isolamento de faturas e documentos no portal.',
        ],
    ];

    public function run(): void
    {
        $project = Project::where('slug', 'atitec')->firstOrFail();
        $deliverable = Deliverable::where('project_id', $project->id)
            ->where('name', 'Administração e CRM')
            ->firstOrFail();
        $userId = User::where('email', 'admin@atitec.com.br')->value('id');
        $nextOrder = ((int) $deliverable->tasks()->max('order')) + 1;
        $created = 0;

        foreach (self::TASKS as $taskData) {
            $task = DeliverableTask::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'deliverable_id' => $deliverable->id,
                    'title' => $taskData['title'],
                ],
                [
                    'user_id' => $userId,
                    'description' => $taskData['description'],
                    'status' => 'concluido',
                    'priority' => 'media',
                    'phase' => '6',
                    'hours_estimated' => null,
                    'hours_actual' => null,
                    'hourly_rate' => 0,
                    'order' => $nextOrder++,
                ],
            );

            $created += (int) $task->wasRecentlyCreated;
        }

        $this->command?->info("ATITEC: {$created} tarefa(s) recente(s) adicionada(s); sem horas e sem custo.");
    }
}
