<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\MasterGudang;
use App\Helpers\DecimalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoodsReceiptController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-gr');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        // Fetch Goods Receipts with company and branch relations
        $receipts = GoodsReceipt::forActiveCompany()
            ->forActiveBranch()
            ->with(['po.supplier', 'po.currency', 'po.tender', 'tender', 'gudang', 'creator', 'company', 'branch', 'details.lokasiRak'])
            ->orderBy('id', 'desc')
            ->take(1000)
            ->get();

        // Fetch active warehouses belonging to the active company & branch (if specified)
        $gudangQuery = MasterGudang::where('status', 'Active');
        if (!empty($activeBranchId)) {
            $gudangQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangQuery->whereNull('cabang_id');
            if ($activeCompanyId) {
                $gudangQuery->where('company_id', $activeCompanyId);
            }
        }
        $gudangs = $gudangQuery->orderBy('nama_gudang', 'asc')->get();

        // Fetch POs that can be received belonging to active company & branch
        $poQuery = PurchaseOrder::whereIn('status', ['Ordered', 'Partially Received', 'Approved', 'Disetujui', 'Confirmed'])
            ->forActiveCompany()
            ->forActiveBranch()
            ->with('supplier');

        $pos = $poQuery->orderBy('nomor_po', 'desc')->get();

        return view('modul_inventori.barang_masuk.viewreceipt', compact('receipts', 'gudangs', 'pos'));
    }

    public function getActivePos(Request $request)
    {
        $this->authorize('lihat-gr');

        $activeBranchId = session('active_branch_id');
        $search = $request->input('search');

        $query = PurchaseOrder::whereIn('status', ['Ordered', 'Approved', 'Disetujui', 'Partially Received', 'Confirmed'])
            ->forActiveCompany()
            ->forActiveBranch()
            ->with('supplier');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('nama_supplier', 'like', "%{$search}%");
                  });
            });
        }

        $pos = $query->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($po) {
                return [
                    'id' => $po->id,
                    'nomor_po' => $po->nomor_po,
                    'supplier_name' => $po->supplier ? $po->supplier->nama_supplier : '-',
                    'tanggal_po' => $po->tanggal_po ? $po->tanggal_po->format('d/m/Y') : '-',
                    'status' => $po->status,
                ];
            });

        return response()->json($pos);
    }

    public function getGudangs(Request $request)
    {
        $this->authorize('lihat-gr');

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $search          = $request->input('search');

        $query = MasterGudang::where('status', 'Active')
            ->with(['company', 'cabang']);

        if (!empty($activeBranchId)) {
            $query->where('cabang_id', $activeBranchId);
        } else {
            $query->whereNull('cabang_id');
            if ($activeCompanyId) {
                $query->where('company_id', $activeCompanyId);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_gudang', 'like', "%{$search}%")
                  ->orWhere('kode_gudang', 'like', "%{$search}%");
            });
        }

        $gudangs = $query->orderBy('nama_gudang', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($g) {
                return [
                    'id'           => $g->id,
                    'nama_gudang'  => $g->nama_gudang,
                    'kode_gudang'  => $g->kode_gudang ?? '-',
                    'company_name' => $g->company ? $g->company->nama_perusahaan : 'PT Pusat',
                    'branch_name'  => $g->cabang ? $g->cabang->nama_cabang : 'Pusat (Headquarter)',
                    'is_pusat'     => empty($g->cabang_id),
                ];
            });

        return response()->json($gudangs);
    }

    public function getLokasiRaks(MasterGudang $gudang)
    {
        $this->authorize('lihat-gr');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            if ($gudang->cabang_id != $activeBranchId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke gudang cabang ini.'], 403);
            }
        } else {
            if ($activeCompanyId && $gudang->company_id && $gudang->company_id != $activeCompanyId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke gudang perusahaan ini.'], 403);
            }
        }

        $raks = \App\Models\MasterLokasiRak::where('gudang_id', $gudang->id)
            ->where('status', 'Active')
            ->orderBy('kode_rak')
            ->get(['id', 'kode_rak', 'nama_rak', 'baris', 'kolom', 'level']);

        return response()->json([
            'gunakan_lokasi_rak' => (bool) $gudang->gunakan_lokasi_rak,
            'raks' => $raks
        ]);
    }

    public function getPoDetails(PurchaseOrder $po)
    {
        $this->authorize('lihat-gr');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            if ($po->branch_id != $activeBranchId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke PO cabang ini.'], 403);
            }
        } else {
            if ($activeCompanyId && $po->company_id && $po->company_id != $activeCompanyId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke PO perusahaan ini.'], 403);
            }
        }

        return response()->json($this->buildPoDetailsPayload($po));
    }

    protected function buildPoDetailsPayload(PurchaseOrder $po): array
    {
        $po->load([
            'details.barang.satuan',
            'details.barang.konversiSatuan.satuan',
            'details.barang.primaryFoto',
            'details.satuan',
            'currency',
            'supplier',
            'pajak',
            'pphPajak',
            'pphCoa',
            'tender'
        ]);

        $totalItem = $po->details->count();
        $totalQty = 0;
        $totalQtyDasar = 0;
        $totalReceivedSoFar = 0;
        $totalReceivedSoFarDasar = 0;

        $items = $po->details->map(function ($detail) use (&$totalReceivedSoFar, &$totalReceivedSoFarDasar, &$totalQty, &$totalQtyDasar, $po) {
            // PO Unit & Conversion Factor
            $poSatuanObj = $detail->satuan ?: ($detail->barang ? $detail->barang->satuan : null);
            $poSatuanNama = $poSatuanObj ? ($poSatuanObj->singkatan ?: ($poSatuanObj->kode_satuan ?: $poSatuanObj->nama_satuan)) : '-';
            $poFaktor = floatval($detail->faktor_pengali) > 0 ? floatval($detail->faktor_pengali) : 1.0;
            $qtyDipesanPO = floatval($detail->jumlah);
            $qtyDipesanDasar = floatval($detail->jumlah_dasar) > 0 ? floatval($detail->jumlah_dasar) : ($qtyDipesanPO * $poFaktor);

            // Base Unit (Satuan Dasar Gudang)
            $dasarSatuanObj = ($detail->barang && $detail->barang->satuan) ? $detail->barang->satuan : $poSatuanObj;
            $dasarSatuanNama = $dasarSatuanObj ? ($dasarSatuanObj->singkatan ?: ($dasarSatuanObj->kode_satuan ?: $dasarSatuanObj->nama_satuan)) : $poSatuanNama;

            // Total Received in Base Unit
            $receivedQtyDasar = GoodsReceiptDetail::where('po_detail_id', $detail->id)
                ->whereHas('goodsReceipt', function ($q) {
                    $q->where('status', 'Posted');
                })->sum(DB::raw('COALESCE(NULLIF(jumlah_dasar, 0), jumlah * COALESCE(faktor_pengali, 1))'));

            $remainingQtyDasar = max(0, $qtyDipesanDasar - $receivedQtyDasar);
            $totalReceivedSoFarDasar += $receivedQtyDasar;
            $totalQtyDasar += $qtyDipesanDasar;

            $receivedQtyPO = $poFaktor > 0 ? ($receivedQtyDasar / $poFaktor) : $receivedQtyDasar;
            $remainingQtyPO = $poFaktor > 0 ? ($remainingQtyDasar / $poFaktor) : $remainingQtyDasar;
            $totalQty += $qtyDipesanPO;
            $totalReceivedSoFar += $receivedQtyPO;

            $barangNama = $detail->barang ? $detail->barang->nama_barang : ($detail->nama_barang ?? 'Barang');
            $barangKode = $detail->barang ? $detail->barang->kode_barang : ($detail->kode_barang ?? '-');
            $tipeItem = $detail->barang ? $detail->barang->tipe_item : 'Persediaan';
            $fotoPath = ($detail->barang && $detail->barang->primaryFoto) ? $detail->barang->primaryFoto->foto_path : null;

            // Build Available Units List for Row Selector
            $availableUnits = [];
            // 1. Satuan PO (default)
            if ($poSatuanObj) {
                $availableUnits[$poSatuanObj->id] = [
                    'satuan_id'      => $poSatuanObj->id,
                    'nama_satuan'    => $poSatuanNama,
                    'faktor_pengali' => $poFaktor,
                    'is_po_unit'     => true,
                    'is_base_unit'   => ($dasarSatuanObj && $poSatuanObj->id === $dasarSatuanObj->id),
                    'label'          => $poFaktor > 1 
                        ? "{$poSatuanNama} (x" . rtrim(rtrim(number_format($poFaktor, 4, ',', '.'), '0'), ',') . ")" 
                        : "{$poSatuanNama} (PO)",
                ];
            }
            // 2. Satuan Dasar (if different from PO unit)
            if ($dasarSatuanObj && !isset($availableUnits[$dasarSatuanObj->id])) {
                $availableUnits[$dasarSatuanObj->id] = [
                    'satuan_id'      => $dasarSatuanObj->id,
                    'nama_satuan'    => $dasarSatuanNama,
                    'faktor_pengali' => 1.0,
                    'is_po_unit'     => false,
                    'is_base_unit'   => true,
                    'label'          => "{$dasarSatuanNama} (Dasar)",
                ];
            }
            // 3. Alternative conversion units from master_barang_konversi
            if ($detail->barang && $detail->barang->konversiSatuan) {
                foreach ($detail->barang->konversiSatuan as $konv) {
                    if ($konv->satuan && !isset($availableUnits[$konv->satuan->id])) {
                        $altNama = $konv->satuan->singkatan ?: ($konv->satuan->kode_satuan ?: $konv->satuan->nama_satuan);
                        $altFaktor = floatval($konv->faktor_pengali) > 0 ? floatval($konv->faktor_pengali) : 1.0;
                        $availableUnits[$konv->satuan->id] = [
                            'satuan_id'      => $konv->satuan->id,
                            'nama_satuan'    => $altNama,
                            'faktor_pengali' => $altFaktor,
                            'is_po_unit'     => false,
                            'is_base_unit'   => false,
                            'label'          => "{$altNama} (1 {$altNama} = " . rtrim(rtrim(number_format($altFaktor, 4, ',', '.'), '0'), ',') . " {$dasarSatuanNama})",
                        ];
                    }
                }
            }
            $availableUnits = array_values($availableUnits);

            // Calculate Effective Item Discount & PPN from PO (Per-Item)
            $hargaSatuan  = floatval($detail->harga_satuan);
            $diskonItem   = floatval($detail->diskon_item ?? 0);
            $diskonPersen = floatval($detail->diskon_persen ?? 0);
            $diskonTipe   = $detail->diskon_tipe ?? 'percent';

            if ($diskonPersen <= 0 && $diskonItem > 0 && $hargaSatuan > 0) {
                $diskonPersen = ($diskonItem / $hargaSatuan) * 100;
            }

            $tarifPpn = floatval($detail->tarif_ppn ?? 0);
            $ppnNominal = floatval($detail->ppn_nominal ?? 0);
            $ppnPajakId = $detail->ppn_pajak_id;
            $namaPpn = ($detail->pajak && $detail->pajak->nama_pajak) ? $detail->pajak->nama_pajak : ($tarifPpn > 0 ? 'PPN ' . $tarifPpn . '%' : 'Non PPN');

            return [
                'po_detail_id'        => $detail->id,
                'barang_id'           => $detail->barang_id,
                'kode_barang'         => $barangKode,
                'nama_barang'         => $barangNama,
                'tipe_item'           => $tipeItem,
                // Satuan PO info
                'po_satuan_id'        => $poSatuanObj ? $poSatuanObj->id : null,
                'po_satuan_nama'      => $poSatuanNama,
                'po_faktor_pengali'   => $poFaktor,
                // Satuan Dasar info
                'dasar_satuan_id'     => $dasarSatuanObj ? $dasarSatuanObj->id : null,
                'dasar_satuan_nama'   => $dasarSatuanNama,
                // Default selected satuan
                'default_satuan_id'   => $poSatuanObj ? $poSatuanObj->id : ($dasarSatuanObj ? $dasarSatuanObj->id : null),
                'satuan'              => $poSatuanNama,
                'available_units'     => $availableUnits,
                'foto_path'           => $fotoPath,
                // Quantities in PO unit and Base unit
                'qty_dipesan'         => $qtyDipesanPO,
                'qty_dipesan_dasar'   => $qtyDipesanDasar,
                'qty_diterima'        => $receivedQtyPO,
                'qty_diterima_dasar'  => $receivedQtyDasar,
                'qty_sisa'            => $remainingQtyPO,
                'qty_sisa_dasar'      => $remainingQtyDasar,
                'harga_satuan'        => $hargaSatuan,
                'harga_satuan_dasar'  => $poFaktor > 0 ? ($hargaSatuan / $poFaktor) : $hargaSatuan,
                'diskon_item'         => round($diskonItem, 2),
                'diskon_persen'       => round($diskonPersen, 2),
                'diskon_tipe'         => $diskonTipe,
                'tarif_ppn'           => $tarifPpn,
                'ppn_nominal'         => round($ppnNominal, 2),
                'ppn_pajak_id'        => $ppnPajakId,
                'nama_ppn'            => $namaPpn,
                'subtotal'            => floatval($detail->subtotal ?? 0),
            ];
        });

        $eta = $po->tanggal_pengiriman ? $po->tanggal_pengiriman->format('d/m/Y') : '-';
        $statusKeterlambatan = 'Ontime';
        if ($po->tanggal_pengiriman) {
            if (now()->startOfDay()->gt($po->tanggal_pengiriman->startOfDay()) && $po->status !== 'Closed') {
                $statusKeterlambatan = 'Terlambat';
            }
        }

        $statusPO = 'Belum Diterima';
        if ($totalReceivedSoFarDasar > 0) {
            if ($totalReceivedSoFarDasar >= ($totalQtyDasar - 0.0001)) {
                $statusPO = 'Selesai';
            } else {
                $statusPO = 'Sebagian (Partial)';
            }
        }

        $rates = [];
        if ($po->details) {
            foreach ($po->details as $d) {
                $r = floatval($d->tarif_ppn ?? 0);
                if ($r > 0) $rates[(string)$r] = true;
            }
        }
        $rateKeys = array_keys($rates);
        $summaryPajakTarif = 0;
        $summaryPajakNama = 'Non PPN';
        if (count($rateKeys) === 1) {
            $summaryPajakTarif = floatval($rateKeys[0]);
            $summaryPajakNama = 'PPN ' . $summaryPajakTarif . '%';
        } elseif (count($rateKeys) > 1) {
            $summaryPajakNama = 'PPN Campur';
        } elseif ($po->pajak && floatval($po->pajak->tarif) > 0) {
            $summaryPajakTarif = floatval($po->pajak->tarif);
            $summaryPajakNama = $po->pajak->nama_pajak;
        }

        $pphTarif = floatval($po->tarif_pph ?? 0);
        $pphNominal = floatval($po->pph_nominal ?? 0);
        $totalPph = floatval($po->total_pph ?? $pphNominal);
        $pphNama = $po->pphPajak ? $po->pphPajak->nama_pajak : ($pphTarif > 0 ? "PPh {$pphTarif}%" : 'Tanpa PPh');
        $pphCoaNama = $po->pphCoa ? ($po->pphCoa->nomor_akun . ' - ' . $po->pphCoa->nama_akun) : null;

        return [
            'po' => [
                'id' => $po->id,
                'nomor_po' => $po->nomor_po,
                'tanggal_po' => $po->tanggal_po ? $po->tanggal_po->format('d/m/Y') : '-',
                'supplier_name' => $po->supplier ? $po->supplier->nama_supplier : '-',
                'pic_supplier' => $po->supplier ? $po->supplier->nama_kontak : '-',
                'telepon_supplier' => $po->supplier ? $po->supplier->telepon : '-',
                'currency_symbol' => $po->currency ? $po->currency->simbol : 'Rp',
                'subtotal' => floatval($po->subtotal),
                'diskon_persen' => floatval($po->diskon_persen ?? 0),
                'diskon_nominal' => floatval($po->diskon_nominal ?? 0),
                'total_diskon' => floatval($po->total_diskon ?? 0),
                'pajak_nama' => $summaryPajakNama,
                'pajak_tarif' => $summaryPajakTarif,
                'total_pajak' => floatval($po->total_pajak ?? 0),
                'pph_pajak_id' => $po->pph_pajak_id,
                'pph_nama' => $pphNama,
                'pph_tarif' => $pphTarif,
                'pph_nominal' => $pphNominal,
                'total_pph' => $totalPph,
                'pph_coa_id' => $po->pph_coa_id,
                'pph_coa_nama' => $pphCoaNama,
                'down_payment' => floatval($po->down_payment ?? 0),
                'tender_name' => $po->tender ? $po->tender->nama_tender : ($po->manual_pr_number ?? '-'),
                'biaya_lain' => floatval($po->biaya_lain ?? 0),
                'deskripsi_biaya_lain' => $po->deskripsi_biaya_lain ?? 'Biaya Lain-lain',
                'grand_total' => floatval($po->grand_total),
                'kurs' => floatval($po->kurs ?? 1),
                'total_item' => $totalItem,
                'total_qty' => $totalQty,
                'total_received_so_far' => $totalReceivedSoFar,
                'eta' => $eta,
                'status_keterlambatan' => $statusKeterlambatan,
                'status_po' => $statusPO,
            ],
            'items' => $items
        ];
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-gr');

        Log::info('GR Store Request: ', $request->all());

        try {
            $request->validate([
                'tanggal_gr'        => 'required|date',
                'po_id'             => 'required|exists:purchase_orders,id',
                'gudang_id'         => 'required|exists:master_gudang,id',
                'nomor_surat_jalan' => 'nullable|string|max:100',
                'keterangan'        => 'nullable|string',
                'auto_number'       => 'required|boolean',
                'nomor_gr'             => 'nullable|required_if:auto_number,0|string|max:50',
                'items'                => 'required|array|min:1',
                'items.*.po_detail_id' => 'required|exists:purchase_order_details,id',
                'items.*.barang_id'    => 'required|exists:master_barang,id',
                'items.*.lokasi_rak_id'=> 'nullable|exists:master_lokasi_rak,id',
                'items.*.satuan_id'    => 'nullable|exists:master_satuan,id',
                'items.*.faktor_pengali' => 'nullable|numeric|min:0.0001',
                'items.*.jumlah'       => 'required|numeric|min:0',
                'items.*.jumlah_reject'=> 'nullable|numeric|min:0',
                'items.*.serials'      => 'nullable|array',
                'items.*.asset_codes'  => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => $e->errors()
            ], 422);
        }

        // Verify PO and Warehouse belong to active company and branch
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        $po = PurchaseOrder::where('id', $request->po_id)
            ->forActiveCompany()
            ->forActiveBranch()
            ->first();

        if (!$po) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => ['po_id' => ['Purchase Order tidak ditemukan atau tidak sesuai dengan PT/cabang aktif Anda!']]
            ], 422);
        }

        $gudangQuery = MasterGudang::where('id', $request->gudang_id)
            ->where('status', 'Active')
            ->forActiveBranch();

        if (empty($activeBranchId) && $activeCompanyId) {
            $gudangQuery->where('company_id', $activeCompanyId);
        }
        $gudang = $gudangQuery->first();

        if (!$gudang) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => ['gudang_id' => ['Gudang tidak ditemukan atau tidak sesuai dengan PT/cabang aktif Anda!']]
            ], 422);
        }

        // Validate GR number uniqueness if manual (nomor_gr has unique constraint in database)
        if (!$request->auto_number) {
            $exists = GoodsReceipt::withTrashed()->where('nomor_gr', $request->nomor_gr)->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors' => ['nomor_gr' => ['Nomor Penerimaan Barang tersebut sudah terdaftar!']]
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Validate items received quantity against PO limit (Thread-safe inside transaction with lock, in Base Unit)
            $requestedQtyDasarPerPoDetail = [];
            foreach ($request->items as $item) {
                $pId = $item['po_detail_id'];
                $faktor = floatval($item['faktor_pengali'] ?? 1.0);
                if ($faktor <= 0) $faktor = 1.0;
                $qtyDasar = floatval($item['jumlah'] ?? 0) * $faktor;
                $requestedQtyDasarPerPoDetail[$pId] = ($requestedQtyDasarPerPoDetail[$pId] ?? 0) + $qtyDasar;
            }

            foreach ($requestedQtyDasarPerPoDetail as $poDetailId => $totalRequestedDasar) {
                $poDetail = PurchaseOrderDetail::where('id', $poDetailId)->lockForUpdate()->first();
                if ($poDetail) {
                    $poFaktor = floatval($poDetail->faktor_pengali) > 0 ? floatval($poDetail->faktor_pengali) : 1.0;
                    $qtyDipesanDasar = floatval($poDetail->jumlah_dasar) > 0 
                        ? floatval($poDetail->jumlah_dasar) 
                        : (floatval($poDetail->jumlah) * $poFaktor);

                    $alreadyReceivedDasar = GoodsReceiptDetail::where('po_detail_id', $poDetailId)
                        ->whereHas('goodsReceipt', function($q) {
                            $q->where('status', 'Posted');
                        })->sum(DB::raw('COALESCE(NULLIF(jumlah_dasar, 0), jumlah * COALESCE(faktor_pengali, 1))'));

                    $remainingDasar = max(0, $qtyDipesanDasar - $alreadyReceivedDasar);

                    if ($totalRequestedDasar > ($remainingDasar + 0.0001)) {
                        DB::rollBack();
                        $namaBarang = ($poDetail->barang) ? $poDetail->barang->nama_barang : 'Barang';
                        $satuanDasarNama = ($poDetail->barang && $poDetail->barang->satuan) ? $poDetail->barang->satuan->nama_satuan : 'Satuan Dasar';
                        return response()->json([
                            'message' => "Total kuantitas barang diterima untuk {$namaBarang} melebihi sisa PO! (Sisa: " . rtrim(rtrim(number_format($remainingDasar, 4, ',', '.'), '0'), ',') . " {$satuanDasarNama})"
                        ], 422);
                    }
                }
            }

            // Auto generation of GR number (Thread-safe & collision proof)
            if ($request->auto_number) {
                $year = date('Y', strtotime($request->tanggal_gr));
                $month = date('m', strtotime($request->tanggal_gr));
                $prefix = "GR/{$year}/{$month}/";
                $prefixLen = strlen($prefix);

                $allGrs = GoodsReceipt::withTrashed()
                    ->where('nomor_gr', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->pluck('nomor_gr');

                $maxNum = 0;
                foreach ($allGrs as $codeStr) {
                    $suffix = substr($codeStr, $prefixLen);
                    if (is_numeric($suffix)) {
                        $val = (int) $suffix;
                        if ($val > $maxNum) {
                            $maxNum = $val;
                        }
                    }
                }

                $number = $maxNum + 1;
                $nomorGr = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

                while (GoodsReceipt::withTrashed()->where('nomor_gr', $nomorGr)->exists()) {
                    $number++;
                    $nomorGr = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
                }
            } else {
                $nomorGr = $request->nomor_gr;
            }

            // Create Header
            $isDraft = $request->boolean('is_draft');
            // Kurs: pakai kurs dari form (bisa beda dari PO), fallback ke kurs PO
            $kursGr = floatval($request->input('kurs', 0));
            if ($kursGr <= 0) $kursGr = floatval($po->kurs ?? 1);
            $targetBranchId  = !empty($activeBranchId) ? $activeBranchId : null;
            $targetCompanyId = !empty($targetBranchId) ? null : $activeCompanyId;

            $gr = GoodsReceipt::create([
                'company_id'        => $targetCompanyId,
                'branch_id'         => $targetBranchId,
                'nomor_gr'          => $nomorGr,
                'tanggal_gr'        => $request->tanggal_gr,
                'po_id'             => $request->po_id,
                'tender_id'         => $po->tender_id ?? null,
                'gudang_id'         => $request->gudang_id,
                'status'            => $isDraft ? 'Draft' : 'Posted',
                'nomor_surat_jalan' => $request->nomor_surat_jalan,
                'keterangan'        => $request->keterangan,
                'kurs'              => $kursGr,
            ]);

            // Save Details with Unit Conversion
            foreach ($request->items as $item) {
                $qtyToReceive = floatval($item['jumlah'] ?? 0);
                $qtyReject = floatval($item['jumlah_reject'] ?? 0);
                if ($qtyToReceive <= 0 && $qtyReject <= 0) continue;

                $satuanId = !empty($item['satuan_id']) ? intval($item['satuan_id']) : null;
                $faktor = floatval($item['faktor_pengali'] ?? 1.0);
                if ($faktor <= 0) $faktor = 1.0;

                $jumlahDasar = $qtyToReceive * $faktor;
                $jumlahDasarReject = $qtyReject * $faktor;

                GoodsReceiptDetail::create([
                    'gr_id'               => $gr->id,
                    'po_detail_id'        => $item['po_detail_id'],
                    'barang_id'           => $item['barang_id'],
                    'lokasi_rak_id'       => $item['lokasi_rak_id'] ?? null,
                    'satuan_id'           => $satuanId,
                    'faktor_pengali'      => $faktor,
                    'jumlah'              => $qtyToReceive,
                    'jumlah_dasar'        => $jumlahDasar,
                    'jumlah_reject'       => $qtyReject,
                    'jumlah_dasar_reject' => $jumlahDasarReject,
                ]);
            }

            if (!$isDraft) {
                $this->processPosting($gr, $request->items);
            }

            DB::commit();

            // Send WA notification to PO Creator asynchronously (non-blocking)
            if (!$isDraft) {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendGrPostedNotification($gr)
                );
            }

            $msg = $isDraft 
                ? 'Penerimaan barang berhasil disimpan sebagai Draft!' 
                : 'Penerimaan barang berhasil disimpan dan diposting!';

            return response()->json([
                'status' => 'success',
                'message' => $msg
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GR Store Exception: ', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->authorize('tambah-gr');

        $isAuthorizedAdmin = auth()->check() && (
            auth()->user()->hasRole('Super Admin') || 
            auth()->user()->hasRole('Superadmin') || 
            auth()->user()->hasRole('superadmin') ||
            auth()->user()->hasRole('Admin') || 
            auth()->user()->hasRole('admin') || 
            (property_exists(auth()->user(), 'role') && in_array(strtolower(strval(auth()->user()->role)), ['superadmin', 'admin']))
        );

        if ($goodsReceipt->status !== 'Draft') {
            return response()->json([
                'message' => 'Dokumen Penerimaan Barang yang telah diposting atau dibatalkan tidak dapat diedit langsung. Silakan gunakan fitur Batalkan Transaksi (Void) pada modal detail jika ingin merevisi!'
            ], 422);
        }

        // Verify Goods Receipt belongs to active company and branch
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        if (!empty($activeBranchId)) {
            if ($goodsReceipt->branch_id != $activeBranchId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data penerimaan barang cabang lain.'], 403);
            }
        } else {
            if ($activeCompanyId && $goodsReceipt->company_id && $goodsReceipt->company_id != $activeCompanyId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data penerimaan barang perusahaan lain.'], 403);
            }
        }

        try {
            $request->validate([
                'tanggal_gr'        => 'required|date',
                'po_id'             => 'required|exists:purchase_orders,id',
                'gudang_id'         => 'required|exists:master_gudang,id',
                'nomor_surat_jalan' => 'nullable|string|max:100',
                'keterangan'        => 'nullable|string',
                'auto_number'       => 'required|boolean',
                'nomor_gr'          => 'nullable|required_if:auto_number,0|string|max:50',
                'items'             => 'required|array|min:1',
                'items.*.po_detail_id' => 'required|exists:purchase_order_details,id',
                'items.*.barang_id'    => 'required|exists:master_barang,id',
                'items.*.lokasi_rak_id'=> 'nullable|exists:master_lokasi_rak,id',
                'items.*.satuan_id'    => 'nullable|exists:master_satuan,id',
                'items.*.faktor_pengali' => 'nullable|numeric|min:0.0001',
                'items.*.jumlah'       => 'required|numeric|min:0',
                'items.*.jumlah_reject'=> 'nullable|numeric|min:0',
                'items.*.serials'      => 'nullable|array',
                'items.*.asset_codes'  => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => $e->errors()
            ], 422);
        }

        // Verify PO and Warehouse belong to active company and branch
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;

        $po = PurchaseOrder::where('id', $request->po_id)
            ->forActiveCompany()
            ->forActiveBranch()
            ->first();

        if (!$po) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => ['po_id' => ['Purchase Order tidak ditemukan atau tidak sesuai dengan PT/cabang aktif Anda!']]
            ], 422);
        }

        $gudangQuery = MasterGudang::where('id', $request->gudang_id)
            ->where('status', 'Active')
            ->forActiveBranch();

        if (empty($activeBranchId) && $activeCompanyId) {
            $gudangQuery->where('company_id', $activeCompanyId);
        }
        $gudang = $gudangQuery->first();

        if (!$gudang) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => ['gudang_id' => ['Gudang tidak ditemukan atau tidak sesuai dengan PT/cabang aktif Anda!']]
            ], 422);
        }

        if (!$request->auto_number && $request->nomor_gr !== $goodsReceipt->nomor_gr) {
            $exists = GoodsReceipt::withTrashed()
                ->where('nomor_gr', $request->nomor_gr)
                ->where('id', '!=', $goodsReceipt->id)
                ->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors' => ['nomor_gr' => ['Nomor Penerimaan Barang tersebut sudah terdaftar!']]
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $requestedQtyDasarPerPoDetail = [];
            foreach ($request->items as $item) {
                $pId = $item['po_detail_id'];
                $faktor = floatval($item['faktor_pengali'] ?? 1.0);
                if ($faktor <= 0) $faktor = 1.0;
                $qtyDasar = floatval($item['jumlah'] ?? 0) * $faktor;
                $requestedQtyDasarPerPoDetail[$pId] = ($requestedQtyDasarPerPoDetail[$pId] ?? 0) + $qtyDasar;
            }

            foreach ($requestedQtyDasarPerPoDetail as $poDetailId => $totalRequestedDasar) {
                $poDetail = PurchaseOrderDetail::where('id', $poDetailId)->lockForUpdate()->first();
                if ($poDetail) {
                    $poFaktor = floatval($poDetail->faktor_pengali) > 0 ? floatval($poDetail->faktor_pengali) : 1.0;
                    $qtyDipesanDasar = floatval($poDetail->jumlah_dasar) > 0 
                        ? floatval($poDetail->jumlah_dasar) 
                        : (floatval($poDetail->jumlah) * $poFaktor);

                    $alreadyReceivedDasar = GoodsReceiptDetail::where('po_detail_id', $poDetailId)
                        ->whereHas('goodsReceipt', function($q) use ($goodsReceipt) {
                            $q->where('status', 'Posted')->where('id', '!=', $goodsReceipt->id);
                        })->sum(DB::raw('COALESCE(NULLIF(jumlah_dasar, 0), jumlah * COALESCE(faktor_pengali, 1))'));

                    $remainingDasar = max(0, $qtyDipesanDasar - $alreadyReceivedDasar);

                    if ($totalRequestedDasar > ($remainingDasar + 0.0001)) {
                        DB::rollBack();
                        $namaBarang = ($poDetail->barang) ? $poDetail->barang->nama_barang : 'Barang';
                        $satuanDasarNama = ($poDetail->barang && $poDetail->barang->satuan) ? $poDetail->barang->satuan->nama_satuan : 'Satuan Dasar';
                        return response()->json([
                            'message' => "Total kuantitas barang diterima untuk {$namaBarang} melebihi sisa PO! (Sisa: " . rtrim(rtrim(number_format($remainingDasar, 4, ',', '.'), '0'), ',') . " {$satuanDasarNama})"
                        ], 422);
                    }
                }
            }

            $wasPosted = ($goodsReceipt->status === 'Posted');
            if ($wasPosted) {
                $this->revertPosting($goodsReceipt);
            }

            $isDraft = $request->boolean('is_draft');

            // Kurs: pakai kurs dari form (bisa beda dari PO), fallback ke kurs PO
            $kursGr = floatval($request->input('kurs', 0));
            if ($kursGr <= 0) $kursGr = floatval($goodsReceipt->kurs ?? 0);
            if ($kursGr <= 0) $kursGr = floatval($po->kurs ?? 1);
            if ($kursGr <= 0) $kursGr = 1;

            $targetBranchId  = !empty($activeBranchId) ? $activeBranchId : null;
            $targetCompanyId = !empty($targetBranchId) ? null : $activeCompanyId;

            $goodsReceipt->update([
                'company_id'        => $targetCompanyId,
                'branch_id'         => $targetBranchId,
                'nomor_gr'          => $request->auto_number ? $goodsReceipt->nomor_gr : $request->nomor_gr,
                'tanggal_gr'        => $request->tanggal_gr,
                'po_id'             => $request->po_id,
                'tender_id'         => $po->tender_id ?? null,
                'gudang_id'         => $request->gudang_id,
                'status'            => $isDraft ? 'Draft' : 'Posted',
                'nomor_surat_jalan' => $request->nomor_surat_jalan,
                'keterangan'        => $request->keterangan,
                'kurs'              => $kursGr,
            ]);

            $goodsReceipt->details()->delete();

            foreach ($request->items as $item) {
                $qtyToReceive = floatval($item['jumlah'] ?? 0);
                $qtyReject = floatval($item['jumlah_reject'] ?? 0);
                if ($qtyToReceive <= 0 && $qtyReject <= 0) continue;

                $satuanId = !empty($item['satuan_id']) ? intval($item['satuan_id']) : null;
                $faktor = floatval($item['faktor_pengali'] ?? 1.0);
                if ($faktor <= 0) $faktor = 1.0;

                $jumlahDasar = $qtyToReceive * $faktor;
                $jumlahDasarReject = $qtyReject * $faktor;

                GoodsReceiptDetail::create([
                    'gr_id'               => $goodsReceipt->id,
                    'po_detail_id'        => $item['po_detail_id'],
                    'barang_id'           => $item['barang_id'],
                    'lokasi_rak_id'       => $item['lokasi_rak_id'] ?? null,
                    'satuan_id'           => $satuanId,
                    'faktor_pengali'      => $faktor,
                    'jumlah'              => $qtyToReceive,
                    'jumlah_dasar'        => $jumlahDasar,
                    'jumlah_reject'       => $qtyReject,
                    'jumlah_dasar_reject' => $jumlahDasarReject,
                ]);
            }

            if (!$isDraft) {
                $this->processPosting($goodsReceipt, $request->items);
            }

            DB::commit();

            // Send WA notification to PO Creator asynchronously (non-blocking)
            if (!$isDraft) {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendGrPostedNotification($goodsReceipt)
                );
            }

            $msg = $isDraft 
                ? 'Draft penerimaan barang berhasil diperbarui!' 
                : 'Penerimaan barang berhasil diperbarui dan diposting!';

            return response()->json([
                'status' => 'success',
                'message' => $msg
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GR Update Exception: ', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function post(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('tambah-gr');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            if ($goodsReceipt->branch_id != $activeBranchId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain.'], 403);
            }
        } else {
            if ($activeCompanyId && $goodsReceipt->company_id && $goodsReceipt->company_id != $activeCompanyId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
            }
        }

        if ($goodsReceipt->status !== 'Draft') {
            return response()->json([
                'message' => 'Hanya dokumen Penerimaan Barang dengan status Draft yang dapat diposting!'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Re-validate remaining PO quantity before posting Draft (Thread-safe inside transaction with lock)
            $goodsReceipt->load('details');
            foreach ($goodsReceipt->details as $grDetail) {
                $poDetail = PurchaseOrderDetail::where('id', $grDetail->po_detail_id)->lockForUpdate()->first();
                if ($poDetail) {
                    $poFaktor = floatval($poDetail->faktor_pengali) > 0 ? floatval($poDetail->faktor_pengali) : 1.0;
                    $qtyDipesanDasar = floatval($poDetail->jumlah_dasar) > 0 
                        ? floatval($poDetail->jumlah_dasar) 
                        : (floatval($poDetail->jumlah) * $poFaktor);

                    $alreadyReceivedDasar = GoodsReceiptDetail::where('po_detail_id', $grDetail->po_detail_id)
                        ->where('gr_id', '!=', $goodsReceipt->id)
                        ->whereHas('goodsReceipt', function($q) {
                            $q->where('status', 'Posted');
                        })->sum(DB::raw('COALESCE(NULLIF(jumlah_dasar, 0), jumlah * COALESCE(faktor_pengali, 1))'));

                    $remainingDasar = max(0, $qtyDipesanDasar - $alreadyReceivedDasar);
                    $grFaktor = floatval($grDetail->faktor_pengali) > 0 ? floatval($grDetail->faktor_pengali) : 1.0;
                    $qtyDasar = floatval($grDetail->jumlah_dasar) > 0 ? floatval($grDetail->jumlah_dasar) : (floatval($grDetail->jumlah) * $grFaktor);

                    if ($qtyDasar > ($remainingDasar + 0.0001)) {
                        DB::rollBack();
                        $namaBarang = ($poDetail->barang) ? $poDetail->barang->nama_barang : 'Barang';
                        $satuanDasarNama = ($poDetail->barang && $poDetail->barang->satuan) ? $poDetail->barang->satuan->nama_satuan : 'Satuan Dasar';
                        return response()->json([
                            'message' => "Posting gagal! Kuantitas barang '{$namaBarang}' pada dokumen ini ({$qtyDasar} {$satuanDasarNama}) melebihi sisa PO yang belum diterima (Sisa: " . rtrim(rtrim(number_format($remainingDasar, 4, ',', '.'), '0'), ',') . " {$satuanDasarNama})!"
                        ], 422);
                    }
                }
            }

            $this->processPosting($goodsReceipt);

            DB::commit();

            // Send WA notification to PO Creator asynchronously (non-blocking)
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendGrPostedNotification($goodsReceipt)
            );

            return response()->json([
                'status' => 'success',
                'message' => "Penerimaan barang {$goodsReceipt->nomor_gr} berhasil diposting ke stok & PO!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GR Post Exception: ', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function processPosting(GoodsReceipt $gr, array $itemsData = [])
    {
        $gr->load(['details.barang.satuan', 'details.satuan']);
        
        $itemsMap = [];
        foreach ($itemsData as $it) {
            if (isset($it['po_detail_id'])) {
                $itemsMap[$it['po_detail_id']] = $it;
            }
        }

        foreach ($gr->details as $grDetail) {
            $grFaktor = floatval($grDetail->faktor_pengali) > 0 ? floatval($grDetail->faktor_pengali) : 1.0;
            $qtyDasar = floatval($grDetail->jumlah_dasar) > 0 ? floatval($grDetail->jumlah_dasar) : (floatval($grDetail->jumlah) * $grFaktor);
            if ($qtyDasar <= 0) continue;

            $barangModel = $grDetail->barang ?: \App\Models\MasterBarang::find($grDetail->barang_id);
            $tipeItem = $barangModel ? $barangModel->tipe_item : 'Stok';

            if ($tipeItem === 'Stok') {
                $stokGudang = StokGudang::firstOrCreate(
                    ['barang_id' => $grDetail->barang_id, 'gudang_id' => $gr->gudang_id],
                    ['stok_aktual' => 0.00, 'harga_pokok_rata_rata' => 0.00]
                );

                $stokGudang = StokGudang::where('id', $stokGudang->id)->lockForUpdate()->first();

                $stokAwal = floatval($stokGudang->stok_aktual);
                $currentAvgCost = floatval($stokGudang->harga_pokok_rata_rata);
                $stokAkhir = $stokAwal + $qtyDasar;

                $poDetailForPrice = PurchaseOrderDetail::with('po')->find($grDetail->po_detail_id);
                if ($poDetailForPrice) {
                    $hargaSatuan = floatval($poDetailForPrice->harga_satuan);
                    $diskonItem = floatval($poDetailForPrice->diskon_item);
                    $netPrice = max(0, $hargaSatuan - $diskonItem);

                    $po = $poDetailForPrice->po;
                    // Gunakan kurs GR (saat penerimaan), bukan kurs PO (saat order)
                    $kurs = floatval($gr->kurs ?? 0);
                    if ($kurs <= 0) $kurs = $po ? floatval($po->kurs) : 1;
                    if ($kurs <= 0) $kurs = 1;

                    if ($po) {
                        $alokasiBiayaPerUnit = $this->calculateItemHppAllocation($po, $poDetailForPrice);
                        $netPrice += $alokasiBiayaPerUnit;
                    }

                    // Price per PO unit in IDR
                    $purchasePricePO = $netPrice * $kurs;
                    // Factor of PO unit
                    $poFaktor = floatval($poDetailForPrice->faktor_pengali) > 0 ? floatval($poDetailForPrice->faktor_pengali) : 1.0;
                    // Price per Satuan Dasar in IDR
                    $purchasePrice = $poFaktor > 0 ? ($purchasePricePO / $poFaktor) : $purchasePricePO;
                } else {
                    $purchasePrice = floatval($grDetail->harga_satuan ?? ($barangModel ? $barangModel->harga_pokok_rata_rata : 0));
                }

                $stokAwalStr = (string)($stokGudang->stok_aktual ?? '0');
                $nilaiPersediaanLamaStr = (string)($stokGudang->nilai_persediaan ?? DecimalHelper::mul($stokAwalStr, (string)($stokGudang->harga_pokok_rata_rata ?? '0'), 4));
                $qtyDasarStr = (string)$qtyDasar;
                $purchasePriceStr = (string)$purchasePrice;

                // 1. Calculate purchase addition to total inventory value
                $nilaiPembelianBaruStr = DecimalHelper::mul($qtyDasarStr, $purchasePriceStr, 4);

                // 2. SOURCE OF TRUTH: Update Total Nilai Persediaan directly
                $nilaiPersediaanBaruStr = DecimalHelper::add($nilaiPersediaanLamaStr, $nilaiPembelianBaruStr, 4);

                // 3. Update Total Stok
                $stokAkhirStr = DecimalHelper::add($stokAwalStr, $qtyDasarStr, 4);

                // 4. DERIVED: HPP = Nilai Persediaan ÷ Total Stok
                $newAvgCostStr = DecimalHelper::comp($stokAkhirStr, '0', 4) > 0 
                    ? DecimalHelper::div($nilaiPersediaanBaruStr, $stokAkhirStr, 6) 
                    : $purchasePriceStr;

                $stokGudang->stok_aktual = $stokAkhirStr;
                $stokGudang->nilai_persediaan = $nilaiPersediaanBaruStr;
                $stokGudang->hpp_sebelumnya = (string)($stokGudang->harga_pokok_rata_rata ?? '0.000000');
                $stokGudang->harga_pokok_rata_rata = $newAvgCostStr;
                $stokGudang->harga_beli_sebelumnya = (string)($stokGudang->harga_beli_terakhir ?? '0.000000');
                $stokGudang->harga_beli_terakhir = $purchasePriceStr;
                $stokGudang->save();

                StokGudang::syncMasterHpp($grDetail->barang_id);

                $satuanNota = $grDetail->satuan ? $grDetail->satuan->nama_satuan : '';
                $satuanDasarNama = ($barangModel && $barangModel->satuan) ? $barangModel->satuan->nama_satuan : '';
                $ketConversion = ($grFaktor > 1 && $satuanNota) 
                    ? " ({$grDetail->jumlah} {$satuanNota} = {$qtyDasar} {$satuanDasarNama})"
                    : "";

                KartuStok::create([
                    'barang_id'       => $grDetail->barang_id,
                    'gudang_id'       => $gr->gudang_id,
                    'tipe'            => 'Masuk',
                    'jumlah'          => $qtyDasar,
                    'stok_awal'       => $stokAwal,
                    'stok_akhir'      => $stokAkhir,
                    'harga_satuan'    => $purchasePrice,
                    'nilai_total'     => $qtyDasar * $purchasePrice,
                    'referensi_jenis' => 'Goods Receipt',
                    'referensi_id'    => $gr->id,
                    'referensi_nomor' => $gr->nomor_gr,
                    'keterangan'      => "Penerimaan PO #{$gr->po->nomor_po}{$ketConversion}",
                ]);
            } elseif ($tipeItem === 'Aset') {
                $itemPayload = $itemsMap[$grDetail->po_detail_id] ?? [];
                $serials = isset($itemPayload['serials']) ? $itemPayload['serials'] : [];
                $customCodes = isset($itemPayload['asset_codes']) ? $itemPayload['asset_codes'] : [];
                $poDetail = PurchaseOrderDetail::with('po')->find($grDetail->po_detail_id);
                $purchasePrice = 0;
                if ($poDetail) {
                    $hargaSatuan = floatval($poDetail->harga_satuan);
                    $diskonItem = floatval($poDetail->diskon_item ?? 0);
                    $netPrice = max(0, $hargaSatuan - $diskonItem);

                    $po = $poDetail->po;
                    $kurs = floatval($gr->kurs ?? 0);
                    if ($kurs <= 0) $kurs = $po ? floatval($po->kurs) : 1;
                    if ($kurs <= 0) $kurs = 1;

                    if ($po) {
                        $alokasiBiayaPerUnit = $this->calculateItemHppAllocation($po, $poDetail);
                        $netPrice += $alokasiBiayaPerUnit;
                    }

                    $poFaktor = floatval($poDetail->faktor_pengali) > 0 ? floatval($poDetail->faktor_pengali) : 1.0;
                    $purchasePrice = $poFaktor > 0 ? (($netPrice * $kurs) / $poFaktor) : ($netPrice * $kurs);
                } else {
                    $purchasePrice = floatval($grDetail->harga_satuan ?? 0);
                }

                $countAsset = intval(round($qtyDasar));
                for ($i = 0; $i < $countAsset; $i++) {
                    $serialNum = isset($serials[$i]) ? trim($serials[$i]) : null;
                    $customCode = isset($customCodes[$i]) ? trim($customCodes[$i]) : null;

                    if (empty($customCode)) {
                        $year = date('Y', strtotime($gr->tanggal_gr));
                        $month = date('m', strtotime($gr->tanggal_gr));
                        $prefix = "AST/{$year}/{$month}/";
                        
                        $latestAsset = \App\Models\FixedAsset::where('kode_aset', 'like', $prefix . '%')
                            ->orderBy('kode_aset', 'desc')
                            ->first();
                        
                        $num = !$latestAsset ? 1 : ((int) substr($latestAsset->kode_aset, strlen($prefix))) + 1;
                        $customCode = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
                    }

                    \App\Models\FixedAsset::create([
                        'barang_id'           => $grDetail->barang_id,
                        'gr_detail_id'        => $grDetail->id,
                        'kode_aset'           => $customCode,
                        'nomor_seri'          => $serialNum,
                        'tanggal_perolehan'   => $gr->tanggal_gr,
                        'harga_perolehan'     => $purchasePrice,
                        'masa_manfaat'        => $barangModel->default_masa_manfaat ?? 0,
                        'nilai_residu'        => $barangModel->default_nilai_residu ?? 0,
                        'nilai_buku_saat_ini' => $purchasePrice,
                        'status'              => 'Aktif',
                    ]);
                }
            }
        }

        // Recalculate and update PO status (only considering Posted Goods Receipts, in Base Unit)
        $po = PurchaseOrder::where('id', $gr->po_id)->lockForUpdate()->first();
        if ($po) {
            $po->load('details');
            
            $receivedQuantitiesDasar = GoodsReceiptDetail::whereIn('po_detail_id', $po->details->pluck('id'))
                ->whereHas('goodsReceipt', function($q) {
                    $q->where('status', 'Posted');
                })
                ->groupBy('po_detail_id')
                ->select('po_detail_id', DB::raw('SUM(COALESCE(NULLIF(jumlah_dasar, 0), jumlah * COALESCE(faktor_pengali, 1))) as total_received_dasar'))
                ->pluck('total_received_dasar', 'po_detail_id');

            $isFullyReceived = true;
            $hasSomeReceived = false;

            foreach ($po->details as $poDetail) {
                $poFaktor = floatval($poDetail->faktor_pengali) > 0 ? floatval($poDetail->faktor_pengali) : 1.0;
                $qtyDipesanDasar = floatval($poDetail->jumlah_dasar) > 0 
                    ? floatval($poDetail->jumlah_dasar) 
                    : (floatval($poDetail->jumlah) * $poFaktor);

                $totalRec = floatval($receivedQuantitiesDasar[$poDetail->id] ?? 0);
                if ($totalRec < ($qtyDipesanDasar - 0.0001)) {
                    $isFullyReceived = false;
                }
                if ($totalRec > 0) {
                    $hasSomeReceived = true;
                }
            }

            if ($isFullyReceived) {
                $po->status = 'Received';
            } elseif ($hasSomeReceived) {
                $po->status = 'Partially Received';
            } else {
                $po->status = 'Ordered';
            }
            $po->save();
        }

        $gr->status = 'Posted';
        $gr->save();

        // Automatic General Ledger Journal Entry Generation
        $this->createJournalForPosting($gr);
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('lihat-gr');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            if ($goodsReceipt->branch_id != $activeBranchId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain.'], 403);
            }
        } else {
            if ($activeCompanyId && $goodsReceipt->company_id && $goodsReceipt->company_id != $activeCompanyId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
            }
        }

        $goodsReceipt->load([
            'company',
            'branch',
            'tender',
            'po.company',
            'po.supplier',
            'po.currency',
            'po.branch',
            'po.pajak',
            'po.pphPajak',
            'po.pphCoa',
            'po.tender',
            'gudang',
            'creator',
            'canceler',
            'details.barang.satuan',
            'details.barang.konversiSatuan.satuan',
            'details.satuan',
            'details.poDetail.satuan',
            'details.barang.primaryFoto',
            'details.poDetail.pajak',
            'details.poDetail',
            'details.lokasiRak',
            'jurnal.details.account',
        ]);

        $raks = [];
        if ($goodsReceipt->gudang_id) {
            $raks = \App\Models\MasterLokasiRak::where('gudang_id', $goodsReceipt->gudang_id)
                ->where('status', 'Active')
                ->orderBy('kode_rak')
                ->get(['id', 'kode_rak', 'nama_rak', 'baris', 'kolom', 'level']);
        }

        $poDetailsPayload = null;
        if ($goodsReceipt->po) {
            $poDetailsPayload = $this->buildPoDetailsPayload($goodsReceipt->po);
        }

        $isAuthorizedAdmin = auth()->check() && (
            auth()->user()->hasRole('Super Admin') || 
            auth()->user()->hasRole('Superadmin') || 
            auth()->user()->hasRole('superadmin') ||
            auth()->user()->hasRole('Admin') || 
            auth()->user()->hasRole('admin') || 
            (property_exists(auth()->user(), 'role') && in_array(strtolower(strval(auth()->user()->role)), ['superadmin', 'admin']))
        );

        return response()->json([
            'gr' => $goodsReceipt,
            'po_details' => $poDetailsPayload,
            'raks' => $raks,
            'is_super_admin' => $isAuthorizedAdmin,
        ]);
    }

    /**
     * Update hanya field kurs di GR — bisa untuk Draft maupun Posted.
     * Jika GR sudah Posted: revert posting lama → simpan kurs baru → repost dengan kurs baru.
     */
    public function updateKurs(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->authorize('tambah-gr');

        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeBranchId)) {
            if ($goodsReceipt->branch_id != $activeBranchId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain.'], 403);
            }
        } else {
            if ($activeCompanyId && $goodsReceipt->company_id && $goodsReceipt->company_id != $activeCompanyId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
            }
        }

        $request->validate([
            'kurs' => 'required|numeric|min:0.0001',
        ]);

        $kursBaru = floatval($request->kurs);

        try {
            DB::beginTransaction();

            $wasPosted = ($goodsReceipt->status === 'Posted');

            if ($wasPosted) {
                // Revert posting lama (stok & jurnal) pakai kurs lama
                $this->revertPosting($goodsReceipt);
            }

            // Simpan kurs baru
            $goodsReceipt->update(['kurs' => $kursBaru]);

            if ($wasPosted) {
                // Reload GR agar kurs baru terbaca di processPosting
                $goodsReceipt->refresh();
                $this->processPosting($goodsReceipt);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Kurs penerimaan barang berhasil diperbarui' . ($wasPosted ? ' dan stok / jurnal telah dihitung ulang.' : '.'),
                'kurs'    => $kursBaru,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GR UpdateKurs Exception: ', ['msg' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Batalkan Transaksi Penerimaan Barang (Void)
     */
    public function cancel(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->authorize('hapus-gr');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            if ($goodsReceipt->branch_id != $activeBranchId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain.'], 403);
            }
        } else {
            if ($activeCompanyId && $goodsReceipt->company_id && $goodsReceipt->company_id != $activeCompanyId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
            }
        }

        $isAuthorizedAdmin = auth()->check() && (
            auth()->user()->hasRole('Super Admin') || 
            auth()->user()->hasRole('Superadmin') || 
            auth()->user()->hasRole('superadmin') ||
            auth()->user()->hasRole('Admin') || 
            auth()->user()->hasRole('admin') || 
            (property_exists(auth()->user(), 'role') && in_array(strtolower(strval(auth()->user()->role)), ['superadmin', 'admin']))
        );

        if (!$isAuthorizedAdmin) {
            return response()->json([
                'message' => 'Pembatalan transaksi penerimaan barang hanya dapat dilakukan oleh Admin / Super Admin!'
            ], 403);
        }

        if ($goodsReceipt->status === 'Dibatalkan') {
            return response()->json([
                'message' => 'Dokumen penerimaan barang ini sudah dibatalkan sebelumnya!'
            ], 400);
        }

        $request->validate([
            'alasan_pembatalan' => 'required|string|min:5|max:40',
        ], [
            'alasan_pembatalan.required' => 'Alasan pembatalan wajib diisi.',
            'alasan_pembatalan.min'      => 'Alasan pembatalan minimal 5 karakter.',
            'alasan_pembatalan.max'      => 'Alasan pembatalan maksimal 40 karakter.',
        ]);

        $alasanPembatalan = trim(strip_tags($request->input('alasan_pembatalan')));

        // Integrity guard 1: Check if already billed in Purchase Invoice
        $activePi = \App\Models\PurchaseInvoice::where('gr_id', $goodsReceipt->id)
            ->where(function($q) {
                $q->whereNull('status')->orWhereNotIn('status', ['Dibatalkan', 'Rejected', 'Ditolak']);
            })->first();

        if ($activePi) {
            return response()->json([
                'message' => "Penerimaan barang tidak dapat dibatalkan karena sudah ditagihkan pada Faktur Pembelian (No. {$activePi->nomor_invoice})! Batalkan faktur pembelian terlebih dahulu."
            ], 422);
        }

        // Integrity guard 2: Check if linked to Alokasi Biaya
        $activeAbDetail = \App\Models\AlokasiBiayaDetail::where('gr_id', $goodsReceipt->id)
            ->whereHas('alokasiBiaya', function($q) {
                $q->whereNull('status')->orWhereNotIn('status', ['Dibatalkan', 'Ditolak', 'Batal']);
            })->with('alokasiBiaya')->first();

        if ($activeAbDetail && $activeAbDetail->alokasiBiaya) {
            $nomorAlokasi = $activeAbDetail->alokasiBiaya->nomor_alokasi ?? ('#' . $activeAbDetail->alokasi_biaya_id);
            return response()->json([
                'message' => "Penerimaan barang tidak dapat dibatalkan karena terhubung dengan Alokasi Biaya (No. {$nomorAlokasi})! Batalkan alokasi biaya terlebih dahulu."
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Concurrency Guard: Lock GoodsReceipt row to guarantee concurrency safety and prevent double-cancel
            $lockedGr = GoodsReceipt::where('id', $goodsReceipt->id)->lockForUpdate()->firstOrFail();
            if ($lockedGr->status === 'Dibatalkan') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Dokumen penerimaan barang ini sudah dibatalkan sebelumnya!'
                ], 400);
            }

            if ($lockedGr->status !== 'Posted') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Hanya dokumen berstatus Diposting yang dapat dibatalkan (void)!'
                ], 400);
            }

            // Revert stock & void journal
            $this->revertPosting($lockedGr, $alasanPembatalan);

            // Update status to Dibatalkan with cancellation audit details
            $lockedGr->update([
                'status'            => 'Dibatalkan',
                'alasan_pembatalan' => $alasanPembatalan,
                'canceled_by'       => auth()->id(),
                'canceled_at'       => now(),
            ]);

            // Recalculate PO status
            $poId = $lockedGr->po_id;
            $po = PurchaseOrder::where('id', $poId)->lockForUpdate()->first();
            if ($po) {
                $po->load('details');
                
                $receivedQuantitiesDasar = GoodsReceiptDetail::whereIn('po_detail_id', $po->details->pluck('id'))
                    ->whereHas('goodsReceipt', function($q) {
                        $q->where('status', 'Posted');
                    })
                    ->groupBy('po_detail_id')
                    ->select('po_detail_id', DB::raw('SUM(COALESCE(NULLIF(jumlah_dasar, 0), jumlah * COALESCE(faktor_pengali, 1))) as total_received_dasar'))
                    ->pluck('total_received_dasar', 'po_detail_id');

                $isFullyReceived = true;
                $hasSomeReceived = false;

                foreach ($po->details as $poDetail) {
                    $poFaktor = floatval($poDetail->faktor_pengali) > 0 ? floatval($poDetail->faktor_pengali) : 1.0;
                    $qtyDipesanDasar = floatval($poDetail->jumlah_dasar) > 0 
                        ? floatval($poDetail->jumlah_dasar) 
                        : (floatval($poDetail->jumlah) * $poFaktor);

                    $totalRec = floatval($receivedQuantitiesDasar[$poDetail->id] ?? 0);
                    if ($totalRec < ($qtyDipesanDasar - 0.0001)) {
                        $isFullyReceived = false;
                    }
                    if ($totalRec > 0) {
                        $hasSomeReceived = true;
                    }
                }

                if ($isFullyReceived && $hasSomeReceived) {
                    $po->status = 'Received';
                } elseif ($hasSomeReceived) {
                    $po->status = 'Partially Received';
                } else {
                    $po->status = 'Ordered';
                }
                $po->save();
            }

            DB::commit();

            Log::info("Goods Receipt {$lockedGr->nomor_gr} (ID: {$lockedGr->id}) voided by User ID: " . auth()->id() . " Reason: {$alasanPembatalan}");

            return response()->json([
                'status'  => 'success',
                'message' => "Penerimaan barang {$lockedGr->nomor_gr} berhasil dibatalkan dan stok telah disesuaikan kembali!"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GR Cancel Exception: ', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal membatalkan penerimaan barang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('hapus-gr');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            if ($goodsReceipt->branch_id != $activeBranchId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain.'], 403);
            }
        } else {
            if ($activeCompanyId && $goodsReceipt->company_id && $goodsReceipt->company_id != $activeCompanyId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
            }
        }

        $isAuthorizedAdmin = auth()->check() && (
            auth()->user()->hasRole('Super Admin') || 
            auth()->user()->hasRole('Superadmin') || 
            auth()->user()->hasRole('superadmin') ||
            auth()->user()->hasRole('Admin') || 
            auth()->user()->hasRole('admin') || 
            (property_exists(auth()->user(), 'role') && in_array(strtolower(strval(auth()->user()->role)), ['superadmin', 'admin']))
        );

        if ($goodsReceipt->status !== 'Draft') {
            return response()->json([
                'message' => 'Hanya dokumen Penerimaan Barang berstatus Draft yang dapat dihapus. Dokumen yang telah diposting atau dibatalkan disimpan sebagai arsip riwayat audit transaksi.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $poId = $goodsReceipt->po_id;
            $gudang = $goodsReceipt->gudang;

            if ($goodsReceipt->status === 'Draft') {
                // Draft can be safely deleted without inventory reversal
                $goodsReceipt->details()->delete();
                $goodsReceipt->delete();
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => "Draft Penerimaan Barang {$goodsReceipt->nomor_gr} berhasil dihapus!"
                ]);
            }

            $this->revertPosting($goodsReceipt);

            // Force delete details and soft-delete header
            $goodsReceipt->details()->delete();
            $goodsReceipt->delete();

            // Recalculate PO status (optimized query & pessimistic locking)
            $po = PurchaseOrder::where('id', $poId)->lockForUpdate()->first();
            if ($po) {
                $po->load('details');
                
                // Get all received quantities for these details in one database query (in Base Unit)
                $receivedQuantitiesDasar = GoodsReceiptDetail::whereIn('po_detail_id', $po->details->pluck('id'))
                    ->whereHas('goodsReceipt', function($q) {
                        $q->where('status', 'Posted');
                    })
                    ->groupBy('po_detail_id')
                    ->select('po_detail_id', DB::raw('SUM(COALESCE(NULLIF(jumlah_dasar, 0), jumlah * COALESCE(faktor_pengali, 1))) as total_received_dasar'))
                    ->pluck('total_received_dasar', 'po_detail_id');

                $isFullyReceived = true;
                $hasSomeReceived = false;

                foreach ($po->details as $poDetail) {
                    $poFaktor = floatval($poDetail->faktor_pengali) > 0 ? floatval($poDetail->faktor_pengali) : 1.0;
                    $qtyDipesanDasar = floatval($poDetail->jumlah_dasar) > 0 
                        ? floatval($poDetail->jumlah_dasar) 
                        : (floatval($poDetail->jumlah) * $poFaktor);

                    $totalRec = floatval($receivedQuantitiesDasar[$poDetail->id] ?? 0);
                    if ($totalRec < ($qtyDipesanDasar - 0.0001)) {
                        $isFullyReceived = false;
                    }
                    if ($totalRec > 0) {
                        $hasSomeReceived = true;
                    }
                }

                if ($isFullyReceived) {
                    $po->status = 'Received';
                } elseif ($hasSomeReceived) {
                    $po->status = 'Partially Received';
                } else {
                    $po->status = 'Ordered';
                }
                $po->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penerimaan barang berhasil dihapus!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GR Destroy Exception: ', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal menghapus penerimaan barang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function print(GoodsReceipt $goodsReceipt)
    {
        $this->authorize('lihat-gr');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            if ($goodsReceipt->branch_id != $activeBranchId) {
                abort(403, 'Anda tidak memiliki akses ke data cabang lain.');
            }
        } else {
            if ($activeCompanyId && $goodsReceipt->company_id && $goodsReceipt->company_id != $activeCompanyId) {
                abort(403, 'Anda tidak memiliki akses ke data perusahaan lain.');
            }
        }

        $goodsReceipt->load([
            'company',
            'branch',
            'tender',
            'po.company',
            'po.supplier',
            'po.branch',
            'po.currency',
            'po.pajak',
            'po.pphPajak',
            'po.pphCoa',
            'po.tender',
            'gudang',
            'creator.activeSignature',
            'canceler',
            'details.barang.satuan',
            'details.barang.primaryFoto',
            'details.lokasiRak',
            'details.fixedAssets',
            'details.poDetail'
        ]);

        $activeBranch = \App\Models\Branch::find($activeBranchId);

        return view('modul_inventori.barang_masuk.printgr', compact('goodsReceipt', 'activeBranch'));
    }

    protected function revertPosting(GoodsReceipt $goodsReceipt, ?string $alasan = null)
    {
        $gudang = $goodsReceipt->gudang;
        if (!$gudang) return;

        // Validation: Check negative stock policy for all items before making any changes
        if (!$gudang->izinkan_stok_negatif) {
            foreach ($goodsReceipt->details as $detail) {
                $detailFaktor = floatval($detail->faktor_pengali) > 0 ? floatval($detail->faktor_pengali) : 1.0;
                $qtyDasar = floatval($detail->jumlah_dasar) > 0 ? floatval($detail->jumlah_dasar) : (floatval($detail->jumlah) * $detailFaktor);

                $stokGudang = StokGudang::where('barang_id', $detail->barang_id)
                    ->where('gudang_id', $goodsReceipt->gudang_id)
                    ->lockForUpdate()
                    ->first();
                $stokAktual = $stokGudang ? floatval($stokGudang->stok_aktual) : 0;
                // Toleransi perbedaan pembulatan desimal (0.05) agar tidak false alarm selisih mikro
                if (($stokAktual + 0.05) < $qtyDasar) {
                    $barang = $detail->barang;
                    $namaBarang = $barang ? $barang->nama_barang : "ID #{$detail->barang_id}";
                    $satuanNama = ($barang && $barang->satuan) ? $barang->satuan->nama_satuan : 'satuan';
                    throw new \Exception("Penerimaan barang tidak dapat diubah/dihapus karena stok '{$namaBarang}' di gudang '{$gudang->nama_gudang}' tidak mencukupi untuk dikurangi! Stok saat ini: {$stokAktual} {$satuanNama}, jumlah yang akan dikurangi: {$qtyDasar} {$satuanNama}.");
                }
            }
        }

        // Revert inventory and log to stock card, or delete fixed assets
        foreach ($goodsReceipt->details as $detail) {
            $barangModel = $detail->barang;
            $tipeItem = $barangModel ? $barangModel->tipe_item : 'Stok';

            if ($tipeItem === 'Stok') {
                $detailFaktor = floatval($detail->faktor_pengali) > 0 ? floatval($detail->faktor_pengali) : 1.0;
                $qtyToRevert = floatval($detail->jumlah_dasar) > 0 ? floatval($detail->jumlah_dasar) : (floatval($detail->jumlah) * $detailFaktor);
                if ($qtyToRevert <= 0) continue;

                $stokGudang = StokGudang::where('barang_id', $detail->barang_id)
                    ->where('gudang_id', $goodsReceipt->gudang_id)
                    ->lockForUpdate()
                    ->first();

                if ($stokGudang) {
                    $stokAwal = floatval($stokGudang->stok_aktual);
                    $currentAvgCost = floatval($stokGudang->harga_pokok_rata_rata);

                    // Clamp jika selisih pembulatan desimal mikro (misal 17.37 vs 17.3736)
                    if ($stokAwal < $qtyToRevert && ($stokAwal + 0.05) >= $qtyToRevert) {
                        $qtyToRevert = $stokAwal;
                    }

                    $stokAkhir = max(0, $stokAwal - $qtyToRevert);

                    $poDetailForPrice = PurchaseOrderDetail::with('po')->find($detail->po_detail_id);
                    if ($poDetailForPrice) {
                        $hargaSatuan = floatval($poDetailForPrice->harga_satuan);
                        $diskonItem = floatval($poDetailForPrice->diskon_item);
                        $netPrice = max(0, $hargaSatuan - $diskonItem);

                        $po = $poDetailForPrice->po;
                        // Gunakan kurs GR saat reversal (konsisten dengan saat posting)
                        $kurs = floatval($goodsReceipt->kurs ?? 0);
                        if ($kurs <= 0) $kurs = $po ? floatval($po->kurs) : 1;
                        if ($kurs <= 0) $kurs = 1;

                        if ($po) {
                            $alokasiBiayaPerUnit = $this->calculateItemHppAllocation($po, $poDetailForPrice);
                            $netPrice += $alokasiBiayaPerUnit;
                        }

                        $poFaktor = floatval($poDetailForPrice->faktor_pengali) > 0 ? floatval($poDetailForPrice->faktor_pengali) : 1.0;
                        $purchasePrice = $poFaktor > 0 ? (($netPrice * $kurs) / $poFaktor) : ($netPrice * $kurs);
                    } else {
                        $purchasePrice = floatval($detail->harga_satuan ?? ($barangModel ? $barangModel->harga_pokok_rata_rata : 0));
                    }

                    if ($stokAkhir > 0) {
                        $newAvgCost = (($stokAwal * $currentAvgCost) - ($qtyToRevert * $purchasePrice)) / $stokAkhir;
                    } else {
                        $newAvgCost = 0;
                    }

                    if ($newAvgCost < 0) $newAvgCost = 0;

                    $previousMasuks = KartuStok::where('barang_id', $detail->barang_id)
                        ->where('gudang_id', $goodsReceipt->gudang_id)
                        ->where('tipe', 'Masuk')
                        ->where('referensi_id', '!=', $goodsReceipt->id)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->take(2)
                        ->get();

                    $hargaBeliTerakhir = 0;
                    $hargaBeliSebelumnya = 0;

                    if ($previousMasuks->count() > 0) {
                        $hargaBeliTerakhir = floatval($previousMasuks[0]->harga_satuan);
                    }
                    if ($previousMasuks->count() > 1) {
                        $hargaBeliSebelumnya = floatval($previousMasuks[1]->harga_satuan);
                    }

                    $nilaiPersediaanLama = floatval($stokGudang->nilai_persediaan ?? ($stokAwal * $currentAvgCost));
                    $nilaiReversal = $qtyToRevert * $purchasePrice;
                    $nilaiPersediaanBaru = max(0, $nilaiPersediaanLama - $nilaiReversal);
                    if ($stokAkhir <= 0) {
                        $nilaiPersediaanBaru = 0;
                        $newAvgCost = '0.000000';
                    } else {
                        $newAvgCost = DecimalHelper::div((string)$nilaiPersediaanBaru, (string)$stokAkhir, 6);
                    }

                    $stokGudang->stok_aktual = $stokAkhir;
                    $stokGudang->nilai_persediaan = (string) $nilaiPersediaanBaru;
                    $stokGudang->hpp_sebelumnya = $stokGudang->harga_pokok_rata_rata ?? 0.00;
                    $stokGudang->harga_pokok_rata_rata = $newAvgCost;
                    $stokGudang->harga_beli_terakhir = $hargaBeliTerakhir;
                    $stokGudang->harga_beli_sebelumnya = $hargaBeliSebelumnya;
                    $stokGudang->save();

                    StokGudang::syncMasterHpp($detail->barang_id);

                    // Log to Stock Card as mutasi Keluar
                    KartuStok::create([
                        'barang_id'       => $detail->barang_id,
                        'gudang_id'       => $goodsReceipt->gudang_id,
                        'tipe'            => 'Keluar',
                        'jumlah'          => $qtyToRevert,
                        'stok_awal'       => $stokAwal,
                        'stok_akhir'      => $stokAkhir,
                        'harga_satuan'    => $purchasePrice,
                        'nilai_total'     => $qtyToRevert * $purchasePrice,
                        'referensi_jenis' => $alasan ? 'Goods Receipt Void' : 'Goods Receipt Adjustment',
                        'referensi_id'    => $goodsReceipt->id,
                        'referensi_nomor' => $goodsReceipt->nomor_gr,
                        'keterangan'      => $alasan 
                            ? "Pembatalan Penerimaan (Void) #{$goodsReceipt->nomor_gr}: {$alasan}" 
                            : "Penyesuaian / Revisi Penerimaan PO #{$goodsReceipt->po->nomor_po}",
                    ]);
                }
            } elseif ($tipeItem === 'Aset') {
                $assets = \App\Models\FixedAsset::where('gr_detail_id', $detail->id)->get();
                foreach ($assets as $asset) {
                    if ($asset->depreciations()->exists()) {
                        throw new \Exception("Aset '{$asset->kode_aset}' ({$barangModel->nama_barang}) sudah memiliki riwayat penyusutan! Hapus penyusutan terlebih dahulu sebelum mengedit/menghapus GR ini.");
                    }
                    $asset->delete();
                }
            }
        }

        // Delete / Void associated Journal entry completely with details (forceDelete to prevent orphan rows)
        $existingJurnals = \App\Models\Jurnal::withTrashed()
            ->where('referensi_tipe', 'Goods Receipt')
            ->where('referensi_id', $goodsReceipt->id)
            ->get();

        foreach ($existingJurnals as $exJnl) {
            $exJnl->details()->delete();
            $exJnl->forceDelete();
        }
    }

    /**
     * Automatic General Ledger Double-Entry Journal Generator for Goods Receipt
     */
    protected function createJournalForPosting(GoodsReceipt $gr)
    {
        // Delete any existing journal for this GR (re-posting safety, forceDelete to clear soft-deleted duplicates)
        $existingJurnals = \App\Models\Jurnal::withTrashed()
            ->where('referensi_tipe', 'Goods Receipt')
            ->where('referensi_id', $gr->id)
            ->get();

        foreach ($existingJurnals as $exJnl) {
            $exJnl->details()->delete();
            $exJnl->forceDelete();
        }

        $rawBranchId = $gr->branch_id !== null ? $gr->branch_id : session('active_branch_id');
        $isBranch = !empty($rawBranchId);

        // Jika input dari Cabang: company_id = NULL, branch_id = ID Cabang.
        // Jika input dari PT Pusat: company_id = ID PT, branch_id = NULL.
        $jurnalCompanyId = $isBranch ? null : ($gr->company_id ?: (session('active_company_id') ?? auth()->user()?->company_id));
        $jurnalBranchId  = $isBranch ? $rawBranchId : null;

        // ID PT untuk resolusi akun COA & Mapping (mengacu ke PT induk jika cabang)
        $coaCompanyId = $gr->company_id ?: (session('active_company_id') ?? auth()->user()?->company_id ?? ($isBranch ? \App\Models\Branch::where('id', $jurnalBranchId)->value('company_id') : 1));

        // 1. Resolve Accounts for active company
        // Unbilled AP (Utang Belum Ditagih / GRNI / Default GR Clearing)
        $unbilledApCoaId = \App\Helpers\AccountMappingHelper::getCoaId('default', 'default_gr_clearing', null, $coaCompanyId)
            ?? \App\Helpers\AccountMappingHelper::getCoaId('default', 'default_ap', null, $coaCompanyId)
            ?? \App\Helpers\AccountMappingHelper::getCoaId('default', 'unbilled_ap', 'accrued_po', $coaCompanyId)
            ?? \App\Models\ChartOfAccount::where('company_id', $coaCompanyId)->detailOnly()->where(function($q) {
                $q->where('code', 'like', '21%')->orWhere('type', 'like', '%Utang%')->orWhere('type', 'Liability');
            })->value('id');

        if (!$unbilledApCoaId) {
            $firstCoa = \App\Models\ChartOfAccount::where('company_id', $coaCompanyId)->detailOnly()->first();
            $unbilledApCoaId = $firstCoa?->id;
        }

        // Default Inventory COA fallback for active company
        $defaultInvCoaId = \App\Helpers\AccountMappingHelper::getCoaId('default', 'default_inventory', null, $coaCompanyId)
            ?? \App\Models\ChartOfAccount::where('company_id', $coaCompanyId)->detailOnly()->where(function($q) {
                $q->where('name', 'Persediaan')->orWhere('name', 'like', 'Persediaan Barang%')->orWhere('type', 'like', '%Persediaan%')->orWhere('type', 'Asset');
            })->value('id');

        $gr->load(['details.barang.kategori', 'po.supplier', 'details.poDetail', 'tender', 'po.tender']);

        // Group inventory value by COA ID
        $debitEntries = []; // coa_id => amount
        $totalInventoryValue = 0;

        foreach ($gr->details as $detail) {
            $kategoriId = $detail->barang?->kategori_barang_id;
            $invCoaId = null;
            if ($kategoriId) {
                $invCoaId = \App\Helpers\AccountMappingHelper::getCoaId('inventory_category', "cat_{$kategoriId}_inventory", 'default_inventory', $coaCompanyId);
            }
            if (!$invCoaId) {
                $invCoaId = $defaultInvCoaId;
            }

            // Calculate exact net item purchase price in IDR accounting currency
            $poDetail = $detail->poDetail;
            $purchasePrice = 0;

            if ($poDetail) {
                $hargaSatuan = floatval($poDetail->harga_satuan);
                $diskonItem = floatval($poDetail->diskon_item ?? 0);
                $netPrice = max(0, $hargaSatuan - $diskonItem);

                $po = $gr->po;
                // Gunakan kurs GR (saat penerimaan) untuk nilai jurnal yang akurat
                $kurs = floatval($gr->kurs ?? 0);
                if ($kurs <= 0) $kurs = $po ? floatval($po->kurs) : 1;
                if ($kurs <= 0) $kurs = 1;

                if ($po) {
                    $alokasiBiayaPerUnit = $this->calculateItemHppAllocation($po, $poDetail);
                    $netPrice += $alokasiBiayaPerUnit;
                }

                $poFaktor = floatval($poDetail->faktor_pengali) > 0 ? floatval($poDetail->faktor_pengali) : 1.0;
                $purchasePrice = $poFaktor > 0 ? (($netPrice * $kurs) / $poFaktor) : ($netPrice * $kurs);
            }

            $detailFaktor = floatval($detail->faktor_pengali) > 0 ? floatval($detail->faktor_pengali) : 1.0;
            $qtyDasar = floatval($detail->jumlah_dasar) > 0 ? floatval($detail->jumlah_dasar) : (floatval($detail->jumlah) * $detailFaktor);
            $itemValue = $qtyDasar * $purchasePrice;

            if ($itemValue > 0 && $invCoaId) {
                $debitEntries[$invCoaId] = ($debitEntries[$invCoaId] ?? 0) + $itemValue;
                $totalInventoryValue += $itemValue;
            }
        }

        if ($totalInventoryValue <= 0) {
            return; // No financial value to journal
        }

        // Generate Journal Number JNL/YYYY/MM/XXXX
        $year   = date('Y', strtotime($gr->tanggal_gr));
        $month  = date('m', strtotime($gr->tanggal_gr));
        $prefix = "JNL/{$year}/{$month}/";
        
        $latestJnlQuery = \App\Models\Jurnal::withTrashed()
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

        while (\App\Models\Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists()) {
            $nextNum++;
            $nomorJurnal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        // Resolve associated tender (Goods Receipt direct tender or PO tender)
        $tenderObj = $gr->tender ?? $gr->po?->tender;
        if (!$tenderObj && ($gr->tender_id || $gr->po?->tender_id)) {
            $tId = $gr->tender_id ?: $gr->po?->tender_id;
            $tenderObj = \App\Models\Tender::find($tId);
        }

        $nomorTender = null;
        if ($tenderObj) {
            $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                : ($tenderObj->nomor_kontrak ?: ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id)));

            if (empty($gr->tender_id)) {
                $gr->update(['tender_id' => $tenderObj->id]);
            }
        }

        $tenderSuffix = $nomorTender ? " [Tender: {$nomorTender}]" : "";
        $supplierName = $gr->po?->supplier?->nama_supplier ?? 'Supplier';

        // Create Jurnal Header (Jika input Cabang: company_id = NULL. Jika input PT: branch_id = NULL)
        $jurnal = \App\Models\Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $gr->tanggal_gr,
            'referensi_tipe' => 'Goods Receipt',
            'referensi_id'   => $gr->id,
            'no_tender'      => $nomorTender,
            'company_id'     => $jurnalCompanyId,
            'branch_id'      => $jurnalBranchId,
            'total_debit'    => $totalInventoryValue,
            'total_kredit'   => $totalInventoryValue,
            'keterangan'     => "Jurnal Penerimaan Barang {$gr->nomor_gr} dari {$supplierName} (PO #{$gr->po?->nomor_po}){$tenderSuffix}",
            'created_by'     => auth()->id(),
        ]);

        // Create Debit entries for Inventory Accounts
        foreach ($debitEntries as $coaId => $amount) {
            \App\Models\JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $coaId,
                'debit'      => $amount,
                'kredit'     => 0,
                'keterangan' => "Persediaan Barang Masuk ({$gr->nomor_gr})",
            ]);
        }

        // Create Credit entry for GRNI (Utang Belum Ditagih / Unbilled AP)
        \App\Models\JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $unbilledApCoaId,
            'debit'      => 0,
            'kredit'     => $totalInventoryValue,
            'keterangan' => "GRNI (Utang Belum Ditagih) - {$supplierName} ({$gr->nomor_gr})",
        ]);
    }

    /**
     * Hitung alokasi biaya HPP per unit item PO berdasarkan metode alokasi masing-masing jenis biaya (Qty vs Nilai Barang)
     */
    protected function calculateItemHppAllocation(PurchaseOrder $po, PurchaseOrderDetail $poDetail): float
    {
        $alokasiTotalPerUnit = 0;
        $details = $po->biaya_lain_details;
        if (is_string($details)) {
            $details = json_decode($details, true);
        }

        $poSubtotal = floatval($po->subtotal);
        $poTotalQty = floatval($po->details ? $po->details->sum('jumlah') : 0);
        if ($poTotalQty <= 0) {
            $poTotalQty = floatval(\App\Models\PurchaseOrderDetail::where('po_id', $po->id)->sum('jumlah'));
        }

        $poDetailSubtotal = floatval($poDetail->subtotal);
        $poDetailJumlah   = floatval($poDetail->jumlah);

        if ($poDetailJumlah <= 0) return 0;

        if (is_array($details) && count($details) > 0) {
            $jbIds = collect($details)->pluck('master_jenis_biaya_id')->filter()->unique()->values();
            $jbMap = \App\Models\MasterJenisBiaya::whereIn('id', $jbIds)->get()->keyBy('id');

            foreach ($details as $exp) {
                $nom = floatval($exp['nominal'] ?? $exp['jumlah'] ?? $exp['biaya'] ?? 0);
                if ($nom <= 0) continue;

                $jbId = $exp['master_jenis_biaya_id'] ?? null;
                $jbModel = ($jbId && isset($jbMap[$jbId])) ? $jbMap[$jbId] : null;

                // Cek status Masuk HPP
                $isHpp = isset($exp['masuk_hpp'])
                    ? ($exp['masuk_hpp'] === '1' || $exp['masuk_hpp'] === true || $exp['masuk_hpp'] === 1 || $exp['masuk_hpp'] === 'true' || strtoupper(strval($exp['masuk_hpp'])) === 'HPP')
                    : (isset($exp['alokasi']) && strtoupper(strval($exp['alokasi'])) === 'HPP' ? true : ($jbModel ? (bool)$jbModel->masuk_hpp : true));

                if (!$isHpp) continue;

                // Tentukan Metode Alokasi: 'Berdasarkan Qty', 'Berdasarkan Nilai Barang', 'Tanpa Alokasi'
                $alokasiMethod = $exp['alokasi_biaya'] ?? ($jbModel ? $jbModel->alokasi_biaya : 'Berdasarkan Nilai Barang');

                if (stripos($alokasiMethod, 'Tanpa') !== false) {
                    continue;
                } elseif (stripos($alokasiMethod, 'Qty') !== false) {
                    // Alokasi Rata Berdasarkan Total Qty PO
                    if ($poTotalQty > 0) {
                        $alokasiTotalPerUnit += ($nom / $poTotalQty);
                    }
                } else {
                    // Alokasi Proporsional Berdasarkan Nilai Subtotal Barang (Default Akuntansi)
                    if ($poSubtotal > 0) {
                        $alokasiLine = ($poDetailSubtotal / $poSubtotal) * $nom;
                        $alokasiTotalPerUnit += ($alokasiLine / $poDetailJumlah);
                    }
                }
            }
        } else {
            // Header Fallback
            $isHppHeader = strtoupper(trim(strval($po->alokasi_biaya_lain ?? ''))) === 'HPP'
                || (isset($po->masuk_hpp) && ($po->masuk_hpp === '1' || $po->masuk_hpp === 1 || $po->masuk_hpp === true || $po->masuk_hpp === 'true'));
            $biayaHppTotal = $isHppHeader ? floatval($po->biaya_lain ?? 0) : 0;
            if ($biayaHppTotal > 0 && $poSubtotal > 0) {
                $alokasiLine = ($poDetailSubtotal / $poSubtotal) * $biayaHppTotal;
                $alokasiTotalPerUnit += ($alokasiLine / $poDetailJumlah);
            }
        }

        return $alokasiTotalPerUnit;
    }
}
