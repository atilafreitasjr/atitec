<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        return view('admin.invoices.index', [
            'invoices' => Invoice::with('client')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.invoices.form', [
            'invoice' => new Invoice,
            'clients' => Client::orderBy('name')->get(),
            'projects' => Project::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Invoice::create($this->validated($request));

        return redirect()->route('admin.invoices.index')->with('success', 'Fatura criada.');
    }

    public function show(Invoice $invoice): View
    {
        return view('admin.invoices.show', [
            'invoice' => $invoice->load(['client', 'project', 'payments']),
        ]);
    }

    public function edit(Invoice $invoice): View
    {
        return view('admin.invoices.form', [
            'invoice' => $invoice,
            'clients' => Client::orderBy('name')->get(),
            'projects' => Project::orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $invoice->update($this->validated($request));

        return redirect()->route('admin.invoices.index')->with('success', 'Fatura atualizada.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'Fatura excluída.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'paid_at' => ['nullable', 'date'],
            'status' => ['required', 'in:pendente,pago,vencido,cancelado'],
            'type' => ['required', 'in:mensalidade,servico,projeto'],
            'gateway' => ['nullable', 'string', 'max:80'],
            'external_id' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
