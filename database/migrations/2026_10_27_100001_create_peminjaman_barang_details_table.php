<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_barang_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peminjaman_id')->index();
            $table->unsignedBigInteger('barang_id')->index();
            $table->unsignedBigInteger('satuan_id')->nullable()->index();
            
            $table->decimal('qty_pinjam', 15, 4)->default(1);
            $table->string('serial_number', 100)->nullable();
            $table->string('kondisi_awal', 100)->default('Bagus / Layak');
            
            // Kolom Hasil Pengembalian (Inspeksi QC Gudang)
            $table->decimal('qty_kembali_bagus', 15, 4)->default(0);
            $table->decimal('qty_kembali_rusak', 15, 4)->default(0);
            $table->decimal('qty_hilang', 15, 4)->default(0);
            
            $table->text('kondisi_akhir')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('peminjaman_id')->references('id')->on('peminjaman_barangs')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('master_barang')->onDelete('restrict');
            $table->foreign('satuan_id')->references('id')->on('master_satuan')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_barang_details');
    }
};
