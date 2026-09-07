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
        if (Schema::hasTable('sales_orders')) {
            DB::statement("ALTER TABLE `sales_orders` MODIFY `pricing_tier` VARCHAR(30) NOT NULL DEFAULT 'Retail'");
        }

        if (Schema::hasTable('sales_order_details')) {
            DB::statement("ALTER TABLE `sales_order_details` MODIFY `pricing_tier` VARCHAR(30) NOT NULL DEFAULT 'Retail'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_order_details')) {
            DB::statement("ALTER TABLE `sales_order_details` MODIFY `pricing_tier` ENUM('Retail', 'Grosir', 'Tender') NOT NULL DEFAULT 'Retail'");
        }

        if (Schema::hasTable('sales_orders')) {
            DB::statement("ALTER TABLE `sales_orders` MODIFY `pricing_tier` ENUM('Retail', 'Grosir', 'Tender') NOT NULL DEFAULT 'Retail'");
        }
    }
};
