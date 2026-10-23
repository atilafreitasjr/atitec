<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AtendimentoIaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        config([
            'atendimento_ia.enabled' => true,
            'services.deepseek.api_key' => 'test-key',
        ]);
    }

    private function fakeReply(string $text): void
    {
        Http::fake([
            'api.deepseek.com/*' => Http::response([
                'choices' => [['message' => ['content' => $text]]],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20],
            ]),
        ]);
    }

    public function test_widget_de_atendimento_aparece_no_site(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-atendimento-ia', false)
            ->assertSee('Atendente ATITEC')
            ->assertSee('atendente-ia-animado.svg')
            ->assertSee('atendente-ia-estatico.svg');
    }

    public function test_mensagem_sem_chave_da_api_retorna_erro_amigavel(): void
    {
        config(['services.deepseek.api_key' => null]);

        $this->postJson(route('atendimento-ia.mensagem'), ['message' => 'Olá'])
            ->assertStatus(503)
            ->assertJsonFragment(['message' => 'O atendimento por IA ainda não está configurado. Fale com a ATITEC pelo WhatsApp '.config('company.phone').'.']);

        $this->assertDatabaseCount('ai_conversations', 0);
    }

    public function test_ia_responde_e_salva_historico_da_conversa(): void
    {
        $this->fakeReply('A ATITEC desenvolve **sistemas sob medida** em Laravel.');

        $response = $this->postJson(route('atendimento-ia.mensagem'), [
            'message' => 'O que a ATITEC faz?',
        ]);

        $response->assertOk()
            ->assertJson([
                'escalated' => false,
                'needs_contact' => false,
                'status' => 'bot',
            ])
            ->assertJsonStructure(['token', 'reply_text', 'reply_html', 'protocol']);

        $conversation = AiConversation::where('token', $response->json('token'))->firstOrFail();

        $this->assertSame('bot', $conversation->status);
        $this->assertSame(2, $conversation->messages()->count());
        $this->assertDatabaseHas('ai_messages', [
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'A ATITEC desenvolve **sistemas sob medida** em Laravel.',
        ]);

        Http::assertSent(fn (ClientRequest $request) => str_contains($request['messages'][0]['content'], 'Você é o Atendente Virtual da ATITEC')
            && str_contains($request['messages'][0]['content'], 'R$ 50,00')
            && $request['messages'][1]['content'] === 'O que a ATITEC faz?');
    }

    public function test_historico_da_conversa_e_enviado_na_ordem_correta(): void
    {
        Http::fake([
            'api.deepseek.com/*' => Http::sequence()
                ->push(['choices' => [['message' => ['content' => 'Primeira resposta.']]]])
                ->push(['choices' => [['message' => ['content' => 'Segunda resposta.']]]]),
        ]);

        $first = $this->postJson(route('atendimento-ia.mensagem'), [
            'message' => 'Primeira pergunta',
        ])->assertOk();

        $this->postJson(route('atendimento-ia.mensagem'), [
            'token' => $first->json('token'),
            'message' => 'Segunda pergunta',
        ])->assertOk();

        Http::assertSent(function (ClientRequest $request) {
            $messages = $request['messages'];

            return count($messages) === 4
                && $messages[0]['role'] === 'system'
                && $messages[1]['content'] === 'Primeira pergunta'
                && $messages[2]['content'] === 'Primeira resposta.'
                && $messages[3]['content'] === 'Segunda pergunta';
        });
    }

    public function test_ia_encaminha_duvida_que_exige_decisao_humana(): void
    {
        $this->fakeReply("Essa condição precisa ser confirmada pela equipe ATITEC. Vou registrar sua dúvida.\n\n[[ENCAMINHAR]]{\"resumo\":\"Visitante pediu desconto para fechar o projeto.\",\"categoria\":\"comercial\",\"urgencia\":\"alta\"}[[/ENCAMINHAR]]");

        $response = $this->postJson(route('atendimento-ia.mensagem'), [
            'message' => 'Vocês me dão desconto se eu fechar esta semana?',
        ]);

        $response->assertOk()
            ->assertJson([
                'escalated' => true,
                'needs_contact' => true,
                'status' => 'encaminhado',
            ]);

        $this->assertStringNotContainsString('ENCAMINHAR', $response->json('reply_text'));

        $conversation = AiConversation::where('token', $response->json('token'))->firstOrFail();

        $this->assertSame('encaminhado', $conversation->status);
        $this->assertSame('comercial', $conversation->category);
        $this->assertSame('alta', $conversation->urgency);
        $this->assertSame('Visitante pediu desconto para fechar o projeto.', $conversation->summary);
        $this->assertNotNull($conversation->escalated_at);
    }

    public function test_visitante_informa_contato_do_encaminhamento(): void
    {
        $conversation = AiConversation::create([
            'token' => str_repeat('a', 48),
            'status' => 'encaminhado',
            'summary' => 'Dúvida sobre contrato.',
            'category' => 'comercial',
            'urgency' => 'normal',
            'escalated_at' => now(),
            'last_message_at' => now(),
        ]);
        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Vou encaminhar para a equipe.',
        ]);

        $response = $this->postJson(route('atendimento-ia.contato'), [
            'token' => $conversation->token,
            'name' => 'Maria Souza',
            'email' => 'Maria@Exemplo.com',
            'phone' => '(41) 99999-0000',
        ]);

        $response->assertOk()->assertJson([
            'token' => $conversation->token,
            'protocol' => $conversation->protocol,
        ]);

        $conversation->refresh();

        $this->assertSame('Maria Souza', $conversation->visitor_name);
        $this->assertSame('maria@exemplo.com', $conversation->visitor_email);
        $this->assertSame('(41) 99999-0000', $conversation->visitor_phone);

        $this->getJson(route('atendimento-ia.conversa', $conversation->token))
            ->assertOk()
            ->assertJson([
                'status' => 'encaminhado',
                'needs_contact' => false,
            ]);
    }

    public function test_admin_com_permissao_atende_e_responde_a_duvida(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $conversation = AiConversation::create([
            'token' => str_repeat('b', 48),
            'visitor_name' => 'João Visitante',
            'visitor_email' => 'joao@exemplo.com',
            'status' => 'encaminhado',
            'summary' => 'Precisa de uma condição especial de pagamento.',
            'category' => 'comercial',
            'urgency' => 'alta',
            'escalated_at' => now(),
            'last_message_at' => now(),
        ]);
        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Vocês parcelam em quantas vezes?',
        ]);
        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Vou encaminhar sua dúvida para a equipe.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.atendimento-ia.index'))
            ->assertOk()
            ->assertSee('João Visitante')
            ->assertSee('Precisa de uma condição especial de pagamento.');

        $this->actingAs($admin)
            ->get(route('admin.atendimento-ia.show', $conversation))
            ->assertOk()
            ->assertSee('Vocês parcelam em quantas vezes?')
            ->assertSee('João Visitante');

        $this->actingAs($admin)
            ->post(route('admin.atendimento-ia.reply', $conversation), [
                'body' => 'Podemos parcelar em até 3 vezes sem juros.',
            ])
            ->assertRedirect(route('admin.atendimento-ia.show', $conversation));

        $conversation->refresh();

        $this->assertSame('respondido', $conversation->status);
        $this->assertSame($admin->id, $conversation->handled_by);
        $this->assertNotNull($conversation->handled_at);
        $this->assertDatabaseHas('ai_messages', [
            'ai_conversation_id' => $conversation->id,
            'role' => 'human',
            'content' => 'Podemos parcelar em até 3 vezes sem juros.',
            'user_id' => $admin->id,
        ]);

        $this->getJson(route('atendimento-ia.conversa', $conversation->token))
            ->assertOk()
            ->assertJsonPath('status', 'respondido')
            ->assertJsonFragment(['text' => 'Podemos parcelar em até 3 vezes sem juros.']);
    }

    public function test_usuario_sem_permissao_nao_acessa_atendimentos_da_ia(): void
    {
        $financeiro = User::factory()->create(['role' => 'financeiro', 'active' => true]);

        $this->actingAs($financeiro)
            ->get(route('admin.atendimento-ia.index'))
            ->assertForbidden();

        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);
        $this->actingAs($gerente)
            ->get(route('admin.atendimento-ia.index'))
            ->assertOk();
    }
}
