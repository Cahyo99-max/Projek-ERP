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
            // Index for main branch listing sorted by ID desc (default DataTables view)
            $table->index(['branch_id', 'deleted_at', 'id'], 'pr_branch_deleted_id_idx');
            
            // Index for filtering by status
            $table->index(['branch_id', 'deleted_at', 'status'], 'pr_branch_deleted_status_idx');
            
            // Index for filtering by tanggal_pr (sorting & filters)
            $table->index(['branch_id', 'deleted_at', 'tanggal_pr'], 'pr_branch_deleted_tgl_idx');
            
            // Index for role-based employee filter
            $table->index(['branch_id', 'deleted_at', 'diminta_oleh'], 'pr_branch_deleted_requester_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropIndex('pr_branch_deleted_id_idx');
            $table->dropIndex('pr_branch_deleted_status_idx');
            $table->dropIndex('pr_branch_deleted_tgl_idx');
            $table->dropIndex('pr_branch_deleted_requester_idx');
        });
    }
};
