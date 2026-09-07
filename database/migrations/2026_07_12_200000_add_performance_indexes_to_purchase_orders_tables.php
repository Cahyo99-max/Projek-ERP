<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah composite indexes pada tabel purchase_orders dan purchase_order_details
     * untuk meningkatkan performa query filter, join, dan subquery aggregation
     * terutama saat data mencapai jutaan baris.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Index untuk filter utama: per branch + status (paling sering dipakai)
            $table->index(['branch_id', 'status'], 'po_branch_status_idx');

            // Index untuk filter per cabang + tanggal (reporting & sorting)
            $table->index(['branch_id', 'tanggal_po'], 'po_branch_tanggal_idx');

            // Index untuk filter created_by (user scope filter)
            $table->index(['branch_id', 'created_by'], 'po_branch_creator_idx');

            // Index untuk relasi PR
            $table->index('pr_id', 'po_pr_id_idx');
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            // Composite index kritis: dipakai di correlated subquery di whereHas PR
            // dan aggregate sum di getPrDetails, store, update, approve
            $table->index(['pr_detail_id', 'jumlah'], 'pod_pr_detail_jumlah_idx');

            // Index untuk filter per PO (dipakai saat delete & update)
            // (po_id sudah ada sebagai FK tapi tambahkan komposit dengan barang_id)
            $table->index(['po_id', 'barang_id'], 'pod_po_barang_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('po_branch_status_idx');
            $table->dropIndex('po_branch_tanggal_idx');
            $table->dropIndex('po_branch_creator_idx');
            $table->dropIndex('po_pr_id_idx');
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropIndex('pod_pr_detail_jumlah_idx');
            $table->dropIndex('pod_po_barang_idx');
        });
    }
};
