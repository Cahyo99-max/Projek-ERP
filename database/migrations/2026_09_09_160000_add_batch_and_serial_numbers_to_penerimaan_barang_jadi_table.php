<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom Nomor Batch, Tanggal Produksi, dan Expired Date ke Header Penerimaan Barang Jadi
        Schema::table('spk_penerimaan_barang_jadis', function (Blueprint $table) {
            if (!Schema::hasColumn('spk_penerimaan_barang_jadis', 'nomor_batch')) {
                $table->string('nomor_batch', 50)->nullable()->after('nomor_penerimaan');
                $table->index('nomor_batch', 'idx_pbj_nomor_batch');
            }
            if (!Schema::hasColumn('spk_penerimaan_barang_jadis', 'tanggal_produksi')) {
                $table->date('tanggal_produksi')->nullable()->after('tanggal_penerimaan');
            }
            if (!Schema::hasColumn('spk_penerimaan_barang_jadis', 'tanggal_kedaluwarsa')) {
                $table->date('tanggal_kedaluwarsa')->nullable()->after('tanggal_produksi');
            }
        });

        // 2. Buat tabel khusus Serial Number per unit produk jadi
        if (!Schema::hasTable('spk_penerimaan_barang_jadi_serials')) {
            Schema::create('spk_penerimaan_barang_jadi_serials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('penerimaan_id')->constrained('spk_penerimaan_barang_jadis')->cascadeOnDelete();
                $table->foreignId('spk_id')->constrained('spk_produksi')->cascadeOnDelete();
                $table->foreignId('barang_jadi_id')->constrained('master_barang')->cascadeOnDelete();
                $table->string('nomor_batch', 50)->nullable();
                $table->string('serial_number', 100);
                $table->string('status', 30)->default('Tersedia'); // Tersedia, Terjual, Rusak/RMA
                $table->text('catatan')->nullable();
                $table->timestamps();

                // Composite & Performance Indexes
                $table->index(['penerimaan_id', 'serial_number'], 'idx_pbj_serials_penerimaan_sn');
                $table->index(['barang_jadi_id', 'serial_number'], 'idx_pbj_serials_barang_sn');
                $table->index(['serial_number'], 'idx_pbj_serials_sn');
                $table->index(['nomor_batch'], 'idx_pbj_serials_batch');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spk_penerimaan_barang_jadi_serials');

        Schema::table('spk_penerimaan_barang_jadis', function (Blueprint $table) {
            if (Schema::hasColumn('spk_penerimaan_barang_jadis', 'nomor_batch')) {
                $table->dropIndex('idx_pbj_nomor_batch');
                $table->dropColumn('nomor_batch');
            }
            if (Schema::hasColumn('spk_penerimaan_barang_jadis', 'tanggal_produksi')) {
                $table->dropColumn('tanggal_produksi');
            }
            if (Schema::hasColumn('spk_penerimaan_barang_jadis', 'tanggal_kedaluwarsa')) {
                $table->dropColumn('tanggal_kedaluwarsa');
            }
        });
    }
};
