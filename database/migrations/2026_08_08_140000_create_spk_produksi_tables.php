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
        // 1. TABEL UTAMA: SPK PRODUKSI (WORK ORDER)
        Schema::create('spk_produksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('nomor_spk', 50)->unique();
            $table->date('tanggal_spk');
            $table->date('target_selesai')->nullable();
            
            // Relasi ke Master BOM & Produk Jadi
            $table->foreignId('bom_id')->constrained('boms')->cascadeOnDelete();
            $table->foreignId('barang_jadi_id')->constrained('master_barang')->cascadeOnDelete();
            $table->foreignId('tender_id')->nullable()->constrained('tenders')->nullOnDelete();
            $table->foreignId('satuan_id')->constrained('master_satuan');
            
            // Qty Target & Realisasi
            $table->decimal('jumlah_rencana', 15, 4)->default(1);
            $table->decimal('jumlah_selesai', 15, 4)->default(0);
            $table->decimal('jumlah_reject', 15, 4)->default(0);

            // Klasifikasi & PIC
            $table->string('tipe_spk', 50)->default('Make to Stock'); // Make to Stock, Make to Order, Custom/Sampel
            $table->string('prioritas', 30)->default('Normal'); // Normal, Tinggi (Urgent), Rendah
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('users')->nullOnDelete();

            // Estimasi Biaya
            $table->decimal('estimasi_biaya_bahan', 15, 4)->default(0);
            $table->decimal('estimasi_biaya_tk', 15, 4)->default(0);
            $table->decimal('estimasi_biaya_overhead', 15, 4)->default(0);
            $table->decimal('estimasi_total_biaya', 15, 4)->default(0);

            // Workflow Status
            $table->string('status', 30)->default('Draft'); // Draft, Pending, Approved, In Progress, Completed, Cancelled
            $table->text('keterangan')->nullable();

            // Approval & Audit
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // Composite Performance Indexes
            $table->index(['company_id', 'deleted_at', 'status'], 'idx_spk_company_status');
            $table->index(['company_id', 'deleted_at', 'nomor_spk'], 'idx_spk_company_nomor');
            $table->index(['company_id', 'branch_id', 'status', 'barang_jadi_id'], 'idx_spk_perf');
        });

        // 2. TABEL RINCIAN KEBUTUHAN BAHAN MENTAH SPK
        Schema::create('spk_produksi_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spk_id')->constrained('spk_produksi')->cascadeOnDelete();
            $table->foreignId('barang_mentah_id')->constrained('master_barang')->cascadeOnDelete();
            $table->foreignId('satuan_id')->constrained('master_satuan');
            
            $table->decimal('kebutuhan_per_unit', 15, 4)->default(0);
            $table->decimal('waste_percent', 5, 2)->default(0);
            $table->decimal('total_kebutuhan', 15, 4)->default(0);
            $table->decimal('total_keluar_gudang', 15, 4)->default(0);
            $table->decimal('estimasi_harga_satuan', 15, 4)->default(0);
            $table->decimal('estimasi_subtotal', 15, 4)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['spk_id', 'barang_mentah_id'], 'idx_spk_mat_item');
        });

        // 3. TABEL TAHAPAN KERJA / ROUTING PRODUKSI
        Schema::create('spk_produksi_tahapans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spk_id')->constrained('spk_produksi')->cascadeOnDelete();
            $table->integer('urutan')->default(1);
            $table->string('nama_tahapan', 100);
            $table->decimal('target_qty', 15, 4)->default(0);
            $table->decimal('qty_selesai', 15, 4)->default(0);
            $table->decimal('qty_reject', 15, 4)->default(0);
            $table->string('status', 30)->default('Menunggu'); // Menunggu, Sedang Dikerjakan, Selesai
            $table->string('pic_operator', 150)->nullable();
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['spk_id', 'urutan'], 'idx_spk_tahapan_urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spk_produksi_tahapans');
        Schema::dropIfExists('spk_produksi_materials');
        Schema::dropIfExists('spk_produksi');
    }
};
