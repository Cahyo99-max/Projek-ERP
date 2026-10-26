<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for 7M+ Extreme Scale Indexing on General Journal (Jurnal Umum).
     */
    public function up(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            // 1. High-Performance Index: PT Pusat + Tanggal + ID (Untuk Pagination Cepat & Anti-Filesort)
            $table->index(['company_id', 'tanggal_jurnal', 'id'], 'idx_jurnal_company_date_id');

            // 2. High-Performance Index: Cabang + Tanggal + ID (Untuk Pagination Cepat & Anti-Filesort)
            $table->index(['branch_id', 'tanggal_jurnal', 'id'], 'idx_jurnal_branch_date_id');

            // 3. Covering Index untuk Kartu Metrik (Count, Sum Debit, Sum Kredit) PT Pusat tanpa sentuh disk table
            $table->index(['company_id', 'tanggal_jurnal', 'total_debit', 'total_kredit'], 'idx_jurnal_company_date_stats');

            // 4. Covering Index untuk Kartu Metrik (Count, Sum Debit, Sum Kredit) Cabang tanpa sentuh disk table
            $table->index(['branch_id', 'tanggal_jurnal', 'total_debit', 'total_kredit'], 'idx_jurnal_branch_date_stats');

            // 5. Index Komposit Sumber Modul / Referensi + Company + Tanggal
            $table->index(['referensi_tipe', 'company_id', 'tanggal_jurnal'], 'idx_jurnal_ref_company_date');
        });

        Schema::table('jurnal_details', function (Blueprint $table) {
            // 6. Covering Index untuk Detail Akun (Eager Loading 100% Index-Only Scan)
            $table->index(['jurnal_id', 'account_id', 'debit', 'kredit'], 'idx_jd_covering_jurnal_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_details', function (Blueprint $table) {
            $table->dropIndex('idx_jd_covering_jurnal_account');
        });

        Schema::table('jurnals', function (Blueprint $table) {
            $table->dropIndex('idx_jurnal_company_date_id');
            $table->dropIndex('idx_jurnal_branch_date_id');
            $table->dropIndex('idx_jurnal_company_date_stats');
            $table->dropIndex('idx_jurnal_branch_date_stats');
            $table->dropIndex('idx_jurnal_ref_company_date');
        });
    }
};
