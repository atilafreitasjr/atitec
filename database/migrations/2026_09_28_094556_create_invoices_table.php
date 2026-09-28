<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['pendente', 'pago', 'vencido', 'cancelado'])->default('pendente');
            $table->enum('type', ['mensalidade', 'servico', 'projeto'])->default('mensalidade');
            $table->string('gateway')->nullable();
            $table->string('external_id')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
