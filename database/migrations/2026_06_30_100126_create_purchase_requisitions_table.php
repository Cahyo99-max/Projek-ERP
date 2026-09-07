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
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pr', 50)->unique();
            $table->date('tanggal_pr');
            $table->date('tanggal_dibutuhkan');
            $table->foreignId('departemen_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('diminta_oleh')->constrained('users')->cascadeOnDelete();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Draft', 'Pending', 'Approved', 'Rejected', 'Closed'])->default('Draft');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_disetujui')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};
