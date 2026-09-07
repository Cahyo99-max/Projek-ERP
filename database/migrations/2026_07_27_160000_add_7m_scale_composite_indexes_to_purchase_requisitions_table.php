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
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            // Composite covering indexes for 7 Million Scale multi-tenant queries
            try {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'id'], 'idx_pr_7m_comp_branch_del_id');
            } catch (\Exception $e) {}

            try {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'status', 'id'], 'idx_pr_7m_comp_branch_del_stat_id');
            } catch (\Exception $e) {}

            try {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'diminta_oleh', 'id'], 'idx_pr_7m_comp_branch_del_req_id');
            } catch (\Exception $e) {}

            try {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'tanggal_pr', 'id'], 'idx_pr_7m_comp_branch_del_tgl_id');
            } catch (\Exception $e) {}
        });

        Schema::table('purchase_requisition_details', function (Blueprint $table) {
            try {
                $table->index(['pr_id', 'id'], 'idx_prd_7m_pr_id');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            try { $table->dropIndex('idx_pr_7m_comp_branch_del_id'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_pr_7m_comp_branch_del_stat_id'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_pr_7m_comp_branch_del_req_id'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_pr_7m_comp_branch_del_tgl_id'); } catch (\Exception $e) {}
        });

        Schema::table('purchase_requisition_details', function (Blueprint $table) {
            try { $table->dropIndex('idx_prd_7m_pr_id'); } catch (\Exception $e) {}
        });
    }
};
