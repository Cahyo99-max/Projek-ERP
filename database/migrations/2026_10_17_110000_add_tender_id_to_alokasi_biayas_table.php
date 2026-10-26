<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alokasi_biayas', function (Blueprint $table) {
            $table->unsignedBigInteger('tender_id')->nullable()->after('po_biaya_id')->index();
            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('alokasi_biayas', function (Blueprint $table) {
            $table->dropForeign(['tender_id']);
            $table->dropColumn('tender_id');
        });
    }
};
