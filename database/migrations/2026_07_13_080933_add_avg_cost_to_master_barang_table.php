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
        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->decimal('harga_pokok_rata_rata', 18, 2)->default(0.00)->after('stok_aktual')->comment('Average cost / HPP rata-rata barang per gudang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->dropColumn('harga_pokok_rata_rata');
        });
    }
};
