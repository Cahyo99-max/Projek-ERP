<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_issue_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gi_id')->constrained('goods_issues')->cascadeOnDelete();
            $table->foreignId('barang_id')->constrained('master_barang')->restrictOnDelete();
            $table->decimal('jumlah', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_issue_details');
    }
};
