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
            $table->decimal('harga_pokok_rata_rata', 20, 6)->default(0.000000)->change();
            $table->decimal('hpp_sebelumnya', 20, 6)->default(0.000000)->change();
            $table->decimal('harga_beli_terakhir', 20, 6)->default(0.000000)->change();
            $table->decimal('harga_beli_sebelumnya', 20, 6)->default(0.000000)->change();
        });



        Schema::table('kartu_stoks', function (Blueprint $table) {
            $table->decimal('harga_satuan', 20, 6)->default(0.000000)->change();
            $table->decimal('nilai_total', 20, 4)->default(0.0000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->decimal('harga_pokok_rata_rata', 18, 2)->default(0.00)->change();
            $table->decimal('hpp_sebelumnya', 18, 2)->default(0.00)->change();
            $table->decimal('harga_beli_terakhir', 18, 2)->default(0.00)->change();
            $table->decimal('harga_beli_sebelumnya', 18, 2)->default(0.00)->change();
        });

        Schema::table('master_barang', function (Blueprint $table) {
            if (Schema::hasColumn('master_barang', 'harga_pokok_rata_rata')) {
                $table->decimal('harga_pokok_rata_rata', 18, 2)->default(0.00)->change();
            }
        });

        Schema::table('kartu_stoks', function (Blueprint $table) {
            $table->decimal('harga_satuan', 18, 2)->default(0.00)->change();
            $table->decimal('nilai_total', 18, 2)->default(0.00)->change();
        });
    }
};
