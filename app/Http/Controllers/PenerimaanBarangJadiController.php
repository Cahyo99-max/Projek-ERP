<?php

namespace App\Http\Controllers;

use App\Helpers\AccountMappingHelper;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Karyawan;
use App\Models\KartuStok;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\MasterLokasiRak;
use App\Models\SpkPenerimaanBarangJadi;
use App\Models\SpkPenerimaanBarangJadiDetail;
use App\Models\SpkPengeluaranBahanDetail;
use App\Models\SpkProduksi;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PenerimaanBarangJadiController extends Controller
{
    /**
     * Display listing of Finished Goods Receipts (Hasil Produksi SPK).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-penerimaan-barang-jadi')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Penerimaan Barang Jadi SPK.');
        }

        if ($request->ajax()) {
            $query = SpkProduksi::query()
                ->forActiveCompany()
                ->forActiveBranch()
                ->where(function ($q) {
                    $q->where('status', 'In Progress')
                      ->orWhere('status', 'Completed')
                      ->orWhereHas('penerimaanBarangJadis');
                })
                ->with([
                    'barangJadi.satuan',
                    'satuan',
                    'tahapans',
                    'materials',
                    'penerimaanBarangJadis' => function ($q) {
                        $q->latest('id')->with(['gudang', 'penerima', 'penyerah']);
                    }
                ])
                ->latest('id');

            if ($request->filled('gudang_id') && $request->gudang_id !== 'all') {
                $query->whereHas('penerimaanBarangJadis', fn($q) => $q->where('gudang_id', $request->gudang_id));
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereHas('penerimaanBarangJadis', fn($q) => $q->whereBetween('tanggal_penerimaan', [$request->start_date, $request->end_date]));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('spk_info', function ($spk) {
                    $kode = $spk->barangJadi ? e($spk->barangJadi->kode_barang) : '-';
                    $nama = $spk->barangJadi ? e($spk->barangJadi->nama_barang) : '-';
                    $satuan = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi && $spk->barangJadi->satuan ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');
                    $qtyRencana = number_format($spk->jumlah_rencana, 0, ',', '.');
                    return '<div>
                        <div class="fw-bold text-info font-monospace fs-6 mb-1 d-flex align-items-center gap-1.5">
                            <i class="bi bi-file-earmark-check"></i>
                            <span>' . e($spk->nomor_spk) . '</span>
                        </div>
                        <div class="fw-bold mb-1" style="font-size: 0.92rem;">
                            <span class="badge spk-badge-cyan rounded-2 px-2 py-0.5 font-monospace me-1" style="font-size: 11px;">' . $kode . '</span>
                            <span class="spk-text-contrast fw-bold">' . $nama . '</span>
                        </div>
                        <div>
                            <span class="badge spk-badge-muted rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 11px;">
                                Target: <b>' . $qtyRencana . '</b> ' . $satuan . '
                            </span>
                        </div>
                    </div>';
                })
                ->addColumn('progress_hasil', function ($spk) {
                    $target = (float)$spk->jumlah_rencana;
                    $selesai = (float)$spk->penerimaanBarangJadis->sum('jumlah_hasil_baik');
                    $reject = (float)$spk->penerimaanBarangJadis->sum('jumlah_reject');
                    $satuan = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi && $spk->barangJadi->satuan ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');

                    $rawPercent = $target > 0 ? ($selesai / $target) * 100 : 0;

                    if ($selesai >= $target && $target > 0) {
                        $badgeClass = 'spk-badge-green';
                        $statusText = 'Lengkap (100%)';
                        $barGradient = 'background: linear-gradient(90deg, #10b981, #059669);';
                        $barWidth = 100;
                    } elseif ($selesai > 0) {
                        $badgeClass = 'spk-badge-cyan';
                        if ($rawPercent < 0.1) {
                            $pctFormatted = number_format($rawPercent, 2, ',', '.') . '%';
                        } elseif ($rawPercent < 1) {
                            $pctFormatted = number_format($rawPercent, 1, ',', '.') . '%';
                        } else {
                            $pctFormatted = round($rawPercent, 1) . '%';
                        }
                        $statusText = 'Sebagian (' . $pctFormatted . ')';
                        $barGradient = 'background: linear-gradient(90deg, #3b82f6, #06b6d4);';
                        $barWidth = max(2, min(100, round($rawPercent, 1)));
                    } else {
                        $badgeClass = 'spk-badge-amber';
                        $statusText = 'Belum Ada Hasil (0%)';
                        $barGradient = 'background: linear-gradient(90deg, #f59e0b, #d97706);';
                        $barWidth = 0;
                    }

                    $rejectText = $reject > 0 ? ' • <span class="text-danger fw-bold"><i class="bi bi-x-circle me-0.5"></i>Reject: ' . number_format($reject, 0, ',', '.') . ' ' . $satuan . '</span>' : '';

                    return '<div style="min-width: 180px;">
                        <div class="d-flex justify-content-between align-items-center mb-1.5" style="font-size: 11px;">
                            <span class="badge rounded-pill px-2.5 py-0.5 ' . $badgeClass . ' font-monospace fw-bold">' . $statusText . '</span>
                            <span class="spk-text-muted font-monospace fw-semibold">' . number_format($selesai, 0, ',', '.') . ' / ' . number_format($target, 0, ',', '.') . ' ' . $satuan . '</span>
                        </div>
                        <div class="progress shadow-inner" style="height: 7px; border-radius: 99px; background-color: rgba(128,128,128,0.2);">
                            <div class="progress-bar rounded-pill" role="progressbar" style="width: ' . $barWidth . '%; ' . $barGradient . '" aria-valuenow="' . $barWidth . '" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="small mt-1 font-monospace" style="font-size: 10.5px;">' . $rejectText . '</div>
                    </div>';
                })
                ->addColumn('frekuensi_sesi', function ($spk) {
                    $count = $spk->penerimaanBarangJadis->count();
                    $last = $spk->penerimaanBarangJadis->first();
                    $lastDate = $last && $last->tanggal_penerimaan ? $last->tanggal_penerimaan->format('d/m/Y') : '-';

                    if ($count === 0) {
                        return '<span class="badge spk-badge-muted rounded-pill px-3 py-1 font-monospace fw-semibold" style="font-size: 11px;"><i class="bi bi-inbox me-1"></i>Belum ada penyerahan</span>';
                    }

                    return '<div>
                        <span class="badge spk-badge-blue rounded-pill px-3 py-1 font-monospace fw-bold" style="font-size: 11.5px;">
                            <i class="bi bi-box-arrow-in-down-right me-1"></i>' . $count . ' Sesi Penerimaan
                        </span>
                        <div class="small spk-text-muted mt-1 font-monospace" style="font-size: 11px;">
                            <i class="bi bi-clock me-1 text-primary"></i>Terakhir: ' . $lastDate . '
                        </div>
                    </div>';
                })
                ->addColumn('status_spk', function ($spk) {
                    if ($spk->status === 'In Progress') {
                        return '<span class="badge spk-badge-cyan rounded-pill px-3 py-1 fw-bold spk-badge-pulse" style="font-size: 11.5px;"><i class="bi bi-play-circle-fill me-1"></i>In Progress</span>';
                    } elseif ($spk->status === 'Completed') {
                        return '<span class="badge spk-badge-green rounded-pill px-3 py-1 fw-bold" style="font-size: 11.5px;"><i class="bi bi-check-circle-fill me-1"></i>Completed</span>';
                    }
                    return '<span class="badge spk-badge-muted rounded-pill px-3 py-1" style="font-size: 11.5px;">' . e($spk->status) . '</span>';
                })
                ->addColumn('action', function ($spk) use ($user) {
                    $canTambah       = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('tambah-penerimaan-barang-jadi');
                    $canDetail       = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('detail-penerimaan-barang-jadi') || $user->can('lihat-penerimaan-barang-jadi');
                    $canLihatJurnal  = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-jurnal-penerimaan-barang-jadi');
                    $canSesuaikanHpp = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('sesuaikan-hpp-penerimaan-barang-jadi');

                    $totalSelesai = (float) $spk->penerimaanBarangJadis->sum('jumlah_hasil_baik');
                    $targetQty    = (float) $spk->jumlah_rencana;
                    $hasBahan     = $spk->pengeluaranBahans->count() > 0;

                    // Terkunci HANYA jika total hasil diterima sudah mencapai atau melebihi target qty
                    $isLocked = ($targetQty > 0) && ($totalSelesai >= $targetQty);

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-2 flex-nowrap text-nowrap">';

                    if (!$isLocked && $canTambah && !in_array($spk->status, ['Draft', 'Dibatalkan', 'Canceled'])) {
                        if ($hasBahan) {
                            $btn .= '<button type="button" class="btn btn-sm btn-emerald-vibrant rounded-pill px-3 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5"
                                        onclick="terimaBarangJadiSpk(' . $spk->id . ')"
                                        title="Terima Hasil Produksi Barang Jadi">
                                        <i class="bi bi-box-arrow-in-down fs-6"></i>
                                        <span>Terima Hasil</span>
                                     </button>';
                        } else {
                            $btn .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5"
                                        onclick="warnBahanBelumKeluar(\'' . e($spk->nomor_spk) . '\')"
                                        title="Bahan baku SPK ini belum dicatat keluar dari gudang!">
                                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                        <span>Belum Ada Bahan</span>
                                     </button>';
                        }
                    } elseif ($isLocked) {
                        $btn .= '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1.5 font-monospace fw-bold d-inline-flex align-items-center gap-1.5" style="font-size: 11px;" title="Target produksi telah 100% selesai (' . number_format($totalSelesai, 0, ',', '.') . '/' . number_format($targetQty, 0, ',', '.') . ') - Terkunci">
                                    <i class="bi bi-lock-fill text-warning"></i> Selesai (Terkunci)
                                 </span>';
                    }

                    if ($canDetail) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5"
                                    onclick="showSpkHistoryModal(' . $spk->id . ')"
                                    title="Lihat Detail & Riwayat Hasil Produksi">
                                    <i class="bi bi-clock-history"></i>
                                    <span>Riwayat</span>
                                 </button>';
                    }

                    // Tombol Lihat Jurnal (hanya jika pengguna berhak & ada sesi penerimaan)
                    if ($canLihatJurnal && $spk->penerimaanBarangJadis->count() > 0) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5"
                                    onclick="showSpkJurnalModal(' . $spk->id . ', \'' . e($spk->nomor_spk) . '\')"
                                    title="Lihat Jurnal Akuntansi Penerimaan">
                                    <i class="bi bi-journal-bookmark-fill" style="color:#6366f1;"></i>
                                    <span>Jurnal</span>
                                 </button>';
                    }

                    if ($canSesuaikanHpp) {
                        // 1. Realisasi Selesai (Semua tahapan selesai)
                        $realisasiSelesai = $spk->tahapans->count() > 0 && $spk->tahapans->filter(function ($t) {
                            return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
                        })->count() === 0;

                        // 2. Pengeluaran Bahan Baku Selesai
                        $bahanSelesai = !$spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->exists();

                        // 3. Barang Masuk Selesai (Jumlah diterima >= Target SPK)
                        $barangMasukSelesai = ($targetQty > 0) && ($totalSelesai >= $targetQty);

                        $allCompleted = $realisasiSelesai && $bahanSelesai && $barangMasukSelesai;

                        if ($spk->is_hpp_closed) {
                            if ($user->hasRole('Super Admin')) {
                                $btn .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5"
                                            onclick="bukaKunciHppSpk(' . $spk->id . ', \'' . e($spk->nomor_spk) . '\')"
                                            title="HPP SPK ini berstatus Closed (Terkunci). Klik untuk Buka Kunci (Unlock) sebagai Super Admin">
                                            <i class="bi bi-lock-fill text-warning"></i>
                                            <span>HPP Closed (Buka)</span>
                                         </button>';
                            } else {
                                $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5 opacity-75"
                                            onclick="showHppClosedWarning(\'' . e($spk->nomor_spk) . '\')"
                                            title="Penyesuaian HPP Terkunci (Closed) - Hanya Super Admin yang dapat membuka kunci">
                                            <i class="bi bi-lock-fill text-warning"></i>
                                            <span>HPP Closed</span>
                                         </button>';
                            }
                        } elseif ($allCompleted) {
                            $btn .= '<button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5"
                                        onclick="bukaModalSesuaikanHpp(' . $spk->id . ')"
                                        title="Sesuaikan HPP: Input Gaji & Overhead Aktual">
                                        <i class="bi bi-calculator-fill text-success"></i>
                                        <span>Sesuaikan HPP</span>
                                     </button>';
                        } else {
                            $pendings = [];
                            if (!$realisasiSelesai) $pendings[] = 'Realisasi Produksi Belum Selesai';
                            if (!$bahanSelesai) $pendings[] = 'Pengeluaran Bahan Baku Belum Selesai';
                            if (!$barangMasukSelesai) $pendings[] = 'Penerimaan Barang Jadi Belum Selesai';
                            $pendingText = implode(', ', $pendings);

                            $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5 opacity-65"
                                        onclick="showHppLockedWarning(\'' . e($pendingText) . '\')"
                                        title="Penyesuaian HPP Terkunci: ' . e($pendingText) . '">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                        <span>Sesuaikan HPP</span>
                                     </button>';
                        }
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['spk_info', 'progress_hasil', 'frekuensi_sesi', 'status_spk', 'action'])
                ->make(true);
        }

        // Active SPK List for select dropdown (Hanya yang In Progress, Pengeluaran Bahan 100% Selesai, & Realisasi 100% Selesai)
        $spkList = SpkProduksi::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'In Progress')
            ->whereHas('pengeluaranBahans')
            ->whereDoesntHave('materials', function ($qm) {
                $qm->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan');
            })
            ->whereDoesntHave('tahapans', function ($qt) {
                $qt->where('status', '!=', 'Selesai');
            })
            ->with(['barangJadi.satuan', 'satuan'])
            ->orderBy('id', 'desc')
            ->get();

        // Warehouse list (Barang Jadi / Umum)
        $gudangList = MasterGudang::forActiveCompany()
            ->active()
            ->orderBy('nama_gudang', 'asc')
            ->get();

        // Employees for Penerima & Penyerah
        $karyawanList = Karyawan::forActiveCompany()
            ->active()
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        // Master Rack Locations
        $rakList = MasterLokasiRak::forActiveCompany()
            ->active()
            ->with('gudang')
            ->orderBy('kode_rak', 'asc')
            ->get();

        // Aggregate Metrics for Header Statistics Cards (Optimized single-pass query for 7M+ scale)
        $totalSpkSelesai = SpkProduksi::forActiveCompany()->forActiveBranch()->where('status', 'Completed')->count();

        $pbgStats = SpkPenerimaanBarangJadi::forActiveCompany()
            ->forActiveBranch()
            ->selectRaw('COUNT(*) as total_sesi, COALESCE(SUM(jumlah_hasil_baik), 0) as total_baik, COALESCE(SUM(jumlah_reject), 0) as total_reject')
            ->first();

        $totalSesiPenerimaan = $pbgStats ? (int)$pbgStats->total_sesi : 0;
        $totalHasilBaik      = $pbgStats ? (float)$pbgStats->total_baik : 0.0;
        $totalHasilReject    = $pbgStats ? (float)$pbgStats->total_reject : 0.0;

        return view('modul_produksi.barang_jadi.index', compact(
            'spkList', 'gudangList', 'karyawanList', 'rakList',
            'totalSpkSelesai', 'totalHasilBaik', 'totalHasilReject', 'totalSesiPenerimaan'
        ));
    }

    /**
     * Get JSON details of SPK for finished goods receipt modal.
     */
    /**
     * Calculate ONLY the actual raw material cost total for an SPK
     * (without TK/Overhead — used specifically for HPP adjustment display).
     */
    private function calculateActualMaterialCost(SpkProduksi $spk): float
    {
        $spk->loadMissing(['pengeluaranBahans.details.barangMentah']);

        $pengeluaranIds = $spk->pengeluaranBahans->pluck('id')->toArray();
        if (!empty($pengeluaranIds)) {
            // 1. First attempt: fetch exact historical monetary value of material issued from KartuStok
            $kartuStokTotal = (float)KartuStok::where('referensi_jenis', 'Pengeluaran Bahan SPK')
                ->whereIn('referensi_id', $pengeluaranIds)
                ->sum('nilai_total');

            if ($kartuStokTotal > 0) {
                return round($kartuStokTotal, 4);
            }
        }

        // 2. Fallback: calculate using detail quantities & average price if KartuStok entries are missing
        $totalMaterialCost = 0.0;
        foreach ($spk->pengeluaranBahans as $pengeluaran) {
            foreach ($pengeluaran->details as $detail) {
                $qty = (float)$detail->qty_diambil_sekarang;
                if ($qty <= 0) continue;

                $barangId = $detail->barang_mentah_id;
                $stokG = StokGudang::where('barang_id', $barangId)
                    ->where('gudang_id', $pengeluaran->gudang_id)
                    ->first();

                $hppItem = ($stokG && (float)$stokG->harga_pokok_rata_rata > 0)
                    ? (float)$stokG->harga_pokok_rata_rata
                    : ($detail->barangMentah ? (float)($detail->barangMentah->harga_pokok_rata_rata ?: $detail->barangMentah->harga_pokok) : 0);

                $totalMaterialCost += ($qty * $hppItem);
            }
        }

        return round($totalMaterialCost, 4);
    }

    /**
     * Helper to calculate SPK unit HPP (Harga Pokok Produksi per unit).
     * Uses stored estimasi_biaya_tk & estimasi_biaya_overhead for TK/overhead components.
     * Uses total actual good yield produced (if available) to ensure 100% absorption of production costs into Finished Goods inventory.
     */
    private function calculateSpkUnitHpp(SpkProduksi $spk): float
    {
        $targetQty = (float)$spk->jumlah_rencana;
        if ($targetQty <= 0) return 0.0;

        $spk->loadMissing(['bom', 'barangJadi']);

        $totalMaterialCost = $this->calculateActualMaterialCost($spk);

        if ($totalMaterialCost > 0) {
            // Determine divisor: use actual good yield produced if available, otherwise fallback to target qty
            $selesaiQty = (float)SpkPenerimaanBarangJadi::where('spk_id', $spk->id)
                ->whereNull('deleted_at')
                ->sum('jumlah_hasil_baik');

            $divisorQty = ($selesaiQty > 0) ? $selesaiQty : $targetQty;

            // HPP = (Biaya Bahan Baku Aktual + Gaji Aktual yg sudah disimpan + Overhead Aktual) / Divisor Qty
            $totalProductionCost = $totalMaterialCost
                + (float)$spk->estimasi_biaya_tk
                + (float)$spk->estimasi_biaya_overhead;
            return round($totalProductionCost / $divisorQty, 4);
        }

        if ((float)$spk->estimasi_total_biaya > 0) {
            return round((float)$spk->estimasi_total_biaya / $targetQty, 4);
        }

        if ($spk->bom && (float)$spk->bom->total_estimasi_hpp > 0) {
            return (float)$spk->bom->total_estimasi_hpp;
        }

        if ($spk->barangJadi && (float)$spk->barangJadi->harga_pokok_rata_rata > 0) {
            return (float)$spk->barangJadi->harga_pokok_rata_rata;
        }

        return 0.0;
    }

    /**
     * Get JSON details of SPK for finished goods receipt modal.
     */
    public function getSpkDetail($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-penerimaan-barang-jadi') && !$user->can('lihat-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        try {
            $spk = SpkProduksi::forActiveCompany()
                ->forActiveBranch()
                ->with(['barangJadi.satuan', 'satuan', 'tahapans'])
                ->findOrFail($spkId);

            // Check if any stage is not yet finished
            $incompleteStages = $spk->tahapans->filter(function ($t) {
                return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
            });

            if ($incompleteStages->count() > 0) {
                $names = $incompleteStages->pluck('nama_tahapan')->map(fn($n) => "'{$n}'")->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "Penerimaan ditolak! Proses Realisasi Produksi SPK #{$spk->nomor_spk} belum selesai sepenuhnya (tahapan: {$names}). Harap selesaikan seluruh tahapan realisasi produksi terlebih dahulu!"
                ], 422);
            }

            // Strict Validation Option 2: Ensure at least 1 Pengeluaran Bahan exists
            if (!$spk->pengeluaranBahans()->whereNull('deleted_at')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "Penerimaan Barang Jadi Ditolak! Transaksi Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} belum dicatat oleh tim gudang bahan. Harap minta tim gudang bahan mencatat Pengeluaran Bahan terlebih dahulu!"
                ], 422);
            }

            $targetQty = (float)$spk->jumlah_rencana;
            $selesaiQty = (float)SpkPenerimaanBarangJadi::forActiveCompany()->forActiveBranch()->where('spk_id', $spkId)->whereNull('deleted_at')->sum('jumlah_hasil_baik');
            $rejectQty = (float)SpkPenerimaanBarangJadi::forActiveCompany()->forActiveBranch()->where('spk_id', $spkId)->whereNull('deleted_at')->sum('jumlah_reject');
            $sisaTarget = max(0, round($targetQty - $selesaiQty, 4));

            $hppPerUnit = $this->calculateSpkUnitHpp($spk);

            // Gudang default hasil produksi (Gudang Barang Jadi)
            $defaultGudang = MasterGudang::forActiveCompany()
                ->active()
                ->where('nama_gudang', 'like', '%Barang Jadi%')
                ->first() ?: MasterGudang::forActiveCompany()->active()->first();

            // Calculate ONLY actual material cost (without TK/overhead) for HPP adjustment form display
            $actualMaterialCost = $this->calculateActualMaterialCost($spk);

            return response()->json([
                'success'                 => true,
                'spk'                     => $spk,
                'target_qty'              => $targetQty,
                'jumlah_selesai'          => $selesaiQty,
                'jumlah_reject'           => $rejectQty,
                'sisa_target'             => $sisaTarget,
                'total_material_cost'     => round($actualMaterialCost, 4),
                'estimasi_hpp_per_unit'   => $hppPerUnit,
                'is_hpp_closed'           => (bool)$spk->is_hpp_closed,
                'hpp_closed_at'           => $spk->hpp_closed_at ? $spk->hpp_closed_at->format('d/m/Y H:i') : null,
                'default_gudang_id'       => $defaultGudang ? $defaultGudang->id : null,
                'default_gudang_kode'     => $defaultGudang ? $defaultGudang->kode_gudang : null,
                'default_gudang_nama'     => $defaultGudang ? $defaultGudang->nama_gudang : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('getSpkDetail error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat detail SPK: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get metadata + recap for SPK finished goods history modal.
     */
    public function showSpkHistory($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('detail-penerimaan-barang-jadi') && !$user->can('lihat-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat riwayat SPK.'], 403);
        }

        try {
            $spk = SpkProduksi::forActiveCompany()
                ->forActiveBranch()
                ->with(['barangJadi.satuan', 'satuan'])
                ->findOrFail($spkId);

            $totalSesi = SpkPenerimaanBarangJadi::forActiveCompany()->forActiveBranch()->where('spk_id', $spkId)
                ->whereNull('deleted_at')
                ->count();

            $totalHasilBaik = (float)SpkPenerimaanBarangJadi::forActiveCompany()->forActiveBranch()->where('spk_id', $spkId)
                ->whereNull('deleted_at')
                ->sum('jumlah_hasil_baik');

            $totalReject = (float)SpkPenerimaanBarangJadi::forActiveCompany()->forActiveBranch()->where('spk_id', $spkId)
                ->whereNull('deleted_at')
                ->sum('jumlah_reject');

            $totalNilaiHpp = (float)SpkPenerimaanBarangJadi::forActiveCompany()->forActiveBranch()->where('spk_id', $spkId)
                ->whereNull('deleted_at')
                ->sum('total_nilai_hpp');

            return response()->json([
                'success'           => true,
                'spk'               => $spk,
                'total_sesi'        => $totalSesi,
                'total_hasil_baik'  => $totalHasilBaik,
                'total_reject'      => $totalReject,
                'total_nilai_hpp'   => $totalNilaiHpp,
            ]);
        } catch (\Throwable $e) {
            Log::error('showSpkHistory error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat riwayat SPK: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Paginated history sessions for finished goods receipts (25/page).
     */
    public function getSpkHistorySessions(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('detail-penerimaan-barang-jadi') && !$user->can('lihat-penerimaan-barang-jadi') && !$user->can('lihat-jurnal-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        try {
            $spk = SpkProduksi::forActiveCompany()->forActiveBranch()->findOrFail($spkId);
            $perPage = (int)$request->get('per_page', 25);
            $perPage = max(5, min(100, $perPage));

            $sessions = SpkPenerimaanBarangJadi::forActiveCompany()
                ->forActiveBranch()
                ->where('spk_id', $spkId)
                ->with([
                    'gudang:id,kode_gudang,nama_gudang',
                    'lokasiRak:id,kode_rak,nama_rak',
                    'barangJadi:id,kode_barang,nama_barang,satuan_id',
                    'barangJadi.satuan:id,nama_satuan',
                    'satuan:id,nama_satuan',
                    'penerima:id,nama_lengkap',
                    'penyerah:id,nama_lengkap',
                    'creator:id,nama_lengkap',
                    'jurnal.details.account:id,code,name',
                ])
                ->latest('id')
                ->paginate($perPage);

            return response()->json([
                'success'      => true,
                'data'         => $sessions->items(),
                'current_page' => $sessions->currentPage(),
                'last_page'    => $sessions->lastPage(),
                'per_page'     => $sessions->perPage(),
                'total'        => $sessions->total(),
                'from'         => $sessions->firstItem(),
                'to'           => $sessions->lastItem(),
            ]);
        } catch (\Throwable $e) {
            Log::error('getSpkHistorySessions error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat sesi riwayat: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store new Finished Goods Receipt (Penerimaan Barang Jadi).
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menerima hasil produksi.'], 403);
        }

        $request->validate([
            'spk_id'                => 'required|exists:spk_produksi,id',
            'gudang_id'             => 'required|exists:master_gudang,id',
            'lokasi_rak_id'         => 'nullable|exists:master_lokasi_rak,id',
            'tanggal_penerimaan'   => 'required|date',
            'jumlah_hasil_baik'     => 'required|numeric|min:0.0001',
            'jumlah_reject'        => 'nullable|numeric|min:0',
            'penerima_karyawan_id'  => 'nullable|exists:karyawans,id',
            'penerima_nama'         => 'required|string|max:100',
            'penyerah_karyawan_id'  => 'nullable|exists:karyawans,id',
            'penyerah_nama'         => 'required|string|max:100',
            'catatan'               => 'nullable|string|max:255',
        ], [
            'jumlah_hasil_baik.required' => 'Jumlah hasil produksi baik wajib diisi!',
            'jumlah_hasil_baik.min'      => 'Jumlah hasil produksi baik minimal 0,0001!',
            'penerima_nama.required'     => 'Nama penerima / petugas gudang wajib diisi!',
            'penyerah_nama.required'     => 'Nama penyerah / operator QC wajib diisi!',
        ]);

        $spk = SpkProduksi::forActiveCompany()->forActiveBranch()->with('tahapans')->findOrFail($request->spk_id);

        // Strict Validation Rule 1: Realisasi Tahapan Produksi (Pola/Cutting/Sewing/QC/Packing) Wajib 100% Selesai
        $incompleteStages = $spk->tahapans->filter(function ($t) {
            return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
        });

        if ($incompleteStages->count() > 0) {
            $names = $incompleteStages->pluck('nama_tahapan')->map(fn($n) => "'{$n}'")->implode(', ');
            return response()->json([
                'message' => "Penerimaan ditolak! Proses Realisasi Tahapan Produksi SPK #{$spk->nomor_spk} belum 100% selesai (tahapan belum selesai: {$names}). Harap selesaikan seluruh tahapan realisasi produksi terlebih dahulu!"
            ], 422);
        }

        // Strict Validation Rule 2: Pengeluaran Bahan Baku Wajib 100% Selesai dari Gudang
        $unfinishedMaterials = $spk->materials()
            ->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')
            ->with('barangMentah')
            ->get();

        if ($unfinishedMaterials->count() > 0) {
            $matNames = $unfinishedMaterials->map(fn($m) => $m->barangMentah ? $m->barangMentah->nama_barang : 'Bahan Mentah')->implode(', ');
            return response()->json([
                'message' => "Penerimaan ditolak! Transaksi Pengeluaran Bahan Baku untuk SPK #{$spk->nomor_spk} belum 100% selesai dikeluarkan dari gudang (bahan belum 100% keluar: {$matNames}). Harap selesaikan pengeluaran bahan 100% terlebih dahulu!"
            ], 422);
        }

        $companyId = session('active_company_id') ?: ($spk->company_id ?: 1);
        $branchId  = session('active_branch_id') ?: ($spk->branch_id ?: null);

        $qtyGood = round((float)$request->jumlah_hasil_baik, 4);
        $qtyReject = round((float)($request->jumlah_reject ?: 0), 4);

        // Auto generate document number: PBJ/YYYY/MM/XXXXX
        $yearMonth = date('Y/m', strtotime($request->tanggal_penerimaan));
        $prefix = "PBJ/{$yearMonth}/";
        $lastNo = SpkPenerimaanBarangJadi::withTrashed()
            ->where('nomor_penerimaan', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('nomor_penerimaan');

        $nextSeq = 1;
        if ($lastNo && preg_match('/\/(\d+)$/', $lastNo, $m)) {
            $nextSeq = (int)$m[1] + 1;
        }

        do {
            $nomorPenerimaan = $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
            $exists = SpkPenerimaanBarangJadi::withTrashed()
                ->where('nomor_penerimaan', $nomorPenerimaan)
                ->exists();
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        DB::beginTransaction();
        try {
            // Update SPK wages & overhead if supplied from the receipt form
            $tk = $request->filled('biaya_tenaga_kerja') ? round((float)$request->biaya_tenaga_kerja, 4) : (float)$spk->estimasi_biaya_tk;
            $oh = $request->filled('biaya_overhead') ? round((float)$request->biaya_overhead, 4) : (float)$spk->estimasi_biaya_overhead;
            $spk->update([
                'estimasi_biaya_tk'      => $tk,
                'estimasi_biaya_overhead'=> $oh,
                'estimasi_total_biaya'   => round((float)$spk->estimasi_biaya_bahan + $tk + $oh, 4)
            ]);

            // Calculate HPP Satuan
            $hppSatuan = $request->filled('hpp_satuan') && (float)$request->hpp_satuan > 0 
                ? round((float)$request->hpp_satuan, 4) 
                : $this->calculateSpkUnitHpp($spk);
            $totalNilaiHpp = round($qtyGood * $hppSatuan, 4);

            $penerimaan = SpkPenerimaanBarangJadi::create([
                'company_id'           => $companyId,
                'branch_id'            => $branchId,
                'nomor_penerimaan'     => $nomorPenerimaan,
                'tanggal_penerimaan'   => $request->tanggal_penerimaan,
                'spk_id'               => $spk->id,
                'barang_jadi_id'       => $spk->barang_jadi_id,
                'gudang_id'            => $request->gudang_id,
                'lokasi_rak_id'        => $request->lokasi_rak_id ?: null,
                'satuan_id'            => $spk->satuan_id,
                'jumlah_hasil_baik'    => $qtyGood,
                'jumlah_reject'        => $qtyReject,
                'hpp_satuan'           => $hppSatuan,
                'total_nilai_hpp'      => $totalNilaiHpp,
                'penerima_karyawan_id' => $request->penerima_karyawan_id ?: null,
                'penerima_nama'        => trim($request->penerima_nama),
                'penyerah_karyawan_id' => $request->penyerah_karyawan_id ?: null,
                'penyerah_nama'        => $request->penyerah_nama ? trim($request->penyerah_nama) : null,
                'status'               => 'Diterima',
                'catatan'              => $request->catatan,
                'created_by'           => $user->id,
            ]);

            // Add Inventory Stock in StokGudang for Finished Goods
            $stokGudang = StokGudang::firstOrCreate(
                ['barang_id' => $spk->barang_jadi_id, 'gudang_id' => $request->gudang_id],
                ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
            );

            $stokAwal = (float)$stokGudang->stok_aktual;
            $nilaiAwal = (float)$stokGudang->nilai_persediaan;
            $stokAkhir = $stokAwal + $qtyGood;
            $nilaiAkhir = $nilaiAwal + $totalNilaiHpp;
            $hppAvgNew = $stokAkhir > 0 ? round($nilaiAkhir / $stokAkhir, 6) : (float)$stokGudang->harga_pokok_rata_rata;

            $stokGudang->update([
                'stok_aktual'            => $stokAkhir,
                'nilai_persediaan'       => $nilaiAkhir,
                'harga_pokok_rata_rata'  => $hppAvgNew,
            ]);
            StokGudang::syncMasterHpp($spk->barang_jadi_id);

            // Record KartuStok entry (Masuk)
            KartuStok::create([
                'barang_id'       => $spk->barang_jadi_id,
                'gudang_id'       => $request->gudang_id,
                'tipe'            => 'Masuk',
                'jumlah'          => $qtyGood,
                'stok_awal'       => $stokAwal,
                'stok_akhir'      => $stokAkhir,
                'harga_satuan'    => $hppSatuan,
                'nilai_total'     => $totalNilaiHpp,
                'referensi_jenis' => 'Penerimaan Barang Jadi SPK',
                'referensi_id'    => $penerimaan->id,
                'referensi_nomor' => $penerimaan->nomor_penerimaan,
                'keterangan'      => "Hasil produksi barang jadi dari SPK #{$spk->nomor_spk}",
            ]);

            // Post Accounting Journal
            $this->createJurnalPenerimaanBarangJadi($penerimaan);

            // Update SPK counters (jumlah_selesai & jumlah_reject)
            $spk->increment('jumlah_selesai', $qtyGood);
            if ($qtyReject > 0) {
                $spk->increment('jumlah_reject', $qtyReject);
            }

            // Check if SPK target quantity is met and all other components (tahapans, materials) are done
            $spk->refresh();
            $realisasiSelesai = $spk->tahapans->count() > 0 && $spk->tahapans->filter(function ($t) {
                return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
            })->count() === 0;

            $bahanSelesai = !$spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->exists();
            $barangMasukSelesai = ((float)$spk->jumlah_selesai >= (float)$spk->jumlah_rencana);

            if ($realisasiSelesai && $bahanSelesai && $barangMasukSelesai) {
                $spk->update(['status' => 'Completed']);
            } else {
                $spk->update(['status' => 'In Progress']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Hasil produksi {$nomorPenerimaan} sebanyak {$qtyGood} item berhasil diterima ke gudang!",
                'id'      => $penerimaan->id
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error store penerimaan barang jadi: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal mencatat penerimaan barang jadi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show detail of Finished Goods Receipt transaction.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('detail-penerimaan-barang-jadi')) {
            abort(403, 'Anda tidak memiliki hak akses.');
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()
            ->forActiveBranch()
            ->with([
                'company',
                'branch',
                'spk.barangJadi.satuan',
                'spk.satuan',
                'barangJadi.satuan',
                'gudang',
                'lokasiRak',
                'penerima',
                'penyerah',
                'creator',
                'jurnal.details.account'
            ])
            ->findOrFail($id);

        return response()->json(['success' => true, 'penerimaan' => $penerimaan]);
    }

    /**
     * Update Finished Goods Receipt transaction with inventory & accounting rebalancing.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-penerimaan-barang-jadi') && !$user->can('tambah-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses.']);
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()->forActiveBranch()->with(['spk.tahapans'])->findOrFail($id);

        $request->validate([
            'gudang_id'           => 'required|exists:master_gudang,id',
            'lokasi_rak_id'       => 'nullable|exists:master_lokasi_rak,id',
            'tanggal_penerimaan' => 'required|date',
            'jumlah_hasil_baik'   => 'required|numeric|min:0.0001',
            'jumlah_reject'      => 'nullable|numeric|min:0',
            'hpp_satuan'          => 'nullable|numeric|min:0',
            'penerima_karyawan_id'=> 'nullable|exists:karyawans,id',
            'penerima_nama'       => 'required|string|max:100',
            'penyerah_karyawan_id'=> 'nullable|exists:karyawans,id',
            'penyerah_nama'       => 'required|string|max:100',
            'catatan'             => 'nullable|string|max:255',
        ], [
            'jumlah_hasil_baik.required' => 'Jumlah hasil produksi baik wajib diisi!',
            'jumlah_hasil_baik.min'      => 'Jumlah hasil produksi baik minimal 0,0001!',
            'penerima_nama.required'     => 'Nama penerima / petugas gudang wajib diisi!',
            'penyerah_nama.required'     => 'Nama penyerah / operator QC wajib diisi!',
        ]);

        $qtyGoodOld = (float)$penerimaan->jumlah_hasil_baik;
        $qtyRejectOld = (float)$penerimaan->jumlah_reject;
        $spk = $penerimaan->spk;

        if ($spk) {
            $incompleteStages = $spk->tahapans->filter(function ($t) {
                return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
            });

            if ($incompleteStages->count() > 0) {
                $names = $incompleteStages->pluck('nama_tahapan')->map(fn($n) => "'{$n}'")->implode(', ');
                return response()->json([
                    'message' => "Update ditolak! Proses Realisasi Produksi SPK #{$spk->nomor_spk} belum selesai sepenuhnya (tahapan: {$names}). Harap selesaikan seluruh tahapan realisasi produksi terlebih dahulu!"
                ], 422);
            }
        }

        if ($spk && !$spk->pengeluaranBahans()->whereNull('deleted_at')->exists()) {
            return response()->json([
                'message' => "Update ditolak! SPK #{$spk->nomor_spk} belum mencatat transaksi Pengeluaran Bahan Baku dari gudang."
            ], 422);
        }

        $qtyGoodNew = round((float)$request->jumlah_hasil_baik, 4);
        $qtyRejectNew = round((float)($request->jumlah_reject ?: 0), 4);

        DB::beginTransaction();
        try {
            // Update SPK wages & overhead if supplied from the receipt form
            if ($spk) {
                $tk = $request->filled('biaya_tenaga_kerja') ? round((float)$request->biaya_tenaga_kerja, 4) : (float)$spk->estimasi_biaya_tk;
                $oh = $request->filled('biaya_overhead') ? round((float)$request->biaya_overhead, 4) : (float)$spk->estimasi_biaya_overhead;
                $spk->update([
                    'estimasi_biaya_tk'      => $tk,
                    'estimasi_biaya_overhead'=> $oh,
                    'estimasi_total_biaya'   => round((float)$spk->estimasi_biaya_bahan + $tk + $oh, 4)
                ]);
            }

            // Calculate HPP Satuan
            $hppSatuanNew = $request->filled('hpp_satuan') && (float)$request->hpp_satuan > 0 
                ? round((float)$request->hpp_satuan, 4) 
                : ((float)$penerimaan->hpp_satuan > 0 ? (float)$penerimaan->hpp_satuan : $this->calculateSpkUnitHpp($spk));
            $totalNilaiHppNew = round($qtyGoodNew * $hppSatuanNew, 4);

            // Revert Old Inventory Stock
            $stokGudangOld = StokGudang::firstOrCreate(
                ['barang_id' => $penerimaan->barang_jadi_id, 'gudang_id' => $penerimaan->gudang_id],
                ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
            );

            $stokAktualOld = (float)$stokGudangOld->stok_aktual;
            $stokAfterRevert = max(0, $stokAktualOld - $qtyGoodOld);
            $nilaiAfterRevert = max(0, (float)$stokGudangOld->nilai_persediaan - (float)$penerimaan->total_nilai_hpp);
            $hppOldAvg = $stokAfterRevert > 0 ? round($nilaiAfterRevert / $stokAfterRevert, 6) : (float)$stokGudangOld->harga_pokok_rata_rata;

            $stokGudangOld->update([
                'stok_aktual'            => $stokAfterRevert,
                'nilai_persediaan'       => $nilaiAfterRevert,
                'harga_pokok_rata_rata'  => $hppOldAvg,
            ]);

            // Record Reversal of Old Stock in KartuStok for Old Gudang
            if ($qtyGoodOld > 0) {
                KartuStok::create([
                    'barang_id'       => $penerimaan->barang_jadi_id,
                    'gudang_id'       => $penerimaan->gudang_id,
                    'tipe'            => 'Keluar',
                    'jumlah'          => $qtyGoodOld,
                    'stok_awal'       => $stokAktualOld,
                    'stok_akhir'      => $stokAfterRevert,
                    'harga_satuan'    => (float)$penerimaan->hpp_satuan,
                    'nilai_total'     => (float)$penerimaan->total_nilai_hpp,
                    'referensi_jenis' => 'Revisi Penerimaan Barang Jadi SPK',
                    'referensi_id'    => $penerimaan->id,
                    'referensi_nomor' => $penerimaan->nomor_penerimaan,
                    'keterangan'      => "Pembatalan/revisi hasil produksi lama untuk SPK #{$spk->nomor_spk}",
                ]);
            }

            // Add New Inventory Stock in Selected Gudang
            $stokGudangNew = StokGudang::firstOrCreate(
                ['barang_id' => $penerimaan->barang_jadi_id, 'gudang_id' => $request->gudang_id],
                ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
            );

            $stokAwalNew = (float)$stokGudangNew->stok_aktual;
            $nilaiAwalNew = (float)$stokGudangNew->nilai_persediaan;
            $stokAkhirNew = $stokAwalNew + $qtyGoodNew;
            $nilaiAkhirNew = $nilaiAwalNew + $totalNilaiHppNew;
            $hppNewAvg = $stokAkhirNew > 0 ? round($nilaiAkhirNew / $stokAkhirNew, 6) : (float)$stokGudangNew->harga_pokok_rata_rata;

            $stokGudangNew->update([
                'stok_aktual'            => $stokAkhirNew,
                'nilai_persediaan'       => $nilaiAkhirNew,
                'harga_pokok_rata_rata'  => $hppNewAvg,
            ]);
            StokGudang::syncMasterHpp($penerimaan->barang_jadi_id);

            // Record New KartuStok entry
            KartuStok::create([
                'barang_id'       => $penerimaan->barang_jadi_id,
                'gudang_id'       => $request->gudang_id,
                'tipe'            => 'Masuk',
                'jumlah'          => $qtyGoodNew,
                'stok_awal'       => $stokAwalNew,
                'stok_akhir'      => $stokAkhirNew,
                'harga_satuan'    => $hppSatuanNew,
                'nilai_total'     => $totalNilaiHppNew,
                'referensi_jenis' => 'Penerimaan Barang Jadi SPK',
                'referensi_id'    => $penerimaan->id,
                'referensi_nomor' => $penerimaan->nomor_penerimaan,
                'keterangan'      => "Revisi hasil produksi barang jadi untuk SPK #{$spk->nomor_spk}",
            ]);

            // Rebalance SPK Counters safely
            $diffGood = $qtyGoodNew - $qtyGoodOld;
            $diffReject = $qtyRejectNew - $qtyRejectOld;

            if ($diffGood != 0) {
                $newSelesai = max(0, (float)$spk->jumlah_selesai + $diffGood);
                $spk->update(['jumlah_selesai' => $newSelesai]);
            }
            if ($diffReject != 0) {
                $newReject = max(0, (float)$spk->jumlah_reject + $diffReject);
                $spk->update(['jumlah_reject' => $newReject]);
            }

            $spk->refresh();
            $realisasiSelesai = $spk->tahapans->count() > 0 && $spk->tahapans->filter(function ($t) {
                return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
            })->count() === 0;

            $bahanSelesai = !$spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->exists();
            $barangMasukSelesai = ((float)$spk->jumlah_selesai >= (float)$spk->jumlah_rencana);

            if ($realisasiSelesai && $bahanSelesai && $barangMasukSelesai) {
                $spk->update(['status' => 'Completed']);
            } else {
                $spk->update(['status' => 'In Progress']);
            }

            // Update Header
            $penerimaan->update([
                'gudang_id'            => $request->gudang_id,
                'lokasi_rak_id'        => $request->lokasi_rak_id ?: null,
                'tanggal_penerimaan'   => $request->tanggal_penerimaan,
                'jumlah_hasil_baik'    => $qtyGoodNew,
                'jumlah_reject'        => $qtyRejectNew,
                'hpp_satuan'           => $hppSatuanNew,
                'total_nilai_hpp'      => $totalNilaiHppNew,
                'penerima_karyawan_id' => $request->penerima_karyawan_id ?: null,
                'penerima_nama'        => trim($request->penerima_nama),
                'penyerah_karyawan_id' => $request->penyerah_karyawan_id ?: null,
                'penyerah_nama'        => $request->penyerah_nama ? trim($request->penyerah_nama) : null,
                'catatan'              => $request->catatan,
                'updated_by'           => $user->id,
            ]);

            // Re-post Accounting Journal
            $this->createJurnalPenerimaanBarangJadi($penerimaan);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sesi penerimaan {$penerimaan->nomor_penerimaan} berhasil diperbarui & mutasi stok disesuaikan!",
                'id'      => $penerimaan->id
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error update penerimaan barang jadi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete & revert Finished Goods Receipt transaction.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('hapus-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()->forActiveBranch()->with('spk')->findOrFail($id);

        DB::beginTransaction();
        try {
            $qtyGood = (float)$penerimaan->jumlah_hasil_baik;
            $qtyReject = (float)$penerimaan->jumlah_reject;
            $spk = $penerimaan->spk;

            // Rollback SPK counters safely
            if ($spk) {
                $newSelesai = max(0, (float)$spk->jumlah_selesai - $qtyGood);
                $newReject = max(0, (float)$spk->jumlah_reject - $qtyReject);
                $spk->update([
                    'jumlah_selesai' => $newSelesai,
                    'jumlah_reject'  => $newReject,
                ]);
                $spk->refresh();
                if ((float)$spk->jumlah_selesai < (float)$spk->jumlah_rencana && $spk->status === 'Completed') {
                    $spk->update(['status' => 'In Progress']);
                }
            }

            // Deduct inventory stock in StokGudang
            $stokGudang = StokGudang::firstOrCreate(
                ['barang_id' => $penerimaan->barang_jadi_id, 'gudang_id' => $penerimaan->gudang_id],
                ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
            );

            $stokAwal = (float)$stokGudang->stok_aktual;
            $stokAkhir = max(0, $stokAwal - $qtyGood);
            $nilaiAwal = (float)$stokGudang->nilai_persediaan;
            $nilaiAkhir = max(0, $nilaiAwal - (float)$penerimaan->total_nilai_hpp);
            $hppAvgNew = $stokAkhir > 0 ? round($nilaiAkhir / $stokAkhir, 6) : (float)$stokGudang->harga_pokok_rata_rata;

            $stokGudang->update([
                'stok_aktual'            => $stokAkhir,
                'nilai_persediaan'       => $nilaiAkhir,
                'harga_pokok_rata_rata'  => $hppAvgNew,
            ]);
            StokGudang::syncMasterHpp($penerimaan->barang_jadi_id);

            // Record KartuStok Reversal (Keluar)
            KartuStok::create([
                'barang_id'       => $penerimaan->barang_jadi_id,
                'gudang_id'       => $penerimaan->gudang_id,
                'tipe'            => 'Keluar',
                'jumlah'          => $qtyGood,
                'stok_awal'       => $stokAwal,
                'stok_akhir'      => $stokAkhir,
                'harga_satuan'    => (float)$penerimaan->hpp_satuan,
                'nilai_total'     => (float)$penerimaan->total_nilai_hpp,
                'referensi_jenis' => 'Pembatalan Penerimaan Barang Jadi SPK',
                'referensi_id'    => $penerimaan->id,
                'referensi_nomor' => $penerimaan->nomor_penerimaan,
                'keterangan'      => "Pembatalan penerimaan hasil produksi SPK #{$penerimaan->nomor_penerimaan}",
            ]);

            // Delete associated Jurnal if any
            Jurnal::where('referensi_tipe', 'Penerimaan Barang Jadi SPK')
                ->where('referensi_id', $penerimaan->id)
                ->delete();

            $penerimaan->update([
                'status'     => 'Dibatalkan',
                'deleted_by' => $user->id,
            ]);
            $penerimaan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Penerimaan hasil produksi {$penerimaan->nomor_penerimaan} berhasil dibatalkan & stok dikembalikan!"
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error delete penerimaan barang jadi: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membatalkan penerimaan barang jadi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create automatic accounting journal (Debit: Finished Goods Inventory, Credit: WIP).
     */
    public function createJurnalPenerimaanBarangJadi(SpkPenerimaanBarangJadi $penerimaan): void
    {
        try {
            $totalNilai = (float)$penerimaan->total_nilai_hpp;
            if ($totalNilai <= 0) return;

            $spk = $penerimaan->spk ?: SpkProduksi::find($penerimaan->spk_id);
            $spkNomor = $spk ? $spk->nomor_spk : '-';

            // 1. Debit Account: Persediaan Barang Jadi (Finished Goods)
            $bjBarang = $penerimaan->barangJadi ?: MasterBarang::find($penerimaan->barang_jadi_id);
            $catId = $bjBarang ? ($bjBarang->kategori_barang_id ?? $bjBarang->kategori_id) : null;

            $fgCoaId = ($catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory") : null)
                    ?? AccountMappingHelper::getCoaId('default', 'default_inventory');

            if (!$fgCoaId) {
                $fgCoa = ChartOfAccount::forActiveCompany()
                    ->where(function ($q) {
                        $q->where('code', '1109')
                          ->orWhere('code', 'like', '1109%')
                          ->orWhere('name', 'like', '%Persediaan Barang Jadi%')
                          ->orWhere('name', 'like', '%Finished Goods%');
                    })
                    ->where('is_header', false)
                    ->first();
                $fgCoaId = $fgCoa ? $fgCoa->id : null;
            }

            if (!$fgCoaId) {
                $fgCoaId = ChartOfAccount::forActiveCompany()
                    ->where('name', 'like', '%Persediaan%')
                    ->where('is_header', false)
                    ->value('id');
            }

            // 2. Credit Account: WIP / Persediaan Barang Dalam Proses
            $wipCoaId = ($catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_wip") : null)
                     ?? AccountMappingHelper::getCoaId('production', 'wip_inventory')
                     ?? AccountMappingHelper::getCoaId('default', 'default_wip');

            if (!$wipCoaId) {
                $wipCoa = ChartOfAccount::forActiveCompany()
                    ->where(function ($q) {
                        $q->where('code', '1108')
                          ->orWhere('code', 'like', '1108%')
                          ->orWhere('name', 'like', '%WIP%')
                          ->orWhere('name', 'like', '%Dalam Proses%')
                          ->orWhere('name', 'like', '%Setengah Jadi%');
                    })
                    ->where('is_header', false)
                    ->first();
                $wipCoaId = $wipCoa ? $wipCoa->id : null;
            }

            if (!$wipCoaId) {
                $wipCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory')
                         ?? ChartOfAccount::forActiveCompany()->where('code', 'like', '11%')->where('is_header', false)->value('id');
            }

            if (!$fgCoaId || !$wipCoaId) {
                Log::warning("Accounting Journal for FG Receipt #{$penerimaan->nomor_penerimaan} skipped due to missing COA mappings (FG: {$fgCoaId}, WIP: {$wipCoaId}).");
                return;
            }

            // Create or Update Jurnal safely (handling soft-deleted or existing nomor_jurnal collisions)
            $jurnal = Jurnal::withTrashed()
                ->where('referensi_tipe', 'Penerimaan Barang Jadi SPK')
                ->where('referensi_id', $penerimaan->id)
                ->first();

            if ($jurnal) {
                $jurnal->restore();
                $jurnal->update([
                    'branch_id'      => $penerimaan->branch_id,
                    'tanggal_jurnal' => $penerimaan->tanggal_penerimaan,
                    'keterangan'     => "Penerimaan Barang Jadi SPK #{$spkNomor} ({$penerimaan->jumlah_hasil_baik} item)",
                    'total_debit'    => $totalNilai,
                    'total_kredit'   => $totalNilai,
                    'updated_by'     => $penerimaan->created_by,
                ]);
            } else {
                $baseNo = 'JRN/FG/' . substr($penerimaan->nomor_penerimaan, 4);
                $nomorJurnal = $baseNo;
                $counter = 1;
                while (Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists()) {
                    $nomorJurnal = $baseNo . '-' . $counter++;
                }

                $jurnal = Jurnal::create([
                    'referensi_tipe' => 'Penerimaan Barang Jadi SPK',
                    'referensi_id'   => $penerimaan->id,
                    'branch_id'      => $penerimaan->branch_id,
                    'nomor_jurnal'   => $nomorJurnal,
                    'tanggal_jurnal' => $penerimaan->tanggal_penerimaan,
                    'keterangan'     => "Penerimaan Barang Jadi SPK #{$spkNomor} ({$penerimaan->jumlah_hasil_baik} item)",
                    'total_debit'    => $totalNilai,
                    'total_kredit'   => $totalNilai,
                    'created_by'     => $penerimaan->created_by,
                ]);
            }

            // Delete old details if updating
            JurnalDetail::where('jurnal_id', $jurnal->id)->delete();

            // 1. Line Debit: Persediaan Barang Jadi
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $fgCoaId,
                'debit'      => $totalNilai,
                'kredit'     => 0,
                'keterangan' => "Penambahan Persediaan Barang Jadi SPK #{$spkNomor}",
            ]);

            // 2. Line Credit: WIP / Barang Dalam Proses
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $wipCoaId,
                'debit'      => 0,
                'kredit'     => $totalNilai,
                'keterangan' => "Pengurangan WIP / Barang Dalam Proses SPK #{$spkNomor}",
            ]);

        } catch (\Throwable $e) {
            Log::error('createJurnalPenerimaanBarangJadi error: ' . $e->getMessage());
        }
    }

    /**
     * Print PDF Bukti Penerimaan Barang Jadi SPK.
     */
    public function printPdf($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('cetak-penerimaan-barang-jadi')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak bukti penerimaan.');
        }

        $penerimaan = SpkPenerimaanBarangJadi::forActiveCompany()
            ->forActiveBranch()
            ->with([
                'company',
                'branch',
                'spk.barangJadi.satuan',
                'spk.satuan',
                'barangJadi.satuan',
                'gudang',
                'lokasiRak',
                'penerima',
                'penyerah',
                'creator',
            ])
            ->findOrFail($id);

        $company = $penerimaan->company ?? Company::find(session('active_company_id')) ?? Company::first();

        return view('modul_produksi.barang_jadi.print_pdf', compact('penerimaan', 'company'));
    }

    /**
     * Adjust HPP for SPK manually.
     */
    public function adjustHpp(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('sesuaikan-hpp-penerimaan-barang-jadi')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menyesuaikan HPP.'], 403);
        }

        $request->validate([
            'biaya_tenaga_kerja' => 'required|numeric|min:0',
            'biaya_overhead'     => 'required|numeric|min:0',
        ]);

        $spk = SpkProduksi::forActiveCompany()->forActiveBranch()->findOrFail($spkId);

        // Security Validation: If HPP is already closed, only Super Admin can recalculate
        if ($spk->is_hpp_closed && !$user->hasRole('Super Admin')) {
            return response()->json([
                'message' => "Penyesuaian ditolak! Nilai HPP SPK #{$spk->nomor_spk} sudah ditutup (Closed). Hanya Super Admin yang berhak menghitung ulang HPP."
            ], 422);
        }

        DB::beginTransaction();
        try {
            $tk = round((float)$request->biaya_tenaga_kerja, 4);
            $oh = round((float)$request->biaya_overhead, 4);

            // Calculate actual material cost fresh from pengeluaran bahan records
            $actualMaterialCost = $this->calculateActualMaterialCost($spk);

            // Persist the adjusted TK & Overhead, update total biaya, and lock HPP (Close)
            $spk->update([
                'estimasi_biaya_tk'       => $tk,
                'estimasi_biaya_overhead' => $oh,
                'estimasi_biaya_bahan'    => round($actualMaterialCost, 4),
                'estimasi_total_biaya'    => round($actualMaterialCost + $tk + $oh, 4),
                'is_hpp_closed'           => true,
                'hpp_closed_at'           => now(),
                'hpp_closed_by'           => $user->id,
            ]);

            // Refresh model to get saved values, then compute final HPP per unit explicitly
            $spk->refresh();
            $targetQty = (float)$spk->jumlah_rencana;

            // Use actual good yield produced if available to absorb 100% of batch costs into FG inventory
            $selesaiQty = (float)SpkPenerimaanBarangJadi::where('spk_id', $spk->id)
                ->whereNull('deleted_at')
                ->sum('jumlah_hasil_baik');

            $divisorQty = ($selesaiQty > 0) ? $selesaiQty : $targetQty;

            if ($divisorQty <= 0) {
                DB::rollBack();
                return response()->json(['message' => 'Quantity pembagi HPP SPK tidak valid (0).'], 422);
            }

            $finalHppPerUnit = round(($actualMaterialCost + $tk + $oh) / $divisorQty, 4);

            // Propagate HPP to all finished goods receipt records, stock ledgers, and journals
            self::adjustHppForSpkWithRate($spk, $finalHppPerUnit);

            DB::commit();

            return response()->json([
                'success'           => true,
                'message'           => "HPP SPK {$spk->nomor_spk} berhasil disesuaikan & status HPP ditutup (Closed)! HPP per unit: Rp " . number_format($finalHppPerUnit, 2, ',', '.'),
                'hpp_per_unit'      => $finalHppPerUnit,
                'total_biaya_bahan' => $actualMaterialCost,
                'biaya_tk'          => $tk,
                'biaya_overhead'    => $oh,
                'is_hpp_closed'     => true,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('adjustHpp error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyesuaikan HPP: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Unlock HPP for an SPK (Super Admin only).
     */
    public function unlockHpp(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasRole('Super Admin')) {
            return response()->json(['message' => 'Akses ditolak! Hanya Super Admin yang berhak membuka kembali kunci HPP SPK.'], 403);
        }

        $spk = SpkProduksi::forActiveCompany()->forActiveBranch()->findOrFail($spkId);

        $spk->update([
            'is_hpp_closed' => false,
            'hpp_closed_at' => null,
            'hpp_closed_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Kunci HPP SPK #{$spk->nomor_spk} berhasil dibuka kembali oleh Super Admin! Nilai HPP kini dapat disesuaikan ulang."
        ], 200);
    }

    /**
     * Recalculate HPP for SPK using the stored estimasi values (called on general reflows).
     */
    public static function adjustHppForSpk(SpkProduksi $spk): void
    {
        $controller = new self();
        $finalHpp = $controller->calculateSpkUnitHpp($spk);
        if ($finalHpp <= 0) return;
        self::adjustHppForSpkWithRate($spk, $finalHpp);
    }

    /**
     * Apply a specific pre-computed HPP per unit to all FG receipt records, stock ledgers, and journals.
     * Used by adjustHpp (manual adjustment) so TK & overhead are NOT double-counted.
     */
    public static function adjustHppForSpkWithRate(SpkProduksi $spk, float $finalHpp): void
    {
        $controller = new self();

        $penerimaans = SpkPenerimaanBarangJadi::where('spk_id', $spk->id)
            ->whereNull('deleted_at')
            ->get();

        foreach ($penerimaans as $penerimaan) {
            $qtyGood = (float)$penerimaan->jumlah_hasil_baik;
            if ($qtyGood <= 0) continue;

            $oldHppSatuan = (float)$penerimaan->hpp_satuan;
            $oldTotalHpp  = (float)$penerimaan->total_nilai_hpp;
            $newTotalHpp  = round($qtyGood * $finalHpp, 4);
            $diffTotalHpp = $newTotalHpp - $oldTotalHpp;

            $hasJurnal = $penerimaan->jurnal()->exists();

            // Skip if no meaningful change AND journal already exists
            if ($hasJurnal && abs($diffTotalHpp) < 0.0001 && abs($finalHpp - $oldHppSatuan) < 0.0001) {
                continue;
            }

            // Update Penerimaan record
            $penerimaan->update([
                'hpp_satuan'      => $finalHpp,
                'total_nilai_hpp' => $newTotalHpp,
            ]);

            // Update StokGudang
            $stokGudang = StokGudang::firstOrCreate(
                ['barang_id' => $penerimaan->barang_jadi_id, 'gudang_id' => $penerimaan->gudang_id],
                ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
            );

            $nilaiAwal    = (float)$stokGudang->nilai_persediaan;
            $stokAkhir    = (float)$stokGudang->stok_aktual;
            $nilaiAkhir   = max(0, $nilaiAwal + $diffTotalHpp);
            $hppAvgNew    = $stokAkhir > 0 ? round($nilaiAkhir / $stokAkhir, 6) : (float)$stokGudang->harga_pokok_rata_rata;

            $stokGudang->update([
                'nilai_persediaan'      => $nilaiAkhir,
                'harga_pokok_rata_rata' => $hppAvgNew,
            ]);

            StokGudang::syncMasterHpp($penerimaan->barang_jadi_id);

            // Update KartuStok
            $kartuStok = KartuStok::where('referensi_jenis', 'Penerimaan Barang Jadi SPK')
                ->where('referensi_id', $penerimaan->id)
                ->first();

            if ($kartuStok) {
                $kartuStok->update([
                    'harga_satuan' => $finalHpp,
                    'nilai_total'  => $newTotalHpp,
                ]);
            }

            // Re-post Accounting Journal
            $controller->createJurnalPenerimaanBarangJadi($penerimaan);
        }
    }
}
