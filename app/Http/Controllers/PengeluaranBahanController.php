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
use App\Models\SpkPengeluaranBahan;
use App\Models\SpkPengeluaranBahanDetail;
use App\Models\SpkProduksi;
use App\Models\SpkProduksiMaterial;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PengeluaranBahanController extends Controller
{
    /**
     * Display a listing of material issue transactions.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-pengeluaran-bahan')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Pengeluaran Bahan Baku SPK.');
        }

        if ($request->ajax()) {
            $query = SpkProduksi::query()
                ->forActiveCompany()
                ->where(function ($q) {
                    $q->where('status', 'In Progress')
                      ->orWhereHas('pengeluaranBahans')
                      ->orWhere(function ($sub) {
                          $sub->where('status', 'Completed')
                              ->whereHas('materials', function ($qm) {
                                  $qm->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan');
                              });
                      });
                })
                ->with([
                    'barangJadi:id,kode_barang,nama_barang,satuan_id',
                    'barangJadi.satuan:id,nama_satuan',
                    'satuan:id,nama_satuan',
                    'materials:id,spk_id,barang_mentah_id,total_kebutuhan,total_keluar_gudang,satuan_id',
                    'materialClosedBy:id,nama_lengkap,username',
                    'materialClosingRequestedBy:id,nama_lengkap,username',
                    'materialClosingRejectedBy:id,nama_lengkap,username',
                    'pengeluaranBahans' => function ($q) {
                        $q->forActiveCompany()->select('id', 'spk_id', 'nomor_pengeluaran', 'tanggal_pengeluaran')->latest('id');
                    }
                ])
                ->latest('id');

            if ($request->filled('gudang_id') && $request->gudang_id !== 'all') {
                $query->whereHas('pengeluaranBahans', fn($q) => $q->where('gudang_id', $request->gudang_id));
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereHas('pengeluaranBahans', fn($q) => $q->whereBetween('tanggal_pengeluaran', [$request->start_date, $request->end_date]));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('spk_info', function ($spk) {
                    $kode = $spk->barangJadi ? e($spk->barangJadi->kode_barang) : '-';
                    $nama = $spk->barangJadi ? e($spk->barangJadi->nama_barang) : '-';
                    $satuan = $spk->satuan ? $spk->satuan->nama_satuan : ($spk->barangJadi && $spk->barangJadi->satuan ? $spk->barangJadi->satuan->nama_satuan : 'Pcs');
                    $rawRencana = (float) $spk->jumlah_rencana;
                    $qty = ($rawRencana == (int)$rawRencana) ? number_format($rawRencana, 0, ',', '.') : rtrim(rtrim(number_format($rawRencana, 4, ',', '.'), '0'), ',');
                    return '<div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center gap-1.5">
                            <a href="javascript:void(0)" onclick="showSpkHistoryModal(' . $spk->id . ')" class="fw-bold text-primary text-decoration-none font-monospace d-inline-flex align-items-center gap-1" style="font-size: 13px;" title="Lihat Riwayat SPK">
                                <i class="bi bi-file-earmark-ruled"></i>
                                <span>' . e($spk->nomor_spk) . '</span>
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="badge spk-badge-cyan rounded-1 px-1.5 py-0.5 font-monospace" style="font-size: 10.5px;">' . $kode . '</span>
                            <span class="fw-bold spk-text-main" style="font-size: 12.5px;">' . $nama . '</span>
                        </div>
                        <div>
                            <span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-semibold" style="background: rgba(37, 99, 235, 0.08); color: #1d4ed8; border: 1px solid rgba(37, 99, 235, 0.22); font-size: 10.5px;">
                                <i class="bi bi-bullseye me-1 text-primary"></i>Target: <b>' . $qty . '</b> ' . e($satuan) . '
                            </span>
                        </div>
                    </div>';
                })
                ->addColumn('progress_pengeluaran', function ($spk) {
                    $materials = $spk->materials;
                    $totalBahan = $materials->count();
                    if ($totalBahan === 0) {
                        return '<span class="badge rounded-pill px-2.5 py-1 text-secondary border border-secondary border-opacity-25" style="background: rgba(100,116,139,0.08); font-size: 11px;">Tidak ada bahan</span>';
                    }

                    $sumPercent = 0;
                    $itemsCompleted = 0;
                    $totalDikeluarkan = 0;
                    foreach ($materials as $m) {
                        $needed = (float) $m->total_kebutuhan;
                        $issued = (float) $m->total_keluar_gudang;
                        $totalDikeluarkan += $issued;
                        $itemPct = $needed > 0 ? min(100, ($issued / $needed) * 100) : 100;
                        $sumPercent += $itemPct;
                        if ($issued >= $needed && $needed > 0) {
                            $itemsCompleted++;
                        }
                    }

                    $percent = $totalBahan > 0 ? round($sumPercent / $totalBahan, 1) : 0;

                    if ($percent >= 100 || $itemsCompleted === $totalBahan) {
                        $badgeClass = 'spk-badge-green';
                        $barGradient = 'background: linear-gradient(90deg, #10b981, #34d399);';
                        $statusText = 'Lengkap (100%)';
                        $barWidth = 100;
                    } elseif ($percent > 0 || $totalDikeluarkan > 0) {
                        $badgeClass = 'spk-badge-cyan';
                        $barGradient = 'background: linear-gradient(90deg, #0284c7, #38bdf8);';
                        $statusText = $percent . '% Terpenuhi';
                        $barWidth = max(4, min(100, $percent));
                    } else {
                        $badgeClass = 'spk-badge-red';
                        $barGradient = 'background: linear-gradient(90deg, #ef4444, #f87171);';
                        $statusText = 'Belum Keluar';
                        $barWidth = 0;
                    }

                    return '<div style="min-width: 190px; max-width: 250px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge rounded-pill px-2.5 py-0.5 ' . $badgeClass . ' font-monospace fw-bold" style="font-size: 10.5px;">' . $statusText . '</span>
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 font-monospace fw-semibold" style="font-size: 10.5px;">' . $itemsCompleted . '/' . $totalBahan . ' Item Selesai</span>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 99px; background-color: rgba(128,128,128,0.18);">
                            <div class="progress-bar rounded-pill" role="progressbar" style="width: ' . $barWidth . '%; ' . $barGradient . '" aria-valuenow="' . $barWidth . '" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>';
                })
                ->addColumn('frekuensi_sesi', function ($spk) {
                    $count = $spk->pengeluaranBahans->count();
                    $last = $spk->pengeluaranBahans->first();
                    $lastDate = $last && $last->tanggal_pengeluaran ? $last->tanggal_pengeluaran->format('d/m/Y') : '-';

                    if ($count === 0) {
                        return '<span class="badge rounded-pill px-2.5 py-1 small fw-semibold text-secondary bg-light border border-secondary border-opacity-25" style="font-size: 10.5px;">
                            <i class="bi bi-dash-circle me-1"></i>Belum ada sesi
                        </span>';
                    }

                    return '<div class="d-flex flex-column gap-1">
                        <div>
                            <span class="badge spk-badge-blue rounded-pill px-2.5 py-0.5 font-monospace fw-bold" style="font-size: 11px;">
                                <i class="bi bi-file-earmark-text me-1"></i>' . $count . ' Sesi Penyerahan
                            </span>
                        </div>
                        <div class="small spk-text-muted font-monospace" style="font-size: 10.5px;">
                            <i class="bi bi-clock me-1 text-primary"></i>Terakhir: <span class="text-dark fw-semibold">' . $lastDate . '</span>
                        </div>
                    </div>';
                })
                ->addColumn('status_spk', function ($spk) {
                    $statusHtml = '<div class="d-flex flex-column align-items-center justify-content-center gap-1 text-nowrap">';
                    if ($spk->status === 'In Progress') {
                        $statusHtml .= '<span class="badge spk-badge-cyan rounded-pill px-2.5 py-0.5 fw-bold spk-badge-pulse" style="font-size: 10.5px;"><i class="bi bi-play-circle-fill me-1"></i>In Progress</span>';
                    } elseif ($spk->status === 'Completed') {
                        $statusHtml .= '<span class="badge spk-badge-green rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 10.5px;"><i class="bi bi-check-circle-fill me-1"></i>Completed</span>';
                    } else {
                        $statusHtml .= '<span class="badge spk-badge-muted rounded-pill px-2.5 py-0.5" style="font-size: 10.5px;">' . e($spk->status) . '</span>';
                    }

                    $statusClosing = $spk->material_closing_status ?: ($spk->is_material_closed ? 'Closed' : 'Open');

                    if ($statusClosing === 'Closed' || $spk->is_material_closed) {
                        $closedAt = $spk->material_closed_at ? $spk->material_closed_at->format('d/m/Y') : '';
                        $byName = $spk->materialClosedBy?->nama_lengkap ?: ($spk->materialClosedBy?->name ?? 'User');
                        $statusHtml .= '<span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); font-size: 10px;" title="Closed oleh ' . e($byName) . ' (' . $closedAt . ')"><i class="bi bi-lock-fill me-1"></i>Closed</span>';
                    } elseif ($statusClosing === 'Menunggu Closing') {
                        $reqAt = $spk->material_closing_requested_at ? $spk->material_closing_requested_at->format('d/m/Y H:i') : '';
                        $reqBy = $spk->materialClosingRequestedBy?->nama_lengkap ?: ($spk->materialClosingRequestedBy?->name ?? 'Tim Produksi');
                        $statusHtml .= '<span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-bold text-dark shadow-2xs spk-badge-pulse" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); font-size: 10px; border: 1px solid #d97706;" title="Diajukan oleh: ' . e($reqBy) . ' (' . $reqAt . ')"><i class="bi bi-hourglass-split me-1"></i>Menunggu Closing</span>';
                    } elseif ($statusClosing === 'Ditolak') {
                        $rejBy = $spk->materialClosingRejectedBy?->nama_lengkap ?: ($spk->materialClosingRejectedBy?->name ?? 'Accounting');
                        $statusHtml .= '<span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-bold text-white shadow-2xs" style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); font-size: 10px;" title="Ditolak oleh ' . e($rejBy) . ': ' . e($spk->material_closing_reject_reason) . '"><i class="bi bi-x-circle-fill me-1"></i>Closing Ditolak</span>';
                    } else {
                        $statusHtml .= '<span class="badge rounded-pill px-2.5 py-0.5 font-monospace fw-semibold" style="background: rgba(6, 182, 212, 0.12); color: #0284c7; border: 1px solid rgba(6, 182, 212, 0.3); font-size: 10px;" title="Open (Pengeluaran Bahan Terbuka)"><i class="bi bi-unlock-fill me-1"></i>Open</span>';
                    }
                    $statusHtml .= '</div>';
                    return $statusHtml;
                })
                ->addColumn('action', function ($spk) use ($user) {
                    $canTambah      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('tambah-pengeluaran-bahan');
                    $canDetail      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('detail-pengeluaran-bahan') || $user->can('lihat-pengeluaran-bahan');
                    $canAjukan      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('ajukan-closing-pengeluaran-bahan');
                    $canBatalAjukan = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('batal-ajukan-closing-pengeluaran-bahan');
                    $canClosing     = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('closing-pengeluaran-bahan');
                    $canReopen      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('buka-closing-pengeluaran-bahan');
                    $canJurnal      = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-jurnal-pengeluaran-bahan') || $user->can('lihat-jurnal-full-pengeluaran-bahan') || $user->can('lihat-jurnal');

                    $bName = $spk->barangJadi ? addslashes($spk->barangJadi->nama_barang) : '';
                    $bKode = $spk->barangJadi ? addslashes($spk->barangJadi->kode_barang) : '';
                    $spkNomor = addslashes($spk->nomor_spk);
                    $statusClosing = $spk->material_closing_status ?: ($spk->is_material_closed ? 'Closed' : 'Open');

                    $btn = '<div class="d-inline-flex align-items-center justify-content-center gap-1.5 text-nowrap">';
                    
                    $hasUnfinishedMaterials = false;
                    foreach ($spk->materials as $m) {
                        if ((float)$m->total_keluar_gudang < (float)$m->total_kebutuhan) {
                            $hasUnfinishedMaterials = true;
                            break;
                        }
                    }

                    // 1. Tombol Keluarkan Bahan (Hanya jika SPK In Progress/Completed, belum Closed, & status closing bukan Closed/Menunggu)
                    if (in_array($spk->status, ['In Progress', 'Completed']) && $statusClosing !== 'Closed' && $statusClosing !== 'Menunggu Closing' && !$spk->is_material_closed && $canTambah) {
                        $btn .= '<button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 fw-bold shadow-2xs d-inline-flex align-items-center gap-1"
                                    onclick="keluarkanBahanSpk(' . $spk->id . ', \'' . $spkNomor . '\', \'[' . $bKode . '] ' . $bName . '\', \'' . $spk->status . '\')"
                                    title="Keluarkan Bahan Baku untuk SPK ini">
                                    <i class="bi bi-plus-lg"></i>
                                    <span>Keluarkan</span>
                                 </button>';
                    }

                    // 2. Tombol Riwayat Sesi (Selalu ada)
                    if ($canDetail) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                    onclick="showSpkHistoryModal(' . $spk->id . ')"
                                    title="Lihat Detail & Riwayat Sesi Pengeluaran">
                                    <i class="bi bi-clock-history text-info"></i>
                                    <span>Riwayat</span>
                                 </button>';
                    }

                    // 3. Tombol Ajukan Closing (Jika status Open / Ditolak & minimal ada 1 sesi penyerahan)
                    if (($statusClosing === 'Open' || $statusClosing === 'Ditolak') && !$spk->is_material_closed && $canAjukan && $spk->pengeluaranBahans->count() > 0) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 fw-bold shadow-2xs d-inline-flex align-items-center gap-1"
                                    onclick="bukaModalAjukanClosing(' . $spk->id . ', \'' . $spkNomor . '\')"
                                    title="Ajukan Penutupan Pengeluaran Bahan ke Bagian Accounting">
                                    <i class="bi bi-send-check"></i>
                                    <span>Ajukan Closing</span>
                                 </button>';
                    }

                    // 4. Jika Status Menunggu Closing:
                    if ($statusClosing === 'Menunggu Closing' && !$spk->is_material_closed) {
                        // A. Tombol Review & Setujui (Jika user memiliki hak otorisasi closing)
                        if ($canClosing) {
                            $btn .= '<button type="button" class="btn btn-sm btn-warning rounded-pill px-2.5 py-1 fw-bold shadow-2xs d-inline-flex align-items-center gap-1 text-dark"
                                        onclick="bukaModalReviewClosing(' . $spk->id . ', \'' . $spkNomor . '\')"
                                        title="Review & Otorisasi Closing Pengeluaran Bahan">
                                        <i class="bi bi-shield-check"></i>
                                        <span>Review & Setujui</span>
                                     </button>';
                        }

                        // B. Tombol Batal Ajukan (Hanya jika role memiliki izin batal-ajukan-closing-pengeluaran-bahan atau Super Admin / Owner)
                        if ($canBatalAjukan) {
                            $btn .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                        onclick="batalAjukanClosing(' . $spk->id . ', \'' . $spkNomor . '\')"
                                        title="Batalkan Pengajuan Closing">
                                        <i class="bi bi-x-circle"></i>
                                        <span>Batal Ajukan</span>
                                     </button>';
                        }
                    }

                    // 5. Jika Sudah Closed: Tombol Lihat Jurnal Konsolidasi Closing
                    if (($statusClosing === 'Closed' || $spk->is_material_closed) && $canJurnal) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                    onclick="showSpkJurnalClosingModal(' . $spk->id . ', \'' . $spkNomor . '\')"
                                    title="Lihat Jurnal Akuntansi Konsolidasi Pengeluaran Bahan">
                                    <i class="bi bi-journal-check text-primary"></i>
                                    <span>Jurnal</span>
                                 </button>';
                    }

                    // 6. Jika Sudah Closed: Tombol Buka Kembali Closing (Reopen)
                    if (($statusClosing === 'Closed' || $spk->is_material_closed) && $canReopen) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-1 fw-semibold shadow-2xs d-inline-flex align-items-center gap-1"
                                    onclick="konfirmasiReopenBahan(' . $spk->id . ', \'' . $spkNomor . '\')"
                                    title="Buka Kembali Closing Bahan (Reopen)">
                                    <i class="bi bi-unlock-fill"></i>
                                    <span>Reopen</span>
                                 </button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['spk_info', 'progress_pengeluaran', 'frekuensi_sesi', 'status_spk', 'action'])
                ->with([
                    'totalPengeluaran'   => SpkPengeluaranBahan::forActiveCompany()->count(),
                    'spkInProgress'      => SpkProduksi::forActiveCompany()->where('status', 'In Progress')->count(),
                    'pengeluaranHariIni' => SpkPengeluaranBahan::forActiveCompany()->where(function ($q) {
                        $today = date('Y-m-d');
                        $q->whereDate('tanggal_pengeluaran', $today)
                          ->orWhereDate('created_at', $today);
                    })->count(),
                ])
                ->make(true);
        }

        $activeCompanyId = session('active_company_id');

        // SPK yang sudah berjalan (In Progress) dan belum di-closing
        $spkList = SpkProduksi::forActiveCompany()
            ->whereIn('status', ['In Progress', 'Completed'])
            ->where(function ($query) {
                $query->where('is_material_closed', false)
                      ->orWhereNull('is_material_closed');
            })
            ->where(function ($query) {
                $query->whereNull('material_closing_status')
                      ->orWhere('material_closing_status', '!=', 'Closed');
            })
            ->with(['barangJadi', 'satuan', 'materials'])
            ->orderBy('id', 'desc')
            ->get();

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        // Master Gudang list (Bahan Baku / Umum) - DISARING KETAT SESUAI CABANG VS PT PUSAT
        $gudangQuery = MasterGudang::forActiveCompany()->active();
        if (!empty($activeBranchId)) {
            $gudangQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangQuery->whereNull('cabang_id');
        }
        $gudangList = $gudangQuery->orderBy('nama_gudang', 'asc')->get();

        // Master Karyawan for Penyerah & Penerima
        $karyawanList = Karyawan::forActiveCompany()
            ->active()
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('modul_produksi.pengeluaran_bahan.index', compact('spkList', 'gudangList', 'karyawanList'));
    }

    /**
     * Get SPK metadata + material recap (without loading all sessions — paginated separately).
     * Fix N+1: gunakan single GROUP BY query untuk rekap bahan, bukan loop per material.
     */
    public function showSpkHistory($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('detail-pengeluaran-bahan') && !$user->can('lihat-pengeluaran-bahan')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat riwayat SPK.'], 403);
        }

        try {
            $spk = SpkProduksi::forActiveCompany()
                ->with([
                    'barangJadi.satuan',
                    'satuan',
                    'materials.barangMentah.satuan',
                    'materials.satuan',
                ])
                ->findOrFail($spkId);

            // ── Fix N+1: ambil semua issued qty dalam SATU query GROUP BY ──
            $materialIds = $spk->materials->pluck('id');

            $issuedQtyMap = SpkPengeluaranBahanDetail::whereIn('spk_material_id', $materialIds)
                ->whereHas('pengeluaran', fn($q) => $q->whereNull('deleted_at'))
                ->groupBy('spk_material_id')
                ->selectRaw('spk_material_id, COALESCE(SUM(qty_diambil_sekarang), 0) as total_issued')
                ->pluck('total_issued', 'spk_material_id');

            // Hitung total sesi untuk ditampilkan di header
            $totalSesi = SpkPengeluaranBahan::where('spk_id', $spkId)
                ->whereNull('deleted_at')
                ->count();

            // Calculate recap of materials (no more N+1 — O(n) in PHP, 1 DB query)
            $materialsRekap = [];
            foreach ($spk->materials as $m) {
                $bm = $m->barangMentah;
                $satName = $m->satuan ? $m->satuan->nama_satuan : ($bm && $bm->satuan ? $bm->satuan->nama_satuan : 'Pcs');
                $qtyTotal = (float)$m->total_kebutuhan;
                $alreadyIssuedFromMap      = (float)($issuedQtyMap[$m->id] ?? 0);
                $alreadyIssuedFromMaterial = (float)$m->total_keluar_gudang;
                $alreadyIssued             = max($alreadyIssuedFromMap, $alreadyIssuedFromMaterial);

                $sisaKebutuhan = max(0, round($qtyTotal - $alreadyIssued, 4));
                $rawPercent    = $qtyTotal > 0 ? ($alreadyIssued / $qtyTotal) * 100 : 0;
                $percent       = $alreadyIssued >= $qtyTotal && $qtyTotal > 0 ? 100 : ($alreadyIssued > 0 ? round($rawPercent, 2) : 0);

                $materialsRekap[] = [
                    'spk_material_id'    => $m->id,
                    'barang_mentah_id'   => $m->barang_mentah_id,
                    'kode_barang'        => $bm ? $bm->kode_barang : '-',
                    'nama_barang'        => $bm ? $bm->nama_barang : '-',
                    'satuan_nama'        => $satName,
                    'qty_total_spk'      => $qtyTotal,
                    'qty_sudah_diambil'  => $alreadyIssued,
                    'qty_sisa_kebutuhan' => $sisaKebutuhan,
                    'percent'            => $percent,
                ];
            }

            return response()->json([
                'success'         => true,
                'spk'             => $spk,
                'materials_rekap' => $materialsRekap,
                'total_sesi'      => $totalSesi,
            ]);
        } catch (\Throwable $e) {
            Log::error('showSpkHistory error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat SPK: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Paginated history sessions for a given SPK (25 per page by default).
     * Endpoint: GET /produksi/pengeluaran-bahan/spk/{spkId}/history/sessions?page=1&per_page=25
     */
    public function getSpkHistorySessions(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('detail-pengeluaran-bahan') && !$user->can('lihat-pengeluaran-bahan') && !$user->can('lihat-jurnal-pengeluaran-bahan') && !$user->can('lihat-jurnal')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat sesi riwayat SPK.'], 403);
        }

        try {
            // Validasi SPK milik perusahaan aktif
            $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

            $perPage = (int) $request->get('per_page', 25);
            $perPage = max(5, min(100, $perPage)); // clamp antara 5 - 100

            $sessions = SpkPengeluaranBahan::where('spk_id', $spkId)
                ->with([
                    'gudang:id,kode_gudang,nama_gudang',
                    'penyerah:id,nama_lengkap',
                    'penerima:id,nama_lengkap',
                    'creator:id,nama_lengkap',
                    'details.barangMentah:id,kode_barang,nama_barang,satuan_id',
                    'details.barangMentah.satuan:id,nama_satuan',
                    'details.satuan:id,nama_satuan',
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
            Log::error('getSpkHistorySessions error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat sesi riwayat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get JSON materials for selected SPK with remaining qty calculation.
     */
    public function getSpkMaterials($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-pengeluaran-bahan') && !$user->can('lihat-pengeluaran-bahan')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengambil data bahan SPK.'], 403);
        }
        try {
            $spk = SpkProduksi::forActiveCompany()
                ->with(['materials.barangMentah.satuan', 'materials.satuan', 'barangJadi.satuan'])
                ->findOrFail($spkId);

            if (!in_array($spk->status, ['In Progress', 'Completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Pengeluaran bahan baku ditolak! SPK {$spk->nomor_spk} belum dimulai (Status saat ini: {$spk->status}). Harap tekan tombol 'Mulai' / 'Mulai Produksi' pada Modul SPK terlebih dahulu!"
                ], 422);
            }

            if ($spk->is_material_closed || $spk->material_closing_status === 'Closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengeluaran bahan untuk SPK ini sudah di-Closing. Operasional pengeluaran bahan terkunci!'
                ], 422);
            }

            if ($spk->material_closing_status === 'Menunggu Closing') {
                return response()->json([
                    'success' => false,
                    'message' => 'SPK ini sedang dalam status Menunggu Closing. Batalkan pengajuan closing terlebih dahulu jika ingin mengeluarkan bahan!'
                ], 422);
            }

            $gudangId = request('gudang_id');

            $materials = [];
            foreach ($spk->materials as $m) {
                $bm = $m->barangMentah;
                $satName = $m->satuan ? $m->satuan->nama_satuan : ($bm && $bm->satuan ? $bm->satuan->nama_satuan : 'Pcs');

                $qtyTotal = (float)$m->total_kebutuhan;

                // Cumulative already issued qty from previous SpkPengeluaranBahanDetail entries
                $alreadyIssued = (float)SpkPengeluaranBahanDetail::where('spk_material_id', $m->id)
                    ->whereHas('pengeluaran', fn($q) => $q->whereNull('deleted_at'))
                    ->sum('qty_diambil_sekarang');

                $sisaKebutuhan = max(0, round($qtyTotal - $alreadyIssued, 4));

                // Real-time stock in selected warehouse
                $stokGudang = null;
                if ($gudangId) {
                    $stokGudang = StokGudang::where('barang_id', $m->barang_mentah_id)
                        ->where('gudang_id', $gudangId)
                        ->first();
                }
                $stokTersedia = $stokGudang ? (float)$stokGudang->stok_aktual : 0;

                $materials[] = [
                    'spk_material_id'     => $m->id,
                    'barang_mentah_id'   => $m->barang_mentah_id,
                    'kode_barang'        => $bm ? $bm->kode_barang : '-',
                    'nama_barang'        => $bm ? $bm->nama_barang : '-',
                    'satuan_id'          => $m->satuan_id ?: ($bm ? $bm->satuan_id : null),
                    'satuan_nama'        => $satName,
                    'qty_total_spk'      => $qtyTotal,
                    'qty_sudah_diambil'  => $alreadyIssued,
                    'qty_sisa_kebutuhan' => $sisaKebutuhan,
                    'stok_tersedia_gudang' => $stokTersedia,
                ];
            }

            return response()->json([
                'success'   => true,
                'spk'       => $spk,
                'materials' => $materials
            ]);
        } catch (\Throwable $e) {
            Log::error('getSpkMaterials error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat rincian bahan SPK: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new Material Issue transaction.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-pengeluaran-bahan')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengeluarkan bahan baku.'], 403);
        }

        $request->validate([
            'spk_id'               => 'required|exists:spk_produksi,id',
            'gudang_id'            => 'required|exists:master_gudang,id',
            'tanggal_pengeluaran'  => 'required|date',
            'penyerah_karyawan_id' => 'nullable|exists:karyawans,id',
            'penerima_karyawan_id' => 'nullable|exists:karyawans,id',
            'penerima_nama'        => 'required|string|max:40',
            'catatan'              => 'nullable|string|max:40',
            'items'                => 'required|array|min:1',
            'items.*.spk_material_id'     => 'required|exists:spk_produksi_materials,id',
            'items.*.barang_mentah_id'   => 'required|exists:master_barang,id',
            'items.*.qty_diambil_sekarang'=> 'required|numeric|min:0.0001',
        ], [
            'penerima_nama.required' => 'Nama penerima / operator line wajib diisi!',
            'penerima_nama.max'      => 'Nama penerima / operator line tidak boleh lebih dari 40 karakter!',
            'items.required'         => 'Wajib memilih minimal 1 item bahan baku untuk dikeluarkan!',
        ]);

        // Security Validation: Check duplicate materials in the request
        $matIds = array_column($request->items, 'spk_material_id');
        if (count($matIds) !== count(array_unique($matIds))) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat item bahan baku yang sama dipilih lebih dari 1 kali. Silakan gabungkan kuantitasnya!'
            ], 422);
        }

        $spk = SpkProduksi::forActiveCompany()->findOrFail($request->spk_id);

        if (!in_array($spk->status, ['In Progress', 'Completed'])) {
            return response()->json([
                'success' => false,
                'message' => "Pengeluaran bahan baku ditolak! SPK {$spk->nomor_spk} belum dimulai (Status saat ini: {$spk->status}). Harap tekan tombol 'Mulai' / 'Mulai Produksi' pada Modul SPK terlebih dahulu!"
            ], 422);
        }

        if ($spk->is_material_closed || $spk->material_closing_status === 'Closed') {
            return response()->json([
                'success' => false,
                'message' => 'Pengeluaran bahan untuk SPK ini sudah di-Closing. Operasional pengeluaran bahan terkunci!'
            ], 422);
        }

        if ($spk->material_closing_status === 'Menunggu Closing') {
            return response()->json([
                'success' => false,
                'message' => 'SPK ini sedang dalam status Menunggu Closing. Batalkan pengajuan closing terlebih dahulu jika ingin menambah pengeluaran bahan!'
            ], 422);
        }

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: ($user->company_id ?? 1);

        // Pastikan entitas transaksi mengikuti pemilik SPK / sesi aktif
        $isBranch = !empty($spk->branch_id) || !empty($activeBranchId);
        $finalBranchId  = $isBranch ? ($spk->branch_id ?: $activeBranchId) : null;
        $finalCompanyId = $isBranch ? null : ($spk->company_id ?: $activeCompanyId);

        // Security check: Pastikan gudang yang dipilih sesuai dengan entitas (Cabang vs PT)
        $selectedGudang = MasterGudang::findOrFail($request->gudang_id);
        if ($isBranch) {
            if ($selectedGudang->cabang_id != $finalBranchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gudang yang dipilih tidak sesuai dengan Cabang aktif!'
                ], 422);
            }
        } else {
            if (!empty($selectedGudang->cabang_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gudang yang dipilih adalah milik Cabang, bukan PT Pusat!'
                ], 422);
            }
        }

        // Auto generate document number: MAT-PT/YYYY/MM/XXXXX atau MAT-CBG/YYYY/MM/XXXXX
        $yearMonth = date('Y/m', strtotime($request->tanggal_pengeluaran));
        $prefix = $isBranch ? "MAT-CBG/{$yearMonth}/" : "MAT-PT/{$yearMonth}/";

        $lastNo = SpkPengeluaranBahan::withTrashed()
            ->where('nomor_pengeluaran', 'like', "{$prefix}%")
            ->orderBy('nomor_pengeluaran', 'desc')
            ->orderBy('id', 'desc')
            ->value('nomor_pengeluaran');

        $nextSeq = 1;
        if ($lastNo && preg_match('/\/(\d+)$/', $lastNo, $m)) {
            $nextSeq = (int)$m[1] + 1;
        }

        // Loop safety agar nomor pengeluaran selalu unik dan tidak terjadi tabrakan
        do {
            $nomorPengeluaran = $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
            $exists = SpkPengeluaranBahan::withTrashed()
                ->where('nomor_pengeluaran', $nomorPengeluaran)
                ->exists();
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        DB::beginTransaction();
        try {
            $pengeluaran = SpkPengeluaranBahan::create([
                'company_id'           => $finalCompanyId,
                'branch_id'            => $finalBranchId,
                'nomor_pengeluaran'    => $nomorPengeluaran,
                'tanggal_pengeluaran'  => $request->tanggal_pengeluaran,
                'spk_id'               => $spk->id,
                'gudang_id'            => $request->gudang_id,
                'penyerah_karyawan_id' => $request->penyerah_karyawan_id ?: null,
                'penyerah_nama'        => $user->nama_lengkap ?: ($user->username ?: 'User'),
                'penerima_karyawan_id' => $request->penerima_karyawan_id ?: null,
                'penerima_nama'        => $request->penerima_nama ? trim($request->penerima_nama) : null,
                'status'               => 'Diserahkan',
                'catatan'              => $request->catatan,
                'created_by'           => $user->id,
            ]);

            $itemsJurnal = [];

            foreach ($request->items as $item) {
                $qtyTaken = round((float)$item['qty_diambil_sekarang'], 4);
                if ($qtyTaken <= 0) continue;

                $spkMat = SpkProduksiMaterial::where('spk_id', $spk->id)->findOrFail($item['spk_material_id']);
                $qtyTotal = (float)$spkMat->total_kebutuhan;

                $alreadyIssued = (float)SpkPengeluaranBahanDetail::where('spk_material_id', $spkMat->id)
                    ->whereHas('pengeluaran', fn($q) => $q->whereNull('deleted_at'))
                    ->sum('qty_diambil_sekarang');

                $sisaBefore = max(0, round($qtyTotal - $alreadyIssued, 4));

                // Anti-Negative Stock Validation & Pessimistic Lock (Race Condition Protection)
                $stokGudang = StokGudang::where('barang_id', $item['barang_mentah_id'])
                    ->where('gudang_id', $request->gudang_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stokGudang) {
                    $stokGudang = StokGudang::create([
                        'barang_id'             => $item['barang_mentah_id'],
                        'gudang_id'             => $request->gudang_id,
                        'stok_aktual'           => 0.00,
                        'nilai_persediaan'     => 0.0000,
                        'harga_pokok_rata_rata' => 0.000000
                    ]);
                }

                $stokAwal = (float)$stokGudang->stok_aktual;
                if ($stokAwal < $qtyTaken) {
                    $bmNama = $spkMat->barangMentah ? $spkMat->barangMentah->nama_barang : 'Bahan Mentah';
                    $gudangObj = MasterGudang::find($request->gudang_id);
                    $gudangNama = $gudangObj ? $gudangObj->nama_gudang : 'Gudang Terpilih';
                    $satNama = $spkMat->satuan ? $spkMat->satuan->nama_satuan : 'Item';
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok tidak mencukupi untuk [{$bmNama}] di {$gudangNama}! Stok fisik tersedia: {$stokAwal} {$satNama}, sedangkan yang ingin dikeluarkan: {$qtyTaken} {$satNama}."
                    ], 422);
                }

                SpkPengeluaranBahanDetail::create([
                    'pengeluaran_id'       => $pengeluaran->id,
                    'spk_material_id'      => $spkMat->id,
                    'barang_mentah_id'    => $item['barang_mentah_id'],
                    'satuan_id'           => $spkMat->satuan_id,
                    'qty_total_spk'       => $qtyTotal,
                    'qty_sudah_diambil'   => $alreadyIssued,
                    'qty_sisa_kebutuhan'  => $sisaBefore,
                    'qty_diambil_sekarang' => $qtyTaken,
                    'catatan'              => $item['catatan'] ?? null,
                ]);

                // Update total_keluar_gudang in spk_produksi_materials
                $spkMat->increment('total_keluar_gudang', $qtyTaken);

                // Deduct Inventory Stock in StokGudang
                $stokAkhir = $stokAwal - $qtyTaken;
                $barangMentah = MasterBarang::find($item['barang_mentah_id']);
                $hpp = (float)($stokGudang->harga_pokok_rata_rata ?? 0);
                if ($hpp <= 0 && (float)($stokGudang->harga_beli_terakhir ?? 0) > 0) {
                    $hpp = (float)$stokGudang->harga_beli_terakhir;
                }
                if ($hpp <= 0 && $barangMentah) {
                    $hpp = (float)($barangMentah->harga_pokok_rata_rata ?: ($barangMentah->harga_pokok ?: ($barangMentah->harga_beli ?: 0)));
                }
                if ($hpp <= 0) {
                    $validSg = StokGudang::where('barang_id', $item['barang_mentah_id'])
                        ->where(function($q) {
                            $q->where('harga_pokok_rata_rata', '>', 0)
                              ->orWhere('harga_beli_terakhir', '>', 0);
                        })->first();
                    if ($validSg) {
                        $hpp = (float)($validSg->harga_pokok_rata_rata > 0 ? $validSg->harga_pokok_rata_rata : $validSg->harga_beli_terakhir);
                    }
                }
                if ($hpp <= 0) {
                    $hpp = 1000; // default nominal jika belum ada histori harga
                }
                $nilaiKeluar = round($qtyTaken * $hpp, 4);

                $stokGudang->update([
                    'stok_aktual'      => $stokAkhir,
                    'nilai_persediaan' => max(0, (float)$stokGudang->nilai_persediaan - $nilaiKeluar),
                ]);

                // Sync HPP Master Barang
                StokGudang::syncMasterHpp($item['barang_mentah_id']);

                // Record KartuStok entry
                KartuStok::create([
                    'barang_id'       => $item['barang_mentah_id'],
                    'gudang_id'       => $request->gudang_id,
                    'tipe'            => 'Keluar',
                    'jumlah'          => $qtyTaken,
                    'stok_awal'       => $stokAwal,
                    'stok_akhir'      => $stokAkhir,
                    'harga_satuan'    => $hpp,
                    'nilai_total'     => $nilaiKeluar,
                    'referensi_jenis' => 'Pengeluaran Bahan SPK',
                    'referensi_id'    => $pengeluaran->id,
                    'referensi_nomor' => $pengeluaran->nomor_pengeluaran,
                    'keterangan'      => "Pengeluaran bahan baku untuk SPK #{$spk->nomor_spk}",
                ]);

                if ($barangMentah) {
                    $itemsJurnal[] = [
                        'barang' => $barangMentah,
                        'nilai'  => $nilaiKeluar,
                    ];
                }
            }

            // Catatan: Jurnal Akuntansi tidak dibuat per sesi pengeluaran, melainkan 1 jurnal konsolidasi saat Closing Pengeluaran Bahan SPK.

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengeluaran bahan baku {$nomorPengeluaran} berhasil dicatat & stok gudang telah terpotong!",
                'id'      => $pengeluaran->id
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error store pengeluaran bahan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mencatat pengeluaran bahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show detail of material issue transaction for modal display.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('detail-pengeluaran-bahan')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat detail pengeluaran bahan.');
        }

        $pengeluaran = SpkPengeluaranBahan::forActiveCompany()
            ->with([
                'company',
                'branch',
                'spk.barangJadi',
                'spk.satuan',
                'spk.materials.barangMentah.satuan',
                'spk.materials.satuan',
                'gudang',
                'penyerah',
                'penerima',
                'creator',
                'details.barangMentah.satuan',
                'details.satuan'
            ])
            ->findOrFail($id);

        return response()->json(['pengeluaran' => $pengeluaran]);
    }

    /**
     * Update material issue transaction with inventory & accounting rebalancing.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-pengeluaran-bahan') && !$user->can('tambah-pengeluaran-bahan')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengedit pengeluaran bahan.'], 403);
        }

        $pengeluaran = SpkPengeluaranBahan::forActiveCompany()->with(['details', 'spk'])->findOrFail($id);
        $spk = $pengeluaran->spk;
        if (!$spk) {
            return response()->json(['success' => false, 'message' => 'Data SPK terkait tidak ditemukan.'], 404);
        }

        if ($spk->is_material_closed || $spk->material_closing_status === 'Closed') {
            return response()->json([
                'success' => false,
                'message' => 'Pengeluaran bahan untuk SPK ini sudah di-Closing. Anda tidak dapat mengubah data pengeluaran!'
            ], 422);
        }

        if ($spk->material_closing_status === 'Menunggu Closing') {
            return response()->json([
                'success' => false,
                'message' => 'SPK ini sedang dalam status Menunggu Closing. Batalkan pengajuan closing terlebih dahulu jika ingin mengubah data pengeluaran bahan!'
            ], 422);
        }

        $request->validate([
            'gudang_id'            => 'required|exists:master_gudang,id',
            'tanggal_pengeluaran'  => 'required|date',
            'penyerah_karyawan_id' => 'nullable|exists:karyawans,id',
            'penerima_karyawan_id' => 'nullable|exists:karyawans,id',
            'penerima_nama'        => 'required|string|max:40',
            'catatan'              => 'nullable|string|max:40',
            'items'                => 'required|array|min:1',
            'items.*.spk_material_id'     => 'required|exists:spk_produksi_materials,id',
            'items.*.barang_mentah_id'   => 'required|exists:master_barang,id',
            'items.*.qty_diambil_sekarang'=> 'required|numeric|min:0.0001',
        ], [
            'penerima_nama.required' => 'Nama penerima / operator line wajib diisi!',
            'penerima_nama.max'      => 'Nama penerima / operator line tidak boleh lebih dari 40 karakter!',
            'items.required'         => 'Wajib memilih minimal 1 item bahan baku untuk dikeluarkan!',
        ]);

        // Security Validation: Check duplicate materials in the request
        $matIds = array_column($request->items, 'spk_material_id');
        if (count($matIds) !== count(array_unique($matIds))) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat item bahan baku yang sama dipilih lebih dari 1 kali. Silakan gabungkan kuantitasnya!'
            ], 422);
        }

        // Security check: Pastikan gudang yang dipilih sesuai dengan entitas (Cabang vs PT)
        $isBranch = !empty($pengeluaran->branch_id);
        $selectedGudang = MasterGudang::findOrFail($request->gudang_id);
        if ($isBranch) {
            if ($selectedGudang->cabang_id != $pengeluaran->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gudang yang dipilih tidak sesuai dengan Cabang pemilik dokumen!'
                ], 422);
            }
        } else {
            if (!empty($selectedGudang->cabang_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gudang yang dipilih adalah milik Cabang, bukan PT Pusat!'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // STEP 1: Revert old stock & SPK material counts
            $oldGudangId = $pengeluaran->gudang_id;
            foreach ($pengeluaran->details as $d) {
                $qtyOld = (float)$d->qty_diambil_sekarang;
                if ($d->spk_material_id) {
                    SpkProduksiMaterial::where('id', $d->spk_material_id)->decrement('total_keluar_gudang', $qtyOld);
                }

                $stokGudang = StokGudang::firstOrCreate(
                    ['barang_id' => $d->barang_mentah_id, 'gudang_id' => $oldGudangId],
                    ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
                );

                $ksOrig = KartuStok::where('referensi_jenis', 'Pengeluaran Bahan SPK')
                    ->where('referensi_id', $pengeluaran->id)
                    ->where('barang_id', $d->barang_mentah_id)
                    ->where('tipe', 'Keluar')
                    ->first();

                $stokAwal = (float)$stokGudang->stok_aktual;
                $stokAkhir = $stokAwal + $qtyOld;
                $hpp = (float)($stokGudang->harga_pokok_rata_rata ?? 0);
                $nilaiRevert = $ksOrig ? (float)$ksOrig->nilai_total : round($qtyOld * ($hpp > 0 ? $hpp : (float)($stokGudang->harga_beli_terakhir ?? 0)), 4);

                $stokGudang->update([
                    'stok_aktual'      => $stokAkhir,
                    'nilai_persediaan' => (float)$stokGudang->nilai_persediaan + $nilaiRevert,
                ]);

                StokGudang::syncMasterHpp($d->barang_mentah_id);
            }

            // Delete old KartuStok and Jurnal entries for this document
            KartuStok::where('referensi_jenis', 'Pengeluaran Bahan SPK')
                ->where('referensi_id', $pengeluaran->id)
                ->delete();

            Jurnal::where('referensi_tipe', 'Pengeluaran Bahan SPK')
                ->where('referensi_id', $pengeluaran->id)
                ->delete();

            // Delete old details
            $pengeluaran->details()->delete();

            // STEP 2: Validate new stock and apply new issuances
            $newGudangId = $request->gudang_id;
            $itemsJurnal = [];

            foreach ($request->items as $item) {
                $qtyTaken = round((float)$item['qty_diambil_sekarang'], 4);
                if ($qtyTaken <= 0) continue;

                $spkMat = SpkProduksiMaterial::where('spk_id', $spk->id)->findOrFail($item['spk_material_id']);
                $qtyTotal = (float)$spkMat->total_kebutuhan;

                $alreadyIssuedOther = (float)SpkPengeluaranBahanDetail::where('spk_material_id', $spkMat->id)
                    ->where('pengeluaran_id', '!=', $pengeluaran->id)
                    ->whereHas('pengeluaran', fn($q) => $q->whereNull('deleted_at'))
                    ->sum('qty_diambil_sekarang');

                $sisaBefore = max(0, round($qtyTotal - $alreadyIssuedOther, 4));

                // Anti-Negative Stock Validation & Pessimistic Lock (Race Condition Protection)
                $stokGudang = StokGudang::where('barang_id', $item['barang_mentah_id'])
                    ->where('gudang_id', $newGudangId)
                    ->lockForUpdate()
                    ->first();

                if (!$stokGudang) {
                    $stokGudang = StokGudang::create([
                        'barang_id'             => $item['barang_mentah_id'],
                        'gudang_id'             => $newGudangId,
                        'stok_aktual'           => 0.00,
                        'nilai_persediaan'     => 0.0000,
                        'harga_pokok_rata_rata' => 0.000000
                    ]);
                }

                $stokAwal = (float)$stokGudang->stok_aktual;
                if ($stokAwal < $qtyTaken) {
                    $bmNama = $spkMat->barangMentah ? $spkMat->barangMentah->nama_barang : 'Bahan Mentah';
                    $gudangObj = MasterGudang::find($newGudangId);
                    $gudangNama = $gudangObj ? $gudangObj->nama_gudang : 'Gudang Terpilih';
                    $satNama = $spkMat->satuan ? $spkMat->satuan->nama_satuan : 'Item';
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok tidak mencukupi untuk [{$bmNama}] di {$gudangNama}! Stok fisik tersedia: {$stokAwal} {$satNama}, sedangkan yang ingin dikeluarkan: {$qtyTaken} {$satNama}."
                    ], 422);
                }

                SpkPengeluaranBahanDetail::create([
                    'pengeluaran_id'       => $pengeluaran->id,
                    'spk_material_id'      => $spkMat->id,
                    'barang_mentah_id'    => $item['barang_mentah_id'],
                    'satuan_id'           => $spkMat->satuan_id,
                    'qty_total_spk'       => $qtyTotal,
                    'qty_sudah_diambil'   => $alreadyIssuedOther,
                    'qty_sisa_kebutuhan'  => $sisaBefore,
                    'qty_diambil_sekarang' => $qtyTaken,
                    'catatan'              => $item['catatan'] ?? null,
                ]);

                // Increment total_keluar_gudang
                $spkMat->increment('total_keluar_gudang', $qtyTaken);

                // Deduct stock in StokGudang
                $stokAkhir = $stokAwal - $qtyTaken;
                $barangMentah = MasterBarang::find($item['barang_mentah_id']);
                $hpp = (float)($stokGudang->harga_pokok_rata_rata ?? 0);
                if ($hpp <= 0 && (float)($stokGudang->harga_beli_terakhir ?? 0) > 0) {
                    $hpp = (float)$stokGudang->harga_beli_terakhir;
                }
                if ($hpp <= 0 && $barangMentah) {
                    $hpp = (float)($barangMentah->harga_pokok_rata_rata ?: ($barangMentah->harga_pokok ?: ($barangMentah->harga_beli ?: 0)));
                }
                if ($hpp <= 0) {
                    $validSg = StokGudang::where('barang_id', $item['barang_mentah_id'])
                        ->where(function($q) {
                            $q->where('harga_pokok_rata_rata', '>', 0)
                              ->orWhere('harga_beli_terakhir', '>', 0);
                        })->first();
                    if ($validSg) {
                        $hpp = (float)($validSg->harga_pokok_rata_rata > 0 ? $validSg->harga_pokok_rata_rata : $validSg->harga_beli_terakhir);
                    }
                }
                if ($hpp <= 0) {
                    $hpp = 1000; // default nominal jika belum ada histori harga
                }
                $nilaiKeluar = round($qtyTaken * $hpp, 4);

                $stokGudang->update([
                    'stok_aktual'      => $stokAkhir,
                    'nilai_persediaan' => max(0, (float)$stokGudang->nilai_persediaan - $nilaiKeluar),
                ]);

                StokGudang::syncMasterHpp($item['barang_mentah_id']);

                // Record new KartuStok entry
                KartuStok::create([
                    'barang_id'       => $item['barang_mentah_id'],
                    'gudang_id'       => $newGudangId,
                    'tipe'            => 'Keluar',
                    'jumlah'          => $qtyTaken,
                    'stok_awal'       => $stokAwal,
                    'stok_akhir'      => $stokAkhir,
                    'harga_satuan'    => $hpp,
                    'nilai_total'     => $nilaiKeluar,
                    'referensi_jenis' => 'Pengeluaran Bahan SPK',
                    'referensi_id'    => $pengeluaran->id,
                    'referensi_nomor' => $pengeluaran->nomor_pengeluaran,
                    'keterangan'      => "Revisi pengeluaran bahan untuk SPK #{$pengeluaran->spk->nomor_spk}",
                ]);

                if ($barangMentah) {
                    $itemsJurnal[] = [
                        'barang' => $barangMentah,
                        'nilai'  => $nilaiKeluar,
                    ];
                }
            }

            // STEP 3: Update Header
            $pengeluaran->update([
                'gudang_id'            => $newGudangId,
                'tanggal_pengeluaran'  => $request->tanggal_pengeluaran,
                'penyerah_karyawan_id' => $request->penyerah_karyawan_id ?: null,
                'penerima_karyawan_id' => $request->penerima_karyawan_id ?: null,
                'penerima_nama'        => $request->penerima_nama ? trim($request->penerima_nama) : null,
                'catatan'              => $request->catatan,
                'updated_by'           => $user->id,
            ]);

            // Catatan: Jurnal Akuntansi diterbitkan saat Closing Pengeluaran Bahan SPK.

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sesi pengeluaran {$pengeluaran->nomor_pengeluaran} berhasil diperbarui & mutasi stok gudang disesuaikan!",
                'id'      => $pengeluaran->id
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error update pengeluaran bahan: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal memperbarui pengeluaran bahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete & revert material issue transaction.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('hapus-pengeluaran-bahan')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk membatalkan pengeluaran bahan.'], 403);
        }

        $pengeluaran = SpkPengeluaranBahan::forActiveCompany()->with(['details', 'spk'])->findOrFail($id);

        if ($pengeluaran->spk && ($pengeluaran->spk->is_material_closed || $pengeluaran->spk->material_closing_status === 'Closed')) {
            return response()->json([
                'success' => false,
                'message' => 'Pengeluaran bahan untuk SPK ini sudah di-Closing. Anda tidak dapat membatalkan / menghapus sesi pengeluaran ini!'
            ], 422);
        }

        if ($pengeluaran->spk && $pengeluaran->spk->material_closing_status === 'Menunggu Closing') {
            return response()->json([
                'success' => false,
                'message' => 'SPK ini sedang dalam status Menunggu Closing. Batalkan pengajuan closing terlebih dahulu jika ingin membatalkan / menghapus sesi pengeluaran ini!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($pengeluaran->details as $d) {
                $qtyRevert = (float)$d->qty_diambil_sekarang;

                // Decrement total_keluar_gudang in spk_produksi_materials
                if ($d->spk_material_id) {
                    SpkProduksiMaterial::where('id', $d->spk_material_id)->decrement('total_keluar_gudang', $qtyRevert);
                }

                // Add back stock to StokGudang
                $stokGudang = StokGudang::firstOrCreate(
                    ['barang_id' => $d->barang_mentah_id, 'gudang_id' => $pengeluaran->gudang_id],
                    ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
                );

                $ksOrig = KartuStok::where('referensi_jenis', 'Pengeluaran Bahan SPK')
                    ->where('referensi_id', $pengeluaran->id)
                    ->where('barang_id', $d->barang_mentah_id)
                    ->where('tipe', 'Keluar')
                    ->first();

                $stokAwal = (float)$stokGudang->stok_aktual;
                $stokAkhir = $stokAwal + $qtyRevert;
                $hpp = (float)($stokGudang->harga_pokok_rata_rata ?? 0);
                $nilaiRevert = $ksOrig ? (float)$ksOrig->nilai_total : round($qtyRevert * ($hpp > 0 ? $hpp : (float)($stokGudang->harga_beli_terakhir ?? 0)), 4);

                $stokGudang->update([
                    'stok_aktual'      => $stokAkhir,
                    'nilai_persediaan' => (float)$stokGudang->nilai_persediaan + $nilaiRevert,
                ]);

                StokGudang::syncMasterHpp($d->barang_mentah_id);

                // Record KartuStok Reversal entry
                KartuStok::create([
                    'barang_id'       => $d->barang_mentah_id,
                    'gudang_id'       => $pengeluaran->gudang_id,
                    'tipe'            => 'Masuk',
                    'jumlah'          => $qtyRevert,
                    'stok_awal'       => $stokAwal,
                    'stok_akhir'      => $stokAkhir,
                    'harga_satuan'    => $hpp,
                    'nilai_total'     => $nilaiRevert,
                    'referensi_jenis' => 'Pembatalan Pengeluaran Bahan SPK',
                    'referensi_id'    => $pengeluaran->id,
                    'referensi_nomor' => $pengeluaran->nomor_pengeluaran,
                    'keterangan'      => "Pembatalan pengeluaran bahan SPK #{$pengeluaran->nomor_pengeluaran}",
                ]);
            }

            // Delete associated Jurnal and JurnalDetails
            Jurnal::where('referensi_tipe', 'Pengeluaran Bahan SPK')
                ->where('referensi_id', $pengeluaran->id)
                ->delete();

            $pengeluaran->update([
                'status'     => 'Batal',
                'deleted_by' => $user->id,
            ]);
            $pengeluaran->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengeluaran bahan {$pengeluaran->nomor_pengeluaran} berhasil dibatalkan & stok gudang dikembalikan!"
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error delete pengeluaran bahan: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membatalkan pengeluaran bahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get preview data for Closing Pengeluaran Bahan SPK.
     */
    public function getClosingPreview($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('closing-pengeluaran-bahan') && !$user->can('ajukan-closing-pengeluaran-bahan') && !$user->can('lihat-pengeluaran-bahan')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat pratinjau closing pengeluaran bahan.'
            ], 403);
        }

        $spk = SpkProduksi::forActiveCompany()
            ->with([
                'barangJadi.satuan',
                'satuan',
                'branch',
                'company',
                'materialClosedBy',
                'materialClosingRequestedBy',
                'materialClosingRejectedBy',
                'materials.barangMentah.satuan',
                'materials.satuan',
                'pengeluaranBahans.details.barangMentah.satuan',
                'pengeluaranBahans.gudang'
            ])
            ->findOrFail($spkId);

        $pengeluaranList = $spk->pengeluaranBahans;
        if ($pengeluaranList->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada sesi penyerahan bahan baku untuk SPK ini. Tidak dapat memproses closing.'
            ], 422);
        }

        $pengeluaranIds = $pengeluaranList->pluck('id');
        $kartuStoks = KartuStok::where('referensi_jenis', 'Pengeluaran Bahan SPK')
            ->whereIn('referensi_id', $pengeluaranIds)
            ->where('tipe', 'Keluar')
            ->get();

        $materials = $spk->materials;
        $totalMaterials = $materials->count();
        $completedCount = 0;
        $incompleteList = [];
        $totalPercentSum = 0;
        $grandTargetQty = 0;

        $itemSummary = [];

        // 1. Initialise all required materials from SPK
        foreach ($materials as $m) {
            $bm = $m->barangMentah;
            $bId = $m->barang_mentah_id;
            $kebutuhan = (float) $m->total_kebutuhan;
            $keluar = (float) $m->total_keluar_gudang;
            $satName = $m->satuan ? $m->satuan->nama_satuan : ($bm && $bm->satuan ? $bm->satuan->nama_satuan : 'Pcs');

            $grandTargetQty += $kebutuhan;
            $pct = $kebutuhan > 0 ? min(100, round(($keluar / $kebutuhan) * 100, 1)) : 100;
            $totalPercentSum += $pct;

            if ($keluar >= $kebutuhan) {
                $completedCount++;
            } else {
                $incompleteList[] = [
                    'barang_id'   => $bId,
                    'kode_barang' => $bm ? $bm->kode_barang : '-',
                    'nama_barang' => $bm ? $bm->nama_barang : '-',
                    'satuan'      => $satName,
                    'kebutuhan'   => $kebutuhan,
                    'keluar'      => $keluar,
                    'sisa'        => max(0, round($kebutuhan - $keluar, 4)),
                    'percent'     => $pct,
                ];
            }

            $itemSummary[$bId] = [
                'barang_id'        => $bId,
                'kode_barang'      => $bm ? $bm->kode_barang : '-',
                'nama_barang'      => $bm ? $bm->nama_barang : '-',
                'satuan'           => $satName,
                'target_kebutuhan' => $kebutuhan,
                'total_qty'        => 0,
                'total_nilai'      => 0,
                'barang'           => $bm,
            ];
        }

        // 2. Accumulate actual issued quantities from recorded sessions
        foreach ($pengeluaranList as $p) {
            foreach ($p->details as $d) {
                $bId = $d->barang_mentah_id;
                if (!isset($itemSummary[$bId])) {
                    $itemSummary[$bId] = [
                        'barang_id'        => $bId,
                        'kode_barang'      => $d->barangMentah?->kode_barang ?? '-',
                        'nama_barang'      => $d->barangMentah?->nama_barang ?? '-',
                        'satuan'           => $d->satuan?->nama_satuan ?? ($d->barangMentah?->satuan?->nama_satuan ?? 'Pcs'),
                        'target_kebutuhan' => (float)$d->qty_total_spk,
                        'total_qty'        => 0,
                        'total_nilai'      => 0,
                        'barang'           => $d->barangMentah,
                    ];
                }
                $itemSummary[$bId]['total_qty'] += (float)$d->qty_diambil_sekarang;
            }
        }

        // 3. Accumulate total values from KartuStok
        foreach ($kartuStoks as $ks) {
            $bId = $ks->barang_id;
            if (isset($itemSummary[$bId])) {
                $itemSummary[$bId]['total_nilai'] += (float)$ks->nilai_total;
            }
        }

        $grandTotalNilai = 0;
        $grandTotalQty   = 0;

        foreach ($itemSummary as $bId => &$info) {
            $sisaTarget = max(0, round($info['target_kebutuhan'] - $info['total_qty'], 4));
            $info['sisa_target'] = $sisaTarget;
            $info['is_lengkap']  = ($sisaTarget <= 0);

            if ($info['total_nilai'] <= 0 && $info['total_qty'] > 0) {
                $hpp = (float)($info['barang']?->harga_pokok_rata_rata ?: ($info['barang']?->harga_pokok ?: ($info['barang']?->harga_beli ?: 0)));
                if ($hpp <= 0) {
                    $validSg = StokGudang::where('barang_id', $bId)
                        ->where(function($q) {
                            $q->where('harga_pokok_rata_rata', '>', 0)
                              ->orWhere('harga_beli_terakhir', '>', 0);
                        })->first();
                    if ($validSg) {
                        $hpp = (float)($validSg->harga_pokok_rata_rata > 0 ? $validSg->harga_pokok_rata_rata : $validSg->harga_beli_terakhir);
                    }
                }
                if ($hpp <= 0) {
                    $hpp = 1000;
                }
                $info['total_nilai'] = round($info['total_qty'] * $hpp, 4);
            }
            $grandTotalNilai += $info['total_nilai'];
            $grandTotalQty   += $info['total_qty'];
        }
        unset($info);

        $avgProgress = $totalMaterials > 0 ? round($totalPercentSum / $totalMaterials, 1) : 100;
        $isComplete  = ($totalMaterials > 0 && $completedCount === $totalMaterials);

        $statusClosing = $spk->material_closing_status ?: ($spk->is_material_closed ? 'Closed' : 'Open');
        $canApprove    = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('closing-pengeluaran-bahan');
        $canReject     = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('tolak-closing-pengeluaran-bahan') || $user->can('closing-pengeluaran-bahan');
        $canAjukan     = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('ajukan-closing-pengeluaran-bahan') || $user->can('tambah-pengeluaran-bahan') || $user->can('closing-pengeluaran-bahan');
        $canLihatHpp   = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-pengeluaran-bahan') || $user->can('closing-pengeluaran-bahan');

        $itemsResponse = array_values($itemSummary);
        if (!$canLihatHpp) {
            foreach ($itemsResponse as &$it) {
                $it['total_nilai'] = null;
            }
            unset($it);
            $grandTotalNilai = null;
        }

        return response()->json([
            'success' => true,
            'spk'     => [
                'id'                      => $spk->id,
                'nomor_spk'               => $spk->nomor_spk,
                'barang_jadi'             => $spk->barangJadi?->nama_barang ?? '-',
                'qty_spk'                 => (float)$spk->jumlah_rencana,
                'satuan'                  => $spk->satuan?->nama_satuan ?? ($spk->barangJadi?->satuan?->nama_satuan ?? 'Pcs'),
                'total_sesi'              => $pengeluaranList->count(),
                'material_closing_status' => $statusClosing,
                'is_material_closed'      => (bool)$spk->is_material_closed,
                'requested_by_name'       => $spk->materialClosingRequestedBy?->nama_lengkap ?: ($spk->materialClosingRequestedBy?->name ?? '-'),
                'requested_at'            => $spk->material_closing_requested_at ? $spk->material_closing_requested_at->format('d/m/Y H:i') : null,
                'requested_note'          => $spk->material_closing_note,
                'rejected_by_name'        => $spk->materialClosingRejectedBy?->nama_lengkap ?: ($spk->materialClosingRejectedBy?->name ?? '-'),
                'rejected_at'             => $spk->material_closing_rejected_at ? $spk->material_closing_rejected_at->format('d/m/Y H:i') : null,
                'reject_reason'           => $spk->material_closing_reject_reason,
                'closed_by_name'          => $spk->materialClosedBy?->nama_lengkap ?: ($spk->materialClosedBy?->name ?? '-'),
                'closed_at'               => $spk->material_closed_at ? $spk->material_closed_at->format('d/m/Y H:i') : null,
            ],
            'user_permissions' => [
                'can_approve'   => $canApprove,
                'can_reject'    => $canReject,
                'can_ajukan'    => $canAjukan,
                'can_lihat_hpp' => $canLihatHpp,
            ],
            'completion_status' => [
                'is_complete'      => $isComplete,
                'total_materials'  => $totalMaterials,
                'completed_count'  => $completedCount,
                'incomplete_count' => count($incompleteList),
                'avg_progress'     => $avgProgress,
                'incomplete_list'  => $incompleteList,
            ],
            'items'             => $itemsResponse,
            'grand_target_qty'  => $grandTargetQty,
            'grand_total_qty'   => $grandTotalQty,
            'grand_total_nilai' => $grandTotalNilai,
            'sesi_list'         => $pengeluaranList->map(function ($p) {
                return [
                    'id'                => $p->id,
                    'nomor_pengeluaran' => $p->nomor_pengeluaran,
                    'tanggal'           => $p->tanggal_pengeluaran ? date('d/m/Y', strtotime($p->tanggal_pengeluaran)) : '-',
                    'gudang'            => $p->gudang?->nama_gudang ?? '-',
                    'penerima'          => $p->penerima_nama ?: ($p->penerima?->nama_lengkap ?? '-'),
                ];
            }),
        ]);
    }

    /**
     * Submit Closing Request (Ajukan Closing) by Production/Warehouse staff.
     */
    public function requestClosingMaterial(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('ajukan-closing-pengeluaran-bahan') && !$user->can('tambah-pengeluaran-bahan') && !$user->can('closing-pengeluaran-bahan')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengajukan closing pengeluaran bahan.'
            ], 403);
        }

        $request->validate([
            'catatan' => 'nullable|string|max:255',
        ]);

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        if ($spk->is_material_closed || $spk->material_closing_status === 'Closed') {
            return response()->json([
                'success' => false,
                'message' => 'Pengeluaran bahan untuk SPK ini sudah ditutup (Closed)!'
            ], 422);
        }

        if ($spk->material_closing_status === 'Menunggu Closing') {
            return response()->json([
                'success' => false,
                'message' => 'Closing pengeluaran bahan untuk SPK ini sudah dalam antrean pengajuan (Menunggu Closing).'
            ], 422);
        }

        if ($spk->pengeluaranBahans()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada sesi penyerahan bahan baku untuk SPK ini. Pengajuan closing tidak dapat dilakukan.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $spk->update([
                'material_closing_status'       => 'Menunggu Closing',
                'material_closing_requested_at' => now(),
                'material_closing_requested_by' => $user->id,
                'material_closing_note'          => $request->catatan,
                'material_closing_reject_reason' => null,
            ]);

            DB::commit();

            // Trigger WhatsApp notification to Accounting / Approvers
            try {
                WhatsAppGatewayController::sendPengeluaranBahanClosingSubmissionNotification($spk, $user);
            } catch (\Throwable $we) {
                Log::warning('WhatsApp notification on material closing submission error: ' . $we->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Pengajuan closing pengeluaran bahan untuk SPK {$spk->nomor_spk} berhasil diajukan! Notifikasi telah dikirimkan ke bagian Accounting.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('requestClosingMaterial error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan closing pengeluaran bahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel Closing Request by Requester or Admin.
     */
    public function cancelClosingRequest(Request $request, $spkId)
    {
        $user = auth()->user();
        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        $canCancel = $user->hasAnyRole(['Super Admin', 'Owner']) 
            || $user->can('batal-ajukan-closing-pengeluaran-bahan');

        if (!$canCancel) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk membatalkan pengajuan closing ini.'
            ], 403);
        }

        if ($spk->material_closing_status !== 'Menunggu Closing') {
            return response()->json([
                'success' => false,
                'message' => 'Status pengajuan bukan Menunggu Closing.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $spk->update([
                'material_closing_status'       => 'Open',
                'material_closing_requested_at' => null,
                'material_closing_requested_by' => null,
                'material_closing_note'          => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengajuan closing untuk SPK {$spk->nomor_spk} berhasil dibatalkan. Operasional pengeluaran bahan kembali terbuka.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('cancelClosingRequest error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pengajuan closing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Closing Approval of raw material issuances for SPK and issue 1 consolidated accounting journal.
     */
    public function closeMaterial(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('closing-pengeluaran-bahan')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melakukan otorisasi closing pengeluaran bahan.'
            ], 403);
        }

        $request->validate([
            'tanggal_closing' => 'required|date',
            'catatan'         => 'nullable|string|max:255',
        ]);

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        if ($spk->is_material_closed || $spk->material_closing_status === 'Closed') {
            return response()->json([
                'success' => false,
                'message' => 'Pengeluaran bahan untuk SPK ini sudah di-Closing!'
            ], 422);
        }

        if ($spk->pengeluaranBahans()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada sesi penyerahan bahan baku untuk SPK ini. Closing tidak dapat diproses.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $spk->update([
                'is_material_closed'      => true,
                'material_closing_status' => 'Closed',
                'material_closed_at'      => now(),
                'material_closed_by'      => $user->id,
            ]);

            $this->createConsolidatedJurnalClosing($spk, $request->tanggal_closing, $request->catatan);

            DB::commit();

            // Trigger WhatsApp notification for requester
            try {
                WhatsAppGatewayController::sendPengeluaranBahanClosingApprovedNotification($spk, $user);
            } catch (\Throwable $we) {
                Log::warning('WhatsApp notification on material closing approval error: ' . $we->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Closing pengeluaran bahan untuk SPK {$spk->nomor_spk} berhasil disetujui & 1 jurnal akuntansi konsolidasi telah diterbitkan!",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('closeMaterial error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses closing pengeluaran bahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject Closing Request by Accounting / Approver.
     */
    public function rejectClosingMaterial(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tolak-closing-pengeluaran-bahan') && !$user->can('closing-pengeluaran-bahan')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menolak pengajuan closing pengeluaran bahan.'
            ], 403);
        }

        $request->validate([
            'alasan_reject' => 'required|string|max:500',
        ], [
            'alasan_reject.required' => 'Alasan penolakan closing wajib diisi!'
        ]);

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        if ($spk->material_closing_status !== 'Menunggu Closing') {
            return response()->json([
                'success' => false,
                'message' => 'Status SPK bukan Menunggu Closing!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $spk->update([
                'material_closing_status'        => 'Ditolak',
                'material_closing_reject_reason' => trim($request->alasan_reject),
                'material_closing_rejected_at'   => now(),
                'material_closing_rejected_by'   => $user->id,
            ]);

            DB::commit();

            // Trigger WhatsApp notification for requester
            try {
                WhatsAppGatewayController::sendPengeluaranBahanClosingRejectedNotification($spk, $user, $request->alasan_reject);
            } catch (\Throwable $we) {
                Log::warning('WhatsApp notification on material closing rejection error: ' . $we->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Pengajuan closing untuk SPK {$spk->nomor_spk} ditolak. Notifikasi telah dikirimkan ke pihak pengaju.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('rejectClosingMaterial error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak pengajuan closing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reopen material closing for adjustments.
     */
    public function reopenMaterial(Request $request, $spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('buka-closing-pengeluaran-bahan')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk membuka kembali closing pengeluaran bahan.'
            ], 403);
        }

        $spk = SpkProduksi::forActiveCompany()->findOrFail($spkId);

        if (!$spk->is_material_closed && $spk->material_closing_status !== 'Closed') {
            return response()->json([
                'success' => false,
                'message' => 'SPK ini belum di-closing atau status pengeluaran bahan masih terbuka.'
            ], 422);
        }

        if ($spk->is_hpp_closed) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membuka closing bahan karena perhitungan HPP SPK sudah di-closing!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $spk->update([
                'is_material_closed'             => false,
                'material_closing_status'        => 'Open',
                'material_closed_at'             => null,
                'material_closed_by'             => null,
                'material_closing_requested_at'  => null,
                'material_closing_requested_by'  => null,
                'material_closing_note'           => null,
                'material_closing_reject_reason'  => null,
                'material_closing_rejected_at'    => null,
                'material_closing_rejected_by'    => null,
            ]);

            // Soft-delete or delete consolidated journal
            $jurnal = Jurnal::where('referensi_tipe', 'Pengeluaran Bahan SPK')
                ->where('referensi_id', $spk->id)
                ->first();

            if ($jurnal) {
                $jurnal->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Closing pengeluaran bahan untuk SPK {$spk->nomor_spk} berhasil dibuka kembali. Jurnal akuntansi ditarik dari Jurnal Umum & operasional pengeluaran bahan dapat dilanjutkan."
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('reopenMaterial error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuka closing pengeluaran bahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display consolidated closing journal details for SPK.
     */
    public function showSpkJurnalClosing($spkId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-jurnal-pengeluaran-bahan') && !$user->can('lihat-jurnal-full-pengeluaran-bahan') && !$user->can('lihat-jurnal')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat jurnal pengeluaran bahan.'
            ], 403);
        }

        $spk = SpkProduksi::forActiveCompany()
            ->with(['barangJadi', 'materialClosedBy', 'materialClosingJurnal.details.account'])
            ->findOrFail($spkId);

        $jurnal = $spk->materialClosingJurnal;
        if (!$jurnal) {
            return response()->json([
                'success' => false,
                'message' => 'Jurnal konsolidasi closing belum ditemukan untuk SPK ini.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'spk' => [
                'id'          => $spk->id,
                'nomor_spk'   => $spk->nomor_spk,
                'barang_jadi' => $spk->barangJadi?->nama_barang ?? '-',
                'closed_by'   => $spk->materialClosedBy?->name ?? 'User',
                'closed_at'   => $spk->material_closed_at ? $spk->material_closed_at->format('d/m/Y H:i') : '-',
            ],
            'jurnal' => [
                'id'             => $jurnal->id,
                'nomor_jurnal'   => $jurnal->nomor_jurnal,
                'tanggal_jurnal' => $jurnal->tanggal_jurnal ? date('d/m/Y', strtotime($jurnal->tanggal_jurnal)) : '-',
                'keterangan'     => $jurnal->keterangan,
                'total_debit'    => (float)$jurnal->total_debit,
                'total_kredit'   => (float)$jurnal->total_kredit,
                'details'        => $jurnal->details->map(function ($d) {
                    return [
                        'kode_akun'  => $d->account?->code ?? '-',
                        'nama_akun'  => $d->account?->name ?? '-',
                        'debit'      => (float)$d->debit,
                        'kredit'     => (float)$d->kredit,
                        'keterangan' => $d->keterangan,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Create 1 consolidated accounting journal for SPK material closing.
     */
    private function createConsolidatedJurnalClosing(SpkProduksi $spk, $tanggalClosing = null, $catatan = null): void
    {
        $pengeluaranList = $spk->pengeluaranBahans()->with(['details.barangMentah.satuan'])->get();
        if ($pengeluaranList->isEmpty()) {
            return;
        }

        $pengeluaranIds = $pengeluaranList->pluck('id');
        $kartuStoks = KartuStok::where('referensi_jenis', 'Pengeluaran Bahan SPK')
            ->whereIn('referensi_id', $pengeluaranIds)
            ->where('tipe', 'Keluar')
            ->get();

        $itemSummary = [];
        $totalNilai = 0;
        $debitGroup = [];
        $kreditGroup = [];

        // Aggregate materials
        foreach ($pengeluaranList as $p) {
            foreach ($p->details as $d) {
                $bId = $d->barang_mentah_id;
                if (!isset($itemSummary[$bId])) {
                    $itemSummary[$bId] = [
                        'barang'      => $d->barangMentah,
                        'total_qty'   => 0,
                        'total_nilai' => 0,
                    ];
                }
                $itemSummary[$bId]['total_qty'] += (float)$d->qty_diambil_sekarang;
            }
        }

        foreach ($kartuStoks as $ks) {
            $bId = $ks->barang_id;
            if (isset($itemSummary[$bId])) {
                $itemSummary[$bId]['total_nilai'] += (float)$ks->nilai_total;
            }
        }

        foreach ($itemSummary as $bId => &$info) {
            if ($info['total_nilai'] <= 0 && $info['total_qty'] > 0) {
                $hpp = (float)($info['barang']?->harga_pokok_rata_rata ?: ($info['barang']?->harga_pokok ?: ($info['barang']?->harga_beli ?: 0)));
                if ($hpp <= 0) {
                    $validSg = StokGudang::where('barang_id', $bId)
                        ->where(function($q) {
                            $q->where('harga_pokok_rata_rata', '>', 0)
                              ->orWhere('harga_beli_terakhir', '>', 0);
                        })->first();
                    if ($validSg) {
                        $hpp = (float)($validSg->harga_pokok_rata_rata > 0 ? $validSg->harga_pokok_rata_rata : $validSg->harga_beli_terakhir);
                    }
                }
                if ($hpp <= 0) {
                    $hpp = 1000;
                }
                $info['total_nilai'] = round($info['total_qty'] * $hpp, 4);
            }
        }
        unset($info);

        // Debit Account: WIP (Persediaan Barang Dalam Proses)
        $bjCatId = $spk->barangJadi ? ($spk->barangJadi->kategori_barang_id ?? $spk->barangJadi->kategori_id) : null;
        $wipCoaId = ($bjCatId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$bjCatId}_wip") : null)
                 ?? AccountMappingHelper::getCoaId('production', 'wip_inventory')
                 ?? AccountMappingHelper::getCoaId('default', 'default_wip');

        if (!$wipCoaId) {
            $wipCoa = ChartOfAccount::forActiveCompany()
                ->where(function ($q) {
                    $q->where('code', 'like', '1108%')
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

        foreach ($itemSummary as $bId => $item) {
            $barang = $item['barang'];
            $nilai  = (float)$item['total_nilai'];
            if ($nilai <= 0) continue;

            $totalNilai += $nilai;

            $catId = $barang ? ($barang->kategori_barang_id ?? $barang->kategori_id ?? null) : null;
            $creditCoaId = ($bId ? AccountMappingHelper::getCoaId('inventory_item', "item_{$bId}_inventory") : null)
                        ?? ($catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory") : null)
                        ?? AccountMappingHelper::getCoaId('default', 'default_inventory');

            if (!$creditCoaId) {
                $creditCoa = ChartOfAccount::forActiveCompany()
                    ->where(function ($q) {
                        $q->where('code', 'like', '1106%')
                          ->orWhere('name', 'like', '%Bahan Baku%')
                          ->orWhere('name', 'like', '%Bahan Mentah%')
                          ->orWhere('name', 'like', '%Persediaan%');
                    })
                    ->where('is_header', false)
                    ->first();
                $creditCoaId = $creditCoa ? $creditCoa->id : null;
            }

            if ($wipCoaId && $creditCoaId) {
                $debitGroup[$wipCoaId] = ($debitGroup[$wipCoaId] ?? 0) + $nilai;
                $kreditGroup[$creditCoaId] = ($kreditGroup[$creditCoaId] ?? 0) + $nilai;
            }
        }

        if ($totalNilai <= 0 || empty($debitGroup) || empty($kreditGroup)) {
            return;
        }

        $dateToUse = $tanggalClosing ?: now()->toDateString();
        $yearMonth = date('Y/m', strtotime($dateToUse));
        $isBranch  = !empty($spk->branch_id);
        $prefix    = $isBranch ? "JRN/MAT-CBG/{$yearMonth}/" : "JRN/MAT-PT/{$yearMonth}/";

        $lastNo = Jurnal::withTrashed()
            ->where('nomor_jurnal', 'like', "{$prefix}%")
            ->orderBy('nomor_jurnal', 'desc')
            ->orderBy('id', 'desc')
            ->value('nomor_jurnal');

        $nextSeq = 1;
        if ($lastNo && preg_match('/\/(\d+)$/', $lastNo, $m)) {
            $nextSeq = (int)$m[1] + 1;
        }

        do {
            $nomorJurnal = $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
            $exists = Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists();
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        $jurnalBranchId  = $isBranch ? $spk->branch_id : null;
        $jurnalCompanyId = $isBranch ? null : ($spk->company_id ?: (session('active_company_id') ?: 1));

        $spk->loadMissing('tender');
        $nomorTender = $spk->tender ? ($spk->tender->nomor_kontrak ?: $spk->tender->nama_tender) : null;

        $jurnal = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Pengeluaran Bahan SPK')
            ->where('referensi_id', $spk->id)
            ->first();

        $ket = "Konsolidasi Pengeluaran Bahan SPK #{$spk->nomor_spk}" . ($catatan ? " - {$catatan}" : "");

        if ($jurnal) {
            $jurnal->restore();
            $jurnal->update([
                'nomor_jurnal'    => $jurnal->nomor_jurnal ?: $nomorJurnal,
                'tanggal_jurnal'  => $dateToUse,
                'branch_id'       => $jurnalBranchId,
                'company_id'      => $jurnalCompanyId,
                'total_debit'     => $totalNilai,
                'total_kredit'    => $totalNilai,
                'keterangan'      => $ket,
                'no_tender'       => $nomorTender,
                'updated_by'      => auth()->id(),
            ]);
            $jurnal->details()->delete();
        } else {
            $jurnal = Jurnal::create([
                'nomor_jurnal'    => $nomorJurnal,
                'tanggal_jurnal'  => $dateToUse,
                'referensi_tipe'  => 'Pengeluaran Bahan SPK',
                'referensi_id'    => $spk->id,
                'branch_id'       => $jurnalBranchId,
                'company_id'      => $jurnalCompanyId,
                'total_debit'     => $totalNilai,
                'total_kredit'    => $totalNilai,
                'keterangan'      => $ket,
                'no_tender'       => $nomorTender,
                'created_by'      => auth()->id(),
            ]);
        }

        foreach ($debitGroup as $coaId => $amount) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $coaId,
                'debit'      => $amount,
                'kredit'     => 0,
                'keterangan' => "Barang Dalam Proses (WIP) SPK #{$spk->nomor_spk}",
            ]);
        }

        foreach ($kreditGroup as $coaId => $amount) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $coaId,
                'debit'      => 0,
                'kredit'     => $amount,
                'keterangan' => "Pengurangan Persediaan Bahan Baku SPK #{$spk->nomor_spk}",
            ]);
        }
    }

    /**
     * Print PDF Bon / Picking Slip Pengeluaran Bahan.
     */
    public function printPdf($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('cetak-pengeluaran-bahan')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak bon pengeluaran bahan.');
        }

        $pengeluaran = SpkPengeluaranBahan::forActiveCompany()
            ->with([
                'company',
                'branch.company',
                'spk.barangJadi.satuan',
                'spk.satuan',
                'spk.branch.company',
                'spk.company',
                'gudang',
                'penyerah',
                'penerima',
                'creator',
                'details.barangMentah.satuan',
                'details.satuan'
            ])
            ->findOrFail($id);

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        // Deteksi entitas: Jika sesi aktif Cabang atau dokumen SPK / Pengeluaran diterbitkan oleh Cabang
        $isBranch = !empty($activeBranchId) || !empty($pengeluaran->branch_id) || !empty($pengeluaran->spk?->branch_id);

        $branch = ($activeBranchId ? \App\Models\Branch::find($activeBranchId) : null)
               ?? $pengeluaran->branch
               ?? $pengeluaran->spk?->branch;

        $company = ($activeCompanyId ? Company::find($activeCompanyId) : null)
                ?? $pengeluaran->company
                ?? $pengeluaran->spk?->company
                ?? ($branch ? $branch->company : null)
                ?? Company::first();

        if ($isBranch && $branch) {
            $entityName = $branch->nama_cabang;
            $entityType = 'Cabang';
        } else {
            $entityName = $company->nama_perusahaan ?? 'PT PERSADA AMAN SENTOSA';
            $entityType = 'PT';
        }

        return view('modul_produksi.pengeluaran_bahan.print_pdf', compact('pengeluaran', 'company', 'branch', 'entityName', 'entityType'));
    }
}
