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
        Schema::create('master_jenis_biayas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_biaya')->nullable()->unique();
            $table->string('nama_jenis_biaya')->comment('Contoh: Ongkir / Pengiriman, Asuransi, Bongkar Muat, Packing, Administrasi, Materai');
            $table->boolean('masuk_hpp')->default(false)->comment('Apakah biaya ini menambah HPP barang (Landed Cost)');
            $table->enum('alokasi_biaya', ['Berdasarkan Nilai Barang', 'Berdasarkan Qty', 'Tanpa Alokasi'])->default('Berdasarkan Nilai Barang');
            $table->foreignId('coa_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_jenis_biayas');
    }
};
