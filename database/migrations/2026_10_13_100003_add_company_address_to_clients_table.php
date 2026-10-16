<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('company_same_address')->default(false)->after('cnpj');
            $table->string('company_zip_code', 8)->nullable()->after('company_same_address');
            $table->string('company_address')->nullable()->after('company_zip_code');
            $table->string('company_address_number', 20)->nullable()->after('company_address');
            $table->string('company_address_complement', 120)->nullable()->after('company_address_number');
            $table->string('company_neighborhood', 120)->nullable()->after('company_address_complement');
            $table->string('company_city', 120)->nullable()->after('company_neighborhood');
            $table->string('company_state', 2)->nullable()->after('company_city');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'company_same_address', 'company_zip_code', 'company_address', 'company_address_number',
                'company_address_complement', 'company_neighborhood', 'company_city', 'company_state',
            ]);
        });
    }
};
