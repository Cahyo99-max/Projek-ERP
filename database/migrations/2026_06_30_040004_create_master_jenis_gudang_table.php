<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Master Jenis Gudang — Standar ERP Enterprise
     * Menyimpan klasifikasi jenis gudang (Gudang Bahan Baku, Gudang Barang Jadi, dll).
     */
    public function up(): void
    {
        Schema::create('master_jenis_gudang', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('kode_jenis_gudang', 20)->unique()->comment('Kode jenis gudang, generated otomatis');
            $table->string('nama_jenis_gudang', 100)->unique()->comment('Nama jenis gudang (unik, tidak boleh duplikat)');
            $table->text('deskripsi')->nullable()->comment('Keterangan tambahan');
            $table->integer('urutan')->nullable()->comment('Urutan tampil');
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
            $table->index('kode_jenis_gudang');
            $table->index('nama_jenis_gudang');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_jenis_gudang');
    }
};
