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
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'is_saldo_awal')) {
                $table->boolean('is_saldo_awal')->default(false)->after('gr_id');
            }
            if (!Schema::hasColumn('purchase_invoices', 'nilai_faktur_asli')) {
                $table->decimal('nilai_faktur_asli', 18, 2)->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('purchase_invoices', 'saldo_awal_coa_id')) {
                $table->foreignId('saldo_awal_coa_id')
                    ->nullable()
                    ->after('uang_muka_coa_id')
                    ->constrained('chart_of_accounts')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_invoices', 'hutang_coa_id')) {
                $table->foreignId('hutang_coa_id')
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
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'saldo_awal_coa_id')) {
                $table->dropForeign(['saldo_awal_coa_id']);
                $table->dropColumn('saldo_awal_coa_id');
            }
            if (Schema::hasColumn('purchase_invoices', 'hutang_coa_id')) {
                $table->dropForeign(['hutang_coa_id']);
                $table->dropColumn('hutang_coa_id');
            }
            if (Schema::hasColumn('purchase_invoices', 'nilai_faktur_asli')) {
                $table->dropColumn('nilai_faktur_asli');
            }
            if (Schema::hasColumn('purchase_invoices', 'is_saldo_awal')) {
                $table->dropColumn('is_saldo_awal');
            }
        });
    }
};

