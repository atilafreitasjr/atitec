<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@atitec.com.br'],
            [
                'name' => 'Administrador ATITEC',
                'password' => 'password',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
