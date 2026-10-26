<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alokasi_biayas', function (Blueprint $table) {
            // Composite index untuk kecepatan query multi-tenancy & pagination skala 7+ juta data
            $table->index(['branch_id', 'status', 'id'], 'idx_alokasi_branch_status_id');
            $table->index(['branch_id', 'deleted_at', 'id'], 'idx_alokasi_branch_del_id');
            $table->index(['company_id', 'status', 'id'], 'idx_alokasi_comp_status_id');
            $table->index(['company_id', 'deleted_at', 'id'], 'idx_alokasi_comp_del_id');
            $table->index(['po_biaya_id', 'status'], 'idx_alokasi_po_status');
            $table->index('tanggal_alokasi', 'idx_alokasi_tanggal');
            $table->index('status', 'idx_alokasi_status');
            $table->index('deleted_at', 'idx_alokasi_deleted_at');
        });

        Schema::table('alokasi_biaya_details', function (Blueprint $table) {
            $table->index(['alokasi_biaya_id', 'barang_id', 'gr_id'], 'idx_alokasi_det_perf');
        });
    }

    public function down(): void
    {
        Schema::table('alokasi_biayas', function (Blueprint $table) {
            $table->dropIndex('idx_alokasi_branch_status_id');
            $table->dropIndex('idx_alokasi_branch_del_id');
            $table->dropIndex('idx_alokasi_comp_status_id');
            $table->dropIndex('idx_alokasi_comp_del_id');
            $table->dropIndex('idx_alokasi_po_status');
            $table->dropIndex('idx_alokasi_tanggal');
            $table->dropIndex('idx_alokasi_status');
            $table->dropIndex('idx_alokasi_deleted_at');
        });

        Schema::table('alokasi_biaya_details', function (Blueprint $table) {
            $table->dropIndex('idx_alokasi_det_perf');
        });
    }
};
