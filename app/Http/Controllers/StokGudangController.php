<?php

namespace App\Http\Controllers;

use App\Models\StokGudang;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\MasterKategoriBarang;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StokGudangController extends Controller
{
    /**
     * Build scoped gudang query based on active company, branch session, and optional filter tab.
     * When user is in Cabang session -> strictly that branch's warehouses.
     * When user is in PT Pusat session -> defaults to PT Pusat warehouses (cabang_id = NULL),
     * or switches to a specific branch if selected via tab.
     */
    private function buildGudangQuery($entitas = null, $cabangId = null)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        $query = MasterGudang::where('status', 'Active');

        if ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId);
        }

        // 1. Sesi Cabang Aktif (Navbar Switcher ke Cabang) -> HANYA gudang milik cabang ini (tanpa PT)
        if (!empty($activeBranchId)) {
            return $query->where('cabang_id', $activeBranchId);
        }

        // 2. Sesi PT Pusat Aktif
        if ($entitas === 'cabang' && !empty($cabangId)) {
            // Pengguna di PT Pusat memilih tab Cabang tertentu
            return $query->where('cabang_id', $cabangId);
        }

        // Default PT Pusat: HANYA gudang milik PT Pusat (cabang_id IS NULL)
        return $query->whereNull('cabang_id');
    }

    private function canSeeHpp(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->hasRole('Super Admin')
            || $user->hasRole('Owner')
            || $user->hasRole('Admin')
            || $user->can('lihat-hpp-stok')
            || $user->can('lihat-hpp-transfer')
            || $user->can('akses-accounting');
    }

    public function index(Request $request)
    {
        // ── 1. Authenticate & Authorize ────────────────────────────────────
        $this->authorize('lihat-stok-aktual');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        $isBranchSession = !empty($activeBranchId);
        $entitas  = $request->input('entitas', $isBranchSession ? 'cabang' : 'pt');
        $cabangId = $request->input('cabang_id', $isBranchSession ? $activeBranchId : null);

        // Fetch branches of active company (for tabs when in PT Pusat mode)
        $branches = collect();
        if (!$isBranchSession && $activeCompanyId) {
            $branches = Branch::where('company_id', $activeCompanyId)
                ->where('status', 'Active')
                ->orderBy('nama_cabang')
                ->get(['id', 'kode_cabang', 'nama_cabang']);
        }

        // Determine active entity label
        if ($isBranchSession) {
            $activeEntityLabel = session('active_branch_name', 'Cabang');
            $activeEntityType  = 'cabang';
        } else {
            if ($entitas === 'cabang' && !empty($cabangId)) {
                $targetBranch = $branches->firstWhere('id', (int)$cabangId);
                $activeEntityLabel = $targetBranch ? $targetBranch->nama_cabang : 'Cabang';
                $activeEntityType  = 'cabang';
            } else {
                $activeEntityLabel = session('active_company_name', 'PT Pusat') . ' (Pusat)';
                $activeEntityType  = 'pt';
            }
        }

        // ── 2. Fetch Filter Options (Scoped to Active Entity) ────
        $gudangs = $this->buildGudangQuery($entitas, $cabangId)
            ->with('cabang')
            ->orderBy('nama_gudang')
            ->get();

        $kategoris = MasterKategoriBarang::where('status', 'Active')
            ->forActiveCompany()
            ->orderBy('nama_kategori')
            ->get();

        $activeGudangIds = $gudangs->pluck('id');

        // ── 3. Filters ─────────────────────────────────────────────────────
        $gudangId   = $request->input('gudang_id');
        $kategoriId = $request->input('kategori_id');
        $status     = $request->input('status'); // 'Aman', 'Kritis', 'Kosong'
        $search     = $request->input('search');

        // Security: ensure selected gudang_id belongs to active branch
        if ($gudangId && !$activeGudangIds->contains((int)$gudangId)) {
            $gudangId = null;
        }

        // ── 4. Base Query Construction (Optimized with Joins for database-level sorting/filtering) ───────
        $query = StokGudang::query()
            ->select('stok_gudangs.*')
            ->join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
            ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
            ->whereIn('stok_gudangs.gudang_id', $activeGudangIds);

        // Apply filters to query
        if ($gudangId) {
            $query->where('stok_gudangs.gudang_id', $gudangId);
        }
        if ($kategoriId) {
            $query->where('master_barang.kategori_barang_id', $kategoriId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('master_barang.nama_barang', 'like', "%{$search}%")
                  ->orWhere('master_barang.kode_barang', 'like', "%{$search}%");
            });
        }

        if ($status) {
            if ($status === 'Kosong') {
                $query->where('stok_gudangs.stok_aktual', '<=', 0);
            } elseif ($status === 'Kritis') {
                $query->where('stok_gudangs.stok_aktual', '>', 0)
                      ->whereRaw('stok_gudangs.stok_aktual <= master_barang.minimum_stok');
            } elseif ($status === 'Aman') {
                $query->where('stok_gudangs.stok_aktual', '>', 0)
                      ->whereRaw('stok_gudangs.stok_aktual > master_barang.minimum_stok');
            }
        }

        // ── 5. KPI Aggregate Calculations (Single Pass DB Query for High Performance at 7M+ scale) ──
        $kpiStats = DB::table('stok_gudangs')
            ->join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
            ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
            ->whereIn('stok_gudangs.gudang_id', $activeGudangIds)
            ->when($gudangId, fn($q) => $q->where('stok_gudangs.gudang_id', $gudangId))
            ->when($kategoriId, fn($q) => $q->where('master_barang.kategori_barang_id', $kategoriId))
            ->when($search, function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('master_barang.nama_barang', 'like', "%{$search}%")
                       ->orWhere('master_barang.kode_barang', 'like', "%{$search}%");
                });
            })
            ->selectRaw('
                SUM(stok_gudangs.stok_aktual) as total_qty,
                SUM(CASE WHEN stok_gudangs.stok_aktual <= 0 THEN 1 ELSE 0 END) as total_kosong,
                SUM(CASE WHEN stok_gudangs.stok_aktual > 0 AND stok_gudangs.stok_aktual <= master_barang.minimum_stok THEN 1 ELSE 0 END) as total_kritis,
                SUM(CASE WHEN stok_gudangs.stok_aktual > 0 AND stok_gudangs.stok_aktual > master_barang.minimum_stok THEN 1 ELSE 0 END) as total_aman,
                SUM(CASE WHEN master_barang.tipe_item = "Stok" THEN stok_gudangs.nilai_persediaan ELSE 0 END) as total_nilai_persediaan
            ')
            ->first();

        $totalQty             = (float) ($kpiStats->total_qty ?? 0);
        $totalKosong          = (int) ($kpiStats->total_kosong ?? 0);
        $totalKritis          = (int) ($kpiStats->total_kritis ?? 0);
        $totalAman            = (int) ($kpiStats->total_aman ?? 0);
        $totalNilaiPersediaan = (float) ($kpiStats->total_nilai_persediaan ?? 0);

        $canSeeHpp = $this->canSeeHpp();
        $totalNilaiPersediaanFmt = $canSeeHpp
            ? 'Rp ' . number_format($totalNilaiPersediaan, 2, ',', '.')
            : '***';

        // ── 6. AJAX Request Handling for Server-Side Yajra DataTables ──────────────────
        if ($request->ajax()) {
            $datatableQuery = $query->with([
                'barang:id,kode_barang,nama_barang,minimum_stok,tipe_item,kategori_barang_id,satuan_id,warna_id',
                'barang.kategori:id,nama_kategori',
                'barang.satuan:id,nama_satuan',
                'barang.warna:id,nama_warna',
                'barang.fotos:id,barang_id,foto_path',
                'gudang:id,nama_gudang,cabang_id',
                'gudang.cabang:id,nama_cabang',
            ]);

            return DataTables::eloquent($datatableQuery)
                ->addIndexColumn()
                ->addColumn('kode_barang', fn($s) => $s->barang?->kode_barang ?? '-')
                ->addColumn('nama_barang', fn($s) => $s->barang?->nama_barang ?? '-')
                ->addColumn('nama_gudang', function ($s) {
                    $gudangNama = $s->gudang?->nama_gudang ?? '-';
                    $isCabang = !empty($s->gudang?->cabang_id);
                    $cabangNama = $s->gudang?->cabang?->nama_cabang;
                    $badge = $isCabang
                        ? '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-0.5 ms-1" style="font-size:0.65rem;"><i class="bi bi-shop me-1"></i>' . e($cabangNama ?: 'Cabang') . '</span>'
                        : '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5 ms-1" style="font-size:0.65rem;"><i class="bi bi-building me-1"></i>PT Pusat</span>';
                    return '<div class="fw-semibold text-dark text-nowrap">' . e($gudangNama) . '</div>' . $badge;
                })
                ->addColumn('warna', fn($s) => $s->barang?->warna?->nama_warna ?? '-')
                ->addColumn('min_stok', function ($s) {
                    $min = (float) ($s->barang?->minimum_stok ?? 0);
                    return number_format($min, 0, ',', '.');
                })
                ->addColumn('stok_aktual_fmt', function ($s) {
                    $aktual = (float) $s->stok_aktual;
                    $satuan = $s->barang?->satuan?->nama_satuan ?? '';
                    $formatted = (floor($aktual) == $aktual)
                        ? number_format($aktual, 0, ',', '.')
                        : rtrim(rtrim(number_format($aktual, 2, ',', '.'), '0'), ',');
                    return $formatted . ' <small class="text-secondary fw-normal">' . e($satuan) . '</small>';
                })
                ->addColumn('harga_beli_sebelumnya_fmt', function ($s) use ($canSeeHpp) {
                    if (!$canSeeHpp) return '<span class="text-muted small">***</span>';
                    if ($s->barang?->tipe_item === 'Stok') {
                        return 'Rp ' . number_format($s->harga_beli_sebelumnya, 2, ',', '.');
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('harga_beli_terakhir_fmt', function ($s) use ($canSeeHpp) {
                    if (!$canSeeHpp) return '<span class="text-muted small">***</span>';
                    if ($s->barang?->tipe_item === 'Stok') {
                        return 'Rp ' . number_format($s->harga_beli_terakhir, 2, ',', '.');
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('harga_pokok_rata_rata_fmt', function ($s) use ($canSeeHpp) {
                    if (!$canSeeHpp) return '<span class="text-muted small">***</span>';
                    if ($s->barang?->tipe_item === 'Stok') {
                        return 'Rp ' . number_format($s->harga_pokok_rata_rata, 2, ',', '.');
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('nilai_persediaan_fmt', function ($s) use ($canSeeHpp) {
                    if (!$canSeeHpp) return '<span class="text-muted small">***</span>';
                    if ($s->barang?->tipe_item === 'Stok') {
                        $nilai = (float) $s->nilai_persediaan;
                        return 'Rp ' . number_format($nilai, 2, ',', '.');
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('safety_status_badge', function ($s) {
                    $stokAktual = (float) $s->stok_aktual;
                    $minStok    = (float) ($s->barang?->minimum_stok ?? 0);
                    if ($stokAktual <= 0) {
                        return '<span class="badge-danger-stock"><i class="bi bi-x-circle me-1"></i>Kosong</span>';
                    } elseif ($stokAktual <= $minStok) {
                        return '<span class="badge-warning-stock"><span class="pulse-dot-warning"></span><i class="bi bi-exclamation-triangle me-1"></i>Min</span>';
                    } else {
                        return '<span class="badge-safe"><i class="bi bi-shield-check me-1"></i>Aman</span>';
                    }
                })
                ->addColumn('action', function ($s) use ($canSeeHpp) {
                    $barangNama        = e($s->barang?->nama_barang ?? '-');
                    $barangKode        = e($s->barang?->kode_barang ?? '-');
                    $kategori          = e($s->barang?->kategori?->nama_kategori ?? '-');
                    $gudang            = e($s->gudang?->nama_gudang ?? '-');
                    $warna             = e($s->barang?->warna?->nama_warna ?? '-');
                    $minStokVal        = (float) ($s->barang?->minimum_stok ?? 0);
                    $satuan            = e($s->barang?->satuan?->nama_satuan ?? '');
                    $stokAktualVal     = (float) $s->stok_aktual;
                    $hppVal            = $canSeeHpp ? (float) $s->harga_pokok_rata_rata : 0;
                    $hppSebelumnyaVal  = $canSeeHpp ? (float) $s->hpp_sebelumnya : 0;
                    $beliTerakhirVal   = $canSeeHpp ? (float) $s->harga_beli_terakhir : 0;
                    $beliSebelumnyaVal = $canSeeHpp ? (float) $s->harga_beli_sebelumnya : 0;
                    $nilaiVal          = $canSeeHpp ? ($stokAktualVal * $hppVal) : 0;

                    $safetyStatus = 'Aman';
                    if ($stokAktualVal <= 0)            $safetyStatus = 'Kosong';
                    elseif ($stokAktualVal <= $minStokVal) $safetyStatus = 'Kritis';

                    $updatedAt = $s->updated_at
                        ? $s->updated_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s')
                        : '-';

                    $firstFoto = $s->barang?->fotos?->first();
                    $fotoUrl   = $firstFoto ? asset('storage/' . $firstFoto->foto_path) : '';
                    $jsonFotos = e(json_encode($s->barang?->fotos?->pluck('foto_path')->map(fn($p) => asset('storage/' . $p))->toArray() ?? []));

                    return '
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-circle btn-detail-stok"
                            data-barang-id="' . $s->barang_id . '"
                            data-gudang-id="' . $s->gudang_id . '"
                            data-barang-nama="' . $barangNama . '"
                            data-barang-kode="' . $barangKode . '"
                            data-kategori="' . $kategori . '"
                            data-gudang="' . $gudang . '"
                            data-warna="' . $warna . '"
                            data-min-stok="' . number_format($minStokVal, 0, ',', '.') . ' ' . $satuan . '"
                            data-stok-aktual="' . number_format($stokAktualVal, 0, ',', '.') . ' ' . $satuan . '"
                            data-hpp="' . ($canSeeHpp ? 'Rp ' . number_format($hppVal, 2, ',', '.') : '***') . '"
                            data-hpp-sebelumnya="' . ($canSeeHpp ? 'Rp ' . number_format($hppSebelumnyaVal, 2, ',', '.') : '***') . '"
                            data-harga-beli-terakhir="' . ($canSeeHpp ? ($beliTerakhirVal > 0 ? 'Rp ' . number_format($beliTerakhirVal, 2, ',', '.') : '-') : '***') . '"
                            data-beli-terakhir="' . ($canSeeHpp ? ($beliTerakhirVal > 0 ? 'Rp ' . number_format($beliTerakhirVal, 2, ',', '.') : '-') : '***') . '"
                            data-harga-beli-sebelumnya="' . ($canSeeHpp ? ($beliSebelumnyaVal > 0 ? 'Rp ' . number_format($beliSebelumnyaVal, 2, ',', '.') : '-') : '***') . '"
                            data-beli-sebelumnya="' . ($canSeeHpp ? ($beliSebelumnyaVal > 0 ? 'Rp ' . number_format($beliSebelumnyaVal, 2, ',', '.') : '-') : '***') . '"
                            data-total-nilai="' . ($canSeeHpp ? 'Rp ' . number_format($nilaiVal, 2, ',', '.') : '***') . '"
                            data-nilai-persediaan="' . ($canSeeHpp ? 'Rp ' . number_format($nilaiVal, 2, ',', '.') : '***') . '"
                            data-safety-status="' . $safetyStatus . '"
                            data-foto-url="' . $fotoUrl . '"
                            data-fotos="' . $jsonFotos . '"
                            data-updated-at="' . $updatedAt . '"
                            title="Lihat Detail">
                            <i class="bi bi-eye"></i>
                        </button>
                    ';
                })
                ->rawColumns(['nama_gudang', 'stok_aktual_fmt', 'harga_pokok_rata_rata_fmt', 'harga_beli_terakhir_fmt', 'harga_beli_sebelumnya_fmt', 'nilai_persediaan_fmt', 'safety_status_badge', 'action'])
                ->with([
                    'totalQty'            => number_format($totalQty, 0, ',', '.'),
                    'totalAman'           => number_format($totalAman, 0, ',', '.'),
                    'totalKritis'         => number_format($totalKritis, 0, ',', '.'),
                    'totalKosong'         => number_format($totalKosong, 0, ',', '.'),
                    'totalNilaiPersediaan'=> $totalNilaiPersediaanFmt,
                    'canSeeHpp'           => $canSeeHpp,
                    'activeEntityLabel'   => $activeEntityLabel,
                    'activeEntityType'    => $activeEntityType,
                    'gudangs'             => $gudangs->map(fn($g) => ['id' => $g->id, 'nama_gudang' => $g->nama_gudang]),
                ])
                ->make(true);
        }

        // Dummy collection for initial blade page load (rendered by ajax datatable)
        $mappedStock = collect();

        // Do NOT load all barangs here — dropdown uses Select2 AJAX search instead.
        $barangs = collect();

        return view('modul_inventori.stok_saatini.viewstok', compact(
            'mappedStock',
            'gudangs',
            'kategoris',
            'barangs',
            'branches',
            'isBranchSession',
            'entitas',
            'cabangId',
            'activeEntityLabel',
            'activeEntityType',
            'gudangId',
            'kategoriId',
            'status',
            'search',
            'totalQty',
            'totalAman',
            'totalKritis',
            'totalKosong',
            'totalNilaiPersediaan',
            'canSeeHpp'
        ));
    }

    public function print(Request $request)
    {
        $this->authorize('lihat-stok-aktual');

        $entitas    = $request->input('entitas');
        $cabangId   = $request->input('cabang_id');
        $gudangId   = $request->input('gudang_id');
        $kategoriId = $request->input('kategori_id');
        $barangId   = $request->input('barang_id');

        // Fetch all warehouses for active entity scope
        $activeGudangs = $this->buildGudangQuery($entitas, $cabangId)->pluck('id');

        // Security: ensure selected gudang_id belongs to current user's scope
        if ($gudangId && !$activeGudangs->contains((int)$gudangId)) {
            $gudangId = null;
        }

        $query = StokGudang::query()
            ->select('stok_gudangs.*')
            ->join('master_barang', 'stok_gudangs.barang_id', '=', 'master_barang.id')
            ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
            ->whereIn('stok_gudangs.gudang_id', $activeGudangs);

        if ($gudangId) {
            $query->where('stok_gudangs.gudang_id', $gudangId);
        }
        if ($kategoriId) {
            $query->where('master_barang.kategori_barang_id', $kategoriId);
        }
        if ($barangId) {
            $query->where('stok_gudangs.barang_id', $barangId);
        }

        $items = $query->with(['barang.kategori', 'barang.satuan', 'barang.warna', 'barang.primaryFoto', 'barang.fotos', 'gudang'])
            ->orderBy('master_barang.nama_barang', 'asc')
            ->get();

        // Resolve display names safely — only from within user's accessible scope
        $selectedGudangName   = $gudangId
            ? ($this->buildGudangQuery($entitas, $cabangId)->where('id', $gudangId)->value('nama_gudang') ?? '-')
            : 'Semua Gudang';
        $selectedKategoriName = $kategoriId
            ? (MasterKategoriBarang::find($kategoriId)?->nama_kategori ?? '-')
            : 'Semua Kategori';
        $selectedBarangName   = $barangId
            ? (MasterBarang::find($barangId)?->nama_barang ?? '-')
            : 'Semua Barang';

        $activeBranchName = session('active_branch_name');
        if ($activeBranchName) {
            $entitasTitle = 'Cabang: ' . $activeBranchName;
        } elseif ($entitas === 'cabang' && $cabangId) {
            $branch = Branch::find($cabangId);
            $entitasTitle = 'Cabang: ' . ($branch?->nama_cabang ?? 'Cabang');
        } else {
            $entitasTitle = 'PT: ' . (session('active_company_name') ?: 'Pusat') . ' (PT Pusat)';
        }

        $canSeeHpp     = $this->canSeeHpp();
        $tampilkanFoto = $request->has('tampilkan_foto') ? (bool) $request->input('tampilkan_foto') : true;

        return view('modul_inventori.stok_saatini.printstok', compact(
            'items',
            'selectedGudangName',
            'selectedKategoriName',
            'selectedBarangName',
            'entitasTitle',
            'canSeeHpp',
            'tampilkanFoto'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // LAST UPDATED — Ultra-lightweight real-time ping endpoint
    // Returns MAX(updated_at) across all stok_gudangs records for
    // the active branch. Frontend polls this every 5s and only
    // triggers a full DataTable reload when the value changes.
    // Query cost: single aggregate — extremely fast even at 7M rows.
    // ══════════════════════════════════════════════════════════════
    public function lastUpdated(Request $request)
    {
        $this->authorize('lihat-stok-aktual');

        $entitas  = $request->input('entitas');
        $cabangId = $request->input('cabang_id');

        $activeGudangIds = $this->buildGudangQuery($entitas, $cabangId)->pluck('id');

        // Single MAX aggregate — O(log n) with index, near-instant even at millions of rows
        $lastUpdated = DB::table('stok_gudangs')
            ->whereIn('gudang_id', $activeGudangIds)
            ->max('updated_at');

        return response()->json([
            'last_updated' => $lastUpdated,
        ])->header('Cache-Control', 'no-store');
    }

    // ══════════════════════════════════════════════════════════════
    // KARTU INFO — Fetch stock overview details
    // ══════════════════════════════════════════════════════════════
    public function kartuInfo(Request $request)
    {
        $this->authorize('lihat-stok-aktual');

        $entitas  = $request->input('entitas');
        $cabangId = $request->input('cabang_id');
        $gudangId = (int) $request->input('gudang_id');
        $barangId = (int) $request->input('barang_id');

        // Security: validate gudang & barang IDs are non-zero integers
        if (!$gudangId || !$barangId) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        $gudangValid = $this->buildGudangQuery($entitas, $cabangId)
            ->where('id', $gudangId)
            ->exists();

        if (!$gudangValid) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Latest 2 actual purchase records (excluding opname/transfers/adjustments)
        $latestPurchases = \App\Models\KartuStok::where('barang_id', $barangId)
            ->where('gudang_id', $gudangId)
            ->where('tipe', 'Masuk')
            ->whereNotIn('referensi_jenis', [
                'Stock Opname', 'Stok Opname', 
                'Transfer Gudang', 'Transfer Internal', 'Transfer Inter-PT', 'Transfer Inter-Cabang',
                'Stock Adjustment', 'Penyesuaian Stok'
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(2)
            ->get();

        // Latest mutation for stok_sebelumnya and last transaction source
        $latestMutationRecord = \App\Models\KartuStok::where('barang_id', $barangId)
            ->where('gudang_id', $gudangId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        $latestMutation = $latestMutationRecord ? $latestMutationRecord->stok_awal : null;

        $sumberTerakhir = '-';
        if ($latestMutationRecord) {
            $refJenis = $latestMutationRecord->referensi_jenis ?: 'Mutasi';
            $refNo    = $latestMutationRecord->referensi_nomor ?: $latestMutationRecord->referensi_id;
            $tglStr   = $latestMutationRecord->created_at ? \Carbon\Carbon::parse($latestMutationRecord->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-';
            $sumberTerakhir = "{$refJenis} #{$refNo} ({$tglStr})";
        } else {
            $sumberTerakhir = "Saldo Awal / Transaksi Perdana";
        }

        $fmt = fn($dt) => $dt ? \Carbon\Carbon::parse($dt)->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') : '-';

        $canSeeHpp = $this->canSeeHpp();
        $stokGudang = StokGudang::where('barang_id', $barangId)->where('gudang_id', $gudangId)->first();

        $hargaBeliTerakhir = (float) ($stokGudang?->harga_beli_terakhir ?? 0);
        $hargaBeliSebelumnya = (float) ($stokGudang?->harga_beli_sebelumnya ?? 0);

        if ($hargaBeliTerakhir <= 0 && $latestPurchases->count() > 0) {
            $hargaBeliTerakhir = (float) ($latestPurchases[0]->harga_satuan ?? 0);
        }
        if ($hargaBeliSebelumnya <= 0 && $latestPurchases->count() > 1) {
            $hargaBeliSebelumnya = (float) ($latestPurchases[1]->harga_satuan ?? 0);
        }

        return response()->json([
            'stok_sebelumnya'           => $latestMutation !== null
                ? number_format((float) $latestMutation, 0, ',', '.')
                : '-',
            'sumber_transaksi_terakhir' => $sumberTerakhir,
            'tanggal_beli_terakhir'     => $fmt($latestPurchases[0]->created_at ?? null),
            'tanggal_beli_sebelumnya'   => $fmt($latestPurchases[1]->created_at ?? null),
            'harga_beli_terakhir'       => $canSeeHpp ? ($hargaBeliTerakhir > 0 ? 'Rp ' . number_format($hargaBeliTerakhir, 2, ',', '.') : '-') : '***',
            'harga_beli_sebelumnya'     => $canSeeHpp ? ($hargaBeliSebelumnya > 0 ? 'Rp ' . number_format($hargaBeliSebelumnya, 2, ',', '.') : '-') : '***',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // MUTASI HISTORY — Fetch categorized mutation table rows for detail modal
    // ══════════════════════════════════════════════════════════════
    public function mutasiHistory(Request $request)
    {
        $this->authorize('lihat-stok-aktual');

        $entitas    = $request->input('entitas');
        $cabangId   = $request->input('cabang_id');
        $gudangId   = (int) $request->input('gudang_id');
        $barangId   = (int) $request->input('barang_id');
        $tipeFilter = strtolower(trim($request->input('tipe_filter', 'semua')));

        if (!$gudangId || !$barangId) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        $gudangValid = $this->buildGudangQuery($entitas, $cabangId)
            ->where('id', $gudangId)
            ->exists();

        if (!$gudangValid) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $query = \App\Models\KartuStok::where('barang_id', $barangId)
            ->where('gudang_id', $gudangId);

        if ($tipeFilter === 'masuk') {
            // Goods Receipt, Purchase, Saldo Awal, or Tipe Masuk (excluding Transfer & Opname)
            $query->where('tipe', 'Masuk')
                ->where(function ($q) {
                    $q->whereNull('referensi_jenis')
                      ->orWhereNotIn('referensi_jenis', ['Transfer Gudang', 'Stok Opname', 'Stock Opname']);
                });
        } elseif ($tipeFilter === 'keluar') {
            // Goods Issue, Sales, or Tipe Keluar (excluding Transfer & Opname)
            $query->where('tipe', 'Keluar')
                ->where(function ($q) {
                    $q->whereNull('referensi_jenis')
                      ->orWhereNotIn('referensi_jenis', ['Transfer Gudang', 'Stok Opname', 'Stock Opname']);
                });
        } elseif ($tipeFilter === 'transfer') {
            // Warehouse Transfer (referensi_jenis = 'Transfer Gudang' or keterangan contains Transfer)
            $query->where(function ($q) {
                $q->where('referensi_jenis', 'Transfer Gudang')
                  ->orWhere('keterangan', 'like', '%Transfer%');
            });
        } elseif ($tipeFilter === 'opname') {
            // Stock Opname (referensi_jenis = 'Stok Opname' / 'Stock Opname' or keterangan contains Opname)
            $query->where(function ($q) {
                $q->whereIn('referensi_jenis', ['Stok Opname', 'Stock Opname'])
                  ->orWhere('keterangan', 'like', '%Opname%');
            });
        }

        $limit = (int) $request->input('limit', 15);
        if ($limit <= 0) $limit = 15;
        if ($limit > 100) $limit = 100;

        $totalCount = (clone $query)->count();

        $mutasis = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $satuan = MasterBarang::find($barangId)?->satuan?->nama_satuan ?? '';
        $canSeeHpp = $this->canSeeHpp();

        $data = $mutasis->map(function ($m) use ($satuan, $canSeeHpp, $gudangId, $barangId) {
            $tgl = $m->created_at
                ? \Carbon\Carbon::parse($m->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i:s')
                : '-';

            $refJenis = $m->referensi_jenis ?: 'Mutasi';
            $refNo    = $m->referensi_nomor ?: $m->referensi_id;
            $refStr   = "{$refJenis} #" . ($refNo ?: '-');

            $jumlahVal   = (float) $m->jumlah;
            $stokAwalVal = (float) $m->stok_awal;
            $stokAkhirVal= (float) $m->stok_akhir;
            $hargaVal    = (float) $m->harga_satuan;
            $nilaiVal    = (float) $m->nilai_total;

            if ($hargaVal == 0) {
                $sg = StokGudang::where('barang_id', $barangId)->where('gudang_id', $gudangId)->first();
                if ($sg) {
                    $hargaVal = (float) ($sg->harga_pokok_rata_rata ?: $sg->harga_beli_terakhir);
                }
            }

            if ($nilaiVal == 0 && $hargaVal > 0) {
                $nilaiVal = $jumlahVal * $hargaVal;
            }

            $tipeKet = $m->tipe;
            if (str_contains(strtolower($refJenis), 'transfer') || str_contains(strtolower($m->keterangan), 'transfer')) {
                if (str_contains(strtolower($refJenis), 'inter-pt') || str_contains(strtolower($m->keterangan), 'inter-pt')) {
                    $tipeKet = 'Transfer Inter-PT';
                } elseif (str_contains(strtolower($refJenis), 'inter-cabang') || str_contains(strtolower($m->keterangan), 'inter-cabang')) {
                    $tipeKet = 'Transfer Inter-Cabang';
                } else {
                    $tipeKet = 'Transfer Internal';
                }
            } elseif (str_contains(strtolower($refJenis), 'opname')) {
                $tipeKet = 'Stok Opname';
            }

            return [
                'id'              => $m->id,
                'tanggal'         => $tgl,
                'tipe'            => $m->tipe,
                'tipe_ket'        => $tipeKet,
                'referensi'       => $refStr,
                'referensi_jenis' => $m->referensi_jenis,
                'referensi_nomor' => $m->referensi_nomor,
                'jumlah_fmt'      => number_format($jumlahVal, 0, ',', '.') . ' ' . $satuan,
                'stok_awal_fmt'   => number_format($stokAwalVal, 0, ',', '.') . ' ' . $satuan,
                'stok_akhir_fmt'  => number_format($stokAkhirVal, 0, ',', '.') . ' ' . $satuan,
                'harga_satuan_fmt'=> $canSeeHpp ? ('Rp ' . number_format($hargaVal, 2, ',', '.')) : '***',
                'nilai_total_fmt' => $canSeeHpp ? ('Rp ' . number_format($nilaiVal, 2, ',', '.')) : '***',
                'keterangan'      => $m->keterangan ?: '-',
            ];
        });

        return response()->json([
            'tipe_filter' => $tipeFilter,
            'total_rows'  => count($data),
            'total_count' => $totalCount,
            'limit'       => $limit,
            'mutasi'      => $data,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SEARCH BARANG — Select2 AJAX remote search for 7M+ scale
    // ══════════════════════════════════════════════════════════════
    public function searchBarang(Request $request)
    {
        $this->authorize('lihat-stok-aktual');

        $q = trim($request->input('q', ''));
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $barangs = MasterBarang::where('status', 'Active')
            ->where(function ($query) use ($q) {
                $query->where('kode_barang', 'like', "%{$q}%")
                      ->orWhere('nama_barang', 'like', "%{$q}%");
            })
            ->limit(25)
            ->get(['id', 'kode_barang', 'nama_barang']);

        $results = $barangs->map(function ($b) {
            return [
                'id'   => $b->id,
                'text' => "{$b->kode_barang} - {$b->nama_barang}"
            ];
        });

        return response()->json($results);
    }
}
