<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel Master Barang Foto - Menyimpan beberapa foto per barang.
     */
    public function up(): void
    {
        Schema::create('master_barang_foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')
                  ->constrained('master_barang')
                  ->cascadeOnDelete();
            
            $table->string('foto_path', 255)->comment('Path gambar di storage');
            $table->boolean('is_primary')->default(false)->comment('Apakah foto utama');
            $table->integer('urutan')->default(0)->comment('Urutan tampil foto');
            
            $table->timestamps();

            // Indexes
            $table->index('barang_id');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_barang_foto');
    }
};
