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
        Schema::table('master_brand', function (Blueprint $table) {
            if (!Schema::hasColumn('master_brand', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_brand_company_status');
            }

            try {
                $table->dropUnique('master_brand_kode_brand_unique');
            } catch (\Exception $e) {}

            try {
                $table->dropUnique('master_brand_nama_brand_unique');
            } catch (\Exception $e) {}
        });

        // Set default company_id for existing master_brand records
        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id') 
                            ?? DB::table('companies')->value('id');

        if ($defaultCompanyId) {
            DB::table('master_brand')
              ->whereNull('company_id')
              ->update(['company_id' => $defaultCompanyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_brand', function (Blueprint $table) {
            if (Schema::hasColumn('master_brand', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_brand_company_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
