<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('project_type')->nullable();
            $table->string('budget_range')->nullable();
            $table->text('details')->nullable();
            $table->enum('status', ['novo', 'em_contato', 'proposta', 'fechado', 'perdido'])->default('novo');
            $table->string('source')->default('site');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
