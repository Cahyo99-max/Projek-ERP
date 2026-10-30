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
        // 1. Tambah kolom serial_numbers pada stock_opname_details
        if (Schema::hasTable('stock_opname_details')) {
            Schema::table('stock_opname_details', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_opname_details', 'serial_numbers')) {
                    $table->json('serial_numbers')->nullable()->after('catatan');
                }
                if (!Schema::hasColumn('stock_opname_details', 'serial_numbers_sistem')) {
                    $table->json('serial_numbers_sistem')->nullable()->after('serial_numbers');
                }
            });
        }

        // 2. Tambah kolom stock_opname_id pada spk_penerimaan_barang_jadi_serials
        if (Schema::hasTable('spk_penerimaan_barang_jadi_serials')) {
            Schema::table('spk_penerimaan_barang_jadi_serials', function (Blueprint $table) {
                if (!Schema::hasColumn('spk_penerimaan_barang_jadi_serials', 'stock_opname_id')) {
                    $table->unsignedBigInteger('stock_opname_id')->nullable()->after('stock_adjustment_id');
                    $table->index(['stock_opname_id'], 'idx_pbj_serials_stock_opname');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_opname_details')) {
            Schema::table('stock_opname_details', function (Blueprint $table) {
                if (Schema::hasColumn('stock_opname_details', 'serial_numbers_sistem')) {
                    $table->dropColumn('serial_numbers_sistem');
                }
                if (Schema::hasColumn('stock_opname_details', 'serial_numbers')) {
                    $table->dropColumn('serial_numbers');
                }
            });
        }

        if (Schema::hasTable('spk_penerimaan_barang_jadi_serials')) {
            Schema::table('spk_penerimaan_barang_jadi_serials', function (Blueprint $table) {
                if (Schema::hasColumn('spk_penerimaan_barang_jadi_serials', 'stock_opname_id')) {
                    $table->dropIndex('idx_pbj_serials_stock_opname');
                    $table->dropColumn('stock_opname_id');
                }
            });
        }
    }
};

