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
            $columnsToDrop = [];
            if (Schema::hasColumn('master_barang', 'harga_pokok_rata_rata')) {
                $columnsToDrop[] = 'harga_pokok_rata_rata';
            }
            if (Schema::hasColumn('master_barang', 'nilai_persediaan')) {
                $columnsToDrop[] = 'nilai_persediaan';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('master_barang', 'harga_pokok_rata_rata')) {
                $table->decimal('harga_pokok_rata_rata', 18, 6)->default(0)->after('status');
            }
            if (!Schema::hasColumn('master_barang', 'nilai_persediaan')) {
                $table->decimal('nilai_persediaan', 18, 4)->default(0)->after('harga_pokok_rata_rata');
            }
        });
    }
};
