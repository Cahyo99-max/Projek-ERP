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
        Schema::table('branches', function (Blueprint $table) {
            $table->index('nama_cabang');
            $table->index('nama_perusahaan');
            $table->index('status');
            $table->index('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex(['nama_cabang']);
            $table->dropIndex(['nama_perusahaan']);
            $table->dropIndex(['status']);
            $table->dropIndex(['city']);
        });
    }
};
