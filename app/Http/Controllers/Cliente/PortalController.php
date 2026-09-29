<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\ClientMessage;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function dashboard(): View
    {
        $client = auth()->user()->client;

        return view('cliente.dashboard', [
            'client' => $client,
            'projects' => $client?->projects()->orderByDesc('updated_at')->get() ?? collect(),
            'invoices' => $client?->invoices()->orderBy('due_date')->get() ?? collect(),
            'conversations' => $client?->conversations()->with('messages')->latest()->take(5)->get() ?? collect(),
        ]);
    }

    public function projetos(): View
    {
        $client = auth()->user()->client;

        return view('cliente.projetos', [
            'projects' => $client?->projects()->with('tasks')->orderByDesc('updated_at')->paginate(10) ?? collect(),
        ]);
    }

    public function projeto(string $id): View
    {
        $client = auth()->user()->client;
        $project = $client?->projects()->with('tasks')->findOrFail($id);

        return view('cliente.projeto', ['project' => $project]);
    }

    public function kanban(string $id): View
    {
        $client = auth()->user()->client;
        $project = $client?->projects()->withCount('deliverables')->findOrFail($id);

        return view('cliente.kanban', ['project' => $project]);
    }

    public function financeiro(): View
    {
        $client = auth()->user()->client;

        return view('cliente.financeiro', [
            'invoices' => $client?->invoices()->with('payments')->orderBy('due_date')->paginate(15) ?? collect(),
        ]);
    }

    public function mensagens(Request $request): View
    {
        $client = auth()->user()->client;
        $conversations = $client?->conversations()->with('messages.user')->latest()->get() ?? collect();
        $broadcasts = Conversation::where('is_broadcast', true)->with('messages.user')->latest()->get();

        $selected = null;
        if ($conversations->isNotEmpty()) {
            $id = $request->integer('conversation');
            $selected = $id ? $conversations->firstWhere('id', $id) : $conversations->first();

            // Abrir a conversa marca como lidas as mensagens enviadas pela ATITEC.
            if ($selected) {
                $selected->messages()
                    ->where('user_id', '!=', auth()->id())
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
                $selected->load('messages.user');
            }
        }

        return view('cliente.mensagens', [
            'conversations' => $conversations,
            'broadcasts' => $broadcasts,
            'selected' => $selected,
        ]);
    }

    public function mensagemStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'conversation_id' => ['nullable', 'exists:conversations,id'],
            'subject' => ['required_without:conversation_id', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $client = auth()->user()->client;
        abort_unless($client, 403);

        $conversation = isset($data['conversation_id'])
            ? $client->conversations()->findOrFail($data['conversation_id'])
            : Conversation::create([
                'subject' => $data['subject'],
                'client_id' => $client->id,
                'created_by' => auth()->id(),
            ]);

        ClientMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return redirect()->route('cliente.mensagens')->with('success', 'Mensagem enviada.');
    }
}
