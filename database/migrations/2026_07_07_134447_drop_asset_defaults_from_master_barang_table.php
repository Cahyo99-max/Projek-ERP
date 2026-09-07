<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop default asset configuration columns from master_barang.
     *
     * These fields (default_masa_manfaat, default_nilai_residu,
     * default_metode_penyusutan, default_kondisi, default_gudang_id)
     * are no longer needed in master_barang. All asset-level details
     * (masa manfaat, nilai residu, metode penyusutan, kondisi, lokasi)
     * are now managed directly in the fixed_assets table at registration time.
     */
    public function up(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            // Drop foreign key first before dropping the column
            if (Schema::hasColumn('master_barang', 'default_gudang_id')) {
                $table->dropForeign(['default_gudang_id']);
                $table->dropColumn('default_gudang_id');
            }
            if (Schema::hasColumn('master_barang', 'default_metode_penyusutan')) {
                $table->dropColumn('default_metode_penyusutan');
            }
            if (Schema::hasColumn('master_barang', 'default_kondisi')) {
                $table->dropColumn('default_kondisi');
            }
            if (Schema::hasColumn('master_barang', 'default_masa_manfaat')) {
                $table->dropColumn('default_masa_manfaat');
            }
            if (Schema::hasColumn('master_barang', 'default_nilai_residu')) {
                $table->dropColumn('default_nilai_residu');
            }
        });
    }

    /**
     * Reverse — re-add columns if rolled back.
     */
    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->integer('default_masa_manfaat')->nullable()->comment('Default masa manfaat aset tetap (bulan)');
            $table->decimal('default_nilai_residu', 18, 2)->default(0)->comment('Default nilai sisa/residu aset tetap');
            $table->enum('default_metode_penyusutan', ['Garis Lurus', 'Saldo Menurun'])
                  ->default('Garis Lurus')->nullable()
                  ->comment('Default metode penyusutan untuk aset tetap');
            $table->enum('default_kondisi', ['Baik', 'Rusak Ringan', 'Rusak Berat'])
                  ->default('Baik')->nullable()
                  ->comment('Default kondisi awal aset');
            $table->foreignId('default_gudang_id')->nullable()
                  ->constrained('master_gudang')->onUpdate('cascade')->nullOnDelete()
                  ->comment('Default gudang/lokasi penempatan aset');
        });
    }
};
