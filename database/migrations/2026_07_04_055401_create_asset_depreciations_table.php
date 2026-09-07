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
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('fixed_assets')->onUpdate('cascade')->onDelete('cascade');
            $table->string('periode_penyusutan', 7)->comment('Format YYYY-MM');
            $table->decimal('nilai_penyusutan', 18, 2);
            $table->decimal('nilai_buku_sebelumnya', 18, 2);
            $table->decimal('nilai_buku_setelahnya', 18, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->onUpdate('cascade')->nullOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index('asset_id');
            $table->index('periode_penyusutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};
