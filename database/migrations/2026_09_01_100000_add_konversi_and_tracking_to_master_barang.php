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
        // 1. Tambah tipe_pelacakan pada master_barang
        if (Schema::hasTable('master_barang')) {
            Schema::table('master_barang', function (Blueprint $table) {
                if (!Schema::hasColumn('master_barang', 'tipe_pelacakan')) {
                    $table->enum('tipe_pelacakan', ['None', 'Serial Number', 'Batch'])
                        ->default('None')
                        ->after('tipe_item');
                }
            });
        }

        // 2. Buat tabel master_barang_konversi
        if (!Schema::hasTable('master_barang_konversi')) {
            Schema::create('master_barang_konversi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('barang_id')->index();
                $table->unsignedBigInteger('satuan_id')->index();
                $table->decimal('faktor_pengali', 18, 4)->comment('1 Satuan Ini = faktor_pengali Satuan Dasar');
                $table->boolean('is_satuan_beli')->default(true);
                $table->boolean('is_satuan_jual')->default(false);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('barang_id')->references('id')->on('master_barang')->onDelete('cascade');
                $table->foreign('satuan_id')->references('id')->on('master_satuan')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_barang_konversi');

        if (Schema::hasTable('master_barang')) {
            Schema::table('master_barang', function (Blueprint $table) {
                if (Schema::hasColumn('master_barang', 'tipe_pelacakan')) {
                    $table->dropColumn('tipe_pelacakan');
                }
            });
        }
    }
};
