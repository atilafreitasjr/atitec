<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('admin.roles.index', [
            'roles' => $roles,
            'catalog' => RolePermissionSeeder::CATALOG,
            'coreRoles' => User::CORE_ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_\-]+$/', Rule::unique('roles', 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::exists('permissions', 'name')],
        ], [
            'name.regex' => 'Use apenas letras minúsculas, números, hífen ou underline.',
        ]);

        $role = Role::create(['guard_name' => 'web', 'name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', "Papel {$role->name} criado.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::exists('permissions', 'name')],
        ]);

        if ($role->name === 'admin') {
            return back()->withErrors(['permissions' => 'O papel admin sempre mantém todas as permissões.']);
        }

        $role->syncPermissions($data['permissions'] ?? []);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', "Permissões do papel {$role->name} atualizadas.");
    }

    /** Salva a matriz completa (um conjunto de permissões por papel). */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['array'],
            'roles.*.*' => [Rule::exists('permissions', 'name')],
        ]);

        $matrix = $request->input('roles', []);
        $valid = Permission::pluck('name')->all();

        foreach (Role::all() as $role) {
            if ($role->name === 'admin') {
                continue; // admin mantém todas as permissões.
            }

            $permissions = array_values(array_intersect($matrix[$role->name] ?? [], $valid));
            $role->syncPermissions($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Permissões atualizadas.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, User::CORE_ROLES, true)) {
            return back()->withErrors(['role' => 'Papéis padrão do sistema não podem ser excluídos.']);
        }

        if (User::where('role', $role->name)->exists()) {
            return back()->withErrors(['role' => 'Há usuários com este papel — troque o papel deles antes de excluir.']);
        }

        $name = $role->name;
        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', "Papel {$name} excluído.");
    }
}
