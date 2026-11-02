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
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'is_saldo_awal')) {
                $table->boolean('is_saldo_awal')->default(false)->after('delivery_order_id');
            }
            if (!Schema::hasColumn('sales_invoices', 'nilai_faktur_asli')) {
                $table->decimal('nilai_faktur_asli', 20, 2)->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('sales_invoices', 'saldo_awal_coa_id')) {
                $table->foreignId('saldo_awal_coa_id')
                    ->nullable()
                    ->after('uang_muka_coa_id')
                    ->constrained('chart_of_accounts')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('sales_invoices', 'piutang_coa_id')) {
                $table->foreignId('piutang_coa_id')
                    ->nullable()
                    ->after('saldo_awal_coa_id')
                    ->constrained('chart_of_accounts')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'piutang_coa_id')) {
                $table->dropForeign(['piutang_coa_id']);
                $table->dropColumn('piutang_coa_id');
            }
            if (Schema::hasColumn('sales_invoices', 'saldo_awal_coa_id')) {
                $table->dropForeign(['saldo_awal_coa_id']);
                $table->dropColumn('saldo_awal_coa_id');
            }
            if (Schema::hasColumn('sales_invoices', 'nilai_faktur_asli')) {
                $table->dropColumn('nilai_faktur_asli');
            }
            if (Schema::hasColumn('sales_invoices', 'is_saldo_awal')) {
                $table->dropColumn('is_saldo_awal');
            }
        });
    }
};

