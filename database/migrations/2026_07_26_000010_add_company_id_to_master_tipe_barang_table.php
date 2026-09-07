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
        Schema::table('master_tipe_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('master_tipe_barang', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_tipe_barang_company_status');
            }

            try {
                $table->dropUnique('master_tipe_barang_kode_tipe_unique');
            } catch (\Exception $e) {}

            try {
                $table->dropUnique('master_tipe_barang_nama_tipe_unique');
            } catch (\Exception $e) {}
        });

        // Set default company_id for existing master_tipe_barang records
        $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id') 
                            ?? DB::table('companies')->value('id');

        if ($defaultCompanyId) {
            DB::table('master_tipe_barang')
              ->whereNull('company_id')
              ->update(['company_id' => $defaultCompanyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_tipe_barang', function (Blueprint $table) {
            if (Schema::hasColumn('master_tipe_barang', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_tipe_barang_company_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
