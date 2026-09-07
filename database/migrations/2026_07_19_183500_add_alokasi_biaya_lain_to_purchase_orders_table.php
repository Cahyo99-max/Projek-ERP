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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('alokasi_biaya_lain', 20)->default('Beban')->after('deskripsi_biaya_lain')->comment('Alokasi biaya lain: Beban (Beban Operasional) atau HPP (HPP Persediaan)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('alokasi_biaya_lain');
        });
    }
};
