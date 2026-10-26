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
        Schema::table('spk_produksi', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'is_material_closed', 'material_closing_status', 'deleted_at'], 'idx_spk_mat_closing_perf');
        });

        Schema::table('spk_pengeluaran_bahans', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'gudang_id', 'tanggal_pengeluaran', 'deleted_at'], 'idx_spk_pb_filter_perf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_produksi', function (Blueprint $table) {
            $table->dropIndex('idx_spk_mat_closing_perf');
        });

        Schema::table('spk_pengeluaran_bahans', function (Blueprint $table) {
            $table->dropIndex('idx_spk_pb_filter_perf');
        });
    }
};
