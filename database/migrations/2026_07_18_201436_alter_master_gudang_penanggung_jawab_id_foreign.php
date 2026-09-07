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
        // Set existing penanggung_jawab_id values to null to avoid constraint failure
        \Illuminate\Support\Facades\DB::table('master_gudang')->update(['penanggung_jawab_id' => null]);

        Schema::table('master_gudang', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                $table->dropForeign(['penanggung_jawab_id']);
            } else {
                $table->dropForeign('master_gudang_penanggung_jawab_id_foreign');
            }
            $table->foreign('penanggung_jawab_id')
                  ->references('id')->on('karyawans')
                  ->onUpdate('cascade')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_gudang', function (Blueprint $table) {
            $table->dropForeign(['penanggung_jawab_id']);
            $table->foreign('penanggung_jawab_id')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')->nullOnDelete();
        });
    }
};
