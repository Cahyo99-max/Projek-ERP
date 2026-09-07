<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            // Drop foreign key referencing users
            $table->dropForeign(['diminta_oleh']);
        });

        // Truncate existing PR data to prevent constraint validation errors on empty karyawans table
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DB::table('purchase_requisition_details')->truncate();
            DB::table('purchase_requisitions')->truncate();
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('purchase_requisition_details')->truncate();
            DB::table('purchase_requisitions')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        Schema::table('purchase_requisitions', function (Blueprint $table) {
            // Add foreign key referencing karyawans
            $table->foreign('diminta_oleh')
                  ->references('id')
                  ->on('karyawans')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropForeign(['diminta_oleh']);
            $table->foreign('diminta_oleh')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }
};
