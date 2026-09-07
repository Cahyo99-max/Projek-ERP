<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table to track service billing, vendor, active periods, and internal PICs
     */
    public function up(): void
    {
        Schema::create('registrasi_jasa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('master_barang')->onUpdate('cascade')->onDelete('restrict');
            $table->string('no_invoice', 100)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('master_suppliers')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->decimal('harga_transaksi', 18, 2)->default(0);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['Aktif', 'Selesai', 'Tenggang'])->default('Aktif');
            $table->text('keterangan')->nullable();

            // Audit Trail
            $table->foreignId('created_by')->nullable()->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onUpdate('cascade')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('barang_id');
            $table->index('supplier_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrasi_jasa');
    }
};
