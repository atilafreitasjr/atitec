<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Rules\Cnpj;
use App\Rules\Cpf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        $data = $this->validated($request);
        $client = DB::transaction(function () use ($data) {
            $client = Client::create($data);
            $this->syncUserLink($client, $data['user_id'] ?? null);

            return $client;
        });

        return redirect()->route('admin.clients.show', $client)->with('success', 'Cliente criado.');
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
        $data = $this->validated($request, $client->id);
        DB::transaction(function () use ($client, $data) {
            $previousUserId = $client->user_id;
            $client->update($data);

            if ($previousUserId && (int) $previousUserId !== (int) ($data['user_id'] ?? 0)) {
                User::whereKey($previousUserId)->where('client_id', $client->id)->update(['client_id' => null]);
            }

            $this->syncUserLink($client, $data['user_id'] ?? null);
        });

        return redirect()->route('admin.clients.show', $client)->with('success', 'Cliente atualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->user_id || $client->projects()->exists() || $client->invoices()->exists() || $client->leads()->exists() || $client->conversations()->exists()) {
            return back()->withErrors([
                'client' => 'Este cliente possui acesso ou histórico vinculado. Desative o cadastro em vez de excluí-lo.',
            ]);
        }

        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Cliente excluído.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'cpf' => preg_replace('/\D+/', '', (string) $request->input('cpf')) ?: null,
            'cnpj' => preg_replace('/\D+/', '', (string) $request->input('cnpj')) ?: null,
            'zip_code' => preg_replace('/\D+/', '', (string) $request->input('zip_code')) ?: null,
            'company_zip_code' => preg_replace('/\D+/', '', (string) $request->input('company_zip_code')) ?: null,
        ]);

        $data = $request->validate([
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'cliente')],
            'name' => ['required', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160', 'unique:clients,email,'.$ignoreId],
            'phone' => ['nullable', 'string', 'max:40'],
            'cpf' => ['nullable', 'digits:11', new Cpf, Rule::unique('clients', 'cpf')->ignore($ignoreId)],
            'cnpj' => ['nullable', 'digits:14', new Cnpj, Rule::unique('clients', 'cnpj')->ignore($ignoreId)],
            'address' => ['nullable', 'string', 'max:255'],
            'address_number' => ['nullable', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:120'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip_code' => ['nullable', 'digits:8'],
            'company_same_address' => ['sometimes', 'boolean'],
            'company_zip_code' => ['nullable', 'digits:8'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_address_number' => ['nullable', 'string', 'max:20'],
            'company_address_complement' => ['nullable', 'string', 'max:120'],
            'company_neighborhood' => ['nullable', 'string', 'max:120'],
            'company_city' => ['nullable', 'string', 'max:120'],
            'company_state' => ['nullable', 'string', 'size:2'],
            'notes' => ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $sameCompanyAddress = filled($data['company'] ?? null) && $request->boolean('company_same_address');
        $data['company_same_address'] = $sameCompanyAddress;

        if (! filled($data['company'] ?? null) || $sameCompanyAddress) {
            foreach ([
                'company_zip_code', 'company_address', 'company_address_number',
                'company_address_complement', 'company_neighborhood', 'company_city', 'company_state',
            ] as $field) {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function syncUserLink(Client $client, ?int $userId): void
    {
        if (! $userId) {
            return;
        }

        Client::where('user_id', $userId)->whereKeyNot($client->id)->update(['user_id' => null]);
        User::whereKey($userId)->update(['client_id' => $client->id]);
    }
}
