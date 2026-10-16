<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\DeliverableTask;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function index(): View
    {
        return view('admin.invoices.index', [
            'invoices' => Invoice::with(['client', 'payments', 'project'])->latest()->paginate(15),
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
        $data = $this->validated($request);
        $pdf = $data['pdf'] ?? null;
        unset($data['pdf'], $data['task_ids']);

        $invoice = DB::transaction(function () use ($request, $data) {
            $invoice = Invoice::create($data);

            if ($invoice->status === 'pago') {
                $paidAt = $invoice->paid_at ?? now();
                $invoice->payments()->create([
                    'amount' => $invoice->amount,
                    'paid_at' => $paidAt,
                    'method' => 'manual',
                    'notes' => 'Pagamento inicial registrado ao criar a fatura como paga.',
                ]);
                $invoice->update(['paid_at' => $paidAt]);
            }

            $this->syncBilledTasks($request, $invoice);

            return $invoice;
        });

        if ($pdf) {
            $invoice->update(['pdf_path' => $pdf->store('invoices/'.$invoice->id, 'local')]);
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Fatura criada.');
    }

    public function show(Invoice $invoice): View
    {
        return view('admin.invoices.show', [
            'invoice' => $invoice->load(['client', 'project', 'payments', 'billedTasks.deliverable']),
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
        $data = $this->validated($request);
        $pdf = $data['pdf'] ?? null;
        unset($data['pdf'], $data['task_ids']);

        if ((float) $data['amount'] < round($invoice->paidAmount() + $invoice->discountAmount(), 2)) {
            return back()->withInput()->withErrors([
                'amount' => 'O valor da fatura não pode ficar abaixo do total já recebido ou abatido (pagamentos + cortesia).',
            ]);
        }

        DB::transaction(function () use ($request, $invoice, $data) {
            if (($data['status'] ?? null) === 'pago' && $invoice->balance() > 0) {
                $remaining = $invoice->balance();
                $invoice->payments()->create([
                    'amount' => $remaining,
                    'paid_at' => $data['paid_at'] ?? now(),
                    'method' => 'manual',
                    'notes' => 'Pagamento registrado ao marcar a fatura como paga.',
                ]);
            }

            $invoice->fill($data);
            $this->syncBilledTasks($request, $invoice);
            $invoice->save();
        });

        $this->syncStatus($invoice);

        if ($pdf) {
            $oldPath = $invoice->pdf_path;
            $newPath = $pdf->store('invoices/'.$invoice->id, 'local');
            $invoice->update(['pdf_path' => $newPath]);
            if ($oldPath) {
                Storage::disk('local')->delete($oldPath);
            }
        }

        return redirect()->route('admin.invoices.index')->with('success', 'Fatura atualizada.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        DB::transaction(function () use ($invoice) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            // Desvincula as tarefas cobradas: voltam a ficar disponíveis para faturamento.
            DeliverableTask::withTrashed()
                ->where('invoice_id', $locked->id)
                ->update(['invoice_id' => null]);

            $locked->payments()->delete();

            if ($locked->pdf_path) {
                Storage::disk('local')->delete($locked->pdf_path);
            }

            $locked->delete();
        });

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Fatura excluída. As tarefas vinculadas voltaram a ficar disponíveis para faturamento.');
    }

    public function paymentStore(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'cancelado') {
            throw ValidationException::withMessages(['amount' => 'Não é possível registrar pagamento em uma fatura cancelada.']);
        }

        DB::transaction(function () use ($request, $invoice) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $balance = $locked->balance();

            if ($balance <= 0) {
                throw ValidationException::withMessages(['amount' => 'Esta fatura já está totalmente paga ou abatida por cortesia.']);
            }

            $data = $request->validate($this->paymentRules($balance));

            $locked->payments()->create([
                ...$data,
                'paid_at' => $data['paid_at'] ?? now(),
            ]);

            $this->syncStatus($locked);
        });

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Pagamento registrado. Saldo restante: R$ '.number_format($invoice->fresh()->balance(), 2, ',', '.').'.');
    }

    public function paymentUpdate(Request $request, Invoice $invoice, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice_id === $invoice->id, 404);

        if ($invoice->status === 'cancelado') {
            throw ValidationException::withMessages(['amount' => 'Não é possível alterar pagamentos de uma fatura cancelada.']);
        }

        DB::transaction(function () use ($request, $invoice, $payment) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $lockedPayment = $locked->payments()->findOrFail($payment->id);
            $maxAmount = round($locked->balance() + (float) $lockedPayment->amount, 2);

            if ($maxAmount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Não há saldo disponível para ajustar este pagamento.']);
            }

            $data = $request->validate($this->paymentRules($maxAmount));
            $lockedPayment->update([
                ...$data,
                'paid_at' => $data['paid_at'] ?? $lockedPayment->paid_at ?? now(),
            ]);

            $this->syncStatus($locked);
        });

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Pagamento atualizado. Saldo restante: R$ '.number_format($invoice->fresh()->balance(), 2, ',', '.').'.');
    }

    public function paymentDestroy(Invoice $invoice, Payment $payment): RedirectResponse
    {
        abort_unless($payment->invoice_id === $invoice->id, 404);

        if ($invoice->status === 'cancelado') {
            throw ValidationException::withMessages(['amount' => 'Não é possível alterar pagamentos de uma fatura cancelada.']);
        }

        DB::transaction(function () use ($invoice, $payment) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $locked->payments()->findOrFail($payment->id)->delete();
            $this->syncStatus($locked);
        });

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Pagamento removido. Saldo restante: R$ '.number_format($invoice->fresh()->balance(), 2, ',', '.').'.');
    }

    /** Aplica desconto/cortesia (diferença assumida pela ATITEC) sobre a fatura. */
    public function discountStore(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'cancelado') {
            throw ValidationException::withMessages(['discount_amount' => 'Não é possível aplicar cortesia em uma fatura cancelada.']);
        }

        $maxDiscount = round((float) $invoice->amount - $invoice->paidAmount(), 2);

        $data = $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:0', 'lte:'.$maxDiscount],
            'discount_reason' => ['nullable', 'string', 'max:255'],
        ], [
            'discount_amount.lte' => 'O desconto não pode passar do que falta receber (valor da fatura menos os pagamentos já registrados).',
        ]);

        $invoice->update($data);
        $this->syncStatus($invoice);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Cortesia/desconto registrado. Saldo restante: R$ '.number_format($invoice->fresh()->balance(), 2, ',', '.').'.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function paymentRules(float $maxAmount): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'lte:'.$maxAmount],
            'paid_at' => ['nullable', 'date'],
            'method' => ['required', 'in:pix,boleto,transferencia,dinheiro,cartao,manual'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Sincroniza as tarefas vinculadas à fatura quando o formulário envia a
     * seleção (marcador tasks_synced). Tarefas desmarcadas são desvinculadas e
     * voltam a ficar disponíveis para faturamento.
     */
    private function syncBilledTasks(Request $request, Invoice $invoice): void
    {
        if (! $request->boolean('tasks_synced')) {
            return;
        }

        $submitted = collect($request->input('task_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($submitted->isEmpty() && ! $invoice->project_id) {
            return;
        }

        if (! $invoice->project_id) {
            throw ValidationException::withMessages([
                'task_ids' => 'Escolha um projeto para vincular tarefas à fatura.',
            ]);
        }

        $currentlyLinked = DeliverableTask::withTrashed()
            ->where('invoice_id', $invoice->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        $newIds = $submitted->diff($currentlyLinked)->values();

        $eligible = DeliverableTask::query()
            ->where('project_id', $invoice->project_id)
            ->whereIn('id', $newIds)
            ->where('status', 'concluido')
            ->whereNull('invoice_id')
            ->whereNotNull('hours_actual')
            ->where('hours_actual', '>', 0)
            ->get()
            ->filter(fn (DeliverableTask $task) => (float) $task->cost_actual > 0)
            ->pluck('id')
            ->map(fn ($id) => (int) $id);

        if ($eligible->count() !== $newIds->count()) {
            throw ValidationException::withMessages([
                'task_ids' => 'Uma ou mais tarefas selecionadas não estão disponíveis para faturamento (já faturadas ou sem custo). Atualize a página e selecione novamente.',
            ]);
        }

        DeliverableTask::withTrashed()
            ->where('invoice_id', $invoice->id)
            ->whereNotIn('id', $submitted->all())
            ->update(['invoice_id' => null]);

        if ($eligible->isNotEmpty()) {
            DeliverableTask::whereIn('id', $eligible->all())->update(['invoice_id' => $invoice->id]);
        }
    }

    /** Recalcula o status da fatura considerando pagamentos + cortesia. */
    private function syncStatus(Invoice $invoice): void
    {
        if ($invoice->status === 'cancelado') {
            return;
        }

        if ($invoice->isSettled()) {
            $invoice->update([
                'status' => 'pago',
                'paid_at' => $invoice->payments()->max('paid_at') ?? $invoice->paid_at ?? now(),
            ]);

            return;
        }

        $invoice->update([
            'status' => $invoice->due_date->lt(today()) ? 'vencido' : 'pendente',
            'paid_at' => null,
        ]);
    }

    public function downloadPdf(Request $request, Invoice $invoice): StreamedResponse
    {
        $user = $request->user();
        $isStaff = in_array($user->role, ['admin', 'gerente', 'financeiro'], true)
            && $user->can('invoice.view');
        $ownsInvoice = $user->client_id !== null && $user->client_id === $invoice->client_id;

        abort_unless($isStaff || $ownsInvoice, 404);
        abort_unless($invoice->pdf_path && Storage::disk('local')->exists($invoice->pdf_path), 404);

        return Storage::disk('local')->download($invoice->pdf_path, 'boleto-'.$invoice->id.'.pdf');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
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
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'task_ids' => ['nullable', 'array'],
            'task_ids.*' => ['integer', 'distinct'],
        ]);

        if (! empty($data['project_id'])) {
            $projectClientId = Project::whereKey($data['project_id'])->value('client_id');

            if ($projectClientId && (int) $projectClientId !== (int) $data['client_id']) {
                throw ValidationException::withMessages([
                    'project_id' => 'O cliente da fatura precisa ser o mesmo cliente vinculado ao projeto.',
                ]);
            }
        }

        return $data;
    }
}
