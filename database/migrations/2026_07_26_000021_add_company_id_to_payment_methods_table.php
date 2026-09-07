<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_methods', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_method_company_status');
            }

            // Drop global unique constraint on kode_metode
            try { $table->dropUnique('payment_methods_kode_metode_unique'); } catch (\Exception $e) {}
        });

        // Backfill company_id for existing payment_methods records
        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id')
            ?? DB::table('companies')->value('id');

        if ($defaultCompanyId) {
            DB::table('payment_methods')
                ->whereNull('company_id')
                ->update(['company_id' => $defaultCompanyId]);
        }
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            if (Schema::hasColumn('payment_methods', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_method_company_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
