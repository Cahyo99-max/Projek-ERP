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
        Schema::create('branches', function (Blueprint $table) {
            $table->id(); // BIGINT PK Auto Increment

            $table->string('kode_cabang', 20)->unique();   // Kode Cabang
            $table->string('nama_cabang', 150);            // Nama Cabang
            $table->string('nama_perusahaan', 150)->nullable(); // Nama Perusahaan (multi-company)
            $table->string('phone', 20)->nullable();       // No Telepon
            $table->string('email', 150)->nullable();      // Email Cabang
            $table->text('address')->nullable();           // Alamat
            $table->string('city', 100)->nullable();       // Kota
            $table->string('province', 100)->nullable();   // Provinsi
            $table->string('postal_code', 10)->nullable(); // Kode Pos
            $table->string('manager_name', 150)->nullable(); // Nama Kepala Cabang
            $table->enum('status', ['Active', 'Inactive'])->default('Active'); // Status

            // Foreign Keys ke tabel users (audit trail)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
