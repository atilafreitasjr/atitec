<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMultiClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_add_and_edit_multiple_clients_on_a_project(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $primaryClient = Client::create(['name' => 'Cliente principal', 'email' => 'principal@teste.com']);
        $secondClient = Client::create(['name' => 'Segundo cliente', 'email' => 'segundo@teste.com']);
        $thirdClient = Client::create(['name' => 'Terceiro cliente', 'email' => 'terceiro@teste.com']);

        $projectData = [
            'client_id' => $primaryClient->id,
            'client_ids' => [$secondClient->id],
            'title' => 'Projeto compartilhado',
            'slug' => 'projeto-compartilhado',
            'description' => 'Projeto visível para mais de um cliente.',
            'status' => 'em_desenvolvimento',
            'progress' => 25,
            'sort_order' => 1,
        ];

        $this->actingAs($admin)
            ->post(route('admin.projects.store'), $projectData)
            ->assertRedirect(route('admin.projects.index'));

        $project = Project::where('slug', 'projeto-compartilhado')->firstOrFail();

        $this->assertDatabaseHas('client_project', [
            'project_id' => $project->id,
            'client_id' => $primaryClient->id,
        ]);
        $this->assertDatabaseHas('client_project', [
            'project_id' => $project->id,
            'client_id' => $secondClient->id,
        ]);

        $this->get(route('admin.projects.edit', $project))
            ->assertOk()
            ->assertSee('name="client_ids[]"', false)
            ->assertSee('Clientes com acesso ao projeto');

        $this->put(route('admin.projects.update', $project), [
            ...$projectData,
            'client_ids' => [$thirdClient->id],
        ])->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseMissing('client_project', [
            'project_id' => $project->id,
            'client_id' => $secondClient->id,
        ]);
        $this->assertDatabaseHas('client_project', [
            'project_id' => $project->id,
            'client_id' => $thirdClient->id,
        ]);
        $this->assertSame($primaryClient->id, $project->fresh()->client_id);

        $this->post(route('admin.invoices.store'), [
            'client_id' => $thirdClient->id,
            'project_id' => $project->id,
            'title' => 'Fatura do cliente adicional',
            'amount' => '250.00',
            'due_date' => today()->addDays(10)->format('Y-m-d'),
            'status' => 'pendente',
            'type' => 'projeto',
        ])->assertRedirect(route('admin.invoices.index'));

        $this->assertDatabaseHas('invoices', [
            'client_id' => $thirdClient->id,
            'project_id' => $project->id,
            'title' => 'Fatura do cliente adicional',
        ]);
    }

    public function test_additional_client_can_access_project_but_not_another_clients_invoice(): void
    {
        $primaryClient = Client::create(['name' => 'Cliente principal', 'email' => 'principal@workspace.test']);
        $additionalClient = Client::create(['name' => 'Cliente adicional', 'email' => 'adicional@workspace.test']);
        $otherClient = Client::create(['name' => 'Outro cliente', 'email' => 'outro@workspace.test']);

        $project = Project::create([
            'client_id' => $primaryClient->id,
            'title' => 'Projeto compartilhado',
            'slug' => 'projeto-compartilhado-workspace',
            'description' => 'Projeto disponível no workspace compartilhado.',
            'status' => 'em_desenvolvimento',
            'progress' => 50,
            'sort_order' => 1,
        ]);
        $project->clients()->syncWithoutDetaching([$additionalClient->id]);

        Invoice::create([
            'client_id' => $primaryClient->id,
            'project_id' => $project->id,
            'title' => 'Fatura privada do cliente principal',
            'amount' => 100,
            'due_date' => today()->addDays(10),
            'status' => 'pendente',
            'type' => 'projeto',
        ]);

        $additionalUser = User::factory()->create([
            'role' => 'cliente',
            'active' => true,
            'client_id' => $additionalClient->id,
        ]);
        $otherUser = User::factory()->create([
            'role' => 'cliente',
            'active' => true,
            'client_id' => $otherClient->id,
        ]);

        $this->actingAs($additionalUser)
            ->get(route('cliente.projeto', $project->id))
            ->assertOk()
            ->assertSee('Projeto compartilhado')
            ->assertDontSee('Fatura privada do cliente principal');

        $this->get(route('cliente.kanban', $project->id))->assertOk();

        $this->actingAs($otherUser)
            ->get(route('cliente.projeto', $project->id))
            ->assertNotFound();
    }
}
