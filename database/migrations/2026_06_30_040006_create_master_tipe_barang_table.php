<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Master Tipe Barang — Standar ERP Enterprise
     * Menyimpan klasifikasi tipe barang (Bahan Baku, Barang Jadi, Barang Setengah Jadi,
     * Barang Konsumsi, Suku Cadang, Aset, dll).
     */
    public function up(): void
    {
        Schema::create('master_tipe_barang', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('kode_tipe', 20)->unique()->comment('Kode tipe barang, generated otomatis oleh sistem');
            $table->string('nama_tipe', 100)->unique()->comment('Nama tipe barang (unik, tidak boleh duplikat)');
            $table->string('deskripsi', 255)->nullable()->comment('Keterangan tambahan');
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
            $table->index('kode_tipe');
            $table->index('nama_tipe');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tipe_barang');
    }
};
