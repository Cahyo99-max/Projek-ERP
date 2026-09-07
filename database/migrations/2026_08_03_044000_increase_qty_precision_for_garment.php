<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mengubah presisi Qty (Quantity) menjadi 4 desimal untuk kebutuhan Industri Garmen/Tekstil.
     * (Misal: pemotongan kain, roll yardage, gramasi kg, benang, dan konsumsi material per pcs).
     */
    public function up(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->decimal('total_qty', 15, 4)->default(0.0000)->change();
        });

        Schema::table('stock_adjustment_details', function (Blueprint $table) {
            $table->decimal('jumlah', 15, 4)->default(0.0000)->change();
        });

        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->decimal('stok_aktual', 15, 4)->default(0.0000)->change();
        });

        Schema::table('kartu_stoks', function (Blueprint $table) {
            $table->decimal('jumlah', 15, 4)->default(0.0000)->change();
            $table->decimal('stok_awal', 15, 4)->default(0.0000)->change();
            $table->decimal('stok_akhir', 15, 4)->default(0.0000)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->decimal('total_qty', 15, 2)->default(0.00)->change();
        });

        Schema::table('stock_adjustment_details', function (Blueprint $table) {
            $table->decimal('jumlah', 15, 2)->default(0.00)->change();
        });

        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->decimal('stok_aktual', 12, 2)->default(0.00)->change();
        });

        Schema::table('kartu_stoks', function (Blueprint $table) {
            $table->decimal('jumlah', 12, 2)->default(0.00)->change();
            $table->decimal('stok_awal', 12, 2)->default(0.00)->change();
            $table->decimal('stok_akhir', 12, 2)->default(0.00)->change();
        });
    }
};
