<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // role passa de enum para string: permite criar novos papéis Spatie sem migração.
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'cliente'");

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','gerente','cliente','financeiro') NOT NULL DEFAULT 'cliente'");
    }
};
