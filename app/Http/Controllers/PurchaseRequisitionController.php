<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionDetail;
use App\Models\Department;
use App\Models\MasterBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Shuchkin\SimpleXLSXGen;

class PurchaseRequisitionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('lihat-pr');

        $activeBranchId = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if ($request->ajax() && !$request->has('_pjax') && $request->has('draw')) {
            // ── Pre-compute auth flags once (not per row) ────────────────────
            $user          = auth()->user();
            $isSuperAdmin  = $user->hasRole('Super Admin');
            $isPrivileged  = $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Manager Produksi', 'Manager Finance', 'Manager']) || $user->can('approve-pr');
            $isAdmin       = $user->hasAnyRole(['Super Admin', 'Admin']);
            $canEdit       = $user->can('edit-pr');
            $canApprove    = $user->can('approve-pr');
            $canDelete     = $user->can('hapus-pr');
            $canPrint      = $user->can('cetak-pr') || $user->can('print-pr') || $isAdmin || $user->hasRole('Owner');
            $karyawanId    = $user->karyawan_id;

            $where = ['pr.deleted_at IS NULL'];
            $bindings = [];

            // ── Strict Pusat vs Cabang Isolation ─────────────────────────────
            if ($activeCompanyId) {
                $where[]    = 'pr.company_id = ?';
                $bindings[] = intval($activeCompanyId);
            }

            if (!empty($activeBranchId)) {
                $where[]    = 'pr.branch_id = ?';
                $bindings[] = intval($activeBranchId);
            } else {
                // When Pusat is active (active_branch_id is NULL), ONLY show Pusat PRs
                $where[]    = 'pr.branch_id IS NULL';
            }

            // Strict Draft privacy: Draft is ONLY visible to the creator ($user->id)
            if ($isPrivileged) {
                $where[]    = "(pr.status != 'Draft' OR pr.dibuat_oleh = ?)";
                $bindings[] = intval($user->id);
            } else {
                if ($karyawanId) {
                    $where[]    = "((pr.status != 'Draft' AND (pr.diminta_oleh = ? OR pr.dibuat_oleh = ?)) OR (pr.status = 'Draft' AND pr.dibuat_oleh = ?))";
                    $bindings[] = intval($karyawanId);
                    $bindings[] = intval($user->id);
                    $bindings[] = intval($user->id);
                } else {
                    $where[]    = "pr.dibuat_oleh = ?";
                    $bindings[] = intval($user->id);
                }
            }

            $search = trim($request->input('search.value', ''));

            if ($request->filled('filter_status')) {
                $where[]    = 'pr.status = ?';
                $bindings[] = $request->filter_status;
            }
            if ($request->filled('filter_departemen')) {
                $dept = Department::forActiveCompany()->where('department_name', $request->filter_departemen)->value('id');
                if ($dept) { $where[] = "pr.departemen_id = ?"; $bindings[] = intval($dept); }
            }
            if ($request->filled('filter_tgl_mulai')) {
                $where[]    = 'pr.tanggal_pr >= ?';
                $bindings[] = $request->filter_tgl_mulai;
            }
            if ($request->filled('filter_tgl_selesai')) {
                $where[]    = 'pr.tanggal_pr <= ?';
                $bindings[] = $request->filter_tgl_selesai;
            }

            if (!empty($search)) {
                // nomor_pr prefix uses unique index, karyawan/dept resolved to IDs
                $deptIds     = Department::forActiveCompany()->where('department_name', 'like', "{$search}%")->pluck('id')->toArray();
                $karyawanIds = \App\Models\Karyawan::forActiveCompany()->where(function($q2) use ($search) {
                    $q2->where('nama_lengkap', 'like', "{$search}%")
                       ->orWhere('kode_karyawan', 'like', "{$search}%");
                })->pluck('id')->toArray();

                $searchClauses = ['pr.nomor_pr LIKE ?'];
                $bindings[]    = "{$search}%";

                if (!empty($deptIds)) {
                    $placeholders  = implode(',', array_fill(0, count($deptIds), '?'));
                    $searchClauses[] = "pr.departemen_id IN ({$placeholders})";
                    $bindings      = array_merge($bindings, $deptIds);
                }
                if (!empty($karyawanIds)) {
                    $placeholders  = implode(',', array_fill(0, count($karyawanIds), '?'));
                    $searchClauses[] = "pr.diminta_oleh IN ({$placeholders})";
                    $bindings      = array_merge($bindings, $karyawanIds);
                }
                $where[] = '(' . implode(' OR ', $searchClauses) . ')';
            }

            $whereSQL = implode(' AND ', $where);

            // ── Count: real COUNT with cache ──────────────────────────────────
            $cacheKey = 'pr_cnt_' . md5($whereSQL . serialize($bindings));
            $cacheTtl = !empty($search) ? 120 : 300;
            $total = \Illuminate\Support\Facades\Cache::remember($cacheKey, $cacheTtl, function() use ($whereSQL, $bindings) {
                return (int) DB::scalar("SELECT COUNT(*) FROM purchase_requisitions pr WHERE {$whereSQL}", $bindings);
            });

            // ── Sort ─────────────────────────────────────────────────────────
            // Optimize: Map column 1 (nomor_pr) to pr.id for index-ordered sorting without filesort
            $colMap  = [0=>'pr.id',1=>'pr.id',2=>'pr.tanggal_pr',3=>'pr.tanggal_dibutuhkan',4=>'dept.department_name',5=>'k.nama_lengkap',6=>'pr.id',7=>'pr.status',8=>'pr.id'];
            $orderCol = $colMap[$request->input('order.0.column', 0)] ?? 'pr.id';
            $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'ASC' : 'DESC';

            // ── Late Row-Lookup: Get IDs first using fast index-only / covering joins ─
            $start  = intval($request->input('start', 0));
            $length = intval($request->input('length', 10));

            // Optimize: Only join departments & karyawans in subquery if search is active or sorting by joined fields
            $joinSQL = "";
            if (!empty($search) || in_array($orderCol, ['dept.department_name', 'k.nama_lengkap'])) {
                $joinSQL = "
                    LEFT JOIN departments dept ON dept.id = pr.departemen_id
                    LEFT JOIN karyawans k ON k.id = pr.diminta_oleh
                ";
            }

            $idBindings = array_merge($bindings, [$start, $length]);
            $ids = DB::select("
                SELECT pr.id
                FROM purchase_requisitions pr
                {$joinSQL}
                WHERE {$whereSQL}
                ORDER BY {$orderCol} {$orderDir}
                LIMIT ?, ?
            ", $idBindings);

            $idList = array_map(fn($item) => $item->id, $ids);

            $items = [];
            if (!empty($idList)) {
                $placeholders = implode(',', array_fill(0, count($idList), '?'));
                $items = DB::select("
                    SELECT
                        pr.id, pr.nomor_pr, pr.tanggal_pr, pr.tanggal_dibutuhkan,
                        pr.diminta_oleh, pr.dibuat_oleh, pr.status,
                        dept.department_name,
                        k.nama_lengkap, k.kode_karyawan,
                        (SELECT COUNT(*) FROM purchase_requisition_details prd WHERE prd.pr_id = pr.id) AS details_count,
                        (SELECT COALESCE(SUM(prd.jumlah), 0) FROM purchase_requisition_details prd WHERE prd.pr_id = pr.id) AS total_qty,
                        CASE WHEN pr.status = 'Approved' AND EXISTS(
                            SELECT 1 FROM purchase_requisition_details prd2
                            WHERE prd2.pr_id = pr.id
                               AND prd2.jumlah > (SELECT COALESCE(SUM(pod.jumlah),0) FROM purchase_order_details pod WHERE pod.pr_detail_id = prd2.id)
                        ) THEN 0 ELSE 1 END AS is_fulfilled
                    FROM purchase_requisitions pr
                    LEFT JOIN departments dept ON dept.id = pr.departemen_id
                    LEFT JOIN karyawans k ON k.id = pr.diminta_oleh
                    WHERE pr.id IN ({$placeholders})
                    ORDER BY {$orderCol} {$orderDir}
                ", $idList);
            }

            // ── Build response rows ───────────────────────────────────────────
            $data = [];
            foreach ($items as $idx => $pr) {
                // Actions
                $actionsHtml = '<div class="d-flex justify-content-center gap-1">';
                $actionsHtml .= '<button type="button" class="btn btn-light btn-sm text-info rounded-circle btn-detail" data-id="'.$pr->id.'" title="Detail" style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"><i class="bi bi-eye"></i></button>';

                $isCreator = ($pr->diminta_oleh == $karyawanId) || ($pr->dibuat_oleh == $user->id);

                if ($canEdit) {
                    // Super Admin can edit any PR (including Approved/Closed), other users can only edit Draft/Pending/Rejected
                    if ($isSuperAdmin || (($isPrivileged || $isCreator) && in_array($pr->status, ['Draft', 'Pending', 'Rejected']))) {
                        $actionsHtml .= '<button type="button" class="btn btn-light btn-sm text-primary rounded-circle btn-edit" data-id="'.$pr->id.'" title="Edit" style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"><i class="bi bi-pencil"></i></button>';
                    }
                }
                if ($canDelete) {
                    // Super Admin can delete any PR (including Approved), other users can only delete Draft
                    if ($isSuperAdmin || ($pr->status === 'Draft' && ($isCreator || $isAdmin))) {
                        $actionsHtml .= '<button type="button" class="btn btn-light btn-sm text-danger rounded-circle btn-delete" data-id="'.$pr->id.'" data-nomor="'.$pr->nomor_pr.'" title="Hapus" style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"><i class="bi bi-trash"></i></button>';
                    }
                }
                if ($canPrint) {
                    $actionsHtml .= '<a href="/purchase-requisition/'.$pr->id.'/print" target="_blank" class="btn btn-light btn-sm text-secondary rounded-circle" title="Cetak/PDF" style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"><i class="bi bi-printer"></i></a>';
                }
                $actionsHtml .= '</div>';

                // Status badge
                $s = $pr->status;
                if ($s === 'Draft') {
                    $statusHtml = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-file-earmark me-1"></i>Draft</span>';
                } elseif ($s === 'Pending') {
                    $statusHtml = '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-50 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-clock me-1"></i>Pending Approval</span>';
                } elseif ($s === 'Approved') {
                    $done = ($pr->is_fulfilled == 1) && ($pr->details_count > 0);
                    $statusHtml = $done
                        ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-50 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-check-circle-fill text-success me-1"></i>Approved (Fulfilled)</span>'
                        : '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-50 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-check2-circle me-1"></i>Approved</span>';
                } elseif ($s === 'Closed') {
                    $statusHtml = '<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-50 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-archive-fill me-1"></i>Closed</span>';
                } else {
                    $statusHtml = '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-50 px-3 py-1.5 rounded-pill fw-bold"><i class="bi bi-x-circle me-1"></i>Rejected</span>';
                }

                $data[] = [
                    'no'               => $start + $idx + 1,
                    'nomor_pr'         => '<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill">'.e($pr->nomor_pr).'</span>',
                    'tanggal_pr'       => date('d-m-Y', strtotime($pr->tanggal_pr)),
                    'tanggal_dibutuhkan' => date('d-m-Y', strtotime($pr->tanggal_dibutuhkan)),
                    'departemen'       => e($pr->department_name ?? '-'),
                    'diminta_oleh'     => '<div class="fw-semibold text-dark">'.e($pr->nama_lengkap ?? '-').'</div><div class="small text-muted">'.e($pr->kode_karyawan ?? '').'</div>',
                    'total_item'       => (float) ($pr->total_qty ?? $pr->details_count ?? 0),
                    'status'           => $statusHtml,
                    'aksi'             => $actionsHtml,
                ];
            }

            return response()->json([
                'draw'            => intval($request->input('draw')),
                'recordsTotal'    => $total,
                'recordsFiltered' => $total,
                'data'            => $data,
            ]);
        }


        $prs = [];
        $departments = Department::forActiveCompany()->get();
        // Removed MasterBarang all() preloading to prevent OOM
        $barang = [];
        $users = \App\Models\User::orderBy('nama_lengkap', 'asc')->get();
        $karyawans = \App\Models\Karyawan::with(['department', 'position'])->forActiveCompany()->where('status', 'Aktif')->orderBy('nama_lengkap', 'asc')->get();
        $jabatans  = \App\Models\Position::forActiveCompany()->where('status', 'Active')->orderBy('position_name', 'asc')->get();

        $satuans = \App\Models\MasterSatuan::forActiveCompany()->orderBy('nama_satuan', 'asc')->get();
        $warnas = \App\Models\MasterWarna::forActiveCompany()->orderBy('nama_warna', 'asc')->get();
        $kategoris = \App\Models\MasterKategoriBarang::forActiveCompany()->orderBy('nama_kategori', 'asc')->get();
        $tipeBarangs = \App\Models\MasterTipeBarang::forActiveCompany()->orderBy('nama_tipe', 'asc')->get();
        $brands = \App\Models\MasterBrand::forActiveCompany()->orderBy('nama_brand', 'asc')->get();

        return view('modul_purchasing.pembuatanpr.viewpr', compact(
            'prs', 'departments', 'barang', 'users', 'karyawans', 'jabatans',
            'satuans', 'warnas', 'kategoris', 'tipeBarangs', 'brands'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-pr');

        // Log request payload for debugging
        Log::info('PR Store Request Payload: ', $request->all());

        try {
            $request->validate([
                'nomor_pr'            => 'nullable|string|max:15|unique:purchase_requisitions,nomor_pr',
                'tanggal_pr'          => 'required|date',
                'tanggal_dibutuhkan'  => 'required|date|after_or_equal:tanggal_pr',
                'departemen_id'       => 'required|exists:departments,id',
                'diminta_oleh'        => 'required|exists:karyawans,id',
                'keterangan'          => 'nullable|string|max:40',
                'status'              => 'required|in:Draft,Pending',
                'items'               => 'required|array|min:1|max:20',
                'items.*.barang_id'   => 'required|exists:master_barang,id',
                'items.*.jumlah'      => 'required|numeric|min:0.01',
                'items.*.harga_estimasi' => 'required|numeric|min:0',
                'items.*.keterangan'  => 'nullable|string|max:40',
            ], [
                'nomor_pr.max'           => 'Nomor PR manual tidak boleh melebihi 15 karakter.',
                'nomor_pr.unique'        => 'Nomor PR sudah digunakan oleh pengajuan lain!',
                'keterangan.max'         => 'Keterangan pengajuan tidak boleh melebihi 40 karakter.',
                'items.min'              => 'Mohon ajukan minimal 1 barang.',
                'items.max'              => 'Pengajuan PR dibatasi maksimal 20 item barang.',
                'items.*.keterangan.max' => 'Catatan/keterangan item tidak boleh melebihi 40 karakter.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('PR Store Validation Errors: ', $e->errors());
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => $e->errors()
            ], 422);
        }

        // Prevent duplicates
        $barangIds = collect($request->items)->pluck('barang_id')->filter()->toArray();
        if (count($barangIds) !== count(array_unique($barangIds))) {
            return response()->json(['message' => 'Barang yang diajukan tidak boleh duplikat!'], 422);
        }

        $attempts = 0;
        $maxAttempts = 5;
        $nomorPr = null;

        do {
            try {
                DB::beginTransaction();

                // Use manual nomor_pr if provided, otherwise auto-generate
                if (!empty($request->nomor_pr)) {
                    $nomorPr = trim($request->nomor_pr);
                } else {
                    // Auto-generate number PR with pessimistic lock
                    $year = date('Y', strtotime($request->tanggal_pr));
                    $month = date('m', strtotime($request->tanggal_pr));
                    $prefix = "PR/{$year}/{$month}/";
                    
                    $prefixLen = strlen($prefix);
                    $latestNomor = PurchaseRequisition::where('nomor_pr', 'like', $prefix . '%')
                        ->orderByRaw("LENGTH(nomor_pr) DESC, nomor_pr DESC")
                        ->lockForUpdate()
                        ->value('nomor_pr');

                    $maxNum = 0;
                    if ($latestNomor) {
                        $suffix = substr($latestNomor, $prefixLen);
                        if (is_numeric($suffix)) {
                            $maxNum = (int) $suffix;
                        }
                    }

                    $num = $maxNum + 1;
                    $nomorPr = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);

                    while (PurchaseRequisition::where('nomor_pr', $nomorPr)->exists()) {
                        $num++;
                        $nomorPr = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
                    }
                }

                $activeBranchId  = session('active_branch_id'); // NULL for Pusat, integer for Cabang
                $activeCompanyId = session('active_company_id');

                if (!$activeCompanyId && $activeBranchId) {
                    $activeCompanyId = \App\Models\Branch::where('id', $activeBranchId)->value('company_id');
                }
                if (!$activeCompanyId) {
                    $activeCompanyId = auth()->user()->companies()->first()?->id ?? 1;
                }

                $pr = PurchaseRequisition::create([
                    'company_id'         => $activeCompanyId,
                    'nomor_pr'           => $nomorPr,
                    'branch_id'          => $activeBranchId,
                    'tanggal_pr'         => $request->tanggal_pr,
                    'tanggal_dibutuhkan' => $request->tanggal_dibutuhkan,
                    'departemen_id'      => $request->departemen_id,
                    'diminta_oleh'       => $request->diminta_oleh,
                    'keterangan'         => $request->keterangan,
                    'status'             => $request->status,
                    'dibuat_oleh'        => auth()->id(),
                ]);

                $requestItems = $request->items;
                $barangModels = MasterBarang::whereIn('id', collect($requestItems)->pluck('barang_id'))->get()->keyBy('id');

                foreach ($requestItems as $item) {
                    $barangId = $item['barang_id'];
                    $bModel = $barangModels->get($barangId);
                    $satuanId = !empty($item['satuan_id']) ? $item['satuan_id'] : ($bModel->satuan_id ?? null);
                    $faktorPengali = isset($item['faktor_pengali']) && (float)$item['faktor_pengali'] > 0 ? (float)$item['faktor_pengali'] : 1;
                    $jumlah = (float)$item['jumlah'];
                    $jumlahDasar = $jumlah * $faktorPengali;

                    $pr->details()->create([
                        'barang_id'      => $barangId,
                        'satuan_id'      => $satuanId,
                        'jumlah'         => $jumlah,
                        'faktor_pengali' => $faktorPengali,
                        'jumlah_dasar'   => $jumlahDasar,
                        'harga_estimasi' => $item['harga_estimasi'],
                        'keterangan'     => $item['keterangan'] ?? null,
                    ]);
                }

                DB::commit();
                $this->clearPrCache();

                if ($pr->status === 'Pending') {
                    \App\Http\Controllers\WhatsAppGatewayController::runAsync(fn() => \App\Http\Controllers\WhatsAppGatewayController::sendPrApprovalNotification($pr));
                }

                return response()->json(['message' => "Pengajuan pembelian {$nomorPr} berhasil dibuat!"]);
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                if ($e->getCode() == 23000 && empty($request->nomor_pr)) {
                    $attempts++;
                    usleep(50000); // Sleep 50ms and retry
                    continue;
                }
                Log::error('QueryException store PR: ' . $e->getMessage());
                return response()->json(['message' => 'Terjadi kesalahan basis data saat membuat pengajuan.'], 500);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error store PR: ' . $e->getMessage());
                return response()->json(['message' => 'Terjadi kesalahan sistem saat membuat pengajuan.'], 500);
            }
        } while ($attempts < $maxAttempts);

        return response()->json(['message' => 'Gagal membuat nomor PR unik setelah beberapa percobaan.'], 500);
    }

    public function edit(PurchaseRequisition $purchaseRequisition)
    {
        $this->authorize('edit-pr');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $purchaseRequisition->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if ($activeBranchId != $purchaseRequisition->branch_id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        $user         = auth()->user();
        $isPrivileged = $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Manager Produksi', 'Manager Finance', 'Manager']) || $user->can('approve-pr');

        if ($purchaseRequisition->status === 'Draft' && $purchaseRequisition->dibuat_oleh !== $user->id) {
            return response()->json(['message' => 'Draft pengajuan ini bersifat privat dan hanya dapat diakses oleh akun pembuatnya.'], 403);
        }

        if (!$isPrivileged && 
            $purchaseRequisition->diminta_oleh !== $user->karyawan_id &&
            $purchaseRequisition->dibuat_oleh !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengubah pengajuan ini.'], 403);
        }

        if (!in_array($purchaseRequisition->status, ['Draft', 'Pending', 'Rejected']) && !$user->hasRole('Super Admin')) {
            return response()->json(['message' => 'Pengajuan yang sudah disetujui/selesai tidak dapat diedit!'], 403);
        }

        $purchaseRequisition->load([
            'department',
            'requester',
            'creator',
            'approver',
            'details.satuan',
            'details.barang.satuan', 
            'details.barang.warna', 
            'details.barang.fotos', 
            'details.barang.primaryFoto',
            'details.barang.konversiSatuan.satuan'
        ]);
        return response()->json(['pr' => $purchaseRequisition]);
    }

    public function show($id)
    {
        $this->authorize('lihat-pr');

        $purchaseRequisition = PurchaseRequisition::withTrashed()->find($id);
        if (!$purchaseRequisition) {
            return response()->json(['message' => 'Pengajuan pembelian tidak ditemukan.'], 404);
        }

        $user         = auth()->user();
        $isPrivileged = $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Manager Produksi', 'Manager Finance', 'Manager']) || $user->can('approve-pr');
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $purchaseRequisition->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseRequisition->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if ($purchaseRequisition->status === 'Draft' && $purchaseRequisition->dibuat_oleh !== $user->id) {
            return response()->json(['message' => 'Draft pengajuan ini bersifat privat dan hanya dapat dilihat oleh akun pembuatnya.'], 403);
        }

        if (!$isPrivileged && 
            $purchaseRequisition->diminta_oleh !== $user->karyawan_id &&
            $purchaseRequisition->dibuat_oleh !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melihat pengajuan ini.'], 403);
        }

        $purchaseRequisition->load([
            'company:id,nama_perusahaan,kode_perusahaan',
            'branch:id,nama_cabang,kode_cabang',
            'department:id,department_name', 
            'requester:id,nama_lengkap,kode_karyawan', 
            'creator:id,nama_lengkap', 
            'approver:id,nama_lengkap,karyawan_id', 
            'approver.karyawan:id,nama_lengkap',
            'details.satuan:id,nama_satuan,singkatan',
            'details.barang:id,kode_barang,nama_barang,tipe_item,satuan_id,warna_id', 
            'details.barang.satuan:id,nama_satuan,singkatan', 
            'details.barang.warna:id,nama_warna,kode_hex',
            'details.barang.primaryFoto:id,barang_id,foto_path',
            'details.barang.konversiSatuan.satuan:id,nama_satuan,singkatan'
        ]);

        // Resolve Atasan / Approver or Rejector Name
        $atasanName = '-';
        $rejectorName = '-';
        if (in_array($purchaseRequisition->status, ['Approved', 'Closed']) && $purchaseRequisition->disetujui_oleh && $purchaseRequisition->approver) {
            $atasanName = $purchaseRequisition->approver->nama_lengkap 
                ?: ($purchaseRequisition->approver->karyawan?->nama_lengkap ?: '-');
        } elseif ($purchaseRequisition->status === 'Rejected' && $purchaseRequisition->disetujui_oleh && $purchaseRequisition->approver) {
            $rejectorName = $purchaseRequisition->approver->nama_lengkap 
                ?: ($purchaseRequisition->approver->karyawan?->nama_lengkap ?: '-');
        }
        $purchaseRequisition->atasan_name = $atasanName;
        $purchaseRequisition->rejector_name = $rejectorName;

        return response()->json(['pr' => $purchaseRequisition]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit-pr');

        $purchaseRequisition = PurchaseRequisition::find($id);
        if (!$purchaseRequisition) {
            return response()->json(['message' => 'Pengajuan pembelian tidak ditemukan.'], 404);
        }

        $user         = auth()->user();
        $isPrivileged = $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Manager Produksi', 'Manager Finance', 'Manager']) || $user->can('approve-pr');
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $purchaseRequisition->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseRequisition->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if ($purchaseRequisition->status === 'Draft' && $purchaseRequisition->dibuat_oleh !== $user->id) {
            return response()->json(['message' => 'Draft pengajuan ini bersifat privat dan hanya dapat diubah oleh akun pembuatnya.'], 403);
        }

        if (!$isPrivileged && 
            $purchaseRequisition->diminta_oleh !== $user->karyawan_id &&
            $purchaseRequisition->dibuat_oleh !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengubah pengajuan ini.'], 403);
        }

        if (!in_array($purchaseRequisition->status, ['Draft', 'Pending', 'Rejected']) && !$user->hasRole('Super Admin')) {
            return response()->json(['message' => 'Pengajuan yang sudah disetujui/selesai tidak dapat diedit!'], 403);
        }

        // Log request payload for debugging
        Log::info('PR Update Request Payload: ', $request->all());

        try {
            $request->validate([
                'tanggal_pr'          => 'required|date',
                'tanggal_dibutuhkan'  => 'required|date|after_or_equal:tanggal_pr',
                'departemen_id'       => 'required|exists:departments,id',
                'diminta_oleh'        => 'required|exists:karyawans,id',
                'keterangan'          => 'nullable|string|max:40',
                'status'              => 'required|in:Draft,Pending,Approved,Rejected,Closed',
                'items'               => 'required|array|min:1|max:20',
                'items.*.barang_id'   => 'required|exists:master_barang,id',
                'items.*.jumlah'      => 'required|numeric|min:0.01',
                'items.*.harga_estimasi' => 'required|numeric|min:0',
                'items.*.keterangan'  => 'nullable|string|max:40',
            ], [
                'keterangan.max'         => 'Keterangan pengajuan tidak boleh melebihi 40 karakter.',
                'items.min'              => 'Mohon ajukan minimal 1 barang.',
                'items.max'              => 'Pengajuan PR dibatasi maksimal 20 item barang.',
                'items.*.keterangan.max' => 'Catatan/keterangan item tidak boleh melebihi 40 karakter.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('PR Update Validation Errors: ', $e->errors());
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => $e->errors()
            ], 422);
        }

        // Prevent duplicate items
        $barangIds = collect($request->items)->pluck('barang_id')->filter()->toArray();
        if (count($barangIds) !== count(array_unique($barangIds))) {
            return response()->json(['message' => 'Barang yang diajukan tidak boleh duplikat!'], 422);
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'tanggal_pr'         => $request->tanggal_pr,
                'tanggal_dibutuhkan' => $request->tanggal_dibutuhkan,
                'departemen_id'      => $request->departemen_id,
                'diminta_oleh'       => $request->diminta_oleh,
                'keterangan'         => $request->keterangan,
                'status'             => $request->status,
            ];

            // If updating a Rejected / Pending / Draft PR, reset previous approval timestamp/approver
            if (in_array($request->status, ['Draft', 'Pending'])) {
                $updateData['disetujui_oleh']    = null;
                $updateData['tanggal_disetujui'] = null;
            } elseif ($request->status === 'Approved' && auth()->user()->hasRole('Super Admin')) {
                $updateData['disetujui_oleh']    = $purchaseRequisition->disetujui_oleh ?? auth()->id();
                $updateData['tanggal_disetujui'] = $purchaseRequisition->tanggal_disetujui ?? now();
            }

            $purchaseRequisition->update($updateData);

            // Sync details: Update existing, create new, delete removed
            $existingDetails = $purchaseRequisition->details->keyBy('barang_id');
            $requestItems = collect($request->items)->keyBy('barang_id');

            // Delete items that are not in request
            $toDelete = $existingDetails->keys()->diff($requestItems->keys());
            if ($toDelete->isNotEmpty()) {
                $purchaseRequisition->details()->whereIn('barang_id', $toDelete)->delete();
            }

            $barangModels = MasterBarang::whereIn('id', $requestItems->keys())->get()->keyBy('id');

            // Update or Create
            foreach ($request->items as $item) {
                $barangId = $item['barang_id'];
                $bModel = $barangModels->get($barangId);
                $satuanId = !empty($item['satuan_id']) ? $item['satuan_id'] : ($bModel->satuan_id ?? null);
                $faktorPengali = isset($item['faktor_pengali']) && (float)$item['faktor_pengali'] > 0 ? (float)$item['faktor_pengali'] : 1;
                $jumlah = (float)$item['jumlah'];
                $jumlahDasar = $jumlah * $faktorPengali;

                $detailPayload = [
                    'satuan_id'      => $satuanId,
                    'jumlah'         => $jumlah,
                    'faktor_pengali' => $faktorPengali,
                    'jumlah_dasar'   => $jumlahDasar,
                    'harga_estimasi' => $item['harga_estimasi'],
                    'keterangan'     => $item['keterangan'] ?? null,
                ];

                if ($existingDetails->has($barangId)) {
                    $existingDetails->get($barangId)->update($detailPayload);
                } else {
                    $purchaseRequisition->details()->create(array_merge([
                        'barang_id' => $barangId,
                    ], $detailPayload));
                }
            }

            DB::commit();
            $this->clearPrCache();

            if ($purchaseRequisition->status === 'Pending') {
                \App\Http\Controllers\WhatsAppGatewayController::runAsync(fn() => \App\Http\Controllers\WhatsAppGatewayController::sendPrApprovalNotification($purchaseRequisition));
            }

            return response()->json(['message' => 'Pengajuan pembelian berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update PR: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui pengajuan.'], 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('hapus-pr');

        $purchaseRequisition = PurchaseRequisition::withTrashed()->find($id);
        if (!$purchaseRequisition || $purchaseRequisition->trashed()) {
            return response()->json(['message' => 'Pengajuan pembelian sudah dihapus atau tidak ditemukan.'], 200);
        }

        $user         = auth()->user();
        $isPrivileged = $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Manager Produksi', 'Manager Finance', 'Manager']) || $user->can('approve-pr');
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $purchaseRequisition->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseRequisition->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if ($purchaseRequisition->status === 'Draft' && $purchaseRequisition->dibuat_oleh !== $user->id) {
            return response()->json(['message' => 'Draft pengajuan ini bersifat privat dan hanya dapat dihapus oleh akun pembuatnya.'], 403);
        }

        if (!$isPrivileged && 
            $purchaseRequisition->diminta_oleh !== $user->karyawan_id &&
            $purchaseRequisition->dibuat_oleh !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menghapus pengajuan ini.'], 403);
        }

        if ($purchaseRequisition->status !== 'Draft' && !$user->hasRole('Super Admin')) {
            return response()->json(['message' => 'Hanya pengajuan berstatus Draft yang dapat dihapus! Pengajuan berstatus ' . $purchaseRequisition->status . ' tidak dapat dihapus.'], 403);
        }

        try {
            DB::beginTransaction();
            $purchaseRequisition->details()->delete();
            $purchaseRequisition->delete();
            DB::commit();
            $this->clearPrCache();
            return response()->json(['message' => 'Pengajuan pembelian berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete PR: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus pengajuan.'], 500);
        }
    }

    public function approve($id)
    {
        $this->authorize('approve-pr');

        $pr = PurchaseRequisition::find($id);
        if (!$pr) {
            return response()->json(['message' => 'Pengajuan pembelian tidak ditemukan.'], 404);
        }

        $user         = auth()->user();
        $isPrivileged = $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Manager Produksi', 'Manager Finance', 'Manager']) || $user->can('approve-pr');
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $pr->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($pr->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if ($pr->status !== 'Pending') {
            return response()->json(['message' => 'Hanya pengajuan dengan status Pending yang dapat disetujui!'], 422);
        }

        try {
            DB::beginTransaction();
            $pr->update([
                'status'            => 'Approved',
                'alasan_penolakan'  => null,
                'disetujui_oleh'    => auth()->id(),
                'tanggal_disetujui' => now(),
            ]);
            DB::commit();
            $this->clearPrCache();

            // Send WhatsApp notification asynchronously without blocking fast response
            \App\Http\Controllers\WhatsAppGatewayController::runAsync(function() use ($pr) {
                \App\Http\Controllers\WhatsAppGatewayController::sendPrApprovedNotification($pr);
            });

            return response()->json(['message' => 'Pengajuan pembelian berhasil disetujui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approve PR: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menyetujui pengajuan.'], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('approve-pr');

        $pr = PurchaseRequisition::find($id);
        if (!$pr) {
            return response()->json(['message' => 'Pengajuan pembelian tidak ditemukan.'], 404);
        }

        $user         = auth()->user();
        $isPrivileged = $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Manager Produksi', 'Manager Finance', 'Manager']) || $user->can('approve-pr');
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $pr->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($pr->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if ($pr->status !== 'Pending') {
            return response()->json(['message' => 'Hanya pengajuan dengan status Pending yang dapat ditolak!'], 422);
        }

        $alasan = $request->input('alasan');

        try {
            DB::beginTransaction();
            $pr->update([
                'status'            => 'Rejected',
                'alasan_penolakan'  => $alasan,
                'disetujui_oleh'    => auth()->id(),
                'tanggal_disetujui' => now(),
            ]);
            DB::commit();
            $this->clearPrCache();

            // Send WhatsApp notification asynchronously
            \App\Http\Controllers\WhatsAppGatewayController::runAsync(function() use ($pr, $alasan) {
                \App\Http\Controllers\WhatsAppGatewayController::sendPrRejectedNotification($pr, $alasan);
            });

            return response()->json(['message' => 'Pengajuan pembelian berhasil ditolak!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reject PR: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menolak pengajuan.'], 500);
        }
    }

    public function print($id)
    {
        $user = auth()->user();
        $isPrivileged = $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Manager Produksi', 'Manager Finance', 'Manager']) || $user->can('approve-pr');

        if (!$user->can('cetak-pr') && !$user->can('print-pr') && !$isPrivileged) {
            $this->authorize('cetak-pr');
        }

        $purchaseRequisition = PurchaseRequisition::withTrashed()->find($id);
        if (!$purchaseRequisition) {
            abort(404, 'Pengajuan pembelian tidak ditemukan.');
        }

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $purchaseRequisition->company_id != $activeCompanyId) {
            abort(403, 'Anda tidak memiliki akses ke data perusahaan lain.');
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseRequisition->branch_id)) {
            abort(403, 'Anda tidak memiliki akses ke data cabang lain / pusat.');
        }

        if ($purchaseRequisition->status === 'Draft' && $purchaseRequisition->dibuat_oleh !== $user->id) {
            abort(403, 'Draft pengajuan ini bersifat privat dan hanya dapat dicetak oleh akun pembuatnya.');
        }

        if (!$isPrivileged && 
            $purchaseRequisition->diminta_oleh !== $user->karyawan_id &&
            $purchaseRequisition->dibuat_oleh !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak pengajuan ini.');
        }

        $purchaseRequisition->load([
            'company',
            'department',
            'requester.position',
            'creator.activeSignature',
            'creator.position',
            'approver.activeSignature',
            'approver.position',
            'details.satuan',
            'details.barang.satuan',
            'details.barang.warna',
            'details.barang.fotos',
            'details.barang.primaryFoto'
        ]);

        $activeBranch = null;
        if (session()->has('active_branch_id')) {
            $activeBranch = \App\Models\Branch::find(session('active_branch_id'));
        }

        $activeCompany = null;
        if (session()->has('active_company_id')) {
            $activeCompany = \App\Models\Company::find(session('active_company_id'));
        }
        if (!$activeCompany && $purchaseRequisition->company) {
            $activeCompany = $purchaseRequisition->company;
        }

        $designatedSupervisor = null;
        if (in_array($purchaseRequisition->status, ['Approved', 'Closed']) && $purchaseRequisition->disetujui_oleh && $purchaseRequisition->approver) {
            $designatedSupervisor = $purchaseRequisition->approver;
        }

        return view('modul_purchasing.pembuatanpr.printpr', compact('purchaseRequisition', 'activeBranch', 'activeCompany', 'designatedSupervisor'));
    }

    public function searchBarang(Request $request)
    {
        $this->authorize('lihat-pr');

        $search = $request->input('search.value');
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));
        $tipe = $request->input('tipe_item');

        $query = MasterBarang::with([
                'satuan:id,nama_satuan,singkatan',
                'primaryFoto:id,barang_id,foto_path',
                'warna:id,nama_warna,kode_hex',
                'brand:id,nama_brand',
                'konversiSatuan.satuan:id,nama_satuan,singkatan',
            ])
            ->select(['id','kode_barang','nama_barang','tipe_item','satuan_id','warna_id','brand_id','status','spesifikasi'])
            ->forActiveCompany()
            ->where('tipe_item', '!=', 'Bundle')
            ->active();

        if (!empty($tipe)) {
            if ($tipe === 'Stok') {
                $query->whereIn('tipe_item', ['Stok', 'Bundle']);
            } else {
                $query->where('tipe_item', $tipe);
            }
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                // Prefer prefix match (uses index); wildcard fallback only for mid-word searches
                $q->where('nama_barang', 'like', "{$search}%")
                  ->orWhere('kode_barang', 'like', "{$search}%");
            });
        }

        // ── Count strategy ─────────────────────────────────────────────────────
        // Accurate cached COUNT per company, tipe, and search term
        $activeCompanyId = session('active_company_id');
        $cacheKey = 'srch_brg_cnt_' . md5(($activeCompanyId ?? 0) . '|' . ($tipe ?? '') . '|' . ($search ?? ''));
        $total = \Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function() use ($query) {
            return (clone $query)->count();
        });
        $items = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($items as $b) {
            $konversisList = [];
            if ($b->satuan) {
                $konversisList[] = [
                    'satuan_id'      => $b->satuan->id,
                    'nama_satuan'    => $b->satuan->nama_satuan,
                    'singkatan'      => $b->satuan->singkatan,
                    'faktor_pengali' => 1,
                    'is_base'        => true,
                    'label'          => $b->satuan->nama_satuan . ' (Satuan Dasar)',
                ];
            }
            if ($b->konversiSatuan) {
                foreach ($b->konversiSatuan as $ks) {
                    if ($ks->satuan && $ks->is_satuan_beli) {
                        $baseUnitName = $b->satuan->singkatan ?? $b->satuan->nama_satuan ?? '';
                        $konversisList[] = [
                            'satuan_id'      => $ks->satuan->id,
                            'nama_satuan'    => $ks->satuan->nama_satuan,
                            'singkatan'      => $ks->satuan->singkatan,
                            'faktor_pengali' => (float)$ks->faktor_pengali,
                            'is_base'        => false,
                            'label'          => $ks->satuan->nama_satuan . ' (1 ' . $ks->satuan->nama_satuan . ' = ' . ($ks->faktor_pengali + 0) . ' ' . $baseUnitName . ')',
                        ];
                    }
                }
            }

            // Only primaryFoto is used in the search result table
            $data[] = [
                'id'          => $b->id,
                'foto'        => $b->primaryFoto ? asset('storage/' . $b->primaryFoto->foto_path) : '',
                'semua_foto'  => $b->primaryFoto ? asset('storage/' . $b->primaryFoto->foto_path) : '',
                'kode'        => $b->kode_barang,
                'nama'        => $b->nama_barang,
                'brand'       => $b->brand->nama_brand ?? '-',
                'tipe_item'   => $b->tipe_item,
                'satuan'      => $b->satuan->singkatan ?? $b->satuan->nama_satuan ?? 'pcs',
                'satuan_id'   => $b->satuan_id,
                'satuan_nama' => $b->satuan->nama_satuan ?? '',
                'warna_nama'  => $b->warna->nama_warna ?? '',
                'warna_hex'   => $b->warna->kode_hex ?? '',
                'spesifikasi' => $b->spesifikasi,
                'konversis'   => $konversisList,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    private function clearPrCache()
    {
        try {
            \Illuminate\Support\Facades\Cache::flush();
        } catch (\Throwable $e) {
            Log::warning('Failed to clear PR cache: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('lihat-pr');

        $tglMulai    = $request->input('tgl_mulai');
        $tglSelesai  = $request->input('tgl_selesai');
        $status      = $request->input('status');
        $departemen  = $request->input('departemen');

        if (empty($tglMulai) || empty($tglSelesai)) {
            return response()->json([
                'success' => false,
                'message' => 'Harap tentukan Tanggal Mulai dan Tanggal Selesai (Maksimal 30 hari).'
            ], 422);
        }

        try {
            $start = Carbon::parse($tglMulai)->startOfDay();
            $end   = Carbon::parse($tglSelesai)->endOfDay();

            if ($start->gt($end)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal Mulai tidak boleh lebih besar dari Tanggal Selesai.'
                ], 422);
            }

            $diffInDays = $start->diffInDays($end);
            if ($diffInDays > 30) {
                return response()->json([
                    'success' => false,
                    'message' => "Export Excel dibatasi maksimal 30 hari. Rentang yang Anda pilih adalah {$diffInDays} hari."
                ], 422);
            }

            $user            = auth()->user();
            $isPrivileged    = $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Manager Produksi', 'Manager Finance', 'Manager']) || $user->can('approve-pr');
            $activeBranchId  = session('active_branch_id');
            $activeCompanyId = session('active_company_id');

            $query = PurchaseRequisition::with(['department', 'requester', 'details.satuan', 'details.barang.satuan'])
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->when(!empty($activeBranchId), fn($q) => $q->where('branch_id', intval($activeBranchId)), fn($q) => $q->whereNull('branch_id'))
                ->whereBetween('tanggal_pr', [$start->toDateString(), $end->toDateString()]);

            if ($isPrivileged) {
                $query->where(function($q) use ($user) {
                    $q->where('status', '!=', 'Draft')
                      ->orWhere('dibuat_oleh', $user->id);
                });
            } else {
                $query->where(function($q) use ($user) {
                    if ($user->karyawan_id) {
                        $q->where(function($q2) use ($user) {
                            $q2->where('status', '!=', 'Draft')
                               ->where(function($q3) use ($user) {
                                   $q3->where('diminta_oleh', $user->karyawan_id)
                                      ->orWhere('dibuat_oleh', $user->id);
                               });
                        })->orWhere(function($q2) use ($user) {
                            $q2->where('status', 'Draft')
                               ->where('dibuat_oleh', $user->id);
                        });
                    } else {
                        $q->where('dibuat_oleh', $user->id);
                    }
                });
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            if (!empty($departemen)) {
                $deptId = Department::where('department_name', $departemen)->value('id');
                if ($deptId) {
                    $query->where('departemen_id', $deptId);
                }
            }

            $prs = $query->orderBy('tanggal_pr', 'asc')->get();

            if ($prs->isEmpty()) {
                return response("<script>alert('Tidak ada data PR yang ditemukan pada rentang tanggal tersebut.'); history.back();</script>");
            }

            $headers = [
                'No',
                'Nomor PR',
                'Tanggal PR',
                'Tanggal Dibutuhkan',
                'Departemen',
                'Diminta Oleh',
                'Status',
                'Kode Barang',
                'Nama Barang',
                'Jumlah',
                'Satuan',
                'Jumlah (Satuan Dasar)',
                'Satuan Dasar',
                'Catatan / Keterangan Item'
            ];

            $rows = [$headers];
            $no = 1;

            foreach ($prs as $pr) {
                if ($pr->details && $pr->details->count() > 0) {
                    foreach ($pr->details as $d) {
                        $satuanNama = $d->satuan->nama_satuan ?? ($d->barang->satuan->nama_satuan ?? '-');
                        $baseSatuanNama = $d->barang->satuan->nama_satuan ?? '-';
                        $jumlahDasar = (float)($d->jumlah_dasar ?? ($d->jumlah * ($d->faktor_pengali ?? 1)));

                        $rows[] = [
                            $no++,
                            $pr->nomor_pr,
                            date('d-m-Y', strtotime($pr->tanggal_pr)),
                            date('d-m-Y', strtotime($pr->tanggal_dibutuhkan)),
                            $pr->department->department_name ?? '-',
                            $pr->requester->nama_lengkap ?? '-',
                            $pr->status,
                            $d->barang->kode_barang ?? '-',
                            $d->barang->nama_barang ?? $d->nama_barang_manual ?? '-',
                            (float) $d->jumlah,
                            $satuanNama,
                            $jumlahDasar,
                            $baseSatuanNama,
                            $d->keterangan ?? '-'
                        ];
                    }
                } else {
                    $rows[] = [
                        $no++,
                        $pr->nomor_pr,
                        date('d-m-Y', strtotime($pr->tanggal_pr)),
                        date('d-m-Y', strtotime($pr->tanggal_dibutuhkan)),
                        $pr->department->department_name ?? '-',
                        $pr->requester->nama_lengkap ?? '-',
                        $pr->status,
                        '-', '-', 0, '-', 0, '-', '-'
                    ];
                }
            }

            $filename = 'Export_PR_' . date('Ymd', strtotime($tglMulai)) . '_sd_' . date('Ymd', strtotime($tglSelesai)) . '.xlsx';
            $xlsx = SimpleXLSXGen::fromArray($rows);
            $xlsx->downloadAs($filename);
            exit();

        } catch (\Exception $e) {
            Log::error('Error Export PR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses export data PR.'
            ], 500);
        }
    }
}
