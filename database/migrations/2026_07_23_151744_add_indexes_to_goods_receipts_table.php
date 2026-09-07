<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->index('tanggal_gr', 'idx_gr_tanggal');
            $table->index('status', 'idx_gr_status');
            $table->index(['po_id', 'status'], 'idx_gr_po_status');
        });

        Schema::table('goods_receipt_details', function (Blueprint $table) {
            $table->index('barang_id', 'idx_gr_detail_barang');
            $table->index('po_detail_id', 'idx_gr_detail_po_detail');
        });

        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->index(['barang_id', 'gudang_id'], 'idx_sg_barang_gudang');
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropIndex('idx_gr_tanggal');
            $table->dropIndex('idx_gr_status');
            $table->dropIndex('idx_gr_po_status');
        });

        Schema::table('goods_receipt_details', function (Blueprint $table) {
            $table->dropIndex('idx_gr_detail_barang');
            $table->dropIndex('idx_gr_detail_po_detail');
        });

        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->dropIndex('idx_sg_barang_gudang');
        });
    }
};
