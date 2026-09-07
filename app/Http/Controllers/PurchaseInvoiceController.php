<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
use App\Models\MasterSupplier;
use App\Models\MasterPajak;
use App\Models\PaymentTerm;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\PurchasePayment;
use App\Models\PurchasePaymentDetail;
use App\Models\ChartOfAccount;
use App\Helpers\AccountMappingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('lihat-gr'); // Uses purchasing permissions

        $activeBranchId = session('active_branch_id');

        // Base Query - Only show invoices that have supplier invoice number entered
        $query = PurchaseInvoice::query()
            ->with(['supplier', 'goodsReceipt', 'po.currency', 'currency', 'branch', 'paymentDetails.payment'])
            ->where('branch_id', $activeBranchId)
            ->whereNotNull('nomor_faktur_supplier')
            ->where('nomor_faktur_supplier', '!=', '')
            ->orderByDesc('id');

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('nomor_faktur', function ($i) {
                    return '<a href="javascript:void(0)" class="fw-bold text-primary btn-view-invoice text-decoration-none" data-id="' . $i->id . '" title="Lihat Detail">' . e($i->nomor_faktur) . '</a>';
                })
                ->addColumn('supplier_name', fn($i) => $i->supplier?->nama_supplier ?? '-')
                ->addColumn('nomor_gr', fn($i) => $i->goodsReceipt?->nomor_gr ?? '-')
                ->addColumn('tanggal_faktur_fmt', fn($i) => $i->tanggal_faktur ? $i->tanggal_faktur->format('d/m/Y') : '-')
                ->addColumn('tanggal_jatuh_tempo_fmt', fn($i) => $i->tanggal_jatuh_tempo ? $i->tanggal_jatuh_tempo->format('d/m/Y') : '-')
                ->addColumn('grand_total_fmt', function ($i) {
                    $cur = $i->currency ?? $i->po?->currency;
                    $curCode = strtoupper($cur?->kode_currency ?? ($cur?->code ?? 'IDR'));
                    $isForeign = $cur && !in_array($curCode, ['IDR', 'RP']);
                    $curSymbol = $cur?->simbol ?? ($cur?->symbol ?? ($curCode === 'USD' ? '$' : 'Rp'));
                    $kurs = (float) ($i->kurs ?: 1);
                    $rem = (float) $i->remaining_balance;
                    $paid = (float) $i->paid_amount;

                    $html = '';
                    if ($isForeign) {
                        $idrEquiv = (float) $i->grand_total * $kurs;
                        $html .= '<div class="fw-bold text-body font-monospace">' . e($curSymbol) . ' ' . number_format($i->grand_total, 2, ',', '.') . '</div>'
                            . '<small class="text-muted d-block font-monospace" style="font-size:0.72rem;">≈ Rp ' . number_format($idrEquiv, 2, ',', '.') . '</small>';
                    } else {
                        $html .= '<div class="fw-bold text-body font-monospace">Rp ' . number_format($i->grand_total, 2, ',', '.') . '</div>';
                    }

                    // Tampilkan status sisa hutang jika ada pembayaran
                    if ($i->status !== 'Draft') {
                        if ($rem <= 0.01 && $paid > 0) {
                            $html .= '<div class="text-success fw-bold font-monospace mt-1" style="font-size:0.75rem;"><i class="bi bi-check2 me-1"></i>Lunas (0)</div>';
                        } elseif ($paid > 0) {
                            $sisaFmt = $isForeign ? ($curSymbol . ' ' . number_format($rem, 2, ',', '.')) : ('Rp ' . number_format($rem, 2, ',', '.'));
                            $html .= '<div class="text-warning-emphasis fw-bold font-monospace mt-1" style="font-size:0.75rem;"><i class="bi bi-hourglass me-1"></i>Sisa: ' . $sisaFmt . '</div>';
                        }
                    }

                    return $html;
                })
                ->addColumn('status_badge', function ($i) {
                    if ($i->status === 'Draft') {
                        return '<span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    }

                    $rem = (float) $i->remaining_balance;
                    $paid = (float) $i->paid_amount;

                    if ($i->status === 'Paid' || ($rem <= 0.01 && $paid > 0)) {
                        return '<span class="badge bg-success text-white border border-success rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check2-all me-1"></i>Lunas</span>';
                    } elseif ($paid > 0 && $rem > 0.01) {
                        return '<span class="badge bg-info text-dark border border-info rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pie-chart-fill me-1"></i>Sebagian</span>';
                    } else {
                        return '<span class="badge bg-primary text-white border border-primary rounded-pill px-3 py-1 fw-bold"><i class="bi bi-hourglass-split me-1"></i>Belum Lunas</span>';
                    }
                })
                ->addColumn('action', function ($i) {
                    $btn = '<div class="d-flex align-items-center justify-content-center gap-1">';
                    $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-info rounded-circle btn-view-invoice" data-id="' . $i->id . '" title="Detail Faktur"><i class="bi bi-eye"></i></button>';

                    if ($i->status === 'Draft') {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-warning rounded-circle btn-edit-invoice" data-id="' . $i->id . '" title="Edit Faktur"><i class="bi bi-pencil"></i></button>';
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-success rounded-circle btn-post-invoice" data-id="' . $i->id . '" title="Posting Faktur"><i class="bi bi-send-check"></i></button>';
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle btn-delete-invoice" data-id="' . $i->id . '" title="Hapus Faktur"><i class="bi bi-trash"></i></button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['nomor_faktur', 'grand_total_fmt', 'status_badge', 'action'])
                ->make(true);
        }

        // KPI Summary Breakdown by Currency
        $kpiQuery = PurchaseInvoice::where('branch_id', $activeBranchId)
            ->whereNotNull('nomor_faktur_supplier')
            ->where('nomor_faktur_supplier', '!=', '')
            ->with(['currency', 'po.currency', 'paymentDetails.payment']);

        $allInvoices = (clone $kpiQuery)->get();
        $totalInvoiceCount = $allInvoices->count();
        $countIdr = 0;
        $countForeign = 0;

        foreach ($allInvoices as $inv) {
            $cur = $inv->currency ?? $inv->po?->currency;
            $curCode = strtoupper($cur?->kode_currency ?? ($cur?->code ?? 'IDR'));
            if ($cur && !in_array($curCode, ['IDR', 'RP'])) {
                $countForeign++;
            } else {
                $countIdr++;
            }
        }

        // Sisa Hutang Belum Lunas (Total Seluruh Faktur Tanpa Batas Periode)
        $unpaidIdr = 0;
        $unpaidForeign = 0;
        $unpaidForeignSymbol = '$';
        $unpaidForeignCode = 'USD';
        $totalUnpaidAmount = 0;

        foreach ($allInvoices as $inv) {
            $cur = $inv->currency ?? $inv->po?->currency;
            $curCode = strtoupper($cur?->kode_currency ?? ($cur?->code ?? 'IDR'));
            $isForeign = $cur && !in_array($curCode, ['IDR', 'RP']);
            $kurs = (float) ($inv->kurs ?: 1);
            $rem = (float) $inv->remaining_balance;

            if ($rem > 0.01) {
                $totalUnpaidAmount += ($rem * $kurs);
                if ($isForeign) {
                    $unpaidForeign += $rem;
                    $unpaidForeignSymbol = $cur?->simbol ?? ($cur?->symbol ?? ($curCode === 'USD' ? '$' : $curCode));
                    $unpaidForeignCode = $curCode;
                } else {
                    $unpaidIdr += $rem;
                }
            }
        }

        // Pembayaran Utang Bulan Ini (This Month's Payments)
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        $currentMonthName = Carbon::now()->translatedFormat('F Y');

        $monthlyPaymentsQuery = PurchasePaymentDetail::whereHas('payment', function($q) use ($activeBranchId, $currentYear, $currentMonth) {
            $q->where('status', 'Posted')
              ->whereYear('tanggal_bayar', $currentYear)
              ->whereMonth('tanggal_bayar', $currentMonth);
            if ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            }
        })->with(['payment.currency', 'invoice.currency', 'invoice.po.currency']);

        $monthlyPaymentDetails = $monthlyPaymentsQuery->get();

        $paidThisMonthIdr = 0;
        $paidThisMonthForeign = 0;
        $paidThisMonthForeignSymbol = '$';
        $paidThisMonthForeignCode = 'USD';
        $totalPaidThisMonth = 0;

        foreach ($monthlyPaymentDetails as $det) {
            $pay = $det->payment;
            $inv = $det->invoice;
            $cur = $pay?->currency ?? ($inv?->currency ?? $inv?->po?->currency);
            $curCode = strtoupper($cur?->kode_currency ?? ($cur?->code ?? 'IDR'));
            $isForeign = $cur && !in_array($curCode, ['IDR', 'RP']);
            $kurs = (float) ($pay?->kurs ?: ($inv?->kurs ?: 1));
            $bayar = (float) ($det->jumlah_bayar + $det->potongan);

            $totalPaidThisMonth += ($bayar * $kurs);
            if ($isForeign) {
                $paidThisMonthForeign += $bayar;
                $paidThisMonthForeignSymbol = $cur?->simbol ?? ($cur?->symbol ?? ($curCode === 'USD' ? '$' : $curCode));
                $paidThisMonthForeignCode = $curCode;
            } else {
                $paidThisMonthIdr += $bayar;
            }
        }

        // Fetch eligible posted GRs for invoice creation dropdown (only GRs that DO NOT have an invoice yet)
        $eligibleGrs = GoodsReceipt::where('status', 'Posted')
            ->whereDoesntHave('purchaseInvoice')
            ->whereHas('po', function ($q) use ($activeBranchId) {
                $q->where('branch_id', $activeBranchId);
            })
            ->with(['po.supplier', 'po.currency', 'details.barang', 'gudang'])
            ->orderByDesc('tanggal_gr')
            ->get();

        $suppliers = MasterSupplier::orderBy('nama_supplier')->get(['id', 'kode_supplier', 'nama_supplier']);
        $pajaks = MasterPajak::orderBy('nama_pajak')->get(['id', 'nama_pajak', 'tarif']);
        $paymentTerms = PaymentTerm::orderBy('nama_term')->get(['id', 'nama_term', 'jumlah_hari']);
        $coas = ChartOfAccount::where('is_header', 0)->orderBy('code')->get(['id', 'code', 'name']);

        $defaultGrniCoaId = \App\Helpers\AccountMappingHelper::getCoaId('default', 'default_gr_clearing')
            ?? \App\Helpers\AccountMappingHelper::getCoaId('default', 'unbilled_ap')
            ?? ChartOfAccount::where('code', 'like', '2102%')->orWhere('name', 'like', '%GR/IR%')->orWhere('name', 'like', '%Penerimaan Barang%')->value('id')
            ?? ChartOfAccount::where('is_header', 0)->value('id');

        $defaultPpnCoaId = \App\Helpers\AccountMappingHelper::getCoaId('tax', 'tax_ppn_masukan')
            ?? ChartOfAccount::where('code', 'like', '1107%')->orWhere('code', 'like', '114%')->orWhere('name', 'like', '%PPN Masukan%')->value('id')
            ?? ChartOfAccount::where('is_header', 0)->value('id');

        $defaultFreightCoaId = ChartOfAccount::where('code', 'like', '5103%')->orWhere('name', 'like', '%Biaya Angkut%')->orWhere('name', 'like', '%Freight%')->value('id')
            ?? ChartOfAccount::where('code', 'like', '6104%')->orWhere('name', 'like', '%Pengiriman%')->value('id')
            ?? $defaultGrniCoaId;

        return view('modul_purchasing.faktur_pembelian.index', compact(
            'eligibleGrs',
            'suppliers',
            'pajaks',
            'paymentTerms',
            'coas',
            'defaultGrniCoaId',
            'defaultPpnCoaId',
            'defaultFreightCoaId',
            'totalInvoiceCount',
            'countIdr',
            'countForeign',
            'totalUnpaidAmount',
            'unpaidIdr',
            'unpaidForeign',
            'unpaidForeignSymbol',
            'unpaidForeignCode',
            'totalPaidThisMonth',
            'paidThisMonthIdr',
            'paidThisMonthForeign',
            'paidThisMonthForeignSymbol',
            'paidThisMonthForeignCode',
            'currentMonthName'
        ));
    }

    public function getGrDetailForInvoice(GoodsReceipt $gr)
    {
        $gr->load([
            'po.supplier',
            'po.currency',
            'po.pajak',
            'po.paymentTerm',
            'gudang',
            'details.barang.satuan',
            'details.barang.kategori',
            'details.poDetail'
        ]);

        // Pre-resolve COA for each item category
        $defaultInvCoaId = \App\Helpers\AccountMappingHelper::getCoaId('default', 'default_inventory');
        foreach ($gr->details as $detail) {
            $kategoriId = $detail->barang?->kategori_barang_id;
            $invCoaId = null;
            if ($kategoriId) {
                $invCoaId = \App\Helpers\AccountMappingHelper::getCoaId('inventory_category', "cat_{$kategoriId}_inventory", 'default_inventory');
            }
            if (!$invCoaId) {
                $invCoaId = $defaultInvCoaId;
            }
            $detail->resolved_coa_id = $invCoaId;
        }

        return response()->json([
            'gr'                 => $gr,
            'biaya_lain_details' => $this->resolveBiayaLainFromPo($gr->po),
        ]);
    }

    /**
     * Extract and normalize biaya_lain_details from a PO for pre-filling in the invoice form.
     * Returns array of ['keterangan'=>..., 'nominal'=>..., 'masuk_hpp'=>..., 'coa_id'=>...]
     */
    protected function resolveBiayaLainFromPo($po): array
    {
        if (!$po) return [];

        $details = $po->biaya_lain_details;
        if (is_string($details)) {
            $details = json_decode($details, true);
        }

        if (is_array($details) && count($details) > 0) {
            return array_map(function ($item) {
                $isHpp = isset($item['masuk_hpp'])
                    ? (in_array($item['masuk_hpp'], [1, '1', true, 'true', 'HPP'], true) ? 1 : 0)
                    : (isset($item['alokasi']) && strtoupper($item['alokasi']) === 'HPP' ? 1 : 0);

                $jenisNama = $item['nama_jenis_biaya'] ?? $item['jenis_biaya'] ?? $item['jenis'] ?? $item['nama'] ?? 'Biaya Lain-lain';
                $ket = !empty(trim(strval($item['keterangan'] ?? ''))) ? trim(strval($item['keterangan'])) : (!empty(trim(strval($item['deskripsi'] ?? ''))) ? trim(strval($item['deskripsi'])) : $jenisNama);

                return [
                    'jenis'      => $jenisNama,
                    'keterangan' => $ket,
                    'nominal'    => floatval($item['nominal'] ?? $item['jumlah'] ?? $item['biaya'] ?? 0),
                    'masuk_hpp'  => $isHpp,
                    'coa_id'     => $item['coa_id'] ?? null,
                ];
            }, array_values($details));
        }

        // Fallback: single biaya_lain header field
        if (floatval($po->biaya_lain) > 0) {
            $isHpp = strtoupper(trim(strval($po->alokasi_biaya_lain ?? ''))) === 'HPP' ? 1 : 0;
            $ket = $po->deskripsi_biaya_lain ?? 'Biaya Lain-lain';
            return [[
                'jenis'      => $ket,
                'keterangan' => $ket,
                'nominal'    => floatval($po->biaya_lain),
                'masuk_hpp'  => $isHpp,
                'coa_id'     => null,
            ]];
        }

        return [];
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-gr');

        $request->validate([
            'gr_id'                 => 'required|exists:goods_receipts,id',
            'nomor_faktur_supplier' => 'required|string|max:100',
            'tanggal_faktur'        => 'required|date',
            'payment_term_id'       => 'nullable|exists:payment_terms,id',
            'pajak_id'              => 'nullable|exists:master_pajaks,id',
            'biaya_lain'            => 'nullable|numeric|min:0',
            'items'                 => 'required|array|min:1',
            'items.*.gr_detail_id'  => 'required|exists:goods_receipt_details,id',
            'items.*.harga_satuan'  => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $gr = GoodsReceipt::with(['po.supplier'])->findOrFail($request->gr_id);
            $activeBranchId = session('active_branch_id') ?? $gr->po?->branch_id;

            // Generate Invoice Number (Custom Manual or Auto System INV/YYYY/MM/XXXX)
            if ($request->filled('nomor_faktur') && trim($request->nomor_faktur) !== '' && !str_contains($request->nomor_faktur, 'Otomatis')) {
                $nomorFaktur = trim($request->nomor_faktur);
            } else {
                $year   = date('Y', strtotime($request->tanggal_faktur));
                $month  = date('m', strtotime($request->tanggal_faktur));
                $prefix = "INV/{$year}/{$month}/";
                
                $latestInv = PurchaseInvoice::withTrashed()
                    ->where('nomor_faktur', 'like', $prefix . '%')
                    ->orderByDesc('id')
                    ->first();

                $nextNum = 1;
                if ($latestInv && preg_match('/(\d+)$/', $latestInv->nomor_faktur, $matches)) {
                    $nextNum = ((int) $matches[1]) + 1;
                }

                $nomorFaktur = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                while (PurchaseInvoice::withTrashed()->where('nomor_faktur', $nomorFaktur)->exists()) {
                    $nextNum++;
                    $nomorFaktur = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                }
            }

            // Calculate Due Date
            $dueDate = $request->tanggal_faktur;
            if ($request->payment_term_id) {
                $term = PaymentTerm::find($request->payment_term_id);
                if ($term && $term->jumlah_hari > 0) {
                    $dueDate = date('Y-m-d', strtotime($request->tanggal_faktur . " +{$term->jumlah_hari} days"));
                }
            }

            // Calculate Subtotal Gross, Diskon & Net Items (Barang)
            $subtotalBarangGross = 0;
            $totalDiskon = 0;
            $itemsPayload = [];

            foreach ($request->items as $itemData) {
                $grDetail = GoodsReceiptDetail::find($itemData['gr_detail_id']);
                if (!$grDetail) continue;

                $hargaGross = floatval($itemData['harga_satuan']);
                $qty = floatval($grDetail->jumlah);

                // Calculate Effective Diskon from PO Detail / PO Header
                $poDetail = $grDetail->poDetail;
                $poHeader = $grDetail->goodsReceipt?->po;
                $diskonItem = $poDetail ? floatval($poDetail->diskon_item ?? 0) : 0;

                if ($diskonItem <= 0 && $poHeader) {
                    if (floatval($poHeader->diskon_persen) > 0) {
                        $diskonItem = $hargaGross * (floatval($poHeader->diskon_persen) / 100);
                    } elseif (floatval($poHeader->total_diskon) > 0 && floatval($poHeader->subtotal) > 0) {
                        $diskonItem = (floatval($poHeader->total_diskon) / floatval($poHeader->subtotal)) * $hargaGross;
                    }
                }

                $hargaNetto = max(0, $hargaGross - $diskonItem);
                $lineSubtotalGross = $qty * $hargaGross;
                $lineDiskon = $qty * $diskonItem;
                $lineSubtotalNet = $qty * $hargaNetto;

                $subtotalBarangGross += $lineSubtotalGross;
                $totalDiskon += $lineDiskon;

                $itemsPayload[] = [
                    'jenis'        => 'Barang',
                    'keterangan'   => $grDetail->barang?->nama_barang,
                    'coa_id'       => $itemData['coa_id'] ?? null,
                    'masuk_hpp'    => true,
                    'gr_detail_id' => $grDetail->id,
                    'barang_id'    => $grDetail->barang_id,
                    'jumlah'       => $qty,
                    'harga_satuan' => $hargaGross,
                    'subtotal'     => $lineSubtotalNet,
                ];
            }

            // Process extra costs - separate HPP vs non-HPP
            $biayaHppTotal    = 0;
            $biayaNonHppTotal = 0;
            $extraCostPayload = [];

            if ($request->extra_costs && is_array($request->extra_costs)) {
                foreach ($request->extra_costs as $extra) {
                    $nom = floatval($extra['nominal'] ?? 0);
                    if ($nom <= 0) continue;

                    $isHpp = in_array($extra['masuk_hpp'] ?? '0', [1, '1', 'true', true], true);

                    if ($isHpp) {
                        $biayaHppTotal += $nom;
                    } else {
                        $biayaNonHppTotal += $nom;
                    }

                    $extraCostPayload[] = [
                        'jenis'        => 'Biaya Lain',
                        'keterangan'   => $extra['keterangan'] ?? 'Biaya Tambahan',
                        'coa_id'       => $extra['coa_id'] ?? null,
                        'masuk_hpp'    => $isHpp,
                        'gr_detail_id' => null,
                        'barang_id'    => null,
                        'jumlah'       => 1,
                        'harga_satuan' => $nom,
                        'subtotal'     => $nom,
                    ];
                }
            } elseif ($request->biaya_lain) {
                $biayaNonHppTotal = floatval($request->biaya_lain);
            }

            // Tax & PPN Calculation (DPP = Subtotal Barang Netto setelah diskon)
            $subtotalBarangNet = max(0, $subtotalBarangGross - $totalDiskon);
            $dpp = $subtotalBarangNet;
            $pajakNominal = 0;

            if ($request->pajak_id) {
                $pajak = MasterPajak::find($request->pajak_id);
                if ($pajak && floatval($pajak->tarif) > 0) {
                    $pajakNominal = $dpp * (floatval($pajak->tarif) / 100);
                }
            }

            // Grand Total = DPP + ALL Extra Costs (HPP + Non-HPP) + PPN Tax
            $totalBiayaLain = $biayaHppTotal + $biayaNonHppTotal;
            $grandTotal     = $dpp + $totalBiayaLain + $pajakNominal;
            $status         = ($request->action_type === 'post') ? 'Posted' : 'Draft';

            $invoice = PurchaseInvoice::create([
                'nomor_faktur'          => $nomorFaktur,
                'nomor_faktur_supplier' => $request->nomor_faktur_supplier,
                'tanggal_faktur'        => $request->tanggal_faktur,
                'tanggal_jatuh_tempo'   => $dueDate,
                'po_id'                 => $gr->po_id,
                'gr_id'                 => $gr->id,
                'supplier_id'           => $gr->po?->supplier_id ?? $request->supplier_id,
                'branch_id'             => $activeBranchId,
                'currency_id'           => $gr->po?->currency_id,
                'kurs'                  => $gr->po?->kurs ?? 1,
                'subtotal'              => $subtotalBarangGross,
                'diskon_persen'         => 0,
                'diskon_nominal'        => $totalDiskon,
                'pajak_id'              => $request->pajak_id,
                'pajak_nominal'         => $pajakNominal,
                'biaya_lain'            => $totalBiayaLain,
                'grand_total'           => $grandTotal,
                'status'                => $status,
                'payment_term_id'       => $request->payment_term_id,
                'keterangan'            => $request->keterangan,
                'created_by'            => auth()->id(),
            ]);

            // Save item details
            foreach ($itemsPayload as $d) {
                $d['purchase_invoice_id'] = $invoice->id;
                PurchaseInvoiceDetail::create($d);
            }

            // Save extra cost details
            foreach ($extraCostPayload as $d) {
                $d['purchase_invoice_id'] = $invoice->id;
                if (empty($d['barang_id'])) {
                    unset($d['barang_id']);
                }
                PurchaseInvoiceDetail::create($d);
            }

            // Save PPN detail row if tax > 0
            if ($pajakNominal > 0) {
                PurchaseInvoiceDetail::create([
                    'purchase_invoice_id' => $invoice->id,
                    'jenis'               => 'PPN',
                    'keterangan'          => 'PPN Masukan Pembelian',
                    'coa_id'              => $request->ppn_coa_id,
                    'masuk_hpp'           => false,
                    'gr_detail_id'        => null,
                    'barang_id'           => null,
                    'jumlah'              => 1,
                    'harga_satuan'        => $pajakNominal,
                    'subtotal'            => $pajakNominal,
                ]);
            }

            if ($status === 'Posted') {
                $this->createJournalForInvoice($invoice);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => $status === 'Posted' 
                    ? "Faktur Pembelian {$invoice->nomor_faktur} berhasil dibuat & diposting!" 
                    : "Faktur Pembelian {$invoice->nomor_faktur} berhasil disimpan sebagai Draft!",
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice Store Exception: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat Faktur Pembelian: ' . $e->getMessage()], 500);
        }
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorize('lihat-gr');

        $purchaseInvoice->load([
            'supplier',
            'goodsReceipt.po.currency',
            'goodsReceipt.gudang',
            'po.currency',
            'currency',
            'branch',
            'pajak',
            'paymentTerm',
            'details.barang.satuan',
            'details.coa',
            'jurnal.details.account',
            'paymentDetails.payment.kasBank',
            'paymentDetails.payment.paymentMethod'
        ]);

        return response()->json(['invoice' => $purchaseInvoice]);
    }

    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorize('lihat-gr');

        $purchaseInvoice->load([
            'supplier',
            'goodsReceipt.po.supplier',
            'goodsReceipt.po.currency',
            'goodsReceipt.gudang',
            'po.currency',
            'currency',
            'pajak',
            'paymentTerm',
            'details.barang.satuan',
            'details.coa'
        ]);

        return response()->json([
            'status'  => 'success',
            'invoice' => $purchaseInvoice
        ]);
    }

    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $this->authorize('tambah-gr');

        if ($purchaseInvoice->status !== 'Draft') {
            return response()->json(['message' => 'Hanya Faktur berstatus Draft yang dapat diubah!'], 400);
        }

        $request->validate([
            'nomor_faktur_supplier' => 'required|string|max:100',
            'tanggal_faktur'        => 'required|date',
            'payment_term_id'       => 'nullable|exists:payment_terms,id',
            'pajak_id'              => 'nullable|exists:master_pajaks,id',
            'items'                 => 'required|array|min:1',
            'items.*.gr_detail_id'  => 'required|exists:goods_receipt_details,id',
            'items.*.harga_satuan'  => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calculate Due Date
            $dueDate = $request->tanggal_faktur;
            if ($request->payment_term_id) {
                $term = PaymentTerm::find($request->payment_term_id);
                if ($term && $term->jumlah_hari > 0) {
                    $dueDate = date('Y-m-d', strtotime($request->tanggal_faktur . " +{$term->jumlah_hari} days"));
                }
            }

            // Calculate Subtotal Gross, Diskon & Net Items (Barang)
            $subtotalBarangGross = 0;
            $totalDiskon = 0;
            $itemsPayload = [];

            foreach ($request->items as $itemData) {
                $grDetail = GoodsReceiptDetail::find($itemData['gr_detail_id']);
                if (!$grDetail) continue;

                $hargaGross = floatval($itemData['harga_satuan']);
                $qty = floatval($grDetail->jumlah);

                // Calculate Effective Diskon from PO Detail / PO Header
                $poDetail = $grDetail->poDetail;
                $poHeader = $grDetail->goodsReceipt?->po;
                $diskonItem = $poDetail ? floatval($poDetail->diskon_item ?? 0) : 0;

                if ($diskonItem <= 0 && $poHeader) {
                    if (floatval($poHeader->diskon_persen) > 0) {
                        $diskonItem = $hargaGross * (floatval($poHeader->diskon_persen) / 100);
                    } elseif (floatval($poHeader->total_diskon) > 0 && floatval($poHeader->subtotal) > 0) {
                        $diskonItem = (floatval($poHeader->total_diskon) / floatval($poHeader->subtotal)) * $hargaGross;
                    }
                }

                $hargaNetto = max(0, $hargaGross - $diskonItem);
                $lineSubtotalGross = $qty * $hargaGross;
                $lineDiskon = $qty * $diskonItem;
                $lineSubtotalNet = $qty * $hargaNetto;

                $subtotalBarangGross += $lineSubtotalGross;
                $totalDiskon += $lineDiskon;

                $itemsPayload[] = [
                    'jenis'        => 'Barang',
                    'keterangan'   => $grDetail->barang?->nama_barang,
                    'coa_id'       => $itemData['coa_id'] ?? null,
                    'masuk_hpp'    => true,
                    'gr_detail_id' => $grDetail->id,
                    'barang_id'    => $grDetail->barang_id,
                    'jumlah'       => $qty,
                    'harga_satuan' => $hargaGross,
                    'subtotal'     => $lineSubtotalNet,
                ];
            }

            // Process extra costs
            $biayaHppTotal    = 0;
            $biayaNonHppTotal = 0;
            $extraCostPayload = [];

            if ($request->extra_costs && is_array($request->extra_costs)) {
                foreach ($request->extra_costs as $extra) {
                    $nom = floatval($extra['nominal'] ?? 0);
                    if ($nom <= 0) continue;

                    $isHpp = in_array($extra['masuk_hpp'] ?? '0', [1, '1', 'true', true], true);

                    if ($isHpp) {
                        $biayaHppTotal += $nom;
                    } else {
                        $biayaNonHppTotal += $nom;
                    }

                    $extraCostPayload[] = [
                        'jenis'        => 'Biaya Lain',
                        'keterangan'   => $extra['keterangan'] ?? 'Biaya Tambahan',
                        'coa_id'       => $extra['coa_id'] ?? null,
                        'masuk_hpp'    => $isHpp,
                        'gr_detail_id' => null,
                        'barang_id'    => null,
                        'jumlah'       => 1,
                        'harga_satuan' => $nom,
                        'subtotal'     => $nom,
                    ];
                }
            }

            $subtotalBarangNet = max(0, $subtotalBarangGross - $totalDiskon);
            $dpp = $subtotalBarangNet;
            $pajakNominal = 0;

            if ($request->pajak_id) {
                $pajak = MasterPajak::find($request->pajak_id);
                if ($pajak && floatval($pajak->tarif) > 0) {
                    $pajakNominal = $dpp * (floatval($pajak->tarif) / 100);
                }
            }

            $totalBiayaLain = $biayaHppTotal + $biayaNonHppTotal;
            $grandTotal     = $dpp + $totalBiayaLain + $pajakNominal;
            $status         = ($request->action_type === 'post') ? 'Posted' : 'Draft';

            $purchaseInvoice->update([
                'nomor_faktur'          => ($request->filled('nomor_faktur') && !str_contains($request->nomor_faktur, 'Otomatis')) ? trim($request->nomor_faktur) : $purchaseInvoice->nomor_faktur,
                'nomor_faktur_supplier' => $request->nomor_faktur_supplier,
                'tanggal_faktur'        => $request->tanggal_faktur,
                'tanggal_jatuh_tempo'   => $dueDate,
                'subtotal'              => $subtotalBarangGross,
                'diskon_nominal'        => $totalDiskon,
                'pajak_id'              => $request->pajak_id,
                'pajak_nominal'         => $pajakNominal,
                'biaya_lain'            => $totalBiayaLain,
                'grand_total'           => $grandTotal,
                'status'                => $status,
                'payment_term_id'       => $request->payment_term_id,
                'keterangan'            => $request->keterangan,
            ]);

            // Re-sync details
            $purchaseInvoice->details()->delete();

            foreach ($itemsPayload as $d) {
                $d['purchase_invoice_id'] = $purchaseInvoice->id;
                PurchaseInvoiceDetail::create($d);
            }

            foreach ($extraCostPayload as $d) {
                $d['purchase_invoice_id'] = $purchaseInvoice->id;
                if (empty($d['barang_id'])) {
                    unset($d['barang_id']);
                }
                PurchaseInvoiceDetail::create($d);
            }

            if ($pajakNominal > 0) {
                PurchaseInvoiceDetail::create([
                    'purchase_invoice_id' => $purchaseInvoice->id,
                    'jenis'               => 'PPN',
                    'keterangan'          => 'PPN Masukan Pembelian',
                    'coa_id'              => $request->ppn_coa_id,
                    'masuk_hpp'           => false,
                    'gr_detail_id'        => null,
                    'barang_id'           => null,
                    'jumlah'              => 1,
                    'harga_satuan'        => $pajakNominal,
                    'subtotal'            => $pajakNominal,
                ]);
            }

            if ($status === 'Posted') {
                $this->createJournalForInvoice($purchaseInvoice);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => $status === 'Posted' 
                    ? "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil diperbarui & diposting!" 
                    : "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil diperbarui!",
                'invoice' => $purchaseInvoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice Update Exception: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui Faktur Pembelian: ' . $e->getMessage()], 500);
        }
    }

    public function approve(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorize('tambah-gr');

        if (!in_array($purchaseInvoice->status, ['Draft', 'Menunggu Approval'])) {
            return response()->json(['message' => 'Hanya Faktur berstatus Draft atau Menunggu Approval yang dapat disetujui!'], 400);
        }

        try {
            DB::beginTransaction();
            $purchaseInvoice->status = 'Approved';
            $purchaseInvoice->save();
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil disetujui (Approved)!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyetujui Faktur: ' . $e->getMessage()], 500);
        }
    }

    public function void(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorize('tambah-gr');

        if ($purchaseInvoice->status === 'Void') {
            return response()->json(['message' => 'Faktur sudah berstatus Void!'], 400);
        }

        try {
            DB::beginTransaction();
            $purchaseInvoice->status = 'Void';
            $purchaseInvoice->save();

            // Delete associated Jurnal entry on Void
            Jurnal::where('referensi_tipe', 'Purchase Invoice')
                ->where('referensi_id', $purchaseInvoice->id)
                ->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil dibatalkan (Void)!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses Void: ' . $e->getMessage()], 500);
        }
    }

    public function pdf(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorize('lihat-gr');

        $purchaseInvoice->load([
            'supplier',
            'goodsReceipt.po.currency',
            'po.currency',
            'currency',
            'branch',
            'pajak',
            'paymentTerm',
            'details.barang.satuan',
            'details.coa',
            'creator'
        ]);

        return view('modul_purchasing.faktur_pembelian.pdf', compact('purchaseInvoice'));
    }

    public function post(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorize('tambah-gr');

        if ($purchaseInvoice->status !== 'Draft') {
            return response()->json(['message' => 'Hanya Faktur berstatus Draft yang dapat diposting!'], 400);
        }

        try {
            DB::beginTransaction();

            $purchaseInvoice->status = 'Posted';
            $purchaseInvoice->save();

            // Auto-Journal Entry Generation for Purchase Invoice
            $this->createJournalForInvoice($purchaseInvoice);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil diposting & Jurnal Akuntansi diterbitkan!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PurchaseInvoice Post Exception: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memposting Faktur: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorize('hapus-gr');

        if ($purchaseInvoice->status !== 'Draft') {
            return response()->json(['message' => 'Faktur yang sudah diposting tidak dapat dihapus!'], 400);
        }

        try {
            DB::beginTransaction();

            // Delete associated Jurnal entry
            Jurnal::where('referensi_tipe', 'Purchase Invoice')
                ->where('referensi_id', $purchaseInvoice->id)
                ->delete();

            $purchaseInvoice->details()->delete();
            $purchaseInvoice->delete();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Faktur Pembelian {$purchaseInvoice->nomor_faktur} berhasil dihapus!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus Faktur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Automatic General Ledger Journal Entry Generator for Purchase Invoice.
     *
     * Journal Mapping:
     *   DEBIT  Utang Belum Ditagih  = Subtotal Barang (+ HPP extra costs)
     *   DEBIT  PPN Masukan          = pajak_nominal
     *   DEBIT  COA Beban / Detail   = each non-HPP extra cost (per coa_id)
     *   KREDIT Utang Usaha (AP)     = grand_total
     */
    protected function createJournalForInvoice(PurchaseInvoice $invoice)
    {
        // Delete existing journal if any (idempotent re-post, forceDelete to clear soft-deleted duplicates)
        $existingJurnals = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Purchase Invoice')
            ->where('referensi_id', $invoice->id)
            ->get();

        foreach ($existingJurnals as $exJnl) {
            $exJnl->details()->delete();
            $exJnl->forceDelete();
        }

        $activeBranchId = session('active_branch_id') ?? $invoice->branch_id;

        // Load details to inspect per-row masuk_hpp and coa_id
        $invoice->loadMissing(['details', 'supplier']);

        // ── COA Resolution ───────────────────────────────────────────────
        // 1. Unbilled AP (Utang Belum Ditagih / GRNI) – DEBIT for barang & HPP costs
        $unbilledApCoaId = AccountMappingHelper::getCoaId('default', 'default_gr_clearing')
            ?? AccountMappingHelper::getCoaId('default', 'unbilled_ap', 'accrued_po')
            ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function($q) {
                $q->where('code', 'like', '2102%')->orWhere('name', 'like', '%GR/IR%')->orWhere('name', 'like', '%Penerimaan Barang%');
            })->value('id')
            ?? ChartOfAccount::forActiveCompany()->detailOnly()->where('type', 'Liability')->value('id');

        // 2. Tax PPN Masukan – DEBIT
        $ppnDetail = $invoice->details->firstWhere('jenis', 'PPN');
        $taxInputCoaId = ($ppnDetail && $ppnDetail->coa_id) ? $ppnDetail->coa_id : null;
        if (!$taxInputCoaId) {
            $pajakId = $invoice->po?->pajak_id;
            $taxInputCoaId = $pajakId ? AccountMappingHelper::getCoaId('tax', "tax_{$pajakId}_in") : null;
            if (!$taxInputCoaId) {
                $taxInputCoaId = AccountMappingHelper::getCoaId('tax', 'tax_ppn_masukan')
                    ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function($q) {
                        $q->where('code', 'like', '1107%')->orWhere('code', 'like', '114%')->orWhere('name', 'like', '%PPN Masukan%');
                    })->value('id');
            }
        }

        // 3. Accounts Payable (Utang Usaha) – KREDIT
        $apCoaId = AccountMappingHelper::getCoaId('default', 'default_ap')
            ?? AccountMappingHelper::getCoaId('default', 'accounts_payable', 'ap')
            ?? ChartOfAccount::forActiveCompany()->detailOnly()->where(function($q) {
                $q->where('code', 'like', '2101%')->orWhere('code', 'like', '211%')->orWhere('name', 'like', '%Hutang Usaha%')->orWhere('name', 'like', '%Utang Usaha%');
            })->value('id');

        // Fallback to first non-header COA if any mapping fails
        $firstCoa = ChartOfAccount::forActiveCompany()->detailOnly()->first();
        if (!$unbilledApCoaId) $unbilledApCoaId = $firstCoa?->id;
        if (!$taxInputCoaId)   $taxInputCoaId   = $firstCoa?->id;
        if (!$apCoaId)         $apCoaId         = $firstCoa?->id;

        // ── Journal Number Generation JNL/YYYY/MM/XXXX ──────────────────
        $year   = date('Y', strtotime($invoice->tanggal_faktur));
        $month  = date('m', strtotime($invoice->tanggal_faktur));
        $prefix = "JNL/{$year}/{$month}/";

        $latestJnl = Jurnal::withTrashed()
            ->where('nomor_jurnal', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextNum = 1;
        if ($latestJnl && preg_match('/(\d+)$/', $latestJnl->nomor_jurnal, $matches)) {
            $nextNum = ((int) $matches[1]) + 1;
        }

        $nomorJurnal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        while (Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists()) {
            $nextNum++;
            $nomorJurnal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        $supplierName = $invoice->supplier?->nama_supplier ?? 'Supplier';
        $grandTotal   = floatval($invoice->grand_total);
        $taxNominal   = floatval($invoice->pajak_nominal);

        // ── Separate detail rows by type and target COA ───────────────────
        $barangDebitRows = [];
        $biayaHppRows    = [];
        $biayaNonHppRows = [];

        foreach ($invoice->details as $det) {
            $nom = floatval($det->subtotal);
            if ($nom <= 0) continue;

            if ($det->jenis === 'Barang') {
                $coa = $det->coa_id ?? $unbilledApCoaId;
                if (!isset($barangDebitRows[$coa])) {
                    $barangDebitRows[$coa] = ['total' => 0, 'keterangan' => 'Penutupan Utang Belum Ditagih (GRNI)'];
                }
                $barangDebitRows[$coa]['total'] += $nom;
            } elseif ($det->jenis === 'Biaya Lain') {
                if ($det->masuk_hpp) {
                    $coa = $det->coa_id ?? $unbilledApCoaId;
                    if (!isset($biayaHppRows[$coa])) {
                        $biayaHppRows[$coa] = ['total' => 0, 'keterangan' => $det->keterangan ?? 'Biaya HPP'];
                    }
                    $biayaHppRows[$coa]['total'] += $nom;
                } else {
                    $coa = $det->coa_id ?? $unbilledApCoaId;
                    if (!isset($biayaNonHppRows[$coa])) {
                        $biayaNonHppRows[$coa] = ['total' => 0, 'keterangan' => $det->keterangan ?? 'Biaya Non-HPP'];
                    }
                    $biayaNonHppRows[$coa]['total'] += $nom;
                }
            }
        }

        // ── Create Journal Header ────────────────────────────────────────
        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $invoice->tanggal_faktur,
            'referensi_tipe' => 'Purchase Invoice',
            'referensi_id'   => $invoice->id,
            'branch_id'      => $activeBranchId,
            'total_debit'    => $grandTotal,
            'total_kredit'   => $grandTotal,
            'keterangan'     => "Jurnal Faktur Pembelian {$invoice->nomor_faktur} dari {$supplierName}",
            'created_by'     => auth()->id(),
        ]);

        // ── Journal Lines ────────────────────────────────────────────────

        // 1. DEBIT: Barang (Penutupan Utang Belum Ditagih / GRNI per COA)
        foreach ($barangDebitRows as $coaId => $row) {
            if ($row['total'] > 0) {
                JurnalDetail::create([
                    'jurnal_id'  => $jurnal->id,
                    'account_id' => $coaId,
                    'debit'      => $row['total'],
                    'kredit'     => 0,
                    'keterangan' => "Penutupan Utang Belum Ditagih (GRNI) ({$invoice->nomor_faktur})",
                ]);
            }
        }

        // 2. DEBIT: Biaya Masuk HPP
        foreach ($biayaHppRows as $coaId => $row) {
            if ($row['total'] > 0) {
                JurnalDetail::create([
                    'jurnal_id'  => $jurnal->id,
                    'account_id' => $coaId,
                    'debit'      => $row['total'],
                    'kredit'     => 0,
                    'keterangan' => "Alokasi Biaya HPP - " . ($row['keterangan'] ?? 'Biaya Tambahan') . " ({$invoice->nomor_faktur})",
                ]);
            }
        }

        // 3. DEBIT: PPN Masukan
        if ($taxNominal > 0) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $taxInputCoaId,
                'debit'      => $taxNominal,
                'kredit'     => 0,
                'keterangan' => "PPN Masukan Pembelian ({$invoice->nomor_faktur})",
            ]);
        }

        // 4. DEBIT: Non-HPP extra costs → per COA (e.g. Beban Angkut, Asuransi)
        foreach ($biayaNonHppRows as $coaId => $row) {
            if ($row['total'] > 0) {
                JurnalDetail::create([
                    'jurnal_id'  => $jurnal->id,
                    'account_id' => $coaId,
                    'debit'      => $row['total'],
                    'kredit'     => 0,
                    'keterangan' => ($row['keterangan'] ?? 'Biaya Lain-lain') . " ({$invoice->nomor_faktur})",
                ]);
            }
        }

        // 5. KREDIT: Utang Usaha / Accounts Payable = Grand Total
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'account_id' => $apCoaId,
            'debit'      => 0,
            'kredit'     => $grandTotal,
            'keterangan' => "Utang Usaha Supplier - {$supplierName} ({$invoice->nomor_faktur})",
        ]);
    }
}

