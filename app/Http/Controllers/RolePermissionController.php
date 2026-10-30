<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\UserActivityLog;

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
            'lihat-dashboard-inventori'  => 'Akses Dashboard',
            'lihat-dashboard-produksi'   => 'Akses Dashboard',
            'lihat-dashboard-sales'      => 'Akses Dashboard',
            'lihat-dashboard-keuangan'   => 'Akses Dashboard',
            'lihat-monitor-aktif'        => 'Audit & Keamanan System',
            'lihat-log-aktivitas-user'   => 'Audit & Keamanan System',
            'hapus-log-aktivitas-user'   => 'Audit & Keamanan System',

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

            'lihat-pengeluaran-bahan'          => 'Pengeluaran Bahan Baku SPK',
            'tambah-pengeluaran-bahan'         => 'Pengeluaran Bahan Baku SPK',
            'detail-pengeluaran-bahan'         => 'Pengeluaran Bahan Baku SPK',
            'edit-pengeluaran-bahan'           => 'Pengeluaran Bahan Baku SPK',
            'hapus-pengeluaran-bahan'          => 'Pengeluaran Bahan Baku SPK',
            'cetak-pengeluaran-bahan'          => 'Pengeluaran Bahan Baku SPK',
            'ajukan-closing-pengeluaran-bahan'       => 'Pengeluaran Bahan Baku SPK',
            'batal-ajukan-closing-pengeluaran-bahan' => 'Pengeluaran Bahan Baku SPK',
            'closing-pengeluaran-bahan'              => 'Pengeluaran Bahan Baku SPK',
            'tolak-closing-pengeluaran-bahan'        => 'Pengeluaran Bahan Baku SPK',
            'buka-closing-pengeluaran-bahan'         => 'Pengeluaran Bahan Baku SPK',
            'lihat-hpp-pengeluaran-bahan'            => 'Pengeluaran Bahan Baku SPK',
            'lihat-jurnal-pengeluaran-bahan'         => 'Pengeluaran Bahan Baku SPK',

            'lihat-penerimaan-barang-jadi'         => 'Penerimaan Barang Jadi SPK',
            'tambah-penerimaan-barang-jadi'        => 'Penerimaan Barang Jadi SPK',
            'detail-penerimaan-barang-jadi'        => 'Penerimaan Barang Jadi SPK',
            'edit-penerimaan-barang-jadi'          => 'Penerimaan Barang Jadi SPK',
            'hapus-penerimaan-barang-jadi'         => 'Penerimaan Barang Jadi SPK',
            'cetak-penerimaan-barang-jadi'         => 'Penerimaan Barang Jadi SPK',
            'lihat-hpp-penerimaan-barang-jadi'     => 'Penerimaan Barang Jadi SPK',
            'lihat-jurnal-penerimaan-barang-jadi'  => 'Penerimaan Barang Jadi SPK',
            'ajukan-closing-penerimaan-barang-jadi' => 'Penerimaan Barang Jadi SPK',
            'batal-ajukan-closing-penerimaan-barang-jadi' => 'Penerimaan Barang Jadi SPK',
            'closing-penerimaan-barang-jadi'       => 'Penerimaan Barang Jadi SPK',
            'tolak-closing-penerimaan-barang-jadi' => 'Penerimaan Barang Jadi SPK',
            'buka-closing-penerimaan-barang-jadi'  => 'Penerimaan Barang Jadi SPK',

            'lihat-tracking-sn'     => 'Pelacakan Serial Number (SN Tracker)',

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

            // Peminjaman Barang (Pameran & Event)
            'lihat-peminjaman'         => 'Peminjaman Barang (Pameran & Event)',
            'tambah-peminjaman'        => 'Peminjaman Barang (Pameran & Event)',
            'edit-peminjaman'          => 'Peminjaman Barang (Pameran & Event)',
            'hapus-peminjaman'         => 'Peminjaman Barang (Pameran & Event)',
            'approve-peminjaman'       => 'Peminjaman Barang (Pameran & Event)',
            'siapkan-peminjaman'       => 'Peminjaman Barang (Pameran & Event)',
            'serah-terima-peminjaman'  => 'Peminjaman Barang (Pameran & Event)',
            'kembalikan-peminjaman'    => 'Peminjaman Barang (Pameran & Event)',

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
            'buka-po'     => 'Purchase Order (PO)',
            'cetak-po'    => 'Purchase Order (PO)',
            'lihat-pembayaran-utang'    => 'Pembayaran Utang (Purchase Payment)',
            'tambah-pembayaran-utang'   => 'Pembayaran Utang (Purchase Payment)',
            'detail-pembayaran-utang'   => 'Pembayaran Utang (Purchase Payment)',
            'edit-pembayaran-utang'     => 'Pembayaran Utang (Purchase Payment)',
            'approve-pembayaran-utang'  => 'Pembayaran Utang (Purchase Payment)',
            'hapus-pembayaran-utang'    => 'Pembayaran Utang (Purchase Payment)',
            'batalkan-pembayaran-utang' => 'Pembayaran Utang (Purchase Payment)',
            'lihat-jurnal-pembayaran-utang' => 'Pembayaran Utang (Purchase Payment)',
            'cetak-pembayaran-utang'    => 'Pembayaran Utang (Purchase Payment)',

            // Rekap Utang & Kartu Utang Supplier (Accounts Payable)
            'lihat-hutang'              => 'Rekap Utang & Kartu Utang Supplier',
            'cetak-hutang'              => 'Rekap Utang & Kartu Utang Supplier',

            // Faktur Pembelian (Purchase Invoice / AP)
            'lihat-purchase-invoice'        => 'Faktur Pembelian (Purchase Invoice / AP)',
            'tambah-purchase-invoice'       => 'Faktur Pembelian (Purchase Invoice / AP)',
            'edit-purchase-invoice'         => 'Faktur Pembelian (Purchase Invoice / AP)',
            'hapus-purchase-invoice'        => 'Faktur Pembelian (Purchase Invoice / AP)',
            'approve-purchase-invoice'      => 'Faktur Pembelian (Purchase Invoice / AP)',
            'batalkan-purchase-invoice'     => 'Faktur Pembelian (Purchase Invoice / AP)',
            'cetak-purchase-invoice'        => 'Faktur Pembelian (Purchase Invoice / AP)',
            'lihat-jurnal-purchase-invoice' => 'Faktur Pembelian (Purchase Invoice / AP)',

            'lihat-supplier'  => 'Data Supplier',
            'tambah-supplier' => 'Data Supplier',
            'edit-supplier'   => 'Data Supplier',
            'hapus-supplier'  => 'Data Supplier',

            // Alokasi Biaya (Landed Cost)
            'lihat-alokasi-biaya'        => 'Alokasi Biaya (Landed Cost)',
            'tambah-alokasi-biaya'       => 'Alokasi Biaya (Landed Cost)',
            'detail-alokasi-biaya'       => 'Alokasi Biaya (Landed Cost)',
            'posting-alokasi-biaya'      => 'Alokasi Biaya (Landed Cost)',
            'lihat-jurnal-alokasi-biaya' => 'Alokasi Biaya (Landed Cost)',
            'batalkan-alokasi-biaya'     => 'Alokasi Biaya (Landed Cost)',

            // Sales & Order Penjualan
            'lihat-customer'         => 'Data Pelanggan (Customer)',
            'tambah-customer'        => 'Data Pelanggan (Customer)',
            'edit-customer'          => 'Data Pelanggan (Customer)',
            'hapus-customer'         => 'Data Pelanggan (Customer)',
            'lihat-tender'           => 'Tender & Proyek',
            'tambah-tender'          => 'Tender & Proyek',
            'edit-tender'            => 'Tender & Proyek',
            'hapus-tender'           => 'Tender & Proyek',
            'approve-tender'         => 'Tender & Proyek',
            'kirim-lelang-tender'    => 'Tender & Proyek',
            'negosiasi-tender'       => 'Tender & Proyek',
            'keputusan-final-tender' => 'Tender & Proyek',
            'lihat-penawaran'        => 'Penawaran Harga (Sales Quotation)',
            'tambah-penawaran'       => 'Penawaran Harga (Sales Quotation)',
            'edit-penawaran'         => 'Penawaran Harga (Sales Quotation)',
            'hapus-penawaran'        => 'Penawaran Harga (Sales Quotation)',
            'revisi-penawaran'       => 'Penawaran Harga (Sales Quotation)',
            'approve-penawaran'      => 'Penawaran Harga (Sales Quotation)',
            'cetak-penawaran'        => 'Penawaran Harga (Sales Quotation)',
            'konversi-so-penawaran'  => 'Penawaran Harga (Sales Quotation)',
            'lihat-hpp-penawaran'    => 'Penawaran Harga (Sales Quotation)',
            'lihat-sales-order'      => 'Order Penjualan (Sales Order / POS)',
            'tambah-sales-order'     => 'Order Penjualan (Sales Order / POS)',
            'edit-sales-order'       => 'Order Penjualan (Sales Order / POS)',
            'hapus-sales-order'      => 'Order Penjualan (Sales Order / POS)',
            'cetak-sales-order'      => 'Order Penjualan (Sales Order / POS)',

            // Pengiriman & Surat Jalan (Delivery Order)
            'lihat-delivery-order'        => 'Pengiriman & Surat Jalan (Delivery Order)',
            'tambah-delivery-order'       => 'Pengiriman & Surat Jalan (Delivery Order)',
            'edit-delivery-order'         => 'Pengiriman & Surat Jalan (Delivery Order)',
            'cetak-delivery-order'        => 'Pengiriman & Surat Jalan (Delivery Order)',
            'approve-delivery-order'      => 'Pengiriman & Surat Jalan (Delivery Order)',
            'selesai-delivery-order'      => 'Pengiriman & Surat Jalan (Delivery Order)',
            'lihat-jurnal-delivery-order' => 'Pengiriman & Surat Jalan (Delivery Order)',

            // Faktur Penjualan (Sales Invoice)
            'lihat-sales-invoice'         => 'Faktur Penjualan (Sales Invoice)',
            'tambah-sales-invoice'        => 'Faktur Penjualan (Sales Invoice)',
            'edit-sales-invoice'          => 'Faktur Penjualan (Sales Invoice)',
            'hapus-sales-invoice'         => 'Faktur Penjualan (Sales Invoice)',
            'cetak-sales-invoice'         => 'Faktur Penjualan (Sales Invoice)',
            'approve-sales-invoice'       => 'Faktur Penjualan (Sales Invoice)',
            'lihat-jurnal-sales-invoice'  => 'Faktur Penjualan (Sales Invoice)',

            // Penerimaan Pembayaran Penjualan (Sales Payment)
            'lihat-sales-payment'         => 'Penerimaan Pembayaran (Sales Payment)',
            'tambah-sales-payment'        => 'Penerimaan Pembayaran (Sales Payment)',
            'edit-sales-payment'          => 'Penerimaan Pembayaran (Sales Payment)',
            'lihat-jurnal-sales-payment'  => 'Penerimaan Pembayaran (Sales Payment)',
            'batalkan-sales-payment'      => 'Penerimaan Pembayaran (Sales Payment)',
            'hapus-sales-payment'         => 'Penerimaan Pembayaran (Sales Payment)',
            'approve-sales-payment'       => 'Penerimaan Pembayaran (Sales Payment)',
            'cetak-sales-payment'         => 'Penerimaan Pembayaran (Sales Payment)',

            // Piutang Pelanggan & Kartu Piutang
            'lihat-piutang'               => 'Rekap Piutang & Kartu Piutang Pelanggan',
            'cetak-piutang'               => 'Rekap Piutang & Kartu Piutang Pelanggan',

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

            // Pengaturan Aplikasi
            'lihat-pengaturan-aplikasi' => 'Pengaturan Aplikasi',
            'edit-pengaturan-aplikasi'  => 'Pengaturan Aplikasi',

            // Kas & Bank (Keuangan)
            'lihat-kas-bank'              => 'Buku Kas & Rekening Bank',
            'detail-kas-bank'             => 'Buku Kas & Rekening Bank',
            'tambah-kas-bank'             => 'Buku Kas & Rekening Bank',
            'edit-kas-bank'               => 'Buku Kas & Rekening Bank',
            'lihat-mutasi-kas-bank'       => 'Buku Kas & Rekening Koran',
            'tambah-kas-masuk'            => 'Buku Kas & Rekening Koran',
            'tambah-kas-keluar'           => 'Buku Kas & Rekening Koran',
            'transfer-kas-bank'           => 'Buku Kas & Rekening Koran',
            'batalkan-mutasi-kas-bank'    => 'Buku Kas & Rekening Koran',
            'cetak-rekening-koran'        => 'Buku Kas & Rekening Koran',

            // Kas Kecil (Petty Cash)
            'lihat-petty-cash'            => 'Kas Kecil (Petty Cash)',
            'filter-petty-cash'           => 'Kas Kecil (Petty Cash)',
            'export-excel-petty-cash'     => 'Kas Kecil (Petty Cash)',
            'cetak-pdf-petty-cash'        => 'Kas Kecil (Petty Cash)',
            'catat-petty-cash'            => 'Kas Kecil (Petty Cash)',
            'detail-petty-cash'           => 'Kas Kecil (Petty Cash)',
            'batalkan-petty-cash'         => 'Kas Kecil (Petty Cash)',

            // Jurnal Umum (General Journal)
            'lihat-jurnal-umum'           => 'Jurnal Umum',
            'tambah-jurnal-umum'          => 'Jurnal Umum',
            'cetak-jurnal-umum'           => 'Jurnal Umum',

            // Pengaturan Akuntansi
            'lihat-pengaturan-akuntansi'  => 'Pengaturan Akuntansi',
            'edit-pengaturan-akuntansi'   => 'Pengaturan Akuntansi',

            // Audit & Keamanan
            'lihat-monitor'            => 'Audit & Keamanan System',
            'lihat-log-aktivitas'      => 'Audit & Keamanan System',
            'lihat-monitor-aktif'      => 'Audit & Keamanan System',
            'lihat-log-aktivitas-user' => 'Audit & Keamanan System',
            'hapus-log-aktivitas-user' => 'Audit & Keamanan System',
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
                       'lihat-dashboard','lihat-dashboard-purchasing','lihat-dashboard-inventori','lihat-dashboard-produksi','lihat-dashboard-sales','lihat-dashboard-keuangan'];
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
            'lihat-peminjaman','tambah-peminjaman','edit-peminjaman','hapus-peminjaman',
            'approve-peminjaman','siapkan-peminjaman','serah-terima-peminjaman','kembalikan-peminjaman',
        ];
        if (in_array($permissionName, $inventory)) return 'inventory';

        // Modul Produksi (BOM, SPK, Pengeluaran Bahan Baku, Realisasi, Penerimaan Barang Jadi, Tracking SN, Tahapan, Input Produksi)
        if (str_contains($permissionName, 'bom') || 
            str_contains($permissionName, 'spk') || 
            str_contains($permissionName, 'realisasi') || 
            str_contains($permissionName, 'pengeluaran-bahan') || 
            str_contains($permissionName, 'penerimaan-barang-jadi') || 
            str_contains($permissionName, 'tracking-sn') || 
            str_contains($permissionName, 'tahapan') ||
            str_contains($permissionName, 'produksi')) {
            return 'produksi';
        }

        // Purchasing
        $purchasing = [
            'lihat-gr','tambah-gr','hapus-gr','lihat-jurnal-gr',
            'lihat-pr','tambah-pr','edit-pr','hapus-pr','approve-pr','cetak-pr',
            'lihat-po','tambah-po','edit-po','hapus-po','approve-po','buka-po','cetak-po',
            'lihat-pembayaran-utang','tambah-pembayaran-utang','detail-pembayaran-utang','edit-pembayaran-utang','posting-pembayaran-utang','hapus-pembayaran-utang','batalkan-pembayaran-utang','lihat-jurnal-pembayaran-utang','cetak-pembayaran-utang',
            'lihat-hutang','cetak-hutang',
            'lihat-supplier','tambah-supplier','edit-supplier','hapus-supplier',
            'lihat-alokasi-biaya','tambah-alokasi-biaya','detail-alokasi-biaya','posting-alokasi-biaya','lihat-jurnal-alokasi-biaya','batalkan-alokasi-biaya',
            'lihat-purchase-invoice','tambah-purchase-invoice','edit-purchase-invoice','hapus-purchase-invoice','approve-purchase-invoice','batalkan-purchase-invoice','cetak-purchase-invoice','lihat-jurnal-purchase-invoice',
        ];
        if (in_array($permissionName, $purchasing) || str_contains($permissionName, 'pembayaran-utang') || str_contains($permissionName, 'alokasi-biaya') || str_contains($permissionName, 'purchase-invoice')) return 'purchasing';

        // Sales
        $sales = [
            'lihat-customer','tambah-customer','edit-customer','hapus-customer',
            'lihat-tender','tambah-tender','edit-tender','hapus-tender','approve-tender','kirim-lelang-tender','negosiasi-tender','keputusan-final-tender',
            'lihat-penawaran','tambah-penawaran','edit-penawaran','hapus-penawaran','revisi-penawaran','approve-penawaran','cetak-penawaran','konversi-so-penawaran','lihat-hpp-penawaran',
            'lihat-sales-order','tambah-sales-order','edit-sales-order','hapus-sales-order','cetak-sales-order',
            'lihat-delivery-order','tambah-delivery-order','edit-delivery-order','cetak-delivery-order','approve-delivery-order','selesai-delivery-order','lihat-jurnal-delivery-order',
            'lihat-sales-invoice','tambah-sales-invoice','edit-sales-invoice','hapus-sales-invoice','cetak-sales-invoice','approve-sales-invoice','lihat-jurnal-sales-invoice',
            'lihat-sales-payment','tambah-sales-payment','edit-sales-payment','lihat-jurnal-sales-payment','batalkan-sales-payment','hapus-sales-payment','approve-sales-payment','cetak-sales-payment',
            'lihat-piutang','cetak-piutang',
        ];
        if (in_array($permissionName, $sales) || str_contains($permissionName, 'sales-invoice') || str_contains($permissionName, 'sales-payment') || str_contains($permissionName, 'piutang')) return 'sales';

        // Karyawan
        $karyawan = ['lihat-karyawan','tambah-karyawan','edit-karyawan','hapus-karyawan'];
        if (in_array($permissionName, $karyawan)) return 'karyawan';

        // Hak Akses & Pengaturan Sistem
        if (in_array($permissionName, ['lihat-hak-akses','edit-hak-akses','lihat-pengaturan-aplikasi','edit-pengaturan-aplikasi'])) return 'hak';

        // Audit & Keamanan
        if (in_array($permissionName, ['lihat-monitor','lihat-log-aktivitas','lihat-monitor-aktif','lihat-log-aktivitas-user','hapus-log-aktivitas-user'])) return 'keamanan_audit';

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

        // Modul Kas, Bank & Keuangan
        if (str_contains($permissionName, 'kas-bank') || 
            str_contains($permissionName, 'kas-masuk') || 
            str_contains($permissionName, 'kas-keluar') || 
            str_contains($permissionName, 'rekening-koran') ||
            str_contains($permissionName, 'petty-cash') ||
            str_contains($permissionName, 'jurnal-umum') ||
            str_contains($permissionName, 'pengaturan-akuntansi')) {
            return 'kas_bank';
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

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        // Kecepatan & Optimasi: Cache tree struktur modul izin (1 hari)
        $sortedModules = Cache::remember('role_permissions_modules_tree', 86400, function () {
            return $this->buildPermissionsTree();
        });

        return response()->json([
            'role'             => $role,
            'modules'          => $sortedModules,
            'role_permissions' => $rolePermissions,
        ]);
    }

    private function buildPermissionsTree(): array
    {
        $allPermissions = \Spatie\Permission\Models\Permission::select('id', 'name')->orderBy('name')->get();

        // Grouping module map using easy to understand Indonesian labels
        $moduleMap = [
            'akses'              => ['label' => 'Hak Akses Modul Utama',          'icon' => 'bi-grid',          'color' => 'warning'],
            'master_gudang'      => ['label' => 'Master Gudang & Cabang',          'icon' => 'bi-building-gear', 'color' => 'info'],
            'master_produk'      => ['label' => 'Master Barang & Produk',          'icon' => 'bi-boxes',         'color' => 'primary'],
            'master_organisasi'  => ['label' => 'Master Organisasi & Departemen',  'icon' => 'bi-diagram-3',     'color' => 'dark'],
            'karyawan'           => ['label' => 'Akun Pengguna & Karyawan',        'icon' => 'bi-people',        'color' => 'success'],
            'master_keuangan'    => ['label' => 'Keuangan, Pajak & Mata Uang',     'icon' => 'bi-wallet2',       'color' => 'secondary'],
            'inventory'          => ['label' => 'Modul Inventory & Stok',          'icon' => 'bi-house-gear',    'color' => 'warning'],
            'purchasing'         => ['label' => 'Modul Purchasing & Pembelian',    'icon' => 'bi-cart-check',    'color' => 'success'],
            'produksi'           => ['label' => 'Master Produksi',                'icon' => 'bi-gear-wide-connected', 'color' => 'success'],
            'sales'              => ['label' => 'Modul Sales & Pelanggan',         'icon' => 'bi-briefcase',     'color' => 'info'],
            'kas_bank'           => ['label' => 'Modul Kas, Bank & Keuangan',      'icon' => 'bi-bank2',         'color' => 'success'],
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

        // Sort subgroups in Kas, Bank & Keuangan module (Kas Kecil tepat di bawah Buku Kas & Rekening Bank)
        if (isset($modules['kas_bank']['subgroups'])) {
            $kasBankSubgroupOrder = [
                'Buku Kas & Rekening Bank',
                'Kas Kecil (Petty Cash)',
                'Buku Kas & Rekening Koran',
                'Jurnal Umum',
                'Pengaturan Akuntansi',
            ];
            $sortedKasBankSubgroups = [];
            foreach ($kasBankSubgroupOrder as $subName) {
                if (isset($modules['kas_bank']['subgroups'][$subName])) {
                    $sortedKasBankSubgroups[$subName] = $modules['kas_bank']['subgroups'][$subName];
                }
            }
            foreach ($modules['kas_bank']['subgroups'] as $subName => $perms) {
                if (!isset($sortedKasBankSubgroups[$subName])) {
                    $sortedKasBankSubgroups[$subName] = $perms;
                }
            }
            $modules['kas_bank']['subgroups'] = $sortedKasBankSubgroups;
        }

        // Sort subgroups in Inventory & Stok module (Peminjaman Barang ditempatkan di bawah Laporan & Ringkasan Stok)
        if (isset($modules['inventory']['subgroups'])) {
            $inventorySubgroupOrder = [
                'Penyesuaian Stok (Stock Adjustment)',
                'Barang Keluar (Goods Issue / GI)',
                'Transfer Gudang',
                'Stock Opname',
                'Saldo Awal Persediaan (Opening Stock)',
                'Laporan & Ringkasan Stok Persediaan',
                'Peminjaman Barang (Pameran & Event)',
            ];
            $sortedInventorySubgroups = [];
            foreach ($inventorySubgroupOrder as $subName) {
                if (isset($modules['inventory']['subgroups'][$subName])) {
                    $sortedInventorySubgroups[$subName] = $modules['inventory']['subgroups'][$subName];
                }
            }
            foreach ($modules['inventory']['subgroups'] as $subName => $perms) {
                if (!isset($sortedInventorySubgroups[$subName])) {
                    $sortedInventorySubgroups[$subName] = $perms;
                }
            }
            $modules['inventory']['subgroups'] = $sortedInventorySubgroups;
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
                'Alokasi Biaya (Landed Cost)',
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

        // Sort subgroups in Sales module
        if (isset($modules['sales']['subgroups'])) {
            $salesSubgroupOrder = [
                'Data Pelanggan (Customer)',
                'Tender & Proyek',
                'Penawaran Harga (Sales Quotation)',
                'Order Penjualan (Sales Order / POS)',
                'Pengiriman & Surat Jalan (Delivery Order)',
                'Faktur Penjualan (Sales Invoice)',
                'Penerimaan Pembayaran (Sales Payment)',
                'Rekap Piutang & Kartu Piutang Pelanggan',
            ];
            $sortedSalesSubgroups = [];
            foreach ($salesSubgroupOrder as $subName) {
                if (isset($modules['sales']['subgroups'][$subName])) {
                    $sortedSalesSubgroups[$subName] = $modules['sales']['subgroups'][$subName];
                }
            }
            foreach ($modules['sales']['subgroups'] as $subName => $perms) {
                if (!isset($sortedSalesSubgroups[$subName])) {
                    $sortedSalesSubgroups[$subName] = $perms;
                }
            }
            $modules['sales']['subgroups'] = $sortedSalesSubgroups;
        }

        // Sort permissions within each subgroup in all modules (CRUD + Workflow order)
        $actionOrderWeight = function ($permName) {
            // Akses Dashboard specific ordering
            if ($permName === 'lihat-dashboard') return 1;
            if ($permName === 'lihat-dashboard-purchasing') return 2;
            if ($permName === 'lihat-dashboard-inventori') return 3;
            if ($permName === 'lihat-dashboard-produksi') return 4;
            if ($permName === 'lihat-dashboard-sales') return 5;
            if ($permName === 'lihat-dashboard-keuangan') return 6;

            // Pengaturan Akuntansi specific ordering
            if ($permName === 'lihat-pengaturan-akuntansi') return 1;
            if ($permName === 'edit-pengaturan-akuntansi') return 2;

            // Jurnal Umum specific ordering
            if ($permName === 'lihat-jurnal-umum') return 1;
            if ($permName === 'tambah-jurnal-umum') return 2;
            if ($permName === 'cetak-jurnal-umum') return 3;

            // Kas Kecil (Petty Cash) specific ordering
            if ($permName === 'lihat-petty-cash') return 1;
            if ($permName === 'filter-petty-cash') return 2;
            if ($permName === 'export-excel-petty-cash') return 3;
            if ($permName === 'cetak-pdf-petty-cash') return 4;
            if ($permName === 'catat-petty-cash') return 5;
            if ($permName === 'detail-petty-cash') return 6;
            if ($permName === 'batalkan-petty-cash') return 7;

            // Pembayaran Utang specific ordering
            if ($permName === 'lihat-pembayaran-utang') return 1;
            if ($permName === 'tambah-pembayaran-utang') return 2;
            if ($permName === 'detail-pembayaran-utang') return 3;
            if ($permName === 'edit-pembayaran-utang') return 4;
            if ($permName === 'approve-pembayaran-utang') return 5;
            if ($permName === 'hapus-pembayaran-utang') return 6;
            if ($permName === 'batalkan-pembayaran-utang') return 7;
            if ($permName === 'lihat-jurnal-pembayaran-utang') return 8;
            if ($permName === 'cetak-pembayaran-utang') return 9;

            // Rekap Utang Supplier specific ordering
            if ($permName === 'lihat-hutang') return 1;
            if ($permName === 'cetak-hutang') return 2;

            // Alokasi Biaya (Landed Cost) specific ordering
            if ($permName === 'lihat-alokasi-biaya') return 1;
            if ($permName === 'tambah-alokasi-biaya') return 2;
            if ($permName === 'detail-alokasi-biaya') return 3;
            if ($permName === 'posting-alokasi-biaya') return 4;
            if ($permName === 'lihat-jurnal-alokasi-biaya') return 5;
            if ($permName === 'batalkan-alokasi-biaya') return 6;

            // Tender specific ordering
            if ($permName === 'lihat-tender') return 1;
            if ($permName === 'tambah-tender') return 2;
            if ($permName === 'edit-tender') return 3;
            if ($permName === 'hapus-tender') return 4;
            if ($permName === 'approve-tender') return 5;
            if ($permName === 'kirim-lelang-tender') return 6;
            if ($permName === 'negosiasi-tender') return 7;
            if ($permName === 'keputusan-final-tender') return 8;

            // Penawaran Harga specific ordering
            if ($permName === 'lihat-penawaran') return 1;
            if ($permName === 'tambah-penawaran') return 2;
            if ($permName === 'edit-penawaran') return 3;
            if ($permName === 'hapus-penawaran') return 4;
            if ($permName === 'revisi-penawaran') return 5;
            if ($permName === 'approve-penawaran') return 6;
            if ($permName === 'cetak-penawaran') return 7;
            if ($permName === 'konversi-so-penawaran') return 8;
            if ($permName === 'lihat-hpp-penawaran') return 9;

            // Order Penjualan (Sales Order / POS)
            if ($permName === 'lihat-sales-order') return 1;
            if ($permName === 'tambah-sales-order') return 2;
            if ($permName === 'edit-sales-order') return 3;
            if ($permName === 'hapus-sales-order') return 4;
            if ($permName === 'cetak-sales-order') return 5;

            // Pengiriman & Surat Jalan (Delivery Order)
            if ($permName === 'lihat-delivery-order') return 1;
            if ($permName === 'tambah-delivery-order') return 2;
            if ($permName === 'edit-delivery-order') return 3;
            if ($permName === 'cetak-delivery-order') return 4;
            if ($permName === 'approve-delivery-order') return 5;
            if ($permName === 'selesai-delivery-order') return 6;
            if ($permName === 'lihat-jurnal-delivery-order') return 7;

            // Faktur Penjualan (Sales Invoice)
            if ($permName === 'lihat-sales-invoice') return 1;
            if ($permName === 'tambah-sales-invoice') return 2;
            if ($permName === 'edit-sales-invoice') return 3;
            if ($permName === 'hapus-sales-invoice') return 4;
            if ($permName === 'cetak-sales-invoice') return 5;
            if ($permName === 'approve-sales-invoice') return 6;

            // Penerimaan Pembayaran Penjualan (Sales Payment)
            if ($permName === 'lihat-sales-payment') return 1;
            if ($permName === 'tambah-sales-payment') return 2;
            if ($permName === 'edit-sales-payment') return 3;
            if ($permName === 'lihat-jurnal-sales-payment') return 4;
            if ($permName === 'batalkan-sales-payment') return 5;
            if ($permName === 'hapus-sales-payment') return 6;
            if ($permName === 'approve-sales-payment') return 7;
            if ($permName === 'cetak-sales-payment') return 8;

            // Rekap Piutang & Kartu Piutang Pelanggan
            if ($permName === 'lihat-piutang') return 1;
            if ($permName === 'cetak-piutang') return 2;

            // Pengeluaran Bahan Baku SPK specific ordering
            if ($permName === 'lihat-pengeluaran-bahan') return 1;
            if ($permName === 'tambah-pengeluaran-bahan') return 2;
            if ($permName === 'detail-pengeluaran-bahan') return 3;
            if ($permName === 'edit-pengeluaran-bahan') return 4;
            if ($permName === 'hapus-pengeluaran-bahan') return 5;
            if ($permName === 'cetak-pengeluaran-bahan') return 6;
            if ($permName === 'lihat-hpp-pengeluaran-bahan') return 7;
            if ($permName === 'lihat-jurnal-pengeluaran-bahan') return 8;
            if ($permName === 'ajukan-closing-pengeluaran-bahan') return 9;
            if ($permName === 'batal-ajukan-closing-pengeluaran-bahan') return 10;
            if ($permName === 'closing-pengeluaran-bahan') return 11;
            if ($permName === 'tolak-closing-pengeluaran-bahan') return 12;
            if ($permName === 'buka-closing-pengeluaran-bahan') return 13;

            // Penerimaan Barang Jadi SPK specific ordering
            if ($permName === 'lihat-penerimaan-barang-jadi') return 1;
            if ($permName === 'tambah-penerimaan-barang-jadi') return 2;
            if ($permName === 'detail-penerimaan-barang-jadi') return 3;
            if ($permName === 'edit-penerimaan-barang-jadi') return 4;
            if ($permName === 'hapus-penerimaan-barang-jadi') return 5;
            if ($permName === 'cetak-penerimaan-barang-jadi') return 6;
            if ($permName === 'lihat-hpp-penerimaan-barang-jadi') return 7;
            if ($permName === 'lihat-jurnal-penerimaan-barang-jadi') return 8;
            if ($permName === 'ajukan-closing-penerimaan-barang-jadi') return 9;
            if ($permName === 'batal-ajukan-closing-penerimaan-barang-jadi') return 10;
            if ($permName === 'closing-penerimaan-barang-jadi') return 11;
            if ($permName === 'tolak-closing-penerimaan-barang-jadi') return 12;
            if ($permName === 'buka-closing-penerimaan-barang-jadi') return 13;

            // Realisasi Produksi & Kontrol Tahapan specific ordering
            if ($permName === 'lihat-realisasi-produksi') return 1;
            if ($permName === 'kontrol-tahapan-realisasi') return 2;
            if ($permName === 'mulai-tahapan-produksi') return 3;
            if ($permName === 'input-realisasi-produksi') return 4;
            if ($permName === 'selesai-tahapan-produksi') return 5;
            if ($permName === 'approve-realisasi-produksi') return 6;
            if ($permName === 'cetak-progress-spk') return 7;
            if ($permName === 'lihat-riwayat-input-produksi') return 8;
            if ($permName === 'input-hasil-harian-produksi') return 9;

            // Peminjaman Barang specific ordering
            if ($permName === 'lihat-peminjaman') return 1;
            if ($permName === 'tambah-peminjaman') return 2;
            if ($permName === 'edit-peminjaman') return 3;
            if ($permName === 'hapus-peminjaman') return 4;
            if ($permName === 'approve-peminjaman') return 5;
            if ($permName === 'siapkan-peminjaman') return 6;
            if ($permName === 'serah-terima-peminjaman') return 7;
            if ($permName === 'kembalikan-peminjaman') return 8;

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

        // Sort modules to consistent view order requested by user
        $order = [
            'akses',
            'master_gudang',
            'master_produk',
            'master_organisasi',
            'karyawan',
            'master_keuangan',
            'inventory',
            'purchasing',
            'produksi',
            'sales',
            'kas_bank',
            'hak',
            'keamanan_audit',
            'lainnya',
        ];
        $sortedModules = [];
        foreach ($order as $k) {
            if (isset($modules[$k])) $sortedModules[$k] = $modules[$k];
        }
        foreach ($modules as $k => $v) {
            if (!isset($sortedModules[$k])) $sortedModules[$k] = $v;
        }

        return $sortedModules;
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('edit-hak-akses');

        // Keamanan 1: Cegah Privilege Escalation pada role Super Admin & Owner
        $isTopLevelRole = in_array(strtolower($role->name), ['super admin', 'owner']);
        $currentUser    = auth()->user();

        if ($isTopLevelRole && !$currentUser->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json([
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah hak akses role tingkat tertinggi ini.'
            ], 403);
        }

        // Keamanan 2: Validasi ketat (whitelist array & pastikan permission terdaftar di database)
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'permissions.array'    => 'Format data hak akses tidak valid.',
            'permissions.*.exists' => 'Terdapat hak akses yang tidak terdaftar di sistem.',
        ]);

        $permissions = $request->permissions ?? [];

        // Kestabilan 1: Mencegah Super Admin kehilangan izin pengelolaan hak akses vital
        if (strtolower($role->name) === 'super admin') {
            $vitalPermissions = ['lihat-hak-akses', 'edit-hak-akses'];
            foreach ($vitalPermissions as $vital) {
                if (!in_array($vital, $permissions)) {
                    $permissions[] = $vital;
                }
            }
        }

        try {
            DB::beginTransaction();

            $role->syncPermissions($permissions);

            DB::commit();

            // Kestabilan 2 & Kecepatan: Reset cache Spatie & cache tree modul
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            Cache::forget('role_permissions_modules_tree');

            // Keamanan 3: Pencatatan Audit Trail ke UserActivityLog & Log File
            try {
                UserActivityLog::create([
                    'user_id'        => $currentUser->id,
                    'username'       => $currentUser->username ?? $currentUser->name ?? 'User #' . $currentUser->id,
                    'activity'       => 'Update Hak Akses: ' . $role->name,
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'status'         => 'Success',
                    'status_message' => 'Berhasil menyinkronkan ' . count($permissions) . ' hak akses untuk role ' . $role->name,
                ]);
            } catch (\Throwable $logErr) {
                Log::warning('Gagal mencatat UserActivityLog saat update permissions: ' . $logErr->getMessage());
            }

            Log::info('Hak akses role berhasil diperbarui', [
                'role'              => $role->name,
                'by_user_id'        => $currentUser->id,
                'by_username'       => $currentUser->username ?? $currentUser->name,
                'permissions_count' => count($permissions),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hak akses untuk role ' . $role->name . ' berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update permissions: ' . $e->getMessage(), [
                'role'  => $role->name,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat memperbarui hak akses: ' . $e->getMessage()
            ], 500);
        }
    }
}
