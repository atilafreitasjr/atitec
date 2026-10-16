<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('cpf', 11)->nullable()->after('document');
            $table->string('cnpj', 14)->nullable()->after('cpf');
            $table->string('zip_code', 8)->nullable()->after('address');
            $table->string('address_number', 20)->nullable()->after('zip_code');
            $table->string('address_complement', 120)->nullable()->after('address_number');
            $table->string('neighborhood', 120)->nullable()->after('address_complement');
            $table->string('city', 120)->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
        });

        // Migra o antigo campo livre `document` (CPF/CNPJ) para as colunas próprias,
        // gravando apenas os dígitos. Valores fora do padrão são preservados em `document`.
        $seen = ['cpf' => [], 'cnpj' => []];
        DB::table('clients')->whereNotNull('document')->orderBy('id')->get(['id', 'document'])
            ->each(function ($row) use (&$seen) {
                $digits = preg_replace('/\D+/', '', (string) $row->document);
                $column = match (strlen($digits)) {
                    11 => 'cpf',
                    14 => 'cnpj',
                    default => null,
                };

                if ($column && ! isset($seen[$column][$digits])) {
                    $seen[$column][$digits] = true;
                    DB::table('clients')->where('id', $row->id)->update([$column => $digits]);
                }
            });

        Schema::table('clients', function (Blueprint $table) {
            $table->unique('cpf');
            $table->unique('cnpj');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
            $table->dropUnique(['cnpj']);
            $table->dropColumn([
                'cpf', 'cnpj', 'zip_code', 'address_number', 'address_complement',
                'neighborhood', 'city', 'state',
            ]);
        });
    }
};
