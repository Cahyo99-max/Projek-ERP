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
            if (!Schema::hasColumn('tenders', 'biaya_ekstra_kena_ppn')) {
                $table->boolean('biaya_ekstra_kena_ppn')->default(false)->after('catatan_biaya_ekstra');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            if (Schema::hasColumn('tenders', 'biaya_ekstra_kena_ppn')) {
                $table->dropColumn('biaya_ekstra_kena_ppn');
            }
        });
    }
};
