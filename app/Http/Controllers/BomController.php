<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\BomItem;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\MasterGudang;
use App\Models\Tender;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BomController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // LAYER 1: PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════

    private function resolveBom(Bom $bom): bool
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang: BOM harus milik cabang tersebut dan company_id IS NULL
            if ($bom->branch_id != $activeBranchId || !is_null($bom->company_id)) {
                return false;
            }
        } else {
            // Aktif sebagai PT Pusat: BOM harus branch_id IS NULL dan company_id sesuai PT aktif
            if (!is_null($bom->branch_id)) {
                return false;
            }
            if ($activeCompanyId && $bom->company_id != $activeCompanyId) {
                return false;
            }
        }

        // Rule: Status Draft HANYA bisa dilihat & diakses oleh user yang membuatnya
        $user = auth()->user();
        if ($bom->status === 'Draft') {
            if (!$user || $bom->created_by != $user->id) {
                return false;
            }
        }

        return true;
    }

    private function generateKodeBom(): string
    {
        $prefix = 'BOM/' . date('Y/m/');

        $lastBom = Bom::withTrashed()
            ->forActiveCompany()
            ->where('kode_bom', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastBom) {
            $parts = explode('/', $lastBom->kode_bom);
            $lastNum = (int)end($parts);
            $nextSeq = max($lastNum + 1, 1);
        }

        // Loop safety: pastikan kode_bom 100% unik di seluruh tabel (termasuk withTrashed)
        do {
            $candidateCode = $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
            $exists = Bom::withTrashed()->where('kode_bom', $candidateCode)->exists();
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        return $candidateCode;
    }

    public function generateKode()
    {
        return response()->json([
            'kode_bom' => $this->generateKodeBom()
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX — BOM List (AJAX DataTables + Blade View)
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $this->authorize('lihat-bom');

        $user            = auth()->user();
        $activeBranchId  = session('active_branch_id');
        $isSupervisor    = $user ? $user->hasAnyRole(['Super Admin', 'Owner']) : false;

        if ($request->ajax()) {
            // 1. Base Query for KPI Aggregates (Strict Group By Safe)
            // Rule: status Draft HANYA bisa dilihat oleh user pembuatnya (created_by)
            $kpiQuery = Bom::forActiveCompany()
                ->forActiveBranch()
                ->visibleToUser($user ? $user->id : 0);

            if ($request->filled('status')) {
                $kpiQuery->where('status', $request->status);
            }
            if ($request->filled('tipe_bom')) {
                $kpiQuery->where('tipe_bom', $request->tipe_bom);
            }
            // Filter Periode / Bulan
            if ($request->filled('periode') && $request->periode !== 'all') {
                $parts = explode('-', $request->periode);
                if (count($parts) === 2) {
                    $kpiQuery->whereYear('boms.created_at', (int)$parts[0])
                             ->whereMonth('boms.created_at', (int)$parts[1]);
                }
            } elseif ($request->filled('bulan') && $request->filled('tahun')) {
                $kpiQuery->whereYear('boms.created_at', (int)$request->tahun)
                         ->whereMonth('boms.created_at', (int)$request->bulan);
            } elseif ($request->filled('tahun')) {
                $kpiQuery->whereYear('boms.created_at', (int)$request->tahun);
            }
            // Gunakan is_numeric dan cek keberadaan key di request, bukan filled()
            // agar tidak tertipu nilai '0' yang dikirim JS ketika input kosong
            if ($request->has('min_hpp') && is_numeric($request->min_hpp)) {
                $kpiQuery->where('total_estimasi_hpp', '>=', (float)$request->min_hpp);
            }
            if ($request->has('max_hpp') && is_numeric($request->max_hpp) && (float)$request->max_hpp > 0) {
                $kpiQuery->where('total_estimasi_hpp', '<=', (float)$request->max_hpp);
            }

            $kpiRow = $kpiQuery->selectRaw("
                COUNT(*) as total_bom,
                COALESCE(SUM(CASE WHEN status IN ('Approved', 'Active') THEN 1 ELSE 0 END), 0) as total_approved,
                COALESCE(SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END), 0) as total_pending,
                COALESCE(SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END), 0) as total_draft,
                COALESCE(SUM(CASE WHEN tender_id IS NOT NULL THEN 1 ELSE 0 END), 0) as total_tender,
                COALESCE(SUM(CASE WHEN tender_id IS NULL THEN 1 ELSE 0 END), 0) as total_stok,
                COALESCE(SUM(total_estimasi_hpp), 0) as total_hpp_accumulated
            ")->first();

            $canViewHpp = $isSupervisor || $user->can('lihat-hpp-bom') || $user->can('lihat-hpp-stok');

            $kpiData = [
                'total_bom'       => (int)($kpiRow->total_bom ?? 0),
                'total_active'    => (int)($kpiRow->total_approved ?? 0),
                'total_pending'   => (int)($kpiRow->total_pending ?? 0),
                'total_draft'     => (int)($kpiRow->total_draft ?? 0),
                'total_tender'    => (int)($kpiRow->total_tender ?? 0),
                'total_stok'      => (int)($kpiRow->total_stok ?? 0),
                'total_hpp_avg'   => $canViewHpp ? (($kpiRow && $kpiRow->total_bom > 0) ? round((float)$kpiRow->total_hpp_accumulated / $kpiRow->total_bom) : 0) : null,
                'can_view_hpp'    => $canViewHpp,
            ];

            // 2. DataTables Query
            // Rule: status Draft HANYA bisa dilihat oleh user pembuatnya (created_by)
            $query = Bom::forActiveCompany()
                ->forActiveBranch()
                ->visibleToUser($user ? $user->id : 0);

            $query->with([
                'barangJadi:id,kode_barang,nama_barang,satuan_id',
                'barangJadi.satuan:id,nama_satuan',
                'satuanHasil:id,nama_satuan',
                'creator:id,nama_lengkap,username',
                'approver:id,nama_lengkap,username',
            ])
            ->select('boms.*');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('tipe_bom')) {
                $query->where('tipe_bom', $request->tipe_bom);
            }
            // Filter Periode / Bulan
            if ($request->filled('periode') && $request->periode !== 'all') {
                $parts = explode('-', $request->periode);
                if (count($parts) === 2) {
                    $query->whereYear('boms.created_at', (int)$parts[0])
                          ->whereMonth('boms.created_at', (int)$parts[1]);
                }
            } elseif ($request->filled('bulan') && $request->filled('tahun')) {
                $query->whereYear('boms.created_at', (int)$request->tahun)
                      ->whereMonth('boms.created_at', (int)$request->bulan);
            } elseif ($request->filled('tahun')) {
                $query->whereYear('boms.created_at', (int)$request->tahun);
            }
            if ($request->has('min_hpp') && is_numeric($request->min_hpp)) {
                $query->where('total_estimasi_hpp', '>=', (float)$request->min_hpp);
            }
            if ($request->has('max_hpp') && is_numeric($request->max_hpp) && (float)$request->max_hpp > 0) {
                $query->where('total_estimasi_hpp', '<=', (float)$request->max_hpp);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('kode_bom_fmt', function ($b) {
                    return '<a href="' . route('produksi.bom.show', $b->id) . '" class="fw-bold text-primary text-decoration-none font-monospace" title="Buka Detail Resep"><i class="bi bi-upc-scan me-1 opacity-75"></i>' . e($b->kode_bom) . '</a>';
                })
                ->addColumn('nama_bom_fmt', function ($b) {
                    $bjName = $b->barangJadi ? ('<div class="text-theme-muted small mt-0.5"><i class="bi bi-box-seam me-1 text-primary"></i>[' . e($b->barangJadi->kode_barang) . '] ' . e($b->barangJadi->nama_barang) . '</div>') : '';
                    $tenderTag = $b->tender ? ('<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-0.5 rounded-pill me-1" style="font-size:10px;"><i class="bi bi-briefcase me-1"></i>' . e($b->tender->nomor_kontrak ?: 'TENDER') . '</span>') : '';
                    $tipeTag = '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size:10px;">' . e($b->tipe_bom ?: 'Garmen') . '</span>';
                    return '<div class="fw-bold text-theme-main">' . e($b->nama_bom) . '</div>' . $bjName . '<div class="mt-1 d-flex align-items-center flex-wrap gap-1">' . $tenderTag . $tipeTag . '</div>';
                })
                ->addColumn('barang_jadi_name', function ($b) {
                    if (!$b->barangJadi) return '-';
                    return '<strong>' . e($b->barangJadi->kode_barang) . '</strong> - ' . e($b->barangJadi->nama_barang);
                })
                ->addColumn('jumlah_hasil_fmt', function ($b) {
                    $satuan = $b->satuanHasil ? $b->satuanHasil->nama_satuan : ($b->barangJadi && $b->barangJadi->satuan ? $b->barangJadi->satuan->nama_satuan : 'Pcs');
                    $qty = number_format($b->jumlah_hasil, str_contains((string)$b->jumlah_hasil, '.') ? 4 : 0, ',', '.');
                    $qtyClean = str_contains($qty, ',') ? rtrim(rtrim($qty, '0'), ',') : $qty;
                    return '<span class="badge bg-secondary bg-opacity-10 text-theme-main border-0 font-monospace fw-bold px-2 py-1">' . $qtyClean . ' ' . e($satuan) . '</span>';
                })
                ->addColumn('total_hpp_fmt', function ($b) use ($canViewHpp) {
                    if (!$canViewHpp) {
                        return '<i class="bi bi-lock-fill text-muted opacity-75" style="font-size: 14px;" title="HPP Terkunci"></i>';
                    }
                    return '<span class="font-monospace fw-bold text-primary" style="white-space:nowrap;">Rp ' . number_format($b->total_estimasi_hpp, 0, ',', '.') . '</span>';
                })
                ->addColumn('status_badge', function ($b) {
                    if ($b->status === 'Draft') {
                        return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    } elseif ($b->status === 'Pending') {
                        return '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-50 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-clock-history me-1"></i>Pending</span>';
                    } elseif ($b->status === 'Approved' || $b->status === 'Active') {
                        return '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-check-circle me-1"></i>Disetujui</span>';
                    } elseif ($b->status === 'Rejected') {
                        return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-x-circle me-1"></i>Ditolak</span>';
                    } elseif ($b->status === 'Completed' || $b->status === 'Selesai') {
                        return '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-check-all me-1"></i>Selesai</span>';
                    } else {
                        return '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-dash-circle me-1"></i>' . e($b->status) . '</span>';
                    }
                })
                ->addColumn('action', function ($b) use ($isSupervisor, $user) {
                    $isCompleted           = in_array($b->status, ['Completed', 'Selesai']);
                    $isApprovedOrCompleted = in_array($b->status, ['Approved', 'Active', 'Completed', 'Selesai']);
                    $isPending             = ($b->status === 'Pending');
                    $isCreator             = ($b->created_by == $user->id);

                    // Hak Akses Tombol Edit & Hapus
                    // Khusus Draft: HANYA pembuatnya ($isCreator) yang boleh mengedit atau menghapus
                    if ($b->status === 'Draft') {
                        $canEdit   = $isCreator;
                        $canDelete = $isCreator;
                    } else {
                        $canEdit   = $isSupervisor || ($user->can('edit-bom') && !$isCompleted);
                        $canDelete = !$isPending && ($isSupervisor || ($user->can('hapus-bom') && !$isApprovedOrCompleted));
                    }

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1.5 flex-nowrap">';

                    // Tombol Kirim / Ajukan ke Pending jika status Draft (HANYA pembuat) atau Rejected
                    if (($b->status === 'Draft' && $isCreator) || ($b->status === 'Rejected' && ($isCreator || $isSupervisor))) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-primary rounded-circle" onclick="sendPendingBom(' . $b->id . ')" title="Ajukan ke Pending Approval"><i class="bi bi-send-fill"></i></button>';
                    }

                    $btn .= '<a href="' . route('produksi.bom.show', $b->id) . '" class="btn btn-sm btn-outline-info rounded-circle" title="Lihat Detail BOM"><i class="bi bi-eye"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="printBom(' . $b->id . ')" title="Cetak PDF BOM"><i class="bi bi-printer"></i></button>';
                    
                    if ($canEdit) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-circle" onclick="editBom(' . $b->id . ')" title="Edit BOM"><i class="bi bi-pencil"></i></button>';
                    }
                    if ($canDelete) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-circle" onclick="deleteBom(' . $b->id . ')" title="Hapus BOM"><i class="bi bi-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['kode_bom_fmt', 'nama_bom_fmt', 'barang_jadi_name', 'jumlah_hasil_fmt', 'total_hpp_fmt', 'status_badge', 'action'])
                ->with('kpi', $kpiData)
                ->make(true);
        }

        $satuans = MasterSatuan::forActiveCompany()->where('status', 'Active')->get();
        $tenders = Tender::forActiveCompany()
            ->forActiveBranch()
            ->orderBy('nama_tender', 'asc')
            ->get(['id', 'nomor_kontrak', 'nama_tender']);
        return view('modul_produksi.bom.index', compact('satuans', 'tenders'));
    }

    // ══════════════════════════════════════════════════════════════
    // STORE — Create New BOM Recipe
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-bom')) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk membuat BOM baru.'], 403);
        }

        $request->validate([
            'nama_bom'           => 'required|string|max:40',
            'kode_bom'           => 'nullable|string|max:20',
            'barang_jadi_id'     => 'required|exists:master_barang,id',
            'tender_id'          => 'nullable|exists:tenders,id',
            'jumlah_hasil'       => 'required|numeric|min:0.0001',
            'satuan_hasil_id'    => 'required|exists:master_satuan,id',
            'status'             => 'nullable|string|in:Draft,Pending,Approved,Rejected,Active,Inactive,Completed',
            'items'              => 'required|array|min:1|max:50',
            'items.*.barang_mentah_id' => 'required|exists:master_barang,id',
            'items.*.jumlah_kebutuhan' => 'required|numeric|min:0.0001',
            'items.*.satuan_id'        => 'required|exists:master_satuan,id',
        ], [
            'items.max' => 'Komposisi bahan baku tidak boleh melebihi 50 item.',
            'items.min' => 'Silakan masukkan minimal 1 bahan baku.',
        ]);

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);

        $finalBranchId  = !empty($activeBranchId) ? $activeBranchId : null;
        $finalCompanyId = !empty($activeBranchId) ? null : $activeCompanyId;

        $isAuto = $request->boolean('auto_number', true);
        if ($isAuto || empty($request->kode_bom)) {
            $kodeBom = $this->generateKodeBom();
        } else {
            $kodeBom = trim($request->kode_bom);
            $exists = Bom::where('kode_bom', $kodeBom)->exists();
            if ($exists) {
                return response()->json([
                    'message' => "Kode BOM '{$kodeBom}' sudah pernah digunakan atau terdaftar di sistem. Silakan gunakan kode unik lain atau centang penomoran Otomatis."
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $biayaTenagaKerja = round((float)($request->biaya_tenaga_kerja ?? 0), 4);
            $biayaOverhead    = round((float)($request->biaya_overhead ?? 0), 4);
            $initialStatus    = $request->status ?: 'Draft';

            $bom = Bom::create([
                'company_id'         => $finalCompanyId,
                'branch_id'          => $finalBranchId,
                'kode_bom'           => $kodeBom,
                'nama_bom'           => $request->nama_bom,
                'barang_jadi_id'     => $request->barang_jadi_id,
                'tender_id'          => $request->tender_id ?: null,
                'jumlah_hasil'       => round((float)$request->jumlah_hasil, 4),
                'satuan_hasil_id'    => $request->satuan_hasil_id,
                'tipe_bom'           => $request->tipe_bom ?: 'Garmen',
                'biaya_tenaga_kerja' => $biayaTenagaKerja,
                'biaya_overhead'     => $biayaOverhead,
                'total_estimasi_hpp' => 0,
                'keterangan'         => $request->keterangan,
                'status'             => $initialStatus,
                'created_by'         => $user->id,
            ]);

            $totalSubtotalBahan = 0;

            foreach ($request->items as $itemData) {
                $jumlahKebutuhan     = round((float)($itemData['jumlah_kebutuhan'] ?? 0), 4);
                $wastePercent        = round((float)($itemData['waste_percent'] ?? 0), 2);
                $estimasiHargaSatuan = round((float)($itemData['estimasi_harga_satuan'] ?? 0), 6);

                // Waste factor calculation: Gross requirement = Net * (1 + waste%)
                $grossQty = $jumlahKebutuhan * (1 + ($wastePercent / 100));
                $subtotal = round($grossQty * $estimasiHargaSatuan, 4);
                $totalSubtotalBahan += $subtotal;

                BomItem::create([
                    'bom_id'                => $bom->id,
                    'barang_mentah_id'      => $itemData['barang_mentah_id'],
                    'jumlah_kebutuhan'      => $jumlahKebutuhan,
                    'satuan_id'             => $itemData['satuan_id'],
                    'waste_percent'         => $wastePercent,
                    'estimasi_harga_satuan' => $estimasiHargaSatuan,
                    'estimasi_subtotal'     => $subtotal,
                    'catatan'               => $itemData['catatan'] ?? null,
                ]);
            }

            $targetHasil  = max((float)$request->jumlah_hasil, 0.0001);
            $totalHpp     = $totalSubtotalBahan + $biayaTenagaKerja + $biayaOverhead;
            $totalHppUnit = round($totalHpp / $targetHasil, 4);
            $bom->update(['total_estimasi_hpp' => $totalHppUnit]);

            DB::commit();

            // Kirim Notifikasi WhatsApp ke Supervisor jika langsung diajukan ke Pending
            if ($initialStatus === 'Pending') {
                WhatsAppGatewayController::sendBomApprovalNotification($bom);
            }

            return response()->json(['message' => 'Resep BOM Produksi berhasil disimpan!', 'id' => $bom->id], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error store BOM: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan BOM: ' . $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW — Full Page Detail BOM
    // ══════════════════════════════════════════════════════════════
    public function show(Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(404);

        $bom->load([
            'barangJadi.satuan',
            'barangJadi.primaryFoto',
            'barangJadi.fotos',
            'satuanHasil',
            'tender',
            'creator.position',
            'creator.roles',
            'updater.position',
            'updater.roles',
            'approver.position',
            'approver.roles',
            'items.barangMentah.satuan',
            'items.satuan',
        ]);

        // Pre-calculate totals
        $subtotalBahan = $bom->items->sum(function($item) {
            $gross = $item->jumlah_kebutuhan * (1 + ($item->waste_percent / 100));
            return $gross * $item->estimasi_harga_satuan;
        });

        $totalHpp   = $subtotalBahan + $bom->biaya_tenaga_kerja + $bom->biaya_overhead;
        $jumlahHasil = max((float)$bom->jumlah_hasil, 0.0001);
        $hppPerUnit  = $totalHpp / $jumlahHasil;

        $satuans = MasterSatuan::forActiveCompany()->where('status', 'Active')->get();
        $tenders = Tender::forActiveCompany()
            ->forActiveBranch()
            ->orderBy('nama_tender', 'asc')
            ->get(['id', 'nomor_kontrak', 'nama_tender']);

        $user = auth()->user();
        $isSupervisor          = $user->hasAnyRole(['Super Admin', 'Owner']);
        $canViewHpp            = $isSupervisor || $user->can('lihat-hpp-bom') || $user->can('lihat-hpp-stok');
        $isPending             = ($bom->status === 'Pending');
        $isCompleted           = in_array($bom->status, ['Completed', 'Selesai']);
        $isApprovedOrCompleted = in_array($bom->status, ['Approved', 'Active', 'Completed', 'Selesai']);
        $isCreator             = ($bom->created_by == $user->id);
        if ($bom->status === 'Draft') {
            $canEdit   = $isCreator;
            $canDelete = $isCreator;
        } else {
            $canEdit   = $isSupervisor || ($user->can('edit-bom') && !$isCompleted);
            $canDelete = !$isPending && ($isSupervisor || ($user->can('hapus-bom') && !$isApprovedOrCompleted));
        }

        return view('modul_produksi.bom.show', compact(
            'bom', 'subtotalBahan', 'totalHpp', 'hppPerUnit', 'satuans', 'tenders', 'canViewHpp',
            'isSupervisor', 'canEdit', 'canDelete', 'isCreator', 'isCompleted', 'isApprovedOrCompleted'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW JSON — for AJAX detail modal & preview
    // ══════════════════════════════════════════════════════════════
    public function showJson(Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(404);

        $bom->load([
            'barangJadi.satuan',
            'barangJadi.primaryFoto',
            'barangJadi.fotos',
            'satuanHasil',
            'tender',
            'creator.position',
            'creator.roles',
            'approver.position',
            'approver.roles',
            'items.barangMentah.satuan',
            'items.barangMentah.primaryFoto',
            'items.barangMentah.fotos',
            'items.satuan',
        ]);

        $bom->items->transform(function($item) {
            if ($item->barangMentah) {
                $primary = $item->barangMentah->primaryFoto ?? ($item->barangMentah->fotos ? $item->barangMentah->fotos->first() : null);
                $item->barangMentah->foto_url = $primary ? asset('storage/' . $primary->foto_path) : null;
            }
            return $item;
        });

        if ($bom->status === 'Draft') {
            $statusBadge = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
        } elseif ($bom->status === 'Pending') {
            $statusBadge = '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-50 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-clock-history me-1"></i>Pending Approval</span>';
        } elseif ($bom->status === 'Approved' || $bom->status === 'Active') {
            $statusBadge = '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-check-circle me-1"></i>Disetujui</span>';
        } elseif ($bom->status === 'Completed' || $bom->status === 'Selesai') {
            $statusBadge = '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-check-all me-1"></i>Selesai</span>';
        } elseif ($bom->status === 'Rejected') {
            $statusBadge = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-x-circle me-1"></i>Ditolak</span>';
        } else {
            $statusBadge = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-dash-circle me-1"></i>' . e($bom->status) . '</span>';
        }

        $user = auth()->user();
        $isSupervisor = $user->hasAnyRole(['Super Admin', 'Owner']);
        $canViewHpp   = $isSupervisor || $user->can('lihat-hpp-bom') || $user->can('lihat-hpp-stok');

        return response()->json([
            'bom'          => $bom,
            'items'        => $bom->items,
            'status_badge' => $statusBadge,
            'can_view_hpp' => $canViewHpp,
        ]);
    }

    public function edit(Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(404);

        $user = auth()->user();
        $isSupervisor = $user->hasAnyRole(['Super Admin', 'Owner']);
        $isCompleted  = in_array($bom->status, ['Completed', 'Selesai']);

        if ($isCompleted && !$isSupervisor) {
            return response()->json(['message' => 'Resep BOM yang sudah berstatus Selesai hanya dapat diedit oleh Super Admin.'], 403);
        }

        $bom->load([
            'barangJadi.satuan',
            'barangJadi.primaryFoto',
            'barangJadi.fotos',
            'satuanHasil',
            'tender',
            'items.barangMentah.satuan',
            'items.barangMentah.primaryFoto',
            'items.barangMentah.fotos',
            'items.satuan',
        ]);

        $bom->items->transform(function($item) {
            if ($item->barangMentah) {
                $primary = $item->barangMentah->primaryFoto ?? ($item->barangMentah->fotos ? $item->barangMentah->fotos->first() : null);
                $item->barangMentah->foto_url = $primary ? asset('storage/' . $primary->foto_path) : null;
            }
            return $item;
        });

        return response()->json([
            'bom'   => $bom,
            'items' => $bom->items,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // WORKFLOW: SEND PENDING, APPROVE, REJECT
    // ══════════════════════════════════════════════════════════════

    public function sendPending(Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(403, 'Anda tidak memiliki akses ke BOM ini.');

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && $bom->created_by != $user->id) {
            return response()->json(['message' => 'Hanya pembuat resep yang dapat mengajukan BOM ini ke status Pending.'], 403);
        }

        if (!in_array($bom->status, ['Draft', 'Rejected'])) {
            return response()->json(['message' => 'Hanya resep BOM berstatus Draft atau Ditolak (Rejected) yang dapat diajukan ke status Pending.'], 422);
        }

        $bom->update([
            'status'     => 'Pending',
            'updated_by' => $user->id,
        ]);

        // Kirim Notifikasi WhatsApp ke Supervisor / Manager Produksi
        WhatsAppGatewayController::sendBomApprovalNotification($bom);

        return response()->json([
            'message' => "Resep BOM {$bom->kode_bom} berhasil diajukan kembali ke status Pending Approval!",
            'status'  => 'Pending'
        ], 200);
    }

    public function approve(Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(403, 'Anda tidak memiliki akses ke BOM ini.');

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('approve-bom')) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menyetujui (Approve) BOM ini.'], 403);
        }

        if ($bom->status === 'Draft') {
            return response()->json(['message' => 'Resep BOM berstatus Draft harus diajukan ke status Pending terlebih dahulu sebelum dapat disetujui.'], 422);
        }

        if ($bom->status === 'Approved' || $bom->status === 'Active') {
            return response()->json(['message' => 'Resep BOM ini sudah disetujui sebelumnya.'], 422);
        }

        $bom->update([
            'status'      => 'Approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'updated_by'  => $user->id,
        ]);

        // Kirim Notifikasi WhatsApp ke Pembuat Resep (Creator)
        WhatsAppGatewayController::sendBomApprovedNotification($bom, $user);

        return response()->json([
            'message' => "Resep BOM {$bom->kode_bom} berhasil disetujui (Approved)!",
            'status'  => 'Approved'
        ], 200);
    }

    public function reject(Request $request, Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(403, 'Anda tidak memiliki akses ke BOM ini.');

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('approve-bom')) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menolak BOM ini.'], 403);
        }

        if ($bom->status === 'Draft') {
            return response()->json(['message' => 'Resep BOM berstatus Draft tidak dapat ditolak karena belum diajukan ke status Pending.'], 422);
        }

        $alasan = $request->input('alasan', 'Ditolak oleh ' . ($user->nama_lengkap ?: $user->name));
        $catatanLama = $bom->keterangan ? ($bom->keterangan . "\n") : '';

        $bom->update([
            'status'      => 'Rejected',
            'keterangan'  => $catatanLama . "[Penolakan: {$alasan}]",
            'updated_by'  => $user->id,
        ]);

        // Kirim Notifikasi WhatsApp ke Pembuat Resep (Creator)
        WhatsAppGatewayController::sendBomRejectedNotification($bom, $user, $alasan);

        return response()->json([
            'message' => "Resep BOM {$bom->kode_bom} telah ditolak (Rejected).",
            'status'  => 'Rejected'
        ], 200);
    }

    public function complete(Request $request, Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(403, 'Anda tidak memiliki akses ke BOM ini.');

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('approve-bom')) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menandai BOM ini sebagai Selesai.'], 403);
        }

        if ($bom->status === 'Completed' || $bom->status === 'Selesai') {
            return response()->json(['message' => 'Resep BOM ini sudah berstatus Selesai.'], 422);
        }

        $bom->update([
            'status'     => 'Completed',
            'updated_by' => $user->id,
        ]);

        return response()->json([
            'message' => "Resep BOM {$bom->kode_bom} telah berhasil ditandai sebagai Selesai.",
            'status'  => 'Completed'
        ], 200);
    }

    public function reactivate(Request $request, Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(403, 'Anda tidak memiliki akses ke BOM ini.');

        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json(['message' => 'Hanya Super Admin / Owner yang memiliki izin untuk mengaktifkan kembali BOM ini.'], 403);
        }

        $bom->update([
            'status'     => 'Approved',
            'updated_by' => $user->id,
        ]);

        return response()->json([
            'message' => "Resep BOM {$bom->kode_bom} telah berhasil diaktifkan kembali (Approved).",
            'status'  => 'Approved'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE — Edit Existing BOM
    // ══════════════════════════════════════════════════════════════
    public function update(Request $request, Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(404);

        $user = auth()->user();
        $isSupervisor = $user->hasAnyRole(['Super Admin', 'Owner']);
        if (!$isSupervisor && !$user->can('edit-bom') && !$user->can('akses-produksi')) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengedit BOM ini.'], 403);
        }

        $isCompleted = in_array($bom->status, ['Completed', 'Selesai']);
        if ($isCompleted && !$isSupervisor) {
            return response()->json(['message' => 'Resep BOM yang sudah berstatus Selesai hanya dapat diedit oleh Super Admin.'], 403);
        }

        $request->validate([
            'nama_bom'           => 'required|string|max:40',
            'barang_jadi_id'     => 'required|exists:master_barang,id',
            'tender_id'          => 'nullable|exists:tenders,id',
            'jumlah_hasil'       => 'required|numeric|min:0.0001',
            'satuan_hasil_id'    => 'required|exists:master_satuan,id',
            'status'             => 'nullable|string|in:Draft,Pending,Approved,Rejected,Active,Inactive,Completed',
            'items'              => 'required|array|min:1|max:50',
            'items.*.barang_mentah_id' => 'required|exists:master_barang,id',
            'items.*.jumlah_kebutuhan' => 'required|numeric|min:0.0001',
            'items.*.satuan_id'        => 'required|exists:master_satuan,id',
        ], [
            'items.max' => 'Komposisi bahan baku tidak boleh melebihi 50 item.',
            'items.min' => 'Silakan masukkan minimal 1 bahan baku.',
        ]);

        DB::beginTransaction();
        try {
            $biayaTenagaKerja = round((float)($request->biaya_tenaga_kerja ?? 0), 4);
            $biayaOverhead    = round((float)($request->biaya_overhead ?? 0), 4);
            $oldStatus        = $bom->status;
            $statusTarget     = $request->status ?: $bom->status;

            $updatePayload = [
                'nama_bom'           => $request->nama_bom,
                'barang_jadi_id'     => $request->barang_jadi_id,
                'tender_id'          => $request->tender_id ?: null,
                'jumlah_hasil'       => round((float)$request->jumlah_hasil, 4),
                'satuan_hasil_id'    => $request->satuan_hasil_id,
                'tipe_bom'           => $request->tipe_bom ?: 'Garmen',
                'biaya_tenaga_kerja' => $biayaTenagaKerja,
                'biaya_overhead'     => $biayaOverhead,
                'keterangan'         => $request->keterangan,
                'status'             => $statusTarget,
                'updated_by'         => $user->id,
            ];

            if ($statusTarget === 'Approved' && !$bom->approved_by) {
                $updatePayload['approved_by'] = $user->id;
                $updatePayload['approved_at'] = now();
            }

            $bom->update($updatePayload);

            $bom->items()->delete();

            $totalSubtotalBahan = 0;

            foreach ($request->items as $itemData) {
                $jumlahKebutuhan     = round((float)($itemData['jumlah_kebutuhan'] ?? 0), 4);
                $wastePercent        = round((float)($itemData['waste_percent'] ?? 0), 2);
                $estimasiHargaSatuan = round((float)($itemData['estimasi_harga_satuan'] ?? 0), 6);

                $grossQty = $jumlahKebutuhan * (1 + ($wastePercent / 100));
                $subtotal = round($grossQty * $estimasiHargaSatuan, 4);
                $totalSubtotalBahan += $subtotal;

                BomItem::create([
                    'bom_id'                => $bom->id,
                    'barang_mentah_id'      => $itemData['barang_mentah_id'],
                    'jumlah_kebutuhan'      => $jumlahKebutuhan,
                    'satuan_id'             => $itemData['satuan_id'],
                    'waste_percent'         => $wastePercent,
                    'estimasi_harga_satuan' => $estimasiHargaSatuan,
                    'estimasi_subtotal'     => $subtotal,
                    'catatan'               => $itemData['catatan'] ?? null,
                ]);
            }

            $targetHasil  = max((float)$request->jumlah_hasil, 0.0001);
            $totalHpp     = $totalSubtotalBahan + $biayaTenagaKerja + $biayaOverhead;
            $totalHppUnit = round($totalHpp / $targetHasil, 4);
            $bom->update(['total_estimasi_hpp' => $totalHppUnit]);

            DB::commit();

            if ($statusTarget === 'Pending' && $oldStatus !== 'Pending') {
                WhatsAppGatewayController::sendBomApprovalNotification($bom);
            } elseif ($statusTarget === 'Approved' && $oldStatus !== 'Approved') {
                WhatsAppGatewayController::sendBomApprovedNotification($bom, $user);
            } elseif ($statusTarget === 'Rejected' && $oldStatus !== 'Rejected') {
                WhatsAppGatewayController::sendBomRejectedNotification($bom, $user, $request->keterangan);
            }

            return response()->json(['message' => 'Resep BOM Produksi berhasil diperbarui!'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error update BOM: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui BOM: ' . $e->getMessage()], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY — Delete BOM
    // ══════════════════════════════════════════════════════════════
    public function destroy(Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(404);

        $user = auth()->user();
        $isSupervisor = $user->hasAnyRole(['Super Admin', 'Owner']);
        if (!$isSupervisor && !$user->can('hapus-bom') && !$user->can('akses-produksi')) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus BOM ini.'], 403);
        }

        if ($bom->status === 'Pending') {
            return response()->json(['message' => 'Resep BOM yang berstatus Pending Approval tidak dapat dihapus. Silakan tolak atau ubah status terlebih dahulu.'], 422);
        }

        $isApprovedOrCompleted = in_array($bom->status, ['Approved', 'Active', 'Completed', 'Selesai']);
        if ($isApprovedOrCompleted && !$isSupervisor) {
            return response()->json(['message' => 'Resep BOM yang sudah Disetujui (Approved) atau Selesai tidak dapat dihapus, kecuali oleh Super Admin.'], 403);
        }

        // Security & Integrity check: Cegah penghapusan jika BOM sedang digunakan pada SPK aktif
        $hasActiveSpk = \App\Models\SpkProduksi::where('bom_id', $bom->id)
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['Dibatalkan', 'Rejected'])
            ->exists();

        if ($hasActiveSpk) {
            return response()->json([
                'message' => 'Resep BOM ini tidak dapat dihapus karena sedang terikat pada Surat Perintah Kerja (SPK) Produksi yang sedang berjalan atau aktif.'
            ], 422);
        }

        $bom->update([
            'deleted_by' => $user->id,
            'kode_bom'   => $bom->kode_bom . '_DEL_' . time(),
        ]);
        $bom->delete();

        return response()->json(['message' => 'BOM berhasil dihapus!'], 200);
    }

    // ══════════════════════════════════════════════════════════════
    // PRINT PDF — Cetak Document Resep BOM
    // ══════════════════════════════════════════════════════════════
    public function printPdf(Bom $bom)
    {
        if (!$this->resolveBom($bom)) abort(404);

        $bom->load([
            'company',
            'branch',
            'barangJadi.satuan',
            'barangJadi.primaryFoto',
            'satuanHasil',
            'creator.position',
            'creator.roles',
            'creator.activeSignature',
            'updater.position',
            'updater.roles',
            'updater.activeSignature',
            'approver.position',
            'approver.roles',
            'approver.activeSignature',
            'items.barangMentah.satuan',
            'items.barangMentah.primaryFoto',
            'items.satuan',
        ]);

        $appLogoSetting = \App\Models\AppSetting::where('key', 'app_logo')->value('value');
        $appLogoUrl     = $appLogoSetting ? asset('storage/' . $appLogoSetting) : asset('gambar/logo.png');

        $user      = auth()->user();
        $canSeeHpp = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-bom') || $user->can('lihat-hpp-stok')) : false;

        return view('modul_produksi.bom.print_bom', compact('bom', 'appLogoUrl', 'canSeeHpp'));
    }

    // ══════════════════════════════════════════════════════════════
    // AJAX SEARCH BARANG (LOOKUP MODAL)
    // ══════════════════════════════════════════════════════════════
    public function searchBarang(Request $request)
    {
        $q = trim($request->get('q', ''));
        $type = $request->get('type', 'all'); // 'jadi' or 'mentah' or 'all'

        $query = MasterBarang::forActiveCompany()
            ->with(['satuan:id,nama_satuan', 'primaryFoto', 'fotos', 'stokGudangs'])
            ->where('status', 'Active');

        // Filter khusus untuk Bahan Baku / Aksesoris:
        // Hanya ambil barang yang memang tercatat persediaannya di tabel stok_gudangs (karena memiliki HPP gudang)
        if ($type === 'mentah') {
            $query->whereHas('stokGudangs');
        }

        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('kode_barang', 'like', "%{$q}%")
                    ->orWhere('nama_barang', 'like', "%{$q}%")
                    ->orWhere('nama_alias', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('nama_barang', 'asc')->limit(50)->get();

        $bomHpps = Bom::forActiveCompany()
            ->visibleToUser()
            ->whereIn('barang_jadi_id', $items->pluck('id'))
            ->whereIn('status', ['Approved', 'Active', 'Pending', 'Draft'])
            ->orderBy('id', 'desc')
            ->get()
            ->keyBy('barang_jadi_id');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()->company_id ?? 1);

        // Gudang untuk entitas aktif (PT Pusat jika branch_id null, atau Cabang aktif)
        $activeGudangQuery = MasterGudang::where('company_id', $activeCompanyId)->where('status', 'Active');
        if (!empty($activeBranchId)) {
            $activeGudangQuery->where('cabang_id', $activeBranchId);
        } else {
            $activeGudangQuery->whereNull('cabang_id');
        }
        $activeEntityGudangIds = $activeGudangQuery->pluck('id')->toArray();

        $result = $items->map(function($b) use ($bomHpps, $activeEntityGudangIds, $type) {
            $fotoPath = $b->primaryFoto ? $b->primaryFoto->foto_path : null;
            $primaryUrl = $fotoPath ? asset('storage/' . $fotoPath) : null;

            $allFotos = [];
            if ($primaryUrl) {
                $allFotos[] = $primaryUrl;
            }
            if ($b->fotos && $b->fotos->count() > 0) {
                foreach ($b->fotos as $f) {
                    $url = asset('storage/' . $f->foto_path);
                    if (!in_array($url, $allFotos)) {
                        $allFotos[] = $url;
                    }
                }
            }

            $stokGudangs = $b->stokGudangs ?? collect();
            $stokTotal = 0;
            $hppVal = 0;

            // Strict Isolation per Entitas Aktif (HANYA gudang milik Cabang Aktif atau PT Pusat Aktif)
            // Jika tidak ada stok di entitas aktif, maka stok = 0 dan HPP = 0 (kosong, tanpa cross-branch fallback)
            if (!empty($activeEntityGudangIds)) {
                $activeSgs = $stokGudangs->filter(fn($sg) => in_array($sg->gudang_id, $activeEntityGudangIds));
                if ($activeSgs->isNotEmpty()) {
                    $actStok  = (float)$activeSgs->sum('stok_aktual');
                    $actNilai = (float)$activeSgs->sum('nilai_persediaan');

                    // Stok fisik aktual
                    $stokTotal = $actStok;

                    if ($actStok > 0 && $actNilai > 0) {
                        // 1. Moving Average Cost = Nilai Persediaan ÷ Stok Aktual
                        $hppVal = $actNilai / $actStok;
                    } else {
                        // 2. Stok 0 atau nilai 0 — tetap ambil HPP dari harga referensi terakhir
                        $sgValid = $activeSgs->first(fn($sg) => (float)$sg->harga_pokok_rata_rata > 0);
                        if ($sgValid) {
                            $hppVal = (float)$sgValid->harga_pokok_rata_rata;
                        } else {
                            $sgBeli = $activeSgs->first(fn($sg) => (float)$sg->harga_beli_terakhir > 0);
                            if ($sgBeli) {
                                $hppVal = (float)$sgBeli->harga_beli_terakhir;
                            }
                        }
                    }
                }
            }

            // Jika barang jadi dan ada resep BOM yang sudah pernah dibuat
            if ($type === 'jadi' && $hppVal <= 0) {
                $bomObj = $bomHpps->get($b->id);
                if ($bomObj && (float)$bomObj->total_estimasi_hpp > 0) {
                    $hppVal = (float)$bomObj->total_estimasi_hpp;
                }
            }

            return [
                'id'          => $b->id,
                'kode_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'satuan_id'   => $b->satuan_id,
                'satuan_name' => $b->satuan ? $b->satuan->nama_satuan : 'Pcs',
                'stok_total'  => $stokTotal,
                'hpp_satuan'  => $hppVal,
                'foto_url'    => $primaryUrl,
                'fotos_url'   => $allFotos,
            ];
        });

        return response()->json($result);
    }

    /**
     * AJAX search endpoint for Tender / Project lookup in BOM.
     */
    public function searchTender(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-bom') && !$user->can('tambah-bom') && !$user->can('edit-bom') && !$user->can('akses-produksi')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $search = trim($request->get('q', ''));

        $query = Tender::forActiveCompany()
            ->forActiveBranch()
            ->with(['customer:id,nama_customer,kode_customer'])
            ->whereNull('deleted_at');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nama_tender', 'like', "%{$search}%")
                  ->orWhere('nomor_kontrak', 'like', "%{$search}%")
                  ->orWhere('kategori_instansi', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($qc) use ($search) {
                      $qc->where('nama_customer', 'like', "%{$search}%")
                         ->orWhere('kode_customer', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->orderBy('id', 'desc')->limit(50)->get();

        $result = $items->map(function($t) {
            $custName = $t->customer ? $t->customer->nama_customer : ($t->kategori_instansi ?: '-');
            return [
                'id'             => $t->id,
                'nomor_kontrak'  => $t->nomor_kontrak ?: 'TENDER #' . $t->id,
                'nama_tender'    => $t->nama_tender,
                'customer_name'  => $custName,
                'nilai_estimasi' => (float)($t->nilai_estimasi ?: 0),
                'nilai_fmt'      => 'Rp ' . number_format((float)($t->nilai_estimasi ?: 0), 0, ',', '.'),
                'deadline'       => $t->deadline ? \Carbon\Carbon::parse($t->deadline)->format('d/m/Y') : '-',
                'status'         => $t->status ?: 'Draft',
            ];
        });

        return response()->json($result);
    }
}
