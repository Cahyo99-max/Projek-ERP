<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->index('nomor_surat_jalan', 'idx_gr_surat_jalan');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['branch_id', 'status'], 'idx_po_branch_status');
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropIndex('idx_gr_surat_jalan');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('idx_po_branch_status');
        });
    }
};
