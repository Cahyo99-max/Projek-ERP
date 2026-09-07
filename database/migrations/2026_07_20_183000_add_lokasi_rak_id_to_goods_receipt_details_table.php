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
        Schema::table('goods_receipt_details', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_receipt_details', 'lokasi_rak_id')) {
                $table->foreignId('lokasi_rak_id')
                    ->nullable()
                    ->after('barang_id')
                    ->constrained('master_lokasi_rak')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipt_details', function (Blueprint $table) {
            if (Schema::hasColumn('goods_receipt_details', 'lokasi_rak_id')) {
                $table->dropForeign(['lokasi_rak_id']);
                $table->dropColumn('lokasi_rak_id');
            }
        });
    }
};
