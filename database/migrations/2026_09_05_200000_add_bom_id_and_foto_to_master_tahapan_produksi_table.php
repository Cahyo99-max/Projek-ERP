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
        Schema::table('master_tahapan_produksi', function (Blueprint $table) {
            $table->unsignedBigInteger('bom_id')->nullable()->after('branch_id')->index();
            $table->string('foto', 255)->nullable()->after('deskripsi');

            $table->foreign('bom_id')->references('id')->on('boms')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_tahapan_produksi', function (Blueprint $table) {
            $table->dropForeign(['bom_id']);
            $table->dropColumn(['bom_id', 'foto']);
        });
    }
};
