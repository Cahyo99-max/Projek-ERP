<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_terms', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_terms', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_term_company_status');
            }
        });

        // Backfill company_id for existing payment_terms records
        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id')
            ?? DB::table('companies')->value('id');

        if ($defaultCompanyId) {
            DB::table('payment_terms')
                ->whereNull('company_id')
                ->update(['company_id' => $defaultCompanyId]);
        }
    }

    public function down(): void
    {
        Schema::table('payment_terms', function (Blueprint $table) {
            if (Schema::hasColumn('payment_terms', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_term_company_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
