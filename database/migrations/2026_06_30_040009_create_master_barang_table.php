<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Master Barang — Standar ERP Enterprise
     * Menyimpan data induk barang/produk.
     * QR Code tidak disimpan di database, akan digenerate dari kode_barang saat cetak label.
     */
    public function up(): void
    {
        Schema::create('master_barang', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('kode_barang', 50)->unique()->comment('Kode barang, generated otomatis. QR Code digenerate dari kode ini saat cetak label.');
            $table->string('barcode', 100)->unique()->nullable()->comment('Barcode EAN/UPC (opsional)');
            $table->string('nama_barang', 200)->comment('Nama barang utama');
            $table->string('nama_alias', 200)->nullable()->comment('Nama alias atau nama dagang barang');

            // Foreign Keys ke tabel referensi
            $table->foreignId('kategori_barang_id')->comment('FK ke kategori barang')
                  ->constrained('master_kategori_barang')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('brand_id')->nullable()->comment('FK ke brand/merk barang (opsional)')
                  ->constrained('master_brand')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('tipe_barang_id')->comment('FK ke tipe barang (Bahan Baku, Barang Jadi, dll)')
                  ->constrained('master_tipe_barang')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('satuan_id')->comment('FK ke satuan dasar barang (Kg, Pcs, dll)')
                  ->constrained('master_satuan')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('warna_id')->nullable()->comment('FK ke warna barang (opsional)')
                  ->constrained('master_warna')->onUpdate('cascade')->onDelete('restrict');

            // Spesifikasi Fisik
            $table->text('spesifikasi')->nullable()->comment('Spesifikasi detail barang');
            $table->decimal('berat', 18, 3)->nullable()->comment('Berat barang (gram/kg)');
            $table->decimal('panjang', 18, 2)->nullable()->comment('Panjang barang (cm/m)');
            $table->decimal('lebar', 18, 2)->nullable()->comment('Lebar barang (cm/m)');
            $table->decimal('tinggi', 18, 2)->nullable()->comment('Tinggi barang (cm/m)');

            // Stok Control
            $table->decimal('minimum_stok', 18, 2)->default(0)->comment('Stok minimum sebelum peringatan');
            $table->decimal('maksimum_stok', 18, 2)->nullable()->comment('Stok maksimum kapasitas gudang');
            $table->decimal('reorder_point', 18, 2)->default(0)->comment('Titik pesan ulang otomatis');

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
            $table->index('kode_barang');
            $table->index('nama_barang');
            $table->index('kategori_barang_id');
            $table->index('tipe_barang_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_barang');
    }
};
