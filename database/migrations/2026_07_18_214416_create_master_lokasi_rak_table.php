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
        Schema::create('master_lokasi_rak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gudang_id')->constrained('master_gudang')->onDelete('cascade');
            $table->string('kode_rak', 20)->unique();
            $table->string('nama_rak', 50);
            $table->string('baris', 10)->nullable();
            $table->string('kolom', 10)->nullable();
            $table->string('level', 10)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->string('deskripsi', 100)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance search
            $table->index('gudang_id');
            $table->index('kode_rak');
            $table->index('nama_rak');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_lokasi_rak');
    }
};
