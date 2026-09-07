<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_payments')) {
            Schema::create('purchase_payments', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_pembayaran', 50)->unique();
                $table->date('tanggal_bayar');
                $table->foreignId('supplier_id')->constrained('master_suppliers')->restrictOnDelete();
                $table->foreignId('kas_bank_id')->constrained('kas_banks')->restrictOnDelete();
                $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
                $table->string('nomor_referensi', 100)->nullable();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
                $table->foreignId('currency_id')->nullable()->constrained('master_currencies')->nullOnDelete();
                $table->decimal('kurs', 15, 4)->default(1);
                $table->decimal('total_faktur', 18, 2)->default(0);
                $table->decimal('total_potongan', 18, 2)->default(0);
                $table->decimal('total_bayar', 18, 2)->default(0);
                $table->enum('status', ['Draft', 'Posted', 'Cancelled'])->default('Draft');
                $table->text('keterangan')->nullable();
                $table->string('lampiran', 255)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('posted_at')->nullable();
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('cancelled_at')->nullable();
                $table->text('alasan_pembatalan')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['branch_id', 'status'], 'idx_pp_branch_status');
                $table->index(['company_id', 'status'], 'idx_pp_company_status');
                $table->index('tanggal_bayar', 'idx_pp_tanggal');
                $table->index('supplier_id', 'idx_pp_supplier');
                $table->index('kas_bank_id', 'idx_pp_kas_bank');
            });
        }

        if (!Schema::hasTable('purchase_payment_details')) {
            Schema::create('purchase_payment_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_payment_id')->constrained('purchase_payments')->cascadeOnDelete();
                $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->restrictOnDelete();
                $table->decimal('total_faktur', 18, 2)->default(0);
                $table->decimal('sisa_utang', 18, 2)->default(0);
                $table->decimal('jumlah_bayar', 18, 2)->default(0);
                $table->decimal('potongan', 18, 2)->default(0);
                $table->decimal('sisa_akhir', 18, 2)->default(0);
                $table->string('catatan', 255)->nullable();
                $table->timestamps();

                $table->index('purchase_payment_id', 'idx_ppd_payment_id');
                $table->index('purchase_invoice_id', 'idx_ppd_invoice_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payment_details');
        Schema::dropIfExists('purchase_payments');
    }
};
