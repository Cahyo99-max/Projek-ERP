<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Master Warna — Standar ERP Enterprise
     * Menyimpan data warna barang (Merah, Biru, Hitam, Silver, dll).
     */
    public function up(): void
    {
        Schema::create('master_warna', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('kode_warna', 20)->unique()->comment('Kode warna, generated otomatis oleh sistem');
            $table->string('nama_warna', 50)->unique()->comment('Nama warna (unik, tidak boleh duplikat)');
            $table->string('kode_hex', 7)->nullable()->comment('Kode warna HEX opsional (#FF0000)');
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
            $table->index('kode_warna');
            $table->index('nama_warna');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_warna');
    }
};
