<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for 7M+ Extreme Scale Indexing on Petty Cash.
     */
    public function up(): void
    {
        Schema::table('petty_cashes', function (Blueprint $table) {
            // 1. Indeks komposit performa tinggi: Filter PT Pusat + Rentang Tanggal + Urutan ID
            $table->index(['company_id', 'tipe_entitas', 'tanggal', 'id'], 'idx_pc_company_entitas_tanggal_id');
            
            // 2. Indeks komposit performa tinggi: Filter Cabang + Rentang Tanggal + Urutan ID
            $table->index(['branch_id', 'tipe_entitas', 'tanggal', 'id'], 'idx_pc_branch_entitas_tanggal_id');
            
            // 3. Indeks akun kas (COA Kas) + tanggal + status (untuk buku kas & laporan per rekening kas)
            $table->index(['coa_kas_id', 'tanggal', 'status'], 'idx_pc_coa_tanggal_status');
            
            // 4. Indeks kalkulasi cepat kartu statistik pengeluaran hari ini & bulan ini (PT)
            $table->index(['company_id', 'status', 'tipe', 'tanggal'], 'idx_pc_company_status_tipe_tanggal');
            
            // 5. Indeks kalkulasi cepat kartu statistik pengeluaran hari ini & bulan ini (Cabang)
            $table->index(['branch_id', 'status', 'tipe', 'tanggal'], 'idx_pc_branch_status_tipe_tanggal');
            
            // 6. Indeks pencarian & pengelompokan pemohon transaksi
            $table->index(['nama_pengaju', 'tanggal'], 'idx_pc_nama_pengaju_tanggal');
            
            // 7. Indeks tender proyek & tanggal
            $table->index(['tender_id', 'tanggal'], 'idx_pc_tender_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cashes', function (Blueprint $table) {
            $table->dropIndex('idx_pc_company_entitas_tanggal_id');
            $table->dropIndex('idx_pc_branch_entitas_tanggal_id');
            $table->dropIndex('idx_pc_coa_tanggal_status');
            $table->dropIndex('idx_pc_company_status_tipe_tanggal');
            $table->dropIndex('idx_pc_branch_status_tipe_tanggal');
            $table->dropIndex('idx_pc_nama_pengaju_tanggal');
            $table->dropIndex('idx_pc_tender_tanggal');
        });
    }
};
