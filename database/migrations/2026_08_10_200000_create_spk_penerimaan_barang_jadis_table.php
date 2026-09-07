<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Penerimaan Barang Jadi SPK (Hasil Produksi).
     */
    public function up(): void
    {
        // 1. TABEL HEADER: SPK PENERIMAAN BARANG JADI (HASIL PRODUKSI)
        Schema::create('spk_penerimaan_barang_jadis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('nomor_penerimaan', 50)->unique();
            $table->date('tanggal_penerimaan');
            
            // Relasi ke SPK & Gudang Barang Jadi
            $table->foreignId('spk_id')->constrained('spk_produksi')->cascadeOnDelete();
            $table->foreignId('barang_jadi_id')->constrained('master_barang')->cascadeOnDelete();
            $table->foreignId('gudang_id')->constrained('master_gudang')->cascadeOnDelete();
            $table->foreignId('satuan_id')->nullable()->constrained('master_satuan')->nullOnDelete();
            
            // Qty Hasil Produksi & Costing HPP
            $table->decimal('jumlah_hasil_baik', 15, 4)->default(0);
            $table->decimal('jumlah_reject', 15, 4)->default(0);
            $table->decimal('hpp_satuan', 15, 4)->default(0);
            $table->decimal('total_nilai_hpp', 15, 4)->default(0);
            
            // Person In Charge
            $table->foreignId('penerima_karyawan_id')->nullable()->constrained('karyawans')->nullOnDelete();
            $table->string('penerima_nama', 100)->nullable(); // Petugas Gudang / Penerima
            $table->foreignId('penyerah_karyawan_id')->nullable()->constrained('karyawans')->nullOnDelete();
            $table->string('penyerah_nama', 100)->nullable(); // Operator Line / QC Penyerah
            
            $table->string('status', 30)->default('Diterima'); // Diterima, Dibatalkan
            $table->text('catatan')->nullable();

            // Audit Trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // ── Performance Composite Indexes (Scale to 7M+ records) ──
            $table->index(['spk_id', 'deleted_at'], 'idx_pbj_spk_soft');
            $table->index(['company_id', 'spk_id', 'deleted_at'], 'idx_pbj_company_spk_soft');
            $table->index(['tanggal_penerimaan', 'company_id', 'deleted_at'], 'idx_pbj_tanggal_company');
            $table->index(['spk_id', 'id', 'deleted_at'], 'idx_pbj_spk_id_paginate');
            $table->index(['gudang_id', 'barang_jadi_id'], 'idx_pbj_gudang_barang');
        });

        // 2. TABEL DETAIL: SPK PENERIMAAN BARANG JADI DETAILS (PER LOKASI RAK / REJECT BREAKDOWN)
        Schema::create('spk_penerimaan_barang_jadi_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')->constrained('spk_penerimaan_barang_jadis')->cascadeOnDelete();
            $table->foreignId('barang_jadi_id')->constrained('master_barang')->cascadeOnDelete();
            $table->foreignId('satuan_id')->nullable()->constrained('master_satuan')->nullOnDelete();
            $table->foreignId('lokasi_rak_id')->nullable()->constrained('master_lokasi_rak')->nullOnDelete();
            
            $table->decimal('jumlah_hasil_baik', 15, 4)->default(0);
            $table->decimal('jumlah_reject', 15, 4)->default(0);
            $table->decimal('hpp_satuan', 15, 4)->default(0);
            $table->decimal('total_nilai_hpp', 15, 4)->default(0);
            $table->string('alasan_reject', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Composite Indexes
            $table->index(['penerimaan_id'], 'idx_pbj_detail_penerimaan');
            $table->index(['barang_jadi_id'], 'idx_pbj_detail_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spk_penerimaan_barang_jadi_details');
        Schema::dropIfExists('spk_penerimaan_barang_jadis');
    }
};
