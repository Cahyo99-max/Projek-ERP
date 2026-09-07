<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend fixed_assets table with additional tracking fields:
     * lokasi gudang, departemen, supplier, PIC, kondisi, metode penyusutan, catatan
     */
    public function up(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            // Lokasi & Organisasi
            $table->foreignId('gudang_id')->nullable()->after('gr_detail_id')
                  ->constrained('master_gudang')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('departemen_id')->nullable()->after('gudang_id')
                  ->constrained('departments')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('departemen_id')
                  ->constrained('master_suppliers')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('penanggung_jawab_id')->nullable()->after('supplier_id')
                  ->constrained('users')->onUpdate('cascade')->nullOnDelete();

            // Kondisi & Penyusutan
            $table->enum('kondisi', ['Baik', 'Rusak Ringan', 'Rusak Berat'])
                  ->default('Baik')->after('nilai_buku_saat_ini')
                  ->comment('Kondisi fisik aset');
            $table->enum('metode_penyusutan', ['Garis Lurus', 'Saldo Menurun'])
                  ->default('Garis Lurus')->after('kondisi')
                  ->comment('Metode perhitungan penyusutan');

            // Catatan tambahan
            $table->text('catatan')->nullable()->after('status')
                  ->comment('Catatan/keterangan tambahan per unit aset');

            // Indexes
            $table->index('gudang_id');
            $table->index('departemen_id');
            $table->index('kondisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['gudang_id']);
            $table->dropForeign(['departemen_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['penanggung_jawab_id']);

            // Drop indexes
            $table->dropIndex(['gudang_id']);
            $table->dropIndex(['departemen_id']);
            $table->dropIndex(['kondisi']);

            // Drop columns
            $table->dropColumn([
                'gudang_id',
                'departemen_id',
                'supplier_id',
                'penanggung_jawab_id',
                'kondisi',
                'metode_penyusutan',
                'catatan',
            ]);
        });
    }
};
