<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionDetailSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Definisi permission detail per modul
        $permissions = [
            // ---- Data Master: Departemen ----
            'lihat-departemen',
            'tambah-departemen',
            'edit-departemen',
            'hapus-departemen',

            // ---- Data Master: Jabatan ----
            'lihat-jabatan',
            'tambah-jabatan',
            'edit-jabatan',
            'hapus-jabatan',

            // ---- Data Master: Perusahaan (PT) ----
            'lihat-perusahaan',
            'tambah-perusahaan',
            'edit-perusahaan',
            'hapus-perusahaan',

            // ---- Data Master: Cabang ----
            'lihat-cabang',
            'tambah-cabang',
            'edit-cabang',
            'hapus-cabang',

            // ---- Data Master: Satuan ----
            'lihat-satuan',
            'tambah-satuan',
            'edit-satuan',
            'hapus-satuan',

            // ---- Data Master: Brand ----
            'lihat-brand',
            'tambah-brand',
            'edit-brand',
            'hapus-brand',

            // ---- Data Master: Tipe Barang ----
            'lihat-tipe-barang',
            'tambah-tipe-barang',
            'edit-tipe-barang',
            'hapus-tipe-barang',

            // ---- Data Master: Warna ----
            'lihat-warna',
            'tambah-warna',
            'edit-warna',
            'hapus-warna',

            // ---- Data Master: Jenis Gudang ----
            'lihat-jenis-gudang',
            'tambah-jenis-gudang',
            'edit-jenis-gudang',
            'hapus-jenis-gudang',

            // ---- Data Master: Kategori Barang ----
            'lihat-kategori-barang',
            'tambah-kategori-barang',
            'edit-kategori-barang',
            'hapus-kategori-barang',

            // ---- Data Master: Gudang ----
            'lihat-gudang',
            'tambah-gudang',
            'edit-gudang',
            'hapus-gudang',

            // ---- Data Master: Lokasi Rak ----
            'lihat-lokasi-rak',
            'tambah-lokasi-rak',
            'edit-lokasi-rak',
            'hapus-lokasi-rak',

            // ---- Data Master: Barang ----
            'lihat-barang',
            'tambah-barang',
            'edit-barang',
            'hapus-barang',

            // ---- Data Master: Master Bundle Produk ----
            'lihat-bundle',
            'tambah-bundle',
            'edit-bundle',
            'hapus-bundle',
            'rakit-bundle',
            'kelola-kategori-bundle',

            // ---- Data Master: Supplier ----
            'lihat-supplier',
            'tambah-supplier',
            'edit-supplier',
            'hapus-supplier',

            // ---- Data Master: Customer ----
            'lihat-customer',
            'tambah-customer',
            'edit-customer',
            'hapus-customer',

            // ---- Penjualan: Tender ----
            'lihat-tender',
            'tambah-tender',
            'edit-tender',
            'hapus-tender',
            'approve-tender',
            'lihat-hpp-tender',

            // ---- Penjualan: Order Penjualan (Sales Order / POS) ----
            'lihat-sales-order',
            'tambah-sales-order',
            'edit-sales-order',
            'hapus-sales-order',
            'cetak-sales-order',

            // ---- Pembelian: Purchase Requisition ----
            'lihat-pr',
            'tambah-pr',
            'edit-pr',
            'hapus-pr',
            'approve-pr',
            'cetak-pr',

            // ---- Pembelian: Purchase Order ----
            'lihat-po',
            'tambah-po',
            'edit-po',
            'hapus-po',
            'approve-po',
            'cetak-po',

            // ---- Gudang: Penerimaan Barang / Goods Receipt, Barang Keluar & Kartu Stok ----
            'lihat-gr',
            'tambah-gr',
            'hapus-gr',
            'lihat-jurnal-gr',
            'lihat-gi',
            'tambah-gi',
            'edit-gi',
            'approve-gi',
            'hapus-gi',
            'lihat-jurnal-gi',
            'cetak-gi',
            'lihat-transfer',
            'tambah-transfer',
            'edit-transfer',
            'approve-transfer',
            'hapus-transfer',
            'lihat-jurnal-transfer',
            'lihat-hpp-transfer',
            'lihat-kartu-stok',
            'lihat-stok-aktual',
            'lihat-hpp-stok',

            // ---- Inventory: Stock Adjustment ----
            'lihat-stok-adjustment',
            'tambah-stok-adjustment',
            'edit-stok-adjustment',
            'hapus-stok-adjustment',
            'approve-stok-adjustment',
            'lihat-hpp-stok-adjustment',
            'lihat-jurnal-stok-adjustment',

            // ---- Inventory: Stock Opname ----
            'lihat-opname',
            'tambah-opname',
            'input-fisik-opname',
            'posting-opname',
            'edit-opname',
            'setuju-opname',
            'closing-opname',
            'lihat-jurnal-opname',
            'lihat-nilai-opname',
            'hapus-opname',

            // ---- Keuangan: Pajak, Currency, Payment Term & Payment Method ----
            'lihat-pajak',
            'tambah-pajak',
            'edit-pajak',
            'hapus-pajak',
            'lihat-currency',
            'tambah-currency',
            'edit-currency',
            'hapus-currency',
            'lihat-payment-term',
            'tambah-payment-term',
            'edit-payment-term',
            'hapus-payment-term',
            'lihat-payment-method',
            'tambah-payment-method',
            'edit-payment-method',
            'hapus-payment-method',

            // ---- Pengaturan: Karyawan/User ----
            'lihat-karyawan',
            'tambah-karyawan',
            'edit-karyawan',
            'hapus-karyawan',

            // ---- Pengaturan: Hak Akses / Roles ----
            'lihat-hak-akses',
            'edit-hak-akses',

            // ---- Modul Gudang ----
            'akses-gudang',

            // ---- Modul Purchasing ----
            'akses-purchasing',

            // ---- Modul HRD ----
            'akses-hrd',

            // ---- Modul Produksi & Manufaktur (BOM & SPK) ----
            'akses-produksi',
            'lihat-bom',
            'tambah-bom',
            'detail-bom',
            'edit-bom',
            'hapus-bom',
            'approve-bom',
            'lihat-hpp-bom',
            'cetak-bom',
            'lihat-spk',
            'tambah-spk',
            'detail-spk',
            'lihat-bahan-spk',
            'edit-spk',
            'hapus-spk',
            'approve-spk',
            'lihat-realisasi-produksi',
            'kontrol-tahapan-realisasi',
            'mulai-tahapan-produksi',
            'input-hasil-harian-produksi',
            'input-realisasi-produksi',
            'lihat-riwayat-input-produksi',
            'selesai-tahapan-produksi',
            'approve-realisasi-produksi',
            'cetak-spk',
            'cetak-progress-spk',
            'lihat-pengeluaran-bahan',
            'tambah-pengeluaran-bahan',
            'detail-pengeluaran-bahan',
            'edit-pengeluaran-bahan',
            'hapus-pengeluaran-bahan',
            'cetak-pengeluaran-bahan',
            'lihat-jurnal-pengeluaran-bahan',
            'lihat-penerimaan-barang-jadi',
            'tambah-penerimaan-barang-jadi',
            'detail-penerimaan-barang-jadi',
            'edit-penerimaan-barang-jadi',
            'hapus-penerimaan-barang-jadi',
            'cetak-penerimaan-barang-jadi',
            'lihat-jurnal-penerimaan-barang-jadi',
            'sesuaikan-hpp-penerimaan-barang-jadi',
            'lihat-master-tahapan',
            'tambah-master-tahapan',
            'edit-master-tahapan',
            'hapus-master-tahapan',

            // ---- Modul QC ----
            'akses-qc',

            // ---- Modul Accounting ----
            'akses-accounting',

            // ---- Modul Sales ----
            'akses-sales',
        ];

        // Buat permission jika belum ada
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Super Admin & Owner mendapatkan semua permission
        $superAdmin = Role::findByName('Super Admin');
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }
        $owner = Role::where('name', 'Owner')->first();
        if ($owner) {
            $owner->syncPermissions(Permission::all());
        }

        // Admin mendapatkan akses penuh ke data master, karyawan, dan produksi
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $admin->syncPermissions([
                'akses-produksi', 'lihat-bom', 'tambah-bom', 'edit-bom', 'hapus-bom', 'cetak-bom',
                'lihat-spk', 'tambah-spk', 'edit-spk', 'hapus-spk', 'approve-spk', 'cetak-spk',
                'lihat-departemen', 'tambah-departemen', 'edit-departemen', 'hapus-departemen',
                'lihat-jabatan', 'tambah-jabatan', 'edit-jabatan', 'hapus-jabatan',
                'lihat-perusahaan', 'tambah-perusahaan', 'edit-perusahaan', 'hapus-perusahaan',
                'lihat-cabang', 'tambah-cabang', 'edit-cabang', 'hapus-cabang',
                'lihat-satuan', 'tambah-satuan', 'edit-satuan', 'hapus-satuan',
                'lihat-brand', 'tambah-brand', 'edit-brand', 'hapus-brand',
                'lihat-tipe-barang', 'tambah-tipe-barang', 'edit-tipe-barang', 'hapus-tipe-barang',
                'lihat-warna', 'tambah-warna', 'edit-warna', 'hapus-warna',
                'lihat-jenis-gudang', 'tambah-jenis-gudang', 'edit-jenis-gudang', 'hapus-jenis-gudang',
                'lihat-kategori-barang', 'tambah-kategori-barang', 'edit-kategori-barang', 'hapus-kategori-barang',
                'lihat-gudang', 'tambah-gudang', 'edit-gudang', 'hapus-gudang',
                'lihat-lokasi-rak', 'tambah-lokasi-rak', 'edit-lokasi-rak', 'hapus-lokasi-rak',
                'lihat-barang', 'tambah-barang', 'edit-barang', 'hapus-barang',
                'lihat-bundle', 'tambah-bundle', 'edit-bundle', 'hapus-bundle', 'rakit-bundle', 'kelola-kategori-bundle',
                'lihat-supplier', 'tambah-supplier', 'edit-supplier', 'hapus-supplier',
                'lihat-customer', 'tambah-customer', 'edit-customer', 'hapus-customer',
                'lihat-tender', 'tambah-tender', 'edit-tender', 'hapus-tender', 'approve-tender',
                'lihat-pr', 'tambah-pr', 'edit-pr', 'hapus-pr', 'approve-pr',
                'lihat-po', 'tambah-po', 'edit-po', 'hapus-po', 'approve-po',
                'lihat-gr', 'tambah-gr', 'hapus-gr', 'lihat-jurnal-gr',
                'lihat-gi', 'tambah-gi', 'hapus-gi',
                'lihat-transfer', 'tambah-transfer', 'edit-transfer', 'approve-transfer', 'hapus-transfer',
                'lihat-kartu-stok',
                'lihat-stok-aktual',
                'lihat-stok-adjustment', 'tambah-stok-adjustment', 'edit-stok-adjustment', 'hapus-stok-adjustment', 'approve-stok-adjustment', 'lihat-hpp-stok-adjustment', 'lihat-jurnal-stok-adjustment',
                'lihat-opname', 'tambah-opname', 'input-fisik-opname', 'posting-opname', 'edit-opname', 'setuju-opname', 'closing-opname', 'hapus-opname',
                'lihat-pajak', 'tambah-pajak', 'edit-pajak', 'hapus-pajak',
                'lihat-currency', 'tambah-currency', 'edit-currency', 'hapus-currency',
                'lihat-payment-term', 'tambah-payment-term', 'edit-payment-term', 'hapus-payment-term',
                'lihat-pengeluaran-bahan', 'tambah-pengeluaran-bahan', 'detail-pengeluaran-bahan', 'edit-pengeluaran-bahan', 'hapus-pengeluaran-bahan', 'cetak-pengeluaran-bahan',
                'lihat-penerimaan-barang-jadi', 'tambah-penerimaan-barang-jadi', 'detail-penerimaan-barang-jadi', 'edit-penerimaan-barang-jadi', 'hapus-penerimaan-barang-jadi', 'cetak-penerimaan-barang-jadi',
                'lihat-karyawan', 'tambah-karyawan', 'edit-karyawan',
                'lihat-hak-akses',
            ]);
        }

        // Gudang Staff: Input draf & hitung fisik & posting & Pengeluaran/Penerimaan Produksi
        $staffGudang = Role::where('name', 'Warehouse')->first();
        if ($staffGudang) {
            $staffGudang->givePermissionTo([
                'lihat-stok-adjustment',
                'tambah-stok-adjustment',
                'lihat-opname',
                'tambah-opname',
                'input-fisik-opname',
                'posting-opname',
                'lihat-pengeluaran-bahan',
                'tambah-pengeluaran-bahan',
                'detail-pengeluaran-bahan',
                'cetak-pengeluaran-bahan',
                'lihat-penerimaan-barang-jadi',
                'tambah-penerimaan-barang-jadi',
                'detail-penerimaan-barang-jadi',
                'cetak-penerimaan-barang-jadi',
                'lihat-bundle',
                'rakit-bundle',
            ]);
        }

        // Supervisor Gudang: Approve & Edit Rencana Opname & Hitung Fisik & Posting & Full Pengeluaran/Penerimaan Produksi
        $spvGudang = Role::where('name', 'Supervisor Warehouse')->first();
        if ($spvGudang) {
            $spvGudang->givePermissionTo([
                'lihat-stok-adjustment',
                'tambah-stok-adjustment',
                'edit-stok-adjustment',
                'approve-stok-adjustment',
                'lihat-hpp-stok-adjustment',
                'lihat-opname',
                'tambah-opname',
                'input-fisik-opname',
                'posting-opname',
                'edit-opname',
                'setuju-opname',
                'hapus-opname',
                'lihat-pengeluaran-bahan',
                'tambah-pengeluaran-bahan',
                'detail-pengeluaran-bahan',
                'edit-pengeluaran-bahan',
                'hapus-pengeluaran-bahan',
                'cetak-pengeluaran-bahan',
                'lihat-penerimaan-barang-jadi',
                'tambah-penerimaan-barang-jadi',
                'detail-penerimaan-barang-jadi',
                'edit-penerimaan-barang-jadi',
                'hapus-penerimaan-barang-jadi',
                'cetak-penerimaan-barang-jadi',
                'lihat-bundle',
                'tambah-bundle',
                'edit-bundle',
                'rakit-bundle',
                'kelola-kategori-bundle',
            ]);
        }

        // Purchasing hanya akses purchasing
        $purchasing = Role::where('name', 'Purchasing')->first();
        if ($purchasing) {
            $purchasing->syncPermissions([
                'lihat-departemen',
                'lihat-jabatan',
                'lihat-satuan',
                'lihat-brand',
                'lihat-tipe-barang',
                'lihat-warna',
                'lihat-kategori-barang',
                'lihat-barang',
                'lihat-supplier',
                'tambah-supplier',
                'edit-supplier',
                'hapus-supplier',
                'lihat-pr',
                'tambah-pr',
                'edit-pr',
                'hapus-pr',
                'approve-pr',
                'lihat-po',
                'tambah-po',
                'edit-po',
                'hapus-po',
                'lihat-pajak',
                'lihat-currency',
                'lihat-payment-term',
                'lihat-payment-method',
                'akses-purchasing',
            ]);
        }

        // Accounting role sync: Closing Opname & Adjustment Jurnal
        $accounting = Role::where('name', 'Accounting')->first();
        if ($accounting) {
            $accounting->givePermissionTo([
                'lihat-stok-adjustment',
                'approve-stok-adjustment',
                'lihat-hpp-stok-adjustment',
                'lihat-jurnal-stok-adjustment',
                'lihat-jurnal-gr',
                'lihat-opname',
                'closing-opname',
            ]);
        }

        // Sales role sync
        $sales = Role::where('name', 'Sales')->first();
        if ($sales) {
            $sales->syncPermissions([
                'lihat-customer',
                'tambah-customer',
                'edit-customer',
                'hapus-customer',
                'lihat-tender',
                'tambah-tender',
                'edit-tender',
                'hapus-tender',
                'lihat-sales-order',
                'tambah-sales-order',
                'edit-sales-order',
                'hapus-sales-order',
                'cetak-sales-order',
                'lihat-bundle',
                'akses-sales',
            ]);
        }

        // Produksi role sync
        $produksiRole = Role::where('name', 'Produksi')->first();
        if ($produksiRole) {
            $produksiRole->syncPermissions([
                'akses-produksi',
                'lihat-bom',
                'tambah-bom',
                'edit-bom',
                'hapus-bom',
                'cetak-bom',
                'lihat-spk',
                'tambah-spk',
                'edit-spk',
                'hapus-spk',
                'approve-spk',
                'mulai-produksi-spk',
                'selesai-produksi-spk',
                'cetak-spk',
                'lihat-bahan-spk',
                'lihat-realisasi-produksi',
                'kontrol-tahapan-realisasi',
                'mulai-tahapan-produksi',
                'input-hasil-harian-produksi',
                'input-realisasi-produksi',
                'lihat-riwayat-input-produksi',
                'selesai-tahapan-produksi',
                'approve-realisasi-produksi',
                'cetak-progress-spk',
                'lihat-pengeluaran-bahan',
                'tambah-pengeluaran-bahan',
                'detail-pengeluaran-bahan',
                'lihat-penerimaan-barang-jadi',
                'tambah-penerimaan-barang-jadi',
                'detail-penerimaan-barang-jadi',
                'lihat-master-tahapan',
                'tambah-master-tahapan',
                'edit-master-tahapan',
                'lihat-barang',
                'lihat-satuan',
                'lihat-brand',
                'lihat-warna',
                'lihat-tipe-barang',
                'lihat-bundle',
                'rakit-bundle',
                'lihat-tender',
            ]);
        }

        // Manager Produksi role sync: Akses penuh produksi, approval, HPP, SPK, BOM, Realisasi, Tahapan, Pengeluaran & Penerimaan
        $mgrProduksi = Role::firstOrCreate(['name' => 'Manager Produksi', 'guard_name' => 'web']);
        if ($mgrProduksi) {
            $mgrProduksi->syncPermissions([
                'akses-produksi',
                'akses-gudang',
                'lihat-bom',
                'tambah-bom',
                'detail-bom',
                'edit-bom',
                'hapus-bom',
                'approve-bom',
                'lihat-hpp-bom',
                'cetak-bom',
                'lihat-spk',
                'tambah-spk',
                'detail-spk',
                'lihat-bahan-spk',
                'edit-spk',
                'hapus-spk',
                'approve-spk',
                'mulai-produksi-spk',
                'selesai-produksi-spk',
                'cetak-spk',
                'lihat-realisasi-produksi',
                'kontrol-tahapan-realisasi',
                'mulai-tahapan-produksi',
                'input-hasil-harian-produksi',
                'input-realisasi-produksi',
                'lihat-riwayat-input-produksi',
                'selesai-tahapan-produksi',
                'approve-realisasi-produksi',
                'cetak-progress-spk',
                'lihat-pengeluaran-bahan',
                'tambah-pengeluaran-bahan',
                'detail-pengeluaran-bahan',
                'edit-pengeluaran-bahan',
                'hapus-pengeluaran-bahan',
                'cetak-pengeluaran-bahan',
                'lihat-jurnal-pengeluaran-bahan',
                'lihat-penerimaan-barang-jadi',
                'tambah-penerimaan-barang-jadi',
                'detail-penerimaan-barang-jadi',
                'edit-penerimaan-barang-jadi',
                'hapus-penerimaan-barang-jadi',
                'cetak-penerimaan-barang-jadi',
                'lihat-jurnal-penerimaan-barang-jadi',
                'sesuaikan-hpp-penerimaan-barang-jadi',
                'lihat-master-tahapan',
                'tambah-master-tahapan',
                'edit-master-tahapan',
                'hapus-master-tahapan',
                'lihat-barang',
                'lihat-satuan',
                'lihat-brand',
                'lihat-tipe-barang',
                'lihat-warna',
                'lihat-kategori-barang',
                'lihat-bundle',
                'tambah-bundle',
                'edit-bundle',
                'hapus-bundle',
                'rakit-bundle',
                'kelola-kategori-bundle',
                'lihat-gudang',
                'lihat-stok-aktual',
                'lihat-kartu-stok',
                'lihat-tender',
            ]);
        }

        // Finance role sync: Keuangan, Akuntansi, Pajak, Currency, Payment, Jurnal, HPP, Approval Keuangan
        $financeRole = Role::firstOrCreate(['name' => 'Finance', 'guard_name' => 'web']);
        if ($financeRole) {
            $financeRole->syncPermissions([
                'akses-accounting',
                'akses-purchasing',
                'akses-sales',
                'akses-gudang',
                'lihat-pajak',
                'tambah-pajak',
                'edit-pajak',
                'hapus-pajak',
                'lihat-currency',
                'tambah-currency',
                'edit-currency',
                'hapus-currency',
                'lihat-payment-term',
                'tambah-payment-term',
                'edit-payment-term',
                'hapus-payment-term',
                'lihat-payment-method',
                'tambah-payment-method',
                'edit-payment-method',
                'hapus-payment-method',
                'lihat-po',
                'approve-po',
                'cetak-po',
                'lihat-pr',
                'approve-pr',
                'cetak-pr',
                'lihat-gr',
                'lihat-jurnal-gr',
                'lihat-tender',
                'approve-tender',
                'lihat-hpp-tender',
                'lihat-stok-adjustment',
                'approve-stok-adjustment',
                'lihat-hpp-stok-adjustment',
                'lihat-jurnal-stok-adjustment',
                'lihat-opname',
                'closing-opname',
                'lihat-nilai-opname',
                'lihat-jurnal-opname',
                'lihat-transfer',
                'lihat-jurnal-transfer',
                'lihat-hpp-transfer',
                'lihat-kartu-stok',
                'lihat-stok-aktual',
                'lihat-hpp-stok',
                'lihat-hpp-bom',
                'lihat-jurnal-pengeluaran-bahan',
                'lihat-jurnal-penerimaan-barang-jadi',
                'lihat-sales-order',
                'cetak-sales-order',
                'lihat-supplier',
                'lihat-customer',
                'lihat-barang',
            ]);
        }

        // Manager Finance role sync: Hak penuh Finance + Approval & Otorisasi Lengkap
        $mgrFinance = Role::firstOrCreate(['name' => 'Manager Finance', 'guard_name' => 'web']);
        if ($mgrFinance) {
            $mgrFinance->syncPermissions([
                'akses-accounting',
                'akses-purchasing',
                'akses-sales',
                'akses-gudang',
                'lihat-pajak',
                'tambah-pajak',
                'edit-pajak',
                'hapus-pajak',
                'lihat-currency',
                'tambah-currency',
                'edit-currency',
                'hapus-currency',
                'lihat-payment-term',
                'tambah-payment-term',
                'edit-payment-term',
                'hapus-payment-term',
                'lihat-payment-method',
                'tambah-payment-method',
                'edit-payment-method',
                'hapus-payment-method',
                'lihat-po',
                'tambah-po',
                'edit-po',
                'hapus-po',
                'approve-po',
                'cetak-po',
                'lihat-pr',
                'tambah-pr',
                'edit-pr',
                'hapus-pr',
                'approve-pr',
                'cetak-pr',
                'lihat-gr',
                'lihat-jurnal-gr',
                'lihat-tender',
                'edit-tender',
                'approve-tender',
                'lihat-hpp-tender',
                'lihat-sales-order',
                'edit-sales-order',
                'hapus-sales-order',
                'cetak-sales-order',
                'lihat-stok-adjustment',
                'approve-stok-adjustment',
                'lihat-hpp-stok-adjustment',
                'lihat-jurnal-stok-adjustment',
                'lihat-opname',
                'closing-opname',
                'setuju-opname',
                'lihat-nilai-opname',
                'lihat-jurnal-opname',
                'lihat-transfer',
                'approve-transfer',
                'lihat-jurnal-transfer',
                'lihat-hpp-transfer',
                'lihat-kartu-stok',
                'lihat-stok-aktual',
                'lihat-hpp-stok',
                'lihat-hpp-bom',
                'lihat-jurnal-pengeluaran-bahan',
                'lihat-jurnal-penerimaan-barang-jadi',
                'sesuaikan-hpp-penerimaan-barang-jadi',
                'lihat-supplier',
                'lihat-customer',
                'lihat-barang',
                'lihat-departemen',
                'lihat-jabatan',
                'lihat-perusahaan',
                'lihat-cabang',
            ]);
        }

        $this->command->info('Permission detail berhasil dibuat dan di-assign ke role!');
    }
}
