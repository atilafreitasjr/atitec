<?php

namespace Tests\Feature;

use App\Livewire\Task\Edit;
use App\Models\Client;
use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspaceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'active' => true]);
    }

    private function clientWithUser(): array
    {
        $email = 'cliente+'.uniqid().'@workspace.test';
        $client = Client::create(['name' => 'Cliente Workspace', 'email' => $email, 'phone' => '(41) 99999-0000']);
        $user = User::factory()->create(['role' => 'cliente', 'active' => true, 'client_id' => $client->id]);
        $client->update(['user_id' => $user->id]);

        return [$client, $user];
    }

    public function test_orcamento_vinculado_recebe_proposta_e_pode_gerar_projeto(): void
    {
        [$client, $clientUser] = $this->clientWithUser();
        $admin = $this->admin();
        $lead = Lead::create([
            'name' => $client->name,
            'email' => $client->email,
            'project_type' => 'Portal de clientes',
            'details' => 'Construir um portal com financeiro e documentos.',
            'status' => 'novo',
            'source' => 'site/orcamento',
        ]);

        $this->actingAs($admin)->put(route('admin.leads.update', $lead), [
            'client_id' => $client->id,
            'status' => 'em_contato',
        ])->assertRedirect(route('admin.leads.show', $lead));

        $this->get(route('admin.leads.show', $lead))->assertOk()->assertSee('Preparar proposta');

        $this->actingAs($admin)->post(route('admin.leads.proposal', $lead), [
            'proposal_title' => 'Portal de clientes',
            'proposal_summary' => 'Escopo, etapas e entregas acordadas.',
            'proposal_amount' => '12500.00',
            'proposal_valid_until' => today()->addDays(20)->format('Y-m-d'),
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', ['id' => $lead->id, 'proposal_status' => 'enviada', 'client_id' => $client->id]);

        $this->actingAs($clientUser)
            ->get(route('cliente.orcamentos'))
            ->assertOk()
            ->assertSee('Portal de clientes')
            ->assertSee('R$ 12.500,00')
            ->assertSee('Aceitar proposta');

        $this->post(route('cliente.orcamentos.responder', $lead), ['resposta' => 'aceita'])
            ->assertRedirect(route('cliente.orcamentos'));
        $this->assertSame('aceita', $lead->fresh()->proposal_status);

        $response = $this->actingAs($admin)->post(route('admin.leads.convert', $lead));
        $project = Project::where('client_id', $client->id)->firstOrFail();

        $response->assertRedirect(route('admin.projects.show', $project));
        $this->get(route('admin.projects.show', $project))->assertOk()->assertSee('Workspace do projeto');
        $this->assertSame($project->id, $lead->fresh()->converted_project_id);
        $this->assertSame('Portal de clientes', $project->title);
        $this->assertSame('12500.00', $project->budget);
    }

    public function test_admin_cria_acesso_do_cliente_vinculado_ao_workspace(): void
    {
        $client = Client::create(['name' => 'Cliente sem login', 'email' => 'sem-login@workspace.test']);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.users.create', ['client_id' => $client->id]))
            ->assertOk()
            ->assertSee('Cliente sem login');

        $this->post(route('admin.users.store'), [
            'name' => $client->name,
            'email' => $client->email,
            'password' => 'senha-forte-123',
            'password_confirmation' => 'senha-forte-123',
            'role' => 'cliente',
            'client_id' => $client->id,
            'active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', $client->email)->firstOrFail();
        $this->assertSame($client->id, $user->client_id);
        $this->assertSame($user->id, $client->fresh()->user_id);

        $this->actingAs($user)->get(route('cliente.dashboard'))
            ->assertOk()
            ->assertSee('Workspace ATITEC')
            ->assertSee('Área do cliente')
            ->assertSee('Meus projetos');
    }

    public function test_workspace_do_cliente_usa_adminlte_com_menu_do_portal_sem_itens_do_admin(): void
    {
        [, $clientUser] = $this->clientWithUser();

        $this->actingAs($clientUser)->get(route('cliente.dashboard'))
            ->assertOk()
            ->assertSee('Meu painel')
            ->assertSee('Meus projetos')
            ->assertSee('Financeiro')
            ->assertSee('Ajuda')
            ->assertDontSee('Leads / Orçamentos')
            ->assertDontSee('Papéis e permissões')
            ->assertDontSee('Canais de contato');

        // O menu administrativo continua intacto (filtrado por permissão Spatie).
        $this->actingAs($this->admin())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Leads / Orçamentos')
            ->assertSee('Papéis e permissões')
            ->assertDontSee('Meu painel');
    }

    public function test_cliente_acompanha_o_kanban_somente_leitura_no_workspace(): void
    {
        [$client, $clientUser] = $this->clientWithUser();
        $project = Project::create([
            'client_id' => $client->id,
            'title' => 'Projeto do cliente',
            'slug' => 'projeto-do-cliente-kanban',
            'description' => 'Projeto.',
            'status' => 'em_desenvolvimento',
            'progress' => 40,
            'sort_order' => 1,
        ]);
        $deliverable = Deliverable::create(['project_id' => $project->id, 'name' => 'Entrega do cliente']);
        DeliverableTask::create([
            'project_id' => $project->id,
            'deliverable_id' => $deliverable->id,
            'title' => 'Tarefa acompanhada',
            'status' => 'andamento',
            'priority' => 'alta',
            'phase' => '4',
        ]);

        $this->actingAs($clientUser)->get(route('cliente.kanban', $project))
            ->assertOk()
            ->assertSee('Somente leitura')
            ->assertSee('Desenvolvimento')
            ->assertSee('Tarefa acompanhada')
            ->assertDontSee('title="Excluir"', false);
    }

    public function test_custo_de_tarefas_concluidas_vira_fatura_sem_faturar_duas_vezes(): void
    {
        [$client] = $this->clientWithUser();
        $admin = $this->admin();
        $project = Project::create([
            'client_id' => $client->id,
            'title' => 'Projeto faturável',
            'slug' => 'projeto-faturavel',
            'description' => 'Projeto de teste.',
            'status' => 'em_desenvolvimento',
            'progress' => 60,
            'sort_order' => 1,
        ]);
        $deliverable = Deliverable::create(['project_id' => $project->id, 'name' => 'Implementação']);
        $task = DeliverableTask::create([
            'project_id' => $project->id,
            'deliverable_id' => $deliverable->id,
            'title' => 'Construção da tela',
            'status' => 'concluido',
            'priority' => 'media',
            'hours_estimated' => 3,
            'hours_actual' => 2.5,
            'hourly_rate' => 120,
        ]);

        $this->actingAs($admin)->post(route('admin.projects.invoices.from-tasks', $project), [
            'due_date' => today()->addDays(14)->format('Y-m-d'),
        ])->assertRedirect();

        $invoice = Invoice::where('project_id', $project->id)->firstOrFail();
        $this->assertSame('300.00', $invoice->amount);
        $this->assertSame($invoice->id, $task->fresh()->invoice_id);
        $this->assertSame(0.0, $project->fresh()->unbilled_actual_cost);

        $this->from(route('admin.projects.show', $project))
            ->post(route('admin.projects.invoices.from-tasks', $project), ['due_date' => today()->addDays(14)->format('Y-m-d')])
            ->assertSessionHasErrors('project');
        $this->assertSame(1, Invoice::where('project_id', $project->id)->count());
    }

    public function test_pagamentos_parciais_atualizam_saldo_e_status_da_fatura(): void
    {
        [$client] = $this->clientWithUser();
        $admin = $this->admin();
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'title' => 'Boleto de teste',
            'amount' => 500,
            'due_date' => today()->addDays(10)->format('Y-m-d'),
            'status' => 'pendente',
            'type' => 'projeto',
        ]);

        $this->actingAs($admin)->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 200,
            'paid_at' => today()->format('Y-m-d'),
            'method' => 'pix',
        ])->assertRedirect();

        $this->assertSame('pendente', $invoice->fresh()->status);
        $this->assertSame(300.0, $invoice->fresh()->balance());

        $this->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 300,
            'paid_at' => today()->format('Y-m-d'),
            'method' => 'boleto',
        ])->assertRedirect();

        $this->assertSame('pago', $invoice->fresh()->status);
        $this->assertSame(0.0, $invoice->fresh()->balance());
        $this->assertSame(2, $invoice->payments()->count());

        $this->actingAs($admin)->get(route('admin.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('wa.me')
            ->assertSee('Registrar pagamento');
    }

    public function test_cortesia_da_atitec_quita_a_fatura_e_zera_o_saldo(): void
    {
        [$client] = $this->clientWithUser();
        $admin = $this->admin();
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'title' => 'Fatura com cortesia',
            'amount' => 500,
            'due_date' => today()->addDays(10)->format('Y-m-d'),
            'status' => 'pendente',
            'type' => 'projeto',
        ]);

        $this->actingAs($admin)->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 200,
            'paid_at' => today()->format('Y-m-d'),
            'method' => 'pix',
        ])->assertRedirect();

        $this->post(route('admin.invoices.discount', $invoice), [
            'discount_amount' => '300.00',
            'discount_reason' => 'Cortesia ATITEC — diferença assumida pela empresa',
        ])->assertRedirect(route('admin.invoices.show', $invoice));

        $invoice->refresh();
        $this->assertSame('pago', $invoice->status);
        $this->assertSame(0.0, $invoice->balance());
        $this->assertSame(300.0, $invoice->discountAmount());

        // Desconto não pode passar do que falta receber.
        $outra = Invoice::create([
            'client_id' => $client->id,
            'title' => 'Fatura sem pagamentos',
            'amount' => 100,
            'due_date' => today()->addDays(5)->format('Y-m-d'),
            'status' => 'pendente',
            'type' => 'projeto',
        ]);
        $this->post(route('admin.invoices.discount', $outra), ['discount_amount' => '150.00'])
            ->assertSessionHasErrors('discount_amount');
    }

    public function test_pagamento_pode_ser_editado_e_removido_com_recalculo_do_saldo(): void
    {
        [$client] = $this->clientWithUser();
        $admin = $this->admin();
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'title' => 'Fatura editável',
            'amount' => 500,
            'due_date' => today()->addDays(10)->format('Y-m-d'),
            'status' => 'pendente',
            'type' => 'projeto',
        ]);

        $this->actingAs($admin)->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 200,
            'paid_at' => today()->format('Y-m-d'),
            'method' => 'pix',
        ])->assertRedirect();

        $payment = Payment::where('invoice_id', $invoice->id)->firstOrFail();

        $this->put(route('admin.invoices.payments.update', [$invoice, $payment]), [
            'amount' => '350.00',
            'paid_at' => today()->format('Y-m-d'),
            'method' => 'boleto',
            'notes' => 'Valor corrigido',
        ])->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertSame(350.0, $invoice->fresh()->paidAmount());
        $this->assertSame(150.0, $invoice->fresh()->balance());
        $this->assertSame('boleto', $payment->fresh()->method);

        $this->delete(route('admin.invoices.payments.destroy', [$invoice, $payment]))
            ->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertSame(0, $invoice->payments()->count());
        $this->assertSame(500.0, $invoice->fresh()->balance());
        $this->assertSame('pendente', $invoice->fresh()->status);
    }

    public function test_fatura_nao_pode_vincular_cliente_diferente_do_projeto(): void
    {
        $admin = $this->admin();
        [$projectClient] = $this->clientWithUser();
        [$otherClient] = $this->clientWithUser();
        $project = Project::create([
            'client_id' => $projectClient->id,
            'title' => 'Projeto do primeiro cliente',
            'slug' => 'projeto-primeiro-cliente',
            'description' => 'Projeto.',
            'status' => 'em_desenvolvimento',
            'progress' => 0,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'client_id' => $otherClient->id,
            'project_id' => $project->id,
            'title' => 'Fatura inconsistente',
            'amount' => '100.00',
            'due_date' => today()->addDays(10)->format('Y-m-d'),
            'status' => 'pendente',
            'type' => 'projeto',
        ])->assertSessionHasErrors('project_id');

        $this->assertDatabaseMissing('invoices', ['title' => 'Fatura inconsistente']);
    }

    public function test_documento_privado_do_projeto_so_e_baixado_pelo_cliente_vinculado(): void
    {
        Storage::fake('local');
        [$client, $clientUser] = $this->clientWithUser();
        $project = Project::create([
            'client_id' => $client->id,
            'title' => 'Projeto com contrato',
            'slug' => 'projeto-com-contrato',
            'description' => 'Projeto.',
            'status' => 'prospeccao',
            'progress' => 0,
            'sort_order' => 1,
        ]);
        $deliverable = Deliverable::create(['project_id' => $project->id, 'name' => 'Entrega principal']);
        DeliverableTask::create([
            'project_id' => $project->id,
            'deliverable_id' => $deliverable->id,
            'title' => 'Implementação acompanhável',
            'status' => 'concluido',
            'priority' => 'media',
            'hours_actual' => 1.5,
            'hourly_rate' => 100,
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)->from(route('admin.projects.show', $project))
            ->post(route('admin.projects.documents.store', $project), [
                'name' => 'Contrato de desenvolvimento',
                'category' => 'contrato',
                'file' => UploadedFile::fake()->create('contrato.pdf', 150, 'application/pdf'),
                'visible_to_client' => '1',
            ])->assertRedirect();

        $document = ProjectDocument::where('project_id', $project->id)->firstOrFail();
        Storage::disk('local')->assertExists($document->path);

        $this->actingAs($clientUser)
            ->get(route('cliente.projeto', $project))
            ->assertOk()
            ->assertSee('Entrega principal')
            ->assertSee('Implementação acompanhável')
            ->assertSee('R$ 150,00')
            ->assertSee('Contrato de desenvolvimento');

        $this->actingAs($clientUser)
            ->get(route('project-documents.download', [$project, $document]))
            ->assertDownload('Contrato de desenvolvimento.pdf');

        $internalPath = 'project-documents/'.$project->id.'/internal.pdf';
        Storage::disk('local')->put($internalPath, 'internal-content');
        $internal = ProjectDocument::create([
            'project_id' => $project->id,
            'uploaded_by' => $admin->id,
            'name' => 'Nota interna',
            'category' => 'outro',
            'path' => $internalPath,
            'visible_to_client' => false,
        ]);
        $this->get(route('project-documents.download', [$project, $internal]))->assertNotFound();

        [$otherClient, $otherUser] = $this->clientWithUser();
        $this->actingAs($otherUser)
            ->get(route('project-documents.download', [$project, $document]))
            ->assertNotFound();
    }

    public function test_cliente_baixa_apenas_o_proprio_boleto(): void
    {
        Storage::fake('local');
        [$client, $clientUser] = $this->clientWithUser();
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'title' => 'Boleto workspace',
            'amount' => 250,
            'due_date' => today()->addDays(7)->format('Y-m-d'),
            'status' => 'pendente',
            'type' => 'projeto',
            'pdf_path' => 'invoices/1/boleto.pdf',
        ]);
        Storage::disk('local')->put($invoice->pdf_path, 'pdf-content');

        $this->actingAs($clientUser)
            ->get(route('cliente.fatura', $invoice))
            ->assertOk()
            ->assertSee('Baixar boleto / documento PDF');
        $this->get(route('invoices.pdf', $invoice))->assertDownload('boleto-'.$invoice->id.'.pdf');

        [, $otherUser] = $this->clientWithUser();
        $this->actingAs($otherUser)->get(route('invoices.pdf', $invoice))->assertNotFound();
    }

    public function test_tarefa_faturada_nao_permite_alterar_horas_ou_valor_hora(): void
    {
        [$client] = $this->clientWithUser();
        $admin = $this->admin();
        $project = Project::create([
            'client_id' => $client->id,
            'title' => 'Projeto com tarefa faturada',
            'slug' => 'projeto-tarefa-faturada',
            'description' => 'Projeto.',
            'status' => 'em_desenvolvimento',
            'progress' => 20,
            'sort_order' => 1,
        ]);
        $deliverable = Deliverable::create(['project_id' => $project->id, 'name' => 'Entrega']);
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'title' => 'Fatura da tarefa',
            'amount' => 100,
            'due_date' => today()->addDays(5),
            'status' => 'pendente',
            'type' => 'projeto',
        ]);
        $task = DeliverableTask::create([
            'project_id' => $project->id,
            'deliverable_id' => $deliverable->id,
            'invoice_id' => $invoice->id,
            'title' => 'Tarefa já faturada',
            'status' => 'concluido',
            'priority' => 'media',
            'hours_actual' => 1,
            'hourly_rate' => 100,
        ]);

        Livewire::actingAs($admin)
            ->test(Edit::class, ['projectId' => $project->id, 'taskId' => $task->id])
            ->set('hourly_rate', '150')
            ->call('save')
            ->assertHasErrors('financial');

        $this->assertSame('100.00', $task->fresh()->hourly_rate);
    }
}
