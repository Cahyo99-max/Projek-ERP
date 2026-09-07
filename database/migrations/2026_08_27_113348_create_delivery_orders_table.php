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
        Schema::dropIfExists('delivery_order_details');
        Schema::dropIfExists('delivery_orders');

        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_do', 50)->unique();
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('master_customers')->nullOnDelete();
            $table->string('nama_penerima')->nullable();
            $table->string('telepon_penerima', 30)->nullable();
            $table->text('alamat_tujuan')->nullable();
            
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('gudang_id')->nullable()->constrained('master_gudang')->nullOnDelete();
            
            $table->date('tanggal_kirim');
            $table->date('estimasi_tiba')->nullable();
            $table->string('metode_pengiriman', 50)->default('Kurir Internal');
            $table->string('nama_ekspedisi')->nullable();
            $table->string('nomor_resi', 100)->nullable();
            $table->string('nama_sopir', 100)->nullable();
            $table->string('plat_nomor', 30)->nullable();
            $table->decimal('biaya_ongkir', 15, 2)->default(0);
            $table->text('catatan_pengiriman')->nullable();
            
            $table->enum('status', ['Draft', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Dibatalkan'])->default('Draft');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('delivery_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->cascadeOnDelete();
            $table->foreignId('sales_order_detail_id')->nullable()->constrained('sales_order_details')->nullOnDelete();
            $table->foreignId('barang_id')->nullable()->constrained('master_barang')->nullOnDelete();
            $table->string('nama_barang_custom')->nullable();
            $table->decimal('qty_order', 12, 2)->default(0);
            $table->decimal('qty_kirim', 12, 2)->default(0);
            $table->string('nama_satuan', 50)->nullable();
            $table->text('catatan_item')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_details');
        Schema::dropIfExists('delivery_orders');
    }
};
