<?php

namespace App\Console\Commands;

use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterProjectTasks extends Command
{
    protected $signature = 'project:tasks:register
                            {--stdin : Lê o manifest JSON da entrada padrão}
                            {--manifest= : Caminho do arquivo JSON}
                            {--project=atitec : Slug do projeto padrão}
                            {--dry-run : Valida e mostra as tarefas sem gravar}';

    protected $description = 'Registra ou atualiza tarefas concluídas do projeto a partir de um manifest JSON';

    public function handle(): int
    {
        $manifest = $this->readManifest();

        if ($manifest === null) {
            return self::FAILURE;
        }

        $tasks = $manifest['tasks'] ?? null;

        if (! is_array($tasks) || $tasks === []) {
            $this->error('O manifest precisa conter um array "tasks" não vazio.');

            return self::FAILURE;
        }

        $defaultProject = (string) ($manifest['project'] ?? $this->option('project') ?? 'atitec');
        $prepared = [];
        $errors = [];

        foreach (array_values($tasks) as $index => $taskData) {
            if (! is_array($taskData)) {
                $errors[] = 'Tarefa #'.($index + 1).': formato inválido.';

                continue;
            }

            $validator = Validator::make($taskData, [
                'project' => ['nullable', 'string', 'max:120'],
                'deliverable' => ['required', 'string', 'max:160'],
                'title' => ['required', 'string', 'max:200'],
                'description' => ['nullable', 'string'],
                'hourly_rate' => ['nullable', 'numeric', 'min:0'],
                'hours_estimated' => ['nullable', 'numeric', 'min:0'],
                'hours_actual' => ['nullable', 'numeric', 'min:0'],
                'status' => ['nullable', Rule::in(array_keys(DeliverableTask::STATUSES))],
                'phase' => ['nullable', Rule::in(array_keys(DeliverableTask::PHASES))],
                'priority' => ['nullable', Rule::in(DeliverableTask::PRIORITIES)],
            ]);

            if ($validator->fails()) {
                $errors[] = 'Tarefa #'.($index + 1).': '.$validator->errors()->first();

                continue;
            }

            $data = $validator->validated();
            $projectSlug = $data['project'] ?? $defaultProject;
            $project = Project::where('slug', $projectSlug)->first();

            if (! $project) {
                $errors[] = 'Tarefa #'.($index + 1).": projeto '{$projectSlug}' não encontrado.";

                continue;
            }

            $deliverable = Deliverable::where('project_id', $project->id)
                ->where('name', $data['deliverable'])
                ->first();

            if (! $deliverable) {
                $errors[] = 'Tarefa #'.($index + 1).": entrega '{$data['deliverable']}' não encontrada no projeto '{$project->title}'.";

                continue;
            }

            $prepared[] = [
                'project' => $project,
                'deliverable' => $deliverable,
                'data' => $data,
            ];
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $userId = User::where('email', 'admin@atitec.com.br')->value('id');
        $dryRun = (bool) $this->option('dry-run');
        $created = 0;
        $updated = 0;

        $save = function () use ($prepared, $userId, $dryRun, &$created, &$updated): void {
            foreach ($prepared as $item) {
                $task = DeliverableTask::firstOrNew([
                    'project_id' => $item['project']->id,
                    'deliverable_id' => $item['deliverable']->id,
                    'title' => $item['data']['title'],
                ]);

                $isNew = ! $task->exists;

                if ($isNew) {
                    $task->fill([
                        'user_id' => $userId,
                        'description' => $item['data']['description'] ?? null,
                        'status' => $item['data']['status'] ?? 'concluido',
                        'priority' => $item['data']['priority'] ?? 'media',
                        'phase' => $item['data']['phase'] ?? '6',
                        'hours_estimated' => $item['data']['hours_estimated'] ?? null,
                        'hours_actual' => $item['data']['hours_actual'] ?? null,
                        'hourly_rate' => $item['data']['hourly_rate'] ?? 50,
                        'order' => ((int) $item['deliverable']->tasks()->max('order')) + 1,
                    ]);
                } else {
                    if (array_key_exists('description', $item['data']) && $item['data']['description'] !== null) {
                        $task->description = $item['data']['description'];
                    }

                    // Ao atualizar, valores nulos preservam o que já está registrado.
                    foreach (['hourly_rate', 'hours_estimated', 'hours_actual', 'status', 'phase', 'priority'] as $field) {
                        if (array_key_exists($field, $item['data']) && $item['data'][$field] !== null) {
                            $task->{$field} = $item['data'][$field];
                        }
                    }
                }

                if (! $dryRun) {
                    $task->save();
                }

                $isNew ? $created++ : $updated++;
                $this->line(($dryRun ? '[dry-run] ' : '').($isNew ? 'Criada' : 'Atualizada').": {$item['data']['title']} — {$item['project']->title} / {$item['deliverable']->name}");
            }
        };

        if ($dryRun) {
            $save();
        } else {
            DB::transaction(function () use ($save): void {
                $save();
            });
        }

        $this->info("Tarefas criadas: {$created}; atualizadas: {$updated}; projeto padrão: {$defaultProject}.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readManifest(): ?array
    {
        if ($this->option('stdin')) {
            $json = stream_get_contents(STDIN);
        } elseif ($path = $this->option('manifest')) {
            if (! is_string($path) || ! is_file($path)) {
                $this->error("Manifest não encontrado: {$path}");

                return null;
            }

            $json = file_get_contents($path);
        } else {
            $this->error('Informe --stdin ou --manifest.');

            return null;
        }

        if (! is_string($json) || trim($json) === '') {
            $this->error('Manifest vazio.');

            return null;
        }

        $data = json_decode($json, true);

        if (! is_array($data)) {
            $this->error('JSON inválido: '.json_last_error_msg());

            return null;
        }

        return $data;
    }
}
