<?php

namespace App\Http\Controllers;

use App\Models\AlokasiBiaya;
use App\Models\AlokasiBiayaDetail;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
use App\Models\PurchaseOrder;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Helpers\AccountMappingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlokasiBiayaController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('lihat-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk melihat menu Alokasi Biaya.');

        $userId       = auth()->id();
        $isManagerial = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance']);

        $query = AlokasiBiaya::forActiveCompany()
            ->forActiveBranch()
            ->when(!$isManagerial, function ($q) use ($userId) {
                // Draft hanya bisa dilihat oleh pembuatnya atau pengguna manajerial
                $q->where(function ($sub) use ($userId) {
                    $sub->where('status', '!=', 'Draft')
                        ->orWhere('created_by', $userId);
                });
            })
            ->with(['tender', 'poBiaya.supplier', 'poBiaya.tender', 'creator', 'details.barang', 'details.goodsReceipt'])
            ->orderByDesc('id');

        // Filter search
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('nomor_alokasi', 'like', "%{$s}%")
                  ->orWhere('keterangan', 'like', "%{$s}%")
                  ->orWhereHas('tender', function ($tQ) use ($s) {
                      $tQ->where('nomor_kontrak', 'like', "%{$s}%")
                         ->orWhere('nama_tender', 'like', "%{$s}%");
                  })
                  ->orWhereHas('poBiaya', function ($poQ) use ($s) {
                      $poQ->where('nomor_po', 'like', "%{$s}%")
                          ->orWhereHas('supplier', function ($supQ) use ($s) {
                              $supQ->where('nama_supplier', 'like', "%{$s}%");
                          });
                  });
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter metode alokasi
        if ($request->filled('metode')) {
            $query->where('metode_alokasi', $request->metode);
        }

        // Filter tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_alokasi', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tanggal_alokasi', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tanggal_alokasi', '<=', $request->end_date);
        }

        // Tampilan baris per halaman: 10 (default), 25, 50, 100
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $alokasiList = $query->paginate($perPage)->withQueryString();

        // Filter stats summary cards khusus berdasarkan bulan berjalan (current running month)
        $startOfMonth      = now()->startOfMonth()->toDateString();
        $endOfMonth        = now()->endOfMonth()->toDateString();
        $namaBulanBerjalan = now()->translatedFormat('F Y');

        $baseStats = AlokasiBiaya::forActiveCompany()
            ->forActiveBranch()
            ->whereBetween('tanggal_alokasi', [$startOfMonth, $endOfMonth])
            ->when(!$isManagerial, function ($q) use ($userId) {
                $q->where(function ($sub) use ($userId) {
                    $sub->where('status', '!=', 'Draft')
                        ->orWhere('created_by', $userId);
                });
            });

        // Stats summary: Satu query agregasi tunggal (Single-Pass Aggregation) agar super ngebut di 7+ juta data
        $stats = (clone $baseStats)
            ->selectRaw("
                COUNT(*) as total_alokasi,
                COUNT(CASE WHEN status = 'Draft' THEN 1 END) as total_draft,
                COUNT(CASE WHEN status = 'Posted' THEN 1 END) as total_posted,
                COUNT(CASE WHEN status = 'Batal' THEN 1 END) as total_batal,
                COALESCE(SUM(CASE WHEN status = 'Posted' THEN total_biaya ELSE 0 END), 0) as total_nominal
            ")
            ->first();

        $totalAlokasi = (int) ($stats->total_alokasi ?? 0);
        $totalDraft   = (int) ($stats->total_draft ?? 0);
        $totalPosted  = (int) ($stats->total_posted ?? 0);
        $totalBatal   = (int) ($stats->total_batal ?? 0);
        $totalNominal = (float) ($stats->total_nominal ?? 0);

        // Active Tenders for dropdown
        $tenders = \App\Models\Tender::forActiveCompany()
            ->forActiveBranch()
            ->whereIn('status', ['Won', 'Running', 'Active', 'Approved', 'Open'])
            ->orderByDesc('id')
            ->get(['id', 'nomor_kontrak', 'nama_tender']);

        return view('modul_purchasing.alokasi_biaya.index', compact(
            'alokasiList',
            'perPage',
            'totalAlokasi',
            'totalDraft',
            'totalPosted',
            'totalBatal',
            'totalNominal',
            'tenders',
            'namaBulanBerjalan',
            'isManagerial'
        ));
    }

    /**
     * AJAX get list of eligible POs (PO Jasa Ekspedisi / Pengiriman)
     * Menggunakan SQL WHERE NOT EXISTS terindeks agar 0 ms dan zero memory bloat di 7+ juta data
     */
    public function getEligiblePos(Request $request)
    {
        abort_if(!auth()->user()->can('tambah-alokasi-biaya') && !auth()->user()->can('edit-alokasi-biaya') && !auth()->user()->can('lihat-alokasi-biaya'), 403, 'Akses ditolak.');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        // PO yang sudah pernah dialokasikan (status Draft atau Posted) tidak boleh muncul lagi
        // Menggunakan index idx_alokasi_po_status via subquery WHERE NOT EXISTS
        $pos = PurchaseOrder::forActiveCompany()
            ->forActiveBranch()
            ->whereNotExists(function ($subQuery) use ($request, $activeBranchId, $activeCompanyId) {
                $subQuery->select(DB::raw(1))
                    ->from('alokasi_biayas')
                    ->whereColumn('alokasi_biayas.po_biaya_id', 'purchase_orders.id')
                    ->whereNull('alokasi_biayas.deleted_at')
                    ->whereIn('alokasi_biayas.status', ['Draft', 'Posted'])
                    ->when(!empty($activeBranchId), fn($q) => $q->where('alokasi_biayas.branch_id', $activeBranchId))
                    ->when(empty($activeBranchId) && !empty($activeCompanyId), fn($q) => $q->where('alokasi_biayas.company_id', $activeCompanyId)->whereNull('alokasi_biayas.branch_id'))
                    ->when($request->filled('alokasi_id'), fn($q) => $q->where('alokasi_biayas.id', '!=', $request->alokasi_id));
            })
            ->whereIn('status', ['Approved', 'Disetujui', 'Ordered', 'Confirmed', 'Closed', 'Partially Received', 'Received'])
            ->where(function ($query) {
                // Hanya PO yang memiliki item dengan tipe_item Jasa atau Kategori Jasa / Pengiriman / Ekspedisi
                $query->whereHas('details.barang', function ($bQ) {
                    $bQ->where('tipe_item', 'Jasa')
                       ->orWhereHas('kategori', function ($kQ) {
                           $kQ->where('nama_kategori', 'like', '%jasa%')
                              ->orWhere('nama_kategori', 'like', '%ekspedisi%')
                              ->orWhere('nama_kategori', 'like', '%pengiriman%')
                              ->orWhere('nama_kategori', 'like', '%ongkir%')
                              ->orWhere('nama_kategori', 'like', '%freight%')
                              ->orWhere('nama_kategori', 'like', '%cargo%')
                              ->orWhere('nama_kategori', 'like', '%kurir%')
                              ->orWhere('nama_kategori', 'like', '%delivery%')
                              ->orWhere('nama_kategori', 'like', '%transport%');
                       });
                })->orWhere(function ($q2) {
                    $q2->where('keterangan', 'like', '%jasa%')
                       ->orWhere('keterangan', 'like', '%ekspedisi%')
                       ->orWhere('keterangan', 'like', '%ongkir%')
                       ->orWhere('keterangan', 'like', '%pengiriman%');
                });
            })
            // Memastikan bukan PO pembelian barang fisik persediaan (Stok)
            ->whereDoesntHave('details.barang', function ($bQ) {
                $bQ->where('tipe_item', 'Stok');
            })
            ->with(['supplier', 'currency', 'tender', 'details.barang.kategori', 'pphPajak'])
            ->orderByDesc('id')
            ->take(100)
            ->get()
            ->map(function ($po) {
                $tenderName = $po->tender ? (!empty($po->tender->nomor_kontrak) ? $po->tender->nomor_kontrak . ($po->tender->nama_tender ? ' - ' . $po->tender->nama_tender : '') : $po->tender->nama_tender) : null;

                $namaJasa = $po->details->map(fn($d) => $d->barang?->nama_barang)->filter()->unique()->values()->implode(', ');

                $kurs = floatval($po->kurs ?: 1);
                if ($kurs <= 0) $kurs = 1;

                $subtotal = floatval($po->subtotal);
                $diskon   = floatval($po->total_diskon ?? 0);
                $biayaLain = floatval($po->biaya_lain ?? 0);
                $dppValas = max(0, $subtotal - $diskon + $biayaLain);
                $dppVal   = $dppValas * $kurs;
                $pajakVal = floatval($po->total_pajak ?? 0) * $kurs;
                $pphVal   = floatval($po->pph_nominal ?: ($po->total_pph ?: 0)) * $kurs;
                $grandVal = floatval($po->grand_total_idr ?: $po->grand_total);

                return [
                    'id'              => $po->id,
                    'nomor_po'        => $po->nomor_po,
                    'tanggal_po'      => $po->tanggal_po ? $po->tanggal_po->format('d/m/Y') : '-',
                    'supplier_nama'   => $po->supplier?->nama_supplier ?? 'Tanpa Vendor',
                    'nama_jasa'       => $namaJasa ?: 'Jasa Pengiriman / Ekspedisi',
                    'currency_kode'   => $po->currency?->kode_currency ?? 'IDR',
                    'currency_simbol' => $po->currency?->simbol ?? 'Rp',
                    'kurs'            => $kurs,
                    'dpp_valas'       => $dppValas,
                    'dpp'             => $dppVal,
                    'subtotal'        => $subtotal * $kurs,
                    'total_pajak'     => $pajakVal,
                    'tarif_pph'       => floatval($po->tarif_pph ?? 0),
                    'pph_nominal'     => $pphVal,
                    'nama_pph'        => $po->pphPajak?->nama_pajak ?? 'PPh',
                    'grand_total'     => $grandVal,
                    'biaya_lain'      => $biayaLain,
                    'status'          => $po->status,
                    'tender_id'       => $po->tender_id,
                    'nomor_tender'    => $tenderName,
                ];
            });

        return response()->json(['success' => true, 'data' => $pos]);
    }

    /**
     * AJAX get list of eligible Goods Receipts (Posted GRs with items)
     */
    public function getEligibleGrs(Request $request)
    {
        abort_if(!auth()->user()->can('tambah-alokasi-biaya') && !auth()->user()->can('edit-alokasi-biaya') && !auth()->user()->can('lihat-alokasi-biaya'), 403, 'Akses ditolak.');

        $selectedIds = $request->input('selected_gr_ids', []);
        if (is_string($selectedIds)) {
            $selectedIds = explode(',', $selectedIds);
        }
        $selectedIds = array_filter(array_map('intval', (array) $selectedIds));

        $baseQuery = GoodsReceipt::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'Posted');

        if (!empty($selectedIds)) {
            // Pastikan GR yang sebelumnya dipilih tetap ter-load meskipun lebih lama dari 200 data terbaru
            $selectedGrs = (clone $baseQuery)->whereIn('id', $selectedIds)->get();
            $recentGrs   = (clone $baseQuery)->whereNotIn('id', $selectedIds)->orderByDesc('tanggal_gr')->take(200)->get();
            $rawGrs      = $selectedGrs->merge($recentGrs);
        } else {
            $rawGrs      = $baseQuery->orderByDesc('tanggal_gr')->take(200)->get();
        }

        $rawGrs->loadMissing(['po.supplier', 'po.tender', 'po.currency', 'tender', 'gudang', 'details.barang.satuan', 'details.satuan', 'details.poDetail']);

        $grs = $rawGrs->map(function ($gr) {
                $totalNilai = 0;
                $totalQty = 0;

                $kurs = floatval($gr->kurs ?: ($gr->po?->kurs ?: 1));
                if ($kurs <= 0) $kurs = 1;

                $items = $gr->details->map(function ($d) use (&$totalNilai, &$totalQty, $gr, $kurs) {
                    $qty = floatval($d->jumlah);
                    $poDetail = $d->poDetail;
                    $hargaSatuan = $poDetail ? floatval($poDetail->harga_satuan) : 0;
                    $diskon = $poDetail ? floatval($poDetail->diskon_item ?? 0) : 0;
                    $hargaNet = max(0, $hargaSatuan - $diskon);
                    
                    $poFaktor = ($poDetail && floatval($poDetail->faktor_pengali) > 0) ? floatval($poDetail->faktor_pengali) : 1.0;
                    $grFaktor = floatval($d->faktor_pengali) > 0 ? floatval($d->faktor_pengali) : 1.0;
                    
                    // Harga beli per satuan GR dalam IDR
                    $hargaIdr = (($hargaNet * $kurs) / $poFaktor) * $grFaktor;

                    $subtotal = $qty * $hargaIdr;
                    $totalNilai += $subtotal;
                    $totalQty += $qty;

                    $satuanNama = $d->satuan?->nama_satuan ?? ($d->barang?->satuan?->nama_satuan ?? 'Unit');

                    return [
                        'gr_detail_id'      => $d->id,
                        'barang_id'         => $d->barang_id,
                        'kode_barang'       => $d->barang?->kode_barang ?? '-',
                        'nama_barang'       => $d->barang?->nama_barang ?? 'Barang',
                        'satuan_nama'       => $satuanNama,
                        'qty'               => $qty,
                        'harga_beli_satuan' => $hargaIdr,
                        'subtotal'          => $subtotal,
                    ];
                });

                $grTender = $gr->tender ?? $gr->po?->tender;
                $grTenderDisplay = null;
                if ($grTender) {
                    $grTenderDisplay = !empty($grTender->nomor_kontrak) && $grTender->nomor_kontrak !== '-'
                        ? ($grTender->nomor_kontrak . (!empty($grTender->nama_tender) ? ' - ' . $grTender->nama_tender : ''))
                        : ($grTender->nama_tender ?: ('Tender #' . $grTender->id));
                }

                return [
                    'id'                => $gr->id,
                    'nomor_gr'          => $gr->nomor_gr,
                    'tanggal_gr'        => $gr->tanggal_gr ? $gr->tanggal_gr->format('d/m/Y') : '-',
                    'nomor_po'          => $gr->po?->nomor_po ?? '-',
                    'supplier_nama'     => $gr->po?->supplier?->nama_supplier ?? '-',
                    'nama_gudang'       => $gr->gudang?->nama_gudang ?? '-',
                    'currency_kode'     => $gr->po?->currency?->kode_currency ?? 'IDR',
                    'kurs'              => $kurs,
                    'tender_id'         => $grTender?->id,
                    'nomor_tender'      => $grTenderDisplay,
                    'total_qty'         => $totalQty,
                    'total_nilai'       => $totalNilai,
                    'items'             => $items,
                ];
            });

        return response()->json(['success' => true, 'data' => $grs]);
    }

    /**
     * Helper to compute mathematically accurate allocation rows from database records and system total cost.
     * Prevents any client payload tampering and ensures 100% penny-balancing.
     */
    protected function computeAllocationRows(array $grIds, float $totalBiaya, string $metode): array
    {
        $grs = GoodsReceipt::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'Posted')
            ->whereIn('id', $grIds)
            ->with(['po.supplier', 'details.barang.satuan', 'details.satuan', 'details.poDetail'])
            ->get();

        $rows = [];
        $totalBasis = 0;

        foreach ($grs as $gr) {
            $kurs = floatval($gr->kurs ?: ($gr->po?->kurs ?: 1));
            if ($kurs <= 0) $kurs = 1;

            foreach ($gr->details as $d) {
                $qty = floatval($d->jumlah);
                if ($qty <= 0) continue;

                $poDetail = $d->poDetail;
                $hargaSatuan = $poDetail ? floatval($poDetail->harga_satuan) : 0;
                $diskon = $poDetail ? floatval($poDetail->diskon_item ?? 0) : 0;
                $hargaNet = max(0, $hargaSatuan - $diskon);

                $poFaktor = ($poDetail && floatval($poDetail->faktor_pengali) > 0) ? floatval($poDetail->faktor_pengali) : 1.0;
                $grFaktor = floatval($d->faktor_pengali) > 0 ? floatval($d->faktor_pengali) : 1.0;

                $hargaNetIdr = (($hargaNet * $kurs) / $poFaktor) * $grFaktor;
                $nilaiBarang = $qty * $hargaNetIdr;
                $satuanNama  = $d->satuan?->nama_satuan ?? ($d->barang?->satuan?->nama_satuan ?? 'Unit');

                $rows[] = [
                    'gr_id'             => $gr->id,
                    'nomor_gr'          => $gr->nomor_gr,
                    'gr_detail_id'      => $d->id,
                    'barang_id'         => $d->barang_id,
                    'kode_barang'       => $d->barang?->kode_barang ?? '-',
                    'nama_barang'       => $d->barang?->nama_barang ?? 'Barang',
                    'satuan_nama'       => $satuanNama,
                    'qty'               => $qty,
                    'harga_beli_satuan' => $hargaNetIdr,
                    'nilai_barang'      => $nilaiBarang,
                    'hpp_awal'          => $hargaNetIdr,
                ];

                if ($metode === 'Berdasarkan Nilai Barang') {
                    $totalBasis += $nilaiBarang;
                } else {
                    $totalBasis += $qty;
                }
            }
        }

        if (empty($rows)) {
            return [];
        }

        $allocatedItems = [];
        $runningBiayaTotal = 0;
        $totalItems = count($rows);

        foreach ($rows as $index => $row) {
            $basis = ($metode === 'Berdasarkan Nilai Barang') ? $row['nilai_barang'] : $row['qty'];
            $porsi = ($totalBasis > 0) ? ($basis / $totalBasis) : (1 / $totalItems);
            $porsiPersen = round($porsi * 100, 4);

            if ($index === $totalItems - 1) {
                $biayaAllocated = max(0, round($totalBiaya - $runningBiayaTotal, 2));
            } else {
                $biayaAllocated = round($porsi * $totalBiaya, 2);
                $runningBiayaTotal += $biayaAllocated;
            }

            $biayaPerUnit = $row['qty'] > 0 ? round($biayaAllocated / $row['qty'], 4) : 0;
            $hppBaru = round($row['hpp_awal'] + $biayaPerUnit, 4);

            $allocatedItems[] = array_merge($row, [
                'porsi_persen'       => $porsiPersen,
                'biaya_dialokasikan' => $biayaAllocated,
                'biaya_per_unit'     => $biayaPerUnit,
                'hpp_baru'           => $hppBaru,
            ]);
        }

        return $allocatedItems;
    }

    /**
     * AJAX Calculate preview allocation across selected Goods Receipts
     */
    public function calculatePreview(Request $request)
    {
        abort_if(!auth()->user()->can('tambah-alokasi-biaya') && !auth()->user()->can('edit-alokasi-biaya') && !auth()->user()->can('lihat-alokasi-biaya'), 403, 'Akses ditolak.');

        $request->validate([
            'total_biaya'    => 'required|numeric|min:1',
            'metode_alokasi' => 'required|in:Berdasarkan Nilai Barang,Berdasarkan Qty',
            'gr_ids'         => 'required|array|min:1',
            'gr_ids.*'       => 'integer|exists:goods_receipts,id',
        ]);

        $totalBiaya = floatval($request->total_biaya);
        $metode = $request->metode_alokasi;
        $grIds = $request->gr_ids;

        $allocatedItems = $this->computeAllocationRows($grIds, $totalBiaya, $metode);
        if (empty($allocatedItems)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada item barang dalam Penerimaan Barang yang dipilih.'], 422);
        }

        $totalBasis = ($metode === 'Berdasarkan Nilai Barang')
            ? array_sum(array_column($allocatedItems, 'nilai_barang'))
            : array_sum(array_column($allocatedItems, 'qty'));

        return response()->json([
            'success'     => true,
            'total_basis' => $totalBasis,
            'total_biaya' => $totalBiaya,
            'metode'      => $metode,
            'items'       => $allocatedItems,
        ]);
    }

    /**
     * Store new Alokasi Biaya (Draft or Posted)
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('tambah-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk membuat Alokasi Biaya.');
        if (!$request->boolean('is_draft')) {
            abort_if(!auth()->user()->can('posting-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk melakukan Posting Alokasi Biaya.');
        }

        $request->validate([
            'tanggal_alokasi' => 'required|date',
            'po_biaya_id'     => 'required|exists:purchase_orders,id',
            'tender_id'       => 'nullable|exists:tenders,id',
            'total_biaya'     => 'required|numeric|min:1',
            'metode_alokasi'  => 'required|in:Berdasarkan Nilai Barang,Berdasarkan Qty',
            'keterangan'      => 'nullable|string|max:40',
            'items'           => 'required|array|min:1',
            'items.*.gr_id'   => 'required|exists:goods_receipts,id',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.qty'     => 'required|numeric|min:0.0001',
            'items.*.biaya_dialokasikan' => 'required|numeric|min:0',
        ]);

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        // Validasi kepemilikan PO Jasa Ekspedisi oleh PT / Cabang yang sedang aktif
        $poBiaya = PurchaseOrder::forActiveCompany()->forActiveBranch()->find($request->po_biaya_id);
        if (!$poBiaya) {
            return response()->json([
                'success' => false,
                'message' => 'PO Biaya yang dipilih tidak ditemukan atau bukan milik unit kerja Anda.',
            ], 403);
        }

        // Validasi status PO harus disetujui (Approved / Ordered / Closed)
        if (!in_array($poBiaya->status, ['Approved', 'Disetujui', 'Ordered', 'Confirmed', 'Closed', 'Partially Received', 'Received'])) {
            return response()->json([
                'success' => false,
                'message' => 'PO Biaya harus berstatus disetujui (Approved / Ordered / Confirmed / Closed).',
            ], 422);
        }

        // Cegah duplikasi: Pastikan PO belum pernah dialokasikan di alokasi aktif (Draft / Posted)
        $isPoAlreadyUsed = AlokasiBiaya::where('po_biaya_id', $request->po_biaya_id)
            ->whereIn('status', ['Draft', 'Posted'])
            ->exists();

        if ($isPoAlreadyUsed) {
            return response()->json([
                'success' => false,
                'message' => 'PO Jasa Ekspedisi ini sudah pernah dialokasikan sebelumnya. Harap pilih PO lainnya agar tidak duplikat.',
            ], 422);
        }

        // Validasi kepemilikan Proyek / Tender jika diisi
        if ($request->filled('tender_id')) {
            $validTender = \App\Models\Tender::forActiveCompany()
                ->forActiveBranch()
                ->where('id', $request->tender_id)
                ->exists();
            if (!$validTender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proyek / Tender yang dipilih tidak valid atau bukan milik unit kerja Anda.',
                ], 403);
            }
        }

        // Validasi kepemilikan dan status seluruh GR penerimaan barang oleh PT / Cabang yang sedang aktif (Hanya GR Posted)
        $submittedGrIds = collect($request->items)->pluck('gr_id')->unique()->toArray();
        $validGrsCount = GoodsReceipt::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'Posted')
            ->whereIn('id', $submittedGrIds)
            ->count();
        if ($validGrsCount !== count($submittedGrIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat Penerimaan Barang (GR) yang tidak valid, belum diposting, atau bukan milik unit kerja Anda.',
            ], 403);
        }

        // Hitung total biaya resmi sistem dari PO Jasa terpilih (Murni DPP IDR: Subtotal - Diskon + Biaya Lain)
        // Catatan Akuntansi: PPh (Withholding Tax) dan PPN (Pajak Masukan) TIDAK BOLEH dihitung ke HPP persediaan barang
        $kursPo = floatval($poBiaya->kurs ?: 1);
        if ($kursPo <= 0) $kursPo = 1;
        $subtotalPo  = floatval($poBiaya->subtotal);
        $diskonPo    = floatval($poBiaya->total_diskon ?? 0);
        $biayaLainPo = floatval($poBiaya->biaya_lain ?? 0);
        $dppIdrPo    = max(0, $subtotalPo - $diskonPo + $biayaLainPo) * $kursPo;

        if ($dppIdrPo <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nilai DPP dari PO Jasa terpilih adalah 0 atau tidak valid. Biaya yang dialokasikan ke HPP harus lebih besar dari 0.',
            ], 422);
        }
        $systemTotalBiaya = round($dppIdrPo, 2);

        // Hitung rincian alokasi resmi dari database secara server-side untuk menjamin 100% akurasi & anti-manipulasi
        $officialItems = $this->computeAllocationRows($submittedGrIds, $systemTotalBiaya, $request->metode_alokasi);
        if (empty($officialItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada item barang dalam Penerimaan Barang (GR) yang dipilih.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Kunci baris PurchaseOrder agar mencegah race-condition double submit
            PurchaseOrder::where('id', $request->po_biaya_id)->lockForUpdate()->first();

            // Double check duplikasi di dalam transaksi
            $isDuplicateInsideTx = AlokasiBiaya::where('po_biaya_id', $request->po_biaya_id)
                ->whereIn('status', ['Draft', 'Posted'])
                ->exists();
            if ($isDuplicateInsideTx) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'PO Jasa Ekspedisi ini sudah dialokasikan oleh transaksi lain.',
                ], 422);
            }

            // Auto generation of Nomor Alokasi (ALB/{Entity}/{YYYY}/{MM}/{XXXX})
            $nomorAlokasi = AlokasiBiaya::generateNomorAlokasi($activeCompanyId, $activeBranchId, $request->tanggal_alokasi);

            $targetBranchId  = !empty($activeBranchId) ? $activeBranchId : null;
            $targetCompanyId = !empty($targetBranchId) ? null : ($activeCompanyId ?: 1);

            $isDraft = $request->boolean('is_draft');

            $alokasi = AlokasiBiaya::create([
                'company_id'      => $targetCompanyId,
                'branch_id'       => $targetBranchId,
                'nomor_alokasi'   => $nomorAlokasi,
                'tanggal_alokasi' => $request->tanggal_alokasi,
                'po_biaya_id'     => $request->po_biaya_id,
                'tender_id'       => $request->tender_id ?: ($poBiaya->tender_id ?? null),
                'total_biaya'     => $systemTotalBiaya,
                'metode_alokasi'  => $request->metode_alokasi,
                'status'          => $isDraft ? 'Draft' : 'Posted',
                'keterangan'      => $request->keterangan,
            ]);

            // Save Details menggunakan hasil kalkulasi resmi server
            foreach ($officialItems as $item) {
                AlokasiBiayaDetail::create([
                    'alokasi_biaya_id'   => $alokasi->id,
                    'gr_id'              => $item['gr_id'],
                    'gr_detail_id'       => $item['gr_detail_id'] ?? null,
                    'barang_id'          => $item['barang_id'],
                    'qty_diterima'       => floatval($item['qty']),
                    'satuan_nama'        => $item['satuan_nama'] ?? null,
                    'harga_beli_satuan'  => floatval($item['harga_beli_satuan'] ?? 0),
                    'nilai_barang'       => floatval($item['nilai_barang'] ?? 0),
                    'porsi_persen'       => floatval($item['porsi_persen'] ?? 0),
                    'biaya_dialokasikan' => floatval($item['biaya_dialokasikan']),
                    'biaya_per_unit'     => floatval($item['biaya_per_unit'] ?? 0),
                    'hpp_awal'           => floatval($item['hpp_awal'] ?? 0),
                    'hpp_baru'           => floatval($item['hpp_baru'] ?? 0),
                ]);
            }

            if (!$isDraft) {
                $this->processPosting($alokasi);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Alokasi biaya berhasil disimpan ' . ($isDraft ? 'sebagai Draft' : 'dan langsung di-Posting!') . '.',
                'alokasi' => $alokasi,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store AlokasiBiaya: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX get data for editing an Alokasi Biaya (Draft only)
     */
    public function getEditData($id)
    {
        abort_if(!auth()->user()->can('edit-alokasi-biaya') && !auth()->user()->can('tambah-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk mengedit Alokasi Biaya.');

        $userId = auth()->id();
        $isManager = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance']);

        $alokasi = AlokasiBiaya::forActiveCompany()
            ->forActiveBranch()
            ->with([
                'poBiaya.supplier',
                'tender',
                'details.goodsReceipt.po.supplier',
                'details.goodsReceipt.gudang',
                'details.barang.satuan'
            ])
            ->findOrFail($id);

        if ($alokasi->status !== 'Draft') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen alokasi biaya tidak dapat diedit karena statusnya ' . $alokasi->status . '. Edit hanya diperbolehkan jika status dokumen masih Draft.',
            ], 422);
        }

        if (!$isManager && $alokasi->created_by != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat mengedit dokumen Draft yang Anda buat sendiri.',
            ], 403);
        }

        $selectedGrIds = $alokasi->details->pluck('gr_id')->unique()->values()->toArray();

        return response()->json([
            'success'         => true,
            'data'            => $alokasi,
            'selected_gr_ids' => $selectedGrIds,
        ]);
    }

    /**
     * Update an existing Alokasi Biaya (Draft only)
     */
    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->can('edit-alokasi-biaya') && !auth()->user()->can('tambah-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk mengedit Alokasi Biaya.');

        if (!$request->boolean('is_draft')) {
            abort_if(!auth()->user()->can('posting-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk melakukan Posting Alokasi Biaya.');
        }

        $userId = auth()->id();
        $isManager = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance']);

        $request->validate([
            'tanggal_alokasi' => 'required|date',
            'po_biaya_id'     => 'required|exists:purchase_orders,id',
            'tender_id'       => 'nullable|exists:tenders,id',
            'total_biaya'     => 'required|numeric|min:1',
            'metode_alokasi'  => 'required|in:Berdasarkan Nilai Barang,Berdasarkan Qty',
            'keterangan'      => 'nullable|string|max:40',
            'items'           => 'required|array|min:1',
            'items.*.gr_id'   => 'required|exists:goods_receipts,id',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.qty'     => 'required|numeric|min:0.0001',
            'items.*.biaya_dialokasikan' => 'required|numeric|min:0',
        ]);

        // Validasi kepemilikan Proyek / Tender jika diisi
        if ($request->filled('tender_id')) {
            $validTender = \App\Models\Tender::forActiveCompany()
                ->forActiveBranch()
                ->where('id', $request->tender_id)
                ->exists();
            if (!$validTender) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proyek / Tender yang dipilih tidak valid atau bukan milik unit kerja Anda.',
                ], 403);
            }
        }

        // Validasi kepemilikan PO Jasa Ekspedisi oleh PT / Cabang yang sedang aktif
        $poBiaya = PurchaseOrder::forActiveCompany()->forActiveBranch()->find($request->po_biaya_id);
        if (!$poBiaya) {
            return response()->json([
                'success' => false,
                'message' => 'PO Biaya yang dipilih tidak ditemukan atau bukan milik unit kerja Anda.',
            ], 403);
        }

        // Validasi status PO harus disetujui (Approved / Ordered / Closed)
        if (!in_array($poBiaya->status, ['Approved', 'Disetujui', 'Ordered', 'Confirmed', 'Closed', 'Partially Received', 'Received'])) {
            return response()->json([
                'success' => false,
                'message' => 'PO Biaya harus berstatus disetujui (Approved / Ordered / Confirmed / Closed).',
            ], 422);
        }

        // Cegah duplikasi: Pastikan PO belum pernah dialokasikan di alokasi aktif LAINNYA
        $isPoAlreadyUsed = AlokasiBiaya::where('id', '!=', $id)
            ->where('po_biaya_id', $request->po_biaya_id)
            ->whereIn('status', ['Draft', 'Posted'])
            ->exists();

        if ($isPoAlreadyUsed) {
            return response()->json([
                'success' => false,
                'message' => 'PO Jasa Ekspedisi ini sudah pernah dialokasikan di dokumen lain. Harap pilih PO lainnya.',
            ], 422);
        }

        // Validasi kepemilikan dan status seluruh GR penerimaan barang oleh PT / Cabang yang sedang aktif (Hanya GR Posted)
        $submittedGrIds = collect($request->items)->pluck('gr_id')->unique()->toArray();
        $validGrsCount = GoodsReceipt::forActiveCompany()
            ->forActiveBranch()
            ->where('status', 'Posted')
            ->whereIn('id', $submittedGrIds)
            ->count();
        if ($validGrsCount !== count($submittedGrIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat Penerimaan Barang (GR) yang tidak valid, belum diposting, atau bukan milik unit kerja Anda.',
            ], 403);
        }

        // Hitung total biaya resmi sistem dari PO Jasa terpilih (Murni DPP IDR: Subtotal - Diskon + Biaya Lain)
        // Catatan Akuntansi: PPh (Withholding Tax) dan PPN (Pajak Masukan) TIDAK BOLEH dihitung ke HPP persediaan barang
        $kursPo = floatval($poBiaya->kurs ?: 1);
        if ($kursPo <= 0) $kursPo = 1;
        $subtotalPo  = floatval($poBiaya->subtotal);
        $diskonPo    = floatval($poBiaya->total_diskon ?? 0);
        $biayaLainPo = floatval($poBiaya->biaya_lain ?? 0);
        $dppIdrPo    = max(0, $subtotalPo - $diskonPo + $biayaLainPo) * $kursPo;

        if ($dppIdrPo <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nilai DPP dari PO Jasa terpilih adalah 0 atau tidak valid. Biaya yang dialokasikan ke HPP harus lebih besar dari 0.',
            ], 422);
        }
        $systemTotalBiaya = round($dppIdrPo, 2);

        // Hitung rincian alokasi resmi dari database secara server-side
        $officialItems = $this->computeAllocationRows($submittedGrIds, $systemTotalBiaya, $request->metode_alokasi);
        if (empty($officialItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada item barang dalam Penerimaan Barang (GR) yang dipilih.',
            ], 422);
        }

        $isDraft = $request->boolean('is_draft');

        try {
            DB::beginTransaction();

            // Kunci baris PurchaseOrder agar konsisten dalam transaksi
            PurchaseOrder::where('id', $request->po_biaya_id)->lockForUpdate()->first();

            $alokasi = AlokasiBiaya::forActiveCompany()
                ->forActiveBranch()
                ->lockForUpdate()
                ->findOrFail($id);

            if ($alokasi->status !== 'Draft') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen alokasi biaya tidak dapat diedit karena statusnya ' . $alokasi->status . '. Edit hanya diperbolehkan jika status dokumen masih Draft.',
                ], 422);
            }

            if (!$isManager && $alokasi->created_by != $userId) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat mengedit dokumen Draft yang Anda buat sendiri.',
                ], 403);
            }

            $alokasi->update([
                'tanggal_alokasi' => $request->tanggal_alokasi,
                'po_biaya_id'     => $request->po_biaya_id,
                'tender_id'       => $request->tender_id ?: ($poBiaya->tender_id ?? null),
                'total_biaya'     => $systemTotalBiaya,
                'metode_alokasi'  => $request->metode_alokasi,
                'status'          => $isDraft ? 'Draft' : 'Posted',
                'keterangan'      => $request->keterangan,
                'updated_by'      => $userId,
            ]);

            // Hapus detail lama, kemudian buat ulang detail baru dari kalkulasi resmi server
            $alokasi->details()->delete();

            foreach ($officialItems as $item) {
                AlokasiBiayaDetail::create([
                    'alokasi_biaya_id'   => $alokasi->id,
                    'gr_id'              => $item['gr_id'],
                    'gr_detail_id'       => $item['gr_detail_id'] ?? null,
                    'barang_id'          => $item['barang_id'],
                    'qty_diterima'       => floatval($item['qty']),
                    'satuan_nama'        => $item['satuan_nama'] ?? null,
                    'harga_beli_satuan'  => floatval($item['harga_beli_satuan'] ?? 0),
                    'nilai_barang'       => floatval($item['nilai_barang'] ?? 0),
                    'porsi_persen'       => floatval($item['porsi_persen'] ?? 0),
                    'biaya_dialokasikan' => floatval($item['biaya_dialokasikan']),
                    'biaya_per_unit'     => floatval($item['biaya_per_unit'] ?? 0),
                    'hpp_awal'           => floatval($item['hpp_awal'] ?? 0),
                    'hpp_baru'           => floatval($item['hpp_baru'] ?? 0),
                ]);
            }

            if (!$isDraft) {
                $alokasi->load(['details.goodsReceipt', 'details.barang.kategori', 'poBiaya.supplier', 'poBiaya.tender', 'tender']);
                $this->processPosting($alokasi);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isDraft 
                    ? "Perubahan Alokasi Biaya #{$alokasi->nomor_alokasi} berhasil disimpan sebagai Draft."
                    : "Alokasi Biaya #{$alokasi->nomor_alokasi} berhasil diperbarui dan diposting ke HPP persediaan serta jurnal akuntansi.",
                'alokasi' => $alokasi,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update AlokasiBiaya: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show detail of Alokasi Biaya
     */
    public function show($id)
    {
        abort_if(!auth()->user()->can('detail-alokasi-biaya') && !auth()->user()->can('lihat-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk melihat detail dokumen ini.');

        $userId = auth()->id();
        $isManager = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance']);

        $alokasi = AlokasiBiaya::forActiveCompany()
            ->forActiveBranch()
            ->where(function ($q) use ($userId, $isManager) {
                if ($isManager) {
                    return;
                }
                // Draft hanya bisa dilihat oleh pembuatnya atau manajerial
                $q->where('status', '!=', 'Draft')
                  ->orWhere('created_by', $userId);
            })
            ->with([
                'poBiaya.supplier',
                'poBiaya.currency',
                'poBiaya.pphPajak',
                'tender',
                'creator',
                'details.barang.satuan',
                'details.goodsReceipt.gudang'
            ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $alokasi,
        ]);
    }

    /**
     * Get Journal Detail for Alokasi Biaya
     */
    public function getJurnal($id)
    {
        abort_if(!auth()->user()->can('lihat-jurnal-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk melihat jurnal dokumen alokasi biaya ini.');

        $userId = auth()->id();
        $isManagerial = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance']);

        $alokasi = AlokasiBiaya::forActiveCompany()
            ->forActiveBranch()
            ->when(!$isManagerial, function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('status', '!=', 'Draft')
                      ->orWhere('created_by', $userId);
                });
            })
            ->with([
                'poBiaya.supplier',
                'poBiaya.tender.customer',
                'tender.customer',
                'company',
                'branch'
            ])
            ->findOrFail($id);

        $jurnal = Jurnal::where('referensi_tipe', 'Alokasi Biaya')
            ->where('referensi_id', $alokasi->id)
            ->with([
                'details.account',
                'company',
                'branch',
                'creator',
            ])
            ->latest('id')
            ->first();

        if (!$jurnal) {
            return response()->json([
                'success' => false,
                'message' => "Jurnal akuntansi untuk alokasi biaya {$alokasi->nomor_alokasi} belum ditemukan atau dokumen belum diposting.",
            ], 404);
        }

        $tenderObj = $alokasi->tender ?? $alokasi->poBiaya?->tender;
        $tenderData = null;
        if ($tenderObj) {
            $customerName = $tenderObj->customer?->nama_customer ?? $tenderObj->customer?->nama_perusahaan ?? '-';
            $tenderData = [
                'id'            => $tenderObj->id,
                'nomor_kontrak' => $tenderObj->nomor_kontrak,
                'nama_tender'   => $tenderObj->nama_tender,
                'customer'      => $customerName,
                'display'       => (!empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-')
                    ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                    : ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)),
            ];
        }

        return response()->json([
            'success' => true,
            'alokasi' => [
                'id'            => $alokasi->id,
                'nomor_alokasi' => $alokasi->nomor_alokasi,
                'status'        => $alokasi->status,
                'total_biaya'   => $alokasi->total_biaya,
                'vendor'        => $alokasi->poBiaya?->supplier?->nama_supplier ?? 'Ekspedisi',
                'nomor_po'      => $alokasi->poBiaya?->nomor_po ?? '-',
            ],
            'tender'  => $tenderData,
            'jurnal'  => $jurnal,
        ]);
    }

    /**
     * Post an existing Draft Alokasi Biaya
     */
    public function post($id)
    {
        abort_if(!auth()->user()->can('posting-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk mem-Posting Alokasi Biaya.');

        $userId = auth()->id();
        $isManager = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance']);

        try {
            DB::beginTransaction();

            $alokasi = AlokasiBiaya::forActiveCompany()
                ->forActiveBranch()
                ->where(function ($q) use ($userId, $isManager) {
                    if ($isManager) return;
                    $q->where('status', '!=', 'Draft')->orWhere('created_by', $userId);
                })
                ->lockForUpdate()
                ->findOrFail($id);

            if ($alokasi->status === 'Batal') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Dokumen yang sudah dibatalkan tidak dapat di-Posting.'], 422);
            }

            if ($alokasi->status === 'Posted') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Dokumen alokasi ini sudah di-Posting sebelumnya.'], 422);
            }

            $alokasi->load(['details.goodsReceipt', 'details.barang.kategori', 'poBiaya.supplier', 'poBiaya.tender', 'tender']);
            $this->processPosting($alokasi);
            $alokasi->update(['status' => 'Posted']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Alokasi biaya {$alokasi->nomor_alokasi} berhasil di-Posting ke HPP & Jurnal Akuntansi!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error posting AlokasiBiaya: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal posting alokasi biaya: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batalkan Dokumen Alokasi Biaya (Draft atau Posted dengan Rollback)
     */
    public function cancel(Request $request, $id)
    {
        abort_if(!auth()->user()->can('batalkan-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk membatalkan Alokasi Biaya.');

        $request->validate([
            'alasan_batal' => 'required|string|max:30',
        ], [
            'alasan_batal.required' => 'Alasan pembatalan wajib diisi.',
            'alasan_batal.max'      => 'Alasan pembatalan maksimal 30 karakter.',
        ]);

        $userId = auth()->id();
        $isManagerial = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance']);

        try {
            DB::beginTransaction();

            $alokasi = AlokasiBiaya::forActiveCompany()
                ->forActiveBranch()
                ->when(!$isManagerial, function ($query) use ($userId) {
                    $query->where(function ($q) use ($userId) {
                        $q->where('status', '!=', 'Draft')
                          ->orWhere('created_by', $userId);
                    });
                })
                ->lockForUpdate()
                ->findOrFail($id);

            if ($alokasi->status === 'Batal') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Dokumen alokasi ini sudah dibatalkan sebelumnya.'], 422);
            }

            $wasPosted = ($alokasi->status === 'Posted');

            if ($wasPosted) {
                $this->processRollbackPosting($alokasi);
            }

            $alokasi->update([
                'status'       => 'Batal',
                'alasan_batal' => trim($request->alasan_batal),
                'updated_by'   => $userId,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Dokumen alokasi biaya {$alokasi->nomor_alokasi} berhasil dibatalkan" . ($wasPosted ? " dan seluruh penyesuaian persediaan stok & jurnal telah di-rollback!" : "."),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancel AlokasiBiaya: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan alokasi biaya: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus dokumen Alokasi Biaya (Hanya status Draft yang diizinkan)
     */
    public function destroy(Request $request, $id)
    {
        abort_if(!auth()->user()->can('hapus-alokasi-biaya') && !auth()->user()->can('batalkan-alokasi-biaya'), 403, 'Anda tidak memiliki hak akses untuk menghapus dokumen Alokasi Biaya.');

        $userId = auth()->id();
        $isManagerial = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Manager Finance']);

        try {
            DB::beginTransaction();

            $alokasi = AlokasiBiaya::forActiveCompany()
                ->forActiveBranch()
                ->when(!$isManagerial, function ($query) use ($userId) {
                    $query->where('created_by', $userId);
                })
                ->lockForUpdate()
                ->findOrFail($id);

            if ($alokasi->status !== 'Draft') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya dokumen alokasi berstatus Draft yang dapat dihapus! Dokumen berstatus ' . $alokasi->status . ' tidak diizinkan dihapus.',
                ], 422);
            }

            $nomorAlokasi = $alokasi->nomor_alokasi;

            if (auth()->check()) {
                $alokasi->deleted_by = auth()->id();
                $alokasi->save();
            }

            // Hapus rincian detail alokasi terlebih dahulu
            $alokasi->details()->delete();

            // Hapus dokumen alokasi biaya
            $alokasi->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Dokumen alokasi biaya {$nomorAlokasi} berhasil dihapus.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete AlokasiBiaya: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dokumen alokasi biaya: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Core Posting logic: updates StokGudang HPP, creates KartuStok entry, and generates Jurnal
     */
    protected function processPosting(AlokasiBiaya $alokasi)
    {
        $alokasi->load(['details.goodsReceipt', 'details.barang.kategori', 'poBiaya.supplier', 'poBiaya.tender', 'tender']);

        $isBranch        = !empty($alokasi->branch_id);
        $jurnalBranchId  = $isBranch ? $alokasi->branch_id : null;
        $jurnalCompanyId = $isBranch ? null : ($alokasi->company_id ?: 1);

        $coaCompanyId = $alokasi->company_id ?: (session('active_company_id') ?? auth()->user()?->company_id ?? ($isBranch ? \App\Models\Branch::where('id', $jurnalBranchId)->value('company_id') : 1));

        // 1. Resolve Accounts
        // Kredit: GRNI (Utang Belum Ditagih Jasa / Default GR Clearing / AP)
        $unbilledApCoaId = AccountMappingHelper::getCoaId('default', 'default_gr_clearing', null, $coaCompanyId)
            ?? AccountMappingHelper::getCoaId('default', 'default_ap', null, $coaCompanyId)
            ?? AccountMappingHelper::getCoaId('default', 'unbilled_ap', 'accrued_po', $coaCompanyId)
            ?? ChartOfAccount::where('company_id', $coaCompanyId)->detailOnly()->where(function ($q) {
                $q->where('code', 'like', '21%')->orWhere('type', 'like', '%Utang%')->orWhere('type', 'Liability');
            })->value('id');

        if (!$unbilledApCoaId) {
            $firstCoa = ChartOfAccount::where('company_id', $coaCompanyId)->detailOnly()->where('type', 'Liability')->first();
            $unbilledApCoaId = $firstCoa?->id;
        }

        if (!$unbilledApCoaId) {
            throw new \Exception("Akun Utang Belum Ditagih (GR Clearing / Unbilled AP) tidak ditemukan pada Chart of Account. Harap atur pemetaan akun akuntansi terlebih dahulu.");
        }

        // Default Inventory COA fallback
        $defaultInvCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory', null, $coaCompanyId)
            ?? ChartOfAccount::where('company_id', $coaCompanyId)->detailOnly()->where(function ($q) {
                $q->where('name', 'Persediaan')->orWhere('name', 'like', 'Persediaan Barang%')->orWhere('type', 'like', '%Persediaan%')->orWhere('type', 'Asset');
            })->value('id');

        if (!$defaultInvCoaId) {
            $defaultInvCoaId = ChartOfAccount::where('company_id', $coaCompanyId)->detailOnly()->where(function ($q) {
                $q->where('code', 'like', '14%')->orWhere('code', 'like', '11%');
            })->value('id');
        }

        $debitEntries = []; // coa_id => amount
        $totalAlokasiValue = 0;

        foreach ($alokasi->details as $detail) {
            $biayaAllocated = floatval($detail->biaya_dialokasikan);
            if ($biayaAllocated <= 0) continue;

            $totalAlokasiValue += $biayaAllocated;
            $gudangId = $detail->goodsReceipt?->gudang_id;

            // A. Update StokGudang (menambah nilai persediaan & update Moving Average Cost)
            if ($gudangId) {
                $stok = StokGudang::firstOrCreate(
                    ['barang_id' => $detail->barang_id, 'gudang_id' => $gudangId],
                    ['stok_aktual' => 0, 'nilai_persediaan' => 0, 'harga_pokok_rata_rata' => 0]
                );

                // Lock row agar serial dan mencegah dirty reads pada moving average cost
                $stok = StokGudang::where('id', $stok->id)->lockForUpdate()->first();

                $stokAktual = floatval($stok->stok_aktual);
                $nilaiAwal  = floatval($stok->nilai_persediaan);

                $stok->hpp_sebelumnya = $stok->harga_pokok_rata_rata;

                if ($stokAktual > 0) {
                    $nilaiBaru  = $nilaiAwal + $biayaAllocated;
                    $stok->nilai_persediaan = $nilaiBaru;
                    $stok->harga_pokok_rata_rata = $nilaiBaru / $stokAktual;
                } else {
                    // Jika stok fisik saat ini 0, perbarui nilai referensi moving average ke HPP baru tanpa meninggalkan ghost inventory valuation
                    $stok->harga_pokok_rata_rata = floatval($detail->hpp_baru);
                }
                $stok->save();

                // B. Catat di Kartu Stok
                KartuStok::create([
                    'barang_id'       => $detail->barang_id,
                    'gudang_id'       => $gudangId,
                    'tipe'            => 'Masuk',
                    'jumlah'          => 0, // Kuantitas fisik tidak bertambah
                    'stok_awal'       => $stokAktual,
                    'stok_akhir'      => $stokAktual,
                    'harga_satuan'    => $detail->biaya_per_unit,
                    'nilai_total'     => $biayaAllocated,
                    'referensi_jenis' => 'Alokasi Biaya',
                    'referensi_id'    => $alokasi->id,
                    'referensi_nomor' => $alokasi->nomor_alokasi,
                    'keterangan'      => "Alokasi Biaya Pengiriman (PO #{$alokasi->poBiaya?->nomor_po}) via {$alokasi->nomor_alokasi}",
                ]);
            }

            // C. Resolusi Akun Persediaan COA per Kategori Barang
            $kategoriId = $detail->barang?->kategori_barang_id;
            $invCoaId = null;
            if ($kategoriId) {
                $invCoaId = AccountMappingHelper::getCoaId('inventory_category', "cat_{$kategoriId}_inventory", 'default_inventory', $coaCompanyId);
            }
            if (!$invCoaId) {
                $invCoaId = $defaultInvCoaId;
            }

            if (!$invCoaId) {
                $namaBarang = $detail->barang?->nama_barang ?? "ID #{$detail->barang_id}";
                throw new \Exception("Akun COA Persediaan Barang untuk item '{$namaBarang}' tidak ditemukan. Harap pastikan akun persediaan pada kategori barang atau pemetaan akun default telah diatur.");
            }

            $debitEntries[$invCoaId] = ($debitEntries[$invCoaId] ?? 0) + $biayaAllocated;
        }

        if ($totalAlokasiValue <= 0) {
            return;
        }

        // D. Verifikasi Keseimbangan Jurnal Akuntansi (Debit == Kredit)
        $totalDebitCheck  = round(array_sum($debitEntries), 2);
        $totalKreditCheck = round($totalAlokasiValue, 2);
        if ($totalDebitCheck !== $totalKreditCheck) {
            throw new \Exception("Jurnal akuntansi tidak seimbang (Total Debit Rp {$totalDebitCheck} != Kredit Rp {$totalKreditCheck}). Proses posting dibatalkan demi keamanan pembukuan.");
        }

        // D. Buat Jurnal Akuntansi Otomatis
        $year   = date('Y', strtotime($alokasi->tanggal_alokasi));
        $month  = date('m', strtotime($alokasi->tanggal_alokasi));
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

        $vendorEkspedisi = $alokasi->poBiaya?->supplier?->nama_supplier ?? 'Ekspedisi';

        $tenderObj = $alokasi->tender ?? $alokasi->poBiaya?->tender;
        $nomorTender = null;
        if ($tenderObj) {
            $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                : ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id));
        }

        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $alokasi->tanggal_alokasi,
            'referensi_tipe' => 'Alokasi Biaya',
            'referensi_id'   => $alokasi->id,
            'no_tender'      => $nomorTender,
            'company_id'     => $jurnalCompanyId,
            'branch_id'      => $jurnalBranchId,
            'total_debit'    => $totalAlokasiValue,
            'total_kredit'   => $totalAlokasiValue,
            'keterangan'     => "Alokasi Biaya Pengiriman {$alokasi->nomor_alokasi} dari {$vendorEkspedisi} (PO #{$alokasi->poBiaya?->nomor_po})",
            'created_by'     => auth()->id(),
        ]);

        // Debit: Persediaan Barang (Nilai persediaan bertambah)
        foreach ($debitEntries as $coaId => $amount) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $coaId,
                'debit'      => $amount,
                'kredit'     => 0,
                'keterangan' => "Kapitalisasi Biaya Masuk Persediaan ({$alokasi->nomor_alokasi})",
            ]);
        }

        // Kredit: GRNI / Utang Belum Ditagih (Jasa Pengiriman)
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $unbilledApCoaId,
            'debit'      => 0,
            'kredit'     => $totalAlokasiValue,
            'keterangan' => "Utang Belum Ditagih - {$vendorEkspedisi} ({$alokasi->nomor_alokasi})",
        ]);
    }

    /**
     * Rollback logic when a Posted Alokasi Biaya is cancelled
     */
    protected function processRollbackPosting(AlokasiBiaya $alokasi)
    {
        $alokasi->loadMissing(['details.goodsReceipt']);

        foreach ($alokasi->details as $detail) {
            $biayaAllocated = floatval($detail->biaya_dialokasikan);
            if ($biayaAllocated <= 0) continue;

            $gudangId = $detail->goodsReceipt?->gudang_id;
            if ($gudangId) {
                $stok = StokGudang::where('barang_id', $detail->barang_id)
                    ->where('gudang_id', $gudangId)
                    ->lockForUpdate()
                    ->first();

                if ($stok) {
                    $stokAktual = floatval($stok->stok_aktual);
                    $nilaiAwal  = floatval($stok->nilai_persediaan);

                    $stok->hpp_sebelumnya = $stok->harga_pokok_rata_rata;

                    if ($stokAktual > 0) {
                        $nilaiBaru  = max(0, $nilaiAwal - $biayaAllocated);
                        $stok->nilai_persediaan = $nilaiBaru;
                        $stok->harga_pokok_rata_rata = $nilaiBaru / $stokAktual;
                    } else {
                        $stok->harga_pokok_rata_rata = floatval($detail->hpp_awal);
                    }
                    $stok->save();

                    // Catat jurnal pembalik di Kartu Stok
                    KartuStok::create([
                        'barang_id'       => $detail->barang_id,
                        'gudang_id'       => $gudangId,
                        'tipe'            => 'Keluar',
                        'jumlah'          => 0, // Kuantitas fisik tidak berkurang
                        'stok_awal'       => $stokAktual,
                        'stok_akhir'      => $stokAktual,
                        'harga_satuan'    => $detail->biaya_per_unit,
                        'nilai_total'     => $biayaAllocated,
                        'referensi_jenis' => 'Alokasi Biaya (Batal)',
                        'referensi_id'    => $alokasi->id,
                        'referensi_nomor' => $alokasi->nomor_alokasi,
                        'keterangan'      => "Pembatalan Kapitalisasi Biaya Ekspedisi ({$alokasi->nomor_alokasi})",
                    ]);
                }
            }
        }

        // Hapus Jurnal Akuntansi yang terkait
        $jurnals = Jurnal::where('referensi_tipe', 'Alokasi Biaya')
            ->where('referensi_id', $alokasi->id)
            ->get();

        foreach ($jurnals as $jurnal) {
            $jurnal->details()->delete();
            $jurnal->delete();
        }
    }
}
