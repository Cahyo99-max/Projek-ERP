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
        Schema::table('tenders', function (Blueprint $table) {
            if (Schema::hasColumn('tenders', 'grand_total')) {
                $table->dropColumn('grand_total');
            }
            if (Schema::hasColumn('tenders', 'nilai_deal')) {
                $table->dropColumn('nilai_deal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->decimal('grand_total', 15, 2)->nullable()->default(0);
            $table->decimal('nilai_deal', 15, 2)->nullable()->default(0);
        });
    }
};
