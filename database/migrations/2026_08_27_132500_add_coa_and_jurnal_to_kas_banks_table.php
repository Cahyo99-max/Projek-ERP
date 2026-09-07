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
        Schema::table('kas_banks', function (Blueprint $table) {
            $table->unsignedBigInteger('coa_id')->nullable()->after('tipe');
            $table->foreign('coa_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });

        Schema::table('kas_mutasis', function (Blueprint $table) {
            $table->unsignedBigInteger('lawan_coa_id')->nullable()->after('kategori');
            $table->unsignedBigInteger('jurnal_id')->nullable()->after('status');
            $table->foreign('lawan_coa_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('jurnal_id')->references('id')->on('jurnals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_mutasis', function (Blueprint $table) {
            $table->dropForeign(['jurnal_id']);
            $table->dropForeign(['lawan_coa_id']);
            $table->dropColumn(['jurnal_id', 'lawan_coa_id']);
        });

        Schema::table('kas_banks', function (Blueprint $table) {
            $table->dropForeign(['coa_id']);
            $table->dropColumn(['coa_id']);
        });
    }
};
