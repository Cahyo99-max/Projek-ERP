<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance Index Migration for SPK Produksi Module
 * 
 * Addresses critical bottlenecks identified in performance audit:
 * 1. spk_produksi_tahapans - missing status index for DataTables default filter
 * 2. spk_produksi_tahapans - missing nama_tahapan index for division filter  
 * 3. spk_produksi_log_harians - missing tanggal index for KPI queries
 * 4. Composite indexes for multi-tenant + date-range queries
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spk_produksi_tahapans', function (Blueprint $table) {
            // Index for status filter (most common DataTables filter: "Menunggu", "Sedang Dikerjakan")
            $table->index(['spk_id', 'status'], 'idx_spktahapan_spk_status');

            // Index for division/nama_tahapan filter
            $table->index(['nama_tahapan', 'status'], 'idx_spktahapan_nama_status');

            // Composite for whereHas spk queries scoped to company
            $table->index(['status', 'updated_at'], 'idx_spktahapan_status_updated');
        });

        Schema::table('spk_produksi_log_harians', function (Blueprint $table) {
            // Index for KPI: selesai_hari_ini (whereDate tanggal = today)
            $table->index(['tanggal'], 'idx_log_tanggal');

            // Composite for laporan harian query (company scoped + date)
            $table->index(['spk_tahapan_id', 'id'], 'idx_log_tahapan_id_desc');
        });

        Schema::table('spk_produksi', function (Blueprint $table) {
            // Index for detailSpk eager loads
            $table->index(['id', 'company_id', 'deleted_at'], 'idx_spk_id_company_soft');
        });
    }

    public function down(): void
    {
        Schema::table('spk_produksi_tahapans', function (Blueprint $table) {
            $table->dropIndex('idx_spktahapan_spk_status');
            $table->dropIndex('idx_spktahapan_nama_status');
            $table->dropIndex('idx_spktahapan_status_updated');
        });

        Schema::table('spk_produksi_log_harians', function (Blueprint $table) {
            $table->dropIndex('idx_log_tanggal');
            $table->dropIndex('idx_log_tahapan_id_desc');
        });

        Schema::table('spk_produksi', function (Blueprint $table) {
            $table->dropIndex('idx_spk_id_company_soft');
        });
    }
};
