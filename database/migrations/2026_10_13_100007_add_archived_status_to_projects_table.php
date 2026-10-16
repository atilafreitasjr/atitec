<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUSES = ['prospeccao', 'em_desenvolvimento', 'homologacao', 'entregue', 'suporte', 'arquivado'];

    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('status', self::STATUSES)->default('em_desenvolvimento')->change();
        });
    }

    public function down(): void
    {
        DB::table('projects')->where('status', 'arquivado')->update(['status' => 'suporte']);

        Schema::table('projects', function (Blueprint $table) {
            $table->enum('status', ['prospeccao', 'em_desenvolvimento', 'homologacao', 'entregue', 'suporte'])
                ->default('em_desenvolvimento')->change();
        });
    }
};
