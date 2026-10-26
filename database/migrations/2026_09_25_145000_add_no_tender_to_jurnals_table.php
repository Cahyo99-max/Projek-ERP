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
        Schema::table('jurnals', function (Blueprint $table) {
            if (!Schema::hasColumn('jurnals', 'no_tender')) {
                $table->string('no_tender', 100)->nullable()->after('referensi_id');
                $table->index('no_tender', 'idx_jurnal_no_tender');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnals', function (Blueprint $table) {
            if (Schema::hasColumn('jurnals', 'no_tender')) {
                $table->dropIndex('idx_jurnal_no_tender');
                $table->dropColumn('no_tender');
            }
        });
    }
};
