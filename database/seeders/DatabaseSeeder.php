<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ChannelSeeder::class,
            ProjectSeeder::class,
            KanbanRolePermissionSeeder::class,
            // KanbanImportSeeder é one-shot (requer staging import_* do agricultura_familiar).
        ]);
    }
}
