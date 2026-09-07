<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds extra cost support columns: jenis (Barang/Biaya Lain), keterangan, coa_id, masuk_hpp
     */
    public function up(): void
    {
        Schema::table('purchase_invoice_details', function (Blueprint $table) {
            // Jenis baris: 'Barang' (dari GR detail) atau 'Biaya Lain' (extra cost)
            $table->string('jenis', 30)->default('Barang')->after('purchase_invoice_id');
            // Keterangan baris (e.g. "Ongkos Kirim", "Asuransi")
            $table->string('keterangan', 255)->nullable()->after('jenis');
            // COA untuk baris ini - nullable (barang pakai COA Persediaan, biaya pakai COA Beban/dll)
            $table->unsignedBigInteger('coa_id')->nullable()->after('keterangan');
            // Apakah biaya lain ini masuk ke HPP (nilai persediaan barang)?
            $table->boolean('masuk_hpp')->default(false)->after('coa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoice_details', function (Blueprint $table) {
            $table->dropColumn(['jenis', 'keterangan', 'coa_id', 'masuk_hpp']);
        });
    }
};
