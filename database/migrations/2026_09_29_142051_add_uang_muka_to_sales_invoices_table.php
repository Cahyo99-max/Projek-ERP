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
            if (!Schema::hasColumn('sales_invoices', 'uang_muka')) {
                $table->decimal('uang_muka', 18, 2)->default(0.00)->after('grand_total');
            }
            if (!Schema::hasColumn('sales_invoices', 'sisa_tagihan')) {
                $table->decimal('sisa_tagihan', 18, 2)->default(0.00)->after('uang_muka');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'sisa_tagihan')) {
                $table->dropColumn('sisa_tagihan');
            }
            if (Schema::hasColumn('sales_invoices', 'uang_muka')) {
                $table->dropColumn('uang_muka');
            }
        });
    }
};
