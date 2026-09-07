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
            if (!Schema::hasColumn('stok_gudangs', 'nilai_persediaan')) {
                $table->decimal('nilai_persediaan', 20, 4)->default(0.0000)->after('stok_aktual')->comment('Source of truth total asset value of inventory');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stok_gudangs', function (Blueprint $table) {
            if (Schema::hasColumn('stok_gudangs', 'nilai_persediaan')) {
                $table->dropColumn('nilai_persediaan');
            }
        });

        Schema::table('master_barang', function (Blueprint $table) {
            if (Schema::hasColumn('master_barang', 'nilai_persediaan')) {
                $table->dropColumn('nilai_persediaan');
            }
        });
    }
};
