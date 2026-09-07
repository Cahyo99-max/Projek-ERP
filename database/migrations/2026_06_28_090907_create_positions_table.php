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
        Schema::create('positions', function (Blueprint $table) {
            $table->id(); // BIGINT(20) PK Auto Increment

            // FK ke tabel departments (Null: No). Jika departemen dihapus, jabatan ikut terhapus (cascade)
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');

            $table->string('position_code', 20)->unique(); // Kode Jabatan (Unique biar rapi)
            $table->string('position_name', 100); // Nama Jabatan
            $table->string('description', 255)->nullable(); // Keterangan (Null: Yes)
            $table->enum('status', ['Active', 'Inactive'])->default('Active'); // Status (Null: No)

            // FK ke tabel users (Null: Yes)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps(); // created_at & updated_at TIMESTAMP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
