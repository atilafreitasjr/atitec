<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'projetosAtivos' => Project::whereIn('status', ['prospeccao', 'em_desenvolvimento', 'homologacao'])->count(),
            'ticketsAbertos' => Conversation::whereNull('closed_at')->count(),
            'faturasPendentes' => Invoice::where('status', 'pendente')->count(),
            'faturasVencidas' => Invoice::where('status', 'pendente')->where('due_date', '<', today())->count(),
            'mrr' => Invoice::where('status', 'pendente')->where('type', 'mensalidade')->sum('amount'),
            'leadsNovos' => Lead::where('status', 'novo')->count(),
            'ultimosProjetos' => Project::latest()->take(5)->get(),
            'ultimasFaturas' => Invoice::with('client')->latest()->take(5)->get(),
            'ultimosLeads' => Lead::latest()->take(5)->get(),
            'totalClientes' => Client::where('active', true)->count(),
        ]);
    }
}
