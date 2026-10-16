<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverableTask;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectBillingController extends Controller
{
    /**
     * Tarefas faturáveis do projeto (JSON): concluídas, com custo e ainda não
     * faturadas. Ao editar uma fatura, inclui também as tarefas já vinculadas a ela.
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        $invoiceId = $request->integer('invoice');

        $tasks = DeliverableTask::query()
            ->with('deliverable:id,name')
            ->where('project_id', $project->id)
            ->where('status', 'concluido')
            ->whereNotNull('hours_actual')
            ->where('hours_actual', '>', 0)
            ->when(
                $invoiceId,
                fn ($query) => $query->where(fn ($sub) => $sub->whereNull('invoice_id')->orWhere('invoice_id', $invoiceId)),
                fn ($query) => $query->whereNull('invoice_id'),
            )
            ->orderBy('deliverable_id')
            ->orderBy('title')
            ->get()
            ->map(fn (DeliverableTask $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'deliverable' => $task->deliverable?->name,
                'hours' => (float) $task->hours_actual,
                'rate' => (float) $task->hourly_rate,
                'cost' => (float) $task->cost_actual,
                'linked' => $invoiceId > 0 && (int) $task->invoice_id === $invoiceId,
            ])
            ->filter(fn (array $task) => $task['linked'] || $task['cost'] > 0)
            ->values();

        return response()->json(['tasks' => $tasks]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['integer', 'distinct'],
        ], [
            'task_ids.required' => 'Selecione ao menos uma tarefa para cobrar.',
            'task_ids.min' => 'Selecione ao menos uma tarefa para cobrar.',
        ]);

        if (! $project->client_id) {
            throw ValidationException::withMessages([
                'project' => 'Associe o projeto a um cliente antes de gerar uma cobrança.',
            ]);
        }

        $invoice = DB::transaction(function () use ($project, $data) {
            $tasks = DeliverableTask::query()
                ->where('project_id', $project->id)
                ->whereIn('id', $data['task_ids'])
                ->where('status', 'concluido')
                ->whereNull('invoice_id')
                ->whereNotNull('hours_actual')
                ->where('hours_actual', '>', 0)
                ->lockForUpdate()
                ->get();

            $lines = $tasks->map(function (DeliverableTask $task) {
                $hours = (float) $task->hours_actual;
                $rate = (float) $task->hourly_rate;
                $cost = round($hours * $rate, 2);

                return [
                    'task' => $task,
                    'hours' => $hours,
                    'rate' => $rate,
                    'cost' => $cost,
                ];
            })->filter(fn (array $line) => $line['cost'] > 0)->values();

            if ($lines->count() !== count($data['task_ids'])) {
                throw ValidationException::withMessages([
                    'task_ids' => 'Uma ou mais tarefas selecionadas não estão disponíveis para faturamento (já faturadas ou sem custo). Atualize a página e selecione novamente.',
                ]);
            }

            $amount = round($lines->sum('cost'), 2);
            $description = "Cobrança pelas tarefas realizadas no projeto {$project->title}:\n";
            $description .= $lines->map(fn (array $line) => sprintf(
                '- %s: %s h × R$ %s = R$ %s',
                $line['task']->title,
                number_format($line['hours'], 2, ',', '.'),
                number_format($line['rate'], 2, ',', '.'),
                number_format($line['cost'], 2, ',', '.'),
            ))->implode("\n");

            $invoice = Invoice::create([
                'client_id' => $project->client_id,
                'project_id' => $project->id,
                'title' => 'Tarefas realizadas — '.$project->title,
                'description' => $description,
                'amount' => $amount,
                'due_date' => $data['due_date'],
                'status' => 'pendente',
                'type' => 'projeto',
                'notes' => 'Gerada a partir de tarefas concluídas e ainda não faturadas.',
            ]);

            DeliverableTask::whereIn('id', $lines->pluck('task')->map(fn (DeliverableTask $task) => $task->id))
                ->update(['invoice_id' => $invoice->id]);

            return $invoice;
        });

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Cobrança criada com as tarefas selecionadas. As tarefas cobradas saem da lista de faturamento.');
    }
}
