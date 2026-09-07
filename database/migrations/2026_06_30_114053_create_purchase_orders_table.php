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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_po', 50)->unique();
            $table->date('tanggal_po');
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('master_suppliers')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('master_currencies')->cascadeOnDelete();
            $table->decimal('kurs', 12, 2)->default(1.00);
            $table->foreignId('pajak_id')->nullable()->constrained('master_pajaks')->nullOnDelete();
            $table->foreignId('pr_id')->nullable()->constrained('purchase_requisitions')->nullOnDelete();
            $table->decimal('diskon_persen', 5, 2)->default(0.00);
            $table->decimal('diskon_nominal', 15, 2)->default(0.00);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total_diskon', 15, 2)->default(0.00);
            $table->decimal('total_pajak', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2);
            $table->decimal('grand_total_idr', 15, 2);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Draft', 'Ordered', 'Closed', 'Cancelled'])->default('Draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
