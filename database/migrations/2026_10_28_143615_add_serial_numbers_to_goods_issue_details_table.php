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
        if (Schema::hasTable('goods_issue_details')) {
            Schema::table('goods_issue_details', function (Blueprint $table) {
                if (!Schema::hasColumn('goods_issue_details', 'serial_numbers')) {
                    $table->json('serial_numbers')->nullable()->after('jumlah');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('goods_issue_details')) {
            Schema::table('goods_issue_details', function (Blueprint $table) {
                if (Schema::hasColumn('goods_issue_details', 'serial_numbers')) {
                    $table->dropColumn('serial_numbers');
                }
            });
        }
    }
};
