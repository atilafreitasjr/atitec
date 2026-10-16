<?php

namespace Tests\Feature;

use App\Models\Client;
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

    public function test_orcamento_de_cliente_existente_vincula_ao_workspace(): void
    {
        $client = Client::create(['name' => 'Cliente Existente', 'email' => 'existente@teste.com']);

        $this->post('/orcamento', [
            'name' => $client->name,
            'email' => $client->email,
            'project_type' => 'Sistema interno',
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
