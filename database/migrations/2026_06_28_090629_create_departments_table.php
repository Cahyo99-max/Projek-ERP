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
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); // BIGINT(20) PK Auto Increment
            $table->string('department_code', 20)->unique(); // Kode Departemen (ditambah unique biar aman)
            $table->string('department_name', 100); // Nama Departemen
            $table->string('description', 255)->nullable(); // Keterangan (Null: Yes)
            $table->enum('status', ['Active', 'Inactive'])->default('Active'); // Status (Null: No)

            // Foreign Keys ke tabel users (Null: Yes)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps(); // otomatis buat created_at & updated_at TIMESTAMP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
