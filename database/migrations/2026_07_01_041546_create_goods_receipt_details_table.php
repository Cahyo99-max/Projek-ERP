<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gr_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('po_detail_id')->constrained('purchase_order_details')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('master_barang')->cascadeOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_details');
    }
};
