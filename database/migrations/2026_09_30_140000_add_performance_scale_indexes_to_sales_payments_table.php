<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengoptimalkan performa modul Penerimaan Pembayaran untuk skala jutaan data (hingga 7+ juta baris).
     */
    public function up(): void
    {
        Schema::table('sales_payments', function (Blueprint $table) {
            // Indeks komposit tenant, status, dan tanggal bayar untuk query utama DataTables & KPI
            if (!Schema::hasIndex('sales_payments', 'idx_sp_tenant_status_date')) {
                $table->index(['company_id', 'branch_id', 'status', 'tanggal_bayar'], 'idx_sp_tenant_status_date');
            }
            if (!Schema::hasIndex('sales_payments', 'idx_sp_branch_status_date')) {
                $table->index(['branch_id', 'status', 'tanggal_bayar'], 'idx_sp_branch_status_date');
            }
            // Indeks pagination cepat berdasarkan tenant & ID
            if (!Schema::hasIndex('sales_payments', 'idx_sp_tenant_pagination')) {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'id'], 'idx_sp_tenant_pagination');
            }
            // Indeks filter customer & kas bank berurutan tanggal
            if (!Schema::hasIndex('sales_payments', 'idx_sp_cust_date')) {
                $table->index(['customer_id', 'tanggal_bayar'], 'idx_sp_cust_date');
            }
            if (!Schema::hasIndex('sales_payments', 'idx_sp_kas_date')) {
                $table->index(['kas_bank_id', 'tanggal_bayar'], 'idx_sp_kas_date');
            }
            if (!Schema::hasIndex('sales_payments', 'idx_sp_deleted_at')) {
                $table->index('deleted_at', 'idx_sp_deleted_at');
            }
            // Indeks untuk live sync fingerprint (MAX updated_at & id)
            if (!Schema::hasIndex('sales_payments', 'idx_sp_sync_lookup')) {
                $table->index(['branch_id', 'company_id', 'updated_at', 'id'], 'idx_sp_sync_lookup');
            }
        });

        Schema::table('sales_payment_details', function (Blueprint $table) {
            if (!Schema::hasIndex('sales_payment_details', 'idx_spd_payment_inv')) {
                $table->index(['sales_payment_id', 'sales_invoice_id'], 'idx_spd_payment_inv');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_payments', function (Blueprint $table) {
            $table->dropIndex('idx_sp_tenant_status_date');
            $table->dropIndex('idx_sp_branch_status_date');
            $table->dropIndex('idx_sp_tenant_pagination');
            $table->dropIndex('idx_sp_cust_date');
            $table->dropIndex('idx_sp_kas_date');
            $table->dropIndex('idx_sp_deleted_at');
            $table->dropIndex('idx_sp_sync_lookup');
        });

        Schema::table('sales_payment_details', function (Blueprint $table) {
            $table->dropIndex('idx_spd_payment_inv');
        });
    }
};
