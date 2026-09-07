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
        Schema::table('master_kategori_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('master_kategori_barang', 'tampil_di_bundle')) {
                $table->boolean('tampil_di_bundle')->default(true)->after('status')->comment('1 = Tampil saat memilih komponen di Master Bundle, 0 = Sembunyikan');
            }
            if (!Schema::hasColumn('master_kategori_barang', 'tampil_di_sales')) {
                $table->boolean('tampil_di_sales')->default(true)->after('tampil_di_bundle')->comment('1 = Tampil di katalog Penjualan / POS / SO, 0 = Sembunyikan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_kategori_barang', function (Blueprint $table) {
            $table->dropColumn(['tampil_di_bundle', 'tampil_di_sales']);
        });
    }
};
