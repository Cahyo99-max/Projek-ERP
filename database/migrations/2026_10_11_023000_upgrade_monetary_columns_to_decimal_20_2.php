<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Upgrade sales_invoices
        if (Schema::hasTable('sales_invoices')) {
            DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN subtotal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN diskon_nominal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN pajak_nominal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            if (Schema::hasColumn('sales_invoices', 'pph_nominal')) {
                DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN pph_nominal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            }
            DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN biaya_lain DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN grand_total DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            if (Schema::hasColumn('sales_invoices', 'uang_muka')) {
                DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN uang_muka DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            }
            if (Schema::hasColumn('sales_invoices', 'sisa_tagihan')) {
                DB::statement("ALTER TABLE sales_invoices MODIFY COLUMN sisa_tagihan DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            }
        }

        // 2. Upgrade sales_invoice_items
        if (Schema::hasTable('sales_invoice_items')) {
            DB::statement("ALTER TABLE sales_invoice_items MODIFY COLUMN harga_satuan DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_invoice_items MODIFY COLUMN diskon_nominal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_invoice_items MODIFY COLUMN nilai_ppn DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_invoice_items MODIFY COLUMN total_harga DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }

        // 3. Upgrade sales_invoice_costs
        if (Schema::hasTable('sales_invoice_costs')) {
            DB::statement("ALTER TABLE sales_invoice_costs MODIFY COLUMN nominal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }

        // 4. Upgrade purchase_invoices
        if (Schema::hasTable('purchase_invoices')) {
            DB::statement("ALTER TABLE purchase_invoices MODIFY COLUMN subtotal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_invoices MODIFY COLUMN diskon_nominal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_invoices MODIFY COLUMN pajak_nominal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            if (Schema::hasColumn('purchase_invoices', 'pph_nominal')) {
                DB::statement("ALTER TABLE purchase_invoices MODIFY COLUMN pph_nominal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            }
            DB::statement("ALTER TABLE purchase_invoices MODIFY COLUMN biaya_lain DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_invoices MODIFY COLUMN grand_total DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            if (Schema::hasColumn('purchase_invoices', 'uang_muka')) {
                DB::statement("ALTER TABLE purchase_invoices MODIFY COLUMN uang_muka DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            }
            if (Schema::hasColumn('purchase_invoices', 'sisa_tagihan')) {
                DB::statement("ALTER TABLE purchase_invoices MODIFY COLUMN sisa_tagihan DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            }
        }

        // 5. Upgrade purchase_invoice_items
        if (Schema::hasTable('purchase_invoice_items')) {
            DB::statement("ALTER TABLE purchase_invoice_items MODIFY COLUMN harga_satuan DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_invoice_items MODIFY COLUMN diskon_nominal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_invoice_items MODIFY COLUMN nilai_ppn DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_invoice_items MODIFY COLUMN total_harga DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }

        // 6. Upgrade kas_banks & kas_mutasis
        if (Schema::hasTable('kas_banks')) {
            DB::statement("ALTER TABLE kas_banks MODIFY COLUMN saldo_awal DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE kas_banks MODIFY COLUMN saldo_berjalan DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }
        if (Schema::hasTable('kas_mutasis')) {
            if (Schema::hasColumn('kas_mutasis', 'jumlah')) {
                DB::statement("ALTER TABLE kas_mutasis MODIFY COLUMN jumlah DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            }
            if (Schema::hasColumn('kas_mutasis', 'saldo_sebelum')) {
                DB::statement("ALTER TABLE kas_mutasis MODIFY COLUMN saldo_sebelum DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            }
            if (Schema::hasColumn('kas_mutasis', 'saldo_sesudah')) {
                DB::statement("ALTER TABLE kas_mutasis MODIFY COLUMN saldo_sesudah DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            }
        }

        // 7. Upgrade sales_payments & details
        if (Schema::hasTable('sales_payments')) {
            DB::statement("ALTER TABLE sales_payments MODIFY COLUMN total_faktur DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_payments MODIFY COLUMN total_potongan DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_payments MODIFY COLUMN total_bayar DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }
        if (Schema::hasTable('sales_payment_details')) {
            DB::statement("ALTER TABLE sales_payment_details MODIFY COLUMN total_faktur DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_payment_details MODIFY COLUMN sisa_piutang DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_payment_details MODIFY COLUMN jumlah_bayar DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_payment_details MODIFY COLUMN potongan DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE sales_payment_details MODIFY COLUMN sisa_akhir DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }

        // 8. Upgrade purchase_payments & details
        if (Schema::hasTable('purchase_payments')) {
            DB::statement("ALTER TABLE purchase_payments MODIFY COLUMN total_faktur DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_payments MODIFY COLUMN total_potongan DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_payments MODIFY COLUMN total_bayar DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }
        if (Schema::hasTable('purchase_payment_details')) {
            DB::statement("ALTER TABLE purchase_payment_details MODIFY COLUMN total_faktur DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_payment_details MODIFY COLUMN sisa_utang DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_payment_details MODIFY COLUMN jumlah_bayar DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_payment_details MODIFY COLUMN potongan DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE purchase_payment_details MODIFY COLUMN sisa_akhir DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }

        // 9. Upgrade jurnals & jurnal_details
        if (Schema::hasTable('jurnals')) {
            DB::statement("ALTER TABLE jurnals MODIFY COLUMN total_debit DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE jurnals MODIFY COLUMN total_kredit DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }
        if (Schema::hasTable('jurnal_details')) {
            DB::statement("ALTER TABLE jurnal_details MODIFY COLUMN debit DECIMAL(20,2) NOT NULL DEFAULT 0.00");
            DB::statement("ALTER TABLE jurnal_details MODIFY COLUMN kredit DECIMAL(20,2) NOT NULL DEFAULT 0.00");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
