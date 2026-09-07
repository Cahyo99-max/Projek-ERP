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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Kode Akun, e.g. 1101, 2101, 5101');
            $table->string('name')->comment('Nama Akun, e.g. Kas Kecil, Hutang Dagang');
            $table->enum('type', [
                'asset',         // Aset / Aktiva
                'liability',     // Kewajiban / Utang
                'equity',        // Ekuitas / Modal
                'revenue',       // Pendapatan / Penjualan
                'cogs',          // Harga Pokok Penjualan (HPP)
                'expense',       // Beban / Biaya Operasional
                'other_revenue', // Pendapatan Lain-Lain
                'other_expense'  // Beban Lain-Lain
            ])->comment('Kategori Akun');
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->boolean('is_header')->default(false)->comment('True jika ini akun induk (Header), False jika Detail');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
