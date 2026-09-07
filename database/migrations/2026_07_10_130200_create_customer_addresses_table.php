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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('master_customers')->cascadeOnDelete();
            $table->enum('jenis_alamat', ['Kantor', 'Gudang', 'Toko', 'Rumah', 'Lainnya'])->default('Kantor');
            $table->string('nama_penerima', 200);
            $table->string('telepon', 50)->nullable();
            $table->text('alamat');
            $table->string('kota', 100);
            $table->string('provinsi', 100);
            $table->string('kode_pos', 20)->nullable();
            $table->string('negara', 100)->default('Indonesia');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
