<?php

namespace Tests\Feature;

use App\Livewire\Kanban\Board;
use App\Models\Client;
use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\KanbanRolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KanbanProjectIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(KanbanRolePermissionSeeder::class);
    }

    private function makeProject(string $title, ?Client $client = null): Project
    {
        return Project::create([
            'client_id' => $client?->id,
            'title' => $title,
            'slug' => str()->slug($title).'-'.str()->random(4),
            'description' => 'Projeto de teste',
            'status' => 'em_desenvolvimento',
            'progress' => 0,
            'sort_order' => 0,
        ]);
    }

    public function test_kanban_mostra_apenas_dados_do_projeto(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $projectA = $this->makeProject('Projeto A');
        $projectB = $this->makeProject('Projeto B');

        $delA = Deliverable::create(['project_id' => $projectA->id, 'name' => 'Entrega Exclusiva A', 'status' => 'andamento']);
        $delB = Deliverable::create(['project_id' => $projectB->id, 'name' => 'Entrega Exclusiva B', 'status' => 'andamento']);
        DeliverableTask::create(['project_id' => $projectA->id, 'deliverable_id' => $delA->id, 'title' => 'Tarefa Secreta A', 'status' => 'pendente', 'priority' => 'media']);
        DeliverableTask::create(['project_id' => $projectB->id, 'deliverable_id' => $delB->id, 'title' => 'Tarefa Secreta B', 'status' => 'pendente', 'priority' => 'media']);

        $this->actingAs($admin);

        $this->get(route('admin.projects.kanban', $projectA))
            ->assertOk()
            ->assertSee('Tarefa Secreta A')
            ->assertDontSee('Tarefa Secreta B');

        $this->get(route('admin.projects.deliverables.index', $projectB))
            ->assertOk()
            ->assertSee('Entrega Exclusiva B')
            ->assertDontSee('Entrega Exclusiva A');
    }

    public function test_movimentar_tarefa_de_outro_projeto_falha(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $projectA = $this->makeProject('Projeto A');
        $projectB = $this->makeProject('Projeto B');

        $delB = Deliverable::create(['project_id' => $projectB->id, 'name' => 'Entrega B', 'status' => 'andamento']);
        $taskB = DeliverableTask::create(['project_id' => $projectB->id, 'deliverable_id' => $delB->id, 'title' => 'Tarefa B', 'status' => 'pendente', 'priority' => 'media']);

        $this->actingAs($admin);

        Livewire::test(Board::class, ['projectId' => $projectA->id])
            ->call('moveTask', $taskB->id, 'up')
            ->assertNotFound();

        $this->assertEquals('pendente', $taskB->fresh()->status);
    }

    public function test_cliente_so_acessa_projeto_do_proprio_cadastro(): void
    {
        $clientA = Client::create(['name' => 'Cliente A', 'email' => 'a@teste.com']);
        $clientB = Client::create(['name' => 'Cliente B', 'email' => 'b@teste.com']);
        $projectA = $this->makeProject('Projeto A', $clientA);
        $projectB = $this->makeProject('Projeto B', $clientB);

        $user = User::factory()->create(['role' => 'cliente', 'client_id' => $clientA->id]);
        $this->actingAs($user);

        $this->get(route('admin.projects.kanban', $projectA))->assertForbidden();
        $this->get(route('cliente.kanban', $projectA->id))->assertOk();
        $this->get(route('cliente.kanban', $projectB->id))->assertNotFound();
    }

    public function test_rotas_exigem_permissao(): void
    {
        $financeiro = User::factory()->create(['role' => 'financeiro']);
        $project = $this->makeProject('Projeto X');

        $this->actingAs($financeiro);
        $this->get(route('admin.projects.kanban', $project))->assertOk();
        $this->get(route('admin.projects.deliverables.create', $project))->assertForbidden();
        $this->get(route('admin.projects.tasks.create', $project))->assertForbidden();
    }
}
