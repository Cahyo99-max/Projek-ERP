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
            // Drop static harga_hpp if it exists
            if (Schema::hasColumn('master_barang', 'harga_hpp')) {
                $table->dropColumn('harga_hpp');
            }

            // Ensure harga_jual_retail exists
            if (!Schema::hasColumn('master_barang', 'harga_jual_retail')) {
                $table->decimal('harga_jual_retail', 18, 2)->default(0)->after('spesifikasi');
            }

            // Add harga_jual_grosir for wholesale / multi-unit grouping
            if (!Schema::hasColumn('master_barang', 'harga_jual_grosir')) {
                $table->decimal('harga_jual_grosir', 18, 2)->default(0)->after('harga_jual_retail');
            }

            // Ensure harga_jual_tender exists
            if (!Schema::hasColumn('master_barang', 'harga_jual_tender')) {
                $table->decimal('harga_jual_tender', 18, 2)->default(0)->after('harga_jual_grosir');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('master_barang', 'harga_hpp')) {
                $table->decimal('harga_hpp', 18, 2)->default(0)->after('spesifikasi');
            }
            if (Schema::hasColumn('master_barang', 'harga_jual_grosir')) {
                $table->dropColumn('harga_jual_grosir');
            }
        });
    }
};
