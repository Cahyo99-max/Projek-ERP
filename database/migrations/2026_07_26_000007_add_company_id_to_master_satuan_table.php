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
        Schema::table('master_satuan', function (Blueprint $table) {
            if (!Schema::hasColumn('master_satuan', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_satuan_company_status');
            }
        });

        // Set default company_id for existing master_satuan records
        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id') 
                            ?? DB::table('companies')->value('id');

        if ($defaultCompanyId) {
            DB::table('master_satuan')
              ->whereNull('company_id')
              ->update(['company_id' => $defaultCompanyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_satuan', function (Blueprint $table) {
            if (Schema::hasColumn('master_satuan', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_satuan_company_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
