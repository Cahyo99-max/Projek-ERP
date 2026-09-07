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
            if (!Schema::hasColumn('tenders', 'ppn_pajak_id')) {
                $table->unsignedBigInteger('ppn_pajak_id')->nullable()->after('diskon_nominal');
            }
            if (!Schema::hasColumn('tenders', 'pph_pajak_id')) {
                $table->unsignedBigInteger('pph_pajak_id')->nullable()->after('nilai_ppn');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->dropColumn(['ppn_pajak_id', 'pph_pajak_id']);
        });
    }
};
