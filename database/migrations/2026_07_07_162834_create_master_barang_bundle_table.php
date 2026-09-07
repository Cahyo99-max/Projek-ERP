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
        Schema::create('master_barang_bundle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_barang_id')->comment('FK ke barang induk bertipe Bundle')
                  ->constrained('master_barang')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('child_barang_id')->comment('FK ke barang penyusun (komponen)')
                  ->constrained('master_barang')->onUpdate('cascade')->onDelete('restrict');
            $table->decimal('qty', 18, 2)->default(1.00)->comment('Jumlah komponen dalam bundle');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_barang_bundle');
    }
};
