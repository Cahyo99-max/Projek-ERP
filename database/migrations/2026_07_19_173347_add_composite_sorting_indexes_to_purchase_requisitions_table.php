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
            // Drop old non-covering indexes to avoid redundancy
            try {
                $table->dropIndex('pr_branch_deleted_status_idx');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('pr_branch_deleted_requester_idx');
            } catch (\Exception $e) {}

            // Create new composite covering indexes that include id to allow fast ORDER BY id sorting without filesort
            $table->index(['branch_id', 'deleted_at', 'status', 'id'], 'pr_branch_status_id_idx');
            $table->index(['branch_id', 'deleted_at', 'diminta_oleh', 'id'], 'pr_branch_requester_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            try {
                $table->dropIndex('pr_branch_status_id_idx');
            } catch (\Exception $e) {}
            try {
                $table->dropIndex('pr_branch_requester_id_idx');
            } catch (\Exception $e) {}

            // Restore old indexes
            $table->index(['branch_id', 'deleted_at', 'status'], 'pr_branch_deleted_status_idx');
            $table->index(['branch_id', 'deleted_at', 'diminta_oleh'], 'pr_branch_deleted_requester_idx');
        });
    }
};
