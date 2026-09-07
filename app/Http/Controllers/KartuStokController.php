<?php

namespace App\Http\Controllers;

use App\Models\KartuStok;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use Illuminate\Http\Request;
use Shuchkin\SimpleXLSXGen;

class KartuStokController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('lihat-kartu-stok');

        $activeBranchId = session('active_branch_id');

        // ── Dropdown data: scoped strictly to active company & active branch ───
        $gudangsQuery = MasterGudang::where('status', 'Active')->forActiveCompany();
        if (!empty($activeBranchId)) {
            $gudangsQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangsQuery->whereNull('cabang_id');
        }
        $gudangs = $gudangsQuery->orderBy('nama_gudang')->get();

        $barangs = MasterBarang::active()->forActiveCompany()->orderBy('nama_barang')->get();

        // ── Validated & sanitised filter inputs ────────────────────────────
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));

        // Security: ensure dates are valid, prevent SQL manipulation
        $startDate = date('Y-m-d', strtotime($startDate)) ?: now()->startOfMonth()->format('Y-m-d');
        $endDate   = date('Y-m-d', strtotime($endDate))   ?: now()->format('Y-m-d');

        // Prevent end_date < start_date
        if ($endDate < $startDate) {
            $endDate = $startDate;
        }

        // ── Security: validate gudang_id belongs to the active branch ───────
        $activeGudangIds = $gudangs->pluck('id');

        $gudangId = null;
        if ($request->filled('gudang_id')) {
            $requestedGudangId = (int) $request->input('gudang_id');
            // Only accept if gudang belongs to active branch
            if ($activeGudangIds->contains($requestedGudangId)) {
                $gudangId = $requestedGudangId;
            }
        }

        // ── Security: validate barang_id is a real barang ───────────────────
        $barangId = null;
        if ($request->filled('barang_id')) {
            $requestedBarangId = (int) $request->input('barang_id');
            if ($barangs->pluck('id')->contains($requestedBarangId)) {
                $barangId = $requestedBarangId;
            }
        }

        // ── Main query: always scoped to active branch's gudangs (Latest First) ───
        $query = KartuStok::with(['barang.satuan', 'gudang.company', 'gudang.cabang'])
            ->whereIn('gudang_id', $activeGudangIds)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc'); // latest transaction first

        if ($barangId) {
            $query->where('barang_id', $barangId);
        }
        if ($gudangId) {
            $query->where('gudang_id', $gudangId);
        }

        $tipeMutasi = $request->input('tipe_mutasi');
        if (!empty($tipeMutasi)) {
            if ($tipeMutasi === 'masuk') {
                $query->where('tipe', 'Masuk')
                      ->where(function ($q) {
                          $q->whereNull('referensi_jenis')
                            ->orWhereNotIn('referensi_jenis', ['Transfer Gudang', 'Transfer Inter-PT', 'Transfer Inter-Cabang', 'Transfer Internal', 'Stok Opname', 'Stock Opname']);
                      });
            } elseif ($tipeMutasi === 'keluar') {
                $query->where('tipe', 'Keluar')
                      ->where(function ($q) {
                          $q->whereNull('referensi_jenis')
                            ->orWhereNotIn('referensi_jenis', ['Transfer Gudang', 'Transfer Inter-PT', 'Transfer Inter-Cabang', 'Transfer Internal', 'Stok Opname', 'Stock Opname']);
                      });
            } elseif ($tipeMutasi === 'transfer') {
                $query->where(function ($q) {
                    $q->where('referensi_jenis', 'like', '%Transfer%')
                      ->orWhere('keterangan', 'like', '%Transfer%');
                });
            } elseif ($tipeMutasi === 'transfer_inter_pt') {
                $query->where(function ($q) {
                    $q->where('referensi_jenis', 'Transfer Inter-PT')
                      ->orWhere('keterangan', 'like', '%Inter-PT%');
                });
            } elseif ($tipeMutasi === 'transfer_inter_cabang') {
                $query->where(function ($q) {
                    $q->where('referensi_jenis', 'Transfer Inter-Cabang')
                      ->orWhere('keterangan', 'like', '%Inter-Cabang%');
                });
            } elseif ($tipeMutasi === 'opname') {
                $query->where(function ($q) {
                    $q->whereIn('referensi_jenis', ['Stok Opname', 'Stock Opname'])
                      ->orWhere('keterangan', 'like', '%Opname%');
                });
            }
        }

        $mutasi = $query->get();

        // ── KPI: Totals ────────────────────────────────────────────────────
        $totalMasuk  = $mutasi->where('tipe', 'Masuk')->sum(fn($r) => floatval($r->jumlah));
        $totalKeluar = $mutasi->where('tipe', 'Keluar')->sum(fn($r) => floatval($r->jumlah));

        // ── KPI: Stok Awal Periode & Stok Akhir Periode ───────────────────
        $stokAwalPeriode  = 0;
        $stokAkhirPeriode = 0;

        if ($mutasi->isNotEmpty()) {
            if ($barangId) {
                // Filter 1 barang spesifik:
                $prevRecord = KartuStok::whereIn('gudang_id', $activeGudangIds)
                    ->where('barang_id', $barangId)
                    ->when($gudangId, fn($q) => $q->where('gudang_id', $gudangId))
                    ->where('created_at', '<', $startDate . ' 00:00:00')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($prevRecord) {
                    $stokAwalPeriode = floatval($prevRecord->stok_akhir);
                } else {
                    $stokAwalPeriode = floatval($mutasi->last()->stok_awal);
                }
                $stokAkhirPeriode = $stokAwalPeriode + $totalMasuk - $totalKeluar;
            } else {
                // Semua Barang (Multi-Barang):
                // Agregasi stok awal dari transaksi terlama setiap kombinasi (barang_id + gudang_id)
                $grouped = $mutasi->groupBy(fn($item) => $item->barang_id . '_' . $item->gudang_id);
                $totalStokAwal = 0;
                foreach ($grouped as $group) {
                    $oldestInPeriod = $group->last(); // mutasi terlama di periode untuk item ini
                    $totalStokAwal += floatval($oldestInPeriod->stok_awal);
                }
                $stokAwalPeriode  = $totalStokAwal;
                $stokAkhirPeriode = $stokAwalPeriode + $totalMasuk - $totalKeluar;
            }
        } else {
            if ($barangId) {
                $prevRecord = KartuStok::whereIn('gudang_id', $activeGudangIds)
                    ->where('barang_id', $barangId)
                    ->when($gudangId, fn($q) => $q->where('gudang_id', $gudangId))
                    ->where('created_at', '<', $startDate . ' 00:00:00')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($prevRecord) {
                    $stokAwalPeriode  = floatval($prevRecord->stok_akhir);
                    $stokAkhirPeriode = floatval($prevRecord->stok_akhir);
                }
            }
        }

        return view('modul_inventori.kartu_stok.kartustok', compact(
            'mutasi',
            'gudangs',
            'barangs',
            'startDate',
            'endDate',
            'barangId',
            'gudangId',
            'tipeMutasi',
            'stokAwalPeriode',
            'totalMasuk',
            'totalKeluar',
            'stokAkhirPeriode'
        ));
    }

    /**
     * Cetak PDF Laporan Kartu Stok & Mutasi
     */
    public function printPdf(Request $request)
    {
        $this->authorize('lihat-kartu-stok');

        $activeBranchId = session('active_branch_id');

        $gudangsQuery = MasterGudang::where('status', 'Active')->forActiveCompany();
        if (!empty($activeBranchId)) {
            $gudangsQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangsQuery->whereNull('cabang_id');
        }
        $gudangs = $gudangsQuery->orderBy('nama_gudang')->get();
        $activeGudangIds = $gudangs->pluck('id');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));
        $startDate = date('Y-m-d', strtotime($startDate)) ?: now()->startOfMonth()->format('Y-m-d');
        $endDate   = date('Y-m-d', strtotime($endDate))   ?: now()->format('Y-m-d');
        if ($endDate < $startDate) $endDate = $startDate;

        $gudangId = $request->filled('gudang_id') && $activeGudangIds->contains((int)$request->input('gudang_id')) ? (int)$request->input('gudang_id') : null;
        $barangId = $request->filled('barang_id') ? (int)$request->input('barang_id') : null;
        $tipeMutasi = $request->input('tipe_mutasi');

        $query = KartuStok::with(['barang.satuan', 'gudang.company', 'gudang.cabang'])
            ->whereIn('gudang_id', $activeGudangIds)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($barangId) $query->where('barang_id', $barangId);
        if ($gudangId) $query->where('gudang_id', $gudangId);

        if (!empty($tipeMutasi)) {
            if ($tipeMutasi === 'masuk') {
                $query->where('tipe', 'Masuk')
                      ->where(function ($q) {
                          $q->whereNull('referensi_jenis')
                            ->orWhereNotIn('referensi_jenis', ['Transfer Gudang', 'Transfer Inter-PT', 'Transfer Inter-Cabang', 'Transfer Internal', 'Stok Opname', 'Stock Opname']);
                      });
            } elseif ($tipeMutasi === 'keluar') {
                $query->where('tipe', 'Keluar')
                      ->where(function ($q) {
                          $q->whereNull('referensi_jenis')
                            ->orWhereNotIn('referensi_jenis', ['Transfer Gudang', 'Transfer Inter-PT', 'Transfer Inter-Cabang', 'Transfer Internal', 'Stok Opname', 'Stock Opname']);
                      });
            } elseif ($tipeMutasi === 'transfer_inter_pt') {
                $query->where(function ($q) {
                    $q->where('referensi_jenis', 'Transfer Inter-PT')
                      ->orWhere('keterangan', 'like', '%Inter-PT%');
                });
            } elseif ($tipeMutasi === 'transfer_inter_cabang') {
                $query->where(function ($q) {
                    $q->where('referensi_jenis', 'Transfer Inter-Cabang')
                      ->orWhere('keterangan', 'like', '%Inter-Cabang%');
                });
            } elseif ($tipeMutasi === 'opname') {
                $query->where(function ($q) {
                    $q->whereIn('referensi_jenis', ['Stok Opname', 'Stock Opname'])
                      ->orWhere('keterangan', 'like', '%Opname%');
                });
            }
        }

        $mutasi = $query->get();

        // ── KPI: Totals ────────────────────────────────────────────────────
        $totalMasuk  = $mutasi->where('tipe', 'Masuk')->sum(fn($r) => floatval($r->jumlah));
        $totalKeluar = $mutasi->where('tipe', 'Keluar')->sum(fn($r) => floatval($r->jumlah));

        // ── KPI: Stok Awal Periode & Stok Akhir Periode ───────────────────
        $stokAwalPeriode  = 0;
        $stokAkhirPeriode = 0;

        if ($mutasi->isNotEmpty()) {
            if ($barangId) {
                $prevRecord = KartuStok::whereIn('gudang_id', $activeGudangIds)
                    ->where('barang_id', $barangId)
                    ->when($gudangId, fn($q) => $q->where('gudang_id', $gudangId))
                    ->where('created_at', '<', $startDate . ' 00:00:00')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($prevRecord) {
                    $stokAwalPeriode = floatval($prevRecord->stok_akhir);
                } else {
                    $stokAwalPeriode = floatval($mutasi->last()->stok_awal);
                }
                $stokAkhirPeriode = $stokAwalPeriode + $totalMasuk - $totalKeluar;
            } else {
                $grouped = $mutasi->groupBy(fn($item) => $item->barang_id . '_' . $item->gudang_id);
                $totalStokAwal = 0;
                foreach ($grouped as $group) {
                    $oldestInPeriod = $group->last();
                    $totalStokAwal += floatval($oldestInPeriod->stok_awal);
                }
                $stokAwalPeriode  = $totalStokAwal;
                $stokAkhirPeriode = $stokAwalPeriode + $totalMasuk - $totalKeluar;
            }
        } else {
            if ($barangId) {
                $prevRecord = KartuStok::whereIn('gudang_id', $activeGudangIds)
                    ->where('barang_id', $barangId)
                    ->when($gudangId, fn($q) => $q->where('gudang_id', $gudangId))
                    ->where('created_at', '<', $startDate . ' 00:00:00')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($prevRecord) {
                    $stokAwalPeriode  = floatval($prevRecord->stok_akhir);
                    $stokAkhirPeriode = floatval($prevRecord->stok_akhir);
                }
            }
        }

        $barang = $barangId ? MasterBarang::find($barangId) : null;
        $gudang = $gudangId ? MasterGudang::find($gudangId) : null;

        return view('modul_inventori.kartu_stok.kartustok_pdf', compact(
            'mutasi',
            'startDate',
            'endDate',
            'barang',
            'gudang',
            'tipeMutasi',
            'stokAwalPeriode',
            'totalMasuk',
            'totalKeluar',
            'stokAkhirPeriode'
        ));
    }

    /**
     * Export Excel (.xls stream high speed)
     */
    public function exportExcel(Request $request)
    {
        $this->authorize('lihat-kartu-stok');

        $activeBranchId = session('active_branch_id');

        $gudangsQuery = MasterGudang::where('status', 'Active')->forActiveCompany();
        if (!empty($activeBranchId)) {
            $gudangsQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangsQuery->whereNull('cabang_id');
        }
        $gudangs = $gudangsQuery->orderBy('nama_gudang')->get();
        $barangs = MasterBarang::active()->forActiveCompany()->orderBy('nama_barang')->get();
        $activeGudangIds = $gudangs->pluck('id');

        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));
        $startDate = date('Y-m-d', strtotime($startDate)) ?: now()->startOfMonth()->format('Y-m-d');
        $endDate   = date('Y-m-d', strtotime($endDate))   ?: now()->format('Y-m-d');
        if ($endDate < $startDate) $endDate = $startDate;

        $gudangId = $request->filled('gudang_id') && $activeGudangIds->contains((int)$request->input('gudang_id')) ? (int)$request->input('gudang_id') : null;
        $barangId = $request->filled('barang_id') ? (int)$request->input('barang_id') : null;
        $tipeMutasi = $request->input('tipe_mutasi');

        $query = KartuStok::with(['barang.satuan', 'gudang.company', 'gudang.cabang'])
            ->whereIn('gudang_id', $activeGudangIds)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($barangId) $query->where('barang_id', $barangId);
        if ($gudangId) $query->where('gudang_id', $gudangId);

        if (!empty($tipeMutasi)) {
            if ($tipeMutasi === 'masuk') {
                $query->where('tipe', 'Masuk')
                      ->where(function ($q) {
                          $q->whereNull('referensi_jenis')
                            ->orWhereNotIn('referensi_jenis', ['Transfer Gudang', 'Transfer Inter-PT', 'Transfer Inter-Cabang', 'Transfer Internal', 'Stok Opname', 'Stock Opname']);
                      });
            } elseif ($tipeMutasi === 'keluar') {
                $query->where('tipe', 'Keluar')
                      ->where(function ($q) {
                          $q->whereNull('referensi_jenis')
                            ->orWhereNotIn('referensi_jenis', ['Transfer Gudang', 'Transfer Inter-PT', 'Transfer Inter-Cabang', 'Transfer Internal', 'Stok Opname', 'Stock Opname']);
                      });
            } elseif ($tipeMutasi === 'transfer_inter_pt') {
                $query->where(function ($q) {
                    $q->where('referensi_jenis', 'Transfer Inter-PT')
                      ->orWhere('keterangan', 'like', '%Inter-PT%');
                });
            } elseif ($tipeMutasi === 'transfer_inter_cabang') {
                $query->where(function ($q) {
                    $q->where('referensi_jenis', 'Transfer Inter-Cabang')
                      ->orWhere('keterangan', 'like', '%Inter-Cabang%');
                });
            } elseif ($tipeMutasi === 'opname') {
                $query->where(function ($q) {
                    $q->whereIn('referensi_jenis', ['Stok Opname', 'Stock Opname'])
                      ->orWhere('keterangan', 'like', '%Opname%');
                });
            }
        }

        $mutasi = $query->get();

        $activeCompanyId = session('active_company_id');

        $activeBranch = null;
        if ($activeBranchId) {
            $activeBranch = \App\Models\Branch::with('company')->find($activeBranchId);
        }

        $activeCompany = null;
        if ($activeBranch && $activeBranch->company) {
            $activeCompany = $activeBranch->company;
        } elseif ($activeCompanyId) {
            $activeCompany = \App\Models\Company::find($activeCompanyId);
        }

        $isBranchActive = !empty($activeBranch);
        $entityTitle    = $isBranchActive ? ($activeBranch->nama_cabang ?: session('active_branch_name', 'Cabang')) : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT PERSADA AMAN SENTOSA'));
        $branchName     = $isBranchActive ? ($activeBranch->nama_cabang ?: session('active_branch_name', 'Cabang')) : (session('active_branch_name') ?: 'Semua Cabang');

        $barangName  = $barangId ? ($barangs->firstWhere('id', $barangId)?->nama_barang ?? 'Semua Barang') : 'Semua Barang';
        $gudangName  = $gudangId ? ($gudangs->firstWhere('id', $gudangId)?->nama_gudang ?? 'Semua Gudang') : 'Semua Gudang';
        $tipeStr     = $tipeMutasi ? strtoupper(str_replace('_', ' ', $tipeMutasi)) : 'SEMUA MUTASI';

        $cleanSlug = \Illuminate\Support\Str::slug($entityTitle, '_');
        $filename  = 'Laporan_Kartu_Stok_' . ($cleanSlug ? $cleanSlug . '_' : '') . date('Ymd_His') . '.xlsx';

        $rows = [
            ['<style bgcolor="#0F274A" color="#FFFFFF" font-size="13"><b>' . mb_strtoupper($entityTitle) . ' - LAPORAN KARTU STOK & MUTASI GUDANG</b>'],
            ['<b>Cabang / Entitas:</b> ' . $branchName . ' | <b>Periode:</b> ' . date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate)) . ' | <b>Waktu Ekspor:</b> ' . date('d/m/Y H:i:s')],
            ['<b>Filter Barang:</b> ' . $barangName . ' | <b>Filter Gudang:</b> ' . $gudangName . ' | <b>Tipe Mutasi:</b> ' . $tipeStr . ' | <b>Total Baris:</b> ' . count($mutasi)],
            [],
            [
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>NO</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>TANGGAL & WAKTU</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>KODE BARANG</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><b>NAMA BARANG</b>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><b>GUDANG</b>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><b>CABANG</b>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>TIPE</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>STOK AWAL</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><right><b>HARGA SATUAN (HPP)</b></right>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>JUMLAH QTY</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><right><b>TOTAL NILAI (RP)</b></right>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>STOK AKHIR</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>REFERENSI JENIS</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><center><b>NOMOR REFERENSI</b></center>',
                '<style bgcolor="#1E40AF" color="#FFFFFF"><b>KETERANGAN</b>'
            ]
        ];

        $totalNilaiMasuk  = 0;
        $totalNilaiKeluar = 0;
        $totalQtyMasuk    = 0;
        $totalQtyKeluar   = 0;

        foreach ($mutasi as $idx => $m) {
            $tglStr = $m->created_at ? $m->created_at->format('d/m/Y H:i:s') : '-';
            
            $fmtStokAwal = (floor($m->stok_awal) == $m->stok_awal) ? number_format($m->stok_awal, 0, ',', '.') : rtrim(rtrim(number_format($m->stok_awal, 4, ',', '.'), '0'), ',');
            $fmtJumlah   = (floor($m->jumlah) == $m->jumlah) ? number_format($m->jumlah, 0, ',', '.') : rtrim(rtrim(number_format($m->jumlah, 4, ',', '.'), '0'), ',');
            $fmtStokAkhir = (floor($m->stok_akhir) == $m->stok_akhir) ? number_format($m->stok_akhir, 0, ',', '.') : rtrim(rtrim(number_format($m->stok_akhir, 4, ',', '.'), '0'), ',');

            $signQty = ($m->tipe === 'Masuk' ? '+' : '-') . $fmtJumlah;
            
            $fmtHarga = $m->harga_satuan > 0 ? 'Rp ' . number_format($m->harga_satuan, 2, ',', '.') : '-';
            $fmtTotalNilai = $m->nilai_total > 0 ? 'Rp ' . number_format($m->nilai_total, 2, ',', '.') : '-';

            if ($m->tipe === 'Masuk') {
                $totalQtyMasuk   += floatval($m->jumlah);
                $totalNilaiMasuk += floatval($m->nilai_total);
                $tipeCell = '<style color="#15803D"><center><b>MASUK</b></center>';
                $qtyCell  = '<style color="#15803D"><right><b>' . $signQty . '</b></right>';
            } else {
                $totalQtyKeluar   += floatval($m->jumlah);
                $totalNilaiKeluar += floatval($m->nilai_total);
                $tipeCell = '<style color="#BE123C"><center><b>KELUAR</b></center>';
                $qtyCell  = '<style color="#BE123C"><right><b>' . $signQty . '</b></right>';
            }

            $rows[] = [
                '<center>' . ($idx + 1) . '</center>',
                '<center>' . $tglStr . '</center>',
                '<center>' . ($m->barang?->kode_barang ?? '-') . '</center>',
                $m->barang?->nama_barang ?? '-',
                $m->gudang?->nama_gudang ?? '-',
                $m->gudang?->cabang?->nama_cabang ?? ($m->gudang?->company?->nama_perusahaan ?? '-'),
                $tipeCell,
                '<right>' . $fmtStokAwal . '</right>',
                '<right>' . $fmtHarga . '</right>',
                $qtyCell,
                '<right>' . $fmtTotalNilai . '</right>',
                '<right><b>' . $fmtStokAkhir . '</b></right>',
                '<center>' . ($m->referensi_jenis ?? '-') . '</center>',
                '<style color="#1D4ED8"><center><b>' . ($m->referensi_nomor ?? '-') . '</b></center>',
                $m->keterangan ?? '-'
            ];
        }

        // Summary footer rows
        $fmtTotMasuk  = (floor($totalQtyMasuk) == $totalQtyMasuk) ? number_format($totalQtyMasuk, 0, ',', '.') : rtrim(rtrim(number_format($totalQtyMasuk, 4, ',', '.'), '0'), ',');
        $fmtTotKeluar = (floor($totalQtyKeluar) == $totalQtyKeluar) ? number_format($totalQtyKeluar, 0, ',', '.') : rtrim(rtrim(number_format($totalQtyKeluar, 4, ',', '.'), '0'), ',');

        $fmtTotNilaiMasuk  = 'Rp ' . number_format($totalNilaiMasuk, 2, ',', '.');
        $fmtTotNilaiKeluar = 'Rp ' . number_format($totalNilaiKeluar, 2, ',', '.');

        $rows[] = [
            '<style bgcolor="#DBEAFE" color="#1E3A8A"><center><b>TOTAL</b></center>',
            '<style bgcolor="#DBEAFE" color="#1E3A8A"><b>TOTAL MASUK:</b>',
            '',
            '',
            '',
            '',
            '<style bgcolor="#DBEAFE" color="#15803D"><center><b>MASUK</b></center>',
            '',
            '',
            '<style bgcolor="#DBEAFE" color="#15803D"><right><b>+' . $fmtTotMasuk . '</b></right>',
            '<style bgcolor="#DBEAFE" color="#1E3A8A"><right><b>' . $fmtTotNilaiMasuk . '</b></right>',
            '',
            '',
            '',
            ''
        ];
        if ($totalQtyKeluar > 0) {
            $rows[] = [
                '<style bgcolor="#FEE2E2" color="#991B1B"><center><b>TOTAL</b></center>',
                '<style bgcolor="#FEE2E2" color="#991B1B"><b>TOTAL KELUAR:</b>',
                '',
                '',
                '',
                '',
                '<style bgcolor="#FEE2E2" color="#BE123C"><center><b>KELUAR</b></center>',
                '',
                '',
                '<style bgcolor="#FEE2E2" color="#BE123C"><right><b>-' . $fmtTotKeluar . '</b></right>',
                '<style bgcolor="#FEE2E2" color="#BE123C"><right><b>' . $fmtTotNilaiKeluar . '</b></right>',
                '',
                '',
                '',
                ''
            ];
        }

        if (ob_get_length()) {
            ob_end_clean();
        }
        $xlsx = SimpleXLSXGen::fromArray($rows);
        $xlsx->downloadAs($filename);
        exit();
    }
}
