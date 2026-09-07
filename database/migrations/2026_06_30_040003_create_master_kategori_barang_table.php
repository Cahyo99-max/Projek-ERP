<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Master Kategori Barang — Standar ERP Enterprise
     * Mendukung kategori bertingkat (multi-level hierarchy) tanpa batas level.
     * Jika parent_id NULL = kategori utama, jika terisi = subkategori.
     */
    public function up(): void
    {
        Schema::create('master_kategori_barang', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('kode_kategori', 20)->unique()->comment('Kode kategori, generated otomatis oleh sistem');
            $table->string('nama_kategori', 100)->comment('Nama kategori barang');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('FK ke self-reference. NULL = kategori utama, terisi = subkategori');
            $table->string('deskripsi', 255)->nullable()->comment('Keterangan tambahan');
            $table->integer('urutan')->nullable()->comment('Urutan tampil kategori');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->comment('Status aktif/nonaktif. Nonaktif tidak boleh dipilih di transaksi baru');

            // Audit Trail
            $table->foreignId('created_by')->nullable()->comment('User yang membuat data')
                  ->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->comment('User yang mengubah data terakhir')
                  ->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->comment('User yang menghapus data (soft delete)')
                  ->constrained('users')->onUpdate('cascade')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Self-reference FK (parent_id → master_kategori_barang.id)
            $table->foreign('parent_id')
                  ->references('id')->on('master_kategori_barang')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // Indexes
            $table->index('kode_kategori');
            $table->index('nama_kategori');
            $table->index('parent_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kategori_barang');
    }
};
