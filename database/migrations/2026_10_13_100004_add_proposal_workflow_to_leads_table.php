<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('proposal_title')->nullable();
            $table->text('proposal_summary')->nullable();
            $table->decimal('proposal_amount', 12, 2)->nullable();
            $table->date('proposal_valid_until')->nullable();
            $table->string('proposal_status', 20)->default('rascunho');
            $table->timestamp('proposal_sent_at')->nullable();
            $table->timestamp('proposal_responded_at')->nullable();
            $table->foreignId('converted_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->index(['client_id', 'proposal_status']);
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'proposal_status']);
            $table->dropConstrainedForeignId('converted_project_id');
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn([
                'proposal_title', 'proposal_summary', 'proposal_amount', 'proposal_valid_until',
                'proposal_status', 'proposal_sent_at', 'proposal_responded_at',
            ]);
        });
    }
};
