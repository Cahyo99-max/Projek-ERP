<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spk_pengeluaran_bahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('nomor_pengeluaran', 50)->unique();
            $table->date('tanggal_pengeluaran');
            $table->foreignId('spk_id')->constrained('spk_produksi')->onDelete('cascade');
            $table->foreignId('gudang_id')->constrained('master_gudang')->onDelete('restrict');
            $table->foreignId('penyerah_karyawan_id')->nullable()->constrained('karyawans')->onDelete('set null');
            $table->foreignId('penerima_karyawan_id')->nullable()->constrained('karyawans')->onDelete('set null');
            $table->string('status', 30)->default('Diserahkan');
            $table->text('catatan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('spk_pengeluaran_bahan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengeluaran_id')->constrained('spk_pengeluaran_bahans')->onDelete('cascade');
            $table->foreignId('spk_material_id')->constrained('spk_produksi_materials')->onDelete('cascade');
            $table->foreignId('barang_mentah_id')->constrained('master_barang')->onDelete('restrict');
            $table->foreignId('satuan_id')->nullable()->constrained('master_satuan')->onDelete('set null');
            $table->decimal('qty_total_spk', 15, 4)->default(0);
            $table->decimal('qty_sudah_diambil', 15, 4)->default(0);
            $table->decimal('qty_sisa_kebutuhan', 15, 4)->default(0);
            $table->decimal('qty_diambil_sekarang', 15, 4)->default(0);
            $table->string('catatan', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk_pengeluaran_bahan_details');
        Schema::dropIfExists('spk_pengeluaran_bahans');
    }
};
