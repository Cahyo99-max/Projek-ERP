<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('master_barang')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('gr_detail_id')->nullable()->constrained('goods_receipt_details')->onUpdate('cascade')->onDelete('set null');
            $table->string('kode_aset', 50)->unique();
            $table->string('nomor_seri', 100)->nullable();
            $table->date('tanggal_perolehan');
            $table->decimal('harga_perolehan', 18, 2);
            $table->integer('masa_manfaat')->comment('Masa manfaat dalam satuan bulan');
            $table->decimal('nilai_residu', 18, 2)->default(0);
            $table->decimal('nilai_buku_saat_ini', 18, 2);
            $table->enum('status', ['Aktif', 'Habis Susut', 'Dijual', 'Rusak/Dibuang'])->default('Aktif');
            
            // Audit Trail
            $table->foreignId('created_by')->nullable()->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onUpdate('cascade')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('barang_id');
            $table->index('kode_aset');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
