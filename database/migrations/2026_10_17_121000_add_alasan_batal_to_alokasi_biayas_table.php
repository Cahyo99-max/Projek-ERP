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
        Schema::table('alokasi_biayas', function (Blueprint $table) {
            if (!Schema::hasColumn('alokasi_biayas', 'alasan_batal')) {
                $table->string('alasan_batal', 30)->nullable()->after('keterangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alokasi_biayas', function (Blueprint $table) {
            if (Schema::hasColumn('alokasi_biayas', 'alasan_batal')) {
                $table->dropColumn('alasan_batal');
            }
        });
    }
};
