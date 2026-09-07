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
        if (Schema::hasTable('sales_quotations') && Schema::hasColumn('sales_quotations', 'margin_persen')) {
            Schema::table('sales_quotations', function (Blueprint $table) {
                $table->decimal('margin_persen', 15, 2)->default(0)->change();
            });
        }

        if (Schema::hasTable('sales_quotation_items') && Schema::hasColumn('sales_quotation_items', 'margin_persen')) {
            Schema::table('sales_quotation_items', function (Blueprint $table) {
                $table->decimal('margin_persen', 15, 2)->default(0)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_quotations') && Schema::hasColumn('sales_quotations', 'margin_persen')) {
            Schema::table('sales_quotations', function (Blueprint $table) {
                $table->decimal('margin_persen', 5, 2)->default(0)->change();
            });
        }

        if (Schema::hasTable('sales_quotation_items') && Schema::hasColumn('sales_quotation_items', 'margin_persen')) {
            Schema::table('sales_quotation_items', function (Blueprint $table) {
                $table->decimal('margin_persen', 5, 2)->default(0)->change();
            });
        }
    }
};
