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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Ini otomatis BIGINT 20 PK Auto Increment
            $table->string('kode_karyawan', 30)->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('departemen_id')->nullable();
            $table->unsignedBigInteger('jabatan_id')->nullable();
            $table->string('foto', 255)->nullable();
            $table->string('nama_lengkap', 150);
            $table->string('username', 50)->unique();
            $table->string('email', 150)->unique();
            $table->string('no_hp', 20)->nullable();
            $table->string('password', 255);
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');
            $table->datetime('login_terakhir')->nullable();
            $table->rememberToken();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
