<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tender_items', function (Blueprint $table) {
            if (!Schema::hasColumn('tender_items', 'nama_barang_custom')) {
                $table->string('nama_barang_custom', 255)->nullable()->after('barang_id');
            }
            if (!Schema::hasColumn('tender_items', 'catatan_item')) {
                $table->string('catatan_item', 255)->nullable()->after('subtotal');
            }
        });

        // Make barang_id nullable if not already
        Schema::table('tender_items', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tender_items', function (Blueprint $table) {
            if (Schema::hasColumn('tender_items', 'nama_barang_custom')) {
                $table->dropColumn('nama_barang_custom');
            }
            if (Schema::hasColumn('tender_items', 'catatan_item')) {
                $table->dropColumn('catatan_item');
            }
        });
    }
};
