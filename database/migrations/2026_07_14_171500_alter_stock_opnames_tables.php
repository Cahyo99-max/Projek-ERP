<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alter stock_opnames table
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->string('status', 30)->default('Draft')->change();
            $table->date('tanggal_mulai')->nullable()->after('tanggal_opname');
            $table->foreignId('disetujui_oleh')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable()->after('disetujui_oleh');
        });

        // Alter stock_opname_details table
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->decimal('stok_fisik', 15, 2)->nullable()->change();
            $table->decimal('selisih', 15, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->decimal('stok_fisik', 15, 2)->nullable(false)->change();
            $table->decimal('selisih', 15, 2)->nullable(false)->change();
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh']);
            $table->dropColumn(['disetujui_oleh', 'disetujui_pada', 'tanggal_mulai']);
        });
    }
};
