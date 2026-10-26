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
            if (!Schema::hasColumn('kas_mutasis', 'tender_id')) {
                $table->unsignedBigInteger('tender_id')->nullable()->after('transfer_to_kas_bank_id');
                $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('set null');
                $table->index(['tender_id', 'tanggal'], 'idx_kas_mutasis_tender_tanggal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_mutasis', function (Blueprint $table) {
            if (Schema::hasColumn('kas_mutasis', 'tender_id')) {
                $table->dropForeign(['tender_id']);
                $table->dropIndex('idx_kas_mutasis_tender_tanggal');
                $table->dropColumn('tender_id');
            }
        });
    }
};
