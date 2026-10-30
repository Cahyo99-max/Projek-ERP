<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Skala 7 Juta Baris: Indeks komposit performa tinggi untuk mencegah timeout,
     * filesort berat, dan beban scanning CPU pada modul peminjaman barang.
     */
    public function up(): void
    {
        Schema::table('peminjaman_barangs', function (Blueprint $table) {
            if (!Schema::hasIndex('peminjaman_barangs', 'idx_pb_tenant_date_id')) {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'tanggal_pinjam', 'id'], 'idx_pb_tenant_date_id');
            }
            if (!Schema::hasIndex('peminjaman_barangs', 'idx_pb_tenant_status')) {
                $table->index(['company_id', 'branch_id', 'status', 'deleted_at'], 'idx_pb_tenant_status');
            }
            if (!Schema::hasIndex('peminjaman_barangs', 'idx_pb_tenant_asal')) {
                $table->index(['company_id', 'branch_id', 'gudang_asal_id', 'deleted_at'], 'idx_pb_tenant_asal');
            }
            if (!Schema::hasIndex('peminjaman_barangs', 'idx_pb_tanggal_pinjam')) {
                $table->index('tanggal_pinjam', 'idx_pb_tanggal_pinjam');
            }
            if (!Schema::hasIndex('peminjaman_barangs', 'idx_pb_deleted_at')) {
                $table->index('deleted_at', 'idx_pb_deleted_at');
            }
            if (!Schema::hasIndex('peminjaman_barangs', 'idx_pb_tenant_pagination')) {
                $table->index(['company_id', 'branch_id', 'deleted_at', 'id'], 'idx_pb_tenant_pagination');
            }
        });

        Schema::table('peminjaman_barang_details', function (Blueprint $table) {
            if (!Schema::hasIndex('peminjaman_barang_details', 'idx_pbd_peminjaman_barang')) {
                $table->index(['peminjaman_id', 'barang_id'], 'idx_pbd_peminjaman_barang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_barangs', function (Blueprint $table) {
            $table->dropIndex('idx_pb_tenant_date_id');
            $table->dropIndex('idx_pb_tenant_status');
            $table->dropIndex('idx_pb_tenant_asal');
            $table->dropIndex('idx_pb_tanggal_pinjam');
            $table->dropIndex('idx_pb_deleted_at');
            $table->dropIndex('idx_pb_tenant_pagination');
        });

        Schema::table('peminjaman_barang_details', function (Blueprint $table) {
            $table->dropIndex('idx_pbd_peminjaman_barang');
        });
    }
};
