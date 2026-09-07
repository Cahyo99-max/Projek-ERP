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
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('master_barang')->cascadeOnDelete();
            $table->foreignId('pr_detail_id')->nullable()->constrained('purchase_requisition_details')->nullOnDelete();
            $table->decimal('jumlah', 12, 2);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('diskon_item', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2);
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_details');
    }
};
