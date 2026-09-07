<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\MasterBarang;
use App\Models\MasterSatuan;
use App\Models\MasterTahapanProduksi;
use App\Models\SpkProduksi;
use App\Models\SpkProduksiMaterial;
use App\Models\SpkProduksiTahapan;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class SpkProduksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display listing of SPK / Work Orders with DataTables & KPIs.
     */
    public function index(Request $request)
    {
        $this->authorize('lihat-spk');

        $activeCompanyId = session('active_company_id');
        $user            = auth()->user();
        $isSupervisor    = $user->hasAnyRole(['Super Admin', 'Owner', 'Admin']);
        $isSuperAdmin    = $user->hasAnyRole(['Super Admin', 'Owner']);

        if ($request->ajax()) {
            $query = SpkProduksi::query()
                ->forActiveCompany()
                ->with([
                    'barangJadi.satuan',
                    'satuan',
                    'bom',
                    'penanggungJawab',
                    'creator',
                ]);

            if (!$request->has('order')) {
                $query->orderBy('spk_produksi.id', 'desc');
            }

            // Filter Status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('spk_produksi.status', $request->status);
            }

            // Filter Prioritas
            if ($request->filled('prioritas') && $request->prioritas !== 'all') {
                $query->where('spk_produksi.prioritas', $request->prioritas);
            }

            // Filter Tipe SPK
            if ($request->filled('tipe_spk') && $request->tipe_spk !== 'all') {
                $query->where('spk_produksi.tipe_spk', $request->tipe_spk);
            }

            // Filter Date Range
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('spk_produksi.tanggal_spk', [$request->start_date, $request->end_date]);
            }

            // Filter Restricted: Draft only visible to creator unless Supervisor
            if (!$isSupervisor) {
                $query->where(function ($q) use ($user) {
                    $q->where('spk_produksi.status', '!=', 'Draft')
                      ->orWhere('spk_produksi.created_by', $user->id);
                });
            }

            // Single aggregate query for fast KPI calculation
            $kpiRaw = DB::table(DB::raw("({$query->toBase()->toSql()}) as spk_sub"))
                ->mergeBindings($query->toBase())
                ->selectRaw('
                    COUNT(*) as total_spk,
                    COUNT(CASE WHEN status = "In Progress" THEN 1 END) as in_progress_spk,
                    COUNT(CASE WHEN status = "Completed" THEN 1 END) as completed_spk,
                    COALESCE(SUM(jumlah_rencana), 0) as total_qty_rencana
                ')
                ->first();

            $kpiData = [
                'total_spk'        => (int) ($kpiRaw->total_spk ?? 0),
                'in_progress_spk'  => (int) ($kpiRaw->in_progress_spk ?? 0),
                'completed_spk'    => (int) ($kpiRaw->completed_spk ?? 0),
                'total_qty_rencana'=> (float) ($kpiRaw->total_qty_rencana ?? 0),
            ];

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor_spk_badge', function ($s) {
                    return '<span class="fw-bold text-primary text-decoration-none font-monospace">' . e($s->nomor_spk) . '</span>';
                })
                ->addColumn('barang_jadi_name', function ($s) {
                    if (!$s->barangJadi) return '<span class="text-muted">-</span>';
                    $nama = e($s->barangJadi->nama_barang);
                    $kode = e($s->barangJadi->kode_barang);
                    $bomName = $s->bom ? ('<br><small class="text-muted"><i class="bi bi-box-seam me-1"></i>BOM: ' . e($s->bom->nama_bom) . '</small>') : '';
                    return '<div><span class="fw-semibold text-body-emphasis">[' . $kode . '] ' . $nama . '</span>' . $bomName . '</div>';
                })
                ->addColumn('target_qty_fmt', function ($s) {
                    $qty = number_format($s->jumlah_rencana, 0, ',', '.');
                    $sat = $s->satuan ? e($s->satuan->nama_satuan) : 'Pcs';
                    return '<span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1.5 fw-bold">' . $qty . ' ' . $sat . '</span>';
                })
                ->addColumn('prioritas_badge', function ($s) {
                    $map = [
                        'Tinggi (Urgent)' => '<span class="badge bg-danger text-white rounded-pill px-2.5 py-1"><i class="bi bi-fire me-1"></i>Urgent</span>',
                        'Normal'          => '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">Normal</span>',
                        'Rendah'          => '<span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2.5 py-1">Rendah</span>',
                    ];
                    return $map[$s->prioritas] ?? '<span class="badge bg-light text-dark rounded-pill px-2.5 py-1">' . e($s->prioritas) . '</span>';
                })
                ->addColumn('mandor_name', function ($s) {
                    return $s->penanggungJawab ? e($s->penanggungJawab->nama_lengkap) : '<span class="text-muted italic">Belum ditentukan</span>';
                })
                ->addColumn('deadline_fmt', function ($s) {
                    if (!$s->target_selesai) return '<span class="text-muted">-</span>';
                    $isPast = $s->target_selesai->isPast() && !in_array($s->status, ['Completed', 'Cancelled']);
                    $cls = $isPast ? 'text-danger fw-bold' : 'text-body-secondary';
                    return '<span class="' . $cls . '"><i class="bi bi-calendar3 me-1"></i>' . $s->target_selesai->format('d/m/Y') . '</span>';
                })
                ->addColumn('status_badge', function ($s) {
                    $map = [
                        'Draft'       => '<span class="badge bg-secondary bg-opacity-75 rounded-pill px-3 py-1.5 fw-semibold shadow-xs"><i class="bi bi-file-earmark me-1"></i>Draft</span>',
                        'Pending'     => '<span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 fw-bold shadow-xs"><i class="bi bi-hourglass-split me-1"></i>Pending Approval</span>',
                        'Rejected'    => '<span class="badge rounded-pill px-3 py-1.5 fw-bold shadow-xs" style="background:#dc3545;color:#fff;"><i class="bi bi-x-circle-fill me-1"></i>Ditolak (Rejected)</span>',
                        'Approved'    => '<span class="badge bg-success rounded-pill px-3 py-1.5 fw-semibold shadow-xs"><i class="bi bi-check-circle-fill me-1"></i>Disetujui (Approved)</span>',
                        'In Progress' => '<span class="badge bg-info text-dark rounded-pill px-3 py-1.5 fw-bold shadow-xs"><i class="bi bi-arrow-repeat me-1 spinner-border spinner-border-sm" style="width:0.8rem;height:0.8rem;"></i> Dalam Proses</span>',
                        'Completed'   => '<span class="badge bg-emerald rounded-pill px-3 py-1.5 fw-bold shadow-xs" style="background-color:#059669;color:#fff;"><i class="bi bi-patch-check-fill me-1"></i>Selesai (Completed)</span>',
                        'Cancelled'   => '<span class="badge bg-danger rounded-pill px-3 py-1.5 fw-semibold shadow-xs"><i class="bi bi-x-circle-fill me-1"></i>Dibatalkan</span>',
                    ];
                    return $map[$s->status] ?? '<span class="badge bg-light text-dark rounded-pill px-3 py-1.5">' . e($s->status) . '</span>';
                })
                ->addColumn('action', function ($s) use ($isSupervisor, $isSuperAdmin, $user) {
                    $canDetail        = $isSupervisor || $user->can('detail-spk');
                    $canEdit          = $isSupervisor || $user->can('edit-spk');
                    $canDelete        = $isSupervisor || $user->can('hapus-spk');
                    $canApprove       = $isSupervisor || $user->can('approve-spk');
                    $canCetakSpk      = $isSupervisor || $user->can('cetak-spk');
                    $canRealisasi     = $isSupervisor || $user->can('input-realisasi-produksi');
                    $canMulai         = $isSupervisor || $user->can('mulai-produksi-spk');
                    $isCreator        = ($s->created_by == $user->id);

                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1">';

                    // Kirim ke Pending jika status Draft, Rejected, atau Cancelled
                    if (in_array($s->status, ['Draft', 'Rejected', 'Cancelled']) && ($isCreator || $isSupervisor || $user->can('tambah-spk') || $user->can('edit-spk'))) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-primary rounded-circle" onclick="sendPendingSpk(' . $s->id . ')" title="Ajukan SPK ke Pending"><i class="bi bi-send-fill"></i></button>';
                    }

                    // Tombol Mulai Produksi jika status Approved (Tampil di Tabel SPK)
                    if ($s->status === 'Approved' && $canMulai) {
                        $btn .= '<button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 me-1 shadow-xs" onclick="startProductionSpk(' . $s->id . ')" title="Mulai Produksi" style="font-size:11.5px; white-space:nowrap;"><i class="bi bi-play-circle-fill me-1"></i>Mulai</button>';
                    }

                    // Lihat Detail SPK (Eye Button)
                    if ($canDetail) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="showDetailSpk(' . $s->id . ')" title="Lihat Detail SPK"><i class="bi bi-eye"></i></button>';
                    }

                    // Cetak Dokumen SPK PDF (Printer Button)
                    if ($canCetakSpk) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" onclick="printSpk(' . $s->id . ')" title="Cetak Dokumen Work Order SPK"><i class="bi bi-printer"></i></button>';
                    }

                    // Edit (Khusus Super Admin / Owner tetap tampil walaupun status Approved/Selesai)
                    $allowEdit = $isSuperAdmin || ($canEdit && in_array($s->status, ['Draft', 'Pending', 'Rejected', 'Cancelled']));
                    if ($allowEdit) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-circle ms-1" onclick="editSpk(' . $s->id . ')" title="Edit SPK"><i class="bi bi-pencil"></i></button>';
                    }

                    // Tombol Batalkan SPK (Khusus Super Admin & Owner jika status In Progress atau Approved)
                    if ($isSuperAdmin && in_array($s->status, ['Approved', 'In Progress'])) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-circle ms-1" onclick="cancelSpk(' . $s->id . ', \'' . e($s->nomor_spk) . '\')" title="Batalkan SPK (Khusus Super Admin)"><i class="bi bi-x-circle-fill"></i></button>';
                    }

                    // Hapus (Khusus Admin/Supervisor tetap tampil walaupun status Selesai/Approved)
                    $allowDelete = $isSupervisor || ($canDelete && in_array($s->status, ['Draft', 'Cancelled']));
                    if ($allowDelete) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-circle ms-1" onclick="deleteSpk(' . $s->id . ')" title="Hapus SPK"><i class="bi bi-trash"></i></button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['nomor_spk_badge', 'barang_jadi_name', 'target_qty_fmt', 'prioritas_badge', 'mandor_name', 'deadline_fmt', 'status_badge', 'action'])
                ->with('kpi', $kpiData)
                ->make(true);
        }

        // PIC Mandor list (from Karyawan master table)
        $picUsers = Karyawan::query()
            ->forActiveCompany()
            ->where(function ($q) {
                $q->where('status', 'Aktif')
                  ->orWhere('status', 1)
                  ->orWhereNull('status');
            })
            ->with('position')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('modul_produksi.spk.index', compact('picUsers'));
    }

    /**
     * Generate auto SPK sequence number.
     */
    public function generateKodeSpk()
    {
        return response()->json(['nomor_spk' => $this->generateRawKodeSpk()]);
    }

    /**
     * Search Approved BOM list for Lookup Modal.
     */
    public function searchApprovedBom(Request $request)
    {
        $query = Bom::query()
            ->forActiveCompany()
            ->where('status', 'Approved')
            ->with(['barangJadi.satuan', 'satuanHasil', 'items.barangMentah.satuan', 'tender.customer']);

        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(function ($q) use ($search) {
                $q->where('kode_bom', 'like', $search)
                  ->orWhere('nama_bom', 'like', $search)
                  ->orWhereHas('barangJadi', fn($b) => $b->where('nama_barang', 'like', $search)->orWhere('kode_barang', 'like', $search))
                  ->orWhereHas('tender', fn($t) => $t->where('nama_tender', 'like', $search)->orWhere('nomor_kontrak', 'like', $search));
            });
        }

        $boms = $query->latest()->limit(25)->get();

        return response()->json([
            'boms' => $boms->map(function ($b) {
                return [
                    'id'               => $b->id,
                    'kode_bom'         => $b->kode_bom,
                    'nama_bom'         => $b->nama_bom,
                    'barang_jadi_id'   => $b->barang_jadi_id,
                    'barang_jadi_kode' => $b->barangJadi?->kode_barang,
                    'barang_jadi_nama' => $b->barangJadi?->nama_barang,
                    'satuan_id'        => $b->satuan_hasil_id,
                    'satuan_nama'      => $b->satuanHasil ? $b->satuanHasil->nama_satuan : ($b->barangJadi?->satuan?->nama_satuan ?? 'Pcs'),
                    'tipe_bom'         => $b->tipe_bom,
                    'total_hpp'        => (float)$b->total_estimasi_hpp,
                    'items_count'      => $b->items->count(),
                    'tender_id'        => $b->tender_id,
                    'nomor_kontrak'    => $b->tender?->nomor_kontrak,
                    'nama_tender'      => $b->tender?->nama_tender,
                    'nama_customer'    => $b->tender?->customer?->nama_customer,
                ];
            })
        ]);
    }

    /**
     * Fetch BOM recipe items and calculate warehouse stock availability for target qty.
     */
    public function getBomDetailsWithStock($bomId, Request $request)
    {
        $bom = Bom::forActiveCompany()
            ->with([
                'barangJadi.satuan',
                'satuanHasil',
                'items.barangMentah.satuan',
                'items.satuan'
            ])
            ->findOrFail($bomId);

        $targetQty = max((float)$request->input('target_qty', 0), 0);
        $companyId = session('active_company_id');
        $targetHasilBom = max((float)$bom->jumlah_hasil, 0.0001);

        $materials = [];
        $totalBiayaBahan = 0;

        // Batch pre-fetch stock values to prevent N+1 DB queries
        $materialIds = $bom->items->pluck('barang_mentah_id')->filter()->unique();
        
        $stockGudangMap = DB::table('stok_gudangs')
            ->whereIn('barang_id', $materialIds)
            ->groupBy('barang_id')
            ->select('barang_id', DB::raw('SUM(stok_aktual) as total_stok'))
            ->pluck('total_stok', 'barang_id');

        $latestKartuMap = DB::table('kartu_stoks')
            ->whereIn('barang_id', $materialIds)
            ->whereIn('id', function($sub) use ($materialIds) {
                $sub->select(DB::raw('MAX(id)'))
                    ->from('kartu_stoks')
                    ->whereIn('barang_id', $materialIds)
                    ->groupBy('barang_id');
            })
            ->pluck('stok_akhir', 'barang_id');

        foreach ($bom->items as $item) {
            $bm = $item->barangMentah;
            if (!$bm) continue;

            $netPerUnit  = (float)$item->jumlah_kebutuhan;
            $wastePct    = (float)$item->waste_percent;
            $grossPerUnit= ($netPerUnit / $targetHasilBom) * (1 + ($wastePct / 100));
            $totalNeed   = round($grossPerUnit * $targetQty, 4);

            // Live total warehouse stock from batch map
            $stockInGudang = (float) ($stockGudangMap[$bm->id] ?? 0);
            $latestStockKartu = (float) ($latestKartuMap[$bm->id] ?? 0);
            $stockActual = $stockInGudang > 0 ? $stockInGudang : $latestStockKartu;

            // Check if stock is sufficient
            $isSufficient = $stockActual >= $totalNeed;
            $deficit = $isSufficient ? 0 : round($totalNeed - $stockActual, 4);

            $hargaSatuan = (float)$item->estimasi_harga_satuan;
            $subtotal    = round($totalNeed * $hargaSatuan, 4);
            $totalBiayaBahan += $subtotal;

            $satuanNama = $item->satuan ? $item->satuan->nama_satuan : ($bm->satuan ? $bm->satuan->nama_satuan : 'Pcs');

            $materials[] = [
                'barang_mentah_id'      => $bm->id,
                'kode_barang'           => $bm->kode_barang,
                'nama_barang'           => $bm->nama_barang,
                'satuan_id'             => $item->satuan_id ?? $bm->satuan_id,
                'satuan_nama'           => $satuanNama,
                'kebutuhan_per_unit'    => round($netPerUnit / $targetHasilBom, 4),
                'waste_percent'         => $wastePct,
                'total_kebutuhan'       => $totalNeed,
                'stok_aktual'           => $stockActual,
                'is_sufficient'         => $isSufficient,
                'deficit'               => $deficit,
                'estimasi_harga_satuan' => $hargaSatuan,
                'estimasi_subtotal'     => $subtotal,
            ];
        }

        $biayaTk       = round(((float)$bom->biaya_tenaga_kerja / $targetHasilBom) * $targetQty, 4);
        $biayaOverhead = round(((float)$bom->biaya_overhead / $targetHasilBom) * $targetQty, 4);
        $grandTotal    = round($totalBiayaBahan + $biayaTk + $biayaOverhead, 4);
        $hppPerUnit    = $targetQty > 0 ? round($grandTotal / $targetQty, 4) : 0;

        return response()->json([
            'bom' => [
                'id'               => $bom->id,
                'kode_bom'         => $bom->kode_bom,
                'nama_bom'         => $bom->nama_bom,
                'barang_jadi_id'   => $bom->barang_jadi_id,
                'barang_jadi_name' => $bom->barangJadi ? ('[' . $bom->barangJadi->kode_barang . '] ' . $bom->barangJadi->nama_barang) : '-',
                'satuan_id'        => $bom->satuan_hasil_id,
                'satuan_nama'      => $bom->satuanHasil ? $bom->satuanHasil->nama_satuan : ($bom->barangJadi?->satuan?->nama_satuan ?? 'Pcs'),
                'biaya_tk_unit'    => (float)$bom->biaya_tenaga_kerja,
                'biaya_oh_unit'    => (float)$bom->biaya_overhead,
            ],
            'target_qty'        => $targetQty,
            'materials'         => $materials,
            'estimasi_biaya'    => [
                'total_bahan'    => $totalBiayaBahan,
                'total_tk'       => $biayaTk,
                'total_overhead' => $biayaOverhead,
                'grand_total'    => $grandTotal,
                'hpp_per_unit'   => $hppPerUnit,
            ]
        ]);
    }

    /**
     * Store new SPK / Work Order.
     */
    public function store(Request $request)
    {
        $this->authorize('tambah-spk');

        $user = auth()->user();

        $request->validate([
            'bom_id'              => 'required|exists:boms,id',
            'jumlah_rencana'      => 'required|numeric|min:0.0001',
            'tanggal_spk'         => 'required|date',
            'target_selesai'      => 'nullable|date|after_or_equal:tanggal_spk',
            'tipe_spk'            => 'required|string',
            'prioritas'           => 'required|string',
            'penanggung_jawab_id' => 'required|exists:karyawans,id',
            'materials'           => 'required|array|min:1',
            'materials.*.barang_mentah_id' => 'required|exists:master_barang,id',
            'materials.*.satuan_id'        => 'required|exists:master_satuan,id',
            'selected_tahapans'            => 'required|array|min:1',
            'selected_tahapans.*.nama_tahapan' => 'required|string|max:150',
            'selected_tahapans.*.urutan'       => 'required|integer|min:1',
        ], [
            'penanggung_jawab_id.required' => 'Penanggung Jawab (PIC Produksi) wajib diisi.',
            'selected_tahapans.required'   => 'Tahapan proses produksi wajib dipilih minimal 1 tahapan.',
            'selected_tahapans.min'        => 'Tahapan proses produksi wajib dipilih minimal 1 tahapan.',
        ]);

        DB::beginTransaction();
        try {
            $bom = Bom::forActiveCompany()->findOrFail($request->bom_id);

            $nomorSpk = $request->boolean('auto_number', true) ? $this->generateRawKodeSpk() : ($request->nomor_spk ?: $this->generateRawKodeSpk());
            $initialStatus = $request->input('status', 'Draft');

            $targetQty      = (float)$request->jumlah_rencana;
            $targetHasilBom = max((float)$bom->jumlah_hasil, 0.0001);
            $biayaTk        = round(((float)$bom->biaya_tenaga_kerja / $targetHasilBom) * $targetQty, 4);
            $biayaOverhead  = round(((float)$bom->biaya_overhead / $targetHasilBom) * $targetQty, 4);

            $totalBahan = 0;

            $spk = SpkProduksi::create([
                'company_id'             => session('active_company_id') ?? $user->company_id ?? 1,
                'branch_id'              => session('active_branch_id') ?: null, // NULL jika aktif sebagai PT Pusat
                'nomor_spk'              => $nomorSpk,
                'tanggal_spk'            => $request->tanggal_spk,
                'target_selesai'         => $request->target_selesai ?: null,
                'bom_id'                 => $bom->id,
                'barang_jadi_id'         => $bom->barang_jadi_id,
                'tender_id'              => $bom->tender_id,
                'satuan_id'              => $bom->satuan_hasil_id,
                'jumlah_rencana'         => $targetQty,
                'jumlah_selesai'         => 0,
                'jumlah_reject'          => 0,
                'tipe_spk'               => $request->tipe_spk ?: 'Make to Stock',
                'prioritas'              => $request->prioritas ?: 'Normal',
                'penanggung_jawab_id'    => $request->penanggung_jawab_id ?: null,
                'estimasi_biaya_bahan'   => 0,
                'estimasi_biaya_tk'      => $biayaTk,
                'estimasi_biaya_overhead'=> $biayaOverhead,
                'estimasi_total_biaya'   => 0,
                'status'                 => $initialStatus,
                'keterangan'             => $request->keterangan,
                'created_by'             => $user->id,
            ]);

            // Save material requirements
            foreach ($request->materials as $mat) {
                $subtotal = round((float)($mat['total_kebutuhan'] ?? 0) * (float)($mat['estimasi_harga_satuan'] ?? 0), 4);
                $totalBahan += $subtotal;

                SpkProduksiMaterial::create([
                    'spk_id'                => $spk->id,
                    'barang_mentah_id'      => $mat['barang_mentah_id'],
                    'satuan_id'             => $mat['satuan_id'],
                    'kebutuhan_per_unit'    => round((float)($mat['kebutuhan_per_unit'] ?? 0), 4),
                    'waste_percent'         => round((float)($mat['waste_percent'] ?? 0), 2),
                    'total_kebutuhan'       => round((float)($mat['total_kebutuhan'] ?? 0), 4),
                    'total_keluar_gudang'   => 0,
                    'estimasi_harga_satuan' => round((float)($mat['estimasi_harga_satuan'] ?? 0), 4),
                    'estimasi_subtotal'     => $subtotal,
                    'catatan'               => $mat['catatan'] ?? null,
                ]);
            }

            // Update Grand Total Costs
            $grandTotal = round($totalBahan + $biayaTk + $biayaOverhead, 4);
            $spk->update([
                'estimasi_biaya_bahan' => $totalBahan,
                'estimasi_total_biaya' => $grandTotal,
            ]);

            // ─── Tahapan Produksi ─────────────────────────────────────────────────
            // Prioritas:
            // 1. selected_tahapans dari user (pilihan checklist di modal SPK)
            // 2. Master Tahapan perusahaan (auto, per Tipe SPK + Semua)
            // 3. 6 tahapan garmen default hardcoded (fallback aman)
            $selectedFromUser = $request->input('selected_tahapans', []);

            if (!empty($selectedFromUser)) {
                // Gunakan pilihan user — urutan sudah dikirim dari frontend
                $stagesToCreate = collect($selectedFromUser)->sortBy('urutan')->values();
            } else {
                $tipeSpk = $request->tipe_spk ?: 'Make to Stock';
                // Auto: coba ambil dari Master Tahapan company
                $stagesToCreate = MasterTahapanProduksi::where('company_id', $activeCompanyId)
                    ->whereIn('tipe_spk', [$tipeSpk, 'Semua'])
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->orderBy('urutan', 'asc')
                    ->get(['urutan', 'nama_tahapan']);

                if ($stagesToCreate->isEmpty()) {
                    // Fallback: 6 tahapan garmen default
                    $stagesToCreate = collect([
                        ['urutan' => 1, 'nama_tahapan' => 'Persiapan & Pola (Pattern / Spreading)'],
                        ['urutan' => 2, 'nama_tahapan' => 'Pemotongan Bahan (Cutting)'],
                        ['urutan' => 3, 'nama_tahapan' => 'Sablon & Bordir (Embellishment / Print)'],
                        ['urutan' => 4, 'nama_tahapan' => 'Penjahitan & Perakitan (Sewing / Assembly)'],
                        ['urutan' => 5, 'nama_tahapan' => 'Pemeriksaan Kualitas (QC & Trimming)'],
                        ['urutan' => 6, 'nama_tahapan' => 'Finishing & Kemasan (Ironing & Packing)'],
                    ]);
                }
            }

            foreach ($stagesToCreate as $stage) {
                SpkProduksiTahapan::create([
                    'spk_id'       => $spk->id,
                    'urutan'       => is_array($stage) ? $stage['urutan'] : $stage->urutan,
                    'nama_tahapan' => is_array($stage) ? $stage['nama_tahapan'] : $stage->nama_tahapan,
                    'target_qty'   => $targetQty,
                    'qty_selesai'  => 0,
                    'qty_reject'   => 0,
                    'status'       => 'Menunggu',
                ]);
            }

            DB::commit();

            // WhatsApp Notification if submitted as Pending
            if ($initialStatus === 'Pending') {
                WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendSpkApprovalNotification($spk));
            } elseif ($initialStatus === 'Approved' && $spk->bom_id) {
                \App\Models\Bom::where('id', $spk->bom_id)->update(['status' => 'Selesai']);
            }

            return response()->json([
                'success' => true,
                'message' => "Surat Perintah Kerja (SPK) {$spk->nomor_spk} berhasil dibuat!",
                'id'      => $spk->id
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error store SPK: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat SPK: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show detail SPK for Modal and View.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-spk') && !$user->can('detail-spk')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat detail SPK.');
        }

        $spk = SpkProduksi::forActiveCompany()
            ->with([
                'company',
                'branch',
                'barangJadi.satuan',
                'barangJadi.primaryFoto',
                'barangJadi.fotos',
                'satuan',
                'bom',
                'tender',
                'penanggungJawab',
                'creator',
                'approver',
                'materials.barangMentah.satuan',
                'materials.barangMentah.primaryFoto',
                'materials.barangMentah.fotos',
                'materials.satuan',
                'tahapans'
            ])
            ->findOrFail($id);

        $fotoProdukUrl = null;
        if ($spk->barangJadi) {
            $foto = $spk->barangJadi->primaryFoto ?? ($spk->barangJadi->fotos && $spk->barangJadi->fotos->isNotEmpty() ? $spk->barangJadi->fotos->first() : null);
            if ($foto && !empty($foto->foto_path)) {
                $fotoProdukUrl = asset('storage/' . $foto->foto_path);
            }
        }

        $isSupervisor     = $user->hasAnyRole(['Super Admin', 'Owner']);
        $canViewMaterials = $isSupervisor || $user->can('lihat-bahan-spk');
        $canApprove       = $isSupervisor || $user->can('approve-spk');
        $canPrint         = $isSupervisor || $user->can('cetak-spk');
        $canRealisasi     = $isSupervisor || $user->can('input-realisasi-produksi');
        $canMulai         = $isSupervisor || $user->can('mulai-produksi-spk');
        $canSelesai       = $isSupervisor || $user->can('selesai-produksi-spk') || $user->can('approve-realisasi-produksi');

        return response()->json([
            'spk'                => $spk,
            'foto_produk_url'    => $fotoProdukUrl,
            'can_view_materials' => $canViewMaterials,
            'can_approve'        => $canApprove,
            'can_print'          => $canPrint,
            'can_realisasi'      => $canRealisasi,
            'can_mulai'          => $canMulai,
            'can_selesai'        => $canSelesai,
            'is_super_admin'     => $isSupervisor,
        ]);
    }

    /**
     * Update SPK.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('edit-spk');

        $user         = auth()->user();
        $isSuperAdmin = $user->hasAnyRole(['Super Admin', 'Owner']);
        $spk          = SpkProduksi::forActiveCompany()->findOrFail($id);

        if (!$isSuperAdmin && in_array($spk->status, ['Approved', 'In Progress', 'Completed', 'Cancelled'])) {
            return response()->json(['message' => 'Hanya Super Admin / Owner yang dapat memperbarui SPK yang sudah disetujui atau selesai.'], 422);
        }

        $request->validate([
            'bom_id'              => 'nullable|exists:boms,id',
            'jumlah_rencana'      => 'nullable|numeric|min:0.0001',
            'tanggal_spk'         => 'required|date',
            'target_selesai'      => 'nullable|date|after_or_equal:tanggal_spk',
            'tipe_spk'            => 'required|string',
            'prioritas'           => 'required|string',
            'penanggung_jawab_id' => 'required|exists:karyawans,id',
            'materials'           => 'nullable|array',
            'materials.*.barang_mentah_id' => 'required_with:materials|exists:master_barang,id',
            'materials.*.satuan_id'        => 'required_with:materials|exists:master_satuan,id',
            'selected_tahapans'            => 'required|array|min:1',
            'selected_tahapans.*.nama_tahapan' => 'required|string|max:150',
            'selected_tahapans.*.urutan'       => 'required|integer|min:1',
        ], [
            'penanggung_jawab_id.required' => 'Penanggung Jawab (PIC Produksi) wajib diisi.',
            'selected_tahapans.required'   => 'Tahapan proses produksi wajib dipilih minimal 1 tahapan.',
            'selected_tahapans.min'        => 'Tahapan proses produksi wajib dipilih minimal 1 tahapan.',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'tanggal_spk'         => $request->tanggal_spk,
                'target_selesai'      => $request->target_selesai ?: null,
                'tipe_spk'            => $request->tipe_spk,
                'prioritas'           => $request->prioritas,
                'penanggung_jawab_id' => $request->penanggung_jawab_id ?: null,
                'keterangan'          => $request->keterangan,
                'updated_by'          => auth()->id(),
            ];

            if ($request->filled('bom_id') && $request->filled('jumlah_rencana')) {
                $bom = Bom::forActiveCompany()->findOrFail($request->bom_id);
                $targetQty      = (float)$request->jumlah_rencana;
                $targetHasilBom = max((float)$bom->jumlah_hasil, 0.0001);

                $biayaTk        = round(((float)$bom->biaya_tenaga_kerja / $targetHasilBom) * $targetQty, 4);
                $biayaOverhead  = round(((float)$bom->biaya_overhead / $targetHasilBom) * $targetQty, 4);

                $updateData['bom_id']                  = $bom->id;
                $updateData['barang_jadi_id']          = $bom->barang_jadi_id;
                $updateData['tender_id']               = $bom->tender_id;
                $updateData['satuan_id']               = $bom->satuan_hasil_id;
                $updateData['jumlah_rencana']          = $targetQty;
                $updateData['estimasi_biaya_tk']       = $biayaTk;
                $updateData['estimasi_biaya_overhead'] = $biayaOverhead;

                // Re-sync materials if provided
                if ($request->has('materials') && is_array($request->materials)) {
                    SpkProduksiMaterial::where('spk_id', $spk->id)->delete();

                    $totalBahan = 0;
                    foreach ($request->materials as $mat) {
                        $subtotal = round((float)($mat['total_kebutuhan'] ?? 0) * (float)($mat['estimasi_harga_satuan'] ?? 0), 4);
                        $totalBahan += $subtotal;

                        SpkProduksiMaterial::create([
                            'spk_id'                => $spk->id,
                            'barang_mentah_id'      => $mat['barang_mentah_id'],
                            'satuan_id'             => $mat['satuan_id'],
                            'kebutuhan_per_unit'    => round((float)($mat['kebutuhan_per_unit'] ?? 0), 4),
                            'waste_percent'         => round((float)($mat['waste_percent'] ?? 0), 2),
                            'total_kebutuhan'       => round((float)($mat['total_kebutuhan'] ?? 0), 4),
                            'total_keluar_gudang'   => 0,
                            'estimasi_harga_satuan' => round((float)($mat['estimasi_harga_satuan'] ?? 0), 4),
                            'estimasi_subtotal'     => $subtotal,
                            'catatan'               => $mat['catatan'] ?? null,
                        ]);
                    }

                    $grandTotal = round($totalBahan + $biayaTk + $biayaOverhead, 4);
                    $updateData['estimasi_biaya_bahan'] = $totalBahan;
                    $updateData['estimasi_total_biaya'] = $grandTotal;
                }

                // Re-sync tahapan target_qty
                if ($request->has('selected_tahapans') && is_array($request->selected_tahapans) && count($request->selected_tahapans) > 0 && in_array($spk->status, ['Draft', 'Pending'])) {
                    SpkProduksiTahapan::where('spk_id', $spk->id)->delete();
                    foreach ($request->selected_tahapans as $stg) {
                        SpkProduksiTahapan::create([
                            'spk_id'       => $spk->id,
                            'urutan'       => $stg['urutan'],
                            'nama_tahapan' => $stg['nama_tahapan'],
                            'target_qty'   => $targetQty,
                            'qty_selesai'  => 0,
                            'qty_reject'   => 0,
                            'status'       => 'Menunggu',
                        ]);
                    }
                } else {
                    SpkProduksiTahapan::where('spk_id', $spk->id)->update(['target_qty' => $targetQty]);
                }
            }

            $spk->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "SPK {$spk->nomor_spk} berhasil diperbarui!"
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error update SPK: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui SPK: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete SPK (only if Draft or Cancelled).
     */
    public function destroy($id)
    {
        $this->authorize('hapus-spk');

        $user         = auth()->user();
        $isSupervisor = $user->hasAnyRole(['Super Admin', 'Owner', 'Admin']);
        $spk          = SpkProduksi::forActiveCompany()->findOrFail($id);

        if (!$isSupervisor && !in_array($spk->status, ['Draft', 'Cancelled'])) {
            return response()->json(['message' => 'Hanya SPK berstatus Draft atau Dibatalkan yang dapat dihapus.'], 422);
        }

        $spk->update(['deleted_by' => $user->id]);
        $spk->delete();

        return response()->json([
            'success' => true,
            'message' => "SPK {$spk->nomor_spk} berhasil dihapus!"
        ], 200);
    }

    /**
     * Send SPK to Pending Approval.
     */
    public function sendPending($id)
    {
        $user = auth()->user();
        $spk  = SpkProduksi::forActiveCompany()->findOrFail($id);

        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && $spk->created_by != $user->id) {
            return response()->json(['message' => 'Hanya pembuat SPK yang dapat mengajukan ke status Pending.'], 403);
        }

        if (!in_array($spk->status, ['Draft', 'Rejected', 'Cancelled'])) {
            return response()->json(['message' => 'Hanya SPK berstatus Draft, Rejected, atau Cancelled yang dapat diajukan ke status Pending.'], 422);
        }

        $spk->update([
            'status'     => 'Pending',
            'updated_by' => $user->id,
        ]);

        // Reset status tahapan jika sebelumnya dibatalkan
        $spk->tahapans()->where('status', 'Dibatalkan')->update([
            'status' => 'Menunggu',
        ]);

        WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendSpkApprovalNotification($spk));

        return response()->json([
            'success' => true,
            'message' => "SPK {$spk->nomor_spk} berhasil diajukan ke status Pending Approval!"
        ], 200);
    }

    /**
     * Approve SPK.
     */
    public function approve($id)
    {
        $this->authorize('approve-spk');

        $user = auth()->user();
        $spk  = SpkProduksi::forActiveCompany()->findOrFail($id);

        if ($spk->status !== 'Pending') {
            return response()->json(['message' => 'Hanya SPK berstatus Pending yang dapat disetujui.'], 422);
        }

        $spk->update([
            'status'      => 'Approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'updated_by'  => $user->id,
        ]);

        // Otomatis ubah status Resep BOM terkait menjadi 'Selesai'
        if ($spk->bom_id) {
            \App\Models\Bom::where('id', $spk->bom_id)->update(['status' => 'Selesai']);
        }

        WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendSpkApprovedNotification($spk, $user));

        return response()->json([
            'success' => true,
            'message' => "SPK {$spk->nomor_spk} berhasil disetujui (Approved) & siap dikerjakan!"
        ], 200);
    }

    /**
     * Reject SPK.
     */
    public function reject(Request $request, $id)
    {
        $this->authorize('approve-spk');

        $user   = auth()->user();
        $spk    = SpkProduksi::forActiveCompany()->findOrFail($id);
        $alasan = $request->input('alasan', 'Perlu penyesuaian jadwal / target.');

        if ($spk->status !== 'Pending') {
            return response()->json(['message' => 'Hanya SPK berstatus Pending yang dapat ditolak.'], 422);
        }

        $spk->update([
            'status'     => 'Rejected',
            'keterangan' => ($spk->keterangan ? $spk->keterangan . "\n" : '') . "[Ditolak " . now()->format('d/m/Y H:i') . " oleh " . ($user->name ?? $user->username ?? 'Atasan') . "]: " . $alasan,
            'updated_by' => $user->id,
        ]);

        // Kirim notifikasi WA ke pengaju SPK
        WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendSpkRejectedNotification($spk, $user, $alasan));

        return response()->json([
            'success' => true,
            'message' => "SPK {$spk->nomor_spk} telah ditolak (Rejected). Pengaju dapat merevisi dan mengajukan kembali."
        ], 200);
    }

    /**
     * Start production (In Progress).
     */
    public function startProduction($id)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('mulai-produksi-spk')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mulai pengerjaan produksi SPK (mulai-produksi-spk).'], 403);
        }

        $spk = SpkProduksi::forActiveCompany()->findOrFail($id);

        if ($spk->status !== 'Approved') {
            return response()->json(['message' => 'Hanya SPK yang sudah disetujui (Approved) yang dapat dimulai produksinya.'], 422);
        }

        $spk->update([
            'status'     => 'In Progress',
            'updated_by' => $user->id,
        ]);

        // Start first tahapan
        $firstTahapan = $spk->tahapans()->orderBy('urutan', 'asc')->first();
        if ($firstTahapan && $firstTahapan->status === 'Menunggu') {
            $firstTahapan->update([
                'status'       => 'Sedang Dikerjakan',
                'waktu_mulai'  => now(),
            ]);
        }

        // Kirim Notifikasi WA ke PIC Produksi bahwa produksi resmi berjalan
        WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendSpkProductionStartedNotification($spk, $user));

        return response()->json([
            'success' => true,
            'message' => "Produksi untuk SPK {$spk->nomor_spk} telah dimulai (In Progress)!"
        ], 200);
    }

    /**
     * Get SPK details & Tahapan progress for realisasi modal.
     */
    public function getTahapanProgress($id)
    {
        $spk = SpkProduksi::forActiveCompany()
            ->with([
                'barangJadi.satuan',
                'satuan',
                'bom',
                'penanggungJawab',
                'tahapans',
                'materials.barangMentah.satuan',
                'materials.satuan'
            ])
            ->findOrFail($id);

        $totalTahap = $spk->tahapans->count();
        $completedTahap = $spk->tahapans->where('status', 'Selesai')->count();
        $inProgressTahap = $spk->tahapans->where('status', 'Sedang Dikerjakan')->count();
        $percentComplete = $totalTahap > 0 ? round(($completedTahap / $totalTahap) * 100) : 0;

        return response()->json([
            'success'           => true,
            'spk'               => $spk,
            'total_tahap'       => $totalTahap,
            'completed_tahap'   => $completedTahap,
            'in_progress_tahap' => $inProgressTahap,
            'percent_complete'  => $percentComplete
        ]);
    }

    /**
     * Update individual tahapan progress.
     */
    public function updateTahapanProgress(Request $request, $id, $tahapanId)
    {
        $spk = SpkProduksi::forActiveCompany()->findOrFail($id);
        $tahap = SpkProduksiTahapan::where('spk_id', $spk->id)->findOrFail($tahapanId);

        $validated = $request->validate([
            'status'       => 'required|in:Menunggu,Sedang Dikerjakan,Selesai',
            'qty_selesai'  => 'nullable|numeric|min:0',
            'qty_reject'   => 'nullable|numeric|min:0',
            'pic_operator' => 'nullable|string|max:150',
            'catatan'      => 'nullable|string|max:500',
        ]);

        $updateData = [
            'status'       => $validated['status'],
            'qty_selesai'  => (float)($validated['qty_selesai'] ?? $tahap->qty_selesai),
            'qty_reject'   => (float)($validated['qty_reject'] ?? $tahap->qty_reject),
            'pic_operator' => $validated['pic_operator'] ?? $tahap->pic_operator,
            'catatan'      => $validated['catatan'] ?? $tahap->catatan,
        ];

        if ($validated['status'] === 'Sedang Dikerjakan' && !$tahap->waktu_mulai) {
            $updateData['waktu_mulai'] = now();
        }
        if ($validated['status'] === 'Selesai') {
            $qtyCheck = (float)($updateData['qty_selesai']);
            if ((float)$tahap->target_qty > 0 && $qtyCheck < (float)$tahap->target_qty) {
                $sisa = (float)$tahap->target_qty - $qtyCheck;
                return response()->json([
                    'message' => "Gagal: Target pengerjaan belum tercapai ({$qtyCheck} / {$tahap->target_qty}). Masih kurang {$sisa}!"
                ], 422);
            }
            if (!$tahap->waktu_mulai) $updateData['waktu_mulai'] = now();
            $updateData['waktu_selesai'] = now();
        }

        $tahap->update($updateData);

        // Auto update SPK status to In Progress if currently Approved
        if ($spk->status === 'Approved' && in_array($validated['status'], ['Sedang Dikerjakan', 'Selesai'])) {
            $spk->update([
                'status'     => 'In Progress',
                'updated_by' => auth()->id()
            ]);
        }

        // Auto activate next tahapan if completed
        if ($validated['status'] === 'Selesai') {
            $nextTahapan = SpkProduksiTahapan::where('spk_id', $spk->id)
                ->where('urutan', '>', $tahap->urutan)
                ->where('status', 'Menunggu')
                ->orderBy('urutan', 'asc')
                ->first();

            if ($nextTahapan) {
                $nextTahapan->update([
                    'status'      => 'Sedang Dikerjakan',
                    'waktu_mulai' => now()
                ]);
            }
        }

        // Recalculate progress metrics
        $totalTahap = $spk->tahapans()->count();
        $completedTahap = $spk->tahapans()->where('status', 'Selesai')->count();
        $percentComplete = $totalTahap > 0 ? round(($completedTahap / $totalTahap) * 100) : 0;

        // Update overall SPK finished Qty from final stage
        $lastCompleted = $spk->tahapans()->where('status', 'Selesai')->orderBy('urutan', 'desc')->first();
        if ($lastCompleted) {
            $spk->update([
                'jumlah_selesai' => $lastCompleted->qty_selesai,
                'jumlah_reject'  => $lastCompleted->qty_reject,
            ]);
        }

        return response()->json([
            'success'          => true,
            'message'          => "Progress Tahapan '{$tahap->nama_tahapan}' berhasil diperbarui!",
            'tahap'            => $tahap->fresh(),
            'spk_status'       => $spk->fresh()->status,
            'percent_complete' => $percentComplete
        ]);
    }

    /**
     * Complete production.
     */
    public function completeProduction(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('selesai-produksi-spk') && !$user->can('approve-realisasi-produksi')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menyetujui penyelesaian SPK ini (selesai-produksi-spk).'
            ], 403);
        }

        $spk = SpkProduksi::forActiveCompany()->with('tahapans')->findOrFail($id);

        if (!in_array($spk->status, ['In Progress', 'Approved'])) {
            return response()->json(['message' => 'Hanya SPK yang sedang berjalan (In Progress) yang dapat diselesaikan.'], 422);
        }

        // Check if any stage is not yet finished (status !== 'Selesai' or qty_selesai < target_qty)
        $incompleteStages = $spk->tahapans->filter(function ($t) {
            return $t->status !== 'Selesai' || ((float)$t->target_qty > 0 && (float)$t->qty_selesai < (float)$t->target_qty);
        });

        if ($incompleteStages->count() > 0) {
            $names = $incompleteStages->pluck('nama_tahapan')->map(fn($n) => "'{$n}'")->implode(', ');
            return response()->json([
                'success' => false,
                'message' => "Gagal: Masih terdapat {$incompleteStages->count()} tahapan produksi yang belum selesai ({$names}). Seluruh tahapan harus diselesaikan 100% terlebih dahulu!"
            ], 422);
        }

        // Check if all raw materials have been fully issued (total_keluar_gudang >= total_kebutuhan)
        $hasUnfinishedMaterials = $spk->materials()->whereColumn('total_keluar_gudang', '<', 'total_kebutuhan')->exists();
        if ($hasUnfinishedMaterials) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan SPK! Transaksi Pengeluaran Bahan Baku SPK belum sepenuhnya selesai dicatat (belum keluar semua). Harap selesaikan pengeluaran bahan terlebih dahulu!'
            ], 422);
        }

        // Check if finished goods receipt is completed (total received >= target qty)
        $totalReceived = (float)$spk->penerimaanBarangJadis()->whereNull('deleted_at')->sum('jumlah_hasil_baik');
        if ($totalReceived < (float)$spk->jumlah_rencana) {
            return response()->json([
                'success' => false,
                'message' => "Gagal menyelesaikan SPK! Penerimaan Barang Jadi belum selesai sepenuhnya (Baru diterima: {$totalReceived} dari target: {$spk->jumlah_rencana}). Harap selesaikan penerimaan barang terlebih dahulu!"
            ], 422);
        }

        $lastTahapan = $spk->tahapans->sortByDesc('urutan')->first();
        $qtySelesai = $lastTahapan ? (float)$lastTahapan->qty_selesai : (float)$spk->jumlah_rencana;
        $qtyReject  = $lastTahapan ? (float)$lastTahapan->qty_reject : 0;

        DB::beginTransaction();
        try {
            $spk->update([
                'status'         => 'Completed',
                'jumlah_selesai' => $qtySelesai,
                'jumlah_reject'  => $qtyReject,
                'updated_by'     => $user->id,
            ]);

            // Adjust HPP of Finished Goods Receipts dynamically based on final actual raw materials cost
            PenerimaanBarangJadiController::adjustHppForSpk($spk);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error completing SPK and adjusting HPP: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan SPK: ' . $e->getMessage()
            ], 500);
        }

        WhatsAppGatewayController::runAsync(fn() => WhatsAppGatewayController::sendSpkCompletedNotification($spk, $user));

        return response()->json([
            'success' => true,
            'message' => "SPK {$spk->nomor_spk} telah selesai diproduksi (Completed)!"
        ], 200);
    }

    /**
     * Cancel SPK (Only Super Admin & Owner).
     */
    public function cancelProduction(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json(['message' => 'Hanya Super Admin dan Owner yang memiliki wewenang untuk membatalkan SPK.'], 403);
        }

        $spk = SpkProduksi::forActiveCompany()->findOrFail($id);

        if (!in_array($spk->status, ['Approved', 'In Progress'])) {
            return response()->json(['message' => 'Hanya SPK berstatus Approved atau Dalam Proses (In Progress) yang dapat dibatalkan.'], 422);
        }

        $alasan = $request->input('alasan', 'Dibatalkan oleh Super Admin / Owner.');

        $spk->update([
            'status'     => 'Cancelled',
            'keterangan' => ($spk->keterangan ? $spk->keterangan . "\n" : '') . "[Dibatalkan " . now()->format('d/m/Y H:i') . " oleh " . ($user->nama_lengkap ?: ($user->username ?: 'Super Admin')) . "]: " . $alasan,
            'updated_by' => $user->id,
        ]);

        // Reset tahapan yang sedang dikerjakan atau belum selesai menjadi Dibatalkan
        $spk->tahapans()->whereIn('status', ['Menunggu', 'Sedang Dikerjakan'])->update([
            'status' => 'Dibatalkan',
        ]);

        return response()->json([
            'success' => true,
            'message' => "SPK {$spk->nomor_spk} telah berhasil dibatalkan!"
        ], 200);
    }

    /**
     * Print PDF Work Order.
     */
    public function printPdf(Request $request, $id)
    {
        $this->authorize('cetak-spk');

        $spk = SpkProduksi::forActiveCompany()
            ->with([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'bom',
                'tender',
                'penanggungJawab.position',
                'penanggungJawab.user.activeSignature',
                'creator.activeSignature',
                'approver.activeSignature',
                'approver.position',
                'updater.activeSignature',
                'updater.position',
                'updater.karyawan.position',
                'materials.barangMentah.satuan',
                'materials.satuan',
                'tahapans'
            ])
            ->findOrFail($id);

        $company = $spk->company ?? Company::find(session('active_company_id')) ?? Company::first();

        // Cari User / PIC yang memulai produksi (updater saat status In Progress / Completed)
        $isProductionStarted = in_array($spk->status, ['In Progress', 'Completed']);
        $starterUser = null;
        if ($isProductionStarted && $spk->updater) {
            $starterUser = $spk->updater;
        }

        $picModel = $isProductionStarted ? ($starterUser ?: $spk->penanggungJawab) : $spk->penanggungJawab;

        $namaDiperiksaDefault = 'Supervisor / PIC Line';
        if ($isProductionStarted && $starterUser) {
            $namaDiperiksaDefault = $starterUser->nama_lengkap ?: ($starterUser->karyawan?->nama_lengkap ?: ($starterUser->username ?: 'PIC Line Produksi'));
        } elseif ($spk->penanggungJawab) {
            $namaDiperiksaDefault = $spk->penanggungJawab->nama_lengkap;
        }

        $jabatanDiperiksaDefault = 'Supervisor / PIC Line';
        if ($isProductionStarted && $starterUser) {
            $jabatanDiperiksaDefault = $starterUser->position?->nama_posisi ?: ($starterUser->karyawan?->position?->nama_posisi ?: 'PIC Line Produksi');
        } elseif ($spk->penanggungJawab) {
            $jabatanDiperiksaDefault = $spk->penanggungJawab->position?->nama_posisi ?: 'Supervisor / PIC Line';
        }

        $showTtd          = $request->boolean('show_ttd', true);
        $namaDibuat       = $request->input('nama_dibuat') ?: ($spk->creator->nama_lengkap ?? auth()->user()->nama_lengkap ?? 'Staff PPIC / Produksi');
        $namaDiperiksa    = $request->input('nama_diperiksa') ?: $namaDiperiksaDefault;
        $namaDisetujui    = $request->input('nama_disetujui') ?: ($spk->approver->nama_lengkap ?? 'Manajer Operasional / Produksi');
        $jabatanDiperiksa = $jabatanDiperiksaDefault;

        $getSigUrl = function($model) {
            if (!$model) return null;
            $user = null;
            if ($model instanceof \App\Models\User) {
                $user = $model;
            } elseif ($model instanceof \App\Models\Karyawan) {
                $user = $model->user ?: \App\Models\User::where('karyawan_id', $model->id)->first();
            }

            if ($user) {
                $sig = $user->activeSignature ?: \App\Models\UserSignature::where('user_id', $user->id)->where('is_active', true)->latest()->first();
                if ($sig) {
                    if (!empty($sig->signature_url)) return $sig->signature_url;
                    if (!empty($sig->signature_base64)) return $sig->signature_base64;
                    if (!empty($sig->signature_path)) return asset('storage/' . ltrim($sig->signature_path, '/'));
                }
            }

            if ($model instanceof \App\Models\Karyawan && !empty($model->active_signature_path)) {
                return asset('storage/' . ltrim($model->active_signature_path, '/'));
            }
            return null;
        };

        $creatorSigUrl   = $getSigUrl($spk->creator);
        $inspectorSigUrl = $getSigUrl($picModel);
        $approverSigUrl  = $getSigUrl($spk->approver);

        $ttdDibuatUrl    = $creatorSigUrl;
        $ttdDiperiksaUrl = $inspectorSigUrl;
        $ttdDisetujuiUrl = $approverSigUrl;

        return view('modul_produksi.spk.print_pdf', compact(
            'spk',
            'company',
            'showTtd',
            'namaDibuat',
            'namaDiperiksa',
            'namaDisetujui',
            'jabatanDiperiksa',
            'creatorSigUrl',
            'inspectorSigUrl',
            'approverSigUrl',
            'ttdDibuatUrl',
            'ttdDiperiksaUrl',
            'ttdDisetujuiUrl'
        ));
    }

    /**
     * Print PDF Production Progress & Work Log Report.
     */
    public function printProgressPdf(Request $request, $id)
    {
        $this->authorize('cetak-progress-spk');

        $spk = SpkProduksi::forActiveCompany()
            ->with([
                'company',
                'branch',
                'barangJadi.satuan',
                'satuan',
                'bom',
                'penanggungJawab.position',
                'penanggungJawab.user.activeSignature',
                'creator.activeSignature',
                'creator.position',
                'approver.activeSignature',
                'approver.position',
                'tahapans.logHarians.creator',
                'materials.barangMentah.satuan'
            ])
            ->findOrFail($id);

        $company = $spk->company ?? Company::find(session('active_company_id')) ?? Company::first();

        $getSigUrl = function($model) {
            if (!$model) return null;
            $sigModel = null;
            if ($model instanceof \App\Models\User) {
                $sigModel = $model->activeSignature;
            } elseif (isset($model->user) && $model->user) {
                $sigModel = $model->user->activeSignature;
            }
            if ($sigModel) {
                return $sigModel->signature_url ?: $sigModel->signature_base64;
            }
            return null;
        };

        $ttdDibuatUrl    = $getSigUrl($spk->creator);
        $ttdDiperiksaUrl = $getSigUrl($spk->penanggungJawab);
        $ttdDisetujuiUrl = $getSigUrl($spk->approver);

        $totalTahap = $spk->tahapans->count();
        $completedTahap = $spk->tahapans->where('status', 'Selesai')->count();
        $percentComplete = $totalTahap > 0 ? round(($completedTahap / $totalTahap) * 100) : 0;

        return view('modul_produksi.spk.print_progress_pdf', compact(
            'spk',
            'company',
            'ttdDibuatUrl',
            'ttdDiperiksaUrl',
            'ttdDisetujuiUrl',
            'totalTahap',
            'completedTahap',
            'percentComplete'
        ));
    }

    /**
     * Internal raw code generator.
     */
    private function generateRawKodeSpk()
    {
        $yearMonth = now()->format('Y/m');
        $prefix = 'SPK/' . $yearMonth . '/';

        $last = SpkProduksi::withTrashed()
            ->where(function($q) use ($prefix) {
                $q->where('nomor_spk', 'like', $prefix . '%')
                  ->orWhere('nomor_spk', 'like', 'SPK-' . now()->format('Y-m') . '-%');
            })
            ->orderBy('id', 'desc')
            ->value('nomor_spk');

        if ($last) {
            $lastNum = (int) preg_replace('/[^0-9]/', '', substr($last, -5));
            $nextNum = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '00001';
        }

        return $prefix . $nextNum;
    }
}
