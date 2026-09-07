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
        Schema::table('master_lokasi_rak', function (Blueprint $table) {
            if (!Schema::hasColumn('master_lokasi_rak', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'gudang_id', 'status'], 'idx_rak_company_gudang_status');
            }

            try {
                $table->dropUnique('master_lokasi_rak_kode_rak_unique');
            } catch (\Exception $e) {}
        });

        // Set company_id for existing records based on gudang's company_id or active company
        $raks = DB::table('master_lokasi_rak')->get();
        foreach ($raks as $r) {
            $gudangCompanyId = DB::table('master_gudang')->where('id', $r->gudang_id)->value('company_id');
            if ($gudangCompanyId) {
                DB::table('master_lokasi_rak')->where('id', $r->id)->update(['company_id' => $gudangCompanyId]);
            } else {
                $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id') ?? DB::table('companies')->value('id');
                if ($defaultCompanyId) {
                    DB::table('master_lokasi_rak')->where('id', $r->id)->update(['company_id' => $defaultCompanyId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_lokasi_rak', function (Blueprint $table) {
            if (Schema::hasColumn('master_lokasi_rak', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_rak_company_gudang_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
