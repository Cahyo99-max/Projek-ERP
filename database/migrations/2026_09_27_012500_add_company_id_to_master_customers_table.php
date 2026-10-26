<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('master_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('master_customers', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_customers_company_status');
                $table->index(['company_id', 'kode_customer'], 'idx_customers_company_kode');
            }
        });

        // Set default company_id for existing master_customers records to the first active company
        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id') 
                            ?? DB::table('companies')->value('id');

        if ($defaultCompanyId) {
            DB::table('master_customers')
              ->whereNull('company_id')
              ->update(['company_id' => $defaultCompanyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_customers', function (Blueprint $table) {
            if (Schema::hasColumn('master_customers', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_customers_company_status');
                $table->dropIndex('idx_customers_company_kode');
                $table->dropColumn('company_id');
            }
        });
    }
};
