<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class KanbanRolePermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'deliverable.view',
        'deliverable.create',
        'deliverable.edit',
        'deliverable.delete',
        'task.view',
        'task.create',
        'task.edit',
        'task.delete',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['guard_name' => 'web', 'name' => $name]);
        }

        // Mapeia a coluna role (ATITEC) para papéis Spatie com permissões do Kanban.
        $map = [
            'admin' => self::PERMISSIONS,
            'gerente' => self::PERMISSIONS,
            'financeiro' => ['deliverable.view', 'task.view'],
            'cliente' => ['deliverable.view', 'task.view'],
        ];

        foreach ($map as $roleName => $permissions) {
            $role = Role::firstOrCreate(['guard_name' => 'web', 'name' => $roleName]);
            $role->syncPermissions($permissions);
        }

        // Sincroniza usuários existentes pela coluna role.
        foreach (User::all() as $user) {
            if ($user->role && isset($map[$user->role])) {
                $user->syncRoles([$user->role]);
            }
        }
    }
}
