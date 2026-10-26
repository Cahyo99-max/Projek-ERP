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
        Schema::table('sales_invoices', function (Blueprint $table) {
            // Indeks isolasi tenant, status & tanggal faktur untuk query utama DataTables & KPI
            if (!Schema::hasIndex('sales_invoices', 'idx_si_tenant_status_date')) {
                $table->index(['company_id', 'branch_id', 'status', 'tanggal_faktur'], 'idx_si_tenant_status_date');
            }
            if (!Schema::hasIndex('sales_invoices', 'idx_si_tanggal_faktur')) {
                $table->index('tanggal_faktur', 'idx_si_tanggal_faktur');
            }
            if (!Schema::hasIndex('sales_invoices', 'idx_si_nomor_pajak')) {
                $table->index('nomor_faktur_pajak', 'idx_si_nomor_pajak');
            }
            if (!Schema::hasIndex('sales_invoices', 'idx_si_status')) {
                $table->index('status', 'idx_si_status');
            }
            if (!Schema::hasIndex('sales_invoices', 'idx_si_due_date')) {
                $table->index('tanggal_jatuh_tempo', 'idx_si_due_date');
            }
            if (!Schema::hasIndex('sales_invoices', 'idx_si_deleted_at')) {
                $table->index('deleted_at', 'idx_si_deleted_at');
            }
            if (!Schema::hasIndex('sales_invoices', 'idx_si_tenant_pagination')) {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'id'], 'idx_si_tenant_pagination');
            }
        });

        Schema::table('sales_invoice_details', function (Blueprint $table) {
            if (!Schema::hasIndex('sales_invoice_details', 'idx_sid_inv_barang')) {
                $table->index(['sales_invoice_id', 'barang_id'], 'idx_sid_inv_barang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_si_tenant_status_date');
            $table->dropIndex('idx_si_tanggal_faktur');
            $table->dropIndex('idx_si_nomor_pajak');
            $table->dropIndex('idx_si_status');
            $table->dropIndex('idx_si_due_date');
            $table->dropIndex('idx_si_deleted_at');
            $table->dropIndex('idx_si_tenant_pagination');
        });

        Schema::table('sales_invoice_details', function (Blueprint $table) {
            $table->dropIndex('idx_sid_inv_barang');
        });
    }
};
