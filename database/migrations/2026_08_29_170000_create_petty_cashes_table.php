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
        Schema::create('petty_cashes', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi', 50)->unique();
            $table->date('tanggal');
            $table->enum('tipe', ['Pengeluaran', 'Pemasukan', 'Klaim'])->default('Pengeluaran');
            $table->string('kategori', 100);
            $table->enum('tipe_entitas', ['PT', 'Cabang'])->default('PT');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('coa_kas_id')->nullable();
            $table->unsignedBigInteger('lawan_coa_id')->nullable();
            $table->unsignedBigInteger('tender_id')->nullable();
            $table->decimal('nominal', 18, 2);
            $table->decimal('saldo_sebelum', 18, 2)->default(0);
            $table->decimal('saldo_sesudah', 18, 2)->default(0);
            $table->string('nama_pengaju', 150)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('lampiran_nota', 255)->nullable();
            $table->enum('status', ['Draft', 'Disetujui', 'Dibatalkan'])->default('Disetujui');
            $table->unsignedBigInteger('jurnal_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('coa_kas_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('lawan_coa_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('set null');
            $table->foreign('jurnal_id')->references('id')->on('jurnals')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['company_id', 'branch_id', 'tipe_entitas']);
            $table->index(['tanggal', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cashes');
    }
};
