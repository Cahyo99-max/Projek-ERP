<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_barangs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('nomor_peminjaman', 50)->unique();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali_rencana');
            $table->dateTime('tanggal_kembali_aktual')->nullable();
            
            // Info Acara / Pameran
            $table->string('nama_acara', 255);
            $table->string('lokasi_acara', 255)->nullable();
            $table->string('penanggung_jawab', 100);
            $table->string('kontak_pj', 50)->nullable();
            
            // Gudang Asal & Gudang Virtual Pameran
            $table->unsignedBigInteger('gudang_asal_id')->index();
            $table->unsignedBigInteger('gudang_pameran_id')->index();
            
            // Status Workflow: Draft, Menunggu, Barang Disiapkan, Barang Siap, Sedang Dipinjam, Selesai, Selesai (Ada Masalah), Ditolak
            $table->string('status', 30)->default('Draft')->index();
            $table->text('keterangan')->nullable();

            // Aktor 1: Pengaju
            $table->unsignedBigInteger('diajukan_oleh')->nullable()->index();
            $table->dateTime('diajukan_at')->nullable();
            $table->longText('tanda_tangan_pengaju')->nullable();

            // Aktor 2: Mengetahui (Manager / Atasan)
            $table->unsignedBigInteger('diketahui_oleh')->nullable()->index();
            $table->dateTime('diketahui_at')->nullable();
            $table->longText('tanda_tangan_mengetahui')->nullable();
            $table->text('catatan_mengetahui')->nullable();

            // Aktor 3: Menyiapkan (Tim Gudang)
            $table->unsignedBigInteger('disiapkan_oleh')->nullable()->index();
            $table->dateTime('disiapkan_at')->nullable();
            $table->longText('tanda_tangan_menyiapkan')->nullable();
            $table->dateTime('diserahkan_at')->nullable();

            // Pengembalian (Tim Gudang / Penerima Kembali)
            $table->unsignedBigInteger('diterima_kembali_oleh')->nullable()->index();
            $table->dateTime('diterima_kembali_at')->nullable();
            $table->longText('tanda_tangan_penerima')->nullable();
            $table->text('catatan_pengembalian')->nullable();

            // Audit Trail
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('gudang_asal_id')->references('id')->on('master_gudang')->onDelete('restrict');
            $table->foreign('gudang_pameran_id')->references('id')->on('master_gudang')->onDelete('restrict');
            $table->foreign('diajukan_oleh')->references('id')->on('users')->onDelete('set null');
            $table->foreign('diketahui_oleh')->references('id')->on('users')->onDelete('set null');
            $table->foreign('disiapkan_oleh')->references('id')->on('users')->onDelete('set null');
            $table->foreign('diterima_kembali_oleh')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_barangs');
    }
};
