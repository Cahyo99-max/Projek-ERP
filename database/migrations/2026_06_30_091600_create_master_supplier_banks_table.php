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
        // 1. Create master_supplier_banks table
        Schema::create('master_supplier_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('master_suppliers')->cascadeOnDelete();
            $table->string('nama_bank', 100);
            $table->string('nomor_rekening', 100);
            $table->string('atas_nama_rekening', 200);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // 2. Drop columns from master_suppliers table
        Schema::table('master_suppliers', function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'nomor_rekening', 'atas_nama_rekening']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restore columns to master_suppliers table
        Schema::table('master_suppliers', function (Blueprint $table) {
            $table->string('nama_bank', 100)->nullable();
            $table->string('nomor_rekening', 100)->nullable();
            $table->string('atas_nama_rekening', 200)->nullable();
        });

        // 2. Drop master_supplier_banks table
        Schema::dropIfExists('master_supplier_banks');
    }
};
