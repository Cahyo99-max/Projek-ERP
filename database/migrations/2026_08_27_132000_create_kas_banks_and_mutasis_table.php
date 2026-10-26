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
        // 1. Tabel Akun Kas & Rekening Bank
        Schema::create('kas_banks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun', 50)->nullable(); // Misal: 1101-01
            $table->string('nama_akun', 100); // Misal: Kasir Utama, Bank BCA Operasional
            $table->enum('tipe', ['Kas Tunai', 'Bank', 'E-Wallet / Giro'])->default('Kas Tunai');
            $table->string('nama_bank', 100)->nullable(); // BCA, Mandiri, BRI, BNI
            $table->string('nomor_rekening', 100)->nullable();
            $table->string('atas_nama', 100)->nullable();
            $table->decimal('saldo_awal', 20, 2)->default(0);
            $table->decimal('saldo_berjalan', 20, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('user_companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('user_branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // 2. Tabel Riwayat Mutasi Buku Kas & Bank
        Schema::create('kas_mutasis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi', 50)->unique(); // KM-2608-0001, KK-2608-0001, TR-2608-0001
            $table->unsignedBigInteger('kas_bank_id');
            $table->enum('tipe', ['Masuk', 'Keluar', 'Transfer'])->default('Masuk');
            $table->string('kategori', 100); // Pembayaran Pelanggan, Pembayaran Supplier, Operasional, Biaya Gaji, Transfer Antar Kas, dll.
            $table->decimal('jumlah', 20, 2);
            $table->decimal('saldo_sebelum', 20, 2)->default(0);
            $table->decimal('saldo_sesudah', 20, 2)->default(0);
            $table->date('tanggal');
            $table->string('nomor_referensi', 100)->nullable(); // No SO, No DO, No PO, No Invoice
            $table->string('kontak_tipe', 50)->nullable(); // Customer, Supplier, Karyawan, Umum
            $table->unsignedBigInteger('kontak_id')->nullable();
            $table->string('nama_kontak', 150)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('lampiran', 255)->nullable(); // File bukti transfer / nota
            $table->unsignedBigInteger('transfer_to_kas_bank_id')->nullable(); // Untuk tipe Transfer
            $table->enum('status', ['Disetujui', 'Dibatalkan'])->default('Disetujui');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kas_bank_id')->references('id')->on('kas_banks')->onDelete('cascade');
            $table->foreign('transfer_to_kas_bank_id')->references('id')->on('kas_banks')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('user_companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('user_branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_mutasis');
        Schema::dropIfExists('kas_banks');
    }
};
