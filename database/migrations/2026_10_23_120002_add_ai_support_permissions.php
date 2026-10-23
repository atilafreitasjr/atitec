<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSIONS = ['ai_support.view', 'ai_support.reply'];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['guard_name' => 'web', 'name' => $name]);
        }

        // O admin sempre tem tudo; gerentes já nascem com o atendimento habilitado.
        foreach (['admin', 'gerente'] as $roleName) {
            Role::where('name', $roleName)
                ->get()
                ->each(fn (Role $role) => $role->givePermissionTo(self::PERMISSIONS));
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', self::PERMISSIONS)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
