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
        Schema::table('purchase_order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_details', 'satuan_id')) {
                $table->foreignId('satuan_id')->nullable()->after('barang_id')->constrained('master_satuan')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_order_details', 'faktor_pengali')) {
                $table->decimal('faktor_pengali', 18, 4)->default(1.0000)->after('jumlah');
            }
            if (!Schema::hasColumn('purchase_order_details', 'jumlah_dasar')) {
                $table->decimal('jumlah_dasar', 18, 4)->nullable()->after('faktor_pengali');
            }
            $table->decimal('jumlah', 18, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_details', 'satuan_id')) {
                $table->dropForeign(['satuan_id']);
                $table->dropColumn('satuan_id');
            }
            if (Schema::hasColumn('purchase_order_details', 'faktor_pengali')) {
                $table->dropColumn('faktor_pengali');
            }
            if (Schema::hasColumn('purchase_order_details', 'jumlah_dasar')) {
                $table->dropColumn('jumlah_dasar');
            }
            $table->decimal('jumlah', 12, 2)->change();
        });
    }
};
