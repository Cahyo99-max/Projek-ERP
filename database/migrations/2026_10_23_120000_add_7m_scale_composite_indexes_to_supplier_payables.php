<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Composite B-Tree indexes engineered for high-performance query execution at 7M+ rows scale.
     */
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasIndex('purchase_invoices', 'idx_pi_supp_status_del')) {
                $table->index(['supplier_id', 'status', 'deleted_at'], 'idx_pi_supp_status_del');
            }
            if (!Schema::hasIndex('purchase_invoices', 'idx_pi_del_tenant_status')) {
                $table->index(['deleted_at', 'company_id', 'branch_id', 'status', 'supplier_id'], 'idx_pi_del_tenant_status');
            }
        });

        Schema::table('purchase_payment_details', function (Blueprint $table) {
            if (!Schema::hasIndex('purchase_payment_details', 'idx_ppd_covering_calc')) {
                $table->index(['purchase_invoice_id', 'purchase_payment_id', 'jumlah_bayar', 'potongan'], 'idx_ppd_covering_calc');
            }
        });

        Schema::table('purchase_payments', function (Blueprint $table) {
            if (!Schema::hasIndex('purchase_payments', 'idx_pp_supp_status_del')) {
                $table->index(['supplier_id', 'status', 'deleted_at'], 'idx_pp_supp_status_del');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasIndex('purchase_invoices', 'idx_pi_supp_status_del')) {
                $table->dropIndex('idx_pi_supp_status_del');
            }
            if (Schema::hasIndex('purchase_invoices', 'idx_pi_del_tenant_status')) {
                $table->dropIndex('idx_pi_del_tenant_status');
            }
        });

        Schema::table('purchase_payment_details', function (Blueprint $table) {
            if (Schema::hasIndex('purchase_payment_details', 'idx_ppd_covering_calc')) {
                $table->dropIndex('idx_ppd_covering_calc');
            }
        });

        Schema::table('purchase_payments', function (Blueprint $table) {
            if (Schema::hasIndex('purchase_payments', 'idx_pp_supp_status_del')) {
                $table->dropIndex('idx_pp_supp_status_del');
            }
        });
    }
};
