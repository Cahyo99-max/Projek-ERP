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
            if (!Schema::hasColumn('master_barang', 'harga_hpp')) {
                $table->decimal('harga_hpp', 18, 2)->default(0)->after('spesifikasi');
            }
            if (!Schema::hasColumn('master_barang', 'harga_jual_retail')) {
                $table->decimal('harga_jual_retail', 18, 2)->default(0)->after('harga_hpp');
            }
            if (!Schema::hasColumn('master_barang', 'harga_jual_tender')) {
                $table->decimal('harga_jual_tender', 18, 2)->default(0)->after('harga_jual_retail');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('master_barang', 'harga_hpp')) $columnsToDrop[] = 'harga_hpp';
            if (Schema::hasColumn('master_barang', 'harga_jual_retail')) $columnsToDrop[] = 'harga_jual_retail';
            if (Schema::hasColumn('master_barang', 'harga_jual_tender')) $columnsToDrop[] = 'harga_jual_tender';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
