<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliverable_tasks', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index(['project_id', 'status', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::table('deliverable_tasks', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'status', 'invoice_id']);
            $table->dropConstrainedForeignId('invoice_id');
        });
    }
};
