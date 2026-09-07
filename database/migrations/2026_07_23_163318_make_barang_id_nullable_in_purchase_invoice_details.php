<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make barang_id nullable so extra cost rows (Biaya Lain) can be stored
     * without a barang_id (they are freight/insurance/misc costs, not goods).
     */
    public function up(): void
    {
        Schema::table('purchase_invoice_details', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoice_details', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable(false)->change();
        });
    }
};
