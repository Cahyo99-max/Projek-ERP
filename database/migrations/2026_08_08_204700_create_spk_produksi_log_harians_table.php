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
        Schema::create('spk_produksi_log_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spk_id')->constrained('spk_produksi')->cascadeOnDelete();
            $table->foreignId('spk_tahapan_id')->constrained('spk_produksi_tahapans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('qty_hasil_hari_ini', 15, 4)->default(0);
            $table->decimal('qty_reject_hari_ini', 15, 4)->default(0);
            $table->string('pic_operator', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['spk_tahapan_id', 'tanggal'], 'idx_log_tahapan_tanggal');
            $table->index(['spk_id', 'tanggal'], 'idx_log_spk_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spk_produksi_log_harians');
    }
};
