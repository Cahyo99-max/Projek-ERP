<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_tahapan_produksi', function (Blueprint $table) {
            $table->id();

            // Multi-tenant scope
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();

            // Tipe SPK yang berlaku: Make to Stock | Make to Order | Custom/Sampel | Semua
            $table->string('tipe_spk', 50)->default('Semua')->index();

            // Urutan proses (1, 2, 3, ...)
            $table->unsignedSmallInteger('urutan')->default(1);

            // Nama tahapan proses
            $table->string('nama_tahapan', 150);

            // Deskripsi / catatan pengerjaan (optional)
            $table->text('deskripsi')->nullable();

            // Toggle aktif/nonaktif — tanpa hapus data
            $table->boolean('is_active')->default(true);

            // Audit trail
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index gabungan untuk query saat store SPK
            $table->index(['company_id', 'tipe_spk', 'is_active', 'urutan'], 'idx_master_tahapan_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_tahapan_produksi');
    }
};
