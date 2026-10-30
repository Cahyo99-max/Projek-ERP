<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Skala 7 Juta Baris: Indeks komposit performa tinggi untuk mencegah timeout,
     * filesort berat, dan beban scanning CPU pada modul stok opname & serial numbers.
     */
    public function up(): void
    {
        // 1. Indeks komposit untuk header stock_opnames
        if (Schema::hasTable('stock_opnames')) {
            Schema::table('stock_opnames', function (Blueprint $table) {
                if (!Schema::hasIndex('stock_opnames', 'idx_so_tenant_date_id')) {
                    $table->index(['company_id', 'branch_id', 'deleted_at', 'tanggal_opname', 'id'], 'idx_so_tenant_date_id');
                }
                if (!Schema::hasIndex('stock_opnames', 'idx_so_tenant_status')) {
                    $table->index(['company_id', 'branch_id', 'status', 'deleted_at'], 'idx_so_tenant_status');
                }
                if (!Schema::hasIndex('stock_opnames', 'idx_so_tenant_gudang')) {
                    $table->index(['company_id', 'branch_id', 'gudang_id', 'deleted_at'], 'idx_so_tenant_gudang');
                }
                if (!Schema::hasIndex('stock_opnames', 'idx_so_deleted_at')) {
                    $table->index('deleted_at', 'idx_so_deleted_at');
                }
            });
        }

        // 2. Indeks komposit untuk serial numbers di gudang (skala jutaan unit serial)
        if (Schema::hasTable('spk_penerimaan_barang_jadi_serials')) {
            Schema::table('spk_penerimaan_barang_jadi_serials', function (Blueprint $table) {
                if (!Schema::hasIndex('spk_penerimaan_barang_jadi_serials', 'idx_pbj_serials_barang_status_gudang')) {
                    $table->index(['barang_jadi_id', 'status', 'gudang_id'], 'idx_pbj_serials_barang_status_gudang');
                }
            });
        }

        // 3. Indeks komposit untuk stock_opname_details (pencarian balik barang_id -> opname_id)
        if (Schema::hasTable('stock_opname_details')) {
            Schema::table('stock_opname_details', function (Blueprint $table) {
                if (!Schema::hasIndex('stock_opname_details', 'idx_sod_barang_opname')) {
                    $table->index(['barang_id', 'opname_id'], 'idx_sod_barang_opname');
                }
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
                try { $table->dropIndex('idx_so_tenant_date_id'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_so_tenant_status'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_so_tenant_gudang'); } catch (\Exception $e) {}
                try { $table->dropIndex('idx_so_deleted_at'); } catch (\Exception $e) {}
            });
        }

        if (Schema::hasTable('spk_penerimaan_barang_jadi_serials')) {
            Schema::table('spk_penerimaan_barang_jadi_serials', function (Blueprint $table) {
                try { $table->dropIndex('idx_pbj_serials_barang_status_gudang'); } catch (\Exception $e) {}
            });
        }

        if (Schema::hasTable('stock_opname_details')) {
            Schema::table('stock_opname_details', function (Blueprint $table) {
                try { $table->dropIndex('idx_sod_barang_opname'); } catch (\Exception $e) {}
            });
        }
    }
};

