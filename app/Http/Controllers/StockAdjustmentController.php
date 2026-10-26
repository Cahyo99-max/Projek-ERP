<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\MasterBarang;
use App\Models\MasterGudang;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\ChartOfAccount;
use App\Helpers\AccountMappingHelper;
use App\Helpers\DecimalHelper;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\WhatsAppGatewayController;

class StockAdjustmentController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // LAYER 1: PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    private function resolveGudang(int $gudangId): ?MasterGudang
    {
        $activeBranchId = session('active_branch_id');
        $query = MasterGudang::where('id', $gudangId)
            ->forActiveCompany()
            ->where('status', 'Active');

        if (!empty($activeBranchId)) {
            $query->where('cabang_id', $activeBranchId);
        } else {
            $query->whereNull('cabang_id');
        }

        return $query->first();
    }

    private function resolveAdjustment(StockAdjustment $adj): bool
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            // Sesi Cabang: dokumen harus milik branch_id tersebut
            if ($adj->branch_id != $activeBranchId) {
                return false;
            }
        } else {
            // Sesi PT Pusat: dokumen harus berstatus PT (branch_id = NULL)
            if ($adj->branch_id !== null) {
                return false;
            }
            if ($activeCompanyId && $adj->company_id && $adj->company_id != $activeCompanyId) {
                return false;
            }
        }
        return MasterGudang::where('id', $adj->gudang_id)
            ->forActiveCompany()
            ->exists();
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX — Adjustment List (AJAX DataTables + Blade)
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-stok-adjustment')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat Stock Adjustment.');
        }

        $activeBranchId  = session('active_branch_id');
        $isSupervisor    = $user->hasAnyRole(['Super Admin', 'Owner']);
        $canApproveGlobal = $isSupervisor || $user->can('approve-stok-adjustment');

        if ($request->ajax()) {
            $query = StockAdjustment::forActiveCompany()
                ->forActiveBranch()
                ->where(function($q) use ($user) {
                    // Draft hanya terlihat oleh pembuat (creator)
                    $q->where('status', '!=', 'Draft')
                      ->orWhere('created_by', $user->id);
                })
                ->with([
                    'gudang:id,nama_gudang',
                    'creator:id,nama_lengkap',
                ])
                ->select('stock_adjustments.*')
                ->orderByDesc('stock_adjustments.id');

            // Filter Periode Bulan (Default: Bulan Berjalan)
            $filterBulan = $request->input('filter_bulan', date('Y-m'));
            $isSemuaBulan = ($filterBulan === 'all' || $request->input('semua_bulan') == '1');

            if (!$isSemuaBulan && !empty($filterBulan)) {
                $parts = explode('-', $filterBulan);
                if (count($parts) === 2) {
                    $query->whereYear('tanggal_adjustment', (int)$parts[0])
                          ->whereMonth('tanggal_adjustment', (int)$parts[1]);
                }
            }

            if ($request->filled('gudang_id')) {
                $query->where('gudang_id', $request->gudang_id);
            }
            if ($request->filled('tipe_adjustment')) {
                $query->where('tipe_adjustment', $request->tipe_adjustment);
            }
            if ($request->filled('kategori_alasan')) {
                $query->where('kategori_alasan', $request->kategori_alasan);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $canSeeHpp  = $isSupervisor || $user->can('lihat-hpp-stok-adjustment');
            $canApprove = $isSupervisor || $user->can('approve-stok-adjustment');
            $canEdit    = $isSupervisor || $user->can('edit-stok-adjustment');
            $canDelete  = $isSupervisor || $user->can('hapus-stok-adjustment');

            $baseQuery = StockAdjustment::forActiveCompany()->forActiveBranch();
            $visibleQuery = (clone $baseQuery)->where(function($q) use ($user) {
                // Draft hanya terlihat oleh pembuat (creator)
                $q->where('status', '!=', 'Draft')->orWhere('created_by', $user->id);
            });

            $kpiVisibleQuery = (clone $visibleQuery);
            if (!$isSemuaBulan && !empty($filterBulan)) {
                $parts = explode('-', $filterBulan);
                if (count($parts) === 2) {
                    $kpiVisibleQuery->whereYear('tanggal_adjustment', (int)$parts[0])
                                    ->whereMonth('tanggal_adjustment', (int)$parts[1]);
                }
            }

            if ($request->filled('gudang_id')) {
                $kpiVisibleQuery->where('gudang_id', $request->gudang_id);
            }
            if ($request->filled('tipe_adjustment')) {
                $kpiVisibleQuery->where('tipe_adjustment', $request->tipe_adjustment);
            }
            if ($request->filled('kategori_alasan')) {
                $kpiVisibleQuery->where('kategori_alasan', $request->kategori_alasan);
            }
            if ($request->filled('status')) {
                $kpiVisibleQuery->where('status', $request->status);
            }

            $kpiRow = $kpiVisibleQuery
                ->selectRaw("
                    COUNT(*) as total_doc,
                    COALESCE(SUM(CASE WHEN tipe_adjustment = 'Pengurangan' THEN total_qty ELSE 0 END), 0) as total_pengurangan,
                    COALESCE(SUM(CASE WHEN tipe_adjustment = 'Penambahan' THEN total_qty ELSE 0 END), 0) as total_penambahan,
                    COALESCE(SUM(total_nilai), 0) as total_nilai
                ")
                ->first();

            $kpiData = [
                'total_doc'        => (int)($kpiRow->total_doc ?? 0),
                'total_pengurangan'=> (float)($kpiRow->total_pengurangan ?? 0),
                'total_penambahan' => (float)($kpiRow->total_penambahan ?? 0),
                'total_nilai'      => $canSeeHpp ? (float)($kpiRow->total_nilai ?? 0) : 0,
                'can_see_hpp'      => $canSeeHpp,
            ];

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor_adj_link', function ($adj) {
                    return '<a href="javascript:void(0)" onclick="showDetailAdjustment('.$adj->id.')" class="fw-bold text-decoration-none text-primary d-inline-flex align-items-center gap-1" title="Klik untuk lihat detail">
                                <span>'.$adj->nomor_adjustment.'</span>
                            </a>';
                })
                ->addColumn('tanggal_fmt', function ($adj) {
                    return $adj->tanggal_adjustment ? '<span class="text-nowrap font-monospace text-body">' . $adj->tanggal_adjustment->format('d/m/Y') . '</span>' : '-';
                })
                ->addColumn('gudang_name', function ($adj) {
                    $namaGudang = $adj->gudang ? $adj->gudang->nama_gudang : '-';
                    return '<span class="text-body fw-medium d-inline-flex align-items-center gap-1.5"><i class="bi bi-building text-muted opacity-75"></i>' . e($namaGudang) . '</span>';
                })
                ->addColumn('tipe_badge', function ($adj) {
                    if ($adj->tipe_adjustment === 'Pengurangan') {
                        return '<span class="badge-tipe-pengurangan"><i class="bi bi-arrow-down-circle me-1"></i>Pengurangan</span>';
                    } else {
                        return '<span class="badge-tipe-penambahan"><i class="bi bi-arrow-up-circle me-1"></i>Penambahan</span>';
                    }
                })
                ->addColumn('alasan_badge', function ($adj) {
                    return '<span class="badge-alasan"><i class="bi bi-tag-fill me-1 opacity-75"></i>' . e($adj->kategori_alasan) . '</span>';
                })
                ->addColumn('status_badge', function ($adj) {
                    if ($adj->status === 'Draft') {
                        return '<span class="badge-draft"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    } elseif ($adj->status === 'Pending') {
                        return '<span class="badge-pending"><i class="bi bi-clock-history me-1"></i>Pending Approval</span>';
                    } elseif ($adj->status === 'Confirmed') {
                        return '<span class="badge-confirmed"><i class="bi bi-check-circle me-1"></i>Confirmed</span>';
                    } else {
                        return '<span class="badge bg-danger text-white px-2.5 py-1 rounded-pill"><i class="bi bi-x-circle me-1"></i>Rejected</span>';
                    }
                })
                ->addColumn('total_qty_fmt', function ($adj) {
                    $val = number_format($adj->total_qty, 4, ',', '.');
                    $formatted = str_contains($val, ',') ? rtrim(rtrim($val, '0'), ',') : $val;
                    return '<span class="fw-bold font-monospace text-body">' . $formatted . '</span>';
                })
                ->addColumn('total_nilai_fmt', function ($adj) use ($canSeeHpp) {
                    if (!$canSeeHpp) {
                        return '<span class="text-muted font-monospace">Rp ***</span>';
                    }
                    return '<span class="fw-bold font-monospace text-emerald">Rp ' . number_format($adj->total_nilai, 2, ',', '.') . '</span>';
                })
                ->addColumn('action', function ($adj) use ($canEdit, $canDelete, $user, $isSupervisor) {
                    $isCreator = ($adj->created_by == $user->id);

                    // Tombol Detail (selalu tampil)
                    $btnDetail = '<button class="btn-action-table btn-action-detail" onclick="showDetailAdjustment('.$adj->id.')" title="Lihat Detail Dokumen"><i class="bi bi-eye"></i></button>';

                    // Tombol Edit:
                    // - Draft: hanya pembuat
                    // - Pending: pembuat, user yang punya hak edit, atau Super Admin
                    // - Confirmed: HANYA Super Admin
                    $btnEdit = '';
                    if ($canEdit) {
                        if ($adj->isDraft() && $isCreator) {
                            $btnEdit = '<button class="btn-action-table btn-action-edit" onclick="editAdjustment('.$adj->id.')" title="Edit Dokumen"><i class="bi bi-pencil"></i></button>';
                        } elseif ($adj->isPending()) {
                            $btnEdit = '<button class="btn-action-table btn-action-edit" onclick="editAdjustment('.$adj->id.')" title="Edit Dokumen"><i class="bi bi-pencil"></i></button>';
                        } elseif ($adj->isConfirmed() && $isSupervisor) {
                            $btnEdit = '<button class="btn-action-table btn-action-edit" onclick="editAdjustment('.$adj->id.')" title="Edit Dokumen (Super Admin)"><i class="bi bi-pencil"></i></button>';
                        }
                    }

                    // Tombol Hapus:
                    // - Draft: hanya pembuat
                    // - Pending: HANYA Super Admin (user biasa tidak bisa hapus saat pending)
                    // - Confirmed: HANYA Super Admin (user biasa tidak bisa hapus saat confirmed)
                    $btnDelete = '';
                    if ($canDelete) {
                        if ($adj->isDraft() && $isCreator) {
                            $btnDelete = '<button class="btn-action-table btn-action-delete" onclick="hapusAdjustment('.$adj->id.', \''.$adj->nomor_adjustment.'\')" title="Hapus Dokumen"><i class="bi bi-trash3"></i></button>';
                        } elseif ($adj->isPending() && $isSupervisor) {
                            $btnDelete = '<button class="btn-action-table btn-action-delete" onclick="hapusAdjustment('.$adj->id.', \''.$adj->nomor_adjustment.'\')" title="Hapus Dokumen (Super Admin)"><i class="bi bi-trash3"></i></button>';
                        } elseif ($adj->isConfirmed() && $isSupervisor) {
                            $btnDelete = '<button class="btn-action-table btn-action-delete" onclick="hapusAdjustment('.$adj->id.', \''.$adj->nomor_adjustment.'\')" title="Hapus Dokumen (Super Admin)"><i class="bi bi-trash3"></i></button>';
                        }
                    }

                    return '<div class="d-inline-flex align-items-center gap-1">' . $btnDetail . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['nomor_adj_link', 'tanggal_fmt', 'gudang_name', 'tipe_badge', 'alasan_badge', 'status_badge', 'total_qty_fmt', 'total_nilai_fmt', 'action'])
                ->with('kpi', $kpiData)
                ->make(true);
        }

        $gudangQuery = MasterGudang::forActiveCompany()->where('status', 'Active');
        if (!empty($activeBranchId)) {
            $gudangQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangQuery->whereNull('cabang_id');
        }
        $gudangs = $gudangQuery->orderBy('nama_gudang')->get();

        $canSeeHpp = $isSupervisor || $user->can('lihat-hpp-stok-adjustment');

        // KPI: hitung hanya dokumen bulan berjalan yang visible untuk user ini
        $baseQuery = StockAdjustment::forActiveCompany()->forActiveBranch();
        $visibleQuery = (clone $baseQuery)->where(function($q) use ($user) {
            // Draft hanya terlihat oleh pembuat (creator)
            $q->where('status', '!=', 'Draft')->orWhere('created_by', $user->id);
        });

        $currentMonthParts = explode('-', date('Y-m'));
        $kpiInitialQuery = (clone $visibleQuery)
            ->whereYear('tanggal_adjustment', (int)$currentMonthParts[0])
            ->whereMonth('tanggal_adjustment', (int)$currentMonthParts[1]);

        $kpiRow = $kpiInitialQuery->selectRaw("
            COUNT(*) as total_doc,
            COALESCE(SUM(CASE WHEN tipe_adjustment = 'Pengurangan' THEN total_qty ELSE 0 END), 0) as total_pengurangan,
            COALESCE(SUM(CASE WHEN tipe_adjustment = 'Penambahan' THEN total_qty ELSE 0 END), 0) as total_penambahan,
            COALESCE(SUM(total_nilai), 0) as total_nilai
        ")->first();

        $kpiData = [
            'total_doc'        => (int)($kpiRow->total_doc ?? 0),
            'total_pengurangan'=> (float)($kpiRow->total_pengurangan ?? 0),
            'total_penambahan' => (float)($kpiRow->total_penambahan ?? 0),
            'total_nilai'      => $canSeeHpp ? (float)($kpiRow->total_nilai ?? 0) : 0,
            'can_see_hpp'      => $canSeeHpp,
        ];

        return view('modul_inventori.stok_ajusmen.viewajusmen', compact('gudangs', 'kpiData'));
    }

    // ══════════════════════════════════════════════════════════════
    // HELPER: Search Barang for Select2 / Dropdown
    // ══════════════════════════════════════════════════════════════
    public function searchBarang(Request $request)
    {
        $user = auth()->user();
        $isSupervisor = $user ? $user->hasAnyRole(['Super Admin', 'Owner']) : false;
        $canSeeHpp = $user ? ($isSupervisor || $user->can('lihat-hpp-stok-adjustment')) : false;

        $q = $request->q;
        $gudangId = $request->gudang_id;

        $query = MasterBarang::active()
            ->forActiveCompany()
            ->with(['satuan:id,nama_satuan', 'primaryFoto'])
            ->where(function($sub) use ($q) {
                if ($q) {
                    $sub->where('kode_barang', 'like', "%{$q}%")
                        ->orWhere('nama_barang', 'like', "%{$q}%");
                }
            })
            ->select('id', 'kode_barang', 'nama_barang', 'satuan_id', 'parent_id')
            ->take(100)
            ->get();

        $result = [];
        foreach ($query as $b) {
            $stokGudang = null;
            if ($gudangId) {
                $stokGudang = StokGudang::where('gudang_id', $gudangId)
                    ->where('barang_id', $b->id)
                    ->first();
            }

            $hpp = $stokGudang ? (float)$stokGudang->harga_pokok_rata_rata : 0;
            $stokAktual = $stokGudang ? (float)$stokGudang->stok_aktual : 0;

            $foto = $b->primaryFoto;
            $fotoUrl = ($foto && $foto->foto_path) ? asset('storage/' . $foto->foto_path) : null;

            $result[] = [
                'id'           => $b->id,
                'kode_barang'  => $b->kode_barang,
                'nama_barang'  => $b->nama_barang,
                'satuan_name'  => $b->satuan ? $b->satuan->nama_satuan : 'Pcs',
                'hpp_satuan'   => $canSeeHpp ? $hpp : 0,
                'stok_aktual'  => $stokAktual,
                'foto_url'     => $fotoUrl,
            ];
        }

        return response()->json($result);
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — Create Draft Adjustment
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-stok-adjustment')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk membuat Stock Adjustment.'], 403);
        }

        $request->validate([
            'gudang_id'          => 'required|exists:master_gudang,id',
            'tanggal_adjustment' => 'required|date',
            'tipe_adjustment'    => 'required|in:Pengurangan,Penambahan',
            'kategori_alasan'    => 'required|in:Barang Rusak,Barang Hilang,Barang Sampel/Promosi,Internal Use,Koreksi Input',
            'items'              => 'required|array|min:1|max:25',
            'items.*.barang_id'  => 'required|exists:master_barang,id',
            'items.*.jumlah'     => 'required|numeric|gt:0',
            'items.*.harga_satuan' => 'nullable|numeric|gte:0',
        ]);

        $gudang = $this->resolveGudang((int)$request->gudang_id);
        if (!$gudang) {
            return response()->json(['message' => 'Gudang tidak valid.'], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->auto_number) {
                $year   = date('Y', strtotime($request->tanggal_adjustment));
                $month  = date('m', strtotime($request->tanggal_adjustment));
                $prefix = "ADJ/{$year}/{$month}/";

                $latest = StockAdjustment::where('nomor_adjustment', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->orderBy('nomor_adjustment', 'desc')
                    ->first();

                $number   = !$latest ? 1 : ((int) substr($latest->nomor_adjustment, strlen($prefix))) + 1;
                $nomorAdj = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            } else {
                $request->validate([
                    'nomor_adjustment' => 'required|string|max:50|unique:stock_adjustments,nomor_adjustment',
                ]);
                $nomorAdj = $request->nomor_adjustment;
            }

            $submitAction = strtolower($request->input('submit_action', 'draft'));
            $statusToSet  = (in_array($submitAction, ['pending', 'pos', 'submit'])) ? 'Pending' : 'Draft';

            // Validasi Keamanan: Pengurangan Stok Tidak Boleh Melebihi Stok Aktual di Gudang
            if ($request->tipe_adjustment === 'Pengurangan') {
                foreach ($request->items as $item) {
                    $qty = round((float)$item['jumlah'], 4);
                    $stokAktual = (float)(StokGudang::where('gudang_id', $request->gudang_id)
                        ->where('barang_id', $item['barang_id'])
                        ->value('stok_aktual') ?? 0);

                    if ($qty > $stokAktual) {
                        $barang = MasterBarang::find($item['barang_id']);
                        $namaBarang = $barang ? $barang->nama_barang : 'Barang #' . $item['barang_id'];
                        DB::rollBack();
                        return response()->json([
                            'message' => "Stok barang '{$namaBarang}' tidak mencukupi untuk pengurangan! Stok tersedia saat ini: " . number_format($stokAktual, 4, ',', '.') . ", jumlah pengurangan diminta: " . number_format($qty, 4, ',', '.') . ". Stok gudang tidak boleh bernilai negatif/minus."
                        ], 422);
                    }
                }
            }

            $totalQty   = 0;
            $totalNilai = 0;
            $detailsData = [];

            foreach ($request->items as $item) {
                $qty = round((float)$item['jumlah'], 4);

                // Proteksi HPP: Jika sistem/gudang sudah memiliki HPP (> 0), kunci HPP tersebut. Jika 0, gunakan HPP yang diinput.
                $stokGudang = StokGudang::where('gudang_id', $request->gudang_id)
                    ->where('barang_id', $item['barang_id'])
                    ->first();
                $systemHpp = $stokGudang ? (float)$stokGudang->harga_pokok_rata_rata : 0;
                if ($systemHpp <= 0) {
                    $masterBarang = MasterBarang::find($item['barang_id']);
                    $systemHpp = $masterBarang ? (float)$masterBarang->hpp : 0;
                }

                $harga      = ($systemHpp > 0) ? round($systemHpp, 6) : round((float)($item['harga_satuan'] ?? 0), 6);

                if ($request->tipe_adjustment === 'Penambahan' && $harga <= 0) {
                    $barang = MasterBarang::find($item['barang_id']);
                    $namaBarang = $barang ? $barang->nama_barang : 'Barang #' . $item['barang_id'];
                    DB::rollBack();
                    return response()->json([
                        'message' => "HPP / Harga Satuan untuk barang '{$namaBarang}' tidak boleh Rp 0 pada penyesuaian penambahan barang masuk! Silakan masukkan nilai HPP yang valid."
                    ], 422);
                }

                $nilaiTotal = round($qty * $harga, 4);

                // Akumulasi total header
                $totalQty   += $qty;
                $totalNilai += $nilaiTotal;

                $detailsData[] = [
                    'barang_id'         => $item['barang_id'],
                    'jumlah'            => $qty,
                    'harga_satuan'      => $harga,
                    'nilai_total'       => $nilaiTotal,
                    'keterangan_detail' => $item['keterangan_detail'] ?? null,
                ];
            }

            $activeBranchId  = session('active_branch_id');
            $activeCompanyId = session('active_company_id');

            $docBranchId  = !empty($activeBranchId) ? $activeBranchId : null;
            $docCompanyId = !empty($activeBranchId) ? null : ($activeCompanyId ?: ($gudang->company_id ?? 1));

            $adj = StockAdjustment::create([
                'nomor_adjustment'   => $nomorAdj,
                'tanggal_adjustment' => $request->tanggal_adjustment,
                'gudang_id'          => $request->gudang_id,
                'company_id'         => $docCompanyId,
                'branch_id'          => $docBranchId,
                'tipe_adjustment'    => $request->tipe_adjustment,
                'kategori_alasan'    => $request->kategori_alasan,
                'keterangan'         => $request->keterangan,
                'status'             => $statusToSet,
                'total_qty'          => $totalQty,
                'total_nilai'        => $totalNilai,
            ]);

            foreach ($detailsData as $detail) {
                $detail['stock_adjustment_id'] = $adj->id;
                StockAdjustmentDetail::create($detail);
            }

            DB::commit();

            if ($statusToSet === 'Pending') {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendStockAdjustmentPendingNotification($adj)
                );
            }

            $msg = ($statusToSet === 'Pending') 
                ? "Dokumen Penyesuaian Stok {$nomorAdj} berhasil diajukan! Status PENDING (Menunggu Persetujuan)."
                : "Draft Penyesuaian Stok {$nomorAdj} berhasil disimpan!";

            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'id'      => $adj->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StockAdjustment Store Exception: ', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan server saat menyimpan data.'], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW — Detail Payload + Automatic Journal Preview
    // ══════════════════════════════════════════════════════════════
    public function show(StockAdjustment $stockAdjustment)
    {
        $user        = auth()->user();
        $isSupervisor = $user->hasAnyRole(['Super Admin', 'Owner']);

        if (!$isSupervisor && !$user->can('lihat-stok-adjustment')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$this->resolveAdjustment($stockAdjustment)) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        // Draft hanya boleh dilihat oleh pembuatnya
        if ($stockAdjustment->isDraft() && $stockAdjustment->created_by !== $user->id) {
            return response()->json(['message' => 'Dokumen Draft hanya bisa dilihat oleh pembuatnya.'], 403);
        }

        $stockAdjustment->load([
            'gudang',
            'creator',
            'approver',
            'details.barang.satuan',
            'details.barang.primaryFoto',
        ]);

        $canSeeHpp    = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-stok-adjustment');
        $canSeeJurnal = $user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-jurnal-stok-adjustment');

        $jurnal = null;
        if ($canSeeJurnal) {
            $jurnal = Jurnal::with(['details.account'])
                ->where('referensi_tipe', 'Penyesuaian Stok')
                ->where('referensi_id', $stockAdjustment->id)
                ->first();
        }

        if (!$canSeeHpp) {
            $stockAdjustment->total_nilai = 0;
            foreach ($stockAdjustment->details as $d) {
                $d->harga_satuan = 0;
                $d->nilai_total  = 0;
            }
        }

        return response()->json([
            'adjustment'     => $stockAdjustment,
            'jurnal'         => $jurnal,
            'can_see_hpp'    => $canSeeHpp,
            'can_see_jurnal' => $canSeeJurnal,
        ]);
    }

    public function edit(StockAdjustment $stockAdjustment)
    {
        $user        = auth()->user();
        $isSupervisor = $user->hasAnyRole(['Super Admin', 'Owner']);

        if (!$isSupervisor && !$user->can('edit-stok-adjustment')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengubah Stock Adjustment.'], 403);
        }

        if (!$this->resolveAdjustment($stockAdjustment)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!$stockAdjustment->isDraft() && !$stockAdjustment->isPending() && !$isSupervisor) {
            return response()->json(['message' => 'Hanya dokumen Draft atau Pending yang dapat diedit.'], 422);
        }

        // Draft hanya bisa diedit oleh pembuatnya
        if ($stockAdjustment->isDraft() && $stockAdjustment->created_by !== $user->id) {
            return response()->json(['message' => 'Draft hanya bisa diedit oleh pembuatnya.'], 403);
        }

        $stockAdjustment->load(['gudang:id,nama_gudang', 'details.barang.satuan', 'details.barang.primaryFoto']);

        $canSeeHpp = $isSupervisor || $user->can('lihat-hpp-stok-adjustment');

        foreach ($stockAdjustment->details as $d) {
            $stokGudang = StokGudang::where('gudang_id', $stockAdjustment->gudang_id)
                ->where('barang_id', $d->barang_id)
                ->first();
            $d->stok_aktual = $stokGudang ? (float)$stokGudang->stok_aktual : 0;
            if (!$canSeeHpp) {
                $d->harga_satuan = 0;
                $d->nilai_total  = 0;
            }
        }

        if (!$canSeeHpp) {
            $stockAdjustment->total_nilai = 0;
        }

        return response()->json($stockAdjustment);
    }

    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        $user         = auth()->user();
        $isSupervisor = $user->hasAnyRole(['Super Admin', 'Owner']);

        if (!$isSupervisor && !$user->can('edit-stok-adjustment')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengubah Stock Adjustment.'], 403);
        }

        if (!$this->resolveAdjustment($stockAdjustment)) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        if ($stockAdjustment->isConfirmed() && !$isSupervisor) {
            return response()->json(['message' => 'Dokumen yang sudah disetujui tidak dapat diubah.'], 422);
        }

        // Draft hanya bisa di-update oleh pembuatnya
        if ($stockAdjustment->isDraft() && $stockAdjustment->created_by !== $user->id) {
            return response()->json(['message' => 'Draft hanya bisa diubah oleh pembuatnya.'], 403);
        }

        $request->validate([
            'tanggal_adjustment' => 'required|date',
            'tipe_adjustment'    => 'required|in:Pengurangan,Penambahan',
            'kategori_alasan'    => 'required|in:Barang Rusak,Barang Hilang,Barang Sampel/Promosi,Internal Use,Koreksi Input',
            'items'              => 'required|array|min:1|max:25',
            'items.*.barang_id'  => 'required|exists:master_barang,id',
            'items.*.jumlah'     => 'required|numeric|gt:0',
            'items.*.harga_satuan' => 'nullable|numeric|gte:0',
        ]);

        try {
            DB::beginTransaction();

            $submitAction = strtolower($request->input('submit_action', strtolower($stockAdjustment->status)));
            $statusToSet  = (in_array($submitAction, ['pending', 'pos', 'submit'])) ? 'Pending' : 'Draft';

            // Validasi Keamanan: Pengurangan Stok Tidak Boleh Melebihi Stok Aktual di Gudang
            if ($request->tipe_adjustment === 'Pengurangan') {
                foreach ($request->items as $item) {
                    $qty = round((float)$item['jumlah'], 4);
                    $stokAktual = (float)(StokGudang::where('gudang_id', $stockAdjustment->gudang_id)
                        ->where('barang_id', $item['barang_id'])
                        ->value('stok_aktual') ?? 0);

                    if ($qty > $stokAktual) {
                        $barang = MasterBarang::find($item['barang_id']);
                        $namaBarang = $barang ? $barang->nama_barang : 'Barang #' . $item['barang_id'];
                        DB::rollBack();
                        return response()->json([
                            'message' => "Stok barang '{$namaBarang}' tidak mencukupi untuk pengurangan! Stok tersedia saat ini: " . number_format($stokAktual, 4, ',', '.') . ", jumlah pengurangan diminta: " . number_format($qty, 4, ',', '.') . ". Stok gudang tidak boleh bernilai negatif/minus."
                        ], 422);
                    }
                }
            }

            $stockAdjustment->details()->delete();

            $totalQty   = 0;
            $totalNilai = 0;

            foreach ($request->items as $item) {
                $qty = round((float)$item['jumlah'], 4);

                // Proteksi HPP: Jika sistem/gudang sudah memiliki HPP (> 0), kunci HPP tersebut. Jika 0, gunakan HPP yang diinput.
                $stokGudang = StokGudang::where('gudang_id', $stockAdjustment->gudang_id)
                    ->where('barang_id', $item['barang_id'])
                    ->first();
                $systemHpp = $stokGudang ? (float)$stokGudang->harga_pokok_rata_rata : 0;
                if ($systemHpp <= 0) {
                    $masterBarang = MasterBarang::find($item['barang_id']);
                    $systemHpp = $masterBarang ? (float)$masterBarang->hpp : 0;
                }

                $harga      = ($systemHpp > 0) ? round($systemHpp, 6) : round((float)($item['harga_satuan'] ?? 0), 6);

                if ($request->tipe_adjustment === 'Penambahan' && $harga <= 0) {
                    $barang = MasterBarang::find($item['barang_id']);
                    $namaBarang = $barang ? $barang->nama_barang : 'Barang #' . $item['barang_id'];
                    DB::rollBack();
                    return response()->json([
                        'message' => "HPP / Harga Satuan untuk barang '{$namaBarang}' tidak boleh Rp 0 pada penyesuaian penambahan barang masuk! Silakan masukkan nilai HPP yang valid."
                    ], 422);
                }

                $nilaiTotal = round($qty * $harga, 4);

                $totalQty   += $qty;
                $totalNilai += $nilaiTotal;

                StockAdjustmentDetail::create([
                    'stock_adjustment_id' => $stockAdjustment->id,
                    'barang_id'           => $item['barang_id'],
                    'jumlah'              => $qty,
                    'harga_satuan'        => $harga,
                    'nilai_total'         => $nilaiTotal,
                    'keterangan_detail'   => $item['keterangan_detail'] ?? null,
                ]);
            }

            $stockAdjustment->update([
                'tanggal_adjustment' => $request->tanggal_adjustment,
                'tipe_adjustment'    => $request->tipe_adjustment,
                'kategori_alasan'    => $request->kategori_alasan,
                'keterangan'         => $request->keterangan,
                'status'             => $statusToSet,
                'total_qty'          => $totalQty,
                'total_nilai'        => $totalNilai,
            ]);

            DB::commit();

            if ($statusToSet === 'Pending') {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendStockAdjustmentPendingNotification($stockAdjustment)
                );
            }

            $msg = ($statusToSet === 'Pending')
                ? "Dokumen adjustment {$stockAdjustment->nomor_adjustment} berhasil diajukan (Pending Approval)!"
                : "Draft adjustment {$stockAdjustment->nomor_adjustment} berhasil diperbarui!";

            return response()->json([
                'status'  => 'success',
                'message' => $msg,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StockAdjustment Update Exception: ', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan server saat memperbarui data.'], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // CONFIRM — Post Adjustment to Inventory & Accounting Journal
    // ══════════════════════════════════════════════════════════════
    public function confirm(StockAdjustment $stockAdjustment)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('approve-stok-adjustment')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menyetujui Stock Adjustment.'], 403);
        }

        if (!$this->resolveAdjustment($stockAdjustment) || $stockAdjustment->isConfirmed()) {
            return response()->json(['message' => 'Dokumen tidak dapat dikonfirmasi.'], 422);
        }

        // Draft hanya boleh diakses/diajukan oleh pembuatnya
        if ($stockAdjustment->isDraft() && $stockAdjustment->created_by !== $user->id) {
            return response()->json(['message' => 'Dokumen Draft hanya dapat diakses oleh pembuatnya.'], 403);
        }

        try {
            DB::beginTransaction();

            $stockAdjustment = StockAdjustment::where('id', $stockAdjustment->id)->lockForUpdate()->first();
            if ($stockAdjustment->isConfirmed()) {
                DB::rollBack();
                return response()->json(['message' => 'Status transaksi sudah dikonfirmasi.'], 422);
            }

            $gudangId = $stockAdjustment->gudang_id;
            $nowStr   = now()->toDateTimeString();

            foreach ($stockAdjustment->details as $detail) {
                $barangId = $detail->barang_id;
                $qty      = (float)$detail->jumlah;
                $harga    = (float)$detail->harga_satuan;

                $stokGudang = StokGudang::where('gudang_id', $gudangId)
                    ->where('barang_id', $barangId)
                    ->lockForUpdate()
                    ->first();

                if (!$stokGudang) {
                    $stokGudang = StokGudang::create([
                        'barang_id'             => $barangId,
                        'gudang_id'             => $gudangId,
                        'stok_aktual'           => 0.00,
                        'nilai_persediaan'      => 0.0000,
                        'harga_pokok_rata_rata' => $harga,
                        'harga_beli_terakhir'   => $harga,
                    ]);
                    $stokGudang = StokGudang::where('id', $stokGudang->id)->lockForUpdate()->first();
                }

                $stokAwal  = (float)$stokGudang->stok_aktual;
                $isTambah  = $stockAdjustment->tipe_adjustment === 'Penambahan';
                $stokAkhir = $isTambah ? ($stokAwal + $qty) : ($stokAwal - $qty);

                if (!$isTambah && $stokAkhir < 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Stok barang '{$detail->barang->nama_barang}' di gudang ini tidak mencukupi untuk pengurangan (Stok saat ini: {$stokAwal}).",
                    ], 422);
                }

                if ($isTambah) {
                    $currHpp = (float)$stokGudang->harga_pokok_rata_rata;
                    if ($currHpp <= 0 || $stokAwal <= 0) {
                        $newHpp = ($harga > 0) ? $harga : $currHpp;
                    } else {
                        // Weighted Moving Average Cost
                        $newHpp = (($stokAwal * $currHpp) + ($qty * $harga)) / ($stokAkhir > 0 ? $stokAkhir : 1);
                    }
                    $stokGudang->harga_pokok_rata_rata = $newHpp;
                    if ($harga > 0) {
                        $stokGudang->harga_beli_terakhir = $harga;
                    }
                }

                $stokGudang->stok_aktual      = $stokAkhir;
                $stokGudang->nilai_persediaan = round($stokAkhir * (float)$stokGudang->harga_pokok_rata_rata, 4);
                $stokGudang->save();

                StokGudang::syncMasterHpp($barangId);

                // Record KartuStok
                KartuStok::create([
                    'barang_id'       => $barangId,
                    'gudang_id'       => $gudangId,
                    'tipe'            => $isTambah ? 'Masuk' : 'Keluar',
                    'jumlah'          => $qty,
                    'stok_awal'       => $stokAwal,
                    'stok_akhir'      => $stokAkhir,
                    'harga_satuan'    => $harga,
                    'nilai_total'     => $qty * $harga,
                    'referensi_jenis' => 'Penyesuaian Stok',
                    'referensi_id'    => $stockAdjustment->id,
                    'referensi_nomor' => $stockAdjustment->nomor_adjustment,
                    'keterangan'      => "Adjustment ({$stockAdjustment->kategori_alasan}): " . ($stockAdjustment->keterangan ?? '-'),
                    'created_at'      => $nowStr,
                ]);
            }

            $stockAdjustment->update([
                'status'         => 'Confirmed',
                'disetujui_oleh' => auth()->id(),
                'disetujui_pada' => now(),
            ]);

            // Post Automatic Accounting Journal
            $this->postJournalForAdjustment($stockAdjustment);

            DB::commit();

            $approverUser = auth()->user();
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendStockAdjustmentConfirmedNotification($stockAdjustment, $approverUser)
            );

            return response()->json([
                'status'  => 'success',
                'message' => "Penyesuaian stok {$stockAdjustment->nomor_adjustment} berhasil dikonfirmasi dan jurnal otomatis telah terbentuk!",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('StockAdjustment Confirm Exception: ', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan server saat konfirmasi.'], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY — Delete Draft Adjustment
    // ══════════════════════════════════════════════════════════════
    public function destroy(StockAdjustment $stockAdjustment)
    {
        $user         = auth()->user();
        $isSupervisor = $user->hasAnyRole(['Super Admin', 'Owner']);

        if (!$isSupervisor && !$user->can('hapus-stok-adjustment')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menghapus Stock Adjustment.'], 403);
        }

        if (!$this->resolveAdjustment($stockAdjustment)) {
            return response()->json(['message' => 'Dokumen tidak ditemukan.'], 404);
        }

        // Draft hanya bisa dihapus oleh pembuatnya
        if ($stockAdjustment->isDraft() && $stockAdjustment->created_by !== $user->id) {
            return response()->json(['message' => 'Draft hanya bisa dihapus oleh pembuatnya.'], 403);
        }

        // Dokumen Pending dan Confirmed hanya bisa dihapus oleh Super Admin
        if (($stockAdjustment->isPending() || $stockAdjustment->isConfirmed()) && !$isSupervisor) {
            return response()->json(['message' => 'Dokumen berstatus ' . $stockAdjustment->status . ' tidak dapat dihapus.'], 403);
        }

        try {
            DB::beginTransaction();

            // Jika status Confirmed dihapus oleh Super Admin, revert stok gudang dan hapus KartuStok & Jurnal
            if ($stockAdjustment->isConfirmed()) {
                $gudangId = $stockAdjustment->gudang_id;
                $isTambah = ($stockAdjustment->tipe_adjustment === 'Penambahan');

                foreach ($stockAdjustment->details as $detail) {
                    $stokGudang = StokGudang::where('gudang_id', $gudangId)
                        ->where('barang_id', $detail->barang_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stokGudang) {
                        $qty = (float)$detail->jumlah;
                        $stokAwal = (float)$stokGudang->stok_aktual;
                        $stokReverted = $isTambah ? ($stokAwal - $qty) : ($stokAwal + $qty);
                        $stokGudang->stok_aktual = $stokReverted;
                        $stokGudang->nilai_persediaan = round($stokReverted * (float)$stokGudang->harga_pokok_rata_rata, 4);
                        $stokGudang->save();

                        StokGudang::syncMasterHpp($detail->barang_id);
                    }
                }

                KartuStok::where('referensi_jenis', 'Penyesuaian Stok')
                    ->where('referensi_id', $stockAdjustment->id)
                    ->delete();

                $jurnal = Jurnal::where('referensi_tipe', 'Penyesuaian Stok')
                    ->where('referensi_id', $stockAdjustment->id)
                    ->first();
                if ($jurnal) {
                    JurnalDetail::where('jurnal_id', $jurnal->id)->delete();
                    $jurnal->delete();
                }
            }

            $stockAdjustment->details()->delete();
            $stockAdjustment->delete();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Dokumen {$stockAdjustment->nomor_adjustment} berhasil dihapus.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StockAdjustment Destroy Exception: ', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menghapus dokumen.'], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // PRINT PDF — Print Adjustment Document
    // ══════════════════════════════════════════════════════════════
    public function printPdf(StockAdjustment $stockAdjustment)
    {
        if (!$this->resolveAdjustment($stockAdjustment)) {
            abort(404);
        }

        $user = auth()->user();
        if ($stockAdjustment->isDraft() && (!$user || $stockAdjustment->created_by !== $user->id)) {
            abort(403, 'Dokumen Draft hanya bisa diakses oleh pembuatnya.');
        }

        $stockAdjustment->load([
            'company',
            'branch.company',
            'gudang.cabang.company',
            'creator.position',
            'creator.roles',
            'creator.activeSignature',
            'approver.position',
            'approver.roles',
            'approver.activeSignature',
            'details.barang.satuan',
            'details.barang.primaryFoto',
        ]);

        // Resolving Active Entity (PT vs Cabang)
        $activeBranchId  = session('active_branch_id') ?? $stockAdjustment->branch_id;
        $activeCompanyId = session('active_company_id') ?? $stockAdjustment->company_id;

        $activeBranch = null;
        if ($activeBranchId) {
            $activeBranch = $stockAdjustment->branch ?? \App\Models\Branch::with('company')->find($activeBranchId);
        }

        $activeCompany = null;
        if ($activeBranch && $activeBranch->company) {
            $activeCompany = $activeBranch->company;
        } elseif ($activeCompanyId) {
            $activeCompany = $stockAdjustment->company ?? \App\Models\Company::find($activeCompanyId);
        }

        $isBranchActive = !empty($activeBranch);
        $entityTitle    = $isBranchActive ? ($activeBranch->nama_cabang ?: session('active_branch_name', 'Cabang')) : ($activeCompany ? $activeCompany->nama_perusahaan : session('active_company_name', 'PT PERSADA AMAN SENTOSA'));
        $entitySubtitle = null; // Dihilangkan sesuai permintaan user

        // Logo: Jika Cabang aktif maka ambil logo cabang (jika tidak ada maka kosongkan). Jika PT aktif maka ambil logo PT (jika tidak ada maka kosongkan).
        $appLogoUrl = null;
        $rawLogo = $isBranchActive ? ($activeBranch->logo ?? null) : ($activeCompany?->logo ?? null);
        if ($rawLogo) {
            $cleanLogo = ltrim($rawLogo, '/');
            if (file_exists(public_path('storage/' . $cleanLogo))) {
                $appLogoUrl = asset('storage/' . $cleanLogo);
            } elseif (file_exists(public_path($cleanLogo))) {
                $appLogoUrl = asset($cleanLogo);
            } elseif (filter_var($rawLogo, FILTER_VALIDATE_URL)) {
                $appLogoUrl = $rawLogo;
            }
        }

        $user       = auth()->user();
        $canSeeHpp  = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-stok-adjustment')) : false;

        return view('modul_inventori.stok_ajusmen.printajusmen', compact(
            'stockAdjustment',
            'appLogoUrl',
            'canSeeHpp',
            'isBranchActive',
            'entityTitle',
            'entitySubtitle'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE: Automatic Journal Posting Helper
    // ══════════════════════════════════════════════════════════════
    private function postJournalForAdjustment(StockAdjustment $adj): void
    {
        $totalNilai = (float)$adj->total_nilai;
        if ($totalNilai <= 0) return;

        // Prevent duplicate journal creation if already posted
        $existingJurnal = Jurnal::where('referensi_tipe', 'Penyesuaian Stok')
            ->where('referensi_id', $adj->id)
            ->first();
        if ($existingJurnal) return;

        $rawBranchId = $adj->branch_id !== null ? $adj->branch_id : ($adj->gudang ? $adj->gudang->cabang_id : session('active_branch_id'));
        $isBranch    = !empty($rawBranchId);

        // Strict isolation: Cabang (company_id = NULL, branch_id = ID Cabang), PT (company_id = ID PT, branch_id = NULL)
        $coaCompanyId    = $adj->company_id ?: (session('active_company_id') ?? auth()->user()?->company_id ?? ($isBranch ? \App\Models\Branch::where('id', $rawBranchId)->value('company_id') : 1));
        $jurnalCompanyId = $isBranch ? null : $coaCompanyId;
        $jurnalBranchId  = $isBranch ? $rawBranchId : null;

        $adj->load('details.barang');

        $debitGroup  = [];
        $kreditGroup = [];
        $isTambah    = ($adj->tipe_adjustment === 'Penambahan');

        foreach ($adj->details as $detail) {
            $barang     = $detail->barang;
            $catId      = $barang ? $barang->kategori_barang_id : null;
            $itemNilai  = (float)$detail->nilai_total;
            if ($itemNilai <= 0) continue;

            // 1. Akun Persediaan: Item Spesifik -> Kategori Barang Spesifik -> Default Inventory -> Fallback COA Search
            $barangId        = $barang ? $barang->id : null;
            $persediaanCoaId = $barangId ? AccountMappingHelper::getCoaId('inventory_item', "item_{$barangId}_inventory", null, $coaCompanyId) : null;
            if (!$persediaanCoaId && $catId) {
                $persediaanCoaId = AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory", null, $coaCompanyId);
            }
            if (!$persediaanCoaId) {
                $persediaanCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory', null, $coaCompanyId);
            }
            if (!$persediaanCoaId) {
                $persediaanCoa = ChartOfAccount::where('company_id', $coaCompanyId)
                    ->where('is_header', false)
                    ->where(function($q) {
                        $q->where('code', 'like', '1105%')
                          ->orWhere('name', 'like', '%Persediaan%');
                    })
                    ->first();
                $persediaanCoaId = $persediaanCoa ? $persediaanCoa->id : 1;
            }

            // 2. Akun Beban/Kontra Adjustment: Item Spesifik -> Kategori Barang Spesifik -> Fallback Alasan -> Default Adjustment -> Fallback COA Search
            $bebanMappingKey = 'default_stock_adjustment';
            if ($adj->kategori_alasan === 'Barang Rusak') {
                $bebanMappingKey = 'expense_damaged_goods';
            } elseif ($adj->kategori_alasan === 'Barang Hilang') {
                $bebanMappingKey = 'expense_lost_goods';
            } elseif ($adj->kategori_alasan === 'Barang Sampel/Promosi') {
                $bebanMappingKey = 'expense_promotion_samples';
            } elseif ($adj->kategori_alasan === 'Internal Use') {
                $bebanMappingKey = 'expense_internal_use';
            }

            $bebanCoaId = $barangId ? AccountMappingHelper::getCoaId('inventory_item', "item_{$barangId}_adjustment", null, $coaCompanyId) : null;
            if (!$bebanCoaId && $catId) {
                $bebanCoaId = AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_adjustment", null, $coaCompanyId);
            }
            if (!$bebanCoaId) {
                $bebanCoaId = AccountMappingHelper::getCoaId('default', $bebanMappingKey, 'default_stock_adjustment', $coaCompanyId);
            }
            if (!$bebanCoaId) {
                $bebanCoa = ChartOfAccount::where('company_id', $coaCompanyId)
                    ->where('is_header', false)
                    ->where(function($q) {
                        $q->where('code', 'like', '5104%')
                          ->orWhere('name', 'like', '%Selisih%')
                          ->orWhere('name', 'like', '%Beban%');
                    })
                    ->first();
                $bebanCoaId = $bebanCoa ? $bebanCoa->id : 2;
            }

            if ($isTambah) {
                // Penambahan: Debit Persediaan, Kredit Beban/Penyesuaian
                $debitGroup[$persediaanCoaId] = ($debitGroup[$persediaanCoaId] ?? 0) + $itemNilai;
                $kreditGroup[$bebanCoaId]     = ($kreditGroup[$bebanCoaId] ?? 0) + $itemNilai;
            } else {
                // Pengurangan: Debit Beban/Penyesuaian, Kredit Persediaan
                $debitGroup[$bebanCoaId]     = ($debitGroup[$bebanCoaId] ?? 0) + $itemNilai;
                $kreditGroup[$persediaanCoaId] = ($kreditGroup[$persediaanCoaId] ?? 0) + $itemNilai;
            }
        }

        if (empty($debitGroup) || empty($kreditGroup)) return;

        $year   = date('Y', strtotime($adj->tanggal_adjustment));
        $month  = date('m', strtotime($adj->tanggal_adjustment));
        $prefix = "JNL/{$year}/{$month}/";

        $latestJnlQuery = Jurnal::withTrashed()->where('nomor_jurnal', 'like', $prefix . '%');
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

        $jurnal = Jurnal::create([
            'nomor_jurnal'    => $nomorJurnal,
            'tanggal_jurnal'  => $adj->tanggal_adjustment,
            'referensi_tipe'  => 'Penyesuaian Stok',
            'referensi_id'    => $adj->id,
            'referensi_nomor' => $adj->nomor_adjustment,
            'keterangan'      => "Jurnal Penyesuaian Stok ({$adj->kategori_alasan}) - No: {$adj->nomor_adjustment}",
            'total_debit'     => $totalNilai,
            'total_kredit'    => $totalNilai,
            'company_id'      => $jurnalCompanyId,
            'branch_id'       => $jurnalBranchId,
            'created_by'      => auth()->id(),
        ]);

        foreach ($debitGroup as $coaId => $nilai) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $coaId,
                'debit'      => $nilai,
                'kredit'     => 0,
                'keterangan' => $isTambah ? 'Penambahan Persediaan Barang' : "Beban Penyesuaian Stok ({$adj->kategori_alasan})",
            ]);
        }

        foreach ($kreditGroup as $coaId => $nilai) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $coaId,
                'debit'      => 0,
                'kredit'     => $nilai,
                'keterangan' => $isTambah ? "Penyesuaian Masuk ({$adj->kategori_alasan})" : 'Pengurangan Persediaan Barang',
            ]);
        }
    }
}
