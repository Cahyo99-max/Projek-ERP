<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            if (Schema::hasColumn('master_barang', 'maksimum_stok')) {
                $table->dropColumn('maksimum_stok');
            }
            if (Schema::hasColumn('master_barang', 'reorder_point')) {
                $table->dropColumn('reorder_point');
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->decimal('maksimum_stok', 15, 2)->nullable()->after('minimum_stok');
            $table->decimal('reorder_point', 15, 2)->nullable()->default(0)->after('maksimum_stok');
        });
    }
};
