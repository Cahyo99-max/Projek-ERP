<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Master Brand — Standar ERP Enterprise
     * Menyimpan data merk/brand barang (Samsung, Toyota, Unilever, dll).
     * Tabel ini diperlukan sebagai referensi FK oleh master_barang.
     */
    public function up(): void
    {
        Schema::create('master_brand', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('kode_brand', 20)->unique()->comment('Kode brand, generated otomatis oleh sistem');
            $table->string('nama_brand', 100)->unique()->comment('Nama brand/merk (unik, tidak boleh duplikat)');
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
            $table->index('kode_brand');
            $table->index('nama_brand');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_brand');
    }
};
