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
        Schema::table('master_barang', function (Blueprint $table) {
            $table->enum('tipe_item', ['Stok', 'Aset', 'Jasa'])->default('Stok')->after('nama_alias')->comment('Kategori barang: Stok, Aset Tetap, atau Jasa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->dropColumn(['tipe_item']);
        });
    }
};
