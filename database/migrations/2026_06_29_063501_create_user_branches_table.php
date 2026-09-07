<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel pivot: menghubungkan user dengan cabang yang bisa mereka akses.
     */
    public function up(): void
    {
        Schema::create('user_branches', function (Blueprint $table) {
            $table->id(); // BIGINT PK Auto Increment

            // FK ke tabel users (siapa usernya)
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete(); // Jika user dihapus, akses cabangnya ikut terhapus

            // FK ke tabel branches (cabang mana)
            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete(); // Jika cabang dihapus, datanya ikut terhapus

            // Apakah ini cabang default user (untuk pre-fill input form)
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            // Satu user tidak boleh duplikat di cabang yang sama
            $table->unique(['user_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_branches');
    }
};
