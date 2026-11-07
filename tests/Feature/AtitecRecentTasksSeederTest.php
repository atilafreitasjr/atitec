<?php

namespace Tests\Feature;

use App\Models\Deliverable;
use App\Models\Project;
use Database\Seeders\AtitecRecentTasksSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtitecRecentTasksSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_atitec_tasks_are_added_without_hours_and_without_duplicates(): void
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
            'name' => 'Administração e CRM',
            'status' => 'concluido',
        ]);

        $this->seed(AtitecRecentTasksSeeder::class);
        $this->seed(AtitecRecentTasksSeeder::class);

        $tasks = $deliverable->fresh()->tasks;

        $this->assertCount(2, $tasks);
        $this->assertSame(2, $tasks->where('status', 'concluido')->count());
        $this->assertTrue($tasks->every(fn ($task) => $task->hours_estimated === null && $task->hours_actual === null));
        $this->assertDatabaseHas('deliverable_tasks', [
            'project_id' => $project->id,
            'deliverable_id' => $deliverable->id,
            'title' => 'Paginação compartilhada no admin e portal',
            'hourly_rate' => 0,
        ]);
    }
}
