<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('kode_bom', 50)->unique();
            $table->string('nama_bom', 150);
            $table->foreignId('barang_jadi_id')->constrained('master_barang')->onDelete('cascade');
            $table->decimal('jumlah_hasil', 15, 4)->default(1.0000);
            $table->foreignId('satuan_hasil_id')->constrained('master_satuan')->onDelete('restrict');
            $table->enum('tipe_bom', ['Garmen', 'Umum'])->default('Garmen');
            $table->decimal('biaya_tenaga_kerja', 15, 4)->default(0.0000);
            $table->decimal('biaya_overhead', 15, 4)->default(0.0000);
            $table->decimal('total_estimasi_hpp', 15, 4)->default(0.0000);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'branch_id', 'status', 'barang_jadi_id'], 'idx_boms_perf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
