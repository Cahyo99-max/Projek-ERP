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
        Schema::table('delivery_orders', function (Blueprint $table) {
            // Indeks komposit untuk isolasi tenant & status (query utama DataTables & Dashboard)
            if (!Schema::hasIndex('delivery_orders', 'idx_do_tenant_status')) {
                $table->index(['company_id', 'branch_id', 'status'], 'idx_do_tenant_status');
            }
            if (!Schema::hasIndex('delivery_orders', 'idx_do_tenant_date')) {
                $table->index(['company_id', 'branch_id', 'tanggal_kirim'], 'idx_do_tenant_date');
            }
            if (!Schema::hasIndex('delivery_orders', 'idx_do_status')) {
                $table->index('status', 'idx_do_status');
            }
            if (!Schema::hasIndex('delivery_orders', 'idx_do_tanggal_kirim')) {
                $table->index('tanggal_kirim', 'idx_do_tanggal_kirim');
            }
            if (!Schema::hasIndex('delivery_orders', 'idx_do_deleted_at')) {
                $table->index('deleted_at', 'idx_do_deleted_at');
            }
            if (!Schema::hasIndex('delivery_orders', 'idx_do_tenant_pagination')) {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'id'], 'idx_do_tenant_pagination');
            }
        });

        Schema::table('delivery_order_details', function (Blueprint $table) {
            if (!Schema::hasIndex('delivery_order_details', 'idx_dod_do_barang')) {
                $table->index(['delivery_order_id', 'barang_id'], 'idx_dod_do_barang');
            }
            if (!Schema::hasIndex('delivery_order_details', 'idx_dod_so_detail')) {
                $table->index('sales_order_detail_id', 'idx_dod_so_detail');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropIndex('idx_do_tenant_status');
            $table->dropIndex('idx_do_tenant_date');
            $table->dropIndex('idx_do_status');
            $table->dropIndex('idx_do_tanggal_kirim');
            $table->dropIndex('idx_do_deleted_at');
            $table->dropIndex('idx_do_tenant_pagination');
        });

        Schema::table('delivery_order_details', function (Blueprint $table) {
            $table->dropIndex('idx_dod_do_barang');
            $table->dropIndex('idx_dod_so_detail');
        });
    }
};
