<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Utama Faktur Pajak Keluaran
        Schema::create('faktur_pajaks', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_faktur_pajak', 50)->unique();
            $table->date('tanggal_faktur_pajak');
            $table->unsignedTinyInteger('masa_pajak');
            $table->unsignedSmallInteger('tahun_pajak');
            $table->string('jenis_transaksi', 10)->default('01'); // 01: Bukan pemungut, 02: Bendahara, 03: BUMN, 07/08: Fasilitas
            $table->enum('jenis_faktur', ['Normal', 'Pengganti', 'Batal'])->default('Normal');
            $table->boolean('is_gabungan')->default(false);

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('nama_customer', 255);
            $table->string('npwp_customer', 50)->nullable();
            $table->string('nik_customer', 50)->nullable();
            $table->text('alamat_customer')->nullable();

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();

            $table->decimal('jumlah_dpp', 15, 2)->default(0.00);
            $table->decimal('jumlah_ppn', 15, 2)->default(0.00);
            $table->decimal('jumlah_ppnbm', 15, 2)->default(0.00);

            $table->enum('status', ['Draft', 'Siap Upload', 'Approve DJP', 'Batal'])->default('Draft');
            $table->string('referensi', 255)->nullable();
            $table->text('keterangan')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('master_customers')->nullOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();

            // High-Scale Performance Indexes
            $table->index(['company_id', 'branch_id', 'status', 'tanggal_faktur_pajak'], 'idx_fp_tenant_status_date');
            $table->index(['customer_id', 'tanggal_faktur_pajak'], 'idx_fp_cust_date');
            $table->index(['masa_pajak', 'tahun_pajak'], 'idx_fp_masa_tahun');
            $table->index('tanggal_faktur_pajak', 'idx_fp_date');
            $table->index('status', 'idx_fp_status');
            $table->index('deleted_at', 'idx_fp_deleted_at');
            $table->index(['company_id', 'branch_id', 'deleted_at', 'id'], 'idx_fp_tenant_pagination');
        });

        // 2. Tabel Relasi Faktur Pajak ke Sales Invoice (Mendukung 1-ke-1 maupun Faktur Pajak Gabungan)
        Schema::create('faktur_pajak_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faktur_pajak_id');
            $table->unsignedBigInteger('sales_invoice_id');
            $table->decimal('dpp', 15, 2)->default(0.00);
            $table->decimal('ppn', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('faktur_pajak_id')->references('id')->on('faktur_pajaks')->onDelete('cascade');
            $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->onDelete('cascade');

            $table->index(['faktur_pajak_id', 'sales_invoice_id'], 'idx_fpi_fp_si');
            $table->index('sales_invoice_id', 'idx_fpi_si');
        });

        // 3. Tabel Rincian Objek Faktur (OF) untuk keperluan Export CSV e-Faktur DJP
        Schema::create('faktur_pajak_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faktur_pajak_id');
            $table->unsignedBigInteger('barang_id')->nullable();
            $table->string('nama_barang', 255);
            $table->decimal('qty', 12, 2)->default(1.00);
            $table->decimal('harga_satuan', 15, 2)->default(0.00);
            $table->decimal('total_harga', 15, 2)->default(0.00);
            $table->decimal('diskon', 15, 2)->default(0.00);
            $table->decimal('dpp', 15, 2)->default(0.00);
            $table->decimal('tarif_ppn', 5, 2)->default(11.00);
            $table->decimal('ppn', 15, 2)->default(0.00);
            $table->decimal('tarif_ppnbm', 5, 2)->default(0.00);
            $table->decimal('ppnbm', 15, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('faktur_pajak_id')->references('id')->on('faktur_pajaks')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('master_barang')->nullOnDelete();

            $table->index(['faktur_pajak_id', 'barang_id'], 'idx_fpd_fp_barang');
        });

        // 4. Tabel Manajemen Jatah Kuota NSFP (Nomor Seri Faktur Pajak dari DJP)
        Schema::create('nsfp_quotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedSmallInteger('tahun');
            $table->string('prefix', 10)->default('010'); // Kode default 010
            $table->string('nomor_awal', 30);            // Contoh: 00000001
            $table->string('nomor_akhir', 30);           // Contoh: 00000100
            $table->string('nomor_terakhir', 30)->nullable(); // Nomor terakhir yang terpakai
            $table->date('tanggal_surat')->nullable();
            $table->enum('status', ['Aktif', 'Habis', 'Nonaktif'])->default('Aktif');
            $table->text('catatan')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['company_id', 'status', 'tahun'], 'idx_nsfp_tenant_status');
        });

        // 5. Tambahkan Permission
        $permissions = [
            'lihat-faktur-pajak',
            'tambah-faktur-pajak',
            'edit-faktur-pajak',
            'hapus-faktur-pajak',
            'export-faktur-pajak',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }
        $owner = Role::where('name', 'Owner')->first();
        if ($owner) {
            $owner->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faktur_pajak_details');
        Schema::dropIfExists('faktur_pajak_invoices');
        Schema::dropIfExists('faktur_pajaks');
        Schema::dropIfExists('nsfp_quotas');

        $permissions = [
            'lihat-faktur-pajak',
            'tambah-faktur-pajak',
            'edit-faktur-pajak',
            'hapus-faktur-pajak',
            'export-faktur-pajak',
        ];

        foreach ($permissions as $perm) {
            $p = Permission::where('name', $perm)->first();
            if ($p) $p->delete();
        }
    }
};
