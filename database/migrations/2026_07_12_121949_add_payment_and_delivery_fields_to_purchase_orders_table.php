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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('payment_term_id')->nullable()->after('pajak_id')->constrained('payment_terms')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->after('payment_term_id')->constrained('payment_methods')->nullOnDelete();
            $table->date('tanggal_pengiriman')->nullable()->after('payment_method_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['payment_term_id']);
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn(['payment_term_id', 'payment_method_id', 'tanggal_pengiriman']);
        });
    }
};
