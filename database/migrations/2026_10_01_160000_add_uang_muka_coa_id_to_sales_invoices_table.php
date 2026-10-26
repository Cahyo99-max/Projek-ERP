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
            if (!Schema::hasColumn('sales_invoices', 'uang_muka_coa_id')) {
                $table->foreignId('uang_muka_coa_id')->nullable()->after('uang_muka')->constrained('chart_of_accounts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'uang_muka_coa_id')) {
                $table->dropForeign(['uang_muka_coa_id']);
                $table->dropColumn('uang_muka_coa_id');
            }
        });
    }
};
