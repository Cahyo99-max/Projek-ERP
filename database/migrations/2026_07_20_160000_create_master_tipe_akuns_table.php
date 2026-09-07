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
        Schema::create('master_tipe_akuns', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tipe')->unique()->comment('Kode Tipe: AST, LIAB, EQT, REV, COGS, EXP');
            $table->string('nama_tipe')->comment('Nama Tipe: Aset / Aktiva, Kewajiban / Utang, dll');
            $table->enum('kategori_laporan', ['neraca', 'laba_rugi'])->default('neraca')->comment('Posisi Laporan Keuangan');
            $table->enum('saldo_normal_default', ['debit', 'credit'])->default('debit')->comment('Saldo Normal Default');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_tipe_akuns');
    }
};
