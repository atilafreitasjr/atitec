<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.leads.index', [
            'leads' => Lead::with('client')
                ->when($request->filled('q'), function ($query) use ($request) {
                    $term = '%'.trim((string) $request->input('q')).'%';
                    $query->where(fn ($search) => $search->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('company', 'like', $term)
                        ->orWhere('project_type', 'like', $term));
                })
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
                ->when($request->input('vinculo') === 'pendente', fn ($query) => $query->whereNull('client_id'))
                ->latest()->paginate(15)->withQueryString(),
            'statuses' => Lead::STATUSES,
        ]);
    }

    public function show(Lead $lead): View
    {
        return view('admin.leads.show', [
            'lead' => $lead->load(['client', 'convertedProject']),
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Lead::STATUSES))],
            'client_id' => ['nullable', 'exists:clients,id'],
        ]);

        $lead->update($data);

        return redirect()->route('admin.leads.show', $lead)->with('success', 'Lead atualizado.');
    }

    public function sendProposal(Request $request, Lead $lead): RedirectResponse
    {
        if ($lead->converted_project_id) {
            return back()->withErrors(['proposal' => 'Este orçamento já foi convertido em projeto.']);
        }

        $data = $request->validate([
            'proposal_title' => ['required', 'string', 'max:160'],
            'proposal_summary' => ['required', 'string', 'max:10000'],
            'proposal_amount' => ['required', 'numeric', 'min:0.01'],
            'proposal_valid_until' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        if (! $lead->client_id) {
            return back()->withInput()->withErrors(['client_id' => 'Associe o orçamento a um cliente antes de enviar a proposta.']);
        }

        $lead->update([
            ...$data,
            'proposal_status' => 'enviada',
            'proposal_sent_at' => now(),
            'proposal_responded_at' => null,
            'status' => 'proposta',
        ]);

        return back()->with('success', 'Proposta comercial disponibilizada no workspace do cliente.');
    }

    public function convertToProject(Lead $lead): RedirectResponse
    {
        if ($lead->converted_project_id) {
            return redirect()->route('admin.projects.show', $lead->converted_project_id)
                ->with('success', 'Este orçamento já está associado a um projeto.');
        }

        if ($lead->proposal_status !== 'aceita' || ! $lead->client_id) {
            return back()->withErrors(['proposal' => 'A proposta precisa estar aceita e vinculada a um cliente antes da conversão.']);
        }

        $project = DB::transaction(function () use ($lead) {
            $title = $lead->proposal_title ?: ($lead->project_type ?: 'Projeto de '.$lead->name);
            $baseSlug = Str::slug($title) ?: 'projeto-'.$lead->id;
            $slug = $baseSlug;
            $suffix = 2;

            while (Project::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $project = Project::create([
                'client_id' => $lead->client_id,
                'title' => $title,
                'slug' => $slug,
                'description' => $lead->proposal_summary,
                'problem' => $lead->details,
                'status' => 'prospeccao',
                'progress' => 0,
                'budget' => $lead->proposal_amount,
                'featured' => false,
                'sort_order' => (Project::max('sort_order') ?? 0) + 1,
            ]);

            $lead->update([
                'converted_project_id' => $project->id,
                'proposal_status' => 'convertida',
                'status' => 'fechado',
            ]);

            return $project;
        });

        return redirect()->route('admin.projects.show', $project)->with('success', 'Projeto criado e vinculado ao cliente.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        if ($lead->client_id || $lead->proposal_sent_at || $lead->converted_project_id) {
            return back()->withErrors(['lead' => 'Este pedido já está vinculado a um cliente ou possui proposta. Mantenha o histórico e atualize sua etapa.']);
        }

        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead excluído.');
    }
}
