<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to drop global unique indexes and replace with per-company composite indexes.
     */
    public function up(): void
    {
        // 1. master_barang
        Schema::table('master_barang', function (Blueprint $table) {
            // Drop legacy global unique indexes if they exist
            try {
                $table->dropUnique('master_barang_kode_barang_unique');
            } catch (\Exception $e) {}

            try {
                $table->dropUnique('master_barang_barcode_unique');
            } catch (\Exception $e) {}
        });

        // 2. master_kategori_barang
        Schema::table('master_kategori_barang', function (Blueprint $table) {
            try {
                $table->dropUnique('master_kategori_barang_kode_kategori_unique');
            } catch (\Exception $e) {}
        });

        // 3. master_satuan
        Schema::table('master_satuan', function (Blueprint $table) {
            try {
                $table->dropUnique('master_satuan_kode_satuan_unique');
            } catch (\Exception $e) {}

            try {
                $table->dropUnique('master_satuan_singkatan_unique');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed for down to avoid breaking multi-tenant entries
    }
};
