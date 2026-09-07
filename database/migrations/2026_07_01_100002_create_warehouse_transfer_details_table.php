<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_transfer_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id');
            $table->unsignedBigInteger('barang_id');
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();

            $table->foreign('transfer_id')->references('id')->on('warehouse_transfers')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('master_barang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_transfer_details');
    }
};
