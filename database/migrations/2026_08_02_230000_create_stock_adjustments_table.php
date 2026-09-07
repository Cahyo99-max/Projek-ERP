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
        Schema::dropIfExists('stock_adjustment_details');
        Schema::dropIfExists('stock_adjustments');

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_adjustment', 50)->unique();
            $table->date('tanggal_adjustment');
            $table->unsignedBigInteger('gudang_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            
            $table->enum('tipe_adjustment', ['Pengurangan', 'Penambahan'])->default('Pengurangan');
            $table->enum('kategori_alasan', [
                'Barang Rusak',
                'Barang Hilang',
                'Barang Sampel/Promosi',
                'Internal Use',
                'Koreksi Input'
            ])->default('Barang Rusak');

            $table->text('keterangan')->nullable();
            $table->enum('status', ['Draft', 'Confirmed'])->default('Draft');

            $table->decimal('total_qty', 15, 2)->default(0.00);
            $table->decimal('total_nilai', 15, 4)->default(0.0000);

            $table->unsignedBigInteger('disetujui_oleh')->nullable();
            $table->timestamp('disetujui_pada')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            // Indexes for fast lookup
            $table->index(['company_id', 'branch_id', 'status']);
            $table->index(['gudang_id', 'tanggal_adjustment']);
        });

        Schema::create('stock_adjustment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_adjustment_id');
            $table->unsignedBigInteger('barang_id');
            
            $table->decimal('jumlah', 15, 2)->default(0.00);
            $table->decimal('harga_satuan', 15, 4)->default(0.0000);
            $table->decimal('nilai_total', 15, 4)->default(0.0000);
            $table->string('keterangan_detail', 255)->nullable();

            $table->timestamps();

            $table->index(['stock_adjustment_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_details');
        Schema::dropIfExists('stock_adjustments');
    }
};
