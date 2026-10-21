<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginas_publicas_respondem(): void
    {
        $this->seed();

        foreach (['/', '/sobre', '/portfolio', '/contato', '/orcamento', '/cartao', '/servicos/desenvolvimento-sob-medida'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/portfolio/sistema-ovinos')->assertOk();
        $this->get('/')->assertSee('Agricultura Familiar');
        $this->get('/cartao')->assertSee('Átila de Freitas Jr')->assertSee('Agricultura Familiar');
        $this->get('/cartao/vcard')->assertOk()->assertDownload('atila-de-freitas-jr.vcf');
    }

    public function test_orcamento_cria_lead(): void
    {
        $this->post('/orcamento', [
            'name' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'project_type' => 'Software sob medida',
            'details' => 'Preciso de um sistema.',
        ])->assertRedirect('/orcamento');

        $this->assertDatabaseHas('leads', ['email' => 'cliente@teste.com', 'status' => 'novo']);
    }

    public function test_orcamento_estima_investimento_a_partir_das_escolhas(): void
    {
        $this->seed();

        $this->post('/orcamento', [
            'name' => 'Cliente Estimativa',
            'email' => 'estimativa@teste.com',
            'project_type' => 'Software sob medida',
            'requirements' => ['painel', 'whatsapp'],
            'infrastructure' => ['servidor', 'dominio'],
            'deadline' => 'normal',
            'budget_range' => 'R$ 4.000 a R$ 6.000',
            'details' => 'Preciso de um sistema com painel e WhatsApp.',
        ])->assertRedirect('/orcamento');

        $lead = Lead::where('email', 'estimativa@teste.com')->firstOrFail();
        $estimate = $lead->requirements;

        // 120 h (tipo) + 20 h (painel) + 12 h (WhatsApp) = 152 h × R$ 50 × 1,1 (prazo) = R$ 8.360
        // (o JSON do MariaDB normaliza números inteiros — daí a comparação exata)
        $this->assertSame(152, $estimate['hours']);
        $this->assertSame(8360, $estimate['development']);
        $this->assertSame(2050, $estimate['setup']); // servidor R$ 2.000 + domínio R$ 50
        $this->assertSame(150, $estimate['monthly']); // servidor R$ 150/mês
        $this->assertSame(10410, $estimate['total']);
        $this->assertSame(['min' => 8300, 'max' => 13100], $estimate['range']);

        $admin = User::where('email', 'admin@atitec.com.br')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertSee('Perfil do orçamento e estimativa')
            ->assertSee('Painel administrativo')
            ->assertSee('Servidor e banco de dados dedicados')
            ->assertSee('Total estimado:');
    }

    public function test_markdown_do_orcamento_e_renderizado_com_seguranca_no_admin(): void
    {
        $this->seed();

        $this->post('/orcamento', [
            'name' => 'Cliente Markdown',
            'email' => 'markdown@teste.com',
            'project_type' => 'Software sob medida',
            'details' => "**Importante**\n\n- Prazo curto\n- Orçamento definido\n\n<script>alert('xss')</script>\n\n[link ruim](javascript:alert('xss'))",
        ])->assertRedirect('/orcamento');

        $lead = Lead::where('email', 'markdown@teste.com')->firstOrFail();
        $admin = User::where('email', 'admin@atitec.com.br')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertSee('<strong>Importante</strong>', false)
            ->assertSee('<li>Prazo curto</li>', false)
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('href="javascript:', false);
    }

    public function test_orcamento_de_cliente_existente_vincula_ao_workspace(): void
    {
        $client = Client::create(['name' => 'Cliente Existente', 'email' => 'existente@teste.com']);

        $this->post('/orcamento', [
            'name' => $client->name,
            'email' => $client->email,
            'project_type' => 'Software sob medida',
            'details' => 'Novo módulo para o projeto.',
        ])->assertRedirect('/orcamento');

        $this->assertDatabaseHas('leads', [
            'email' => $client->email,
            'client_id' => $client->id,
            'source' => 'site/orcamento',
        ]);
    }

    public function test_formularios_publicos_reutilizam_os_dados_do_cliente_autenticado(): void
    {
        $client = Client::create([
            'name' => 'Cliente Workspace',
            'email' => 'workspace@teste.com',
            'phone' => '(41) 99999-1111',
            'company' => 'Empresa Workspace',
        ]);
        $user = User::factory()->create([
            'name' => $client->name,
            'email' => $client->email,
            'role' => 'cliente',
            'client_id' => $client->id,
        ]);

        $this->actingAs($user)->get('/orcamento')
            ->assertOk()
            ->assertSee('value="Cliente Workspace"', false)
            ->assertSee('value="Empresa Workspace"', false)
            ->assertSee('value="workspace@teste.com"', false)
            ->assertSee('value="(41) 99999-1111"', false);

        $this->get('/contato')->assertSee('value="Cliente Workspace"', false)->assertSee('value="workspace@teste.com"', false);
        $this->get('/cartao')->assertSee('value="(41) 99999-1111"', false);
    }

    public function test_cartao_cria_lead(): void
    {
        $this->post('/cartao/contato', [
            'name' => 'Contato Cartao',
            'phone' => '(42) 90000-0000',
        ])->assertRedirect('/cartao');

        $this->assertDatabaseHas('leads', ['phone' => '(42) 90000-0000', 'source' => 'site/cartao']);
    }

    public function test_admin_exige_perfil(): void
    {
        $this->seed();

        $this->get('/admin')->assertRedirect('/login');

        $cliente = User::factory()->create(['role' => 'cliente']);
        $this->actingAs($cliente)->get('/admin')->assertForbidden();

        $admin = User::where('email', 'admin@atitec.com.br')->first();
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_dashboard_redireciona_por_perfil(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@atitec.com.br')->first();
        $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin');

        // Cliente sem cadastro completo informa os dados antes de acessar o portal.
        $semCadastro = User::factory()->create(['role' => 'cliente']);
        $this->actingAs($semCadastro)->get('/dashboard')->assertRedirect('/cadastro/completar');

        $cliente = User::factory()->create([
            'role' => 'cliente',
            'client_id' => Client::create(['name' => 'Cliente', 'email' => 'cliente@teste.com'])->id,
        ]);
        $this->actingAs($cliente)->get('/dashboard')->assertRedirect('/cliente');
    }
}
