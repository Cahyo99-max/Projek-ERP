<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_currencies', function (Blueprint $table) {
            if (!Schema::hasColumn('master_currencies', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_currency_company_status');
            }

            // Drop global unique constraint on kode_currency
            try { $table->dropUnique('master_currencies_kode_currency_unique'); } catch (\Exception $e) {}
        });

        // Backfill company_id for existing master_currencies records
        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id')
            ?? DB::table('companies')->value('id');

        if ($defaultCompanyId) {
            DB::table('master_currencies')
                ->whereNull('company_id')
                ->update(['company_id' => $defaultCompanyId]);
        }
    }

    public function down(): void
    {
        Schema::table('master_currencies', function (Blueprint $table) {
            if (Schema::hasColumn('master_currencies', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_currency_company_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
