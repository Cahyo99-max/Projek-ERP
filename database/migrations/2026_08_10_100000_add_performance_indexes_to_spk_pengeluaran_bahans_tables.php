<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Index Migration for SPK Pengeluaran Bahan Module
 *
 * Addresses bottlenecks at 7M+ record scale:
 * 1. spk_pengeluaran_bahans       — composite index spk+company+deleted_at (main query filter)
 * 2. spk_pengeluaran_bahans       — tanggal + company (filter laporan tanggal)
 * 3. spk_pengeluaran_bahan_details — spk_material_id + pengeluaran_id (N+1 fix — GROUP BY SUM)
 * 4. spk_pengeluaran_bahan_details — barang_mentah_id (lookup by material)
 * 5. spk_pengeluaran_bahan_details — pengeluaran_id   (cascade delete + detail load)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spk_pengeluaran_bahans', function (Blueprint $table) {
            // Primary filter: semua query yang filter by spk_id (paling sering dipakai)
            $table->index(['spk_id', 'deleted_at'], 'idx_pb_spk_soft');

            // Multi-tenant: filter by company + spk
            $table->index(['company_id', 'spk_id', 'deleted_at'], 'idx_pb_company_spk_soft');

            // Filter laporan by tanggal range scoped per company
            $table->index(['tanggal_pengeluaran', 'company_id', 'deleted_at'], 'idx_pb_tanggal_company');

            // Paginasi riwayat: order by id DESC + filter spk_id
            $table->index(['spk_id', 'id', 'deleted_at'], 'idx_pb_spk_id_paginate');
        });

        Schema::table('spk_pengeluaran_bahan_details', function (Blueprint $table) {
            // Kunci utama fix N+1: GROUP BY spk_material_id + SUM(qty_diambil_sekarang)
            // Query: WHERE spk_material_id IN (...) GROUP BY spk_material_id
            $table->index(['spk_material_id'], 'idx_pb_detail_material');

            // Join / load details by pengeluaran_id (eager load HasMany)
            $table->index(['pengeluaran_id'], 'idx_pb_detail_pengeluaran');

            // Composite: filter aktif (join ke parent yg tidak soft-deleted)
            $table->index(['spk_material_id', 'pengeluaran_id'], 'idx_pb_detail_mat_pengeluaran');

            // Lookup by barang (untuk laporan per bahan)
            $table->index(['barang_mentah_id'], 'idx_pb_detail_barang');
        });
    }

    public function down(): void
    {
        Schema::table('spk_pengeluaran_bahans', function (Blueprint $table) {
            $table->dropIndex('idx_pb_spk_soft');
            $table->dropIndex('idx_pb_company_spk_soft');
            $table->dropIndex('idx_pb_tanggal_company');
            $table->dropIndex('idx_pb_spk_id_paginate');
        });

        Schema::table('spk_pengeluaran_bahan_details', function (Blueprint $table) {
            $table->dropIndex('idx_pb_detail_material');
            $table->dropIndex('idx_pb_detail_pengeluaran');
            $table->dropIndex('idx_pb_detail_mat_pengeluaran');
            $table->dropIndex('idx_pb_detail_barang');
        });
    }
};
