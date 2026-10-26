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

            // ---- Pembelian: Alokasi Biaya (Landed Cost) ----
            'lihat-alokasi-biaya',
            'tambah-alokasi-biaya',
            'detail-alokasi-biaya',
            'posting-alokasi-biaya',
            'batalkan-alokasi-biaya',

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
            'kirim-lelang-tender',
            'negosiasi-tender',
            'keputusan-final-tender',

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
            'buka-po',
            'cetak-po',

            // ---- Purchasing: Faktur Pembelian (Purchase Invoice / AP) ----
            'lihat-purchase-invoice',
            'tambah-purchase-invoice',
            'edit-purchase-invoice',
            'hapus-purchase-invoice',
            'approve-purchase-invoice',
            'cetak-purchase-invoice',

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
            'lihat-tracking-sn',
            'lihat-master-tahapan',
            'tambah-master-tahapan',
            'edit-master-tahapan',
            'hapus-master-tahapan',

            // ---- Modul QC ----
            'akses-qc',

            // ---- Modul Accounting & Keuangan ----
            'akses-accounting',
            'lihat-kas-bank',
            'detail-kas-bank',
            'tambah-kas-bank',
            'edit-kas-bank',
            'lihat-mutasi-kas-bank',
            'tambah-kas-masuk',
            'tambah-kas-keluar',
            'transfer-kas-bank',
            'batalkan-mutasi-kas-bank',
            'cetak-rekening-koran',

            // ---- Modul Sales & Piutang ----
            'akses-sales',
            'lihat-piutang',
            'cetak-piutang',
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
                'lihat-tender', 'tambah-tender', 'edit-tender', 'hapus-tender', 'approve-tender', 'kirim-lelang-tender', 'negosiasi-tender', 'keputusan-final-tender',
                'lihat-pr', 'tambah-pr', 'edit-pr', 'hapus-pr', 'approve-pr',
                'lihat-po', 'tambah-po', 'edit-po', 'hapus-po', 'approve-po', 'buka-po',
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
                'lihat-piutang', 'cetak-piutang',
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
                'lihat-alokasi-biaya',
                'tambah-alokasi-biaya',
                'detail-alokasi-biaya',
                'posting-alokasi-biaya',
                'batalkan-alokasi-biaya',
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
                'kirim-lelang-tender',
                'negosiasi-tender',
                'keputusan-final-tender',
                'lihat-sales-order',
                'tambah-sales-order',
                'edit-sales-order',
                'hapus-sales-order',
                'cetak-sales-order',
                'lihat-bundle',
                'akses-sales',
                'lihat-piutang',
                'cetak-piutang',
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
                'buka-po',
                'cetak-po',
                'lihat-pr',
                'approve-pr',
                'cetak-pr',
                'lihat-gr',
                'lihat-jurnal-gr',
                'lihat-tender',
                'approve-tender',
                'kirim-lelang-tender',
                'negosiasi-tender',
                'keputusan-final-tender',
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
                'lihat-piutang',
                'cetak-piutang',
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
                'buka-po',
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
                'kirim-lelang-tender',
                'negosiasi-tender',
                'keputusan-final-tender',
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
                'lihat-piutang',
                'cetak-piutang',
                'lihat-departemen',
                'lihat-jabatan',
                'lihat-perusahaan',
                'lihat-cabang',
            ]);
        }

        // Director Produksi role sync
        $dirProduksi = Role::firstOrCreate(['name' => 'Director Produksi', 'guard_name' => 'web']);
        if ($dirProduksi) {
            $dirProduksi->syncPermissions([
                'akses-produksi', 'akses-gudang', 'lihat-dashboard',
                'lihat-bom', 'detail-bom', 'approve-bom', 'lihat-hpp-bom', 'cetak-bom',
                'lihat-spk', 'detail-spk', 'lihat-bahan-spk', 'approve-spk', 'cetak-spk',
                'lihat-realisasi-produksi', 'kontrol-tahapan-realisasi', 'approve-realisasi-produksi', 'cetak-progress-spk',
                'lihat-pengeluaran-bahan', 'detail-pengeluaran-bahan', 'lihat-jurnal-pengeluaran-bahan',
                'lihat-penerimaan-barang-jadi', 'detail-penerimaan-barang-jadi', 'lihat-jurnal-penerimaan-barang-jadi', 'sesuaikan-hpp-penerimaan-barang-jadi',
                'lihat-master-tahapan', 'lihat-barang', 'lihat-satuan', 'lihat-brand', 'lihat-tipe-barang', 'lihat-warna',
                'lihat-kategori-barang', 'lihat-bundle', 'lihat-gudang', 'lihat-stok-aktual', 'lihat-kartu-stok', 'lihat-tender',
            ]);
        }

        // Director Accounting role sync
        $dirAccounting = Role::firstOrCreate(['name' => 'Director Accounting', 'guard_name' => 'web']);
        if ($dirAccounting) {
            $dirAccounting->syncPermissions([
                'akses-accounting', 'lihat-dashboard',
                'lihat-kas-bank', 'detail-kas-bank', 'lihat-mutasi-kas-bank', 'cetak-rekening-koran',
                'lihat-jurnal-umum', 'cetak-jurnal-umum',
                'lihat-petty-cash', 'detail-petty-cash', 'filter-petty-cash', 'export-excel-petty-cash', 'cetak-pdf-petty-cash',
                'lihat-pengaturan-akuntansi', 'edit-pengaturan-akuntansi',
                'lihat-jurnal-gr', 'lihat-jurnal-delivery-order', 'lihat-jurnal-sales-payment',
                'lihat-jurnal-stok-adjustment', 'lihat-jurnal-opname', 'lihat-jurnal-transfer',
                'lihat-alokasi-biaya', 'detail-alokasi-biaya', 'lihat-jurnal-alokasi-biaya',
                'lihat-pajak', 'lihat-currency', 'lihat-payment-term', 'lihat-payment-method',
            ]);
        }

        // Director Sales role sync
        $dirSales = Role::firstOrCreate(['name' => 'Director Sales', 'guard_name' => 'web']);
        if ($dirSales) {
            $dirSales->syncPermissions([
                'akses-sales', 'lihat-dashboard',
                'lihat-customer', 'lihat-tender', 'approve-tender',
                'lihat-penawaran', 'approve-penawaran',
                'lihat-sales-order', 'cetak-sales-order',
                'lihat-delivery-order', 'approve-delivery-order',
                'lihat-sales-invoice', 'approve-sales-invoice',
                'lihat-sales-payment', 'approve-sales-payment',
                'lihat-piutang', 'cetak-piutang', 'lihat-bundle', 'lihat-barang',
            ]);
        }

        // Manager Sales role sync
        $mgrSales = Role::firstOrCreate(['name' => 'Manager Sales', 'guard_name' => 'web']);
        if ($mgrSales) {
            $mgrSales->syncPermissions([
                'akses-sales', 'lihat-dashboard',
                'lihat-customer', 'tambah-customer', 'edit-customer',
                'lihat-tender', 'tambah-tender', 'edit-tender', 'approve-tender', 'negosiasi-tender', 'keputusan-final-tender',
                'lihat-penawaran', 'tambah-penawaran', 'edit-penawaran', 'approve-penawaran', 'cetak-penawaran', 'revisi-penawaran',
                'lihat-sales-order', 'tambah-sales-order', 'edit-sales-order', 'cetak-sales-order',
                'lihat-delivery-order', 'tambah-delivery-order', 'edit-delivery-order', 'approve-delivery-order',
                'lihat-sales-invoice', 'tambah-sales-invoice', 'edit-sales-invoice', 'approve-sales-invoice',
                'lihat-sales-payment', 'approve-sales-payment',
                'lihat-piutang', 'cetak-piutang',
                'lihat-bundle', 'lihat-barang',
            ]);
        }

        // Staf Gudang role sync
        $stafGudang = Role::firstOrCreate(['name' => 'Staf Gudang', 'guard_name' => 'web']);
        if ($stafGudang) {
            $stafGudang->syncPermissions([
                'akses-gudang',
                'lihat-barang', 'lihat-satuan', 'lihat-brand', 'lihat-tipe-barang', 'lihat-warna', 'lihat-kategori-barang',
                'lihat-gr', 'tambah-gr',
                'lihat-gi', 'tambah-gi', 'cetak-gi',
                'lihat-stok-aktual', 'lihat-kartu-stok',
                'lihat-opname', 'tambah-opname', 'input-fisik-opname',
                'lihat-transfer', 'tambah-transfer',
                'lihat-pengeluaran-bahan', 'tambah-pengeluaran-bahan', 'detail-pengeluaran-bahan',
                'lihat-penerimaan-barang-jadi', 'tambah-penerimaan-barang-jadi', 'detail-penerimaan-barang-jadi',
                'lihat-bundle', 'rakit-bundle',
            ]);
        }

        // Manager Purchasing role sync
        $mgrPurchasing = Role::firstOrCreate(['name' => 'Manager Purchasing', 'guard_name' => 'web']);
        if ($mgrPurchasing) {
            $mgrPurchasing->syncPermissions([
                'akses-purchasing', 'lihat-dashboard-purchasing',
                'lihat-supplier', 'tambah-supplier', 'edit-supplier', 'hapus-supplier',
                'lihat-pr', 'tambah-pr', 'edit-pr', 'hapus-pr', 'approve-pr',
                'lihat-po', 'tambah-po', 'edit-po', 'hapus-po', 'approve-po', 'cetak-po',
                'lihat-purchase-invoice', 'approve-purchase-invoice',
                'lihat-pembayaran-utang', 'lihat-hutang', 'cetak-hutang',
                'lihat-alokasi-biaya', 'tambah-alokasi-biaya', 'detail-alokasi-biaya', 'posting-alokasi-biaya', 'batalkan-alokasi-biaya',
                'lihat-barang', 'lihat-satuan', 'lihat-pajak', 'lihat-currency', 'lihat-payment-term', 'lihat-payment-method',
            ]);
        }

        // PIC Helm & PIC Garmen role sync (Operasional Produksi)
        $picPerms = [
            'akses-produksi', 'lihat-dashboard-produksi',
            'lihat-satuan', 'lihat-brand', 'lihat-tipe-barang', 'lihat-warna', 'lihat-barang', 'lihat-tender',
            'lihat-bom', 'tambah-bom', 'detail-bom', 'edit-bom', 'cetak-bom',
            'lihat-spk', 'tambah-spk', 'detail-spk', 'lihat-bahan-spk', 'edit-spk', 'cetak-spk',
            'mulai-produksi-spk', 'selesai-produksi-spk',
            'lihat-realisasi-produksi', 'kontrol-tahapan-realisasi', 'mulai-tahapan-produksi',
            'input-hasil-harian-produksi', 'input-realisasi-produksi', 'lihat-riwayat-input-produksi', 'selesai-tahapan-produksi', 'cetak-progress-spk',
            'lihat-pengeluaran-bahan', 'tambah-pengeluaran-bahan', 'detail-pengeluaran-bahan',
            'lihat-penerimaan-barang-jadi', 'tambah-penerimaan-barang-jadi', 'detail-penerimaan-barang-jadi',
            'lihat-master-tahapan', 'tambah-master-tahapan', 'edit-master-tahapan',
            'lihat-bundle', 'rakit-bundle',
        ];

        $picHelm = Role::firstOrCreate(['name' => 'PIC Helm', 'guard_name' => 'web']);
        if ($picHelm) {
            $picHelm->syncPermissions(Permission::whereIn('name', $picPerms)->pluck('name')->toArray());
        }

        $picGarmen = Role::firstOrCreate(['name' => 'PIC Garmen', 'guard_name' => 'web']);
        if ($picGarmen) {
            $picGarmen->syncPermissions(Permission::whereIn('name', $picPerms)->pluck('name')->toArray());
        }

        // Ruang Meeting role sync (Display/Monitor Read-Only)
        $meetingPerms = [
            'lihat-dashboard', 'lihat-dashboard-produksi', 'lihat-dashboard-sales',
            'lihat-dashboard-purchasing', 'lihat-dashboard-inventori', 'lihat-dashboard-keuangan',
            'lihat-realisasi-produksi', 'kontrol-tahapan-realisasi',
            'lihat-spk', 'detail-spk', 'lihat-bom', 'detail-bom',
            'lihat-stok-aktual', 'lihat-kartu-stok', 'lihat-barang', 'lihat-bundle',
            'lihat-sales-order', 'lihat-tender',
        ];

        $ruangMeeting = Role::firstOrCreate(['name' => 'Ruang Meeting', 'guard_name' => 'web']);
        if ($ruangMeeting) {
            $ruangMeeting->syncPermissions(Permission::whereIn('name', $meetingPerms)->pluck('name')->toArray());
        }

        $this->command->info('Permission detail berhasil dibuat dan di-assign ke role!');
    }
}
