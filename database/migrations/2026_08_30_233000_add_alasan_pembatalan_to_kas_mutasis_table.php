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
        Schema::table('kas_mutasis', function (Blueprint $table) {
            if (!Schema::hasColumn('kas_mutasis', 'alasan_pembatalan')) {
                $table->string('alasan_pembatalan', 40)->nullable()->after('cancelled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_mutasis', function (Blueprint $table) {
            if (Schema::hasColumn('kas_mutasis', 'alasan_pembatalan')) {
                $table->dropColumn('alasan_pembatalan');
            }
        });
    }
};
