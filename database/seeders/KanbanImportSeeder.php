<?php

namespace Database\Seeders;

use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Importação one-shot do Kanban real do agricultura_familiar (banco aecaf_producao).
 *
 * Pré-requisito: tabelas de staging import_deliverables/import_tasks carregadas
 * no banco da ATITEC a partir do dump de produção (ver docs da migração).
 * É idempotente: se o projeto já tiver entregas, nada é feito.
 */
class KanbanImportSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::where('slug', 'agricultura-familiar')->firstOrFail();

        if (Deliverable::where('project_id', $project->id)->exists()) {
            $this->command->warn("Projeto {$project->title} já possui entregas — importação ignorada.");

            return;
        }

        abort_unless(
            Schema::hasTable('import_deliverables') && Schema::hasTable('import_tasks'),
            'Tabelas de staging import_deliverables/import_tasks não encontradas.'
        );

        // Mapa e-mail (origem) → id (ATITEC) para preservar responsáveis quando possível.
        $usersByEmail = User::pluck('id', 'email');

        // Mapa id (origem) → e-mail, extraído de aecaf_producao.users no dia da importação.
        $legacyUsers = [
            1 => 'atilafreitasjr@gmail.com',
            2 => 'sistema.ovinos@gmail.com',
            3 => 'sergiki.vilmar@gmail.com',
            4 => 'atila@atitec.com.br',
        ];

        $deliverables = DB::table('import_deliverables')->orderBy('id')->get();
        foreach ($deliverables as $row) {
            Deliverable::create([
                'id' => $row->id,
                'project_id' => $project->id,
                'name' => $row->name,
                'description' => $row->description,
                'status' => $row->status,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'notes' => $row->notes,
                'hours_estimated' => $row->hours_estimated,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        $legacyEmails = [];
        $tasks = DB::table('import_tasks')->orderBy('id')->get();
        foreach ($tasks as $row) {
            $legacyEmail = $row->user_id ? ($legacyUsers[$row->user_id] ?? null) : null;
            if ($legacyEmail) {
                $legacyEmails[$legacyEmail] = ($legacyEmails[$legacyEmail] ?? 0) + 1;
            }

            DeliverableTask::create([
                'id' => $row->id,
                'project_id' => $project->id,
                'deliverable_id' => $row->deliverable_id,
                'phase' => $row->phase,
                'user_id' => $legacyEmail ? ($usersByEmail[$legacyEmail] ?? null) : null,
                'title' => $row->title,
                'description' => $row->description,
                'status' => $row->status,
                'priority' => $row->priority,
                'due_date' => $row->due_date,
                'start_date' => $row->start_date,
                'end_date' => $row->end_date,
                'hours_estimated' => $row->hours_estimated,
                'hours_actual' => $row->hours_actual,
                'hourly_rate' => $row->hourly_rate,
                'order' => $row->order,
                'tags' => $row->tags,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'deleted_at' => $row->deleted_at,
            ]);
        }

        $this->command->info("Importados {$deliverables->count()} entregas e {$tasks->count()} tarefas para '{$project->title}'.");
        foreach ($legacyEmails as $email => $count) {
            $mapped = $usersByEmail[$email] ?? null;
            $this->command->line("  - {$count} tarefa(s) de {$email} → ".($mapped ? "usuário #{$mapped}" : 'sem correspondência (responsável zerado)'));
        }
    }
}
