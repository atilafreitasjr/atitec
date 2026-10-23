<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AtendimentoIaService;
use App\Services\DeepSeekService;
use App\Support\Markdown;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AtendimentoIaController extends Controller
{
    public function mensagem(Request $request): JsonResponse
    {
        if (! config('atendimento_ia.enabled')) {
            return response()->json(['message' => 'O atendimento virtual está indisponível no momento.'], 503);
        }

        if (! DeepSeekService::configured()) {
            return response()->json([
                'message' => 'O atendimento por IA ainda não está configurado. Fale com a ATITEC pelo WhatsApp '.config('company.phone').'.',
            ], 503);
        }

        $data = $request->validate([
            'token' => ['nullable', 'string', 'max:64'],
            'message' => ['required', 'string', 'max:'.(int) config('atendimento_ia.max_message_length', 2000)],
        ]);

        $conversation = $this->resolveConversation($request, $data['token'] ?? null);

        if ($conversation->status === 'encerrado') {
            $conversation->update(['status' => 'bot', 'handled_at' => null, 'handled_by' => null]);
        } elseif ($conversation->status === 'respondido') {
            // O visitante voltou a escrever depois de uma resposta humana.
            $conversation->update(['status' => 'em_atendimento']);
        }

        $userMessage = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => trim($data['message']),
        ]);

        $conversation->update(['last_message_at' => $userMessage->created_at]);

        try {
            $answer = AtendimentoIaService::fromConfig()->answer($conversation);
        } catch (Throwable $exception) {
            Log::warning('Falha no atendimento por IA', [
                'conversation_id' => $conversation->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'token' => $conversation->token,
                'message' => 'Não consegui falar com a IA agora. Tente novamente em instantes ou fale com a ATITEC pelo WhatsApp '.config('company.phone').'.',
            ], 503);
        }

        $assistantMessage = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $answer['text'],
        ]);

        $escalation = $answer['escalation'];

        if ($escalation !== null) {
            $conversation->update([
                'status' => 'encaminhado',
                'summary' => $escalation['summary'],
                'category' => $escalation['category'],
                'urgency' => $escalation['urgency'],
                'escalated_at' => $conversation->escalated_at ?? now(),
                'last_message_at' => now(),
            ]);
        }

        $conversation->refresh();

        return response()->json([
            'token' => $conversation->token,
            'reply_text' => $assistantMessage->content,
            'reply_html' => Markdown::render($assistantMessage->content),
            'escalated' => $escalation !== null,
            'needs_contact' => $escalation !== null && ! $conversation->hasContact(),
            'protocol' => $conversation->protocol,
            'status' => $conversation->status,
            'status_label' => AiConversation::STATUSES[$conversation->status] ?? $conversation->status,
        ]);
    }

    public function conversa(string $token): JsonResponse
    {
        $conversation = AiConversation::where('token', $token)->firstOrFail();

        return response()->json($this->conversationPayload($conversation));
    }

    public function contato(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'exists:ai_conversations,token'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $conversation = AiConversation::where('token', $data['token'])->firstOrFail();

        $conversation->update([
            'visitor_name' => $data['name'],
            'visitor_email' => mb_strtolower($data['email']),
            'visitor_phone' => $data['phone'] ?? null,
            'status' => $conversation->status === 'bot' ? 'encaminhado' : $conversation->status,
            'escalated_at' => $conversation->escalated_at ?? now(),
            'last_message_at' => now(),
        ]);

        return response()->json([
            'token' => $conversation->token,
            'protocol' => $conversation->protocol,
            'message' => 'Dúvida registrada! A equipe da ATITEC foi avisada e retornará pelo contato informado.',
        ]);
    }

    private function resolveConversation(Request $request, ?string $token): AiConversation
    {
        if (filled($token)) {
            $existing = AiConversation::where('token', $token)->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        $user = $request->user();
        $client = $user?->client;

        return AiConversation::create([
            'token' => AiConversation::generateToken(),
            'visitor_name' => $client?->name ?? $user?->name,
            'visitor_email' => $client?->email ?? $user?->email,
            'visitor_phone' => $client?->phone,
            'status' => 'bot',
            'ip_address' => $request->ip(),
            'last_message_at' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function conversationPayload(AiConversation $conversation): array
    {
        return [
            'token' => $conversation->token,
            'protocol' => $conversation->protocol,
            'status' => $conversation->status,
            'status_label' => AiConversation::STATUSES[$conversation->status] ?? $conversation->status,
            'needs_contact' => $conversation->isEscalated() && ! $conversation->hasContact(),
            'messages' => $conversation->messages->map(fn (AiMessage $message) => [
                'id' => $message->id,
                'role' => $message->role,
                'author' => AiMessage::ROLES[$message->role] ?? 'Atendente',
                'html' => Markdown::render($message->content),
                'text' => $message->content,
                'created_at' => $message->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
