<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\ClientMessage;
use App\Models\Conversation;
use App\Models\Invoice;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function dashboard(): View
    {
        $client = auth()->user()->client;
        $projects = $client?->projects()->orderByDesc('updated_at')->get() ?? collect();
        $invoices = $client?->invoices()->with('payments')->orderBy('due_date')->get() ?? collect();
        $proposals = $client?->leads()->where('proposal_status', 'enviada')->latest()->take(3)->get() ?? collect();
        $openBalance = $invoices->sum(fn ($invoice) => $invoice->balance());

        return view('cliente.dashboard', [
            'client' => $client,
            'projects' => $projects,
            'invoices' => $invoices,
            'conversations' => $client?->conversations()->with('messages')->latest()->take(5)->get() ?? collect(),
            'pendingProposals' => $client?->leads()->where('proposal_status', 'enviada')->count() ?? 0,
            'proposals' => $proposals,
            'openBalance' => $openBalance,
        ]);
    }

    public function projetos(): View
    {
        $client = auth()->user()->client;
        abort_unless($client, 404);

        return view('cliente.projetos', [
            'projects' => $client->projects()->withCount('deliverables')->orderByDesc('updated_at')->paginate(10),
        ]);
    }

    public function projeto(string $id): View
    {
        $client = auth()->user()->client;
        abort_unless($client, 404);

        $project = $client->projects()
            ->with(['deliverables.tasks.invoice', 'invoices.payments'])
            ->with(['documents' => fn ($query) => $query->where('visible_to_client', true)])
            ->findOrFail($id);

        return view('cliente.projeto', ['project' => $project]);
    }

    public function kanban(string $id): View
    {
        $client = auth()->user()->client;
        abort_unless($client, 404);
        $project = $client->projects()->withCount('deliverables')->findOrFail($id);

        return view('cliente.kanban', ['project' => $project]);
    }

    public function financeiro(): View
    {
        $client = auth()->user()->client;
        abort_unless($client, 404);

        return view('cliente.financeiro', [
            'invoices' => $client->invoices()->with('payments')->orderBy('due_date')->paginate(15),
        ]);
    }

    public function fatura(Invoice $invoice): View
    {
        $client = auth()->user()->client;
        abort_unless($client, 404);

        $invoice = $client->invoices()->with(['payments', 'project'])->findOrFail($invoice->id);

        return view('cliente.fatura', compact('invoice'));
    }

    public function orcamentos(): View
    {
        $client = auth()->user()->client;
        abort_unless($client, 404);

        return view('cliente.orcamentos', [
            'leads' => $client->leads()->with('convertedProject')->latest()->paginate(10),
        ]);
    }

    public function responderOrcamento(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate(['resposta' => ['required', 'in:aceita,recusada']]);
        $client = auth()->user()->client;
        abort_unless($client, 404);

        $lead = $client->leads()->findOrFail($lead->id);
        if (! $lead->canRespondToProposal()) {
            return back()->withErrors(['proposal' => 'Esta proposta não está mais disponível para resposta.']);
        }

        $lead->update([
            'proposal_status' => $data['resposta'],
            'proposal_responded_at' => now(),
            'status' => $data['resposta'] === 'aceita' ? 'fechado' : 'perdido',
        ]);

        return redirect()->route('cliente.orcamentos')
            ->with('success', $data['resposta'] === 'aceita'
                ? 'Proposta aceita. A ATITEC entrará em contato para iniciar o projeto.'
                : 'Proposta recusada. Obrigado pelo retorno.');
    }

    public function mensagens(Request $request): View
    {
        $client = auth()->user()->client;
        abort_unless($client, 404);
        $conversations = $client->conversations()->with('messages.user')->latest()->get();
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
