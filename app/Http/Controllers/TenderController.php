<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use App\Models\TenderItem;
use App\Models\TenderDocument;
use App\Models\TenderFollowup;
use App\Models\TenderApproval;
use App\Models\MasterCustomer;
use App\Models\MasterBarang;
use App\Models\MasterKategoriBarang;
use App\Models\MasterCurrency;
use App\Models\MasterPajak;
use App\Models\PaymentTerm;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

use Yajra\DataTables\Facades\DataTables;

class TenderController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-tender');

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $activeCompany = Company::find($activeCompanyId);
        $activeBranch = session('active_branch_id') ? Branch::find(session('active_branch_id')) : null;

        $user = Auth::user();
        $canSeeHpp = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-tender') || $user->can('lihat-hpp-stok') || $user->can('akses-accounting')) : false;

        $currencies = MasterCurrency::where('status', 'Active')->get();
        $customers = MasterCustomer::forActiveCompany()->where('status', 'Active')->orderBy('nama_customer', 'asc')->get();
        $salesList = Karyawan::forActiveCompany()
            ->where('status', 'Aktif')
            ->whereHas('department', function ($q) {
                $q->where('department_name', 'LIKE', '%Sales%')
                  ->orWhere('department_name', 'LIKE', '%Marketing%');
            })->get();

        if ($salesList->isEmpty()) {
            $salesList = Karyawan::forActiveCompany()->where('status', 'Aktif')->get();
        }
        
        // Active Category Filter for Tender according to Setting Visibilitas Kategori
        $kategoriList = MasterKategoriBarang::forActiveCompany()
            ->where('status', 'Active')
            ->where('tampil_di_sales', true)
            ->orderBy('nama_kategori', 'asc')
            ->get();

        $allowedKategoriIds = $kategoriList->pluck('id')->toArray();

        // Products with Moving Average HPP and 3-Tier Prices filtered strictly by Category Visibility
        $barangList = MasterBarang::where('status', 'Active')
            ->whereNull('parent_id')
            ->when($activeCompanyId, function ($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId);
            })
            ->whereIn('kategori_barang_id', $allowedKategoriIds)
            ->with([
                'satuan', 
                'kategori', 
                'primaryFoto', 
                'fotos',
                'bundleComponents.childBarang.satuan',
                'bundleComponents.childBarang.primaryFoto',
                'bundleComponents.childBarang.fotos'
            ])
            ->orderBy('nama_barang', 'asc')
            ->get();

        // Calculate moving average HPP and all sources
        $barangIds = $barangList->pluck('id')->toArray();
        $hppMap = $this->resolveBarangHppMap($barangIds, $canSeeHpp);

        $barangList->transform(function ($b) use ($hppMap, $canSeeHpp) {
            $b->hpp_referensi = $canSeeHpp ? ($hppMap[$b->id] ?? 0) : 0;
            $b->is_bundle = ($b->tipe_item === 'Bundle');
            if ($b->is_bundle && $b->bundleComponents) {
                $b->bundle_components = $b->bundleComponents->map(function ($comp) {
                    $cBrg = $comp->childBarang;
                    $cFoto = ($cBrg && $cBrg->primaryFoto) ? $cBrg->primaryFoto->foto_path : (($cBrg && $cBrg->fotos && $cBrg->fotos->first()) ? $cBrg->fotos->first()->foto_path : 'uploads/barang/placeholder_garment.png');
                    return [
                        'nama_barang' => $cBrg ? $cBrg->nama_barang : 'Barang',
                        'kode_barang' => $cBrg ? $cBrg->kode_barang : '',
                        'foto_path'   => $cFoto,
                        'qty'         => (float)$comp->qty,
                        'satuan'      => ($cBrg && $cBrg->satuan) ? $cBrg->satuan->nama_satuan : 'Pcs',
                    ];
                })->values();
            } else {
                $b->bundle_components = [];
            }
            return $b;
        });

        // Master Pajak Lists (PPN and PPh)
        $masterPajaks = MasterPajak::forActiveCompany()->active()->orderBy('nama_pajak')->get();

        $pajakPpnList = $masterPajaks->filter(function ($p) {
            return stripos($p->nama_pajak, 'PPN') !== false || stripos($p->kode_pajak, 'PPN') !== false || stripos($p->kode_pajak, 'TX001') !== false || stripos($p->kode_pajak, 'TX002') !== false;
        })->values();
        if ($pajakPpnList->isEmpty()) {
            $pajakPpnList = $masterPajaks;
        }

        $pajakPphList = $masterPajaks->filter(function ($p) {
            return stripos($p->nama_pajak, 'PPh') !== false || stripos($p->kode_pajak, 'PPh') !== false || stripos($p->kode_pajak, 'TX003') !== false;
        })->values();
        if ($pajakPphList->isEmpty()) {
            $pajakPphList = $masterPajaks;
        }

        // Master Payment Terms
        $paymentTerms = PaymentTerm::forActiveCompany()->where('status', 'Active')->orderBy('nama_term', 'asc')->get();

        // Branches under active company for PT/Branch filtering & switching
        $branches = Branch::where('company_id', $activeCompanyId)->where('status', 'Active')->orderBy('nama_cabang', 'asc')->get();

        // Summary KPI Metrics
        $tenderQuery = Tender::forActiveCompany();
        $countTotal = (clone $tenderQuery)->count();
        $countWon = (clone $tenderQuery)->where('status', 'Won')->count();
        $countLost = (clone $tenderQuery)->where('status', 'Lost')->count();
        $countActive = (clone $tenderQuery)->whereIn('status', ['Draft', 'Sent', 'Negotiation'])->count();
        
        $valueWon = (clone $tenderQuery)->where('status', 'Won')->sum('nilai_estimasi');
        $valuePipeline = (clone $tenderQuery)->whereIn('status', ['Draft', 'Sent', 'Negotiation'])->sum('nilai_estimasi');
        $winRate = ($countTotal > 0) ? round(($countWon / $countTotal) * 100, 1) : 0;

        return view('modul_marketing.kontrak.viewtender', compact(
            'currencies',
            'customers',
            'salesList',
            'kategoriList',
            'barangList',
            'activeCompany',
            'activeBranch',
            'branches',
            'masterPajaks',
            'pajakPpnList',
            'pajakPphList',
            'paymentTerms',
            'countTotal',
            'countWon',
            'countLost',
            'countActive',
            'valueWon',
            'valuePipeline',
            'winRate',
            'canSeeHpp'
        ));
    }

    public function getData(Request $request)
    {
        $this->authorize('lihat-tender');

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        $query = Tender::query()
            ->with(['customer', 'sales', 'salesOrder', 'items.barang', 'company', 'branch', 'approvals' => function ($q) {
                $q->latest();
            }])->latest();

        if (!empty($activeCompanyId)) {
            $query->where('tenders.company_id', $activeCompanyId);
        }

        // Filter Entitas: 'all' (Pusat & Seluruh Cabang), 'pusat' (branch_id is null), or specific branch_id
        if ($request->filled('filter_entitas') && $request->filter_entitas !== 'all') {
            if ($request->filter_entitas === 'pusat') {
                $query->whereNull('tenders.branch_id');
            } else {
                $query->where('tenders.branch_id', $request->filter_entitas);
            }
        } elseif (!$request->filled('filter_entitas')) {
            // Default based on current session
            if (!empty($activeBranchId)) {
                $query->where('tenders.branch_id', $activeBranchId);
            } else {
                $query->whereNull('tenders.branch_id');
            }
        }

        // Status Filter
        if ($request->filled('filter_status') && $request->filter_status !== 'all') {
            $query->where('status', $request->filter_status);
        }

        // Priority Filter
        if ($request->filled('filter_priority') && $request->filter_priority !== 'all') {
            $query->where('priority', $request->filter_priority);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nomor_kontrak', function ($row) {
                return $row->nomor_kontrak ?: '-';
            })
            ->addColumn('customer_name', function ($row) {
                return $row->customer ? ($row->customer->nama_perusahaan ?: $row->customer->nama_customer) : '-';
            })
            ->addColumn('sales_name', function ($row) {
                return $row->sales ? $row->sales->nama_lengkap : '-';
            })
            ->addColumn('kategori_instansi', function ($row) {
                return $row->kategori_instansi ?: 'Pemerintah / B2G';
            })
            ->addColumn('entitas_badge', function ($row) {
                if ($row->branch) {
                    return '<span class="badge badge-soft-emerald fs-9 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-shop me-1"></i>' . e($row->branch->nama_cabang) . '</span>';
                }
                return '<span class="badge badge-soft-indigo fs-9 px-2.5 py-1 rounded-pill fw-semibold"><i class="bi bi-building me-1"></i>PT (Pusat)</span>';
            })
            ->addColumn('entitas_name', function ($row) {
                return $row->branch ? 'Cabang ' . $row->branch->nama_cabang : 'PT (Pusat)';
            })
            ->addColumn('deadline', function ($row) {
                return $row->deadline ? $row->deadline->format('d M Y') : '-';
            })
            ->addColumn('days_to_deadline', function ($row) {
                return $row->deadline ? now()->startOfDay()->diffInDays($row->deadline->startOfDay(), false) : 0;
            })
            ->addColumn('tanggal', function ($row) {
                return $row->tanggal ? $row->tanggal->format('d M Y') : '-';
            })
            ->addColumn('items_count', function ($row) {
                return $row->items ? $row->items->count() : 0;
            })
            ->addColumn('has_sales_order', function ($row) {
                return !empty($row->sales_order_id);
            })
            ->editColumn('nilai_estimasi', function ($row) {
                return (float)($row->nilai_estimasi ?? 0);
            })
            ->rawColumns(['entitas_badge'])
            ->make(true);
    }

    /**
     * Get Tenders Grouped by Pipeline Stages for Interactive Kanban Board
     */
    public function getKanbanData(Request $request)
    {
        $this->authorize('lihat-tender');

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        $query = Tender::query()
            ->with(['customer', 'sales', 'salesOrder', 'items.barang', 'company', 'branch'])
            ->orderBy('deadline', 'asc');

        if (!empty($activeCompanyId)) {
            $query->where('tenders.company_id', $activeCompanyId);
        }

        if ($request->filled('filter_entitas') && $request->filter_entitas !== 'all') {
            if ($request->filter_entitas === 'pusat') {
                $query->whereNull('tenders.branch_id');
            } else {
                $query->where('tenders.branch_id', $request->filter_entitas);
            }
        } elseif (!$request->filled('filter_entitas')) {
            if (!empty($activeBranchId)) {
                $query->where('tenders.branch_id', $activeBranchId);
            } else {
                $query->whereNull('tenders.branch_id');
            }
        }

        $tenders = $query->get();

        $stages = [
            'Draft' => [
                'label' => 'Draft RKS',
                'badge_class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                'color' => '#64748b',
                'items' => []
            ],
            'Pending Approval' => [
                'label' => 'Menunggu Approval',
                'badge_class' => 'bg-warning-subtle text-warning border border-warning-subtle',
                'color' => '#f59e0b',
                'items' => []
            ],
            'Approved' => [
                'label' => 'Disetujui (Siap Kirim)',
                'badge_class' => 'bg-primary-subtle text-primary border border-primary-subtle',
                'color' => '#3b82f6',
                'items' => []
            ],
            'Sent' => [
                'label' => 'Penawaran Dikirim',
                'badge_class' => 'bg-info-subtle text-info border border-info-subtle',
                'color' => '#0284c7',
                'items' => []
            ],
            'Negotiation' => [
                'label' => 'Aanwijzing & Negosiasi',
                'badge_class' => 'bg-indigo-subtle text-indigo border border-indigo-subtle',
                'color' => '#6366f1',
                'items' => []
            ],
            'Won' => [
                'label' => 'Menang (SPK Terbit)',
                'badge_class' => 'bg-success-subtle text-success border border-success-subtle',
                'color' => '#10b981',
                'items' => []
            ],
            'Lost' => [
                'label' => 'Kalah / Ditolak',
                'badge_class' => 'bg-danger-subtle text-danger border border-danger-subtle',
                'color' => '#ef4444',
                'items' => []
            ]
        ];

        foreach ($tenders as $t) {
            $statusKey = array_key_exists($t->status, $stages) ? $t->status : 'Draft';
            if ($t->status === 'Rejected') {
                $statusKey = 'Lost';
            }
            
            $daysToDeadline = now()->startOfDay()->diffInDays($t->deadline->startOfDay(), false);

            $stages[$statusKey]['items'][] = [
                'id' => $t->id,
                'nomor_kontrak' => $t->nomor_kontrak ?? '-',
                'nama_tender' => $t->nama_tender,
                'kategori_instansi' => $t->kategori_instansi ?: 'B2G / Instansi',
                'entitas_label' => $t->branch ? 'Cabang ' . $t->branch->nama_cabang : 'PT (Pusat)',
                'branch_id' => $t->branch_id,
                'customer_name' => $t->customer ? ($t->customer->nama_perusahaan ?: $t->customer->nama_customer) : '-',
                'nilai_estimasi' => (float)($t->nilai_estimasi ?? 0),
                'deadline' => $t->deadline->format('d M Y'),
                'days_to_deadline' => $daysToDeadline,
                'priority' => $t->priority,
                'status' => $t->status,
                'sales_name' => $t->sales ? $t->sales->nama_lengkap : '-',
                'items_count' => $t->items->count(),
                'sales_order_id' => $t->sales_order_id,
            ];
        }

        return response()->json([
            'stages' => $stages
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-tender');

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);

        $request->validate([
            'nomor_kontrak'         => 'required|string|max:40',
            'customer_id'           => 'required|exists:master_customers,id',
            'kategori_instansi'     => 'nullable|string|max:50',
            'sales_id'              => 'required|exists:karyawans,id',
            'nama_tender'           => 'required|string|max:50',
            'tanggal'               => 'required|date',
            'deadline'              => 'required|date|after_or_equal:tanggal',
            'estimasi_closing'      => 'required|date|after_or_equal:tanggal',
            'currency'              => 'required|string|max:10',
            'status'                => 'nullable|string',
            'priority'              => 'required|in:Low,Medium,High',
            'nilai_deal'            => 'nullable|numeric|min:0',
            'nilai_estimasi'        => 'nullable|numeric|min:0|max_digits:20',
            'diskon_tipe'           => 'nullable|string|in:Persen,Nominal',
            'diskon_persen'         => 'nullable|numeric|min:0',
            'diskon_nominal'        => 'nullable|numeric|min:0',
            'payment_term_id'       => 'nullable',
            'ppn_pajak_id'          => 'nullable',
            'tipe_ppn'              => 'nullable|string',
            'tarif_ppn'             => 'nullable|numeric|min:0',
            'pph_pajak_id'          => 'nullable',
            'tipe_pph'              => 'nullable|string',
            'tarif_pph'             => 'nullable|numeric|min:0',
            'biaya_ekstra'          => 'nullable|numeric|min:0',
            'catatan_biaya_ekstra'  => 'nullable|string|max:255',
            'catatan'               => 'nullable|string|max:50',
            'items'                 => 'nullable|array',
            'items.*.barang_id'     => 'nullable',
            'items.*.qty'           => 'nullable|numeric|min:0.01',
            'items.*.satuan'        => 'nullable|string|max:50',
            'items.*.harga'         => 'nullable|numeric|min:0',
            'documents'             => 'nullable|array|max:30',
            'documents.*'           => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,zip,rar',
            'document_names'        => 'nullable|array|max:30',
        ], [
            'nama_tender.required'   => 'Nama Proyek / Pengadaan Tender wajib diisi.',
            'nama_tender.max'        => 'Nama Proyek / Pengadaan Tender maksimal 50 karakter.',
            'nomor_kontrak.required' => 'Nomor Tender / Registrasi Proyek wajib diisi.',
            'nomor_kontrak.max'      => 'Nomor Tender / Registrasi Proyek maksimal 40 karakter.',
            'catatan.max'            => 'Catatan Tender maksimal 50 karakter.',
            'nilai_estimasi.max_digits' => 'Nilai Pagu Anggaran maksimal 20 digit.',
            'documents.*.max'       => 'Ukuran berkas lampiran tidak boleh melebihi 10 MB.',
            'documents.*.mimes'     => 'Format berkas lampiran harus berupa PDF, Word, Excel, atau ZIP/RAR.',
            'documents.max'         => 'Maksimal 30 berkas dokumen yang dapat dilampirkan.',
        ]);

        try {
            DB::beginTransaction();

            $nomor = trim($request->nomor_kontrak);

            $paguNilai = (float)($request->nilai_estimasi ?: 0);

            $branchId = null;
            if ($request->filled('branch_id')) {
                $branchId = ($request->branch_id === 'pusat' || empty($request->branch_id)) ? null : (int)$request->branch_id;
            } elseif (session()->has('active_branch_id')) {
                $branchId = session('active_branch_id');
            }

            // Create Tender
            $tender = Tender::create([
                'company_id'            => $activeCompanyId,
                'branch_id'             => $branchId,
                'nomor_kontrak'         => $nomor,
                'customer_id'           => $request->customer_id,
                'kategori_instansi'     => $request->kategori_instansi ?: 'Pemerintah / B2G',
                'sales_id'              => $request->sales_id,
                'nama_tender'           => $request->nama_tender,
                'tanggal'               => $request->tanggal,
                'deadline'              => $request->deadline,
                'estimasi_closing'      => $request->estimasi_closing,
                'currency'              => $request->currency ?: 'IDR',
                'status'                => $request->status ?: 'Draft',
                'tahap_tender'          => $this->getStageLabel($request->status ?: 'Draft'),
                'priority'              => $request->priority ?: 'Medium',
                'nilai_estimasi'        => $paguNilai,
                'catatan'               => $request->catatan,
                'created_by'            => auth()->id(),
                'updated_by'            => auth()->id(),
            ]);

            // Save Items
            if (is_array($request->items)) {
                foreach ($request->items as $it) {
                    $bId = !empty($it['barang_id']) ? $it['barang_id'] : null;
                    $namaCustom = !empty($it['nama_barang_custom']) ? $it['nama_barang_custom'] : null;
                    $namaDisplay = !empty($it['nama_barang_display']) ? $it['nama_barang_display'] : null;
                    
                    if (!$bId && !$namaCustom && !$namaDisplay) continue;

                    $qty = max(0.01, floatval($it['qty'] ?? 1));
                    $harga = max(0, floatval($it['harga'] ?? 0));
                    $satuan = !empty($it['satuan']) ? $it['satuan'] : 'Pcs';

                    TenderItem::create([
                        'tender_id'          => $tender->id,
                        'barang_id'          => $bId,
                        'nama_barang_custom' => $namaCustom ?: ($bId ? null : $namaDisplay),
                        'qty'                => $qty,
                        'satuan'             => $satuan,
                        'catatan_item'       => $it['catatan_item'] ?? null,
                    ]);
                }
            }

            // Save Documents
            if ($request->hasFile('documents')) {
                $uploadDir = public_path('uploads/tenders');
                if (!File::exists($uploadDir)) {
                    File::makeDirectory($uploadDir, 0755, true);
                }

                foreach ($request->file('documents') as $index => $file) {
                    if (!$file || !$file->isValid()) continue;
                    
                    $docName = $request->input("document_names.{$index}") ?: $request->input("document_types.{$index}", 'Lainnya');
                    $docName = mb_substr(trim($docName), 0, 40);

                    $filename = 'tnd_' . $tender->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);

                    TenderDocument::create([
                        'tender_id'     => $tender->id,
                        'nama_file'     => $file->getClientOriginalName(),
                        'jenis_dokumen' => $docName,
                        'path'          => 'uploads/tenders/' . $filename,
                    ]);
                }
            }

            // Init initial Approval
            $salesUser = User::where('karyawan_id', $request->sales_id)->first();
            TenderApproval::create([
                'tender_id' => $tender->id,
                'user_id'   => $salesUser ? $salesUser->id : auth()->id(),
                'status'    => 'Pending',
            ]);

            if ($tender->status === 'Pending Approval') {
                $tender->update(['submitted_at' => now()]);
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendTenderSubmissionNotification($tender)
                );
            }

            DB::commit();
            $msg = ($tender->status === 'Pending Approval')
                ? 'Proposal Tender berhasil dibuat & langsung diajukan ke Atasan!'
                : 'Proposal Tender berhasil disimpan sebagai Draft.';

            return response()->json([
                'success'   => true, 
                'message'   => $msg, 
                'tender_id' => $tender->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store tender: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan proposal tender: ' . $e->getMessage()
            ], 500);
        }
    }

    private function authorizeCompanyAccess(Tender $tender)
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $user = Auth::user();

        if ($user && $user->hasAnyRole(['Super Admin', 'Owner'])) {
            return;
        }

        if ($activeCompanyId && $tender->company_id && $tender->company_id != $activeCompanyId) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang mengakses data tender perusahaan lain.');
        }

        if (!empty($activeBranchId) && $tender->branch_id != $activeBranchId) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang mengakses data tender milik cabang lain atau PT Pusat.');
        }

        if (empty($activeBranchId) && !empty($tender->branch_id)) {
            abort(403, 'Akses ditolak. Anda sedang aktif di PT Pusat dan tidak dapat mengelola langsung data tender milik cabang.');
        }
    }

    public function edit(Request $request, Tender $tender)
    {
        $this->authorize('edit-tender');
        $this->authorizeCompanyAccess($tender);
        
        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $tender->load([
            'branch',
            'items.barang.satuan',
            'items.barang.primaryFoto',
            'items.barang.fotos',
            'items.barang.bundleComponents.childBarang.satuan',
            'items.barang.bundleComponents.childBarang.primaryFoto',
            'items.barang.bundleComponents.childBarang.fotos',
            'customer',
            'sales',
            'documents'
        ]);

        $user = Auth::user();
        $canSeeHpp = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-tender') || $user->can('lihat-hpp-stok') || $user->can('akses-accounting')) : false;

        if (!$canSeeHpp) {
            $tender->total_hpp = null;
            $tender->estimasi_laba_kotor = null;
            $tender->margin_persen = null;
        }

        return response()->json([
            'success' => true,
            'tender' => $tender,
            'can_see_hpp' => $canSeeHpp
        ]);
    }

    public function getDetailJson(Tender $tender)
    {
        $this->authorize('lihat-tender');
        $this->authorizeCompanyAccess($tender);

        $user = Auth::user();
        $canSeeHpp = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-tender') || $user->can('lihat-hpp-stok') || $user->can('akses-accounting')) : false;

        $tender->load([
            'customer',
            'sales.department',
            'company',
            'branch',
            'salesOrder',
            'approver',
            'rejector',
            'items.barang.satuan',
            'items.barang.primaryFoto',
            'items.barang.fotos',
            'items.barang.bundleComponents.childBarang.satuan',
            'items.barang.bundleComponents.childBarang.primaryFoto',
            'items.barang.bundleComponents.childBarang.fotos',
            'documents',
            'followups.user',
            'approvals.user'
        ]);

        if ($tender->items) {
            foreach ($tender->items as $item) {
                if ($item->barang) {
                    $item->barang->resolved_photo_url = $this->resolvePhotoUrl($item->barang->primaryFoto ? $item->barang->primaryFoto->foto_path : null);
                    
                    $photos = [];
                    if ($item->barang->fotos && $item->barang->fotos->isNotEmpty()) {
                        foreach ($item->barang->fotos as $f) {
                            $url = $this->resolvePhotoUrl($f->foto_path);
                            if (!in_array($url, $photos)) $photos[] = $url;
                        }
                    }
                    if (empty($photos) && $item->barang->resolved_photo_url) {
                        $photos[] = $item->barang->resolved_photo_url;
                    }
                    $item->barang->resolved_photos = $photos;

                    if ($item->barang->bundleComponents) {
                        foreach ($item->barang->bundleComponents as $comp) {
                            if ($comp->childBarang) {
                                $comp->childBarang->resolved_photo_url = $this->resolvePhotoUrl($comp->childBarang->primaryFoto ? $comp->childBarang->primaryFoto->foto_path : null);
                                $cPhotos = [];
                                if ($comp->childBarang->fotos && $comp->childBarang->fotos->isNotEmpty()) {
                                    foreach ($comp->childBarang->fotos as $cf) {
                                        $curl = $this->resolvePhotoUrl($cf->foto_path);
                                        if (!in_array($curl, $cPhotos)) $cPhotos[] = $curl;
                                    }
                                }
                                if (empty($cPhotos) && $comp->childBarang->resolved_photo_url) {
                                    $cPhotos[] = $comp->childBarang->resolved_photo_url;
                                }
                                $comp->childBarang->resolved_photos = $cPhotos;
                            }
                        }
                    }
                }
            }
        }

        $canApprove = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('approve-tender')) : false;
        $canEdit = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('edit-tender')) : false;

        return response()->json([
            'tender'      => $tender,
            'can_see_hpp' => $canSeeHpp,
            'can_approve' => $canApprove,
            'can_edit'    => $canEdit,
        ]);
    }

    private function resolvePhotoUrl(?string $path): string
    {
        if (empty($path)) {
            return asset('uploads/barang/placeholder_garment.png');
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'uploads/')) {
            return asset($path);
        }
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    public function update(Request $request, Tender $tender)
    {
        $this->authorize('edit-tender');
        $this->authorizeCompanyAccess($tender);

        $request->validate([
            'nomor_kontrak'         => 'required|string|max:40',
            'customer_id'           => 'required|exists:master_customers,id',
            'sales_id'              => 'required|exists:karyawans,id',
            'nama_tender'           => 'required|string|max:50',
            'kategori_instansi'     => 'nullable|string|max:100',
            'tanggal'               => 'required|date',
            'deadline'              => 'required|date',
            'estimasi_closing'      => 'required|date',
            'priority'              => 'required|in:Low,Medium,High',
            'status'                => 'nullable|string',
            'nilai_deal'            => 'nullable|numeric|min:0',
            'nilai_estimasi'        => 'nullable|numeric|min:0|max_digits:20',
            'catatan'               => 'nullable|string|max:50',
            'items'                 => 'nullable|array',
            'items.*.barang_id'     => 'nullable',
            'items.*.qty'           => 'nullable|numeric|min:0.01',
            'items.*.harga'         => 'nullable|numeric|min:0',
            'documents'             => 'nullable|array|max:30',
            'documents.*'           => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,zip,rar',
            'document_names'        => 'nullable|array|max:30',
        ], [
            'nama_tender.required'   => 'Nama Proyek / Pengadaan Tender wajib diisi.',
            'nama_tender.max'        => 'Nama Proyek / Pengadaan Tender maksimal 50 karakter.',
            'nomor_kontrak.required' => 'Nomor Tender / Registrasi Proyek wajib diisi.',
            'nomor_kontrak.max'      => 'Nomor Tender / Registrasi Proyek maksimal 40 karakter.',
            'catatan.max'            => 'Catatan Tender maksimal 50 karakter.',
            'nilai_estimasi.max_digits' => 'Nilai Pagu Anggaran maksimal 20 digit.',
            'documents.*.max'        => 'Ukuran berkas lampiran tidak boleh melebihi 10 MB.',
            'documents.*.mimes'      => 'Format berkas lampiran harus berupa PDF, Word, Excel, atau ZIP/RAR.',
            'documents.max'          => 'Maksimal 30 berkas dokumen yang dapat dilampirkan.',
        ]);

        $existingDocCount = $tender->documents()->count();
        $newDocCount = $request->hasFile('documents') ? count($request->file('documents')) : 0;
        if (($existingDocCount + $newDocCount) > 30) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Batas maksimal 30 berkas dokumen tercapai! Dokumen yang tersimpan: ' . $existingDocCount . ', berkas baru yang Anda unggah: ' . $newDocCount . '.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $paguNilai = (float)($request->nilai_estimasi ?: $tender->nilai_estimasi);

            $branchId = $tender->branch_id;
            if ($request->filled('branch_id')) {
                $branchId = ($request->branch_id === 'pusat' || empty($request->branch_id)) ? null : (int)$request->branch_id;
            }

            $tender->update([
                'branch_id'             => $branchId,
                'nomor_kontrak'         => $request->nomor_kontrak ?: $tender->nomor_kontrak,
                'customer_id'           => $request->customer_id,
                'sales_id'              => $request->sales_id,
                'nama_tender'           => $request->nama_tender,
                'kategori_instansi'     => $request->kategori_instansi ?? 'Pemerintah / B2G',
                'tanggal'               => $request->tanggal,
                'deadline'              => $request->deadline,
                'estimasi_closing'      => $request->estimasi_closing,
                'currency'              => $request->currency ?? $tender->currency,
                'status'                => $request->filled('status') ? $request->status : $tender->status,
                'tahap_tender'          => $this->getStageLabel($request->filled('status') ? $request->status : $tender->status),
                'priority'              => $request->priority,
                'nilai_estimasi'        => $paguNilai,
                'catatan'               => $request->catatan,
                'updated_by'            => auth()->id(),
            ]);

            // Sync Items
            if (is_array($request->items)) {
                $tender->items()->delete();
                foreach ($request->items as $it) {
                    $bId = !empty($it['barang_id']) ? $it['barang_id'] : null;
                    $namaCustom = !empty($it['nama_barang_custom']) ? $it['nama_barang_custom'] : null;
                    $namaDisplay = !empty($it['nama_barang_display']) ? $it['nama_barang_display'] : null;
                    
                    if (!$bId && !$namaCustom && !$namaDisplay) continue;

                    $qty = max(0.01, floatval($it['qty'] ?? 1));
                    $harga = max(0, floatval($it['harga'] ?? 0));
                    $satuan = !empty($it['satuan']) ? $it['satuan'] : 'Pcs';

                    TenderItem::create([
                        'tender_id'          => $tender->id,
                        'barang_id'          => $bId,
                        'nama_barang_custom' => $namaCustom ?: ($bId ? null : $namaDisplay),
                        'qty'                => $qty,
                        'satuan'             => $satuan,
                        'catatan_item'       => $it['catatan_item'] ?? null,
                    ]);
                }
            }

            // Upload additional documents
            if ($request->hasFile('documents')) {
                $uploadDir = public_path('uploads/tenders');
                if (!File::exists($uploadDir)) {
                    File::makeDirectory($uploadDir, 0755, true);
                }

                foreach ($request->file('documents') as $index => $file) {
                    if (!$file || !$file->isValid()) continue;
                    
                    $docName = $request->input("document_names.{$index}") ?: $request->input("document_types.{$index}", 'Lainnya');
                    $docName = mb_substr(trim($docName), 0, 40);

                    $filename = 'tnd_' . $tender->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);

                    TenderDocument::create([
                        'tender_id'     => $tender->id,
                        'nama_file'     => $file->getClientOriginalName(),
                        'jenis_dokumen' => $docName,
                        'path'          => 'uploads/tenders/' . $filename,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Tender berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update tender: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui tender: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Submit Tender for Approval (Draft/Rejected -> Pending Approval)
     */
    public function submitApproval(Request $request, Tender $tender)
    {
        $this->authorize('edit-tender');
        $this->authorizeCompanyAccess($tender);

        if (!in_array($tender->status, ['Draft', 'Rejected'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya tender berstatus Draft atau Ditolak yang dapat diajukan untuk approval.'
            ], 422);
        }

        $tender->update([
            'status'       => 'Pending Approval',
            'tahap_tender' => 'Review & Persetujuan Atasan',
            'submitted_at' => now(),
            'updated_by'   => auth()->id(),
        ]);

        // Send WhatsApp Notification Async
        WhatsAppGatewayController::runAsync(fn() =>
            WhatsAppGatewayController::sendTenderSubmissionNotification($tender)
        );

        return response()->json([
            'success' => true,
            'message' => 'Proposal tender berhasil diajukan untuk persetujuan (approval) atasan!',
            'status'  => $tender->status,
        ]);
    }

    /**
     * Approve Tender (Pending Approval -> Approved)
     */
    public function approve(Request $request, Tender $tender)
    {
        $this->authorize('approve-tender');
        $this->authorizeCompanyAccess($tender);

        if ($tender->status !== 'Pending Approval') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya tender yang sedang menunggu persetujuan (Pending Approval) yang dapat disetujui.'
            ], 422);
        }

        $tender->update([
            'status'          => 'Approved',
            'tahap_tender'    => 'Disetujui (Siap Kirim)',
            'approved_by'     => auth()->id(),
            'approved_at'     => now(),
            'rejected_by'     => null,
            'rejected_at'     => null,
            'rejected_reason' => null,
            'updated_by'      => auth()->id(),
        ]);

        // Log Approval to tender_approvals table if exists
        try {
            $tender->approvals()->create([
                'user_id' => auth()->id(),
                'status'  => 'Approved',
                'catatan' => $request->input('catatan', 'Disetujui oleh atasan / manager.'),
            ]);
        } catch (\Exception $e) {}

        // Send WhatsApp Notification Async
        $approverUser = auth()->user();
        WhatsAppGatewayController::runAsync(fn() =>
            WhatsAppGatewayController::sendTenderApprovedNotification($tender, $approverUser)
        );

        return response()->json([
            'success' => true,
            'message' => 'Proposal tender berhasil disetujui (Approved)!',
            'status'  => $tender->status,
        ]);
    }

    /**
     * Reject Tender (Pending Approval -> Rejected)
     */
    public function reject(Request $request, Tender $tender)
    {
        $this->authorize('approve-tender');
        $this->authorizeCompanyAccess($tender);

        $request->validate([
            'alasan' => 'required|string|max:500',
        ], [
            'alasan.required' => 'Alasan penolakan tender wajib diisi.',
        ]);

        if ($tender->status !== 'Pending Approval') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya tender yang sedang menunggu persetujuan (Pending Approval) yang dapat ditolak.'
            ], 422);
        }

        $alasan = trim($request->alasan);

        $tender->update([
            'status'          => 'Rejected',
            'tahap_tender'    => 'Ditolak (Perlu Revisi)',
            'rejected_by'     => auth()->id(),
            'rejected_at'     => now(),
            'rejected_reason' => $alasan,
            'updated_by'      => auth()->id(),
        ]);

        try {
            $tender->approvals()->create([
                'user_id' => auth()->id(),
                'status'  => 'Rejected',
                'catatan' => $alasan,
            ]);
        } catch (\Exception $e) {}

        // Send WhatsApp Notification Async
        $rejectorUser = auth()->user();
        WhatsAppGatewayController::runAsync(fn() =>
            WhatsAppGatewayController::sendTenderRejectedNotification($tender, $rejectorUser, $alasan)
        );

        return response()->json([
            'success' => true,
            'message' => 'Proposal tender berhasil ditolak dengan catatan evaluasi.',
            'status'  => $tender->status,
        ]);
    }

    /**
     * Mark Tender as Won (Menang Lelang)
     */
    public function markAsWon(Request $request, Tender $tender)
    {
        $this->authorize('edit-tender');
        $this->authorizeCompanyAccess($tender);

        $request->validate([
            'nilai_deal' => 'nullable|numeric|min:0',
        ]);

        $nilaiDeal = (float)($request->nilai_deal ?: $tender->nilai_estimasi);

        $tender->update([
            'status'       => 'Won',
            'tahap_tender' => 'Pemenang Tender (Won)',
            'nilai_deal'   => $nilaiDeal,
            'updated_by'   => auth()->id(),
        ]);

        // Send WhatsApp Broadcast
        WhatsAppGatewayController::runAsync(fn() =>
            WhatsAppGatewayController::sendTenderWonNotification($tender)
        );

        return response()->json([
            'success' => true,
            'message' => 'Selamat! Tender berhasil ditetapkan sebagai PEMENANG (Won)!',
            'status'  => $tender->status,
        ]);
    }

    /**
     * Mark Tender as Lost (Kalah Lelang)
     */
    public function markAsLost(Request $request, Tender $tender)
    {
        $this->authorize('edit-tender');
        $this->authorizeCompanyAccess($tender);

        $request->validate([
            'lost_reason'        => 'required|string|max:500',
            'winning_competitor' => 'nullable|string|max:200',
        ], [
            'lost_reason.required' => 'Alasan kekalahan tender wajib diisi untuk evaluasi.',
        ]);

        $tender->update([
            'status'             => 'Lost',
            'tahap_tender'       => 'Kalah Lelang (Lost)',
            'lost_reason'        => $request->lost_reason,
            'winning_competitor' => $request->winning_competitor,
            'updated_by'         => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status tender berhasil diperbarui menjadi Kalah (Lost).',
            'status'  => $tender->status,
        ]);
    }

    /**
     * Update Tender Stage (Quick Change from Kanban or Modal Detail)
     */
    public function updateStage(Request $request, Tender $tender)
    {
        $this->authorize('edit-tender');
        $this->authorizeCompanyAccess($tender);

        $request->validate([
            'status' => 'required|in:Draft,Pending Approval,Approved,Rejected,Sent,Negotiation,Won,Lost,Cancelled',
        ]);

        $status = $request->status;

        // Extra check: If transitioning to Won, broadcast WA
        if ($status === 'Won' && $tender->status !== 'Won') {
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendTenderWonNotification($tender)
            );
        }

        $tender->update([
            'status'       => $status,
            'tahap_tender' => $this->getStageLabel($status),
            'updated_by'   => auth()->id(),
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Status tahapan tender berhasil diperbarui!',
            'status'       => $tender->status,
            'tahap_tender' => $tender->tahap_tender,
        ]);
    }

    /**
     * Helper to get descriptive human label for stages
     */
    private function getStageLabel(string $status): string
    {
        return match ($status) {
            'Draft'            => 'Draft Proposal',
            'Pending Approval' => 'Review & Persetujuan Atasan',
            'Approved'         => 'Disetujui (Siap Kirim)',
            'Rejected'         => 'Ditolak (Perlu Revisi)',
            'Sent'             => 'Terkirim ke Panitia/Klien',
            'Negotiation'      => 'Aanwijzing / Negosiasi',
            'Won'              => 'Pemenang Tender (Won)',
            'Lost'             => 'Kalah Lelang (Lost)',
            'Cancelled'        => 'Dibatalkan',
            default            => $status,
        };
    }

    /**
     * Helper to compute detailed financials, taxes (PPN/PPh), discounts, extra costs, and HPP
     */
    private function calculateTenderFinancials(array $items, Request $request): array
    {
        $barangIds = collect($items)->pluck('barang_id')->unique()->filter()->toArray();
        $hppMap = $this->resolveBarangHppMap($barangIds, true);

        $processedItems = [];
        $totalSubtotal = 0;
        $totalHpp = 0;

        foreach ($items as $item) {
            $qty = max(0, floatval($item['qty'] ?? 0));
            $harga = max(0, floatval($item['harga'] ?? 0));
            $tier = $item['tier_harga'] ?? 'Custom';

            $itemDiskonPersen = max(0, floatval($item['diskon_persen'] ?? 0));
            $itemDiskonNominal = max(0, floatval($item['diskon_nominal'] ?? 0));
            if ($itemDiskonPersen > 0) {
                $itemDiskonNominal = ($qty * $harga) * ($itemDiskonPersen / 100);
            }
            $lineSubtotal = max(0, ($qty * $harga) - $itemDiskonNominal);
            $totalSubtotal += $lineSubtotal;

            // HPP Lookup from robust resolver
            $hppSatuan = $hppMap[$item['barang_id']] ?? 0;
            $lineHpp = $qty * $hppSatuan;
            $totalHpp += $lineHpp;

            $marginNominal = $lineSubtotal - $lineHpp;
            $marginPersen = ($lineSubtotal > 0) ? round(($marginNominal / $lineSubtotal) * 100, 2) : 0;

            $processedItems[] = [
                'barang_id'               => !empty($item['barang_id']) ? $item['barang_id'] : null,
                'nama_barang_custom'      => $item['nama_barang_custom'] ?? null,
                'qty'                     => $qty,
                'satuan'                  => $item['satuan'] ?? 'Pcs',
                'harga_estimasi'          => $harga,
                'tier_harga'              => $tier,
                'diskon_persen'           => $itemDiskonPersen,
                'diskon_nominal'          => round($itemDiskonNominal, 2),
                'subtotal'                => round($lineSubtotal, 2),
                'catatan_item'            => $item['catatan_item'] ?? null,
                'hpp_satuan'              => round($hppSatuan, 2),
                'total_hpp'               => round($lineHpp, 2),
                'estimasi_margin_nominal' => round($marginNominal, 2),
                'estimasi_margin_persen'  => $marginPersen,
            ];
        }

        // Header Calculations
        $diskonTipe = $request->input('diskon_tipe', 'Nominal');
        $diskonPersen = max(0, floatval($request->input('diskon_persen', 0)));
        $diskonNominal = max(0, floatval($request->input('diskon_nominal', 0)));
        if ($diskonTipe === 'Persen' && $diskonPersen > 0) {
            $diskonNominal = $totalSubtotal * ($diskonPersen / 100);
        }
        
        $biayaEkstra = max(0, floatval($request->input('biaya_ekstra', 0)));
        $catatanBiayaEkstra = $request->input('catatan_biaya_ekstra');
        $biayaEkstraKenaPpn = $request->boolean('biaya_ekstra_kena_ppn') || ($request->input('biaya_ekstra_kena_ppn') == '1');

        $baseDpp = max(0, $totalSubtotal - $diskonNominal);
        $dpp = $biayaEkstraKenaPpn ? ($baseDpp + $biayaEkstra) : $baseDpp;

        // 3. Pajak PPN (Master Pajak Lookup)
        $ppnPajakId = $request->input('ppn_pajak_id');
        $rawTipePpn = $request->input('tipe_ppn', 'Non-PPN');
        $tarifPpn = max(0, floatval($request->input('tarif_ppn', 0)));
        $tipePpn = 'Non-PPN';

        if (!empty($ppnPajakId)) {
            $pjkPpn = MasterPajak::find($ppnPajakId);
            if ($pjkPpn) {
                $tarifPpn = (float)$pjkPpn->tarif;
                $tipePpn = $pjkPpn->nama_pajak ?: 'PPN';
            }
        } elseif ($rawTipePpn !== 'Non-PPN' && $tarifPpn > 0) {
            $tipePpn = 'PPN';
        }

        if ($tarifPpn <= 0) {
            $tipePpn = 'Non-PPN';
            $tarifPpn = 0;
            $nilaiPpn = 0;
            $ppnPajakId = null;
        } else {
            $nilaiPpn = $dpp * ($tarifPpn / 100);
        }

        // 4. Pajak PPh (Master Pajak Lookup)
        $pphPajakId = $request->input('pph_pajak_id');
        $rawTipePph = $request->input('tipe_pph', 'Non-PPh');
        $tarifPph = max(0, floatval($request->input('tarif_pph', 0)));
        $tipePph = 'Non-PPh';

        if (!empty($pphPajakId)) {
            $pjkPph = MasterPajak::find($pphPajakId);
            if ($pjkPph) {
                $tarifPph = (float)$pjkPph->tarif;
                $tipePph = $pjkPph->nama_pajak ?: 'PPh';
            }
        } elseif ($rawTipePph === 'PPh-22-1.5') {
            $tarifPph = 1.5;
            $tipePph = 'PPh 22';
        } elseif ($rawTipePph !== 'Non-PPh') {
            $tipePph = $rawTipePph;
        }

        if ($tarifPph <= 0) {
            $tipePph = 'Non-PPh';
            $pphPajakId = null;
            $tarifPph = 0;
        }
        $nilaiPph = ($tarifPph > 0) ? ($dpp * ($tarifPph / 100)) : 0;

        $grandTotal = $biayaEkstraKenaPpn ? ($dpp + $nilaiPpn) : ($dpp + $nilaiPpn + $biayaEkstra);
        $estimasiLabaKotor = $baseDpp - $totalHpp - $biayaEkstra;
        $marginPersen = ($baseDpp > 0) ? round(($estimasiLabaKotor / $baseDpp) * 100, 2) : 0;

        // 5. Payment Terms & Down Payment (DP)
        $paymentTermId = $request->input('payment_term_id');
        $tipeDp = $request->input('tipe_dp', 'Persen');
        $dpPersen = max(0, floatval($request->input('dp_persen', 0)));
        $dpNominal = max(0, floatval($request->input('dp_nominal', 0)));
        $catatanPembayaran = $request->input('catatan_pembayaran');

        if ($tipeDp === 'Persen') {
            $dpNominal = $grandTotal * ($dpPersen / 100);
        } else {
            $dpPersen = ($grandTotal > 0) ? round(($dpNominal / $grandTotal) * 100, 2) : 0;
        }
        $sisaTagihan = max(0, $grandTotal - $dpNominal);

        return [
            'items'                 => $processedItems,
            'subtotal'              => round($totalSubtotal, 2),
            'diskon_tipe'           => $diskonTipe,
            'diskon_persen'         => $diskonPersen,
            'diskon_nominal'        => round($diskonNominal, 2),
            'ppn_pajak_id'          => $ppnPajakId,
            'tipe_ppn'              => $tipePpn,
            'tarif_ppn'             => $tarifPpn,
            'nilai_ppn'             => round($nilaiPpn, 2),
            'pph_pajak_id'          => $pphPajakId,
            'tipe_pph'              => $tipePph,
            'tarif_pph'             => $tarifPph,
            'nilai_pph'             => round($nilaiPph, 2),
            'biaya_ekstra'          => round($biayaEkstra, 2),
            'catatan_biaya_ekstra'  => $catatanBiayaEkstra,
            'biaya_ekstra_kena_ppn' => $biayaEkstraKenaPpn,
            'payment_term_id'       => $paymentTermId,
            'tipe_dp'               => $tipeDp,
            'dp_persen'             => round($dpPersen, 2),
            'dp_nominal'            => round($dpNominal, 2),
            'sisa_tagihan'          => round($sisaTagihan, 2),
            'catatan_pembayaran'    => $catatanPembayaran,
            'grand_total'           => round($grandTotal, 2),
            'total_hpp'             => round($totalHpp, 2),
            'estimasi_laba_kotor'   => round($estimasiLabaKotor, 2),
            'margin_persen'         => $marginPersen,
        ];
    }

    /**
     * Convert Tender to Sales Order (SO)
     */
    public function convertToSalesOrder(Request $request, Tender $tender)
    {
        $this->authorize('tambah-tender');
        $this->authorizeCompanyAccess($tender);

        if ($tender->sales_order_id) {
            $existingSo = SalesOrder::find($tender->sales_order_id);
            if ($existingSo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tender ini sudah terhubung dengan Sales Order ' . $existingSo->nomor_so,
                    'sales_order_id' => $existingSo->id,
                    'nomor_so' => $existingSo->nomor_so
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $tender->load(['items.barang.satuan', 'customer', 'sales']);

            $nomorSo = 'SO/' . date('Ymd') . '/' . str_pad(SalesOrder::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT);

            $salesOrder = SalesOrder::create([
                'company_id'          => $tender->company_id ?: session('active_company_id', 1),
                'branch_id'           => session('active_branch_id'),
                'nomor_so'            => $nomorSo,
                'tanggal_so'          => now()->toDateString(),
                'customer_id'         => $tender->customer_id,
                'tender_id'           => $tender->id,
                'pricing_tier'        => 'Tender',
                'status'              => 'Confirmed',
                'subtotal'            => $tender->nilai_estimasi ?: 0,
                'diskon_tipe'         => 'Nominal',
                'diskon_nilai'        => 0,
                'diskon_nominal'      => 0,
                'pajak_tipe'          => 'None',
                'pajak_persen'        => 0,
                'pajak_nominal'       => 0,
                'total'               => $tender->nilai_estimasi ?: 0,
                'catatan'             => 'Ditarik dari Proposal Tender: ' . $tender->nama_tender . ' (' . ($tender->nomor_kontrak ?: 'TND-'.$tender->id) . ')',
                'created_by'          => auth()->id(),
            ]);

            foreach ($tender->items as $item) {
                $itemHarga = $item->barang ? (float)($item->barang->harga_tender ?: $item->barang->harga_grosir ?: $item->barang->harga_retail ?: 0) : 0;
                $itemQty = (float)($item->qty ?: 1);
                $itemSubtotal = $itemQty * $itemHarga;

                SalesOrderDetail::create([
                    'sales_order_id' => $salesOrder->id,
                    'barang_id'      => $item->barang_id,
                    'qty'            => $itemQty,
                    'satuan_id'      => $item->barang ? $item->barang->satuan_id : null,
                    'pricing_tier'   => 'Tender',
                    'harga_satuan'   => $itemHarga,
                    'diskon_persen'  => 0,
                    'diskon_nominal' => 0,
                    'subtotal'       => $itemSubtotal,
                    'catatan'        => $item->catatan_item ?: ('Dari tender ' . ($tender->nomor_kontrak ?: 'TND-'.$tender->id)),
                ]);
            }

            // Update tender
            $tender->update([
                'sales_order_id' => $salesOrder->id,
                'status'         => 'Won',
                'tahap_tender'   => $this->getStageLabel('Won'),
            ]);

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'Berhasil menarik Tender ke Sales Order (' . $nomorSo . ')!',
                'sales_order_id' => $salesOrder->id,
                'nomor_so'       => $nomorSo,
                'redirect_url'   => route('marketing.sales-order.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error convert tender to SO: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menarik ke Sales Order: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Tender $tender)
    {
        $this->authorize('hapus-tender');
        $this->authorizeCompanyAccess($tender);

        try {
            $tender->delete();
            return response()->json(['success' => true, 'message' => 'Tender berhasil dihapus!']);
        } catch (\Exception $e) {
            Log::error('Error delete tender: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus tender.'], 500);
        }
    }

    public function storeFollowup(Request $request, Tender $tender)
    {
        $this->authorize('tambah-tender');
        $this->authorizeCompanyAccess($tender);

        $request->validate([
            'tanggal'    => 'required|date',
            'aktivitas'  => 'required|string|max:100',
            'keterangan' => 'required|string',
        ]);

        try {
            TenderFollowup::create([
                'tender_id'  => $tender->id,
                'tanggal'    => $request->tanggal,
                'aktivitas'  => $request->aktivitas,
                'keterangan' => $request->keterangan,
                'user_id'    => auth()->id(),
            ]);

            return response()->json(['success' => true, 'message' => 'Follow-up berhasil ditambahkan!']);
        } catch (\Exception $e) {
            Log::error('Error store followup: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan follow-up.'], 500);
        }
    }




    public function printPenawaran(Request $request, Tender $tender)
    {
        $this->authorize('lihat-tender');
        $this->authorizeCompanyAccess($tender);

        $tender->load([
            'customer',
            'sales.department',
            'sales.user',
            'company',
            'items.barang.satuan',
            'items.barang.primaryFoto',
            'items.barang.fotos',
            'items.barang.bundleComponents.childBarang.satuan',
            'items.barang.bundleComponents.childBarang.primaryFoto',
            'items.barang.bundleComponents.childBarang.fotos',
            'documents',
            'approvals.user'
        ]);

        $companies = \App\Models\Company::active()->get();
        $selectedCompanyId = $request->query('company_id', $tender->company_id ?? session('active_company_id'));
        $company = $companies->firstWhere('id', $selectedCompanyId) ?? $tender->company ?? $companies->first() ?? \App\Models\Company::first();

        $appLogo = ($company && $company->logo && file_exists(public_path('storage/' . $company->logo)))
            ? asset('storage/' . $company->logo)
            : asset('uploads/barang/placeholder_garment.png');

        $totalValue = (float)($tender->nilai_estimasi ?: 0);
        $terbilang = $this->terbilang($totalValue) . ' Rupiah';

        return view('modul_marketing.kontrak.print_penawaran', compact('tender', 'company', 'companies', 'appLogo', 'terbilang'));
    }

    private function terbilang($angka)
    {
        $angka = abs((float)$angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $terbilang = '';

        if ($angka < 12) {
            $terbilang = ' ' . $baca[(int)$angka];
        } elseif ($angka < 20) {
            $terbilang = $this->terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            $terbilang = $this->terbilang((int)($angka / 10)) . ' Puluh ' . $this->terbilang($angka % 10);
        } elseif ($angka < 200) {
            $terbilang = ' Seratus ' . $this->terbilang($angka - 100);
        } elseif ($angka < 1000) {
            $terbilang = $this->terbilang((int)($angka / 100)) . ' Ratus ' . $this->terbilang($angka % 100);
        } elseif ($angka < 2000) {
            $terbilang = ' Seribu ' . $this->terbilang($angka - 1000);
        } elseif ($angka < 1000000) {
            $terbilang = $this->terbilang((int)($angka / 1000)) . ' Ribu ' . $this->terbilang($angka % 1000);
        } elseif ($angka < 1000000000) {
            $terbilang = $this->terbilang((int)($angka / 1000000)) . ' Juta ' . $this->terbilang(fmod($angka, 1000000));
        } elseif ($angka < 1000000000000) {
            $terbilang = $this->terbilang((int)($angka / 1000000000)) . ' Milyar ' . $this->terbilang(fmod($angka, 1000000000));
        } elseif ($angka < 1000000000000000) {
            $terbilang = $this->terbilang((int)($angka / 1000000000000)) . ' Triliun ' . $this->terbilang(fmod($angka, 1000000000000));
        }

        return preg_replace('/\s+/', ' ', trim($terbilang));
    }

    /**
     * Helper to resolve HPP across stok_gudangs, kartu_stoks, opening_stocks, and bundle components
     */
    private function resolveBarangHppMap(array $barangIds, bool $canSeeHpp): array
    {
        if (!$canSeeHpp || empty($barangIds)) {
            return [];
        }

        // 1. Stok Gudang (Moving Average & Nilai/Stok)
        $stokInfoMap = DB::table('stok_gudangs')
            ->whereIn('barang_id', $barangIds)
            ->groupBy('barang_id')
            ->selectRaw('barang_id, SUM(stok_aktual) as total_stok, SUM(nilai_persediaan) as total_nilai, AVG(CASE WHEN harga_pokok_rata_rata > 0 THEN harga_pokok_rata_rata END) as avg_hpp, MAX(harga_beli_terakhir) as last_buy')
            ->get()
            ->keyBy('barang_id');

        // 2. Kartu Stok latest transaction with price
        $kartuStokMap = DB::table('kartu_stoks')
            ->whereIn('barang_id', $barangIds)
            ->where('harga_satuan', '>', 0)
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('barang_id')
            ->map(fn($group) => (float)$group->first()->harga_satuan);

        // 3. Opening Stock details
        $openingMap = DB::table('opening_stock_details')
            ->whereIn('barang_id', $barangIds)
            ->where('harga_pokok', '>', 0)
            ->get()
            ->groupBy('barang_id')
            ->map(fn($group) => (float)$group->first()->harga_pokok);

        // 4. Bundle Components
        $bundleMap = DB::table('master_barang_bundle')
            ->whereIn('parent_barang_id', $barangIds)
            ->get()
            ->groupBy('parent_barang_id');

        $childIds = $bundleMap->flatten()->pluck('child_barang_id')->unique()->filter()->toArray();
        $childHppMap = [];
        if (!empty($childIds)) {
            $childHppMap = $this->resolveBarangHppMap($childIds, $canSeeHpp);
        }

        $hppResult = [];
        foreach ($barangIds as $bId) {
            $hpp = 0;
            $sInfo = $stokInfoMap->get($bId);
            if ($sInfo) {
                if ($sInfo->avg_hpp > 0) {
                    $hpp = (float)$sInfo->avg_hpp;
                } elseif ($sInfo->total_stok > 0 && $sInfo->total_nilai > 0) {
                    $hpp = $sInfo->total_nilai / $sInfo->total_stok;
                } elseif ($sInfo->last_buy > 0) {
                    $hpp = (float)$sInfo->last_buy;
                }
            }

            if ($hpp <= 0 && isset($kartuStokMap[$bId])) {
                $hpp = $kartuStokMap[$bId];
            }

            if ($hpp <= 0 && isset($openingMap[$bId])) {
                $hpp = $openingMap[$bId];
            }

            if ($hpp <= 0 && isset($bundleMap[$bId])) {
                $bComps = $bundleMap[$bId];
                $bHpp = 0;
                foreach ($bComps as $comp) {
                    $cId = $comp->child_barang_id;
                    $cHpp = $childHppMap[$cId] ?? 0;
                    $bHpp += ((float)$comp->qty * $cHpp);
                }
                if ($bHpp > 0) {
                    $hpp = $bHpp;
                }
            }

            $hppResult[$bId] = round($hpp, 2);
        }

        return $hppResult;
    }
}
