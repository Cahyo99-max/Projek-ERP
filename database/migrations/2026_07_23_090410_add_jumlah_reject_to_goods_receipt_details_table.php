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
        Schema::table('goods_receipt_details', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_receipt_details', 'jumlah_reject')) {
                $table->decimal('jumlah_reject', 12, 2)->default(0.00)->after('jumlah');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipt_details', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipt_details', 'jumlah_reject')) {
                $table->dropColumn('jumlah_reject');
            }
        });
    }
};
