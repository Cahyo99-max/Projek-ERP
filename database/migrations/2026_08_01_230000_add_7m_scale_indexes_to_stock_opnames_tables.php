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
        if (Schema::hasTable('stock_opnames')) {
            Schema::table('stock_opnames', function (Blueprint $table) {
                try {
                    $table->index(['gudang_id', 'status', 'tanggal_opname'], 'idx_so_gudang_status_date');
                    $table->index(['nomor_opname'], 'idx_so_nomor_opname');
                } catch (\Exception $e) {}
            });
        }

        if (Schema::hasTable('stock_opname_details')) {
            Schema::table('stock_opname_details', function (Blueprint $table) {
                try {
                    $table->index(['opname_id', 'barang_id'], 'idx_sod_opname_barang');
                    $table->index(['opname_id', 'stok_fisik'], 'idx_sod_opname_stok_fisik');
                } catch (\Exception $e) {}
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_opnames')) {
            Schema::table('stock_opnames', function (Blueprint $table) {
                try { $table->dropIndex('idx_so_gudang_status_date'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_so_nomor_opname'); } catch (\Exception $e) {}
            });
        }

        if (Schema::hasTable('stock_opname_details')) {
            Schema::table('stock_opname_details', function (Blueprint $table) {
                try { $table->dropIndex('idx_sod_opname_barang'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_sod_opname_stok_fisik'); } catch (\Exception $e) {}
            });
        }
    }
};
