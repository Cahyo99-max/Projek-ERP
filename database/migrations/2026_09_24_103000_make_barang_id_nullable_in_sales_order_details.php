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
        if (Schema::hasTable('sales_order_details')) {
            DB::statement("ALTER TABLE `sales_order_details` MODIFY `barang_id` BIGINT UNSIGNED NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales_order_details')) {
            DB::statement("ALTER TABLE `sales_order_details` MODIFY `barang_id` BIGINT UNSIGNED NOT NULL");
        }
    }
};
