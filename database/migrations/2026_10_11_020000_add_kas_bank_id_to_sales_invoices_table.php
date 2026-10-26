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
            if (!Schema::hasColumn('sales_invoices', 'kas_bank_id')) {
                $table->foreignId('kas_bank_id')->nullable()->after('uang_muka_coa_id')->constrained('kas_banks')->nullOnDelete();
            }
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'kas_bank_id')) {
                $table->foreignId('kas_bank_id')->nullable()->after('uang_muka_coa_id')->constrained('kas_banks')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'kas_bank_id')) {
                $table->dropForeign(['kas_bank_id']);
                $table->dropColumn('kas_bank_id');
            }
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'kas_bank_id')) {
                $table->dropForeign(['kas_bank_id']);
                $table->dropColumn('kas_bank_id');
            }
        });
    }
};
