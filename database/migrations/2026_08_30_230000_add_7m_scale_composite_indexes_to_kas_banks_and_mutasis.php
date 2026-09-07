<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for 7M+ Extreme Scale Indexing on Kas & Bank.
     */
    public function up(): void
    {
        // 1. Indeks Komposit Skala 7 Juta Data pada Kas Mutasi
        Schema::table('kas_mutasis', function (Blueprint $table) {
            // Indeks pencarian & pengurutan mutasi per perusahaan, cabang & tanggal (Buku Kas Utama)
            $table->index(['company_id', 'branch_id', 'tanggal', 'id'], 'idx_km_company_branch_tanggal_id');
            
            // Indeks buku besar per akun rekening & tanggal (Kartu Mutasi & Rekening Koran)
            $table->index(['kas_bank_id', 'tanggal', 'id'], 'idx_km_kasbank_tanggal_id');
            
            // Indeks kalkulasi cepat dashboard ringkasan kas masuk & keluar
            $table->index(['company_id', 'status', 'tipe', 'tanggal'], 'idx_km_company_status_tipe_tanggal');
            
            // Indeks pencarian cepat nomor referensi & nama kontak
            $table->index('nomor_referensi', 'idx_km_nomor_referensi');
            $table->index('nama_kontak', 'idx_km_nama_kontak');
        });

        // 2. Indeks pada Master Akun Kas & Bank
        Schema::table('kas_banks', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'is_active'], 'idx_kb_company_branch_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_mutasis', function (Blueprint $table) {
            $table->dropIndex('idx_km_company_branch_tanggal_id');
            $table->dropIndex('idx_km_kasbank_tanggal_id');
            $table->dropIndex('idx_km_company_status_tipe_tanggal');
            $table->dropIndex('idx_km_nomor_referensi');
            $table->dropIndex('idx_km_nama_kontak');
        });

        Schema::table('kas_banks', function (Blueprint $table) {
            $table->dropIndex('idx_kb_company_branch_active');
        });
    }
};
