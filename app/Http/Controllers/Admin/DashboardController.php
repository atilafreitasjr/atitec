<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\DeliverableTask;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $openInvoices = Invoice::query()->whereIn('status', ['pendente', 'vencido'])->withSum('payments', 'amount')->get();
        $openBalance = $openInvoices->sum(fn (Invoice $invoice) => max(0, (float) $invoice->amount - (float) ($invoice->payments_sum_amount ?? 0)));

        return view('admin.dashboard', [
            'activeProjects' => Project::whereIn('status', ['prospeccao', 'em_desenvolvimento', 'homologacao'])->count(),
            'openConversations' => Conversation::whereNull('closed_at')->count(),
            'openInvoices' => $openInvoices->count(),
            'overdueInvoices' => Invoice::whereIn('status', ['pendente', 'vencido'])->where('due_date', '<', today())->count(),
            'openBalance' => $openBalance,
            'receivedThisMonth' => Payment::whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'tasksReadyToBill' => DeliverableTask::where('status', 'concluido')->whereNull('invoice_id')->whereNotNull('hours_actual')
                ->where('hours_actual', '>', 0)->where('hourly_rate', '>', 0)
                ->whereHas('project', fn ($query) => $query->whereNotNull('client_id'))
                ->sum(DB::raw('COALESCE(hours_actual, 0) * COALESCE(hourly_rate, 0)')),
            'unlinkedLeads' => Lead::whereNull('client_id')->whereNotIn('status', ['fechado', 'perdido'])->count(),
            'proposalsWaiting' => Lead::where('proposal_status', 'enviada')->count(),
            'latestProjects' => Project::with('client')->latest()->take(6)->get(),
            'latestInvoices' => Invoice::with(['client', 'payments'])->latest()->take(6)->get(),
            'latestLeads' => Lead::with('client')->latest()->take(6)->get(),
            'activeClients' => Client::where('active', true)->count(),
        ]);
    }
}
