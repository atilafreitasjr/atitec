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
            'client_id' => ['nullable', 'exists:clients,id'],
            'is_broadcast' => ['sometimes', 'boolean'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = Conversation::create([
            'subject' => $data['subject'],
            'client_id' => $data['client_id'] ?? null,
            'created_by' => auth()->id(),
            'is_broadcast' => $data['is_broadcast'] ?? false,
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
        return view('admin.conversations.show', [
            'conversation' => $conversation->load(['client', 'messages.user']),
        ]);
    }

    public function update(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        ClientMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return redirect()->route('admin.conversations.show', $conversation)->with('success', 'Resposta enviada.');
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        $conversation->update(['closed_at' => now()]);

        return redirect()->route('admin.conversations.index')->with('success', 'Conversa encerrada.');
    }
}
