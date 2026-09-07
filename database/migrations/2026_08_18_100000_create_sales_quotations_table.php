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
        if (!Schema::hasTable('sales_quotations')) {
            Schema::create('sales_quotations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('nomor_quotation', 50);
                $table->unsignedInteger('revisi')->default(0);
                $table->foreignId('parent_quotation_id')->nullable()->constrained('sales_quotations')->nullOnDelete();
                $table->foreignId('customer_id')->constrained('master_customers');
                $table->foreignId('tender_id')->nullable()->constrained('tenders')->nullOnDelete();
                $table->foreignId('sales_id')->nullable()->constrained('karyawans')->nullOnDelete();
                
                $table->date('tanggal_quotation');
                $table->date('berlaku_hingga')->nullable();
                $table->string('perihal', 255)->nullable();
                $table->enum('status', ['Draft', 'Sent', 'Revised', 'Approved', 'Rejected', 'Converted_to_SO'])->default('Draft');
                
                $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms')->nullOnDelete();
                $table->enum('tipe_dp', ['Persen', 'Nominal', 'None'])->default('None');
                $table->decimal('dp_persen', 5, 2)->default(0);
                $table->decimal('dp_nominal', 18, 2)->default(0);
                $table->decimal('sisa_tagihan', 18, 2)->default(0);
                $table->text('catatan_pembayaran')->nullable();
                
                $table->string('currency', 10)->default('IDR');
                $table->decimal('subtotal', 18, 2)->default(0);
                $table->enum('diskon_tipe', ['Nominal', 'Persen'])->default('Nominal');
                $table->decimal('diskon_persen', 5, 2)->default(0);
                $table->decimal('diskon_nominal', 18, 2)->default(0);
                
                $table->foreignId('ppn_pajak_id')->nullable()->constrained('master_pajaks')->nullOnDelete();
                $table->enum('tipe_ppn', ['None', 'Include', 'Exclude'])->default('None');
                $table->decimal('tarif_ppn', 5, 2)->default(0);
                $table->decimal('nilai_ppn', 18, 2)->default(0);
                
                $table->foreignId('pph_pajak_id')->nullable()->constrained('master_pajaks')->nullOnDelete();
                $table->enum('tipe_pph', ['None', 'Include', 'Exclude'])->default('None');
                $table->decimal('tarif_pph', 5, 2)->default(0);
                $table->decimal('nilai_pph', 18, 2)->default(0);
                
                $table->decimal('biaya_pengiriman', 18, 2)->default(0);
                $table->text('catatan_biaya_pengiriman')->nullable();
                $table->decimal('grand_total', 18, 2)->default(0);
                
                $table->decimal('total_hpp', 18, 2)->default(0);
                $table->decimal('estimasi_laba_kotor', 18, 2)->default(0);
                $table->decimal('margin_persen', 5, 2)->default(0);
                
                $table->text('catatan')->nullable();
                $table->text('syarat_ketentuan')->nullable();
                $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
                
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->foreignId('deleted_by')->nullable()->constrained('users');
                $table->timestamps();
                $table->softDeletes();
                
                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'customer_id']);
                $table->index(['company_id', 'tender_id']);
                $table->index(['company_id', 'tanggal_quotation']);
            });
        }

        if (!Schema::hasTable('sales_quotation_items')) {
            Schema::create('sales_quotation_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sales_quotation_id')->constrained('sales_quotations')->onDelete('cascade');
                $table->foreignId('barang_id')->nullable()->constrained('master_barang')->nullOnDelete();
                $table->string('nama_barang_custom', 255)->nullable();
                $table->text('deskripsi_item')->nullable();
                $table->foreignId('kategori_id')->nullable()->constrained('master_kategori_barang')->nullOnDelete();
                $table->foreignId('satuan_id')->nullable()->constrained('master_satuan')->nullOnDelete();
                $table->string('nama_satuan', 50)->nullable();
                
                $table->decimal('qty', 14, 4)->default(1);
                $table->decimal('harga_satuan', 18, 2)->default(0);
                $table->decimal('diskon_persen', 5, 2)->default(0);
                $table->decimal('diskon_nominal', 18, 2)->default(0);
                $table->decimal('subtotal', 18, 2)->default(0);
                
                $table->decimal('hpp_satuan', 18, 2)->default(0);
                $table->decimal('total_hpp', 18, 2)->default(0);
                $table->decimal('estimasi_laba', 18, 2)->default(0);
                $table->decimal('margin_persen', 5, 2)->default(0);
                
                $table->text('catatan')->nullable();
                $table->unsignedInteger('urutan')->default(1);
                $table->timestamps();
                
                $table->index(['sales_quotation_id', 'barang_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_quotation_items');
        Schema::dropIfExists('sales_quotations');
    }
};
