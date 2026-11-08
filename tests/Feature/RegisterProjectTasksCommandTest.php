<?php

namespace Tests\Feature;

use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterProjectTasksCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_tasks_idempotently_with_fifty_per_hour_and_no_hours(): void
    {
        [$project, $deliverable] = $this->makeProject();
        $path = $this->manifest([
            [
                'deliverable' => $deliverable->name,
                'title' => 'Tarefa do PSP',
                'description' => 'Registrada pelo PSP.',
                'hourly_rate' => 50,
            ],
        ]);

        try {
            $this->artisan('project:tasks:register', ['--manifest' => $path])
                ->assertExitCode(0);

            $this->assertDatabaseHas('deliverable_tasks', [
                'project_id' => $project->id,
                'deliverable_id' => $deliverable->id,
                'title' => 'Tarefa do PSP',
                'hourly_rate' => 50,
                'status' => 'concluido',
                'phase' => '6',
                'priority' => 'media',
            ]);

            $this->artisan('project:tasks:register', ['--manifest' => $path])
                ->assertExitCode(0);

            $this->assertSame(1, DeliverableTask::count());

            $task = DeliverableTask::firstOrFail();

            $this->assertNull($task->hours_estimated);
            $this->assertNull($task->hours_actual);
        } finally {
            @unlink($path);
        }
    }

    public function test_preserves_existing_hours_when_manifest_sends_null(): void
    {
        [$project, $deliverable] = $this->makeProject();

        $task = DeliverableTask::create([
            'project_id' => $project->id,
            'deliverable_id' => $deliverable->id,
            'title' => 'Tarefa existente',
            'status' => 'concluido',
            'priority' => 'media',
            'phase' => '6',
            'hours_estimated' => 4,
            'hours_actual' => 4,
            'hourly_rate' => 0,
        ]);

        $path = $this->manifest([
            [
                'deliverable' => $deliverable->name,
                'title' => 'Tarefa existente',
                'hourly_rate' => 50,
                'hours_estimated' => null,
                'hours_actual' => null,
            ],
        ]);

        try {
            $this->artisan('project:tasks:register', ['--manifest' => $path])
                ->assertExitCode(0);

            $task->refresh();

            $this->assertSame(4.0, (float) $task->hours_estimated);
            $this->assertSame(4.0, (float) $task->hours_actual);
            $this->assertSame(50.0, (float) $task->hourly_rate);
        } finally {
            @unlink($path);
        }
    }

    public function test_dry_run_does_not_create_tasks(): void
    {
        [, $deliverable] = $this->makeProject();
        $path = $this->manifest([
            [
                'deliverable' => $deliverable->name,
                'title' => 'Tarefa simulada',
            ],
        ]);

        try {
            $this->artisan('project:tasks:register', [
                '--manifest' => $path,
                '--dry-run' => true,
            ])->assertExitCode(0);

            $this->assertSame(0, DeliverableTask::count());
        } finally {
            @unlink($path);
        }
    }

    public function test_fails_for_unknown_deliverable_without_creating_tasks(): void
    {
        $this->makeProject();
        $path = $this->manifest([
            [
                'deliverable' => 'Entrega inexistente',
                'title' => 'Tarefa inválida',
            ],
        ]);

        try {
            $this->artisan('project:tasks:register', ['--manifest' => $path])
                ->assertExitCode(1);

            $this->assertSame(0, DeliverableTask::count());
        } finally {
            @unlink($path);
        }
    }

    /**
     * @return array{0: Project, 1: Deliverable}
     */
    private function makeProject(): array
    {
        $project = Project::create([
            'title' => 'ATITEC',
            'slug' => 'atitec',
            'description' => 'Projeto interno da ATITEC.',
            'status' => 'suporte',
            'progress' => 100,
            'sort_order' => 0,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'name' => 'Site institucional',
            'status' => 'concluido',
        ]);

        return [$project, $deliverable];
    }

    /**
     * @param  array<int, array<string, mixed>>  $tasks
     */
    private function manifest(array $tasks): string
    {
        $path = tempnam(sys_get_temp_dir(), 'psp-tasks-').'.json';

        file_put_contents($path, json_encode([
            'project' => 'atitec',
            'tasks' => $tasks,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $path;
    }
}
