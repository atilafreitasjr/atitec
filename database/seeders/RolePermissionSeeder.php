<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Catálogo de permissões + papéis padrão do sistema.
 *
 * Idempotente: cria as permissões que faltam, garante que o papel `admin` tenha
 * todas e ADICIONA aos outros papéis apenas as permissões padrão ausentes —
 * permissões revogadas manualmente no painel não são reintroduzidas.
 */
class RolePermissionSeeder extends Seeder
{
    /** Permissões gerenciáveis no painel, por módulo. */
    public const CATALOG = [
        'deliverable' => ['view', 'create', 'edit', 'delete'],
        'task' => ['view', 'create', 'edit', 'delete'],
        'client' => ['view', 'create', 'edit', 'delete'],
        'project' => ['view', 'create', 'edit', 'delete'],
        'invoice' => ['view', 'create', 'edit', 'delete'],
        'lead' => ['view', 'edit', 'delete'],
        'conversation' => ['view', 'create', 'edit', 'delete'],
        'channel' => ['view', 'create', 'edit', 'delete'],
        'user' => ['view', 'create', 'edit', 'delete'],
        'role' => ['view', 'create', 'edit', 'delete'],
    ];

    /** Papéis do sistema e suas permissões padrão. */
    public const ROLE_DEFAULTS = [
        'admin' => ['*'],
        'gerente' => [
            'deliverable.*', 'task.*',
            'client.*', 'project.*', 'lead.*', 'conversation.*',
            'invoice.view', 'invoice.create', 'invoice.edit',
            'channel.view',
        ],
        'financeiro' => [
            'deliverable.view', 'task.view', 'client.view', 'project.view',
            'invoice.*', 'lead.view', 'conversation.view',
        ],
        'cliente' => ['deliverable.view', 'task.view'],
    ];

    public static function permissions(): array
    {
        $all = [];
        foreach (self::CATALOG as $module => $actions) {
            foreach ($actions as $action) {
                $all[] = "{$module}.{$action}";
            }
        }

        return $all;
    }

    /** Expande curingas ("invoice.*") para a lista de permissões. */
    public static function expand(array $patterns): array
    {
        $all = self::permissions();
        $result = [];

        foreach ($patterns as $pattern) {
            if (str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -1); // "invoice."
                $result = array_merge($result, array_filter($all, fn ($p) => str_starts_with($p, $prefix)));

                continue;
            }
            $result[] = $pattern;
        }

        return array_values(array_unique($result));
    }

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $all = self::permissions();

        // Remove permissões que saíram do catálogo (mantém o banco limpo).
        Permission::whereNotIn('name', $all)->delete();

        foreach ($all as $name) {
            Permission::firstOrCreate(['guard_name' => 'web', 'name' => $name]);
        }

        foreach (self::ROLE_DEFAULTS as $roleName => $defaults) {
            $role = Role::firstOrCreate(['guard_name' => 'web', 'name' => $roleName]);

            if ($roleName === 'admin') {
                $role->syncPermissions(Permission::where('guard_name', 'web')->get());

                continue;
            }

            $missing = collect(self::expand($defaults))
                ->diff($role->permissions->pluck('name'))
                ->all();

            if ($missing !== []) {
                $role->givePermissionTo($missing);
            }
        }

        // Sincroniza o papel Spatie de todos os usuários com a coluna role.
        User::all()->each(function ($user) {
            if ($user->role && Role::where('name', $user->role)->exists()) {
                $user->syncRoles([$user->role]);
            }
        });
    }
}
