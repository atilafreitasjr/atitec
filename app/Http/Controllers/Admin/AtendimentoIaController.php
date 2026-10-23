<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AtendimentoIaController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = AiConversation::query()
            ->where('status', '!=', 'bot')
            ->when($request->filled('status') && array_key_exists((string) $request->input('status'), AiConversation::STATUSES), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim((string) $request->input('q')).'%';

                $query->where(fn ($search) => $search
                    ->where('visitor_name', 'like', $term)
                    ->orWhere('visitor_email', 'like', $term)
                    ->orWhere('visitor_phone', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('token', 'like', $term));
            })
            ->withCount('messages')
            ->latest('last_message_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.atendimento-ia.index', [
            'conversations' => $conversations,
            'statuses' => AiConversation::STATUSES,
            'counts' => [
                'encaminhado' => AiConversation::where('status', 'encaminhado')->count(),
                'em_atendimento' => AiConversation::where('status', 'em_atendimento')->count(),
                'respondido' => AiConversation::where('status', 'respondido')->count(),
                'total' => AiConversation::where('status', '!=', 'bot')->count(),
            ],
        ]);
    }

    public function show(AiConversation $conversation): View
    {
        abort_if($conversation->status === 'bot', 404);

        return view('admin.atendimento-ia.show', [
            'conversation' => $conversation->load(['messages.user', 'handledBy']),
            'statuses' => AiConversation::STATUSES,
        ]);
    }

    public function reply(Request $request, AiConversation $conversation): RedirectResponse
    {
        abort_if($conversation->status === 'bot', 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'human',
            'content' => $data['body'],
            'user_id' => auth()->id(),
        ]);

        $conversation->update([
            'status' => 'respondido',
            'handled_by' => auth()->id(),
            'handled_at' => now(),
            'last_message_at' => now(),
        ]);

        return redirect()
            ->route('admin.atendimento-ia.show', $conversation)
            ->with('success', 'Resposta enviada. O visitante verá a mensagem no chat ao retornar.');
    }

    public function status(Request $request, AiConversation $conversation): RedirectResponse
    {
        abort_if($conversation->status === 'bot', 404);

        $data = $request->validate([
            'status' => ['required', 'in:encaminhado,em_atendimento,encerrado'],
        ]);

        $attributes = ['status' => $data['status']];

        if ($data['status'] === 'encaminhado') {
            $attributes['handled_by'] = null;
            $attributes['handled_at'] = null;
        } else {
            $attributes['handled_by'] = auth()->id();
            $attributes['handled_at'] = now();
        }

        $conversation->update($attributes);

        return redirect()
            ->route('admin.atendimento-ia.show', $conversation)
            ->with('success', 'Status atualizado para “'.AiConversation::STATUSES[$data['status']].'”.');
    }
}
