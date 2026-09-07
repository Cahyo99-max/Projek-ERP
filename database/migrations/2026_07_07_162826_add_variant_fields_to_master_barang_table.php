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
            $table->foreignId('parent_id')->nullable()->after('id')->comment('FK ke parent barang jika barang ini varian')
                  ->constrained('master_barang')->onUpdate('cascade')->onDelete('cascade');
            $table->string('ukuran', 50)->nullable()->after('warna_id')->comment('Ukuran varian barang (S, M, L, dll.)');
        });

        // Modifikasi enum tipe_item
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE master_barang MODIFY COLUMN tipe_item ENUM('Stok', 'Aset', 'Jasa', 'Bundle') NOT NULL DEFAULT 'Stok'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'ukuran']);
        });

        // Kembalikan enum tipe_item
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE master_barang MODIFY COLUMN tipe_item ENUM('Stok', 'Aset', 'Jasa') NOT NULL DEFAULT 'Stok'");
        }
    }
};
