<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_project', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'client_id']);
            $table->index('client_id');
        });

        DB::table('projects')
            ->whereNotNull('client_id')
            ->orderBy('id')
            ->chunkById(500, function ($projects) {
                $links = $projects->map(fn ($project) => [
                    'project_id' => $project->id,
                    'client_id' => $project->client_id,
                ])->all();

                DB::table('client_project')->insert($links);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_project');
    }
};
