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
        Schema::table('master_suppliers', function (Blueprint $table) {
            $table->index(['status', 'deleted_at']);
            $table->index('nama_supplier');
            $table->index('telepon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_suppliers', function (Blueprint $table) {
            $table->dropIndex(['status', 'deleted_at']);
            $table->dropIndex(['nama_supplier']);
            $table->dropIndex(['telepon']);
        });
    }
};
