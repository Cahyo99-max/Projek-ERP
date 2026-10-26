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
        if (Schema::hasTable('delivery_order_details')) {
            Schema::table('delivery_order_details', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_order_details', 'serial_numbers')) {
                    $table->json('serial_numbers')->nullable()->after('catatan_item');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('delivery_order_details')) {
            Schema::table('delivery_order_details', function (Blueprint $table) {
                if (Schema::hasColumn('delivery_order_details', 'serial_numbers')) {
                    $table->dropColumn('serial_numbers');
                }
            });
        }
    }
};
