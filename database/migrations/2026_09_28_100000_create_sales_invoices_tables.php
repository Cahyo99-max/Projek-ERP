<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_faktur', 50)->unique();
            $table->string('nomor_faktur_pajak', 50)->nullable();
            $table->date('tanggal_faktur');
            $table->date('tanggal_jatuh_tempo')->nullable();
            
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->unsignedBigInteger('delivery_order_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->decimal('kurs', 15, 4)->default(1.0000);
            
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('diskon_persen', 5, 2)->default(0.00);
            $table->decimal('diskon_nominal', 15, 2)->default(0.00);
            $table->unsignedBigInteger('pajak_id')->nullable();
            $table->decimal('pajak_nominal', 15, 2)->default(0.00);
            $table->decimal('biaya_lain', 15, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            
            $table->enum('status', ['Draft', 'Unpaid', 'Partial', 'Paid', 'Cancelled'])->default('Draft');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->text('catatan')->nullable();
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->nullOnDelete();
            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('master_customers')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('currency_id')->references('id')->on('master_currencies')->nullOnDelete();
            $table->foreign('pajak_id')->references('id')->on('master_pajaks')->nullOnDelete();
            $table->foreign('payment_term_id')->references('id')->on('payment_terms')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();

            // Index
            $table->index(['branch_id', 'status']);
            $table->index(['customer_id', 'tanggal_faktur']);
        });

        Schema::create('sales_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_invoice_id');
            $table->unsignedBigInteger('barang_id')->nullable();
            $table->string('nama_barang');
            $table->unsignedBigInteger('satuan_id')->nullable();
            $table->decimal('qty', 12, 2)->default(1.00);
            $table->decimal('harga_satuan', 15, 2)->default(0.00);
            $table->decimal('diskon_persen', 5, 2)->default(0.00);
            $table->decimal('diskon_nominal', 15, 2)->default(0.00);
            $table->unsignedBigInteger('ppn_pajak_id')->nullable();
            $table->decimal('tarif_ppn', 5, 2)->default(0.00);
            $table->decimal('nilai_ppn', 15, 2)->default(0.00);
            $table->decimal('total_harga', 15, 2)->default(0.00);
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('master_barang')->nullOnDelete();
            $table->foreign('satuan_id')->references('id')->on('master_satuan')->nullOnDelete();
            $table->foreign('ppn_pajak_id')->references('id')->on('master_pajaks')->nullOnDelete();
        });

        // Add permissions
        $permissions = [
            'lihat-sales-invoice',
            'tambah-sales-invoice',
            'edit-sales-invoice',
            'hapus-sales-invoice',
            'approve-sales-invoice',
            'cetak-sales-invoice',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign to Super Admin if exists
        $adminRole = Role::where('name', 'Super Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_details');
        Schema::dropIfExists('sales_invoices');

        $permissions = [
            'lihat-sales-invoice',
            'tambah-sales-invoice',
            'edit-sales-invoice',
            'hapus-sales-invoice',
            'approve-sales-invoice',
            'cetak-sales-invoice',
        ];

        foreach ($permissions as $perm) {
            $p = Permission::where('name', $perm)->first();
            if ($p) {
                $p->delete();
            }
        }
    }
};
