<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Selaraskan presisi HPP stock_adjustment_details dengan standar sistem inventori:
     *   - harga_satuan : decimal(15, 6)  ← konsisten dengan stok_gudangs, kartu_stoks
     *   - nilai_total  : decimal(15, 4)  ← tetap 4 (sama dengan kartu_stoks.nilai_total)
     *   - jumlah (qty) : decimal(15, 2)  ← tetap 2 (sama dengan kartu_stoks.jumlah)
     *
     * stock_adjustments header:
     *   - total_nilai  : decimal(15, 4)  ← tetap 4
     *   - total_qty    : decimal(15, 2)  ← tetap 2
     */
    public function up(): void
    {
        Schema::table('stock_adjustment_details', function (Blueprint $table) {
            // HPP naik dari 4 → 6 desimal agar konsisten dengan sistem
            $table->decimal('harga_satuan', 15, 6)->default(0.000000)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustment_details', function (Blueprint $table) {
            $table->decimal('harga_satuan', 15, 4)->default(0.0000)->change();
        });
    }
};
