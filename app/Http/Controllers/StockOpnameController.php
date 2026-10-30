<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\ChartOfAccount;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\MasterKategoriBarang;
use App\Models\SpkPenerimaanBarangJadiSerial;
use App\Helpers\DecimalHelper;
use App\Helpers\AccountMappingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockOpnameController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // LAYER 1: PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

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

        if (auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))) {
            return $gudang;
        }

        if (!empty($activeBranchId) && !empty($gudang->cabang_id) && $gudang->cabang_id != $activeBranchId) {
            return null;
        }

        return $gudang;
    }

    private function resolveStockOpname(StockOpname $so): bool
    {
        // Strict Privacy: Status Draft HANYA BISA DIAKSES OLEH PEMBUATNYA (created_by)
        if ($so->status === 'Draft' && (int)$so->created_by !== (int)auth()->id()) {
            return false;
        }

        if (auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))) {
            return true;
        }

        $gudang = MasterGudang::find($so->gudang_id);
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

    private function canSeeNilai(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        return $user->hasRole('Super Admin')
            || $user->can('lihat-nilai-opname');
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX — Proposals List
    // ══════════════════════════════════════════════════════════════
    public function index()
    {
        $this->authorize('lihat-opname');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        $activeGudangsQuery = MasterGudang::where('status', 'Active')->forActiveCompany();
        if (!empty($activeBranchId)) {
            $activeGudangsQuery->where('cabang_id', $activeBranchId);
        } else {
            $activeGudangsQuery->whereNull('cabang_id');
        }
        $activeGudangIds = $activeGudangsQuery->pluck('id');

        // Auto-activate Approved opnames if today is within their planned period
        $today = now()->format('Y-m-d');
        $approvedOpnames = StockOpname::whereIn('gudang_id', $activeGudangIds)
            ->where('status', 'Approved')
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->get();

        foreach ($approvedOpnames as $so) {
            $so->status = 'Sedang Berjalan';
            $so->save();
        }

        // Fetch list of opnames
        $userId = auth()->id();
        $opnamesQuery = StockOpname::forActiveCompany();
        if (!empty($activeBranchId)) {
            $opnamesQuery->where('branch_id', $activeBranchId);
        } else {
            $opnamesQuery->whereNull('branch_id');
        }

        // Strict Privacy: Draft hanya tampil untuk user yang membuatnya
        $opnamesQuery->where(function ($q) use ($userId) {
            $q->where('status', '!=', 'Draft')
              ->orWhere('created_by', $userId);
        });

        $opnames = $opnamesQuery->whereIn('gudang_id', $activeGudangIds)
            ->with(['gudang', 'creator', 'approver', 'closer'])
            ->orderBy('tanggal_opname', 'desc')
            ->orderBy('id', 'desc')
            ->take(200)
            ->get();

        $gudangs = MasterGudang::whereIn('id', $activeGudangIds)
            ->orderBy('nama_gudang')
            ->get();

        $kategoris = MasterKategoriBarang::where('status', 'Active')
            ->forActiveCompany()
            ->orderBy('nama_kategori')
            ->get();

        $barangs = collect();
        $barangsData = collect();

        return view('modul_inventori.stok_opname.viewopname', compact('opnames', 'gudangs', 'kategoris', 'barangs', 'barangsData'));
    }

    public function searchBarang(Request $request)
    {
        if (!auth()->user()->can('tambah-opname') && !auth()->user()->can('input-fisik-opname') && !auth()->user()->can('lihat-opname')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $search     = trim($request->get('q', ''));
        $gudangId   = $request->get('gudang_id');
        $kategoriId = $request->get('kategori_id');
        $lookupFull = $request->boolean('lookup_full');

        $query = MasterBarang::active()
            ->forActiveCompany()
            ->with(['satuan', 'kategori']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if (!empty($kategoriId)) {
            $query->where('kategori_barang_id', (int) $kategoriId);
        }

        // Filter ONLY items registered in the selected warehouse stock records
        if (!empty($gudangId)) {
            $gudangBarangIds = StokGudang::where('gudang_id', (int) $gudangId)->pluck('barang_id')->toArray();
            $query->whereIn('id', $gudangBarangIds);
        }

        $barangs = $query->limit($lookupFull ? 200 : 50)->get();

        if ($lookupFull) {
            $stokMap = [];
            if (!empty($gudangId)) {
                $stokMap = StokGudang::where('gudang_id', (int) $gudangId)
                    ->whereIn('barang_id', $barangs->pluck('id'))
                    ->pluck('stok_aktual', 'barang_id')
                    ->toArray();
            }

            return response()->json($barangs->map(function ($b) use ($stokMap) {
                return [
                    'id'          => $b->id,
                    'kode_barang' => $b->kode_barang,
                    'nama_barang' => $b->nama_barang,
                    'kategori'    => $b->kategori->nama_kategori ?? '-',
                    'satuan'      => $b->satuan->nama_satuan ?? 'PCS',
                    'stok'        => isset($stokMap[$b->id]) ? floatval($stokMap[$b->id]) : 0,
                ];
            }));
        }

        return response()->json($barangs->map(function ($b) {
            return [
                'id'   => $b->id,
                'text' => $b->kode_barang . ' - ' . $b->nama_barang,
            ];
        }));
    }

    // ══════════════════════════════════════════════════════════════
    // STORE PROPOSAL (DRAFT PLAN)
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $this->authorize('tambah-opname');

        $request->validate([
            'tanggal_opname'  => 'required|date',
            'tanggal_mulai'   => 'required|date|after_or_equal:tanggal_opname',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'gudang_id'       => 'required|integer',
            'jenis_opname'    => 'required|in:Full Stock Opname,Partial Stock Opname',
            'keterangan'      => 'required|string|max:40',
        ], [
            'keterangan.max'  => 'Alasan stock opname maksimal 40 karakter!',
        ]);

        $gudang = $this->resolveGudang((int) $request->gudang_id);
        if (!$gudang) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_id' => ['Gudang tidak ditemukan di cabang aktif Anda!']],
            ], 422);
        }

        // 🛑 COOLDOWN RULE: Pembatasan pengajuan Stock Opname minimal 20 hari sekali per Gudang & PT & Cabang
        $minIntervalDays = 20;
        $lastOpnameQuery = StockOpname::where('gudang_id', $gudang->id)
            ->whereNotIn('status', ['Rejected']);

        if (!empty($gudang->cabang_id)) {
            $lastOpnameQuery->where('branch_id', $gudang->cabang_id);
        } else {
            $lastOpnameQuery->whereNull('branch_id');
            if (!empty($gudang->company_id)) {
                $lastOpnameQuery->where('company_id', $gudang->company_id);
            }
        }

        $lastOpname = $lastOpnameQuery->orderBy('tanggal_opname', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOpname) {
            $lastDate = \Carbon\Carbon::parse($lastOpname->tanggal_opname)->startOfDay();
            $newDate  = \Carbon\Carbon::parse($request->tanggal_opname)->startOfDay();
            $diffDays = $lastDate->diffInDays($newDate, false);

            if ($diffDays < $minIntervalDays) {
                $nextAllowedDate = $lastDate->copy()->addDays($minIntervalDays)->format('d/m/Y');
                $remainingDays   = $minIntervalDays - max(0, $diffDays);
                return response()->json([
                    'message' => 'Batasi Frekuensi Pengajuan!',
                    'errors'  => [
                        'tanggal_opname' => [
                            "Pengajuan Stock Opname untuk gudang ini dibatasi minimal <strong>{$minIntervalDays} hari sekali</strong>.<br><br>" .
                            "📋 Pengajuan Terakhir: <strong>{$lastDate->format('d/m/Y')}</strong> (Dokumen: <span class='badge bg-secondary'>{$lastOpname->nomor_opname}</span>)<br>" .
                            "📅 Boleh Mengajukan Lagi: <strong class='text-success'>{$nextAllowedDate}</strong> (<span class='badge bg-warning text-dark'>{$remainingDays} hari lagi</span>)."
                        ]
                    ]
                ], 422);
            }
        }

        // Validate manual SO number uniqueness and length max 25
        if (!$request->auto_number) {
            $request->validate([
                'nomor_opname' => 'required|string|max:25|unique:stock_opnames,nomor_opname',
            ], [
                'nomor_opname.max' => 'Nomor pengajuan maksimal 25 karakter!',
            ]);
        }

        // Determine item list to count based on Full/Partial selection
        $barangIds = [];

        if ($request->jenis_opname === 'Full Stock Opname') {
            // Load only items that have stock records in this warehouse
            $barangIds = StokGudang::where('gudang_id', $gudang->id)
                ->pluck('barang_id')
                ->toArray();
        } else {
            // Partial: Combinable filtering
            $query = MasterBarang::active()->forActiveCompany();
            $hasFilter = false;

            if ($request->has('filter_barang_ids') && is_array($request->filter_barang_ids) && count($request->filter_barang_ids) > 0) {
                $requestedIds = array_map('intval', array_filter($request->filter_barang_ids));
                if (count($requestedIds) > 0) {
                    $query->whereIn('id', $requestedIds);
                    $hasFilter = true;
                }
            }

            if ($request->filter_kategori_id) {
                $query->where('kategori_barang_id', (int) $request->filter_kategori_id);
                $hasFilter = true;
            }

            if (!$hasFilter) {
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors'  => ['jenis_opname' => ['Untuk Partial Stock Opname, pilih minimal Kategori Barang atau isi daftar Barang Tertentu!']],
                ], 422);
            }

            // Must exist in this warehouse's stock list
            $gudangBarangIds = StokGudang::where('gudang_id', $gudang->id)->pluck('barang_id')->toArray();
            $query->whereIn('id', $gudangBarangIds);

            $barangIds = $query->pluck('id')->toArray();
        }

        if (count($barangIds) === 0) {
            return response()->json([
                'message' => 'Tidak ditemukan barang aktif untuk rencana Stock Opname ini.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->auto_number) {
                $year   = date('Y', strtotime($request->tanggal_opname));
                $month  = date('m', strtotime($request->tanggal_opname));
                $prefix = "SO/{$year}/{$month}/";

                $latest = StockOpname::withTrashed()
                    ->where('nomor_opname', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->orderBy('nomor_opname', 'desc')
                    ->first();

                $number  = !$latest ? 1 : ((int) substr($latest->nomor_opname, strlen($prefix))) + 1;
                $nomorOpname = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            } else {
                $nomorOpname = $request->nomor_opname;
            }

            // Create Proposal (Draft state or direct Submission to approval)
            $initialStatus = $request->submit_directly ? 'Menunggu Persetujuan' : 'Draft';
            $targetBranchId  = $gudang->cabang_id ?? session('active_branch_id');
            $targetCompanyId = !empty($targetBranchId) ? null : ($gudang->company_id ?? session('active_company_id'));

            $so = StockOpname::create([
                'nomor_opname'    => $nomorOpname,
                'tanggal_opname'  => $request->tanggal_opname,
                'tanggal_mulai'   => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'gudang_id'       => $gudang->id,
                'company_id'      => $targetCompanyId,
                'branch_id'       => $targetBranchId,
                'jenis_opname'    => $request->jenis_opname,
                'keterangan'      => strip_tags($request->keterangan),
                'status'          => $initialStatus,
            ]);

            // Save details in bulk
            $stokGudangList = StokGudang::where('gudang_id', $gudang->id)
                ->pluck('stok_aktual', 'barang_id')
                ->toArray();

            $detailsData = [];
            $now = now();
            foreach ($barangIds as $barangId) {
                $stokSistem = isset($stokGudangList[$barangId]) ? floatval($stokGudangList[$barangId]) : 0.00;
                $detailsData[] = [
                    'opname_id'   => $so->id,
                    'barang_id'   => $barangId,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik'  => null,
                    'selisih'     => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            foreach (array_chunk($detailsData, 500) as $chunk) {
                StockOpnameDetail::insert($chunk);
            }

            DB::commit();

            if ($request->submit_directly) {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendStockOpnameSubmissionNotification($so)
                );
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Proposal Rencana Stock Opname berhasil dibuat.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("StockOpnameStoreError: " . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan sistem.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // EDIT PROPOSAL (DRAFT PLAN)
    // ══════════════════════════════════════════════════════════════
    public function edit(StockOpname $stockOpname)
    {
        $this->authorize('tambah-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak! Dokumen tidak ditemukan di PT/Cabang Anda.'], 403);
        }

        if (!in_array($stockOpname->status, ['Draft', 'Rejected'])) {
            return response()->json(['message' => 'Hanya proposal berstatus Draft atau Ditolak (Rejected) yang dapat diubah.'], 422);
        }

        $selectedBarangs = [];
        if ($stockOpname->jenis_opname === 'Partial Stock Opname') {
            $selectedBarangIds = StockOpnameDetail::where('opname_id', $stockOpname->id)
                ->pluck('barang_id')
                ->toArray();

            if (count($selectedBarangIds) > 0) {
                $stokMap = StokGudang::where('gudang_id', $stockOpname->gudang_id)
                    ->whereIn('barang_id', $selectedBarangIds)
                    ->pluck('stok_aktual', 'barang_id')
                    ->toArray();

                $selectedBarangs = MasterBarang::whereIn('id', $selectedBarangIds)
                    ->select('id', 'kode_barang', 'nama_barang')
                    ->get()
                    ->map(function ($b) use ($stokMap) {
                        return [
                            'id'          => $b->id,
                            'kode_barang' => $b->kode_barang,
                            'nama_barang' => $b->nama_barang,
                            'stok'        => isset($stokMap[$b->id]) ? floatval($stokMap[$b->id]) : 0,
                            'text'        => $b->kode_barang . ' - ' . $b->nama_barang,
                        ];
                    });
            }
        }

        return response()->json([
            'opname' => [
                'id'              => $stockOpname->id,
                'gudang_id'       => $stockOpname->gudang_id,
                'tanggal_opname'  => $stockOpname->tanggal_opname ? $stockOpname->tanggal_opname->format('Y-m-d') : '',
                'tanggal_mulai'   => $stockOpname->tanggal_mulai ? $stockOpname->tanggal_mulai->format('Y-m-d') : '',
                'tanggal_selesai' => $stockOpname->tanggal_selesai ? $stockOpname->tanggal_selesai->format('Y-m-d') : '',
                'jenis_opname'    => $stockOpname->jenis_opname,
                'keterangan'      => $stockOpname->keterangan,
                'nomor_opname'    => $stockOpname->nomor_opname,
            ],
            'selected_barangs' => $selectedBarangs,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE PROPOSAL (DRAFT PLAN)
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, StockOpname $stockOpname)
    {
        $this->authorize('tambah-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak! Dokumen tidak ditemukan di PT/Cabang Anda.'], 403);
        }

        if (!in_array($stockOpname->status, ['Draft', 'Rejected'])) {
            return response()->json(['message' => 'Hanya proposal berstatus Draft atau Ditolak (Rejected) yang dapat diubah.'], 422);
        }

        $request->validate([
            'tanggal_opname'  => 'required|date',
            'tanggal_mulai'   => 'required|date|after_or_equal:tanggal_opname',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'gudang_id'       => 'required|integer',
            'jenis_opname'    => 'required|in:Full Stock Opname,Partial Stock Opname',
            'keterangan'      => 'required|string|max:40',
        ], [
            'keterangan.max'  => 'Alasan stock opname maksimal 40 karakter!',
        ]);

        $gudang = $this->resolveGudang((int) $request->gudang_id);
        if (!$gudang) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => ['gudang_id' => ['Gudang tidak ditemukan di cabang aktif Anda!']],
            ], 422);
        }

        // 🛑 COOLDOWN RULE: Pembatasan pengajuan Stock Opname minimal 20 hari sekali per Gudang & PT & Cabang
        $minIntervalDays = 20;
        $lastOpnameQuery = StockOpname::where('gudang_id', $gudang->id)
            ->where('id', '!=', $stockOpname->id)
            ->whereNotIn('status', ['Rejected']);

        if (!empty($gudang->cabang_id)) {
            $lastOpnameQuery->where('branch_id', $gudang->cabang_id);
        } else {
            $lastOpnameQuery->whereNull('branch_id')->where('company_id', $gudang->company_id);
        }

        $lastOpname = $lastOpnameQuery->orderBy('tanggal_opname', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOpname) {
            $lastDate = \Carbon\Carbon::parse($lastOpname->tanggal_opname)->startOfDay();
            $newDate  = \Carbon\Carbon::parse($request->tanggal_opname)->startOfDay();
            $diffDays = $lastDate->diffInDays($newDate, false);

            if ($diffDays < $minIntervalDays) {
                $nextAllowedDate = $lastDate->copy()->addDays($minIntervalDays)->format('d/m/Y');
                $remainingDays   = $minIntervalDays - max(0, $diffDays);
                return response()->json([
                    'message' => 'Batasi Frekuensi Pengajuan!',
                    'errors'  => [
                        'tanggal_opname' => [
                            "Pengajuan Stock Opname untuk gudang ini dibatasi minimal <strong>{$minIntervalDays} hari sekali</strong>.<br><br>" .
                            "📋 Pengajuan Terakhir: <strong>{$lastDate->format('d/m/Y')}</strong> (Dokumen: <span class='badge bg-secondary'>{$lastOpname->nomor_opname}</span>)<br>" .
                            "📅 Boleh Mengajukan Lagi: <strong class='text-success'>{$nextAllowedDate}</strong> (<span class='badge bg-warning text-dark'>{$remainingDays} hari lagi</span>)."
                        ]
                    ]
                ], 422);
            }
        }

        if (!$request->auto_number && $request->nomor_opname !== $stockOpname->nomor_opname) {
            $request->validate([
                'nomor_opname' => 'required|string|max:25|unique:stock_opnames,nomor_opname,' . $stockOpname->id,
            ], [
                'nomor_opname.max' => 'Nomor pengajuan maksimal 25 karakter!',
            ]);
        }

        $barangIds = [];

        if ($request->jenis_opname === 'Full Stock Opname') {
            $barangIds = StokGudang::where('gudang_id', $gudang->id)
                ->pluck('barang_id')
                ->toArray();
        } else {
            // Partial: Combinable filtering
            $query = MasterBarang::active()->forActiveCompany();
            $hasFilter = false;

            if ($request->has('filter_barang_ids') && is_array($request->filter_barang_ids) && count($request->filter_barang_ids) > 0) {
                $requestedIds = array_map('intval', array_filter($request->filter_barang_ids));
                if (count($requestedIds) > 0) {
                    $query->whereIn('id', $requestedIds);
                    $hasFilter = true;
                }
            }

            if ($request->filter_kategori_id) {
                $query->where('kategori_barang_id', (int) $request->filter_kategori_id);
                $hasFilter = true;
            }

            if (!$hasFilter) {
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors'  => ['jenis_opname' => ['Untuk Partial Stock Opname, pilih minimal Kategori Barang atau isi daftar Barang Tertentu!']],
                ], 422);
            }

            // Must exist in this warehouse's stock list
            $gudangBarangIds = StokGudang::where('gudang_id', $gudang->id)->pluck('barang_id')->toArray();
            $query->whereIn('id', $gudangBarangIds);

            $barangIds = $query->pluck('id')->toArray();
        }

        if (count($barangIds) === 0) {
            return response()->json([
                'message' => 'Tidak ditemukan barang aktif untuk rencana Stock Opname ini.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $targetBranchId  = $gudang->cabang_id ?? session('active_branch_id');
            $targetCompanyId = !empty($targetBranchId) ? null : ($gudang->company_id ?? session('active_company_id'));

            $updateData = [
                'tanggal_opname'  => $request->tanggal_opname,
                'tanggal_mulai'   => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'gudang_id'       => $gudang->id,
                'company_id'      => $targetCompanyId,
                'branch_id'       => $targetBranchId,
                'jenis_opname'    => $request->jenis_opname,
                'keterangan'      => strip_tags($request->keterangan),
            ];

            if ($request->submit_directly) {
                $updateData['status'] = 'Menunggu Persetujuan';
                $updateData['alasan_reject'] = null;
                $updateData['disetujui_oleh'] = null;
                $updateData['disetujui_pada'] = null;
            } elseif ($stockOpname->status === 'Rejected') {
                $updateData['status'] = 'Draft';
                $updateData['alasan_reject'] = null;
                $updateData['disetujui_oleh'] = null;
                $updateData['disetujui_pada'] = null;
            }

            if (!$request->auto_number && $request->nomor_opname) {
                $updateData['nomor_opname'] = strip_tags($request->nomor_opname);
            }

            $stockOpname->update($updateData);

            StockOpnameDetail::where('opname_id', $stockOpname->id)->delete();

            $stokGudangList = StokGudang::where('gudang_id', $gudang->id)
                ->pluck('stok_aktual', 'barang_id')
                ->toArray();

            $detailsData = [];
            $now = now();
            foreach ($barangIds as $barangId) {
                $stokSistem = isset($stokGudangList[$barangId]) ? floatval($stokGudangList[$barangId]) : 0.00;
                $detailsData[] = [
                    'opname_id'   => $stockOpname->id,
                    'barang_id'   => $barangId,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik'  => null,
                    'selisih'     => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            foreach (array_chunk($detailsData, 500) as $chunk) {
                StockOpnameDetail::insert($chunk);
            }

            DB::commit();

            if ($request->submit_directly) {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendStockOpnameSubmissionNotification($stockOpname)
                );
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Proposal Rencana Stock Opname berhasil diperbarui.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("StockOpnameUpdateError: " . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan sistem.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // SUBMIT PROPOSAL FOR APPROVAL
    // ══════════════════════════════════════════════════════════════
    public function submit(StockOpname $stockOpname)
    {
        $this->authorize('tambah-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!in_array($stockOpname->status, ['Draft', 'Rejected'])) {
            return response()->json(['message' => 'Hanya proposal Draft atau Ditolak yang dapat diajukan.'], 422);
        }

        try {
            $stockOpname->status = 'Menunggu Persetujuan';
            $stockOpname->alasan_reject = null;
            $stockOpname->disetujui_oleh = null;
            $stockOpname->disetujui_pada = null;
            $stockOpname->save();

            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendStockOpnameSubmissionNotification($stockOpname)
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Proposal Stock Opname berhasil diajukan ke atasan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengajukan proposal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // APPROVE PROPOSAL
    // ══════════════════════════════════════════════════════════════
    public function approve(StockOpname $stockOpname)
    {
        $this->authorize('setuju-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$stockOpname->isPending()) {
            return response()->json(['message' => 'Hanya proposal Menunggu Persetujuan yang dapat disetujui.'], 422);
        }

        try {
            $today = now()->format('Y-m-d');
            
            // Auto transition to "Sedang Berjalan" if today is within period
            $startsToday = ($stockOpname->tanggal_mulai->format('Y-m-d') <= $today && $stockOpname->tanggal_selesai->format('Y-m-d') >= $today);
            
            $stockOpname->status = $startsToday ? 'Sedang Berjalan' : 'Approved';
            $stockOpname->disetujui_oleh = auth()->id();
            $stockOpname->disetujui_pada = now();
            $stockOpname->save();

            $approverUser = auth()->user();
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendStockOpnameApprovedNotification($stockOpname, $approverUser)
            );

            return response()->json([
                'status'  => 'success',
                'message' => $startsToday 
                    ? 'Proposal disetujui dan Stock Opname LANGSUNG AKTIF (Sedang Berjalan).' 
                    : 'Proposal berhasil disetujui (Approved).',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyetujui proposal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // REJECT PROPOSAL
    // ══════════════════════════════════════════════════════════════
    public function reject(Request $request, StockOpname $stockOpname)
    {
        $this->authorize('setuju-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$stockOpname->isPending()) {
            return response()->json(['message' => 'Hanya proposal Menunggu Persetujuan yang dapat ditolak.'], 422);
        }

        $request->validate([
            'alasan_reject' => 'required|string|max:250',
        ]);

        try {
            $alasan = strip_tags($request->alasan_reject);
            $stockOpname->status = 'Rejected';
            $stockOpname->alasan_reject = $alasan;
            $stockOpname->disetujui_oleh = auth()->id();
            $stockOpname->disetujui_pada = now();
            $stockOpname->save();

            $rejectorUser = auth()->user();
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendStockOpnameRejectedNotification($stockOpname, $rejectorUser, $alasan)
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Proposal Stock Opname berhasil ditolak.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menolak proposal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // SAVE PROGRESS PHYSICAL COUNTS (DURING RUNNING / SEDANG BERJALAN)
    // ══════════════════════════════════════════════════════════════
    public function savePhysical(Request $request, StockOpname $stockOpname)
    {
        if (!auth()->user()->can('input-fisik-opname') && !auth()->user()->can('tambah-opname')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$stockOpname->isRunning()) {
            return response()->json(['message' => 'Stok fisik hanya dapat diinput saat status Sedang Berjalan.'], 422);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                $barangId = (int) $item['barang_id'];
                
                // Allow empty inputs to save as null (uncounted) or number counts
                $stokFisik = (isset($item['stok_fisik']) && $item['stok_fisik'] !== '') ? floatval($item['stok_fisik']) : null;
                $catatan = isset($item['catatan']) ? strip_tags($item['catatan']) : null;

                $detail = StockOpnameDetail::where('opname_id', $stockOpname->id)
                    ->where('barang_id', $barangId)
                    ->first();

                if ($detail) {
                    $stokSistem = floatval($detail->stok_sistem);
                    $detail->stok_fisik = $stokFisik;
                    $detail->selisih = !is_null($stokFisik) ? ($stokFisik - $stokSistem) : null;
                    $detail->catatan = $catatan;

                    if (array_key_exists('serial_numbers', $item)) {
                        $sns = $item['serial_numbers'];
                        if (is_string($sns)) {
                            $sns = json_decode($sns, true);
                        }
                        $detail->serial_numbers = is_array($sns) ? array_values(array_filter(array_map('trim', $sns))) : null;
                    }

                    $detail->save();
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Progress hitungan fisik berhasil disimpan.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan hitungan fisik.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // COMPLETE COUNTING WORK — SUBMIT FOR CLOSING
    // ══════════════════════════════════════════════════════════════
    public function complete(Request $request, StockOpname $stockOpname)
    {
        if (!auth()->user()->can('posting-opname') && !auth()->user()->can('input-fisik-opname') && !auth()->user()->can('tambah-opname')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$stockOpname->isRunning()) {
            return response()->json(['message' => 'Transaksi hanya dapat diselesaikan jika berstatus Sedang Berjalan.'], 422);
        }

        try {
            DB::beginTransaction();

            // 1. If items are sent in request, save them first
            if ($request->has('items') && is_array($request->items) && count($request->items) > 0) {
                foreach ($request->items as $item) {
                    $barangId = (int) $item['barang_id'];
                    $stokFisik = (isset($item['stok_fisik']) && $item['stok_fisik'] !== '') ? floatval($item['stok_fisik']) : null;
                    $catatan = isset($item['catatan']) ? strip_tags($item['catatan']) : null;

                    $detail = StockOpnameDetail::where('opname_id', $stockOpname->id)
                        ->where('barang_id', $barangId)
                        ->first();

                    if ($detail) {
                        $stokSistem = floatval($detail->stok_sistem);
                        $detail->stok_fisik = $stokFisik;
                        $detail->selisih = !is_null($stokFisik) ? ($stokFisik - $stokSistem) : null;
                        $detail->catatan = $catatan;

                        if (array_key_exists('serial_numbers', $item)) {
                            $sns = $item['serial_numbers'];
                            if (is_string($sns)) {
                                $sns = json_decode($sns, true);
                            }
                            $detail->serial_numbers = is_array($sns) ? array_values(array_filter(array_map('trim', $sns))) : null;
                        }

                        $detail->save();
                    }
                }
            }

            // 2. Validate that at least one item has been counted in database details
            $countedCount = $stockOpname->details()->whereNotNull('stok_fisik')->count();
            if ($countedCount === 0) {
                DB::rollBack();
                return response()->json(['message' => 'Gagal menyelesaikan: Belum ada stok fisik yang diinput/disimpan! Silakan input minimal 1 barang dan klik "Simpan Progress" terlebih dahulu.'], 422);
            }

            // 3. Change status
            $stockOpname->status = 'Menunggu Closing';
            $stockOpname->save();

            DB::commit();

            $completedUser = auth()->user();
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendStockOpnameCompletedNotification($stockOpname, $completedUser)
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Proses hitung fisik selesai. Dokumen dikirim ke atasan untuk Closing (Posting Adjustment).',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyelesaikan perhitungan.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // CLOSING / POSTING ADJUSTMENT (BY SUPERVISOR / ADMIN)
    // ══════════════════════════════════════════════════════════════
    public function close(Request $request, StockOpname $stockOpname)
    {
        if (!auth()->user()->can('closing-opname') && !auth()->user()->can('posting-opname') && !auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Admin')) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki hak akses closing-opname.'], 403);
        }

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak: Dokumen tidak milik cabang/perusahaan aktif.'], 403);
        }

        if (!$stockOpname->isPendingClosing()) {
            return response()->json(['message' => 'Hanya dokumen berstatus Menunggu Closing yang dapat di-Close.'], 422);
        }

        // Security Check: Verify that active company has COA accounts setup
        $hasCoa = ChartOfAccount::forActiveCompany()->where('is_header', false)->active()->exists();
        if (!$hasCoa) {
            return response()->json([
                'message' => 'Gagal Closing: Belum ada Akun COA (Chart of Accounts) yang terdaftar/aktif untuk perusahaan ini. Silakan atur dan isi Data Akun COA terlebih dahulu di Pengaturan Akuntansi.'
            ], 422);
        }

        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        try {
            DB::beginTransaction();

            // Lock header
            $stockOpname = StockOpname::where('id', $stockOpname->id)->lockForUpdate()->first();
            if (!$stockOpname->isPendingClosing()) {
                DB::rollBack();
                return response()->json(['message' => 'Status transaksi sudah berubah.'], 422);
            }

            // Update status
            $stockOpname->status = 'Closed';
            $stockOpname->diclosing_oleh = auth()->id();
            $stockOpname->diclosing_pada = now();
            $stockOpname->save();

            $gudangId = $stockOpname->gudang_id;
            $now = now();

            // Process inventory levels and ledger entries in chunks of 500 items to support up to 7 million rows safely
            $stockOpname->details()->whereNotNull('stok_fisik')->chunk(500, function ($chunk) use ($stockOpname, $gudangId, $now) {
                $barangIds = $chunk->pluck('barang_id')->toArray();

                $stokGudangMap = StokGudang::where('gudang_id', $gudangId)
                    ->whereIn('barang_id', $barangIds)
                    ->get()
                    ->keyBy('barang_id');

                $kartuStokBatch = [];

                foreach ($chunk as $detail) {
                    $barangId = $detail->barang_id;
                    $stokFisik = floatval($detail->stok_fisik);

                    $stokGudang = $stokGudangMap->get($barangId);

                    if (!$stokGudang) {
                        $stokGudang = StokGudang::create([
                            'barang_id'             => $barangId,
                            'gudang_id'             => $gudangId,
                            'stok_aktual'           => 0.00,
                            'nilai_persediaan'      => 0.0000,
                            'harga_pokok_rata_rata' => 0.000000,
                        ]);
                    }

                    $stokAwalStr  = (string)($stokGudang->stok_aktual ?? '0');
                    $stokFisikStr = (string)$stokFisik;
                    $selisihStr   = DecimalHelper::sub($stokFisikStr, $stokAwalStr, 2);
                    $hppStr       = (string)($stokGudang->harga_pokok_rata_rata ?? '0.000000');

                    // Update details with execution-time snapshots
                    $detail->stok_sistem = $stokAwalStr;
                    $detail->selisih     = $selisihStr;
                    $detail->save();

                    // Apply live stock update & update Source of Truth Nilai Persediaan
                    $newNilaiPersediaanStr = DecimalHelper::mul($stokFisikStr, $hppStr, 4);

                    $stokGudang->stok_aktual      = $stokFisikStr;
                    $stokGudang->nilai_persediaan = $newNilaiPersediaanStr;
                    $stokGudang->save();

                    StokGudang::syncMasterHpp($barangId);

                    // Write to Kartu Stok Batch
                    if (DecimalHelper::comp($selisihStr, '0', 4) !== 0) {
                        $isLebih = DecimalHelper::comp($selisihStr, '0', 4) > 0;
                        $tipe = $isLebih ? 'Masuk' : 'Keluar';
                        $jumlahStr = ltrim($selisihStr, '-');

                        $kartuStokBatch[] = [
                            'barang_id'       => $barangId,
                            'gudang_id'       => $gudangId,
                            'tipe'            => $tipe,
                            'jumlah'          => $jumlahStr,
                            'stok_awal'       => $stokAwalStr,
                            'stok_akhir'      => $stokFisikStr,
                            'harga_satuan'    => $hppStr,
                            'nilai_total'     => DecimalHelper::mul($jumlahStr, $hppStr, 4),
                            'referensi_jenis' => 'Stock Opname',
                            'referensi_id'    => $stockOpname->id,
                            'referensi_nomor' => $stockOpname->nomor_opname,
                            'keterangan'      => "Adjustment Closing Opname (Selisih " . ($isLebih ? "Lebih" : "Kurang") . ")",
                            'created_at'      => $now,
                        ];
                    }

                    // Sinkronisasi & Rekonsiliasi Serial Number (jika barang menggunakan pelacakan SN)
                    if (!empty($detail->serial_numbers) && is_array($detail->serial_numbers)) {
                        $existingSns = SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $barangId)
                            ->where('status', 'Tersedia')
                            ->where(function($q) use ($gudangId) {
                                $q->whereHas('penerimaan', function($p) use ($gudangId) {
                                    $p->where('gudang_id', $gudangId);
                                })->orWhere('gudang_id', $gudangId);
                            })
                            ->pluck('serial_number')
                            ->toArray();

                        $verifiedSns = array_values(array_filter(array_map('trim', $detail->serial_numbers)));
                        $missingSns  = array_values(array_diff($existingSns, $verifiedSns));
                        $extraSns    = array_values(array_diff($verifiedSns, $existingSns));

                        // 1. Update status serial yang hilang/kurang menjadi 'Hilang'
                        if (!empty($missingSns)) {
                            SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $barangId)
                                ->whereIn('serial_number', $missingSns)
                                ->where('status', 'Tersedia')
                                ->where(function($q) use ($gudangId) {
                                    $q->whereHas('penerimaan', function($p) use ($gudangId) {
                                        $p->where('gudang_id', $gudangId);
                                    })->orWhere('gudang_id', $gudangId);
                                })
                                ->update([
                                    'status'  => 'Hilang',
                                    'catatan' => "Selisih Kurang Fisik via Stock Opname {$stockOpname->nomor_opname}",
                                ]);
                        }

                        // 2. Daftarkan serial baru (kelebihan fisik opname) sebagai 'Tersedia'
                        foreach ($extraSns as $newSn) {
                            $snTrim = trim($newSn);
                            if (!empty($snTrim)) {
                                SpkPenerimaanBarangJadiSerial::create([
                                    'barang_jadi_id'  => $barangId,
                                    'gudang_id'       => $gudangId,
                                    'stock_opname_id' => $stockOpname->id,
                                    'serial_number'   => $snTrim,
                                    'status'          => 'Tersedia',
                                    'catatan'         => "Selisih Lebih Fisik via Stock Opname {$stockOpname->nomor_opname}",
                                ]);
                            }
                        }
                    }
                }

                if (!empty($kartuStokBatch)) {
                    KartuStok::insert($kartuStokBatch);
                }
            });

            // Read user COA overrides if selected
            $overrideInvCoaId = $request->input('override_inventory_coa_id');
            $overrideAdjCoaId = $request->input('override_adjustment_coa_id');

            // Automatically Generate Accounting Journal Entry for Inventory Adjustment
            $this->generateOpnameJournal($stockOpname, $overrideInvCoaId, $overrideAdjCoaId);

            DB::commit();

            $closerUser = auth()->user();
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendStockOpnameClosedNotification($stockOpname, $closerUser)
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Stock Opname berhasil di-Closing! Jurnal akuntansi persediaan dan stok fisik telah di-posting ke database.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("StockOpnameClosingError: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal Closing: ' . $e->getMessage(),
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // ACCOUNTING JOURNAL GENERATOR FOR STOCK OPNAME
    // ══════════════════════════════════════════════════════════════
    private function generateOpnameJournal(StockOpname $stockOpname, ?int $overrideInvCoaId = null, ?int $overrideAdjCoaId = null): void
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        // 1. Resolve Default System Mappings (Secured with multi-tenant company validation)
        $defaultInventoryCoaId = null;
        if ($overrideInvCoaId) {
            $isValidInv = ChartOfAccount::forActiveCompany()
                ->active()
                ->where('is_header', false)
                ->where('id', (int)$overrideInvCoaId)
                ->exists();
            if ($isValidInv) {
                $defaultInventoryCoaId = (int)$overrideInvCoaId;
            }
        }
        if (!$defaultInventoryCoaId) {
            $defaultInventoryCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory');
        }
        if (!$defaultInventoryCoaId) {
            $defaultInventoryCoaId = ChartOfAccount::forActiveCompany()
                ->where(function($q) {
                    $q->whereIn('type', ['asset', 'ast1', 'Aset', 'Asset', 'Persediaan', 'Inventory'])
                      ->orWhere('name', 'like', '%persediaan%')
                      ->orWhere('code', 'like', '1%');
                })
                ->where('is_header', false)
                ->value('id');
        }

        $defaultAdjustmentCoaId = null;
        if ($overrideAdjCoaId) {
            $isValidAdj = ChartOfAccount::forActiveCompany()
                ->active()
                ->where('is_header', false)
                ->where('id', (int)$overrideAdjCoaId)
                ->exists();
            if ($isValidAdj) {
                $defaultAdjustmentCoaId = (int)$overrideAdjCoaId;
            }
        }
        if (!$defaultAdjustmentCoaId) {
            $defaultAdjustmentCoaId = AccountMappingHelper::getCoaId('default', 'default_stock_adjustment')
                ?? AccountMappingHelper::getCoaId('default', 'inventory_adjustment')
                ?? AccountMappingHelper::getCoaId('stock_opname', 'opname_adjustment');
        }

        if (!$defaultAdjustmentCoaId) {
            $defaultAdjustmentCoaId = ChartOfAccount::forActiveCompany()
                ->where(function($q) {
                    $q->where('name', 'like', '%selisih%')
                      ->orWhere('name', 'like', '%opname%')
                      ->orWhere('name', 'like', '%penyesuaian%')
                      ->orWhere('name', 'like', '%kerugian%')
                      ->orWhere('code', 'like', '5%')
                      ->orWhere('code', 'like', '6%');
                })
                ->where('is_header', false)
                ->value('id');
        }

        if (!$defaultAdjustmentCoaId) {
            $defaultAdjustmentCoaId = ChartOfAccount::forActiveCompany()
                ->where(function($q) {
                    $q->whereIn('type', ['expense', 'cogs', 'other_expense', 'revenue', 'other_revenue', 'bbn', 'Beban', 'Expense', 'Pendapatan', 'Revenue', 'HPP']);
                })
                ->where('is_header', false)
                ->value('id');
        }

        if (!$defaultInventoryCoaId) {
            $defaultInventoryCoaId = ChartOfAccount::forActiveCompany()
                ->where('is_header', false)
                ->value('id');
        }

        if (!$defaultAdjustmentCoaId) {
            $defaultAdjustmentCoaId = ChartOfAccount::forActiveCompany()
                ->where('is_header', false)
                ->where('id', '!=', $defaultInventoryCoaId)
                ->value('id') ?? $defaultInventoryCoaId;
        }

        if (!$defaultInventoryCoaId || !$defaultAdjustmentCoaId) {
            throw new \Exception('Akun COA Penyesuaian Persediaan / Selisih Opname belum dikonfigurasi. Silakan atur Pemetaan Akun (Account Mapping) atau buat Akun COA Selisih Opname terlebih dahulu di Pengaturan Akuntansi.');
        }

        // Group surplus & deficit amounts per COA pair [invCoaId, adjCoaId]
        $surplusGrouped = [];
        $deficitGrouped = [];
        $grandTotalSurplus = 0.00;
        $grandTotalDeficit = 0.00;

        // Process details in chunks of 1000 rows to support up to 7 million rows with minimal RAM footprint
        $stockOpname->details()
            ->with('barang')
            ->whereNotNull('stok_fisik')
            ->chunk(1000, function ($chunk) use (&$surplusGrouped, &$deficitGrouped, &$grandTotalSurplus, &$grandTotalDeficit, $defaultInventoryCoaId, $defaultAdjustmentCoaId, $stockOpname) {
                
                $barangIds = $chunk->pluck('barang_id')->toArray();
                $stokGudangMap = StokGudang::where('gudang_id', $stockOpname->gudang_id)
                    ->whereIn('barang_id', $barangIds)
                    ->pluck('harga_pokok_rata_rata', 'barang_id')
                    ->toArray();

                foreach ($chunk as $detail) {
                    $selisih = floatval($detail->selisih ?? 0);
                    if ($selisih == 0) continue;

                    $hpp = isset($stokGudangMap[$detail->barang_id]) ? floatval($stokGudangMap[$detail->barang_id]) : 0.00;
                    if ($hpp <= 0 && $detail->barang) {
                        $hpp = floatval($detail->barang->harga_pokok_rata_rata ?? 0);
                    }

                    $nilai = abs($selisih) * $hpp;
                    if ($nilai <= 0) continue;

                    $catId = $detail->barang ? $detail->barang->kategori_barang_id : null;
                    $invCoaId = $catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory") : null;
                    if (!$invCoaId) {
                        $invCoaId = $defaultInventoryCoaId;
                    }

                    $adjCoaId = $catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_adjustment") : null;
                    if (!$adjCoaId || $adjCoaId == $invCoaId) {
                        $adjCoaId = $defaultAdjustmentCoaId;
                    }

                    $key = "{$invCoaId}_{$adjCoaId}";

                    if ($selisih > 0) {
                        if (!isset($surplusGrouped[$key])) {
                            $surplusGrouped[$key] = ['inv' => $invCoaId, 'adj' => $adjCoaId, 'amount' => 0.00];
                        }
                        $surplusGrouped[$key]['amount'] += $nilai;
                        $grandTotalSurplus += $nilai;
                    } else {
                        if (!isset($deficitGrouped[$key])) {
                            $deficitGrouped[$key] = ['inv' => $invCoaId, 'adj' => $adjCoaId, 'amount' => 0.00];
                        }
                        $deficitGrouped[$key]['amount'] += $nilai;
                        $grandTotalDeficit += $nilai;
                    }
                }
            });

        $grandTotalSurplus = round($grandTotalSurplus, 2);
        $grandTotalDeficit = round($grandTotalDeficit, 2);

        if ($grandTotalSurplus == 0 && $grandTotalDeficit == 0) {
            return; // No financial value adjustment required
        }

        $totalJurnalVal = $grandTotalSurplus + $grandTotalDeficit;
        $nomorJurnal = 'JRN/SO/' . date('Y/m/') . sprintf('%04d', $stockOpname->id);
        $rawBranchId = !empty($stockOpname->branch_id) ? $stockOpname->branch_id : ($stockOpname->gudang ? $stockOpname->gudang->cabang_id : $activeBranchId);
        $isBranch = !empty($rawBranchId);
        $jurnalCompanyId = $isBranch ? null : ($stockOpname->company_id ?: $activeCompanyId ?: ($stockOpname->gudang ? $stockOpname->gudang->company_id : 1));
        $jurnalBranchId  = $isBranch ? $rawBranchId : null;

        // Check if journal already exists to prevent duplicate entries
        $existing = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Stock Opname')
            ->where('referensi_id', $stockOpname->id)
            ->first();

        if ($existing) {
            $existing->details()->delete();
            $jurnal = $existing;
            $jurnal->update([
                'tanggal_jurnal' => $stockOpname->diclosing_pada ?? now(),
                'company_id'     => $jurnalCompanyId,
                'branch_id'      => $jurnalBranchId,
                'total_debit'    => $totalJurnalVal,
                'total_kredit'   => $totalJurnalVal,
                'keterangan'     => "Jurnal Adjustment Stock Opname {$stockOpname->nomor_opname}",
                'updated_by'     => auth()->id(),
            ]);
        } else {
            $jurnal = Jurnal::create([
                'nomor_jurnal'   => $nomorJurnal,
                'tanggal_jurnal' => $stockOpname->diclosing_pada ?? now(),
                'referensi_tipe' => 'Stock Opname',
                'referensi_id'   => $stockOpname->id,
                'company_id'     => $jurnalCompanyId,
                'branch_id'      => $jurnalBranchId,
                'total_debit'    => $totalJurnalVal,
                'total_kredit'   => $totalJurnalVal,
                'keterangan'     => "Jurnal Adjustment Stock Opname {$stockOpname->nomor_opname}",
                'created_by'     => auth()->id(),
            ]);
        }

        // 1. Post Surplus Entries (Stok Lebih -> DEBIT Persediaan, KREDIT Selisih Opname)
        foreach ($surplusGrouped as $pair) {
            $amount = round($pair['amount'], 2);
            if ($amount <= 0) continue;

            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $pair['inv'],
                'debit'      => $amount,
                'kredit'     => 0,
                'keterangan' => "Penyesuaian Stok Lebih (Surplus) Opname {$stockOpname->nomor_opname}",
            ]);

            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $pair['adj'],
                'debit'      => 0,
                'kredit'     => $amount,
                'keterangan' => "Kredit Penyesuaian Selisih Lebih Opname {$stockOpname->nomor_opname}",
            ]);
        }

        // 2. Post Deficit Entries (Stok Kurang -> DEBIT Selisih Opname, KREDIT Persediaan)
        foreach ($deficitGrouped as $pair) {
            $amount = round($pair['amount'], 2);
            if ($amount <= 0) continue;

            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $pair['adj'],
                'debit'      => $amount,
                'kredit'     => 0,
                'keterangan' => "Beban Penyesuaian Selisih Kurang (Defisit) Opname {$stockOpname->nomor_opname}",
            ]);

            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $pair['inv'],
                'debit'      => 0,
                'kredit'     => $amount,
                'keterangan' => "Kredit Persediaan Penyesuaian Stok Kurang Opname {$stockOpname->nomor_opname}",
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DATATABLE DETAILS (PAGINATED FOR MILLIONS OF ROWS)
    // ══════════════════════════════════════════════════════════════
    public function detailsDatatable(Request $request, StockOpname $stockOpname)
    {
        $this->authorize('lihat-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $query = DB::table('stock_opname_details')
            ->join('master_barang', 'stock_opname_details.barang_id', '=', 'master_barang.id')
            ->leftJoin('master_satuan', 'master_barang.satuan_id', '=', 'master_satuan.id')
            ->where('stock_opname_details.opname_id', $stockOpname->id)
            ->select([
                'stock_opname_details.barang_id',
                'master_barang.kode_barang',
                'master_barang.nama_barang',
                'master_satuan.nama_satuan as satuan',
                'stock_opname_details.stok_sistem',
                'stock_opname_details.stok_fisik',
                'stock_opname_details.selisih',
                'stock_opname_details.catatan',
                'stock_opname_details.serial_numbers'
            ]);

        // Total count
        $totalRecords = $query->count();

        // Search
        if ($request->has('search') && !empty($request->input('search')['value'])) {
            $searchValue = $request->input('search')['value'];
            $query->where(function($q) use ($searchValue) {
                $q->where('master_barang.nama_barang', 'like', "%{$searchValue}%")
                  ->orWhere('master_barang.kode_barang', 'like', "%{$searchValue}%");
            });
        }

        $filteredRecords = $query->count();

        // Ordering
        if ($request->has('order') && isset($request->input('order')[0])) {
            $colIndex = intval($request->input('order')[0]['column']);
            $colDir = $request->input('order')[0]['dir'];
            
            // Map index to columns
            $cols = [
                1 => 'master_barang.kode_barang',
                2 => 'master_barang.nama_barang',
                3 => 'stock_opname_details.stok_sistem',
                4 => 'stock_opname_details.stok_fisik',
                5 => 'stock_opname_details.selisih',
                7 => 'stock_opname_details.catatan'
            ];

            if (isset($cols[$colIndex])) {
                $query->orderBy($cols[$colIndex], $colDir);
            }
        } else {
            // Default ordering: Counted items (stok_fisik IS NOT NULL) at the top!
            $query->orderByRaw('CASE WHEN stock_opname_details.stok_fisik IS NULL THEN 1 ELSE 0 END ASC')
                  ->orderBy('master_barang.kode_barang', 'asc');
        }

        // Pagination
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 25));
        
        $data = $query->skip($start)->take($length)->get();

        // Preload HPP of all items in this branch/warehouse to avoid N+1 query loops
        $stokGudangList = StokGudang::where('gudang_id', $stockOpname->gudang_id)
            ->pluck('harga_pokok_rata_rata', 'barang_id')
            ->toArray();

        $canSeeNilai = $this->canSeeNilai();

        $rows = $data->map(function ($d, $idx) use ($start, $stokGudangList, $canSeeNilai) {
            $sistem = floatval($d->stok_sistem);
            $fisik = !is_null($d->stok_fisik) ? floatval($d->stok_fisik) : null;
            $diff = !is_null($d->selisih) ? floatval($d->selisih) : null;
            
            $hpp = isset($stokGudangList[$d->barang_id]) ? floatval($stokGudangList[$d->barang_id]) : 0.00;

            $nilaiSelisih = !is_null($diff) ? ($diff * $hpp) : 0;

            return [
                'index'          => $start + $idx + 1,
                'barang_kode'    => $d->kode_barang,
                'barang_nama'    => $d->nama_barang,
                'satuan'         => $d->satuan ?? '',
                'stok_sistem'    => $sistem,
                'stok_fisik'     => $fisik,
                'selisih'        => $diff,
                'hpp'            => $canSeeNilai ? $hpp : 0.00,
                'nilai_selisih'  => $canSeeNilai ? $nilaiSelisih : 0.00,
                'can_see_nilai'  => $canSeeNilai,
                'catatan'        => $d->catatan ?? '',
                'serial_numbers' => !empty($d->serial_numbers) ? (is_array($d->serial_numbers) ? $d->serial_numbers : json_decode($d->serial_numbers, true)) : [],
            ];
        });

        return response()->json([
            'draw'            => intval($request->input('draw', 1)),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $rows
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // PRINT STOCK OPNAME (PDF PRINT FRIENDLY)
    // ══════════════════════════════════════════════════════════════
    public function print(StockOpname $stockOpname)
    {
        $this->authorize('lihat-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            abort(403, 'Akses ditolak.');
        }

        $stockOpname->load([
            'gudang',
            'creator.activeSignature', 'creator.position', 'creator.karyawan.position', 'creator.roles',
            'approver.activeSignature', 'approver.position', 'approver.karyawan.position', 'approver.roles',
            'closer.activeSignature', 'closer.position', 'closer.karyawan.position', 'closer.roles'
        ]);

        // Aggregate statistics using database count/sum to avoid memory overload
        $countTotal = $stockOpname->details()->count();
        $countDihitung = $stockOpname->details()->whereNotNull('stok_fisik')->count();
        $countBelum = $countTotal - $countDihitung;

        $selisihLebihVal = 0;
        $selisihKurangVal = 0;
        $nilaiSelisihTotal = 0;

        $stokGudangList = StokGudang::where('gudang_id', $stockOpname->gudang_id)
            ->pluck('harga_pokok_rata_rata', 'barang_id')
            ->toArray();

        // Load all details in chunk to avoid memory limit issues for large datasets
        $details = [];
        $stockOpname->details()->with('barang.satuan')->chunk(500, function ($chunk) use (&$details, &$selisihLebihVal, &$selisihKurangVal, &$nilaiSelisihTotal, $stokGudangList) {
            foreach ($chunk as $d) {
                $sistem = floatval($d->stok_sistem);
                $fisik = !is_null($d->stok_fisik) ? floatval($d->stok_fisik) : null;
                $diff = !is_null($d->selisih) ? floatval($d->selisih) : null;
                $hpp = isset($stokGudangList[$d->barang_id]) ? floatval($stokGudangList[$d->barang_id]) : 0.00;
                if ($hpp <= 0 && $d->barang) {
                    $hpp = floatval($d->barang->harga_pokok_rata_rata ?? 0);
                }

                if (!is_null($diff)) {
                    $valDiff = $diff * $hpp;
                    $nilaiSelisihTotal += $valDiff;

                    if ($diff > 0) {
                        $selisihLebihVal += $valDiff;
                    } elseif ($diff < 0) {
                        $selisihKurangVal += abs($valDiff);
                    }
                }

                $details[] = [
                    'barang_kode'    => $d->barang ? $d->barang->kode_barang : '-',
                    'barang_nama'    => $d->barang ? $d->barang->nama_barang : '-',
                    'satuan'         => $d->barang && $d->barang->satuan ? $d->barang->satuan->nama_satuan : '',
                    'stok_sistem'    => $sistem,
                    'stok_fisik'     => $fisik,
                    'selisih'        => $diff,
                    'hpp'            => $hpp,
                    'nilai_selisih'  => !is_null($diff) ? ($diff * $hpp) : 0,
                    'catatan'        => $d->catatan ?? '',
                    'serial_numbers' => $d->serial_numbers ?? [],
                ];
            }
        });

        // Sort details: counted items first
        usort($details, function($a, $b) {
            $aCount = $a['stok_fisik'] !== null ? 1 : 0;
            $bCount = $b['stok_fisik'] !== null ? 1 : 0;
            return $bCount - $aCount;
        });

        $canSeeFinancials = $this->canSeeNilai();

        return view('modul_inventori.stok_opname.printopname', compact(
            'stockOpname',
            'details',
            'countTotal',
            'countDihitung',
            'countBelum',
            'selisihLebihVal',
            'selisihKurangVal',
            'nilaiSelisihTotal',
            'canSeeFinancials'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // EXPORT TO EXCEL (ZERO-DEPENDENCY STYLED XLS XML STREAM)
    // ══════════════════════════════════════════════════════════════
    public function exportExcel(StockOpname $stockOpname)
    {
        $this->authorize('lihat-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            abort(403, 'Akses ditolak.');
        }

        $stockOpname->load(['gudang', 'creator', 'approver', 'closer']);

        $countTotal = $stockOpname->details()->count();
        $countDihitung = $stockOpname->details()->whereNotNull('stok_fisik')->count();
        $countBelum = $countTotal - $countDihitung;

        $selisihLebihVal = 0;
        $selisihKurangVal = 0;
        $nilaiSelisihTotal = 0;

        $stokGudangList = StokGudang::where('gudang_id', $stockOpname->gudang_id)
            ->pluck('harga_pokok_rata_rata', 'barang_id')
            ->toArray();

        // Load details in chunk to avoid memory limit issues for large datasets
        $details = [];
        $stockOpname->details()->with('barang.satuan')->chunk(500, function ($chunk) use (&$details, &$selisihLebihVal, &$selisihKurangVal, &$nilaiSelisihTotal, $stokGudangList) {
            foreach ($chunk as $d) {
                $sistem = floatval($d->stok_sistem);
                $fisik = !is_null($d->stok_fisik) ? floatval($d->stok_fisik) : null;
                $diff = !is_null($d->selisih) ? floatval($d->selisih) : null;
                $hpp = isset($stokGudangList[$d->barang_id]) ? floatval($stokGudangList[$d->barang_id]) : 0.00;
                if ($hpp <= 0 && $d->barang) {
                    $hpp = floatval($d->barang->harga_pokok_rata_rata ?? 0);
                }

                if (!is_null($diff)) {
                    $valDiff = $diff * $hpp;
                    $nilaiSelisihTotal += $valDiff;

                    if ($diff > 0) {
                        $selisihLebihVal += $valDiff;
                    } elseif ($diff < 0) {
                        $selisihKurangVal += abs($valDiff);
                    }
                }

                $details[] = [
                    'barang_kode'    => $d->barang ? $d->barang->kode_barang : '-',
                    'barang_nama'    => $d->barang ? $d->barang->nama_barang : '-',
                    'satuan'         => $d->barang && $d->barang->satuan ? $d->barang->satuan->nama_satuan : '',
                    'stok_sistem'    => $sistem,
                    'stok_fisik'     => $fisik,
                    'selisih'        => $diff,
                    'hpp'            => $hpp,
                    'nilai_selisih'  => !is_null($diff) ? ($diff * $hpp) : 0,
                    'catatan'        => $d->catatan ?? '',
                    'serial_numbers' => $d->serial_numbers ?? [],
                ];
            }
        });

        // Sort details: counted items first
        usort($details, function($a, $b) {
            $aCount = $a['stok_fisik'] !== null ? 1 : 0;
            $bCount = $b['stok_fisik'] !== null ? 1 : 0;
            return $bCount - $aCount;
        });

        $canSeeFinancials = $this->canSeeNilai();

        $filename = "Stock_Opname_" . str_replace('/', '_', $stockOpname->nomor_opname) . ".xls";

        return response()->stream(function() use ($stockOpname, $details, $countTotal, $countDihitung, $countBelum, $selisihLebihVal, $selisihKurangVal, $nilaiSelisihTotal, $canSeeFinancials) {
            echo view('modul_inventori.stok_opname.exceleftopname', compact(
                'stockOpname',
                'details',
                'countTotal',
                'countDihitung',
                'countBelum',
                'selisihLebihVal',
                'selisihKurangVal',
                'nilaiSelisihTotal',
                'canSeeFinancials'
            ));
        }, 200, [
            "Content-Type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
            "Cache-Control" => "max-age=0",
        ]);
    }

    // ── Show Details (AJAX Preview) ──────────────────────────────────────
    public function show(StockOpname $stockOpname)
    {
        $this->authorize('lihat-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak: Dokumen tidak milik cabang/perusahaan aktif.'], 403);
        }

        $stockOpname->load(['gudang', 'creator', 'approver', 'closer']);

        // Aggregate statistics for closing review using database queries
        $countTotal = $stockOpname->details()->count();
        $countDihitung = $stockOpname->details()->whereNotNull('stok_fisik')->count();
        $countBelum = $countTotal - $countDihitung;

        $selisihLebihVal = 0;
        $selisihKurangVal = 0;
        $nilaiSelisihTotal = 0;

        if ($countDihitung > 0) {
            $stokGudangList = StokGudang::where('gudang_id', $stockOpname->gudang_id)
                ->pluck('harga_pokok_rata_rata', 'barang_id')
                ->toArray();

            $countedDetails = $stockOpname->details()
                ->whereNotNull('stok_fisik')
                ->select(['barang_id', 'stok_sistem', 'stok_fisik', 'selisih'])
                ->get();

            foreach ($countedDetails as $d) {
                $diff = floatval($d->selisih ?? ($d->stok_fisik - $d->stok_sistem));
                $hpp = isset($stokGudangList[$d->barang_id]) ? floatval($stokGudangList[$d->barang_id]) : 0.00;
                $valDiff = $diff * $hpp;
                $nilaiSelisihTotal += $valDiff;

                if ($diff > 0) {
                    $selisihLebihVal += $valDiff;
                } elseif ($diff < 0) {
                    $selisihKurangVal += abs($valDiff);
                }
            }
        }

        $user = auth()->user();
        $canViewJurnal = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) 
                      || $user->can('lihat-jurnal-opname') 
                      || $user->can('akses-accounting')) : false;

        $jurnalData = null;
        if ($stockOpname->status === 'Closed') {
            $jurnal = Jurnal::where('referensi_tipe', 'Stock Opname')
                ->where('referensi_id', $stockOpname->id)
                ->with(['details.account'])
                ->first();

            if ($jurnal && $canViewJurnal) {
                $jurnalData = [
                    'id'             => $jurnal->id,
                    'nomor_jurnal'   => $jurnal->nomor_jurnal,
                    'tanggal_jurnal' => $jurnal->tanggal_jurnal ? $jurnal->tanggal_jurnal->format('d/m/Y') : '-',
                    'total_debit'    => floatval($jurnal->total_debit),
                    'total_kredit'   => floatval($jurnal->total_kredit),
                    'keterangan'     => $jurnal->keterangan,
                    'details'        => $jurnal->details->map(function ($jd) {
                        return [
                            'account_code' => $jd->account ? ($jd->account->code ?? $jd->account->kode_akun ?? '-') : '-',
                            'account_name' => $jd->account ? ($jd->account->name ?? $jd->account->nama_akun ?? '-') : '-',
                            'debit'        => floatval($jd->debit),
                            'kredit'       => floatval($jd->kredit),
                            'keterangan'   => $jd->keterangan,
                        ];
                    }),
                ];
            }
        }

        $previewCoaInventory = null;
        $previewCoaAdjustment = null;
        $availableCoas = [];

        if ($stockOpname->status === 'Menunggu Closing' && $canViewJurnal) {
            $firstDetail = $stockOpname->details()->with('barang')->first();
            $catId = $firstDetail && $firstDetail->barang ? $firstDetail->barang->kategori_barang_id : null;

            $inventoryCoaId = ($catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory") : null)
                ?? AccountMappingHelper::getCoaId('default', 'default_inventory')
                ?? ChartOfAccount::forActiveCompany()
                    ->where(function($q) {
                        $q->whereIn('type', ['asset', 'Aset', 'Asset', 'Persediaan', 'Inventory'])
                          ->orWhere('name', 'like', '%persediaan%')
                          ->orWhere('code', 'like', '113%');
                    })
                    ->where('is_header', false)
                    ->value('id');

            $adjustmentCoaId = ($catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_adjustment") : null)
                ?? AccountMappingHelper::getCoaId('default', 'default_stock_adjustment')
                ?? AccountMappingHelper::getCoaId('default', 'inventory_adjustment')
                ?? AccountMappingHelper::getCoaId('stock_opname', 'opname_adjustment')
                ?? ChartOfAccount::forActiveCompany()
                    ->where(function($q) {
                        $q->where('name', 'like', '%selisih%')
                          ->orWhere('name', 'like', '%opname%')
                          ->orWhere('name', 'like', '%penyesuaian%')
                          ->orWhere('code', 'like', '5%')
                          ->orWhere('code', 'like', '6%');
                    })
                    ->where('is_header', false)
                    ->value('id');

            if ($inventoryCoaId) {
                $invAcc = ChartOfAccount::find($inventoryCoaId);
                if ($invAcc) {
                    $previewCoaInventory = ['id' => $invAcc->id, 'code' => $invAcc->code, 'name' => $invAcc->name];
                }
            }

            if ($adjustmentCoaId) {
                $adjAcc = ChartOfAccount::find($adjustmentCoaId);
                if ($adjAcc) {
                    $previewCoaAdjustment = ['id' => $adjAcc->id, 'code' => $adjAcc->code, 'name' => $adjAcc->name];
                }
            }

            $availableCoas = ChartOfAccount::forActiveCompany()
                ->detailOnly()
                ->active()
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'type'])
                ->map(function ($c) {
                    return [
                        'id'    => $c->id,
                        'code'  => $c->code,
                        'name'  => $c->name,
                        'type'  => $c->type_label ?? $c->type,
                    ];
                });
        }

        $gudangId = $stockOpname->gudang_id;
        $detailsList = $stockOpname->details()->with('barang.satuan')->get()->map(function ($d) use ($gudangId) {
            $hasSerials = $d->barang ? ($d->barang->tipe_pelacakan === 'Serial Number') : false;
            if (!$hasSerials && $d->barang) {
                // Juga deteksi jika ada data nomor seri aktif di gudang ini
                $hasSerials = SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $d->barang_id)
                    ->where(function($q) use ($gudangId) {
                        $q->whereHas('penerimaan', function($p) use ($gudangId) {
                            $p->where('gudang_id', $gudangId);
                        })->orWhere('gudang_id', $gudangId);
                    })
                    ->exists();
            }

            return [
                'barang_id'      => $d->barang_id,
                'barang_kode'    => $d->barang ? $d->barang->kode_barang : '-',
                'barang_nama'    => $d->barang ? $d->barang->nama_barang : '-',
                'barang_barcode' => $d->barang ? ($d->barang->barcode ?? $d->barang->kode_barang) : '',
                'satuan'         => $d->barang && $d->barang->satuan ? $d->barang->satuan->nama_satuan : 'Unit',
                'stok_sistem'    => floatval($d->stok_sistem),
                'stok_fisik'     => !is_null($d->stok_fisik) ? floatval($d->stok_fisik) : null,
                'selisih'        => !is_null($d->selisih) ? floatval($d->selisih) : null,
                'catatan'        => $d->catatan ?? '',
                'has_serials'    => $hasSerials,
                'serial_numbers' => $d->serial_numbers ?? [],
            ];
        });

        return response()->json([
            'id'                     => $stockOpname->id,
            'nomor_opname'           => $stockOpname->nomor_opname,
            'tanggal_opname'         => $stockOpname->tanggal_opname->format('d/m/Y'),
            'tanggal_mulai'          => $stockOpname->tanggal_mulai ? $stockOpname->tanggal_mulai->format('d/m/Y') : '-',
            'tanggal_selesai'        => $stockOpname->tanggal_selesai ? $stockOpname->tanggal_selesai->format('d/m/Y') : '-',
            'jenis_opname'           => $stockOpname->jenis_opname,
            'gudang_nama'            => $stockOpname->gudang ? $stockOpname->gudang->nama_gudang : '-',
            'keterangan'             => $stockOpname->keterangan ?? '-',
            'alasan_reject'          => $stockOpname->alasan_reject ?? '-',
            'status'                 => $stockOpname->status,
            'creator_name'           => $stockOpname->creator ? $stockOpname->creator->nama_lengkap : '-',
            'approver_name'          => $stockOpname->approver ? $stockOpname->approver->nama_lengkap : '-',
            'approved_at'            => $stockOpname->disetujui_pada ? $stockOpname->disetujui_pada->format('d/m/Y H:i') : '-',
            'closer_name'            => $stockOpname->closer ? $stockOpname->closer->nama_lengkap : '-',
            'closed_at'              => $stockOpname->diclosing_pada ? $stockOpname->diclosing_pada->format('d/m/Y H:i') : '-',
            
            // Details list for Physical Count Modal & Autocomplete
            'details'                => $detailsList,

            // Closing Metrics & Financial Permissions
            'can_see_financials'     => $this->canSeeNilai(),
            'can_view_jurnal'        => $canViewJurnal,
            'jurnal'                 => $jurnalData,
            'preview_coa_inventory'  => $previewCoaInventory,
            'preview_coa_adjustment' => $previewCoaAdjustment,
            'available_coas'         => $availableCoas,
            'total_items'            => $countTotal,
            'items_dihitung'         => $countDihitung,
            'items_belum'            => $countBelum,
            'nilai_selisih_lebih'    => $this->canSeeNilai() ? $selisihLebihVal : 0.00,
            'nilai_selisih_kurang'   => $this->canSeeNilai() ? $selisihKurangVal : 0.00,
            'nilai_selisih_netto'    => $this->canSeeNilai() ? $nilaiSelisihTotal : 0.00,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // DELETE DRAFT PROPOSAL
    // ══════════════════════════════════════════════════════════════
    public function destroy(StockOpname $stockOpname)
    {
        $this->authorize('hapus-opname');

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Deletion allowed only if Draft or Rejected
        if (!in_array($stockOpname->status, ['Draft', 'Rejected'])) {
            return response()->json(['message' => 'Hanya proposal berstatus Draft atau Ditolak yang dapat dihapus.'], 422);
        }

        try {
            DB::beginTransaction();
            $stockOpname->delete();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Proposal Rencana Stock Opname berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus proposal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // FIND ACTIVE BARANG BY CODE/BARCODE OR SERIAL NUMBER (ON-THE-FLY SCAN)
    // ══════════════════════════════════════════════════════════════
    public function findBarangByCode(Request $request, StockOpname $stockOpname)
    {
        if (!auth()->user()->can('tambah-opname') && !auth()->user()->can('input-fisik-opname') && !auth()->user()->can('posting-opname') && !auth()->user()->can('lihat-opname')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $rawCode = trim((string)$request->get('code'));
        if (empty($rawCode)) {
            return response()->json(['message' => 'Kode barang atau nomor seri kosong.'], 400);
        }

        // Parsing QR Code / Barcode cerdas:
        // Mendukung:
        // 1. Parameter URL ?sn=
        // 2. Structured QR format (gabungan SN: ..., KODE: ..., BARANG: ..., BATCH: ..., WARNA: ...)
        // 3. Multi-line QR format dengan \r\n atau \n
        // 4. Prefix SN: ...
        // 5. Kode barang / barcode murni
        $extractedSn   = null;
        $extractedKode = null;

        if (preg_match('/[?&]sn=([^&]+)/i', $rawCode, $m)) {
            $extractedSn = urldecode(trim($m[1]));
        }

        if (!$extractedSn && preg_match('/SN\s*:\s*(.+?)(?=(?:(?:KODE|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i', $rawCode, $m)) {
            $extractedSn = trim($m[1]);
        }

        if (preg_match('/KODE\s*:\s*(.+?)(?=(?:(?:SN|BARANG|BATCH|WARNA)\s*:)|[\r\n]|$)/i', $rawCode, $m)) {
            $extractedKode = trim($m[1]);
        }

        if (!$extractedSn && preg_match('/^SN\s*[:\-\s]\s*(.+)$/i', $rawCode, $m)) {
            $extractedSn = trim($m[1]);
        }

        $cleanCode = $extractedSn ?: ($extractedKode ?: $rawCode);

        $matchedType   = 'barang';
        $matchedSerial = null;
        $barang        = null;
        $gudangId      = $stockOpname->gudang_id;

        // 1. Cek apakah yang discan adalah Serial Number (QR/Barcode SN)
        $potentialSns = array_values(array_filter(array_unique([$extractedSn, $cleanCode, $rawCode])));
        if (!empty($potentialSns)) {
            // Prioritas 1: Serial yang sudah tercatat di gudang opname ini
            $snRecord = SpkPenerimaanBarangJadiSerial::whereIn('serial_number', $potentialSns)
                ->where(function($q) use ($gudangId) {
                    $q->whereHas('penerimaan', function($p) use ($gudangId) {
                        $p->where('gudang_id', $gudangId);
                    })
                    ->orWhere('gudang_id', $gudangId)
                    ->orWhereHas('adjustment', function($a) use ($gudangId) {
                        $a->where('gudang_id', $gudangId);
                    })
                    ->orWhereHas('opname', function($o) use ($gudangId) {
                        $o->where('gudang_id', $gudangId);
                    });
                })
                ->first();

            // Prioritas 2: Serial tercatat di perusahaan aktif (misal fisik ditemukan di gudang ini)
            if (!$snRecord) {
                $snRecord = SpkPenerimaanBarangJadiSerial::whereIn('serial_number', $potentialSns)
                    ->forActiveCompany()
                    ->first();
            }

            // Prioritas 3: Serial tercatat di tabel serial secara global
            if (!$snRecord) {
                $snRecord = SpkPenerimaanBarangJadiSerial::whereIn('serial_number', $potentialSns)->first();
            }

            if ($snRecord) {
                $barang = MasterBarang::active()->with('satuan')->find($snRecord->barang_jadi_id);
                if ($barang) {
                    $matchedType   = 'serial_number';
                    $matchedSerial = $snRecord->serial_number;
                }
            }
        }

        // 2. Jika bukan serial number yang tercatat, cari berdasarkan kode barang atau barcode
        if (!$barang) {
            $searchCodes = array_values(array_filter(array_unique([$extractedKode, $cleanCode, $rawCode])));
            $barang = MasterBarang::active()
                ->forActiveCompany()
                ->with('satuan')
                ->where(function($q) use ($searchCodes) {
                    $q->whereIn('kode_barang', $searchCodes)
                      ->orWhereIn('barcode', $searchCodes);
                })
                ->first();

            if (!$barang) {
                $barang = MasterBarang::active()
                    ->with('satuan')
                    ->where(function($q) use ($searchCodes) {
                        $q->whereIn('kode_barang', $searchCodes)
                          ->orWhereIn('barcode', $searchCodes);
                    })
                    ->first();
            }

            // Jika barang berhasil ditemukan dan QR memiliki nomor seri (temuan serial fisik baru)
            if ($barang && !empty($extractedSn)) {
                $matchedType   = 'serial_number';
                $matchedSerial = $extractedSn;
            }
        }

        if (!$barang) {
            return response()->json(['message' => "Kode/Barcode atau QR Serial '{$rawCode}' tidak terdaftar di sistem!"], 404);
        }

        // Deteksi apakah barang memiliki pelacakan serial number
        $hasSerials = ($barang->tipe_pelacakan === 'Serial Number') || ($matchedSerial !== null) || SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $barang->id)
            ->where(function($q) use ($gudangId) {
                $q->whereHas('penerimaan', function($p) use ($gudangId) {
                    $p->where('gudang_id', $gudangId);
                })->orWhere('gudang_id', $gudangId);
            })
            ->exists();

        // Check if there is already a stock record for this item in this warehouse
        $stokGudang = StokGudang::where('barang_id', $barang->id)
            ->where('gudang_id', $gudangId)
            ->first();

        $stokSistem = $stokGudang ? floatval($stokGudang->stok_aktual) : 0.00;

        // Auto-create detail row if it doesn't exist yet
        $detail = StockOpnameDetail::where('opname_id', $stockOpname->id)
            ->where('barang_id', $barang->id)
            ->first();

        if (!$detail) {
            $detail = StockOpnameDetail::create([
                'opname_id'   => $stockOpname->id,
                'barang_id'   => $barang->id,
                'stok_sistem' => $stokSistem,
                'stok_fisik'  => null,
                'selisih'     => null,
            ]);
        }

        return response()->json([
            'id'             => $detail->id,
            'barang_id'      => $barang->id,
            'barang_kode'    => $barang->kode_barang,
            'barang_nama'    => $barang->nama_barang,
            'barang_barcode' => $barang->barcode,
            'satuan'         => $barang->satuan ? $barang->satuan->nama_satuan : 'Pcs',
            'stok_sistem'    => $stokSistem,
            'stok_fisik'     => $detail->stok_fisik,
            'selisih'        => $detail->selisih,
            'catatan'        => $detail->catatan,
            'has_serials'    => $hasSerials,
            'serial_numbers' => $detail->serial_numbers ?? [],
            'matched_type'   => $matchedType,
            'matched_serial' => $matchedSerial,
        ]);
    }

    /**
     * Dapatkan daftar Serial Number yang terdaftar di gudang ini untuk barang terkait.
     */
    public function getAvailableSerials(Request $request, StockOpname $stockOpname)
    {
        if (!auth()->user()->can('lihat-opname') && !auth()->user()->can('input-fisik-opname') && !auth()->user()->can('tambah-opname') && !auth()->user()->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$this->resolveStockOpname($stockOpname)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $barangId = (int)$request->get('barang_id');
        if (!$barangId) {
            return response()->json(['message' => 'Barang ID wajib diisi.'], 422);
        }

        $gudangId = $stockOpname->gudang_id;

        $serials = SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $barangId)
            ->where('status', 'Tersedia')
            ->where(function($q) use ($gudangId) {
                $q->whereHas('penerimaan', function($p) use ($gudangId) {
                    $p->where('gudang_id', $gudangId);
                })
                ->orWhere('gudang_id', $gudangId)
                ->orWhereHas('adjustment', function($a) use ($gudangId) {
                    $a->where('gudang_id', $gudangId);
                })
                ->orWhereHas('opname', function($o) use ($gudangId) {
                    $o->where('gudang_id', $gudangId);
                });
            })
            ->select('serial_number')
            ->orderBy('serial_number', 'asc')
            ->pluck('serial_number')
            ->unique()
            ->values()
            ->toArray();

        $detail = StockOpnameDetail::where('opname_id', $stockOpname->id)
            ->where('barang_id', $barangId)
            ->first();

        $savedSerials = ($detail && is_array($detail->serial_numbers)) ? $detail->serial_numbers : [];

        return response()->json([
            'success'       => true,
            'serials'       => $serials,
            'saved_serials' => $savedSerials,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // GET SYSTEM STOK — AJAX endpoint
    // ══════════════════════════════════════════════════════════════
    public function getStokBarang(int $gudangId, int $barangId)
    {
        $this->authorize('lihat-opname');

        $gudang = $this->resolveGudang($gudangId);
        if (!$gudang) {
            return response()->json(['error' => 'Gudang tidak ditemukan atau bukan milik cabang aktif.'], 403);
        }

        $stokGudang = StokGudang::where('barang_id', $barangId)
            ->where('gudang_id', $gudangId)
            ->first();

        $stok = $stokGudang ? floatval($stokGudang->stok_aktual) : 0.00;

        return response()->json([
            'stok_aktual' => $stok,
        ]);
    }
}
