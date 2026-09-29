<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['client', 'roles'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($sub) => $sub->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['role' => 'cliente', 'active' => true]),
            'roles' => Role::orderBy('name')->pluck('name'),
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'client_id' => $data['client_id'] ?? null,
            'active' => $request->boolean('active'),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', "Usuário {$user->name} criado.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::orderBy('name')->pluck('name'),
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        // Não permite rebaixar/desativar o último admin ativo do sistema.
        if ($this->wouldRemoveLastAdmin($user, $data['role'], $request->boolean('active'))) {
            return back()->withInput()->withErrors([
                'role' => 'Este é o último administrador ativo — promova outro usuário antes de alterar.',
            ]);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'client_id' => $data['client_id'] ?? null,
            'active' => $request->boolean('active'),
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        return redirect()->route('admin.users.index')->with('success', "Usuário {$user->name} atualizado.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->withErrors(['user' => 'Você não pode excluir o próprio usuário.']);
        }

        if ($this->wouldRemoveLastAdmin($user, 'cliente', false)) {
            return back()->withErrors(['user' => 'Não é possível excluir o último administrador ativo.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "Usuário {$name} excluído.");
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'client_id' => ['nullable', 'exists:clients,id'],
            'active' => ['nullable', 'boolean'],
        ]);

        return $data;
    }

    /** Detecta se mudar role/active ou excluir deixaria o sistema sem admin ativo. */
    private function wouldRemoveLastAdmin(User $user, string $newRole, bool $newActive): bool
    {
        if ($user->role !== 'admin' || ! $user->active) {
            return false;
        }

        if ($newRole === 'admin' && $newActive) {
            return false;
        }

        return User::where('role', 'admin')->where('active', true)->whereKeyNot($user->getKey())->count() === 0;
    }
}
