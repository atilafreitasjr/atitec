<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientMessage;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(): View
    {
        return view('admin.conversations.index', [
            'conversations' => Conversation::with(['client', 'messages'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.conversations.form', [
            'conversation' => new Conversation,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'destino' => ['required', 'in:cliente,geral'],
            'client_id' => ['required_if:destino,cliente', 'nullable', 'exists:clients,id'],
            'body' => ['required', 'string', 'max:5000'],
        ], [
            'client_id.required_if' => 'Selecione o cliente da conversa.',
        ]);

        $isBroadcast = $data['destino'] === 'geral';

        $conversation = Conversation::create([
            'subject' => $data['subject'],
            // Comunicado geral não fica vinculado a um cliente específico.
            'client_id' => $isBroadcast ? null : $data['client_id'],
            'created_by' => auth()->id(),
            'is_broadcast' => $isBroadcast,
        ]);

        ClientMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return redirect()->route('admin.conversations.show', $conversation)->with('success', 'Conversa criada.');
    }

    public function show(Conversation $conversation): View
    {
        // Abrir a conversa marca como lidas as mensagens enviadas pelo cliente.
        $conversation->messages()
            ->where('user_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.conversations.show', [
            'conversation' => $conversation->load(['client', 'messages.user']),
        ]);
    }

    public function update(Request $request, Conversation $conversation): RedirectResponse
    {
        // Reabre a conversa ao responder, caso estivesse encerrada.
        if ($conversation->closed_at) {
            return redirect()->route('admin.conversations.show', $conversation)
                ->withErrors(['body' => 'Esta conversa está encerrada. Reabra antes de responder.']);
        }

        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        ClientMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return redirect()->route('admin.conversations.show', $conversation)->with('success', 'Resposta enviada.');
    }

    public function reopen(Conversation $conversation): RedirectResponse
    {
        $conversation->update(['closed_at' => null]);

        return redirect()->route('admin.conversations.show', $conversation)->with('success', 'Conversa reaberta.');
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        $conversation->update(['closed_at' => now()]);

        return redirect()->route('admin.conversations.index')->with('success', 'Conversa encerrada.');
    }
}
