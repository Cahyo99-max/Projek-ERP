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
        if (!Schema::hasTable('sales_orders')) {
            Schema::create('sales_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('nomor_so', 30);
                $table->date('tanggal_so');
                $table->foreignId('customer_id')->nullable()->constrained('master_customers')->nullOnDelete();
                $table->string('nama_customer_walkin', 100)->nullable();
                $table->string('telepon_customer_walkin', 30)->nullable();
                $table->enum('pricing_tier', ['Retail', 'Grosir', 'Tender'])->default('Retail');
                $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms')->nullOnDelete();
                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
                $table->enum('status', ['Draft', 'Confirmed', 'Processing', 'Delivered', 'Completed', 'Cancelled'])->default('Confirmed');
                $table->decimal('subtotal', 18, 2)->default(0);
                $table->enum('diskon_tipe', ['Nominal', 'Persen'])->default('Nominal');
                $table->decimal('diskon_nilai', 18, 2)->default(0);
                $table->decimal('diskon_nominal', 18, 2)->default(0);
                $table->enum('pajak_tipe', ['None', 'PPN_Include', 'PPN_Exclude'])->default('None');
                $table->decimal('pajak_persen', 5, 2)->default(0);
                $table->decimal('pajak_nominal', 18, 2)->default(0);
                $table->decimal('total', 18, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->text('alamat_pengiriman')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->foreignId('deleted_by')->nullable()->constrained('users');
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['company_id', 'nomor_so', 'deleted_at'], 'so_company_nomor_unique');
                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'tanggal_so']);
            });
        }

        if (!Schema::hasTable('sales_order_details')) {
            Schema::create('sales_order_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
                $table->foreignId('barang_id')->constrained('master_barang');
                $table->decimal('qty', 12, 4)->default(1);
                $table->foreignId('satuan_id')->nullable()->constrained('master_satuan')->nullOnDelete();
                $table->enum('pricing_tier', ['Retail', 'Grosir', 'Tender'])->default('Retail');
                $table->decimal('harga_satuan', 18, 2)->default(0);
                $table->decimal('diskon_persen', 5, 2)->default(0);
                $table->decimal('diskon_nominal', 18, 2)->default(0);
                $table->decimal('subtotal', 18, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['sales_order_id', 'barang_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_details');
        Schema::dropIfExists('sales_orders');
    }
};
