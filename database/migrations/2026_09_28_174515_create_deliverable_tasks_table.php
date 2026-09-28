<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliverable_tasks', function (Blueprint $table) {
            $table->id();
            // Escopo por projeto (desnormalizado da entrega para isolamento simples e rápido).
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deliverable_id')->constrained()->cascadeOnDelete();
            $table->string('phase', 1)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('pendente');
            $table->string('priority')->default('media');
            $table->date('due_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('hours_estimated', 8, 2)->nullable();
            $table->decimal('hours_actual', 8, 2)->nullable();
            $table->decimal('hourly_rate', 10, 2)->default(100);
            $table->unsignedInteger('order')->default(0);
            $table->string('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverable_tasks');
    }
};
