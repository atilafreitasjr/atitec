<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['a_fazer', 'fazendo', 'revisao', 'concluido'])->default('a_fazer');
            $table->enum('priority', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->string('assigned_to')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
