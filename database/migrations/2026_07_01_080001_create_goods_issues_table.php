<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_issues', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_gi', 50)->unique();
            $table->date('tanggal_gi');
            $table->foreignId('gudang_id')->constrained('master_gudang')->restrictOnDelete();
            $table->string('tujuan', 150)->comment('Nama penerima / departemen / proyek');
            $table->enum('tipe_pengeluaran', ['Internal', 'Retur Supplier', 'Penyesuaian', 'Lainnya'])
                  ->default('Internal');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Draft', 'Confirmed'])->default('Draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_issues');
    }
};
