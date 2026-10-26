<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sales_quotations')) {
            Schema::table('sales_quotations', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->change();
            });

            // Update existing quotations that have branch_id so company_id is NULL
            DB::table('sales_quotations')
                ->whereNotNull('branch_id')
                ->update(['company_id' => null]);
        }

        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->change();
            });

            // Update existing sales orders that have branch_id so company_id is NULL
            DB::table('sales_orders')
                ->whereNotNull('branch_id')
                ->update(['company_id' => null]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_quotations')) {
            Schema::table('sales_quotations', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable(false)->change();
            });
        }

        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable(false)->change();
            });
        }
    }
};
