<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Master Satuan — Standar ERP Enterprise
     * Menyimpan data satuan ukuran barang (Kg, Pcs, Liter, Meter, dll).
     */
    public function up(): void
    {
        Schema::create('master_satuan', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('kode_satuan', 20)->unique()->comment('Kode satuan, generated otomatis oleh sistem');
            $table->string('singkatan', 10)->unique()->comment('Singkatan satuan (Kg, Pcs, Ltr, dll)');
            $table->string('nama_satuan', 100)->comment('Nama lengkap satuan (Kilogram, Pieces, Liter)');
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
            $table->index('nama_satuan');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_satuan');
    }
};
