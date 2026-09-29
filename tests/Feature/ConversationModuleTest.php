<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientMessage;
use App\Models\Conversation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationModuleTest extends TestCase
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

    public function test_pagina_de_nova_conversa_abre(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.conversations.create'))
            ->assertOk()
            ->assertSee('Nova conversa');
    }

    public function test_cria_conversa_com_cliente(): void
    {
        $admin = $this->admin();
        $client = Client::create(['name' => 'Cliente A', 'email' => 'a@teste.com']);
        $this->actingAs($admin);

        $this->post(route('admin.conversations.store'), [
            'subject' => 'Entrega da semana',
            'destino' => 'cliente',
            'client_id' => $client->id,
            'body' => 'Olá, seguem os ajustes.',
        ])->assertRedirect();

        $conversation = Conversation::firstOrFail();
        $this->assertSame($client->id, $conversation->client_id);
        $this->assertFalse($conversation->is_broadcast);
        $this->assertEquals(1, $conversation->messages()->count());
    }

    public function test_comunicado_geral_nao_vincula_cliente(): void
    {
        $client = Client::create(['name' => 'Cliente A', 'email' => 'a@teste.com']);
        $this->actingAs($this->admin());

        $this->post(route('admin.conversations.store'), [
            'subject' => 'Aviso de manutenção',
            'destino' => 'geral',
            'client_id' => $client->id,
            'body' => 'Sistema em manutenção sábado.',
        ])->assertRedirect();

        $conversation = Conversation::firstOrFail();
        $this->assertTrue($conversation->is_broadcast);
        $this->assertNull($conversation->client_id);
    }

    public function test_conversa_com_cliente_exige_cliente(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('admin.conversations.store'), [
            'subject' => 'Sem cliente',
            'destino' => 'cliente',
            'body' => 'Mensagem',
        ])->assertSessionHasErrors('client_id');
    }

    public function test_abrir_conversa_marca_mensagens_do_cliente_como_lidas(): void
    {
        $admin = $this->admin();
        $client = Client::create(['name' => 'Cliente A', 'email' => 'a@teste.com']);
        $clientUser = User::factory()->create(['role' => 'cliente', 'client_id' => $client->id, 'active' => true]);

        $conversation = Conversation::create([
            'subject' => 'Dúvida', 'client_id' => $client->id, 'created_by' => $clientUser->id,
        ]);
        $mensagem = ClientMessage::create([
            'conversation_id' => $conversation->id, 'user_id' => $clientUser->id, 'body' => 'Preciso de ajuda',
        ]);

        $this->assertNull($mensagem->fresh()->read_at);

        $this->actingAs($admin)->get(route('admin.conversations.show', $conversation))->assertOk();

        $this->assertNotNull($mensagem->fresh()->read_at);
    }

    public function test_resposta_em_conversa_encerrada_e_bloqueada_e_pode_ser_reaberta(): void
    {
        $admin = $this->admin();
        $client = Client::create(['name' => 'Cliente A', 'email' => 'a@teste.com']);
        $conversation = Conversation::create([
            'subject' => 'Assunto', 'client_id' => $client->id, 'created_by' => $admin->id, 'closed_at' => now(),
        ]);

        $this->actingAs($admin);

        $this->put(route('admin.conversations.update', $conversation), ['body' => 'Tentativa'])
            ->assertSessionHasErrors('body');
        $this->assertEquals(0, $conversation->messages()->count());

        $this->post(route('admin.conversations.reopen', $conversation))->assertRedirect();
        $this->assertNull($conversation->fresh()->closed_at);

        $this->put(route('admin.conversations.update', $conversation), ['body' => 'Agora vai'])
            ->assertRedirect();
        $this->assertEquals(1, $conversation->messages()->count());
    }

    public function test_portal_do_cliente_mostra_conversa_e_responde(): void
    {
        $client = Client::create(['name' => 'Cliente A', 'email' => 'a@teste.com']);
        $clientUser = User::factory()->create(['role' => 'cliente', 'client_id' => $client->id, 'active' => true]);
        $admin = $this->admin();

        $conversation = Conversation::create([
            'subject' => 'Bem-vindo', 'client_id' => $client->id, 'created_by' => $admin->id,
        ]);
        $staffMessage = ClientMessage::create([
            'conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'Bem-vindo ao portal!',
        ]);

        $this->actingAs($clientUser);

        $this->get(route('cliente.mensagens'))
            ->assertOk()
            ->assertSee('Bem-vindo')
            ->assertSee('Bem-vindo ao portal!');

        // Abrir marca a mensagem da ATITEC como lida.
        $this->assertNotNull($staffMessage->fresh()->read_at);

        $this->post(route('cliente.mensagens.store'), [
            'conversation_id' => $conversation->id,
            'body' => 'Obrigado!',
        ])->assertRedirect(route('cliente.mensagens'));

        $this->assertEquals(2, $conversation->messages()->count());
    }

    public function test_navbar_mostra_mensagens_nao_lidas_do_cliente(): void
    {
        $admin = $this->admin();
        $client = Client::create(['name' => 'Cliente A', 'email' => 'a@teste.com']);
        $clientUser = User::factory()->create(['role' => 'cliente', 'client_id' => $client->id, 'active' => true]);

        $conversation = Conversation::create([
            'subject' => 'Urgente', 'client_id' => $client->id, 'created_by' => $clientUser->id,
        ]);
        ClientMessage::create([
            'conversation_id' => $conversation->id, 'user_id' => $clientUser->id, 'body' => 'Pode me ajudar?',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pode me ajudar?');
    }
}
