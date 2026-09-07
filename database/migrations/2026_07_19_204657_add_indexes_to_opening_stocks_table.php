<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan index yang hilang di tabel opening_stocks dan opening_stock_details
     * untuk mengatasi full table scan yang menyebabkan halaman hang saat data besar.
     */
    public function up(): void
    {
        // Index untuk query utama di index() controller: WHERE branch_id = ? ORDER BY tanggal_opening_stock DESC
        Schema::table('opening_stocks', function (Blueprint $table) {
            $table->index(['branch_id', 'deleted_at'], 'os_branch_deleted_idx');
            $table->index(['branch_id', 'tanggal_opening_stock', 'deleted_at'], 'os_branch_tanggal_idx');
        });

        // Index untuk JOIN/load di opening_stock_details: WHERE opening_stock_id = ?
        Schema::table('opening_stock_details', function (Blueprint $table) {
            $table->index(['opening_stock_id', 'barang_id'], 'osd_opening_barang_idx');
        });
    }

    public function down(): void
    {
        Schema::table('opening_stocks', function (Blueprint $table) {
            $table->dropIndex('os_branch_deleted_idx');
            $table->dropIndex('os_branch_tanggal_idx');
        });

        Schema::table('opening_stock_details', function (Blueprint $table) {
            $table->dropIndex('osd_opening_barang_idx');
        });
    }
};
