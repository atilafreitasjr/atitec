<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(): View
    {
        return view('admin.leads.index', [
            'leads' => Lead::latest()->paginate(15),
        ]);
    }

    public function show(Lead $lead): View
    {
        return view('admin.leads.show', ['lead' => $lead]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(Lead::STATUSES))],
        ]);

        $lead->update($data);

        return redirect()->route('admin.leads.index')->with('success', 'Lead atualizado.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead excluído.');
    }
}
