<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocsTest extends TestCase
{
    use RefreshDatabase;

    public function test_docs_publicas_renderizam_o_guia_do_cliente(): void
    {
        $this->get('/docs')
            ->assertOk()
            ->assertSee('Guia do Cliente')
            ->assertSee('Visão geral');

        $this->get('/docs/acesso')->assertOk()->assertSee('Acesso e cadastro');
        $this->get('/docs/financeiro')->assertOk()->assertSee('Financeiro e cobranças');
        $this->get('/docs/pagina-inexistente')->assertNotFound();
    }

    public function test_links_entre_paginas_do_guia_sao_reescritos_para_o_contexto(): void
    {
        $this->get('/docs')->assertOk()->assertSee('href="'.url('docs/acesso').'"', false);

        $client = Client::create(['name' => 'Cliente Guia', 'email' => 'guia@teste.com']);
        $user = User::factory()->create(['role' => 'cliente', 'client_id' => $client->id]);

        $this->actingAs($user)->get(route('cliente.ajuda'))
            ->assertOk()
            ->assertSee('Central de ajuda')
            ->assertSee('href="'.url('cliente/ajuda/acesso').'"', false);
    }

    public function test_workspace_do_cliente_tem_central_de_ajuda_com_menu(): void
    {
        $client = Client::create(['name' => 'Cliente Guia', 'email' => 'guia2@teste.com']);
        $user = User::factory()->create(['role' => 'cliente', 'client_id' => $client->id]);

        $this->actingAs($user)->get(route('cliente.ajuda'))
            ->assertOk()
            ->assertSee('Como funciona o seu workspace')
            ->assertSee('Tópicos do guia')
            ->assertSee('href="'.route('cliente.ajuda').'"', false);

        $this->get(route('cliente.dashboard'))->assertOk()->assertSee('Ajuda');

        $this->get(route('cliente.ajuda', ['page' => 'orcamentos']))
            ->assertOk()
            ->assertSee('Orçamentos e propostas');
    }
}
