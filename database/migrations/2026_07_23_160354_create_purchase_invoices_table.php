<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_invoices')) {
            Schema::create('purchase_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_faktur', 50)->unique();
                $table->string('nomor_faktur_supplier', 100)->nullable();
                $table->date('tanggal_faktur');
                $table->date('tanggal_jatuh_tempo')->nullable();
                $table->foreignId('po_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                $table->foreignId('gr_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
                $table->foreignId('supplier_id')->constrained('master_suppliers')->restrictOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('currency_id')->nullable()->constrained('master_currencies')->nullOnDelete();
                $table->decimal('kurs', 15, 4)->default(1);
                $table->decimal('subtotal', 18, 2)->default(0);
                $table->decimal('diskon_persen', 5, 2)->default(0);
                $table->decimal('diskon_nominal', 18, 2)->default(0);
                $table->foreignId('pajak_id')->nullable()->constrained('master_pajaks')->nullOnDelete();
                $table->decimal('pajak_nominal', 18, 2)->default(0);
                $table->decimal('biaya_lain', 18, 2)->default(0);
                $table->decimal('grand_total', 18, 2)->default(0);
                $table->enum('status', ['Draft', 'Posted', 'Paid', 'Cancelled'])->default('Draft');
                $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms')->nullOnDelete();
                $table->text('keterangan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['branch_id', 'status'], 'idx_pi_branch_status');
                $table->index('tanggal_faktur', 'idx_pi_tanggal');
                $table->index('supplier_id', 'idx_pi_supplier');
            });
        }

        if (!Schema::hasTable('purchase_invoice_details')) {
            Schema::create('purchase_invoice_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->cascadeOnDelete();
                $table->foreignId('gr_detail_id')->nullable()->constrained('goods_receipt_details')->nullOnDelete();
                $table->foreignId('barang_id')->constrained('master_barang')->restrictOnDelete();
                $table->decimal('jumlah', 15, 4)->default(0);
                $table->decimal('harga_satuan', 18, 2)->default(0);
                $table->decimal('subtotal', 18, 2)->default(0);
                $table->timestamps();

                $table->index('purchase_invoice_id', 'idx_pid_invoice_id');
                $table->index('barang_id', 'idx_pid_barang_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_details');
        Schema::dropIfExists('purchase_invoices');
    }
};
