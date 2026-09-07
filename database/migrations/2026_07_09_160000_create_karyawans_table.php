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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique()->nullable();
            $table->string('kode_karyawan', 30)->unique()->nullable();
            $table->string('nama_lengkap', 150);
            $table->string('foto', 255)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->date('tanggal_masuk')->nullable();
            
            $table->foreignId('departemen_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('jabatan_id')->nullable()->constrained('positions')->nullOnDelete();
            
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
