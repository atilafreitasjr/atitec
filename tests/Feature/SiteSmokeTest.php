<?php

namespace Tests\Feature;

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

        $cliente = User::factory()->create(['role' => 'cliente']);
        $this->actingAs($cliente)->get('/dashboard')->assertRedirect('/cliente');
    }
}
