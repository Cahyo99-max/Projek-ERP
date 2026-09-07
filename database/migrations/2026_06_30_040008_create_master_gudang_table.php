<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Master Gudang — Standar ERP Enterprise
     * Menyimpan data gudang penyimpanan barang per cabang.
     * Mendukung fitur lokasi rak dan stok negatif per gudang.
     */
    public function up(): void
    {
        Schema::create('master_gudang', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('kode_gudang', 20)->unique()->comment('Kode gudang, generated otomatis oleh sistem');
            $table->string('nama_gudang', 100)->comment('Nama gudang');

            // Foreign Keys ke tabel referensi
            $table->foreignId('cabang_id')->comment('FK ke cabang tempat gudang berada')
                  ->constrained('branches')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('jenis_gudang_id')->comment('FK ke jenis/klasifikasi gudang')
                  ->constrained('master_jenis_gudang')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('penanggung_jawab_id')->nullable()->comment('FK ke user penanggung jawab gudang')
                  ->constrained('users')->onUpdate('cascade')->nullOnDelete();

            // Konfigurasi Gudang
            $table->boolean('gunakan_lokasi_rak')->default(false)->comment('Apakah gudang menggunakan sistem lokasi rak');
            $table->boolean('izinkan_stok_negatif')->default(false)->comment('Apakah gudang mengizinkan stok minus');

            // Info Kontak
            $table->text('alamat')->nullable()->comment('Alamat lengkap gudang');
            $table->string('telepon', 20)->nullable()->comment('Nomor telepon gudang');
            $table->string('email', 100)->nullable()->comment('Email gudang');
            $table->text('keterangan')->nullable()->comment('Catatan atau keterangan tambahan');

            $table->enum('status', ['Active', 'Inactive'])->default('Active')->comment('Status aktif/nonaktif');

            // Audit Trail
            $table->foreignId('created_by')->nullable()->comment('User yang membuat data')
                  ->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('User yang mengubah data terakhir')
                  ->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('User yang menghapus data (soft delete)')
                  ->constrained('users')->onUpdate('cascade')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_gudang');
            $table->index('nama_gudang');
            $table->index('cabang_id');
            $table->index('jenis_gudang_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_gudang');
    }
};
