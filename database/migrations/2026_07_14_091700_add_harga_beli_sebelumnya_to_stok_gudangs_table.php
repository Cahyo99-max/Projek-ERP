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
        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->decimal('harga_beli_sebelumnya', 18, 2)->default(0.00)->after('harga_beli_terakhir')->comment('Harga beli sebelum yang terakhir pada gudang ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stok_gudangs', function (Blueprint $table) {
            $table->dropColumn('harga_beli_sebelumnya');
        });
    }
};
