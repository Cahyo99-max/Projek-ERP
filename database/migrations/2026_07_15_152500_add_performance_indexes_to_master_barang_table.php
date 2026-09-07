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
            $table->index(['parent_id', 'deleted_at', 'tipe_item'], 'mb_parent_deleted_tipe_idx');
            $table->index(['parent_id', 'deleted_at', 'status'], 'mb_parent_deleted_status_idx');
            $table->index(['created_at'], 'mb_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->dropIndex('mb_parent_deleted_tipe_idx');
            $table->dropIndex('mb_parent_deleted_status_idx');
            $table->dropIndex('mb_created_at_idx');
        });
    }
};
