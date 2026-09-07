<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('master_barang')->cascadeOnDelete();
            $table->foreignId('gudang_id')->constrained('master_gudang')->cascadeOnDelete();
            $table->enum('tipe', ['Masuk', 'Keluar']);
            $table->decimal('jumlah', 12, 2);
            $table->decimal('stok_awal', 12, 2);
            $table->decimal('stok_akhir', 12, 2);
            $table->string('referensi_jenis', 100);
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->string('referensi_nomor', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_stoks');
    }
};
