<?php

namespace Tests\Feature;

use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use Database\Seeders\AtitecRecentTasksSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtitecRecentTasksSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_atitec_tasks_use_fifty_per_hour_without_hours_and_without_duplicates(): void
    {
        $project = Project::create([
            'title' => 'ATITEC',
            'slug' => 'atitec',
            'description' => 'Projeto interno da ATITEC.',
            'status' => 'suporte',
            'progress' => 100,
            'sort_order' => 0,
        ]);

        foreach (['Administração e CRM', 'Fundação do projeto e infraestrutura', 'Site institucional'] as $name) {
            Deliverable::create([
                'project_id' => $project->id,
                'name' => $name,
                'status' => 'concluido',
            ]);
        }

        $this->seed(AtitecRecentTasksSeeder::class);
        $this->seed(AtitecRecentTasksSeeder::class);

        $tasks = DeliverableTask::where('project_id', $project->id)->get();

        $this->assertCount(5, $tasks);
        $this->assertSame(5, $tasks->where('status', 'concluido')->count());
        $this->assertTrue($tasks->every(fn ($task) => $task->hours_estimated === null && $task->hours_actual === null));
        $this->assertTrue($tasks->every(fn ($task) => (float) $task->hourly_rate === 50.0));
        $this->assertDatabaseHas('deliverable_tasks', [
            'project_id' => $project->id,
            'title' => 'Troca persistente de tema claro/escuro no painel',
            'hourly_rate' => 50,
        ]);
        $this->assertDatabaseHas('deliverable_tasks', [
            'project_id' => $project->id,
            'title' => 'Tema claro fiel na home pública com switch persistente',
            'hourly_rate' => 50,
        ]);
    }
}
