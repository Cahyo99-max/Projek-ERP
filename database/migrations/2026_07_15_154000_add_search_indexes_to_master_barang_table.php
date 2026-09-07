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
        Schema::table('master_barang', function (Blueprint $table) {
            $table->index(['nama_barang'], 'mb_nama_barang_idx');
            $table->index(['parent_id', 'deleted_at', 'id'], 'mb_parent_deleted_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->dropIndex('mb_nama_barang_idx');
            $table->dropIndex('mb_parent_deleted_id_idx');
        });
    }
};
