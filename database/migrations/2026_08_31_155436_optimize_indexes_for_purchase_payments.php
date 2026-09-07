<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan composite B-Tree indexes untuk performa tinggi pada skala jutaan baris data.
     */
    public function up(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            // Index komposit untuk query pagination ORDER BY id DESC dengan filter branch/company & deleted_at
            $table->index(['deleted_at', 'branch_id', 'id'], 'idx_pp_del_branch_id');
            $table->index(['deleted_at', 'company_id', 'id'], 'idx_pp_del_comp_id');
            $table->index(['deleted_at', 'status', 'id'], 'idx_pp_del_status_id');
            $table->index(['deleted_at', 'updated_at'], 'idx_pp_del_updated');
        });

        Schema::table('purchase_payment_details', function (Blueprint $table) {
            $table->index(['purchase_payment_id', 'purchase_invoice_id'], 'idx_ppd_pay_inv');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropIndex('idx_pp_del_branch_id');
            $table->dropIndex('idx_pp_del_comp_id');
            $table->dropIndex('idx_pp_del_status_id');
            $table->dropIndex('idx_pp_del_updated');
        });

        Schema::table('purchase_payment_details', function (Blueprint $table) {
            $table->dropIndex('idx_ppd_pay_inv');
        });
    }
};
