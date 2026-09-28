<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('problem')->nullable();
            $table->text('solution')->nullable();
            $table->text('results')->nullable();
            $table->string('url')->nullable();
            $table->string('segment')->nullable();
            $table->string('technologies')->nullable();
            $table->enum('status', ['prospeccao', 'em_desenvolvimento', 'homologacao', 'entregue', 'suporte'])->default('em_desenvolvimento');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->date('deadline')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('image')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
