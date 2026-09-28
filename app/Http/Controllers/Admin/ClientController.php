<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        return view('admin.clients.index', [
            'clients' => Client::withCount(['projects', 'invoices'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.clients.form', [
            'client' => new Client,
            'users' => User::where('role', 'cliente')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Client::create($this->validated($request));

        return redirect()->route('admin.clients.index')->with('success', 'Cliente criado.');
    }

    public function show(Client $client): View
    {
        return view('admin.clients.show', [
            'client' => $client->load(['projects', 'invoices', 'conversations']),
        ]);
    }

    public function edit(Client $client): View
    {
        return view('admin.clients.form', [
            'client' => $client,
            'users' => User::where('role', 'cliente')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validated($request, $client->id));

        return redirect()->route('admin.clients.index')->with('success', 'Cliente atualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Cliente excluído.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160', 'unique:clients,email,'.$ignoreId],
            'phone' => ['nullable', 'string', 'max:40'],
            'document' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
        ]);
    }
}
