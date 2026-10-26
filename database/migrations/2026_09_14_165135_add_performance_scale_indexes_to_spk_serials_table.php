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
        Schema::table('spk_penerimaan_barang_jadi_serials', function (Blueprint $table) {
            // Index untuk filter status dan komposit pencarian berkecepatan tinggi
            if (!Schema::hasIndex('spk_penerimaan_barang_jadi_serials', 'idx_pbj_serials_status')) {
                $table->index('status', 'idx_pbj_serials_status');
            }
            if (!Schema::hasIndex('spk_penerimaan_barang_jadi_serials', 'idx_pbj_serials_status_penerimaan')) {
                $table->index(['status', 'penerimaan_id'], 'idx_pbj_serials_status_penerimaan');
            }
            if (!Schema::hasIndex('spk_penerimaan_barang_jadi_serials', 'idx_pbj_serials_spk_status')) {
                $table->index(['spk_id', 'status'], 'idx_pbj_serials_spk_status');
            }
            if (!Schema::hasIndex('spk_penerimaan_barang_jadi_serials', 'idx_pbj_serials_sn_status')) {
                $table->index(['serial_number', 'status'], 'idx_pbj_serials_sn_status');
            }
            if (!Schema::hasIndex('spk_penerimaan_barang_jadi_serials', 'idx_pbj_serials_batch_status')) {
                $table->index(['nomor_batch', 'status'], 'idx_pbj_serials_batch_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_penerimaan_barang_jadi_serials', function (Blueprint $table) {
            $table->dropIndex('idx_pbj_serials_status');
            $table->dropIndex('idx_pbj_serials_status_penerimaan');
            $table->dropIndex('idx_pbj_serials_spk_status');
            $table->dropIndex('idx_pbj_serials_sn_status');
            $table->dropIndex('idx_pbj_serials_batch_status');
        });
    }
};
