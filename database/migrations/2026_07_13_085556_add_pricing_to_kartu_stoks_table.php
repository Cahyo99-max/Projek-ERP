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
        Schema::table('kartu_stoks', function (Blueprint $table) {
            $table->decimal('harga_satuan', 18, 2)->default(0.00)->after('stok_akhir')->comment('Harga per unit barang pada transaksi ini');
            $table->decimal('nilai_total', 18, 2)->default(0.00)->after('harga_satuan')->comment('Total nilai transaksi ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kartu_stoks', function (Blueprint $table) {
            $table->dropColumn(['harga_satuan', 'nilai_total']);
        });
    }
};
