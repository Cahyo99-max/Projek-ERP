<?php

namespace App\Http\Controllers;

use App\Models\GoodsIssue;
use App\Models\GoodsIssueDetail;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\MasterTipePengeluaran;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\ChartOfAccount;
use App\Helpers\AccountMappingHelper;
use App\Helpers\DecimalHelper;
use App\Http\Controllers\WhatsAppGatewayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoodsIssueController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // LAYER 1: PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    /**
     * Resolve & verify a warehouse belongs to active company and active branch.
     * Returns the MasterGudang model or null.
     */
    private function resolveGudang(int $gudangId): ?MasterGudang
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        $query = MasterGudang::where('status', 'Active');
        if ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId);
        }

        $gudang = $query->where('id', $gudangId)->first();
        if (!$gudang) return null;

        if (auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Owner') || $this->isSuperAdminUser())) {
            return $gudang;
        }

        if (!empty($activeBranchId) && !empty($gudang->cabang_id) && $gudang->cabang_id != $activeBranchId) {
            return null;
        }

        return $gudang;
    }

    /**
     * Verify a GoodsIssue belongs to active company and active branch's warehouses.
     */
    private function resolveGoodsIssue(GoodsIssue $gi): bool
    {
        if (auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Owner') || $this->isSuperAdminUser())) {
            return true;
        }

        $gudang = MasterGudang::find($gi->gudang_id);
        if (!$gudang) return false;

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        if ($activeCompanyId && !empty($gudang->company_id) && $gudang->company_id != $activeCompanyId) {
            return false;
        }

        if (!empty($activeBranchId) && !empty($gudang->cabang_id) && $gudang->cabang_id != $activeBranchId) {
            return false;
        }

        return true;
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX — List all GI for active branch
    // ══════════════════════════════════════════════════════════════
    public function index()
    {
        // LAYER 2: Permission check
        $this->authorize('lihat-gi');

        $activeBranchId = session('active_branch_id');

        // LAYER 3: Branch & Company scope — strictly only warehouses belonging to active company & branch
        $activeGudangQuery = MasterGudang::active()->forActiveCompany();
        if (!empty($activeBranchId)) {
            $activeGudangQuery->where('cabang_id', $activeBranchId);
        } else {
            $activeGudangQuery->whereNull('cabang_id');
        }
        $activeGudangIds = $activeGudangQuery->pluck('id');

        $userId = auth()->id();

        $issuesQuery = GoodsIssue::forActiveCompany()
            ->forActiveBranch()
            ->whereIn('gudang_id', $activeGudangIds)
            ->where(function ($q) use ($userId) {
                // STRICT PRIVACY: Status Draft HANYA BISA DILIHAT OLEH PEMBUATNYA (Super Admin pun tidak bisa melihat draf milik user lain)
                $q->where('status', '!=', 'Draft')
                  ->orWhere(function ($draftQ) use ($userId) {
                      $draftQ->where('status', 'Draft')
                             ->where('created_by', $userId);
                  });
            })
            ->with([
                'gudang:id,kode_gudang,nama_gudang,cabang_id,company_id',
                'company:id,nama_perusahaan',
                'branch:id,nama_cabang',
                'creator:id,nama_lengkap,username',
            ]);

        $issues = $issuesQuery->orderBy('tanggal_gi', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        $gudangs = MasterGudang::whereIn('id', $activeGudangIds)
            ->select('id', 'kode_gudang', 'nama_gudang', 'cabang_id', 'company_id')
            ->orderBy('nama_gudang')
            ->get();

        $barangs = MasterBarang::active()
            ->forActiveCompany()
            ->select('id', 'kode_barang', 'nama_barang', 'satuan_id')
            ->with('satuan:id,nama_satuan')
            ->orderBy('nama_barang')
            ->get();

        $barangsData = $barangs->map(function ($b) {
            return [
                'id'    => $b->id,
                'kode'  => $b->kode_barang,
                'nama'  => $b->nama_barang,
                'satuan'=> $b->satuan ? $b->satuan->nama_satuan : '-',
            ];
        })->values();

        $tipePengeluarans = MasterTipePengeluaran::forActiveCompany()
            ->active()
            ->select('id', 'kode_tipe', 'nama_tipe', 'coa_id', 'coa_kredit_id')
            ->orderBy('nama_tipe')
            ->get();

        return view('modul_inventori.barang_keluar.viewissue', compact('issues', 'gudangs', 'barangs', 'barangsData', 'tipePengeluarans'));
    }

    // ══════════════════════════════════════════════════════════════
    // GET BARANGS BY GUDANG — Only items in stock for selected gudang & active company/branch
    // ══════════════════════════════════════════════════════════════
    public function getBarangsByGudang(int $gudangId)
    {
        $this->authorize('tambah-gi');

        $gudang = $this->resolveGudang($gudangId);
        if (!$gudang) {
            return response()->json(['error' => 'Gudang tidak ditemukan atau bukan milik PT/cabang aktif.'], 403);
        }

        if ($gudang->izinkan_stok_negatif) {
            $barangs = MasterBarang::active()
                ->forActiveCompany()
                ->with(['satuan', 'primaryFoto', 'kategori'])
                ->orderBy('nama_barang')
                ->get();
        } else {
            // Strictly fetch items with stok_aktual > 0 in this specific warehouse
            $stokItems = StokGudang::where('gudang_id', $gudangId)
                ->where('stok_aktual', '>', 0)
                ->pluck('stok_aktual', 'barang_id');

            $barangIds = $stokItems->keys()->toArray();

            $barangs = MasterBarang::active()
                ->forActiveCompany()
                ->with(['satuan', 'primaryFoto', 'kategori'])
                ->whereIn('id', $barangIds)
                ->orderBy('nama_barang')
                ->get();
        }

        // Fetch stok for all items in this gudang in a single query
        $stokMap = StokGudang::where('gudang_id', $gudangId)
            ->pluck('stok_aktual', 'barang_id');

        $result = $barangs->map(function ($b) use ($stokMap) {
            $stokVal = floatval($stokMap[$b->id] ?? 0);
            $fotoUrl = $b->primaryFoto ? asset('storage/' . $b->primaryFoto->foto_path) : null;
            $kategoriName = $b->kategori ? $b->kategori->nama_kategori : '-';

            return [
                'id'          => $b->id,
                'kode'        => $b->kode_barang,
                'nama'        => $b->nama_barang,
                'kategori'    => $kategoriName,
                'satuan'      => $b->satuan ? $b->satuan->nama_satuan : '-',
                'stok_aktual' => $stokVal,
                'foto'        => $fotoUrl,
            ];
        })->values();

        return response()->json(['barangs' => $result]);
    }

    // ══════════════════════════════════════════════════════════════
    // GET STOK — AJAX endpoint: real-time stok for a barang in a gudang
    // ══════════════════════════════════════════════════════════════
    public function getStokBarang(int $gudangId, int $barangId)
    {
        // LAYER 2: Permission check
        $this->authorize('tambah-gi');

        // LAYER 3: Branch scope
        $gudang = $this->resolveGudang($gudangId);
        if (!$gudang) {
            return response()->json(['error' => 'Gudang tidak ditemukan atau bukan milik cabang aktif.'], 403);
        }

        // LAYER 4: Validate barang is active
        $barang = MasterBarang::active()->with(['satuan', 'bundleComponents'])->find($barangId);
        if (!$barang) {
            return response()->json(['error' => 'Barang tidak ditemukan atau tidak aktif.'], 404);
        }

        if ($barang->isBundle()) {
            if ($barang->bundleComponents->isEmpty()) {
                $stok = 0;
            } else {
                $maxSets = null;
                foreach ($barang->bundleComponents as $component) {
                    $compStok = StokGudang::where('gudang_id', $gudangId)
                        ->where('barang_id', $component->child_barang_id)
                        ->value('stok_aktual') ?? 0;
                    
                    $qtyRequired = floatval($component->qty);
                    if ($qtyRequired <= 0) continue;
                    
                    $possibleSets = floor(floatval($compStok) / $qtyRequired);
                    if (is_null($maxSets) || $possibleSets < $maxSets) {
                        $maxSets = $possibleSets;
                    }
                }
                $stok = is_null($maxSets) ? 0 : max(0, $maxSets);
            }
        } else {
            $stok = StokGudang::where('gudang_id', $gudangId)
                ->where('barang_id', $barangId)
                ->value('stok_aktual') ?? 0;
        }

        return response()->json([
            'stok_aktual'      => floatval($stok),
            'satuan'           => $barang->satuan ? $barang->satuan->nama_satuan : '-',
            'izinkan_negatif'  => (bool) $gudang->izinkan_stok_negatif,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — Create a new Goods Issue
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        // LAYER 2: Permission check
        $this->authorize('tambah-gi');

        Log::info('GI Store Request: ', $request->all());

        // ── Schema Validation ──────────────────────────────────────────────
        try {
            $request->validate([
                'tanggal_gi'       => 'required|date',
                'gudang_id'        => 'required|integer|exists:master_gudang,id',
                'tujuan'           => 'required|string|max:40',
                'tipe_pengeluaran' => 'required|string|max:150',
                'keterangan'       => 'nullable|string|max:40',
                'auto_number'      => 'required|boolean',
                'nomor_gi'         => 'nullable|required_if:auto_number,0|string|max:50',
                'status'           => 'nullable|in:Draft,Pending,Confirmed',
                'items'            => 'required|array|min:1|max:25',
                'items.*.barang_id'=> 'required|integer|exists:master_barang,id',
                'items.*.jumlah'   => 'required|numeric|min:0.01',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        // ── LAYER 3: Branch scope — verify gudang belongs to active branch ─
        $gudang = $this->resolveGudang((int) $request->gudang_id);
        if (!$gudang) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_id' => ['Gudang tidak ditemukan di cabang aktif Anda!']],
            ], 422);
        }

        // ── Check approval permission if saving as Confirmed ────────────────
        $user = auth()->user();
        if ($request->status === 'Confirmed' && !$user->can('approve-gi') && !$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses approval (approve-gi) untuk langsung mengonfirmasi pengeluaran barang. Silakan ajukan pengeluaran untuk persetujuan atasan.',
            ], 403);
        }

        // ── Validate manual GI number uniqueness (termasuk data deleted) ──
        if (!$request->auto_number) {
            $exists = GoodsIssue::withTrashed()->where('nomor_gi', $request->nomor_gi)->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors'  => ['nomor_gi' => ['Nomor Pengeluaran Barang tersebut sudah terdaftar atau pernah digunakan!']],
                ], 422);
            }
        }

        // ── Check duplicate barang_id items ─────────────────────────────────
        $itemBarangIds = array_column($request->items, 'barang_id');
        if (count($itemBarangIds) !== count(array_unique($itemBarangIds))) {
            return response()->json([
                'message' => 'Terdapat barang yang diinput lebih dari 1 kali! Harap gabungkan jumlah ke dalam satu baris.',
            ], 422);
        }

        $itemsGrouped = [];
        foreach ($request->items as $item) {
            $bid = (int) $item['barang_id'];
            $itemsGrouped[$bid] = floatval($item['jumlah']);
        }

        // ── LAYER 4 + 5: Business logic validation (outside transaction) ───
        // Check every barang is active and belongs to active company
        $activeBarangIds = MasterBarang::active()->forActiveCompany()->pluck('id')->toArray();
        foreach ($itemsGrouped as $barangId => $jumlah) {
            if (!in_array($barangId, $activeBarangIds)) {
                return response()->json([
                    'message' => "Barang ID #{$barangId} tidak ditemukan atau tidak aktif.",
                ], 422);
            }
        }

        // ── Check stock sufficiency if negative stock is disallowed ────────
        if (!$gudang->izinkan_stok_negatif) {
            foreach ($itemsGrouped as $barangId => $jumlah) {
                $barang = MasterBarang::with('bundleComponents.childBarang')->find($barangId);
                if ($barang && $barang->isBundle()) {
                    foreach ($barang->bundleComponents as $component) {
                        $stokGudang = StokGudang::where('barang_id', $component->child_barang_id)
                            ->where('gudang_id', $gudang->id)
                            ->first();
                        $stokAktual = $stokGudang ? floatval($stokGudang->stok_aktual) : 0;
                        $qtyRequired = $jumlah * floatval($component->qty);

                        if ($stokAktual < $qtyRequired) {
                            $compBarang = $component->childBarang;
                            $namaComp = $compBarang ? $compBarang->nama_barang : "ID #{$component->child_barang_id}";
                            return response()->json([
                                'message' => "Stok tidak cukup untuk komponen '{$namaComp}' (bagian dari bundle '{$barang->nama_barang}') di gudang '{$gudang->nama_gudang}'! Stok tersedia: {$stokAktual}, diminta: {$qtyRequired}. Gudang ini tidak mengizinkan stok negatif.",
                            ], 422);
                        }
                    }
                } else {
                    $stokGudang = StokGudang::where('barang_id', $barangId)
                        ->where('gudang_id', $gudang->id)
                        ->first();
                    $stokAktual = $stokGudang ? floatval($stokGudang->stok_aktual) : 0;

                    if ($stokAktual < $jumlah) {
                        $namaBarang = $barang ? $barang->nama_barang : "ID #{$barangId}";
                        return response()->json([
                            'message' => "Stok tidak cukup untuk '{$namaBarang}' di gudang '{$gudang->nama_gudang}'! Stok tersedia: {$stokAktual}, diminta: {$jumlah}. Gudang ini tidak mengizinkan stok negatif.",
                        ], 422);
                    }
                }
            }
        }

        // ── DB Transaction to save Draft ───────────────────────────────────
        try {
            DB::beginTransaction();

            // ── Auto-generate GI number ────────────────────────────────────
            if ($request->auto_number) {
                $year   = date('Y', strtotime($request->tanggal_gi));
                $month  = date('m', strtotime($request->tanggal_gi));
                $prefix = "GI/{$year}/{$month}/";

                $latest = GoodsIssue::withTrashed()
                    ->where('nomor_gi', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->orderByRaw('LENGTH(nomor_gi) DESC, nomor_gi DESC')
                    ->first();

                $number  = !$latest ? 1 : ((int) substr($latest->nomor_gi, strlen($prefix))) + 1;
                $nomorGi = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

                // Anti-collision guarantee (lewati nomor jika sudah pernah ada/terdaftar)
                while (GoodsIssue::withTrashed()->where('nomor_gi', $nomorGi)->exists()) {
                    $number++;
                    $nomorGi = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
                }
            } else {
                $nomorGi = $request->nomor_gi;
            }

            $targetStatus = $request->input('status', 'Draft');
            $initialStatus = in_array($targetStatus, ['Draft', 'Pending']) ? $targetStatus : 'Draft';

            $targetBranchId  = $gudang->cabang_id ?? session('active_branch_id');
            $targetCompanyId = !empty($targetBranchId) ? null : ($gudang->company_id ?? session('active_company_id'));

            // ── Create GI Header ───────────────────────────────────────────
            $gi = GoodsIssue::create([
                'nomor_gi'        => $nomorGi,
                'tanggal_gi'      => $request->tanggal_gi,
                'gudang_id'       => $gudang->id,
                'company_id'      => $targetCompanyId,
                'branch_id'       => $targetBranchId,
                'tujuan'          => strip_tags($request->tujuan),
                'tipe_pengeluaran'=> $request->tipe_pengeluaran,
                'keterangan'      => $request->keterangan ? strip_tags($request->keterangan) : null,
                'status'          => $initialStatus,
            ]);

            // ── Process each item: save detail
            foreach ($itemsGrouped as $barangId => $jumlah) {
                // Save detail line
                GoodsIssueDetail::create([
                    'gi_id'    => $gi->id,
                    'barang_id'=> $barangId,
                    'jumlah'   => $jumlah,
                ]);
            }

            DB::commit();

            if ($targetStatus === 'Confirmed') {
                $confirmResponse = $this->confirm($gi);
                if ($confirmResponse->getStatusCode() !== 200) {
                    $resData = json_decode($confirmResponse->getContent(), true);
                    return response()->json([
                        'message' => $resData['message'] ?? 'Gagal mengonfirmasi pengeluaran barang.',
                    ], $confirmResponse->getStatusCode());
                }

                return response()->json([
                    'status'   => 'success',
                    'message'  => "Pengeluaran barang {$nomorGi} berhasil disimpan dan disetujui!",
                    'nomor_gi' => $nomorGi,
                ]);
            }

            if ($targetStatus === 'Pending') {
                // Trigger WhatsApp Notification for Approvers ONLY on submission (Async)
                $giId = $gi->id;
                WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendGoodsIssueSubmissionNotification($giId));

                return response()->json([
                    'status'  => 'success',
                    'message' => "Pengeluaran barang {$nomorGi} berhasil diajukan untuk persetujuan!",
                    'nomor_gi'=> $nomorGi,
                ]);
            }

            // Draft: Simpan draf lokal (TIDAK kirim WA ke approver)
            return response()->json([
                'status'  => 'success',
                'message' => "Draft pengeluaran barang {$nomorGi} berhasil disimpan!",
                'nomor_gi'=> $nomorGi,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GI Store Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan server. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Check if current authenticated user is Super Admin
     */
    private function isSuperAdminUser(): bool
    {
        return auth()->check() && (
            auth()->user()->hasRole('Super Admin') || 
            auth()->user()->hasRole('Superadmin') || 
            auth()->user()->hasRole('superadmin') ||
            (property_exists(auth()->user(), 'role') && strtolower(strval(auth()->user()->role)) === 'superadmin')
        );
    }

    /**
     * Check if the user is allowed to access/modify a specific Goods Issue.
     * STRICT PRIVACY: Documents with Draft status are strictly accessible ONLY by their creator.
     * No one else (including Super Admin) can view or modify other users' drafts.
     */
    private function canAccessGoodsIssue(GoodsIssue $gi): bool
    {
        if ($gi->status === 'Draft') {
            return auth()->check() && ((int) $gi->created_by === (int) auth()->id());
        }

        return true;
    }

    /**
     * Revert stock deductions and journal posting when a Confirmed GI is edited or deleted by Super Admin.
     */
    private function revertConfirmation(GoodsIssue $gi): void
    {
        $gi->load(['details.barang.bundleComponents', 'gudang']);

        // Revert KartuStok and StokGudang
        $kartuStoks = KartuStok::where('referensi_jenis', 'Goods Issue')
            ->where('referensi_id', $gi->id)
            ->get();

        foreach ($kartuStoks as $ks) {
            $stokGudang = StokGudang::where('barang_id', $ks->barang_id)
                ->where('gudang_id', $ks->gudang_id)
                ->first();

            if ($stokGudang) {
                $qtyStr       = (string) $ks->jumlah;
                $nilaiTotStr  = (string) $ks->nilai_total;
                $stokAwalStr  = (string) $stokGudang->stok_aktual;
                $nilaiLamaStr = (string) $stokGudang->nilai_persediaan;

                $stokBaruStr  = DecimalHelper::add($stokAwalStr, $qtyStr, 2);
                $nilaiBaruStr = DecimalHelper::add($nilaiLamaStr, $nilaiTotStr, 4);

                $stokGudang->stok_aktual      = $stokBaruStr;
                $stokGudang->nilai_persediaan = $nilaiBaruStr;
                if (DecimalHelper::comp($stokBaruStr, '0', 4) > 0) {
                    $stokGudang->harga_pokok_rata_rata = DecimalHelper::div($nilaiBaruStr, $stokBaruStr, 6);
                } else {
                    $stokGudang->harga_pokok_rata_rata = '0.000000';
                }
                $stokGudang->save();

                StokGudang::syncMasterHpp($ks->barang_id);
            }
        }

        // Delete KartuStok entries for this GI
        KartuStok::where('referensi_jenis', 'Goods Issue')
            ->where('referensi_id', $gi->id)
            ->delete();

        // Delete Journal & Details for this GI
        $existingJurnals = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Goods Issue')
            ->where('referensi_id', $gi->id)
            ->get();

        foreach ($existingJurnals as $ej) {
            $ej->details()->delete();
            $ej->forceDelete();
        }
    }

    private function canSeeJournal(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        return $user->hasRole('Super Admin')
            || $user->hasRole('Owner')
            || $user->can('lihat-jurnal-gi')
            || $user->can('akses-accounting');
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW — Detail GI (AJAX for modal)
    // ══════════════════════════════════════════════════════════════
    public function show(GoodsIssue $goodsIssue)
    {
        // LAYER 2: Permission check
        $this->authorize('lihat-gi');

        // LAYER 3: Branch scope
        if (!$this->resolveGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Goods Issue tidak ditemukan.'], 404);
        }

        // LAYER 4: Draft privacy check — Draft only accessible by its creator
        if (!$this->canAccessGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Dokumen draf ini hanya dapat diakses oleh pembuatnya.'], 403);
        }

        $canSeeJournal = $this->canSeeJournal();

        $relations = [
            'gudang',
            'company',
            'branch',
            'creator.karyawan',
            'updater.karyawan',
            'approver.karyawan',
            'details.barang.satuan',
            'details.barang.primaryFoto',
        ];

        if ($canSeeJournal) {
            $relations[] = 'jurnal.details.account';
        }

        $goodsIssue->load($relations);

        return response()->json([
            'gi'              => $goodsIssue,
            'is_super_admin'  => $this->isSuperAdminUser(),
            'is_creator'      => auth()->check() && ((int) $goodsIssue->created_by === (int) auth()->id()),
            'can_see_journal' => $canSeeJournal,
            'can_approve'     => auth()->user() ? (auth()->user()->can('approve-gi') || auth()->user()->hasAnyRole(['Super Admin', 'Owner'])) : false,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // PRINT PDF — Print Goods Issue Document (Blue Theme)
    // ══════════════════════════════════════════════════════════════
    public function printPdf(GoodsIssue $goodsIssue)
    {
        // Permission check
        if (auth()->user() && auth()->user()->can('cetak-gi')) {
            $this->authorize('cetak-gi');
        } else {
            $this->authorize('lihat-gi');
        }

        if (!$this->resolveGoodsIssue($goodsIssue)) {
            abort(404, 'Goods Issue tidak ditemukan.');
        }

        if (!$this->canAccessGoodsIssue($goodsIssue)) {
            abort(403, 'Dokumen draf ini hanya dapat diakses oleh pembuatnya.');
        }

        $goodsIssue->load([
            'company',
            'branch',
            'gudang.cabang',
            'gudang.company',
            'creator.karyawan',
            'creator.activeSignature',
            'updater.karyawan',
            'updater.activeSignature',
            'approver.karyawan',
            'approver.activeSignature',
            'details.barang.satuan',
            'details.barang.kategori',
        ]);

        $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
        $appLogoUrl     = $appLogoSetting ? asset('storage/' . $appLogoSetting) : asset('gambar/logo.png');

        return view('modul_inventori.barang_keluar.printissue', compact('goodsIssue', 'appLogoUrl'));
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE — Edit Goods Issue (Draft or Pending; Confirmed only for Super Admin)
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, GoodsIssue $goodsIssue)
    {
        // LAYER 2: Permission check (allow edit-gi or fallback to tambah-gi)
        if (auth()->user()->can('edit-gi')) {
            $this->authorize('edit-gi');
        } else {
            $this->authorize('tambah-gi');
        }

        // LAYER 3: Branch scope
        if (!$this->resolveGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Goods Issue tidak ditemukan atau bukan milik cabang Anda.'], 404);
        }

        // LAYER 4: Draft privacy check — Draft only modifiable by its creator
        if (!$this->canAccessGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Dokumen draf ini hanya dapat diubah oleh pembuatnya.'], 403);
        }

        $isSuperAdmin = $this->isSuperAdminUser();

        // LAYER 4: Business rule — Draft or Pending can be edited (Confirmed only Super Admin)
        if (!in_array($goodsIssue->status, ['Draft', 'Pending']) && !$isSuperAdmin) {
            return response()->json([
                'message' => 'Dokumen pengeluaran barang yang sudah disetujui (Confirmed) hanya dapat diubah oleh Super Admin.',
            ], 422);
        }

        // Schema validation
        try {
            $request->validate([
                'tanggal_gi'       => 'required|date',
                'gudang_id'        => 'required|integer|exists:master_gudang,id',
                'tujuan'           => 'required|string|max:40',
                'tipe_pengeluaran' => 'required|string|max:150',
                'keterangan'       => 'nullable|string|max:40',
                'status'           => 'nullable|in:Draft,Pending,Confirmed',
                'items'            => 'required|array|min:1|max:25',
                'items.*.barang_id'=> 'required|integer|exists:master_barang,id',
                'items.*.jumlah'   => 'required|numeric|min:0.01',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        $gudang = $this->resolveGudang((int) $request->gudang_id);
        if (!$gudang) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_id' => ['Gudang tidak ditemukan di cabang aktif Anda!']],
            ], 422);
        }

        // ── Check approval permission if saving as Confirmed ────────────────
        $user = auth()->user();
        if ($request->status === 'Confirmed' && !$user->can('approve-gi') && !$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses approval (approve-gi) untuk langsung mengonfirmasi pengeluaran barang.',
            ], 403);
        }

        // ── Check duplicate barang_id items ─────────────────────────────────
        $itemBarangIds = array_column($request->items, 'barang_id');
        if (count($itemBarangIds) !== count(array_unique($itemBarangIds))) {
            return response()->json([
                'message' => 'Terdapat barang yang diinput lebih dari 1 kali! Harap gabungkan jumlah ke dalam satu baris.',
            ], 422);
        }

        $itemsGrouped = [];
        foreach ($request->items as $item) {
            $bid = (int) $item['barang_id'];
            $itemsGrouped[$bid] = floatval($item['jumlah']);
        }

        // Check active barang
        $activeBarangIds = MasterBarang::active()->forActiveCompany()->pluck('id')->toArray();
        foreach ($itemsGrouped as $barangId => $jumlah) {
            if (!in_array($barangId, $activeBarangIds)) {
                return response()->json([
                    'message' => "Barang ID #{$barangId} tidak ditemukan atau tidak aktif.",
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $oldStatus = $goodsIssue->status;

            // If it was Confirmed and edited by Super Admin, revert first
            if ($oldStatus === 'Confirmed') {
                $this->revertConfirmation($goodsIssue);
            }

            $targetStatus = $request->input('status', $oldStatus);
            if ($targetStatus === 'Confirmed' && !$user->can('approve-gi') && !$isSuperAdmin) {
                $targetStatus = 'Pending';
            }

            $targetBranchId  = $gudang->cabang_id ?? session('active_branch_id');
            $targetCompanyId = !empty($targetBranchId) ? null : ($gudang->company_id ?? session('active_company_id'));

            $goodsIssue->update([
                'tanggal_gi'      => $request->tanggal_gi,
                'gudang_id'       => $gudang->id,
                'company_id'      => $targetCompanyId,
                'branch_id'       => $targetBranchId,
                'tujuan'          => strip_tags($request->tujuan),
                'tipe_pengeluaran'=> $request->tipe_pengeluaran,
                'keterangan'      => $request->keterangan ? strip_tags($request->keterangan) : null,
                'status'          => in_array($targetStatus, ['Draft', 'Pending']) ? $targetStatus : 'Draft',
            ]);

            // Replace Details
            $goodsIssue->details()->delete();

            foreach ($itemsGrouped as $barangId => $jumlah) {
                GoodsIssueDetail::create([
                    'gi_id'    => $goodsIssue->id,
                    'barang_id'=> $barangId,
                    'jumlah'   => $jumlah,
                ]);
            }

            DB::commit();

            if ($targetStatus === 'Confirmed') {
                $confirmResponse = $this->confirm($goodsIssue);
                if ($confirmResponse->getStatusCode() !== 200) {
                    $resData = json_decode($confirmResponse->getContent(), true);
                    return response()->json([
                        'message' => $resData['message'] ?? 'Gagal mengonfirmasi pengeluaran barang.',
                    ], $confirmResponse->getStatusCode());
                }

                return response()->json([
                    'status'  => 'success',
                    'message' => "Pengeluaran barang {$goodsIssue->nomor_gi} berhasil diperbarui dan disetujui!",
                ]);
            }

            if ($targetStatus === 'Pending' && $oldStatus === 'Draft') {
                // Trigger WA notification to approvers if submitted from Draft to Pending
                $giId = $goodsIssue->id;
                WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendGoodsIssueSubmissionNotification($giId));
            }

            $statusMsg = $targetStatus === 'Pending' ? 'diajukan untuk persetujuan' : 'disimpan sebagai draf';

            return response()->json([
                'status'  => 'success',
                'message' => "Pengeluaran barang {$goodsIssue->nomor_gi} berhasil diperbarui dan {$statusMsg}!",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GI Update Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan server. Silakan coba lagi.',
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // SUBMIT — Submit Draft GI for Approval (Draft -> Pending)
    // ══════════════════════════════════════════════════════════════
    public function submit(GoodsIssue $goodsIssue)
    {
        // LAYER 2: Permission check
        if (!auth()->user()->can('tambah-gi') && !auth()->user()->can('edit-gi')) {
            $this->authorize('tambah-gi');
        }

        // LAYER 3: Branch scope
        if (!$this->resolveGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Goods Issue tidak ditemukan.'], 404);
        }

        // LAYER 4: Draft privacy check — Draft only submittable by its creator
        if (!$this->canAccessGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Dokumen draf ini hanya dapat diajukan oleh pembuatnya.'], 403);
        }

        if ($goodsIssue->status !== 'Draft') {
            return response()->json(['message' => 'Hanya dokumen berstatus Draft yang dapat diajukan untuk persetujuan.'], 422);
        }

        $goodsIssue->update(['status' => 'Pending']);

        // Trigger WhatsApp Notification for Approvers (Async)
        $giId = $goodsIssue->id;
        WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendGoodsIssueSubmissionNotification($giId));

        return response()->json([
            'status'  => 'success',
            'message' => "Pengeluaran barang {$goodsIssue->nomor_gi} berhasil diajukan! Notifikasi persetujuan telah dikirim ke atasan.",
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // REJECT — Return Pending GI back to Draft (Pending -> Draft)
    // ══════════════════════════════════════════════════════════════
    public function reject(GoodsIssue $goodsIssue)
    {
        $user = auth()->user();
        if (!$user->can('approve-gi') && !$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses approval (approve-gi) untuk menolak pengeluaran barang.',
            ], 403);
        }

        if (!$this->resolveGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Goods Issue tidak ditemukan.'], 404);
        }

        if ($goodsIssue->status !== 'Pending') {
            return response()->json(['message' => 'Hanya dokumen berstatus Menunggu Persetujuan (Pending) yang dapat dikembalikan ke Draft.'], 422);
        }

        $goodsIssue->update([
            'status'      => 'Draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Pengeluaran barang {$goodsIssue->nomor_gi} berhasil dikembalikan ke status Draft untuk direvisi.",
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY — Soft delete
    // Only allowed if status = 'Draft' (Pending cannot be deleted; Confirmed only for Super Admin)
    // ══════════════════════════════════════════════════════════════
    public function destroy(GoodsIssue $goodsIssue)
    {
        // LAYER 2: Permission check
        $this->authorize('hapus-gi');

        // LAYER 3: Branch scope
        if (!$this->resolveGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Goods Issue tidak ditemukan atau bukan milik cabang Anda.'], 404);
        }

        // LAYER 4: Draft privacy check — Draft only deletable by its creator
        if (!$this->canAccessGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Dokumen draf ini hanya dapat dihapus oleh pembuatnya.'], 403);
        }

        $isSuperAdmin = $this->isSuperAdminUser();

        // Business rule — only Draft can be deleted unless Super Admin
        if (!$goodsIssue->isDraft() && !$isSuperAdmin) {
            if ($goodsIssue->status === 'Pending') {
                return response()->json([
                    'message' => 'Pengeluaran barang yang sedang Menunggu Persetujuan (Pending) tidak dapat dihapus. Silakan kembalikan ke Draft terlebih dahulu jika ingin membatalkan.',
                ], 422);
            }
            return response()->json([
                'message' => 'Pengeluaran barang yang sudah disetujui (Confirmed) hanya dapat dihapus oleh Super Admin.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            if ($goodsIssue->status === 'Confirmed') {
                $this->revertConfirmation($goodsIssue);
            }

            $goodsIssue->details()->delete();
            $goodsIssue->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Pengeluaran barang {$goodsIssue->nomor_gi} berhasil dihapus.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GI Destroy Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan server. Silakan coba lagi.',
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // CONFIRM — Confirm / Approve GI (locks it from deletion & mutates stock & posts journal)
    // ══════════════════════════════════════════════════════════════
    public function confirm(GoodsIssue $goodsIssue)
    {
        // LAYER 2: Permission check
        $user = auth()->user();
        if (!$user->can('approve-gi') && !$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses approval (approve-gi) untuk menyetujui/mengonfirmasi pengeluaran barang.',
            ], 403);
        }

        // LAYER 3: Branch scope
        if (!$this->resolveGoodsIssue($goodsIssue)) {
            return response()->json(['message' => 'Goods Issue tidak ditemukan.'], 404);
        }

        // LAYER 4: Business rule
        if ($goodsIssue->isConfirmed()) {
            return response()->json(['message' => 'Dokumen ini sudah berstatus Confirmed / Disetujui.'], 422);
        }

        try {
            DB::beginTransaction();

            $goodsIssue->load(['details', 'gudang']);
            $gudang = $goodsIssue->gudang;

            // Step 1: Pessimistic locking & stock sufficiency check
            foreach ($goodsIssue->details as $detail) {
                $detail->load('barang.bundleComponents.childBarang');
                $barang = $detail->barang;

                if ($barang && $barang->isBundle()) {
                    foreach ($barang->bundleComponents as $component) {
                        $stokGudang = StokGudang::where('barang_id', $component->child_barang_id)
                            ->where('gudang_id', $goodsIssue->gudang_id)
                            ->lockForUpdate()
                            ->first();

                        $stokAktual = $stokGudang ? floatval($stokGudang->stok_aktual) : 0;
                        $qtyRequired = floatval($detail->jumlah) * floatval($component->qty);

                        // Check stok negatif policy
                        if (!$gudang->izinkan_stok_negatif && $stokAktual < $qtyRequired) {
                            DB::rollBack();
                            $compBarang = $component->childBarang;
                            $namaComp = $compBarang ? $compBarang->nama_barang : "ID #{$component->child_barang_id}";
                            return response()->json([
                                'message' => "Stok tidak cukup untuk komponen '{$namaComp}' (bagian dari bundle '{$barang->nama_barang}') di gudang '{$gudang->nama_gudang}'! "
                                    . "Stok tersedia: {$stokAktual}, diminta: {$qtyRequired}. "
                                    . "Gudang ini tidak mengizinkan stok negatif.",
                            ], 422);
                        }
                    }
                } else {
                    $stokGudang = StokGudang::where('barang_id', $detail->barang_id)
                        ->where('gudang_id', $goodsIssue->gudang_id)
                        ->lockForUpdate()
                        ->first();

                    $stokAktual = $stokGudang ? floatval($stokGudang->stok_aktual) : 0;

                    // Check stok negatif policy
                    if (!$gudang->izinkan_stok_negatif && $stokAktual < $detail->jumlah) {
                        DB::rollBack();
                        $namaBarang = $barang ? $barang->nama_barang : "ID #{$detail->barang_id}";
                        return response()->json([
                            'message' => "Stok tidak cukup untuk '{$namaBarang}' di gudang '{$gudang->nama_gudang}'! "
                                . "Stok tersedia: {$stokAktual}, diminta: {$detail->jumlah}. "
                                . "Gudang ini tidak mengizinkan stok negatif.",
                        ], 422);
                    }
                }
            }

            // Step 2: Update inventory & write to kartu stok
            $totalNilaiKeluar = 0.00;
            $detailValues     = [];

            foreach ($goodsIssue->details as $detail) {
                $detail->load('barang');
                $barang = $detail->barang;

                if ($barang && $barang->isBundle()) {
                    foreach ($barang->bundleComponents as $component) {
                        $stokGudang = StokGudang::firstOrCreate(
                            ['barang_id' => $component->child_barang_id, 'gudang_id' => $goodsIssue->gudang_id],
                            ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
                        );

                        $qtyRequiredStr = DecimalHelper::mul((string)$detail->jumlah, (string)$component->qty, 4);
                        $stokAwalStr    = (string)($stokGudang->stok_aktual ?? '0');
                        $nilaiLamaStr   = (string)($stokGudang->nilai_persediaan ?? DecimalHelper::mul($stokAwalStr, (string)($stokGudang->harga_pokok_rata_rata ?? '0'), 4));

                        $hppStr = (string)($stokGudang->harga_pokok_rata_rata ?? '0.000000');
                        if (DecimalHelper::comp($hppStr, '0', 6) === 0 && DecimalHelper::comp($stokAwalStr, '0', 4) > 0) {
                            $hppStr = DecimalHelper::div($nilaiLamaStr, $stokAwalStr, 6);
                        }
                        $compBarang = $component->childBarang;
                        if (DecimalHelper::comp($hppStr, '0', 6) === 0 && $compBarang) {
                            $hppStr = (string)($compBarang->harga_pokok_rata_rata ?? $compBarang->harga_pokok ?? $compBarang->harga_beli ?? '0.000000');
                        }

                        $nilaiKeluarStr        = DecimalHelper::mul($qtyRequiredStr, $hppStr, 4);
                        $nilaiPersediaanBaruStr= max('0.0000', DecimalHelper::sub($nilaiLamaStr, $nilaiKeluarStr, 4));
                        $stokAkhirStr          = max('0.00', DecimalHelper::sub($stokAwalStr, $qtyRequiredStr, 2));

                        $stokGudang->stok_aktual      = $stokAkhirStr;
                        $stokGudang->nilai_persediaan = $nilaiPersediaanBaruStr;
                        $stokGudang->harga_pokok_rata_rata = DecimalHelper::comp($hppStr, '0', 6) > 0 ? $hppStr : (string)($stokGudang->harga_pokok_rata_rata ?? '0.000000');
                        $stokGudang->save();

                        StokGudang::syncMasterHpp($component->child_barang_id);

                        $totalNilaiKeluar += floatval($nilaiKeluarStr);
                        $detailValues[] = [
                            'kategori_id' => $compBarang ? $compBarang->kategori_barang_id : null,
                            'nama'        => $compBarang ? $compBarang->nama_barang : "Komponen #{$component->child_barang_id}",
                            'nilai'       => floatval($nilaiKeluarStr),
                        ];

                        // Write to kartu stok for component
                        KartuStok::create([
                            'barang_id'       => $component->child_barang_id,
                            'gudang_id'       => $goodsIssue->gudang_id,
                            'tipe'            => 'Keluar',
                            'jumlah'          => $qtyRequiredStr,
                            'stok_awal'       => $stokAwalStr,
                            'stok_akhir'      => $stokAkhirStr,
                            'harga_satuan'    => $hppStr,
                            'nilai_total'     => $nilaiKeluarStr,
                            'referensi_jenis' => 'Goods Issue',
                            'referensi_id'    => $goodsIssue->id,
                            'referensi_nomor' => $goodsIssue->nomor_gi,
                            'keterangan'      => "Komponen dari Set '{$barang->nama_barang}' - {$goodsIssue->tipe_pengeluaran} ke {$goodsIssue->tujuan}",
                        ]);
                    }
                } else {
                    $stokGudang = StokGudang::firstOrCreate(
                        ['barang_id' => $detail->barang_id, 'gudang_id' => $goodsIssue->gudang_id],
                        ['stok_aktual' => 0.00, 'nilai_persediaan' => 0.0000, 'harga_pokok_rata_rata' => 0.000000]
                    );

                    $qtyIssueStr  = (string)$detail->jumlah;
                    $stokAwalStr  = (string)($stokGudang->stok_aktual ?? '0');
                    $nilaiLamaStr = (string)($stokGudang->nilai_persediaan ?? DecimalHelper::mul($stokAwalStr, (string)($stokGudang->harga_pokok_rata_rata ?? '0'), 4));

                    $hppStr = (string)($stokGudang->harga_pokok_rata_rata ?? '0.000000');
                    if (DecimalHelper::comp($hppStr, '0', 6) === 0 && DecimalHelper::comp($stokAwalStr, '0', 4) > 0) {
                        $hppStr = DecimalHelper::div($nilaiLamaStr, $stokAwalStr, 6);
                    }
                    if (DecimalHelper::comp($hppStr, '0', 6) === 0 && $barang) {
                        $hppStr = (string)($barang->harga_pokok_rata_rata ?? $barang->harga_pokok ?? $barang->harga_beli ?? '0.000000');
                    }

                    $nilaiKeluarStr         = DecimalHelper::mul($qtyIssueStr, $hppStr, 4);
                    $nilaiPersediaanBaruStr = max('0.0000', DecimalHelper::sub($nilaiLamaStr, $nilaiKeluarStr, 4));
                    $stokAkhirStr           = max('0.00', DecimalHelper::sub($stokAwalStr, $qtyIssueStr, 2));

                        $stokGudang->stok_aktual      = $stokAkhirStr;
                        $stokGudang->nilai_persediaan = $nilaiPersediaanBaruStr;
                        $stokGudang->harga_pokok_rata_rata = DecimalHelper::comp($hppStr, '0', 6) > 0 ? $hppStr : (string)($stokGudang->harga_pokok_rata_rata ?? '0.000000');
                        $stokGudang->save();

                    StokGudang::syncMasterHpp($detail->barang_id);

                    $totalNilaiKeluar += floatval($nilaiKeluarStr);
                    $detailValues[] = [
                        'kategori_id' => $barang ? $barang->kategori_barang_id : null,
                        'nama'        => $barang ? $barang->nama_barang : "Barang #{$detail->barang_id}",
                        'jumlah'      => floatval($qtyIssueStr),
                        'nilai'       => floatval($nilaiKeluarStr),
                    ];

                    // Write to kartu stok
                    KartuStok::create([
                        'barang_id'       => $detail->barang_id,
                        'gudang_id'       => $goodsIssue->gudang_id,
                        'tipe'            => 'Keluar',
                        'jumlah'          => $qtyIssueStr,
                        'stok_awal'       => $stokAwalStr,
                        'stok_akhir'      => $stokAkhirStr,
                        'harga_satuan'    => $hppStr,
                        'nilai_total'     => $nilaiKeluarStr,
                        'referensi_jenis' => 'Goods Issue',
                        'referensi_id'    => $goodsIssue->id,
                        'referensi_nomor' => $goodsIssue->nomor_gi,
                        'keterangan'      => "Pengeluaran Barang - {$goodsIssue->tipe_pengeluaran} ke {$goodsIssue->tujuan}",
                    ]);
                }
            }

            // Post Automatic Accounting Journal
            $this->postJournalForGoodsIssue($goodsIssue, $totalNilaiKeluar, $detailValues);

            $goodsIssue->update([
                'status'      => 'Confirmed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            // Trigger WhatsApp Notification for Confirmed GI (Async)
            $giId = $goodsIssue->id;
            WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendGoodsIssueConfirmedNotification($giId));

            return response()->json([
                'status'  => 'success',
                'message' => "GI {$goodsIssue->nomor_gi} berhasil dikonfirmasi dan stok telah dikurangi.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GI Confirm Exception: ', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan server saat konfirmasi.',
            ], 500);
        }
    }

    /**
     * Post automatic Accounting Journal (Jurnal & JurnalDetail) when Goods Issue is Confirmed
     */
    private function postJournalForGoodsIssue(GoodsIssue $gi, float $totalNilaiKeluar, array $detailValues)
    {
        // Force delete existing journal entries if re-posting to avoid orphaned details
        $existingJurnals = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Goods Issue')
            ->where('referensi_id', $gi->id)
            ->get();

        foreach ($existingJurnals as $ej) {
            $ej->details()->delete();
            $ej->forceDelete();
        }

        if ($totalNilaiKeluar <= 0) return;

        $rawBranchId = $gi->branch_id !== null ? $gi->branch_id : ($gi->gudang ? $gi->gudang->cabang_id : session('active_branch_id'));
        $isBranch    = !empty($rawBranchId);

        // Strict isolation: Cabang (company_id = NULL, branch_id = ID Cabang), PT (company_id = ID PT, branch_id = NULL)
        $jurnalCompanyId = $isBranch ? null : ($gi->company_id ?: (session('active_company_id') ?? auth()->user()?->company_id));
        $jurnalBranchId  = $isBranch ? $rawBranchId : null;

        // ID PT untuk resolusi akun COA & Mapping (mengacu ke PT induk jika cabang)
        $coaCompanyId = $gi->company_id ?: (session('active_company_id') ?? auth()->user()?->company_id ?? ($isBranch ? \App\Models\Branch::where('id', $jurnalBranchId)->value('company_id') : 1));

        // Auto-generate Jurnal Number JNL/YYYY/MM/XXXX
        $year   = date('Y', strtotime($gi->tanggal_gi));
        $month  = date('m', strtotime($gi->tanggal_gi));
        $prefix = "JNL/{$year}/{$month}/";

        $latestJnlQuery = Jurnal::withTrashed()
            ->where('nomor_jurnal', 'like', $prefix . '%');

        if ($isBranch) {
            $latestJnlQuery->where('branch_id', $jurnalBranchId);
        } else {
            $latestJnlQuery->where('company_id', $jurnalCompanyId)->whereNull('branch_id');
        }

        $latestJnl = $latestJnlQuery->orderByDesc('id')->first();

        $nextNum = 1;
        if ($latestJnl && preg_match('/(\d+)$/', $latestJnl->nomor_jurnal, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }
        $nomorJurnal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        while (Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists()) {
            $nextNum++;
            $nomorJurnal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        $tipeNama = trim($gi->tipe_pengeluaran);
        $slug     = strtolower(str_replace(' ', '_', $tipeNama));

        // Determine Debit COA ID based on Master Tipe Pengeluaran mapping / Fallback System Mapping
        $debitCoaId = null;
        $masterTipe = MasterTipePengeluaran::where('company_id', $coaCompanyId)
            ->where(DB::raw('LOWER(nama_tipe)'), strtolower($tipeNama))
            ->first();
        if ($masterTipe && $masterTipe->coa_id) {
            $debitCoaId = $masterTipe->coa_id;
        }

        if (!$debitCoaId) {
            $debitCoaId = AccountMappingHelper::getCoaId('goods_issue', "gi_type_{$slug}_debit", null, $coaCompanyId) 
                       ?? AccountMappingHelper::getCoaId('goods_issue', "gi_type_{$slug}", null, $coaCompanyId);
        }

        if (!$debitCoaId) {
            // Fallback COA mapping lookup
            if (strcasecmp($tipeNama, 'Internal') === 0) {
                $debitCoaId = AccountMappingHelper::getCoaId('default', 'gi_type_internal', 'default_cogs', $coaCompanyId);
            } elseif (strcasecmp($tipeNama, 'Retur Supplier') === 0) {
                $debitCoaId = AccountMappingHelper::getCoaId('default', 'gi_type_retur_supplier', 'unbilled_ap', $coaCompanyId);
            } elseif (strcasecmp($tipeNama, 'Penyesuaian') === 0) {
                $debitCoaId = AccountMappingHelper::getCoaId('default', 'gi_type_penyesuaian', 'inventory_adjustment', $coaCompanyId);
            } else {
                $debitCoaId = AccountMappingHelper::getCoaId('default', 'gi_type_lainnya', 'default_cogs', $coaCompanyId);
            }
        }

        // Default Debit COA fallback to any active Expense/HPP account if mapping not configured
        if (!$debitCoaId) {
            $debitCoaId = ChartOfAccount::where('company_id', $coaCompanyId)
                ->whereIn('type', ['Beban', 'HPP', 'Expense', 'Cost of Goods Sold'])
                ->where('is_header', false)
                ->value('id');
        }

        // Determine Credit COA ID — fallback chain
        $defaultCreditCoaId = AccountMappingHelper::getCoaId('goods_issue', "gi_type_{$slug}_credit", null, $coaCompanyId);
        if (!$defaultCreditCoaId) {
            $defaultCreditCoaId = AccountMappingHelper::getCoaId('goods_issue', 'gi_credit_inventory', null, $coaCompanyId);
        }
        if (!$defaultCreditCoaId) {
            $defaultCreditCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory', null, $coaCompanyId);
        }
        if (!$defaultCreditCoaId) {
            $defaultCreditCoaId = ChartOfAccount::where('company_id', $coaCompanyId)
                ->whereIn('type', ['Aset', 'Asset', 'Persediaan', 'Inventory'])
                ->where('is_header', false)
                ->value('id');
        }

        if (!$debitCoaId || !$defaultCreditCoaId) {
            Log::warning("GoodsIssue Journal skipped: COA mapping incomplete for company {$coaCompanyId}");
            return;
        }

        // Create Jurnal Header
        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $gi->tanggal_gi,
            'referensi_tipe' => 'Goods Issue',
            'referensi_id'   => $gi->id,
            'company_id'     => $jurnalCompanyId,
            'branch_id'      => $jurnalBranchId,
            'total_debit'    => $totalNilaiKeluar,
            'total_kredit'   => $totalNilaiKeluar,
            'keterangan'     => "Jurnal Pengeluaran Barang {$gi->nomor_gi} ({$gi->tipe_pengeluaran}) - {$gi->tujuan}",
            'created_by'     => auth()->id(),
        ]);

        // Post 1 Consolidated DEBIT entry (1 Baris Akun Beban Pengeluaran)
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $debitCoaId,
            'debit'      => $totalNilaiKeluar,
            'kredit'     => 0,
            'keterangan' => "Pengeluaran Barang {$gi->nomor_gi} ({$gi->tipe_pengeluaran})",
        ]);

        // Post KREDIT entries (Consolidated per COA Account for clean accounting presentation)
        $groupedCreditEntries = [];
        foreach ($detailValues as $itemVal) {
            $val = floatval($itemVal['nilai']);
            if ($val <= 0) continue;

            $itemName = $itemVal['nama'] ?? 'Barang';
            $qtyStr   = isset($itemVal['jumlah']) && $itemVal['jumlah'] > 0 ? " (" . floatval($itemVal['jumlah']) . " Pcs)" : '';
            $itemKeterangan = "{$itemName}{$qtyStr}";

            // Priority 1: Category specific inventory COA
            $catId = $itemVal['kategori_id'] ?? null;
            $itemCreditCoaId = $catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory", null, $coaCompanyId) : null;

            // Priority 2: System default inventory COA
            if (!$itemCreditCoaId) {
                $itemCreditCoaId = $defaultCreditCoaId;
            }

            if (!isset($groupedCreditEntries[$itemCreditCoaId])) {
                $groupedCreditEntries[$itemCreditCoaId] = [
                    'kredit' => 0.0,
                    'keterangan_list' => []
                ];
            }
            $groupedCreditEntries[$itemCreditCoaId]['kredit'] += $val;
            $groupedCreditEntries[$itemCreditCoaId]['keterangan_list'][] = $itemKeterangan;
        }

        foreach ($groupedCreditEntries as $coaId => $entry) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $coaId,
                'debit'      => 0,
                'kredit'     => $entry['kredit'],
                'keterangan' => implode(', ', $entry['keterangan_list']),
            ]);
        }
    }
}
