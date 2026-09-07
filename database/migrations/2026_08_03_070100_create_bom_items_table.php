<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->onDelete('cascade');
            $table->foreignId('barang_mentah_id')->constrained('master_barang')->onDelete('cascade');
            $table->decimal('jumlah_kebutuhan', 15, 4)->default(0.0000);
            $table->foreignId('satuan_id')->constrained('master_satuan')->onDelete('restrict');
            $table->decimal('waste_percent', 5, 2)->default(0.00);
            $table->decimal('estimasi_harga_satuan', 15, 6)->default(0.000000);
            $table->decimal('estimasi_subtotal', 15, 4)->default(0.0000);
            $table->string('catatan', 255)->nullable();
            $table->timestamps();

            $table->index(['bom_id', 'barang_mentah_id'], 'idx_bom_items_bom_barang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
