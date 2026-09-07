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
        Schema::table('companies', function (Blueprint $table) {
            $table->index('nama_perusahaan');
            $table->index('status');
            $table->index('director_name');
            $table->index('city');
            $table->index('npwp');
            $table->index('nib');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['nama_perusahaan']);
            $table->dropIndex(['status']);
            $table->dropIndex(['director_name']);
            $table->dropIndex(['city']);
            $table->dropIndex(['npwp']);
            $table->dropIndex(['nib']);
        });
    }
};
