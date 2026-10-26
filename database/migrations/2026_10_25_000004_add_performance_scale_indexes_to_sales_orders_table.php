<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * High-performance composite indexes for 7M+ row scalability on Sales Orders
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // Indeks komposit tenant + status + tanggal untuk query dashboard & analitik cepat
            if (!Schema::hasIndex('sales_orders', 'idx_so_tenant_status_date')) {
                $table->index(['company_id', 'branch_id', 'status', 'tanggal_so'], 'idx_so_tenant_status_date');
            }
            if (!Schema::hasIndex('sales_orders', 'idx_so_branch_status_date')) {
                $table->index(['branch_id', 'status', 'tanggal_so'], 'idx_so_branch_status_date');
            }
            if (!Schema::hasIndex('sales_orders', 'idx_so_tanggal_so')) {
                $table->index('tanggal_so', 'idx_so_tanggal_so');
            }
            if (!Schema::hasIndex('sales_orders', 'idx_so_status')) {
                $table->index('status', 'idx_so_status');
            }
            if (!Schema::hasIndex('sales_orders', 'idx_so_deleted_at')) {
                $table->index('deleted_at', 'idx_so_deleted_at');
            }
            if (!Schema::hasIndex('sales_orders', 'idx_so_tenant_pagination')) {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'id'], 'idx_so_tenant_pagination');
            }
        });

        Schema::table('sales_order_details', function (Blueprint $table) {
            if (!Schema::hasIndex('sales_order_details', 'idx_sod_so_qty')) {
                $table->index(['sales_order_id', 'qty'], 'idx_sod_so_qty');
            }
            if (!Schema::hasIndex('sales_order_details', 'idx_sod_barang_so')) {
                $table->index(['barang_id', 'sales_order_id'], 'idx_sod_barang_so');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex('idx_so_tenant_status_date');
            $table->dropIndex('idx_so_branch_status_date');
            $table->dropIndex('idx_so_tanggal_so');
            $table->dropIndex('idx_so_status');
            $table->dropIndex('idx_so_deleted_at');
            $table->dropIndex('idx_so_tenant_pagination');
        });

        Schema::table('sales_order_details', function (Blueprint $table) {
            $table->dropIndex('idx_sod_so_qty');
            $table->dropIndex('idx_sod_barang_so');
        });
    }
};
