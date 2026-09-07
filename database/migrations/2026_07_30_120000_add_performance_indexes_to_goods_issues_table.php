<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_issues', function (Blueprint $table) {
            // Index for filtering by company + branch + sorting by date & id
            $table->index(['company_id', 'branch_id', 'tanggal_gi', 'id'], 'idx_gi_comp_branch_tgl_id');
            // Index for filtering by gudang + sorting by date
            $table->index(['gudang_id', 'tanggal_gi', 'id'], 'idx_gi_gudang_tgl_id');
            // Index for filtering by status
            $table->index(['company_id', 'status', 'tanggal_gi'], 'idx_gi_comp_status_tgl');
        });

        Schema::table('goods_issue_details', function (Blueprint $table) {
            // Index for fast detail lookup by gi_id and barang_id
            $table->index(['gi_id', 'barang_id'], 'idx_gid_gi_barang');
        });
    }

    public function down(): void
    {
        Schema::table('goods_issues', function (Blueprint $table) {
            $table->dropIndex('idx_gi_comp_branch_tgl_id');
            $table->dropIndex('idx_gi_gudang_tgl_id');
            $table->dropIndex('idx_gi_comp_status_tgl');
        });

        Schema::table('goods_issue_details', function (Blueprint $table) {
            $table->dropIndex('idx_gid_gi_barang');
        });
    }
};
