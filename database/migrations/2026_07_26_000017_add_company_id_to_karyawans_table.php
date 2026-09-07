<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawans', 'company_id')) {
                $table->foreignId('company_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('companies')
                      ->onDelete('cascade');

                $table->index(['company_id', 'status'], 'idx_karyawan_company_status');
            }

            // Drop global unique constraints — kode_karyawan and nik can be the same across different companies
            try { $table->dropUnique('karyawans_kode_karyawan_unique'); } catch (\Exception $e) {}
            try { $table->dropUnique('karyawans_nik_unique'); } catch (\Exception $e) {}
        });

        // Assign company_id to existing karyawans from their department's company_id
        $karyawans = DB::table('karyawans')->get();
        foreach ($karyawans as $k) {
            $deptCompanyId = $k->departemen_id
                ? DB::table('departments')->where('id', $k->departemen_id)->value('company_id')
                : null;
            $companyId = $deptCompanyId
                ?? DB::table('companies')->where('status', 'Active')->value('id')
                ?? DB::table('companies')->value('id');
            if ($companyId) {
                DB::table('karyawans')->where('id', $k->id)->update(['company_id' => $companyId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (Schema::hasColumn('karyawans', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex('idx_karyawan_company_status');
                $table->dropColumn('company_id');
            }
        });
    }
};
