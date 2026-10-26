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
        if (Schema::hasTable('purchase_order_details')) {
            Schema::table('purchase_order_details', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_order_details', 'ppn_nominal')) {
                    $table->decimal('ppn_nominal', 20, 2)->default(0)->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_order_details')) {
            Schema::table('purchase_order_details', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_order_details', 'ppn_nominal')) {
                    $table->decimal('ppn_nominal', 15, 2)->default(0)->change();
                }
            });
        }
    }
};
