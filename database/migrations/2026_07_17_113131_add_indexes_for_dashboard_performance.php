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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['branch_id', 'status', 'tanggal_po'], 'po_dashboard_perf_idx');
        });

        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->index(['branch_id', 'status'], 'pr_dashboard_perf_idx');
        });

        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->index(['gudang_id', 'barang_id', 'stok_aktual'], 'stok_dashboard_perf_idx');
        });

        Schema::table('tenders', function (Blueprint $table) {
            $table->index(['status'], 'tenders_status_perf_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('po_dashboard_perf_idx');
        });

        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropIndex('pr_dashboard_perf_idx');
        });

        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->dropIndex('stok_dashboard_perf_idx');
        });

        Schema::table('tenders', function (Blueprint $table) {
            $table->dropIndex('tenders_status_perf_idx');
        });
    }
};
