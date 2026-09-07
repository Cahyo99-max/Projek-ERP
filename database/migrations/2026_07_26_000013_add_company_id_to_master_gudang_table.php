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
        Schema::table('master_gudang', function (Blueprint $table) {
            if (!Schema::hasColumn('master_gudang', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'cabang_id', 'status'], 'idx_gdg_company_cabang_status');
            }

            try {
                $table->dropUnique('master_gudang_kode_gudang_unique');
            } catch (\Exception $e) {}
        });

        // Set company_id based on branch's company_id or default active company
        $gudangs = DB::table('master_gudang')->get();
        foreach ($gudangs as $g) {
            $branchCompanyId = DB::table('branches')->where('id', $g->cabang_id)->value('company_id');
            if ($branchCompanyId) {
                DB::table('master_gudang')->where('id', $g->id)->update(['company_id' => $branchCompanyId]);
            } else {
                $defaultCompanyId = DB::table('companies')->where('status', 'Active')->value('id') ?? DB::table('companies')->value('id');
                if ($defaultCompanyId) {
                    DB::table('master_gudang')->where('id', $g->id)->update(['company_id' => $defaultCompanyId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_gudang', function (Blueprint $table) {
            if (Schema::hasColumn('master_gudang', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_gdg_company_cabang_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
