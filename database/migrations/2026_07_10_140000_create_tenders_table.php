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
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kontrak', 100)->nullable();
            $table->foreignId('customer_id')->constrained('master_customers');
            $table->foreignId('sales_id')->constrained('users');
            $table->string('nama_tender', 200);
            $table->date('tanggal');
            $table->date('deadline');
            $table->date('estimasi_closing');
            $table->string('currency', 10)->default('IDR');
            $table->string('status', 50)->default('Draft'); // Draft, Sent, Won, Lost, Cancelled
            $table->string('priority', 20)->default('Medium'); // Low, Medium, High
            $table->decimal('nilai_estimasi', 15, 2)->default(0.00);
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
