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
        // 1. Tambah kolom serial_numbers pada stock_adjustment_details
        if (Schema::hasTable('stock_adjustment_details')) {
            Schema::table('stock_adjustment_details', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_adjustment_details', 'serial_numbers')) {
                    $table->json('serial_numbers')->nullable()->after('keterangan_detail');
                }
            });
        }

        // 2. Modifikasi tabel spk_penerimaan_barang_jadi_serials agar mendukung penambahan dari Stock Adjustment
        if (Schema::hasTable('spk_penerimaan_barang_jadi_serials')) {
            Schema::table('spk_penerimaan_barang_jadi_serials', function (Blueprint $table) {
                // Buat penerimaan_id dan spk_id nullable (karena barang dari adjustment tidak melalui SPK pabrik)
                if (Schema::hasColumn('spk_penerimaan_barang_jadi_serials', 'penerimaan_id')) {
                    $table->unsignedBigInteger('penerimaan_id')->nullable()->change();
                }
                if (Schema::hasColumn('spk_penerimaan_barang_jadi_serials', 'spk_id')) {
                    $table->unsignedBigInteger('spk_id')->nullable()->change();
                }

                // Tambah relasi gudang_id dan stock_adjustment_id jika belum ada
                if (!Schema::hasColumn('spk_penerimaan_barang_jadi_serials', 'gudang_id')) {
                    $table->unsignedBigInteger('gudang_id')->nullable()->after('barang_jadi_id');
                    $table->index(['gudang_id', 'status'], 'idx_pbj_serials_gudang_status');
                }
                if (!Schema::hasColumn('spk_penerimaan_barang_jadi_serials', 'stock_adjustment_id')) {
                    $table->unsignedBigInteger('stock_adjustment_id')->nullable()->after('gudang_id');
                    $table->index(['stock_adjustment_id'], 'idx_pbj_serials_stock_adj');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_adjustment_details')) {
            Schema::table('stock_adjustment_details', function (Blueprint $table) {
                if (Schema::hasColumn('stock_adjustment_details', 'serial_numbers')) {
                    $table->dropColumn('serial_numbers');
                }
            });
        }

        if (Schema::hasTable('spk_penerimaan_barang_jadi_serials')) {
            Schema::table('spk_penerimaan_barang_jadi_serials', function (Blueprint $table) {
                if (Schema::hasColumn('spk_penerimaan_barang_jadi_serials', 'gudang_id')) {
                    $table->dropIndex('idx_pbj_serials_gudang_status');
                    $table->dropColumn('gudang_id');
                }
                if (Schema::hasColumn('spk_penerimaan_barang_jadi_serials', 'stock_adjustment_id')) {
                    $table->dropIndex('idx_pbj_serials_stock_adj');
                    $table->dropColumn('stock_adjustment_id');
                }
            });
        }
    }
};
