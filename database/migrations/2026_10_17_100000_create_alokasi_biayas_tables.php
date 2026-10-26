<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alokasi_biayas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('nomor_alokasi', 50)->unique();
            $table->date('tanggal_alokasi');
            $table->unsignedBigInteger('po_biaya_id')->index(); // PO Jasa Pengiriman / Ekspedisi
            $table->decimal('total_biaya', 20, 2)->default(0);
            $table->enum('metode_alokasi', ['Berdasarkan Nilai Barang', 'Berdasarkan Qty'])->default('Berdasarkan Nilai Barang');
            $table->enum('status', ['Draft', 'Posted'])->default('Draft');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('po_biaya_id')->references('id')->on('purchase_orders')->onDelete('restrict');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('alokasi_biaya_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alokasi_biaya_id')->index();
            $table->unsignedBigInteger('gr_id')->index();
            $table->unsignedBigInteger('gr_detail_id')->nullable()->index();
            $table->unsignedBigInteger('barang_id')->index();
            $table->decimal('qty_diterima', 16, 4)->default(0);
            $table->string('satuan_nama', 50)->nullable();
            $table->decimal('harga_beli_satuan', 20, 4)->default(0);
            $table->decimal('nilai_barang', 20, 2)->default(0);
            $table->decimal('porsi_persen', 8, 4)->default(0);
            $table->decimal('biaya_dialokasikan', 20, 2)->default(0);
            $table->decimal('biaya_per_unit', 20, 4)->default(0);
            $table->decimal('hpp_awal', 20, 4)->default(0);
            $table->decimal('hpp_baru', 20, 4)->default(0);
            $table->timestamps();

            $table->foreign('alokasi_biaya_id')->references('id')->on('alokasi_biayas')->onDelete('cascade');
            $table->foreign('gr_id')->references('id')->on('goods_receipts')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('master_barang')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alokasi_biaya_details');
        Schema::dropIfExists('alokasi_biayas');
    }
};
