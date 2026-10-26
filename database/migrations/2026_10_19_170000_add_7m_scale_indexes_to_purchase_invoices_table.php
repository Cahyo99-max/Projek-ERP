<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * High-performance composite indexes designed for 7M+ rows scale.
     */
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            // Index untuk pencarian multi-branch & multi-company beserta status dan sorting ID
            $table->index(['company_id', 'status', 'id'], 'idx_pi_comp_status_id');
            $table->index(['branch_id', 'status', 'id'], 'idx_pi_branch_status_id');
            $table->index(['company_id', 'branch_id', 'status'], 'idx_pi_comp_branch_status');
            
            // Index untuk reminder jatuh tempo dan status pembayaran
            $table->index(['tanggal_jatuh_tempo', 'status'], 'idx_pi_due_status');
            
            // Index untuk pencarian nomor faktur supplier (sering dicari di data table)
            $table->index('nomor_faktur_supplier', 'idx_pi_faktur_supplier');

            // Index untuk creator filter status Draft
            $table->index(['created_by', 'status'], 'idx_pi_creator_status');
        });

        Schema::table('purchase_invoice_details', function (Blueprint $table) {
            // Index untuk jenis detail (Barang, Biaya Lain, PPN)
            $table->index(['purchase_invoice_id', 'jenis'], 'idx_pid_inv_jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_pi_comp_status_id');
            $table->dropIndex('idx_pi_branch_status_id');
            $table->dropIndex('idx_pi_comp_branch_status');
            $table->dropIndex('idx_pi_due_status');
            $table->dropIndex('idx_pi_faktur_supplier');
            $table->dropIndex('idx_pi_creator_status');
        });

        Schema::table('purchase_invoice_details', function (Blueprint $table) {
            $table->dropIndex('idx_pid_inv_jenis');
        });
    }
};
