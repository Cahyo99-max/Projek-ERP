<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolePermissionController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-hak-akses');
        $roles = Role::with('permissions')->get();
        return view('settings.role_permissions', compact('roles'));
    }

    // ════════════════════════════════════════════════════════════════
    // PERMISSION → SUBGROUP (tampil di UI matriks)
    // ════════════════════════════════════════════════════════════════
    private function getSubgroupName(string $permissionName): string
    {
        $map = [
            // Modul Akses Utama
            'akses-gudang'            => 'Akses Modul Utama',
            'akses-purchasing'        => 'Akses Modul Utama',
            'akses-hrd'               => 'Akses Modul Utama',
            'akses-produksi'          => 'Akses Modul Utama',
            'akses-qc'                => 'Akses Modul Utama',
            'akses-accounting'        => 'Akses Modul Utama',
            'akses-sales'             => 'Akses Modul Utama',
            'lihat-dashboard'            => 'Akses Dashboard',
            'lihat-dashboard-purchasing' => 'Akses Dashboard',
            'lihat-monitor-aktif'        => 'Akses Dashboard',
            'lihat-log-aktivitas-user'   => 'Akses Dashboard',

            // Produksi & Manufaktur (BOM, SPK, Realisasi, Bahan, Barang Jadi, Tahapan)
            'lihat-bom'      => 'Resep Produksi (Bill of Materials / BOM)',
            'tambah-bom'     => 'Resep Produksi (Bill of Materials / BOM)',
            'detail-bom'     => 'Resep Produksi (Bill of Materials / BOM)',
            'edit-bom'       => 'Resep Produksi (Bill of Materials / BOM)',
            'hapus-bom'      => 'Resep Produksi (Bill of Materials / BOM)',
            'approve-bom'    => 'Resep Produksi (Bill of Materials / BOM)',
            'lihat-hpp-bom'  => 'Resep Produksi (Bill of Materials / BOM)',
            'cetak-bom'      => 'Resep Produksi (Bill of Materials / BOM)',

            'lihat-spk'       => 'Surat Perintah Kerja (SPK / Work Order)',
            'tambah-spk'      => 'Surat Perintah Kerja (SPK / Work Order)',
            'detail-spk'      => 'Surat Perintah Kerja (SPK / Work Order)',
            'lihat-bahan-spk' => 'Surat Perintah Kerja (SPK / Work Order)',
            'edit-spk'        => 'Surat Perintah Kerja (SPK / Work Order)',
            'hapus-spk'       => 'Surat Perintah Kerja (SPK / Work Order)',
            'approve-spk'     => 'Surat Perintah Kerja (SPK / Work Order)',
            'mulai-produksi-spk' => 'Surat Perintah Kerja (SPK / Work Order)',
            'selesai-produksi-spk' => 'Surat Perintah Kerja (SPK / Work Order)',
            'cetak-spk'       => 'Surat Perintah Kerja (SPK / Work Order)',

            'lihat-realisasi-produksi'     => 'Realisasi Produksi & Kontrol Tahapan',
            'kontrol-tahapan-realisasi'    => 'Realisasi Produksi & Kontrol Tahapan',
            'mulai-tahapan-produksi'       => 'Realisasi Produksi & Kontrol Tahapan',
            'input-hasil-harian-produksi'  => 'Realisasi Produksi & Kontrol Tahapan',
            'input-realisasi-produksi'     => 'Realisasi Produksi & Kontrol Tahapan',
            'lihat-riwayat-input-produksi' => 'Realisasi Produksi & Kontrol Tahapan',
            'selesai-tahapan-produksi'     => 'Realisasi Produksi & Kontrol Tahapan',
            'approve-realisasi-produksi'   => 'Realisasi Produksi & Kontrol Tahapan',
            'cetak-progress-spk'           => 'Realisasi Produksi & Kontrol Tahapan',

            'lihat-pengeluaran-bahan'        => 'Pengeluaran Bahan Baku SPK',
            'tambah-pengeluaran-bahan'       => 'Pengeluaran Bahan Baku SPK',
            'detail-pengeluaran-bahan'       => 'Pengeluaran Bahan Baku SPK',
            'edit-pengeluaran-bahan'         => 'Pengeluaran Bahan Baku SPK',
            'hapus-pengeluaran-bahan'        => 'Pengeluaran Bahan Baku SPK',
            'cetak-pengeluaran-bahan'        => 'Pengeluaran Bahan Baku SPK',
            'lihat-jurnal-pengeluaran-bahan' => 'Pengeluaran Bahan Baku SPK',

            'lihat-penerimaan-barang-jadi'        => 'Penerimaan Barang Jadi SPK',
            'tambah-penerimaan-barang-jadi'       => 'Penerimaan Barang Jadi SPK',
            'detail-penerimaan-barang-jadi'       => 'Penerimaan Barang Jadi SPK',
            'edit-penerimaan-barang-jadi'         => 'Penerimaan Barang Jadi SPK',
            'hapus-penerimaan-barang-jadi'        => 'Penerimaan Barang Jadi SPK',
            'cetak-penerimaan-barang-jadi'        => 'Penerimaan Barang Jadi SPK',
            'lihat-jurnal-penerimaan-barang-jadi' => 'Penerimaan Barang Jadi SPK',
            'sesuaikan-hpp-penerimaan-barang-jadi' => 'Penerimaan Barang Jadi SPK',

            'lihat-master-tahapan'  => 'Master Tahapan Produksi',
            'tambah-master-tahapan' => 'Master Tahapan Produksi',
            'edit-master-tahapan'   => 'Master Tahapan Produksi',
            'hapus-master-tahapan'  => 'Master Tahapan Produksi',

            // Stock Adjustment
            'lihat-stok-adjustment'       => 'Penyesuaian Stok (Stock Adjustment)',
            'tambah-stok-adjustment'      => 'Penyesuaian Stok (Stock Adjustment)',
            'edit-stok-adjustment'        => 'Penyesuaian Stok (Stock Adjustment)',
            'hapus-stok-adjustment'       => 'Penyesuaian Stok (Stock Adjustment)',
            'approve-stok-adjustment'     => 'Penyesuaian Stok (Stock Adjustment)',
            'lihat-hpp-stok-adjustment'   => 'Penyesuaian Stok (Stock Adjustment)',
            'lihat-jurnal-stok-adjustment'=> 'Penyesuaian Stok (Stock Adjustment)',

            // Goods Issue (GI)
            'lihat-gi'         => 'Barang Keluar (Goods Issue / GI)',
            'tambah-gi'        => 'Barang Keluar (Goods Issue / GI)',
            'edit-gi'          => 'Barang Keluar (Goods Issue / GI)',
            'approve-gi'       => 'Barang Keluar (Goods Issue / GI)',
            'hapus-gi'         => 'Barang Keluar (Goods Issue / GI)',
            'lihat-jurnal-gi'  => 'Barang Keluar (Goods Issue / GI)',
            'cetak-gi'         => 'Barang Keluar (Goods Issue / GI)',

            // Transfer Gudang
            'lihat-transfer'        => 'Transfer Gudang',
            'tambah-transfer'       => 'Transfer Gudang',
            'edit-transfer'         => 'Transfer Gudang',
            'approve-transfer'      => 'Transfer Gudang',
            'hapus-transfer'        => 'Transfer Gudang',
            'lihat-jurnal-transfer' => 'Transfer Gudang',
            'lihat-hpp-transfer'    => 'Transfer Gudang',

            // Stock Opname
            'lihat-opname'         => 'Stock Opname',
            'tambah-opname'        => 'Stock Opname',
            'input-fisik-opname'   => 'Stock Opname',
            'posting-opname'       => 'Stock Opname',
            'edit-opname'          => 'Stock Opname',
            'hapus-opname'         => 'Stock Opname',
            'setuju-opname'        => 'Stock Opname',
            'closing-opname'       => 'Stock Opname',
            'lihat-jurnal-opname'  => 'Stock Opname',
            'lihat-nilai-opname'   => 'Stock Opname',

            // Saldo Awal Persediaan (Opening Stock)
            'lihat-opening-stock'      => 'Saldo Awal Persediaan (Opening Stock)',
            'tambah-opening-stock'     => 'Saldo Awal Persediaan (Opening Stock)',
            'konfirmasi-opening-stock' => 'Saldo Awal Persediaan (Opening Stock)',
            'hapus-opening-stock'      => 'Saldo Awal Persediaan (Opening Stock)',

            // Stok & Laporan Persediaan
            'lihat-kartu-stok'         => 'Laporan & Ringkasan Stok Persediaan',
            'lihat-stok-aktual'        => 'Laporan & Ringkasan Stok Persediaan',
            'lihat-hpp-stok'           => 'Laporan & Ringkasan Stok Persediaan',

            // Purchasing
            'lihat-gr'        => 'Penerimaan Barang (Goods Receipt / GR)',
            'tambah-gr'       => 'Penerimaan Barang (Goods Receipt / GR)',
            'hapus-gr'        => 'Penerimaan Barang (Goods Receipt / GR)',
            'lihat-jurnal-gr' => 'Penerimaan Barang (Goods Receipt / GR)',
            'lihat-pr'    => 'Purchase Request (PR)',
            'tambah-pr'   => 'Purchase Request (PR)',
            'edit-pr'     => 'Purchase Request (PR)',
            'hapus-pr'    => 'Purchase Request (PR)',
            'approve-pr'  => 'Purchase Request (PR)',
            'cetak-pr'    => 'Purchase Request (PR)',
            'lihat-po'    => 'Purchase Order (PO)',
            'tambah-po'   => 'Purchase Order (PO)',
            'edit-po'     => 'Purchase Order (PO)',
            'hapus-po'    => 'Purchase Order (PO)',
            'approve-po'  => 'Purchase Order (PO)',
            'cetak-po'    => 'Purchase Order (PO)',
            'lihat-pembayaran-utang'    => 'Pembayaran Utang (Purchase Payment)',
            'tambah-pembayaran-utang'   => 'Pembayaran Utang (Purchase Payment)',
            'detail-pembayaran-utang'   => 'Pembayaran Utang (Purchase Payment)',
            'edit-pembayaran-utang'     => 'Pembayaran Utang (Purchase Payment)',
            'approve-pembayaran-utang'  => 'Pembayaran Utang (Purchase Payment)',
            'batalkan-pembayaran-utang' => 'Pembayaran Utang (Purchase Payment)',
            'cetak-pembayaran-utang'    => 'Pembayaran Utang (Purchase Payment)',
            'lihat-supplier'  => 'Data Supplier',
            'tambah-supplier' => 'Data Supplier',
            'edit-supplier'   => 'Data Supplier',
            'hapus-supplier'  => 'Data Supplier',

            // Sales & Order Penjualan
            'lihat-customer'    => 'Data Pelanggan (Customer)',
            'tambah-customer'   => 'Data Pelanggan (Customer)',
            'edit-customer'     => 'Data Pelanggan (Customer)',
            'hapus-customer'    => 'Data Pelanggan (Customer)',
            'lihat-tender'      => 'Tender & Proyek',
            'tambah-tender'     => 'Tender & Proyek',
            'edit-tender'       => 'Tender & Proyek',
            'hapus-tender'      => 'Tender & Proyek',
            'approve-tender'    => 'Tender & Proyek',
            'lihat-penawaran'     => 'Penawaran Harga (Sales Quotation)',
            'tambah-penawaran'    => 'Penawaran Harga (Sales Quotation)',
            'edit-penawaran'      => 'Penawaran Harga (Sales Quotation)',
            'hapus-penawaran'     => 'Penawaran Harga (Sales Quotation)',
            'revisi-penawaran'    => 'Penawaran Harga (Sales Quotation)',
            'approve-penawaran'   => 'Penawaran Harga (Sales Quotation)',
            'cetak-penawaran'     => 'Penawaran Harga (Sales Quotation)',
            'lihat-hpp-penawaran' => 'Penawaran Harga (Sales Quotation)',
            'lihat-sales-order' => 'Order Penjualan (Sales Order / POS)',
            'tambah-sales-order'=> 'Order Penjualan (Sales Order / POS)',
            'edit-sales-order'  => 'Order Penjualan (Sales Order / POS)',
            'hapus-sales-order' => 'Order Penjualan (Sales Order / POS)',
            'cetak-sales-order' => 'Order Penjualan (Sales Order / POS)',

            // Master Barang & Produk
            'lihat-barang'           => 'Master Data Barang',
            'tambah-barang'          => 'Master Data Barang',
            'edit-barang'            => 'Master Data Barang',
            'hapus-barang'           => 'Master Data Barang',

            'lihat-kategori-barang'  => 'Master Kategori Barang',
            'tambah-kategori-barang' => 'Master Kategori Barang',
            'edit-kategori-barang'   => 'Master Kategori Barang',
            'hapus-kategori-barang'  => 'Master Kategori Barang',

            'lihat-brand'            => 'Master Brand / Merk',
            'tambah-brand'           => 'Master Brand / Merk',
            'edit-brand'             => 'Master Brand / Merk',
            'hapus-brand'            => 'Master Brand / Merk',

            'lihat-satuan'           => 'Master Satuan',
            'tambah-satuan'          => 'Master Satuan',
            'edit-satuan'            => 'Master Satuan',
            'hapus-satuan'           => 'Master Satuan',

            'lihat-tipe-barang'      => 'Master Tipe Barang',
            'tambah-tipe-barang'     => 'Master Tipe Barang',
            'edit-tipe-barang'       => 'Master Tipe Barang',
            'hapus-tipe-barang'      => 'Master Tipe Barang',

            'lihat-warna'            => 'Master Warna',
            'tambah-warna'           => 'Master Warna',
            'edit-warna'             => 'Master Warna',
            'hapus-warna'            => 'Master Warna',

            // Master Bundle Produk
            'lihat-bundle'           => 'Master Bundle Produk',
            'tambah-bundle'          => 'Master Bundle Produk',
            'edit-bundle'            => 'Master Bundle Produk',
            'hapus-bundle'           => 'Master Bundle Produk',
            'rakit-bundle'           => 'Master Bundle Produk',
            'kelola-kategori-bundle' => 'Master Bundle Produk',

            // Master Gudang & Cabang
            'lihat-gudang'        => 'Master Gudang',
            'tambah-gudang'       => 'Master Gudang',
            'edit-gudang'         => 'Master Gudang',
            'hapus-gudang'        => 'Master Gudang',

            'lihat-jenis-gudang'  => 'Master Jenis Gudang',
            'tambah-jenis-gudang' => 'Master Jenis Gudang',
            'edit-jenis-gudang'   => 'Master Jenis Gudang',
            'hapus-jenis-gudang'  => 'Master Jenis Gudang',

            'lihat-cabang'        => 'Master Cabang',
            'tambah-cabang'       => 'Master Cabang',
            'edit-cabang'         => 'Master Cabang',
            'hapus-cabang'        => 'Master Cabang',

            // Master Organisasi & Departemen
            'lihat-departemen'    => 'Master Departemen',
            'tambah-departemen'   => 'Master Departemen',
            'edit-departemen'     => 'Master Departemen',
            'hapus-departemen'    => 'Master Departemen',

            'lihat-jabatan'       => 'Master Jabatan',
            'tambah-jabatan'      => 'Master Jabatan',
            'edit-jabatan'        => 'Master Jabatan',
            'hapus-jabatan'       => 'Master Jabatan',

            // Keuangan, Pajak & Pembayaran
            'lihat-pajak'         => 'Master Pajak',
            'tambah-pajak'        => 'Master Pajak',
            'edit-pajak'          => 'Master Pajak',
            'hapus-pajak'         => 'Master Pajak',

            'lihat-currency'      => 'Master Mata Uang (Currency)',
            'tambah-currency'     => 'Master Mata Uang (Currency)',
            'edit-currency'       => 'Master Mata Uang (Currency)',
            'hapus-currency'      => 'Master Mata Uang (Currency)',

            'lihat-payment-term'  => 'Master Syarat Pembayaran (Payment Term)',
            'tambah-payment-term' => 'Master Syarat Pembayaran (Payment Term)',
            'edit-payment-term'   => 'Master Syarat Pembayaran (Payment Term)',
            'hapus-payment-term'  => 'Master Syarat Pembayaran (Payment Term)',

            'lihat-payment-method'  => 'Master Metode Pembayaran (Payment Method)',
            'tambah-payment-method' => 'Master Metode Pembayaran (Payment Method)',
            'edit-payment-method'   => 'Master Metode Pembayaran (Payment Method)',
            'hapus-payment-method'  => 'Master Metode Pembayaran (Payment Method)',

            // Karyawan
            'lihat-karyawan'  => 'Pengguna & Karyawan',
            'tambah-karyawan' => 'Pengguna & Karyawan',
            'edit-karyawan'   => 'Pengguna & Karyawan',
            'hapus-karyawan'  => 'Pengguna & Karyawan',

            // Hak Akses
            'lihat-hak-akses' => 'Pengaturan Hak Akses',
            'edit-hak-akses'  => 'Pengaturan Hak Akses',

            // Audit & Keamanan
            'lihat-monitor'      => 'Audit & Keamanan System',
            'lihat-log-aktivitas'=> 'Audit & Keamanan System',
        ];

        return $map[$permissionName] ?? 'Lain-lain';
    }

    // ════════════════════════════════════════════════════════════════
    // PERMISSION → MODULE KEY (pengelompokan modul besar)
    // ════════════════════════════════════════════════════════════════
    private function getModuleKey(string $permissionName): string
    {
        // Akses Modul Utama
        $aksesModul = ['akses-gudang','akses-purchasing','akses-hrd','akses-produksi',
                       'akses-qc','akses-accounting','akses-sales',
                       'lihat-dashboard','lihat-dashboard-purchasing',
                       'lihat-monitor-aktif','lihat-log-aktivitas-user'];
        if (in_array($permissionName, $aksesModul)) return 'akses';

        // Inventory & Stok
        $inventory = [
            'lihat-stok-adjustment','tambah-stok-adjustment','edit-stok-adjustment',
            'hapus-stok-adjustment','approve-stok-adjustment','lihat-hpp-stok-adjustment',
            'lihat-jurnal-stok-adjustment',
            'lihat-gi','tambah-gi','edit-gi','approve-gi','hapus-gi','lihat-jurnal-gi','cetak-gi',
            'lihat-transfer','tambah-transfer','edit-transfer','approve-transfer','hapus-transfer',
            'lihat-jurnal-transfer','lihat-hpp-transfer',
            'lihat-opname','tambah-opname','input-fisik-opname','posting-opname',
            'edit-opname','hapus-opname','setuju-opname','closing-opname',
            'lihat-jurnal-opname','lihat-nilai-opname',
            'lihat-opening-stock','tambah-opening-stock','konfirmasi-opening-stock','hapus-opening-stock',
            'lihat-kartu-stok','lihat-stok-aktual','lihat-hpp-stok',
        ];
        if (in_array($permissionName, $inventory)) return 'inventory';

        // Modul Produksi (BOM, SPK, Pengeluaran Bahan Baku, Realisasi, Penerimaan Barang Jadi, Tahapan)
        if (str_contains($permissionName, 'bom') || 
            str_contains($permissionName, 'spk') || 
            str_contains($permissionName, 'realisasi') || 
            str_contains($permissionName, 'pengeluaran-bahan') || 
            str_contains($permissionName, 'penerimaan-barang-jadi') || 
            str_contains($permissionName, 'tahapan')) {
            return 'produksi';
        }

        // Purchasing
        $purchasing = [
            'lihat-gr','tambah-gr','hapus-gr','lihat-jurnal-gr',
            'lihat-pr','tambah-pr','edit-pr','hapus-pr','approve-pr','cetak-pr',
            'lihat-po','tambah-po','edit-po','hapus-po','approve-po','cetak-po',
            'lihat-pembayaran-utang','tambah-pembayaran-utang','detail-pembayaran-utang','edit-pembayaran-utang','posting-pembayaran-utang','batalkan-pembayaran-utang','cetak-pembayaran-utang',
            'lihat-supplier','tambah-supplier','edit-supplier','hapus-supplier',
        ];
        if (in_array($permissionName, $purchasing) || str_contains($permissionName, 'pembayaran-utang')) return 'purchasing';

        // Sales
        $sales = [
            'lihat-customer','tambah-customer','edit-customer','hapus-customer',
            'lihat-tender','tambah-tender','edit-tender','hapus-tender','approve-tender',
            'lihat-penawaran','tambah-penawaran','edit-penawaran','hapus-penawaran','revisi-penawaran','approve-penawaran','cetak-penawaran','lihat-hpp-penawaran',
            'lihat-sales-order','tambah-sales-order','edit-sales-order','hapus-sales-order','cetak-sales-order',
        ];
        if (in_array($permissionName, $sales)) return 'sales';

        // Karyawan
        $karyawan = ['lihat-karyawan','tambah-karyawan','edit-karyawan','hapus-karyawan'];
        if (in_array($permissionName, $karyawan)) return 'karyawan';

        // Hak Akses
        if (in_array($permissionName, ['lihat-hak-akses','edit-hak-akses'])) return 'hak';

        // Audit & Keamanan
        if (in_array($permissionName, ['lihat-monitor','lihat-log-aktivitas'])) return 'keamanan_audit';

        // Master Produk & Bundle
        if (str_starts_with($permissionName, 'lihat-barang') || str_starts_with($permissionName, 'tambah-barang') ||
            str_starts_with($permissionName, 'edit-barang') || str_starts_with($permissionName, 'hapus-barang') ||
            str_contains($permissionName, 'bundle') ||
            str_contains($permissionName, 'kategori-barang') || str_contains($permissionName, 'tipe-barang') ||
            str_contains($permissionName, 'satuan') || str_contains($permissionName, 'brand') ||
            str_contains($permissionName, 'warna')) {
            return 'master_produk';
        }

        // Master Gudang
        if (str_contains($permissionName, 'gudang') || str_contains($permissionName, 'jenis-gudang') ||
            str_contains($permissionName, 'cabang') || str_contains($permissionName, 'lokasi-rak')) {
            return 'master_gudang';
        }

        // Master Organisasi
        if (str_contains($permissionName, 'departemen') || str_contains($permissionName, 'jabatan') ||
            str_contains($permissionName, 'perusahaan')) {
            return 'master_organisasi';
        }

        // Master Keuangan
        if (str_contains($permissionName, 'pajak') || str_contains($permissionName, 'currency') ||
            str_contains($permissionName, 'payment')) {
            return 'master_keuangan';
        }

        return 'lainnya';
    }

    public function getPermissions(Role $role)
    {
        $this->authorize('lihat-hak-akses');

        $allPermissions = \Spatie\Permission\Models\Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        // Grouping module map using easy to understand Indonesian labels
        $moduleMap = [
            'akses'              => ['label' => 'Hak Akses Modul Utama',          'icon' => 'bi-grid',          'color' => 'warning'],
            'produksi'           => ['label' => 'Master Produksi',                'icon' => 'bi-gear-wide-connected', 'color' => 'success'],
            'master_produk'      => ['label' => 'Master Barang & Produk',          'icon' => 'bi-boxes',         'color' => 'primary'],
            'purchasing'         => ['label' => 'Modul Purchasing & Pembelian',    'icon' => 'bi-cart-check',    'color' => 'success'],
            'sales'              => ['label' => 'Modul Sales & Pelanggan',         'icon' => 'bi-briefcase',     'color' => 'info'],
            'inventory'          => ['label' => 'Modul Inventory & Stok',          'icon' => 'bi-house-gear',    'color' => 'warning'],
            'master_gudang'      => ['label' => 'Master Gudang & Cabang',          'icon' => 'bi-building-gear', 'color' => 'info'],
            'master_organisasi'  => ['label' => 'Master Organisasi & Departemen',  'icon' => 'bi-diagram-3',     'color' => 'dark'],
            'karyawan'           => ['label' => 'Akun Pengguna & Karyawan',        'icon' => 'bi-people',        'color' => 'success'],
            'master_keuangan'    => ['label' => 'Keuangan, Pajak & Mata Uang',     'icon' => 'bi-wallet2',       'color' => 'secondary'],
            'hak'                => ['label' => 'Pengaturan Akses Pengguna',       'icon' => 'bi-shield-lock',   'color' => 'danger'],
            'keamanan_audit'     => ['label' => 'Keamanan & Audit Trail',          'icon' => 'bi-shield-shaded', 'color' => 'danger'],
            'lainnya'            => ['label' => 'Modul Lainnya',                   'icon' => 'bi-circle',        'color' => 'secondary'],
        ];

        $modules = [];

        foreach ($allPermissions as $perm) {
            $name = $perm->name;
            $key  = $this->getModuleKey($name);

            if (!isset($modules[$key])) {
                $modules[$key] = [
                    'label'     => $moduleMap[$key]['label']  ?? ucfirst($key),
                    'icon'      => $moduleMap[$key]['icon']   ?? 'bi-circle',
                    'color'     => $moduleMap[$key]['color']  ?? 'secondary',
                    'subgroups' => [],
                ];
            }

            $subgroupName = $this->getSubgroupName($name);
            if (!isset($modules[$key]['subgroups'][$subgroupName])) {
                $modules[$key]['subgroups'][$subgroupName] = [];
            }
            $modules[$key]['subgroups'][$subgroupName][] = $perm;
        }

        // Sort subgroups in Master Produk & Barang module
        if (isset($modules['master_produk']['subgroups'])) {
            $produkSubgroupOrder = [
                'Master Data Barang',
                'Master Kategori Barang',
                'Master Brand / Merk',
                'Master Satuan',
                'Master Tipe Barang',
                'Master Warna',
                'Master Bundle Produk',
            ];
            $sortedProdukSubgroups = [];
            foreach ($produkSubgroupOrder as $subName) {
                if (isset($modules['master_produk']['subgroups'][$subName])) {
                    $sortedProdukSubgroups[$subName] = $modules['master_produk']['subgroups'][$subName];
                }
            }
            foreach ($modules['master_produk']['subgroups'] as $subName => $perms) {
                if (!isset($sortedProdukSubgroups[$subName])) {
                    $sortedProdukSubgroups[$subName] = $perms;
                }
            }
            $modules['master_produk']['subgroups'] = $sortedProdukSubgroups;
        }

        // Sort subgroups in Produksi module to match workflow sequence
        if (isset($modules['produksi']['subgroups'])) {
            $produksiSubgroupOrder = [
                'Resep Produksi (Bill of Materials / BOM)',
                'Surat Perintah Kerja (SPK / Work Order)',
                'Pengeluaran Bahan Baku SPK',
                'Realisasi Produksi & Kontrol Tahapan',
                'Penerimaan Barang Jadi SPK',
                'Master Tahapan Produksi',
            ];
            $sortedSubgroups = [];
            foreach ($produksiSubgroupOrder as $subName) {
                if (isset($modules['produksi']['subgroups'][$subName])) {
                    $sortedSubgroups[$subName] = $modules['produksi']['subgroups'][$subName];
                }
            }
            foreach ($modules['produksi']['subgroups'] as $subName => $perms) {
                if (!isset($sortedSubgroups[$subName])) {
                    $sortedSubgroups[$subName] = $perms;
                }
            }
            $modules['produksi']['subgroups'] = $sortedSubgroups;
        }

        // Sort subgroups in Purchasing module
        if (isset($modules['purchasing']['subgroups'])) {
            $purchasingSubgroupOrder = [
                'Purchase Request (PR)',
                'Purchase Order (PO)',
                'Penerimaan Barang (Goods Receipt / GR)',
                'Pembayaran Utang (Purchase Payment)',
                'Data Supplier',
            ];
            $sortedPurchasingSubgroups = [];
            foreach ($purchasingSubgroupOrder as $subName) {
                if (isset($modules['purchasing']['subgroups'][$subName])) {
                    $sortedPurchasingSubgroups[$subName] = $modules['purchasing']['subgroups'][$subName];
                }
            }
            foreach ($modules['purchasing']['subgroups'] as $subName => $perms) {
                if (!isset($sortedPurchasingSubgroups[$subName])) {
                    $sortedPurchasingSubgroups[$subName] = $perms;
                }
            }
            $modules['purchasing']['subgroups'] = $sortedPurchasingSubgroups;
        }

        // Sort permissions within each subgroup in all modules (CRUD + Workflow order)
        $actionOrderWeight = function ($permName) {
            // Pembayaran Utang specific ordering
            if ($permName === 'lihat-pembayaran-utang') return 1;
            if ($permName === 'tambah-pembayaran-utang') return 2;
            if ($permName === 'detail-pembayaran-utang') return 3;
            if ($permName === 'edit-pembayaran-utang') return 4;
            if ($permName === 'approve-pembayaran-utang') return 5;
            if ($permName === 'batalkan-pembayaran-utang') return 6;
            if ($permName === 'cetak-pembayaran-utang') return 7;

            // Tender specific ordering
            if ($permName === 'lihat-tender') return 1;
            if ($permName === 'tambah-tender') return 2;
            if ($permName === 'edit-tender') return 3;
            if ($permName === 'hapus-tender') return 4;
            if ($permName === 'approve-tender') return 5;

            // General action weight sorting
            if (str_starts_with($permName, 'lihat-') && !str_contains($permName, '-hpp') && !str_contains($permName, '-jurnal') && !str_contains($permName, '-nilai')) return 10;
            if (str_starts_with($permName, 'tambah-')) return 20;
            if (str_starts_with($permName, 'detail-')) return 30;
            if (str_starts_with($permName, 'edit-') || str_starts_with($permName, 'ubah-')) return 40;
            if (str_starts_with($permName, 'revisi-')) return 45;
            if (str_starts_with($permName, 'posting-')) return 50;
            if (str_starts_with($permName, 'hapus-') || str_starts_with($permName, 'batalkan-')) return 55;
            if (str_starts_with($permName, 'approve-') || str_starts_with($permName, 'setuju-') || str_starts_with($permName, 'konfirmasi-')) return 60;
            if (str_starts_with($permName, 'cetak-')) return 70;
            if (str_contains($permName, '-hpp') || str_contains($permName, '-jurnal') || str_contains($permName, '-nilai')) return 80;
            return 100;
        };

        foreach ($modules as $modKey => &$modData) {
            if (isset($modData['subgroups'])) {
                foreach ($modData['subgroups'] as $subKey => &$permsList) {
                    usort($permsList, function ($a, $b) use ($actionOrderWeight) {
                        $wA = $actionOrderWeight($a->name);
                        $wB = $actionOrderWeight($b->name);
                        if ($wA === $wB) {
                            return strcmp($a->name, $b->name);
                        }
                        return $wA <=> $wB;
                    });
                }
            }
        }
        unset($modData);
        unset($permsList);

        // Sort modules to consistent view order
        $order = ['akses','produksi','master_produk','purchasing','sales','inventory',
                  'master_gudang','master_organisasi','karyawan','master_keuangan',
                  'hak','keamanan_audit','lainnya'];
        $sortedModules = [];
        foreach ($order as $k) {
            if (isset($modules[$k])) $sortedModules[$k] = $modules[$k];
        }
        foreach ($modules as $k => $v) {
            if (!isset($sortedModules[$k])) $sortedModules[$k] = $v;
        }

        return response()->json([
            'role'             => $role,
            'modules'          => $sortedModules,
            'role_permissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('edit-hak-akses');

        try {
            DB::beginTransaction();

            $permissions = $request->permissions ?? [];
            $role->syncPermissions($permissions);

            DB::commit();

            // Reset permission cache agar perubahan langsung efektif
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            return response()->json(['message' => 'Hak akses berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update permissions: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui hak akses.'], 500);
        }
    }
}
