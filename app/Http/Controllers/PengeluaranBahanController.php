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
                    'barangJadi.satuan',
                    'satuan',
                    'materials.barangMentah.satuan',
                    'materials.satuan',
                    'pengeluaranBahans' => function ($q) {
                        $q->latest('id')->with(['gudang', 'penyerah', 'penerima', 'details']);
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
                    $qty = number_format($spk->jumlah_rencana, 0, ',', '.');
                    return '<div>
                        <div class="fw-bold text-primary font-monospace fs-6 mb-1 d-flex align-items-center gap-1.5">
                            <i class="bi bi-file-earmark-ruled"></i>
                            <span>' . e($spk->nomor_spk) . '</span>
                        </div>
                        <div class="fw-bold spk-text-main mb-1" style="font-size: 0.92rem;">
                            <span class="badge spk-badge-cyan rounded-2 px-2 py-0.5 font-monospace me-1" style="font-size: 11px;">' . $kode . '</span>
                            <span class="spk-text-main">' . $nama . '</span>
                        </div>
                        <div>
                            <span class="badge spk-badge-muted rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 11px;">
                                Target: <b>' . $qty . '</b> ' . $satuan . '
                            </span>
                        </div>
                    </div>';
                })
                ->addColumn('progress_pengeluaran', function ($spk) {
                    $materials = $spk->materials;
                    $totalBahan = $materials->count();
                    if ($totalBahan === 0) {
                        return '<span class="badge spk-badge-muted rounded-pill px-2.5 py-1">Tidak ada bahan</span>';
                    }

                    $totalKebutuhan = (float) $materials->sum('total_kebutuhan');
                    
                    // Sum already taken from spk_produksi_materials AND flatMap details
                    $totalDiambilFromMaterials = (float) $materials->sum('total_keluar_gudang');
                    $totalDiambilFromDetails   = (float) $spk->pengeluaranBahans->flatMap(function ($p) {
                        return $p->details;
                    })->sum('qty_diambil_sekarang');

                    $totalDiambil = max($totalDiambilFromMaterials, $totalDiambilFromDetails);

                    $rawPercent = $totalKebutuhan > 0 ? ($totalDiambil / $totalKebutuhan) * 100 : 0;

                    if ($totalDiambil >= $totalKebutuhan && $totalKebutuhan > 0) {
                        $badgeClass  = 'spk-badge-green';
                        $statusText  = 'Lengkap (100%)';
                        $barWidth    = 100;
                        $barGradient = 'background: linear-gradient(90deg, #10b981, #059669);';
                    } elseif ($totalDiambil > 0) {
                        $badgeClass  = 'spk-badge-cyan';
                        $percentDisplay = $rawPercent < 1 ? number_format($rawPercent, 2) : number_format($rawPercent, 1);
                        $statusText  = 'Sebagian (' . $percentDisplay . '%)';
                        $barWidth    = max(4, min(99, round($rawPercent, 1)));
                        $barGradient = 'background: linear-gradient(90deg, #3b82f6, #06b6d4);';
                    } else {
                        $badgeClass  = 'spk-badge-amber';
                        $statusText  = 'Belum Diambil (0%)';
                        $barWidth    = 0;
                        $barGradient = 'background: linear-gradient(90deg, #f59e0b, #d97706);';
                    }

                    return '<div style="min-width: 175px;">
                        <div class="d-flex justify-content-between align-items-center mb-1.5" style="font-size: 11px;">
                            <span class="badge rounded-pill px-2.5 py-0.5 ' . $badgeClass . ' font-monospace fw-bold">' . $statusText . '</span>
                            <span class="spk-text-muted font-monospace fw-semibold">' . $totalBahan . ' Item</span>
                        </div>
                        <div class="progress shadow-inner" style="height: 7px; border-radius: 99px; background-color: rgba(128,128,128,0.2); box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);">
                            <div class="progress-bar rounded-pill" role="progressbar" style="width: ' . $barWidth . '%; ' . $barGradient . '" aria-valuenow="' . $barWidth . '" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>';
                })
                ->addColumn('frekuensi_sesi', function ($spk) {
                    $count = $spk->pengeluaranBahans->count();
                    $last = $spk->pengeluaranBahans->first();
                    $lastDate = $last && $last->tanggal_pengeluaran ? $last->tanggal_pengeluaran->format('d/m/Y') : '-';

                    if ($count === 0) {
                        return '<span class="badge spk-badge-muted rounded-pill px-2.5 py-1 small"><i>Belum ada sesi</i></span>';
                    }

                    return '<div>
                        <span class="badge spk-badge-blue rounded-pill px-3 py-1 font-monospace fw-bold" style="font-size: 11.5px;">
                            <i class="bi bi-file-earmark-text me-1"></i>' . $count . ' Sesi Penyerahan
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
                    $canTambah = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('tambah-pengeluaran-bahan');
                    $canDetail = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('detail-pengeluaran-bahan');
                    $canJurnalFull = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-jurnal-full-pengeluaran-bahan') || $user->can('lihat-jurnal-pengeluaran-bahan');

                    $bName = $spk->barangJadi ? addslashes($spk->barangJadi->nama_barang) : '';
                    $bKode = $spk->barangJadi ? addslashes($spk->barangJadi->kode_barang) : '';
                    $spkNomor = addslashes($spk->nomor_spk);

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-2">';
                    
                    $hasUnfinishedMaterials = false;
                    foreach ($spk->materials as $m) {
                        if ((float)$m->total_keluar_gudang < (float)$m->total_kebutuhan) {
                            $hasUnfinishedMaterials = true;
                            break;
                        }
                    }

                    if ($canTambah && ($spk->status === 'In Progress' || ($spk->status === 'Completed' && $hasUnfinishedMaterials))) {
                        $btn .= '<button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5"
                                    onclick="keluarkanBahanSpk(' . $spk->id . ', \'' . $spkNomor . '\', \'[' . $bKode . '] ' . $bName . '\', \'' . $spk->status . '\')"
                                    title="Keluarkan Bahan untuk SPK ini">
                                    <i class="bi bi-plus-lg"></i>
                                    <span>Keluarkan</span>
                                 </button>';
                    }

                    if ($canDetail) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs d-flex align-items-center gap-1.5"
                                    onclick="showSpkHistoryModal(' . $spk->id . ')"
                                    title="Lihat Detail & Riwayat Pengeluaran">
                                    <i class="bi bi-clock-history text-info"></i>
                                    <span>Riwayat</span>
                                 </button>';
                    }

                    if ($canJurnalFull && $spk->pengeluaranBahans->count() > 0) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5"
                                    onclick="showSpkJurnalFullPbModal(' . $spk->id . ', \'' . e($spk->nomor_spk) . '\')"
                                    title="Lihat Jurnal Full Akuntansi Pengeluaran Bahan Baku">
                                    <i class="bi bi-journal-bookmark-fill" style="color:#0284c7;"></i>
                                    <span>Jurnal Full</span>
                                 </button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['spk_info', 'progress_pengeluaran', 'frekuensi_sesi', 'status_spk', 'action'])
                ->make(true);
        }

        $activeCompanyId = session('active_company_id');

        // SPK yang sedang berjalan (In Progress) atau Completed dengan bahan belum selesai dikeluarkan
        $spkList = SpkProduksi::forActiveCompany()
            ->where(function ($query) {
                $query->where('status', 'In Progress')
                      ->orWhere(function ($q) {
                          $q->where('status', 'Completed')
                            ->whereHas('materials', function ($qm) {
                                $qm->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan');
                            });
                      });
            })
            ->with(['barangJadi', 'satuan', 'materials'])
            ->orderBy('id', 'desc')
            ->get();

        // Master Gudang list (Bahan Baku / Umum)
        $gudangList = MasterGudang::forActiveCompany()
            ->active()
            ->orderBy('nama_gudang', 'asc')
            ->get();

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

            // Auto-generate jurnal jika ada yang belum punya jurnal (hanya pada halaman ini)
            $needsReload = false;
            foreach ($sessions->items() as $pb) {
                if (!$pb->jurnal) {
                    $itemsJurnal = [];
                    foreach ($pb->details as $d) {
                        $bm = $d->barangMentah ?: MasterBarang::find($d->barang_mentah_id);
                        $qty = (float)$d->qty_diambil_sekarang;
                        $stokGudang = StokGudang::where('barang_id', $d->barang_mentah_id)
                            ->where('gudang_id', $pb->gudang_id)->first();
                        $hpp = $stokGudang ? (float)$stokGudang->harga_pokok_rata_rata : ($bm ? (float)$bm->harga_pokok : 0);
                        if ($hpp <= 0 && $bm && (float)$bm->harga_beli > 0) {
                            $hpp = (float)$bm->harga_beli;
                        }
                        if ($hpp <= 0) { $hpp = 1000; }
                        $nilai = round($qty * $hpp, 4);
                        if ($bm && $nilai > 0) {
                            $itemsJurnal[] = ['barang' => $bm, 'nilai' => $nilai];
                        }
                    }
                    if (!empty($itemsJurnal)) {
                        $this->createJurnalPengeluaran($pb, $itemsJurnal);
                        $needsReload = true;
                    }
                }
            }

            // Reload relasi jurnal jika ada yang baru di-generate
            if ($needsReload) {
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
                    ->paginate($perPage, ['*'], 'page', $request->get('page', 1));
            }

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
        $companyId = $spk->company_id ?? session('active_company_id') ?: 1;

        // Auto generate document number: MAT/YYYY/MM/XXXXX
        $yearMonth = date('Y/m', strtotime($request->tanggal_pengeluaran));
        $prefix = "MAT/{$yearMonth}/";
        $lastNo = SpkPengeluaranBahan::where('nomor_pengeluaran', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('nomor_pengeluaran');

        $nextSeq = 1;
        if ($lastNo && preg_match('/\/(\d+)$/', $lastNo, $m)) {
            $nextSeq = (int)$m[1] + 1;
        }
        $nomorPengeluaran = $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $pengeluaran = SpkPengeluaranBahan::create([
                'company_id'           => $companyId,
                'branch_id'            => $spk->branch_id,
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
                $hpp = (float)$stokGudang->harga_pokok_rata_rata;
                if ($hpp <= 0 && $barangMentah) {
                    $hpp = (float)($barangMentah->harga_pokok_rata_rata ?: ($barangMentah->harga_pokok ?: ($barangMentah->harga_beli ?: 0)));
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

            // Create Automatic Accounting Journal (Debit: WIP, Credit: Persediaan Bahan Baku)
            $this->createJurnalPengeluaran($pengeluaran, $itemsJurnal);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengeluaran bahan baku {$nomorPengeluaran} berhasil dicatat, stok gudang terpotong, & jurnal akuntansi diterbitkan!",
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

                $stokAwal = (float)$stokGudang->stok_aktual;
                $stokAkhir = $stokAwal + $qtyOld;
                $hpp = (float)$stokGudang->harga_pokok_rata_rata;
                $nilaiRevert = round($qtyOld * $hpp, 4);

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
                $hpp = (float)$stokGudang->harga_pokok_rata_rata;
                if ($hpp <= 0 && $barangMentah) {
                    $hpp = (float)($barangMentah->harga_pokok_rata_rata ?: ($barangMentah->harga_pokok ?: ($barangMentah->harga_beli ?: 0)));
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

            // STEP 4: Regenerate Automatic Accounting Journal
            $this->createJurnalPengeluaran($pengeluaran, $itemsJurnal);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sesi pengeluaran {$pengeluaran->nomor_pengeluaran} berhasil diperbarui, mutasi stok disesuaikan & jurnal akuntansi diregenerasi!",
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

        $pengeluaran = SpkPengeluaranBahan::forActiveCompany()->with('details')->findOrFail($id);

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

                $stokAwal = (float)$stokGudang->stok_aktual;
                $stokAkhir = $stokAwal + $qtyRevert;
                $hpp = (float)$stokGudang->harga_pokok_rata_rata;
                $nilaiRevert = round($qtyRevert * $hpp, 4);

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
     * Create automatic accounting journal for raw material issuance to production (WIP).
     */
    private function createJurnalPengeluaran(SpkPengeluaranBahan $pengeluaran, array $itemsJurnal): void
    {
        try {
            $companyId = $pengeluaran->company_id ?: (session('active_company_id') ?: 1);
            $totalNilai = 0;
            $debitGroup = [];
            $kreditGroup = [];

            // Debit Account: WIP (Persediaan Barang Dalam Proses)
            // Hierarki Prioritas:
            // 1. Mapping Kategori Produk Jadi SPK (inventory_category -> cat_{bjCatId}_wip)
            // 2. Mapping Modul Produksi WIP (production -> wip_inventory)
            // 3. Mapping Default WIP (default -> default_wip)
            // 4. Fallback Otomatis COA Akun WIP (1108% / WIP / Dalam Proses)
            $spkObj = $pengeluaran->spk ?: SpkProduksi::with('barangJadi')->find($pengeluaran->spk_id);
            $bjCatId = $spkObj && $spkObj->barangJadi ? ($spkObj->barangJadi->kategori_barang_id ?? $spkObj->barangJadi->kategori_id) : null;

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

            // Fallback generic inventory if WIP account not found
            if (!$wipCoaId) {
                $wipCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory')
                         ?? ChartOfAccount::forActiveCompany()->where('code', 'like', '11%')->where('is_header', false)->value('id');
            }

            foreach ($itemsJurnal as $item) {
                $barang = $item['barang'];
                $nilai = (float)$item['nilai'];
                if ($nilai <= 0) continue;

                $totalNilai += $nilai;

                // Credit Account: Persediaan Bahan Baku
                // Hierarki Prioritas:
                // 1. Mapping Spesifik per Item Barang (inventory_item -> item_{barangId}_inventory)
                // 2. Mapping Kategori Barang (inventory_category -> cat_{catId}_inventory) [DIUTAMAKAN]
                // 3. Mapping Default Persediaan Perusahaan (default -> default_inventory)
                // 4. Fallback Pencarian COA Akun Persediaan Bahan Baku (1106% / Bahan Baku)
                $catId = $barang->kategori_barang_id ?? $barang->kategori_id ?? null;
                $barangId = $barang->id ?? null;

                $creditCoaId = ($barangId ? AccountMappingHelper::getCoaId('inventory_item', "item_{$barangId}_inventory") : null)
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

            $nomorJurnal = 'JRN/MAT/' . date('Y/m/') . str_pad($pengeluaran->id, 5, '0', STR_PAD_LEFT);
            $spk = $pengeluaran->spk ?: SpkProduksi::find($pengeluaran->spk_id);
            $spkNomor = $spk ? $spk->nomor_spk : '-';

            // Find existing journal (including soft-deleted) or create new
            $jurnal = Jurnal::withTrashed()
                ->where('referensi_tipe', 'Pengeluaran Bahan SPK')
                ->where('referensi_id', $pengeluaran->id)
                ->first();

            if ($jurnal) {
                $jurnal->restore();
                $jurnal->update([
                    'tanggal_jurnal'  => $pengeluaran->tanggal_pengeluaran,
                    'branch_id'       => $pengeluaran->branch_id,
                    'total_debit'     => $totalNilai,
                    'total_kredit'    => $totalNilai,
                    'keterangan'      => "Pengeluaran Bahan Baku SPK #{$spkNomor} ({$pengeluaran->nomor_pengeluaran})",
                    'updated_by'      => auth()->id(),
                ]);
                // Wipe old details and insert fresh ones
                $jurnal->details()->delete();
            } else {
                $jurnal = Jurnal::create([
                    'nomor_jurnal'    => $nomorJurnal,
                    'tanggal_jurnal'  => $pengeluaran->tanggal_pengeluaran,
                    'referensi_tipe'  => 'Pengeluaran Bahan SPK',
                    'referensi_id'    => $pengeluaran->id,
                    'branch_id'       => $pengeluaran->branch_id,
                    'total_debit'     => $totalNilai,
                    'total_kredit'    => $totalNilai,
                    'keterangan'      => "Pengeluaran Bahan Baku SPK #{$spkNomor} ({$pengeluaran->nomor_pengeluaran})",
                    'created_by'      => auth()->id(),
                ]);
            }

            foreach ($debitGroup as $coaId => $amount) {
                JurnalDetail::create([
                    'jurnal_id'  => $jurnal->id,
                    'account_id' => $coaId,
                    'debit'      => $amount,
                    'kredit'     => 0,
                    'keterangan' => "Barang Dalam Proses (WIP) SPK #{$spkNomor}",
                ]);
            }

            foreach ($kreditGroup as $coaId => $amount) {
                JurnalDetail::create([
                    'jurnal_id'  => $jurnal->id,
                    'account_id' => $coaId,
                    'debit'      => 0,
                    'kredit'     => $amount,
                    'keterangan' => "Pengurangan Persediaan Bahan Baku SPK #{$spkNomor}",
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('createJurnalPengeluaran error: ' . $e->getMessage());
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
                'branch',
                'spk.barangJadi.satuan',
                'spk.satuan',
                'gudang',
                'penyerah',
                'penerima',
                'creator',
                'details.barangMentah.satuan',
                'details.satuan'
            ])
            ->findOrFail($id);

        $company = $pengeluaran->company ?? Company::find(session('active_company_id')) ?? Company::first();

        return view('modul_produksi.pengeluaran_bahan.print_pdf', compact('pengeluaran', 'company'));
    }
}
