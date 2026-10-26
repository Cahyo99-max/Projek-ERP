<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionDetail;
use App\Models\MasterSupplier;
use App\Models\MasterCurrency;
use App\Models\MasterPajak;
use App\Models\MasterBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-po');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        // Only pass lightweight lookup data — PO rows are loaded via server-side DataTables
        // PRs and Suppliers are loaded via AJAX popup modals to handle large datasets efficiently
        $currencies     = MasterCurrency::forActiveCompany()->active()->get();
        $pajaks         = MasterPajak::forActiveCompany()->active()->get();
        $paymentTerms   = \App\Models\PaymentTerm::forActiveCompany()->active()->get();
        $paymentMethods = \App\Models\PaymentMethod::forActiveCompany()->active()->get();
        $expenseTypes   = \App\Models\MasterJenisBiaya::forActiveCompany()->active()->get();
        $coas           = \App\Models\ChartOfAccount::where('is_header', 0)->orderBy('code')->get(['id', 'code', 'name']);
        $defaultPphCoaId = $this->resolveDefaultPphCoaId();

        $pphCoaMap = [];
        foreach ($pajaks as $pj) {
            $mapped = \App\Helpers\AccountMappingHelper::getCoaId('tax', "tax_{$pj->id}_in");
            if ($mapped) {
                $pphCoaMap[$pj->id] = $mapped;
            }
        }

        // Filter Pajak khusus PPh (Withholding Tax) — kecualikan PPN
        $pphPajaks = $pajaks->filter(function($p) {
            $n = strtolower($p->nama_pajak ?? '');
            $k = strtolower($p->kode_pajak ?? '');
            return str_contains($n, 'pph') || str_contains($k, 'pph');
        });
        if ($pphPajaks->isEmpty()) {
            $pphPajaks = $pajaks->filter(function($p) {
                return !str_contains(strtolower($p->nama_pajak ?? ''), 'ppn');
            });
        }

        $tenders        = \App\Models\Tender::forActiveCompany()
            ->whereNotIn(DB::raw('LOWER(status)'), ['draft', 'lost', 'rejected', 'kalah'])
            ->where(function($query) {
                $query->whereNull('tahap_tender')
                      ->orWhereNotIn(DB::raw('LOWER(tahap_tender)'), ['kalah lelang (lost)', 'ditolak', 'draft']);
            })
            ->with('customer')
            ->orderBy('nama_tender')
            ->get();

        return view('modul_purchasing.pembuatanpo.viewpo', compact(
            'currencies', 'pajaks', 'pphPajaks', 'paymentTerms', 'paymentMethods', 'expenseTypes', 'tenders', 'coas', 'defaultPphCoaId', 'pphCoaMap'
        ));
    }

    /**
     * Resolve default COA "Utang PPh" (liability) untuk PPh yang dipotong atas pembelian.
     */
    protected function resolveDefaultPphCoaId(): ?int
    {
        return \App\Helpers\AccountMappingHelper::getCoaId('tax', 'tax_pph_payable')
            ?? \App\Models\ChartOfAccount::where('is_header', 0)->where(function ($q) {
                $q->where('name', 'like', '%Utang PPh%')
                  ->orWhere('name', 'like', '%Hutang PPh%')
                  ->orWhere('name', 'like', '%PPh 23%')
                  ->orWhere('name', 'like', '%PPh Pasal 23%');
            })->value('id')
            ?? \App\Models\ChartOfAccount::where('is_header', 0)->where('name', 'like', '%PPh%')->value('id');
    }

    /**
     * Server-side DataTables endpoint for Purchase Order listing.
     * Handles sorting, searching, and pagination in the database — not in PHP/browser.
     */
    public function datatable(Request $request)
    {
        $this->authorize('lihat-po');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        $query = PurchaseOrder::with([
            'supplier:id,nama_supplier',
            'currency:id,kode_currency,simbol',
            'pr:id,nomor_pr,diminta_oleh',
            'pr.requester:id,nama_lengkap',
            'tender:id,nomor_kontrak,nama_tender',
            'branch:id,nama_cabang',
            'pajak:id,nama_pajak,tarif',
            'pphPajak:id,nama_pajak,tarif',
            'pphCoa:id,code,name',
            'details:id,po_id,diskon_item,jumlah,tarif_ppn,ppn_nominal',
        ])
        ->select('purchase_orders.*');

        if (!empty($activeBranchId)) {
            $query->where('purchase_orders.branch_id', intval($activeBranchId));
        } else {
            $query->whereNull('purchase_orders.branch_id');
            if ($activeCompanyId) {
                $query->where('purchase_orders.company_id', $activeCompanyId);
            }
        }

        // Apply Filters
        if ($request->filled('filter_status')) {
            $query->where('purchase_orders.status', $request->filter_status);
        }
        if ($request->filled('filter_tender')) {
            if ($request->filter_tender === 'non_tender') {
                $query->whereNull('purchase_orders.tender_id');
            } else {
                $query->where('purchase_orders.tender_id', $request->filter_tender);
            }
        }
        if ($request->filled('filter_currency')) {
            $query->where('purchase_orders.currency_id', $request->filter_currency);
        }
        if ($request->filled('filter_tgl_mulai')) {
            $query->whereDate('purchase_orders.tanggal_po', '>=', $request->filter_tgl_mulai);
        }
        if ($request->filled('filter_tgl_selesai')) {
            $query->whereDate('purchase_orders.tanggal_po', '<=', $request->filter_tgl_selesai);
        }

        // Strict Draft privacy: Draft is ONLY visible to the creator (created_by == auth()->id())
        $userId = auth()->id();
        $user = auth()->user();
        if ($user->can('lihat-po') || $user->can('approve-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting', 'Manager Finance', 'Manager Produksi'])) {
            $query->where(function($q) use ($userId) {
                $q->where('purchase_orders.status', '!=', 'Draft')
                  ->orWhere('purchase_orders.created_by', $userId);
            });
        } else {
            $query->where('purchase_orders.created_by', $userId);
        }

        return DataTables::eloquent($query)
            ->order(function ($q) use ($request) {
                if ($request->has('order') && !empty($request->input('order'))) {
                    $order = $request->input('order')[0];
                    $colIdx = intval($order['column'] ?? 2);
                    $colDir = strtolower($order['dir'] ?? 'desc');
                    $colDir = in_array($colDir, ['asc', 'desc']) ? $colDir : 'desc';

                    $colMap = [
                        1 => 'purchase_orders.nomor_po',
                        2 => 'purchase_orders.tanggal_po',
                        4 => 'purchase_orders.total_pajak',
                        5 => 'purchase_orders.total_diskon',
                        6 => 'purchase_orders.grand_total',
                        7 => 'purchase_orders.status',
                    ];

                    if ($colIdx === 2) {
                        // Urutkan berdasarkan tanggal (terbaru) dan yang terakhir di-input (id desc)
                        $q->orderBy('purchase_orders.tanggal_po', $colDir)
                          ->orderBy('purchase_orders.id', $colDir);
                        return;
                    } elseif ($colIdx === 3) {
                        $q->orderBy(
                            \App\Models\MasterSupplier::select('nama_supplier')
                                ->whereColumn('master_suppliers.id', 'purchase_orders.supplier_id')
                                ->limit(1),
                            $colDir
                        );
                        return;
                    } elseif (isset($colMap[$colIdx])) {
                        $q->orderBy($colMap[$colIdx], $colDir);
                        return;
                    }
                }

                // Default urutan: Tanggal PO terbaru (desc) & Terakhir di-input (id desc)
                $q->orderBy('purchase_orders.tanggal_po', 'desc')
                  ->orderBy('purchase_orders.id', 'desc');
            })
            ->addIndexColumn()
            ->addColumn('nomor_po_badge', function ($po) {
                return $po->nomor_po;
            })
            ->addColumn('tanggal_fmt', function ($po) {
                return $po->tanggal_po ? $po->tanggal_po->format('d/m/Y') : '-';
            })
            ->addColumn('supplier_name', function ($po) {
                if (!$po->supplier) return '-';
                $raw = $po->supplier->nama_supplier;
                return preg_replace('/^Cv\b/i', 'CV', preg_replace('/^Pt\b/i', 'PT', ucwords(strtolower($raw))));
            })
            ->addColumn('cabang_name', function ($po) {
                return $po->branch ? $po->branch->nama_cabang : '-';
            })
            ->addColumn('pr_info', function ($po) {
                if ($po->pr) {
                    return [
                        'nomor_pr' => $po->pr->nomor_pr,
                        'requester' => $po->pr->requester ? $po->pr->requester->nama_lengkap : null,
                    ];
                } elseif ($po->manual_pr_number) {
                    return [
                        'nomor_pr' => $po->manual_pr_number . ' (Manual)',
                        'requester' => null,
                    ];
                }
                return null;
            })
            ->addColumn('tender', function ($po) {
                if ($po->tender) {
                    return [
                        'id' => $po->tender->id,
                        'nomor_kontrak' => $po->tender->nomor_kontrak,
                        'nama_tender' => $po->tender->nama_tender,
                    ];
                }
                return null;
            })
            ->addColumn('pajak_info', function ($po) {
                $totalPajak = (float) $po->total_pajak;
                $simbol = $po->currency ? $po->currency->simbol : 'Rp';
                $details = $po->relationLoaded('details') ? $po->details : $po->details()->get();

                $rates = [];
                if ($details && $details->count()) {
                    foreach ($details as $d) {
                        $rate = (float) ($d->tarif_ppn ?? 0);
                        if ($rate > 0) {
                            $rates[(string)$rate] = true;
                        }
                    }
                }
                $rateKeys = array_keys($rates);
                $rateText = '';
                if (count($rateKeys) === 1) {
                    $rateText = $rateKeys[0] . '%';
                } elseif (count($rateKeys) > 1) {
                    $rateText = 'Campur';
                } elseif ($po->pajak && (float)$po->pajak->tarif > 0) {
                    $rateText = ((float)$po->pajak->tarif) . '%';
                }

                return [
                    'nama_pajak'  => $po->pajak ? $po->pajak->nama_pajak : 'PPN',
                    'tarif'       => $rateText,
                    'total_pajak' => $totalPajak,
                    'simbol'      => $simbol,
                ];
            })
            ->addColumn('diskon_info', function ($po) {
                $savedTotalDiskon = (float) $po->total_diskon;
                $itemDiskonTotal = 0;
                $firstItemDiskonUnit = 0;
                $itemCountWithDiskon = 0;

                $details = $po->relationLoaded('details') ? $po->details : $po->details()->get();
                if ($details && $details->count()) {
                    foreach ($details as $d) {
                        $dItem = (float)$d->diskon_item;
                        $dQty = (float)$d->jumlah;
                        if ($dItem > 0) {
                            $itemDiskonTotal += ($dItem * $dQty);
                            if ($firstItemDiskonUnit == 0) {
                                $firstItemDiskonUnit = $dItem;
                            }
                            $itemCountWithDiskon++;
                        }
                    }
                }

                $grandDiskon = max($savedTotalDiskon, $itemDiskonTotal);
                $grossSubtotal = (float)$po->subtotal + $grandDiskon;
                $persen = (float) $po->diskon_persen;
                if ($persen <= 0 && $grossSubtotal > 0 && $grandDiskon > 0) {
                    $persen = round(($grandDiskon / $grossSubtotal) * 100, 2);
                }

                return [
                    'diskon_persen'         => $persen,
                    'total_diskon'          => $grandDiskon,
                    'header_diskon'         => $savedTotalDiskon > $itemDiskonTotal ? ($savedTotalDiskon - $itemDiskonTotal) : 0,
                    'item_diskon_total'     => $itemDiskonTotal,
                    'item_diskon_unit'      => $firstItemDiskonUnit,
                    'item_diskon_count'     => $itemCountWithDiskon,
                    'subtotal'              => (float) $po->subtotal,
                    'simbol'                => $po->currency ? $po->currency->simbol : 'Rp',
                ];
            })
            ->addColumn('grand_total_info', function ($po) {
                return [
                    'simbol'          => $po->currency ? $po->currency->simbol : 'Rp',
                    'kode_currency'   => $po->currency ? $po->currency->kode_currency : 'IDR',
                    'grand_total'     => (float) $po->grand_total,
                    'grand_total_idr' => (float) $po->grand_total_idr,
                    'down_payment'    => (float) $po->down_payment,
                    'biaya_lain'      => (float) $po->biaya_lain,
                    'pph_nominal'     => (float) ($po->pph_nominal ?: $po->total_pph ?: 0),
                    'tarif_pph'       => (float) $po->tarif_pph,
                    'nama_pph'        => $po->pphPajak?->nama_pajak,
                    'kode_pph_coa'    => $po->pphCoa?->code,
                    'sisa_bayar'      => (float) ($po->grand_total - $po->down_payment),
                ];
            })
            ->addColumn('can_edit', function ($po) {
                $user = auth()->user();
                if (!$user->can('edit-po')) return false;
                $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Owner');
                if ($isSuperAdmin) return true;

                $userId = $user->id;
                $isRejectedOwner = $po->status === 'Rejected' && $po->created_by == $userId;
                return in_array($po->status, ['Draft', 'Pending']) || $isRejectedOwner;
            })
            ->addColumn('can_delete', function ($po) {
                $user = auth()->user();
                if (!$user->can('hapus-po')) return false;
                $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Owner');
                if ($isSuperAdmin) return true;

                // User biasa HANYA boleh menghapus PO jika statusnya Draft
                return $po->status === 'Draft';
            })
            ->addColumn('can_reopen', function ($po) {
                $user = auth()->user();
                if (!$user->can('buka-po') && !$user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director'])) {
                    return false;
                }
                return $po->status !== 'Draft';
            })
            ->filterColumn('nomor_po', function ($query, $keyword) {
                $query->where('nomor_po', 'like', "%{$keyword}%");
            })
            ->filterColumn('supplier_name', function ($query, $keyword) {
                $query->whereHas('supplier', fn($q) => $q->where('nama_supplier', 'like', "%{$keyword}%"));
            })
            ->orderColumn('tanggal_fmt', 'tanggal_po $1')
            ->orderColumn('grand_total_info', 'grand_total $1')
            ->rawColumns([])
            ->make(true);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('lihat-po');

        $tglMulai    = $request->input('tgl_mulai');
        $tglSelesai  = $request->input('tgl_selesai');
        $status      = $request->input('filter_status');
        $tenderId    = $request->input('filter_tender');
        $currencyId  = $request->input('filter_currency');

        if (empty($tglMulai) || empty($tglSelesai)) {
            return response()->json([
                'success' => false,
                'message' => 'Harap tentukan Tanggal Mulai dan Tanggal Selesai (Maksimal 6 bulan).'
            ], 422);
        }

        try {
            $start = \Carbon\Carbon::parse($tglMulai)->startOfDay();
            $end   = \Carbon\Carbon::parse($tglSelesai)->endOfDay();

            if ($start->gt($end)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal Mulai tidak boleh lebih besar dari Tanggal Selesai.'
                ], 422);
            }

            $diffInDays = $start->diffInDays($end);
            if ($diffInDays > 186) {
                return response()->json([
                    'success' => false,
                    'message' => "Export Excel dibatasi maksimal 6 bulan (186 hari). Rentang yang Anda pilih adalah {$diffInDays} hari."
                ], 422);
            }

            $activeBranchId  = session('active_branch_id');
            $activeCompanyId = session('active_company_id');

            $query = PurchaseOrder::with([
                'supplier:id,nama_supplier',
                'currency:id,kode_currency,simbol',
                'pajak:id,nama_pajak,tarif',
                'tender:id,nomor_kontrak,nama_tender',
                'creator:id,nama_lengkap',
                'approver:id,nama_lengkap',
                'pr:id,nomor_pr,diminta_oleh',
                'pr.requester:id,nama_lengkap',
                'details.barang.satuan',
            ]);

            if (!empty($activeBranchId)) {
                $query->where('branch_id', intval($activeBranchId));
            } else {
                $query->whereNull('branch_id');
                if ($activeCompanyId) {
                    $query->where('company_id', $activeCompanyId);
                }
            }
            $query->whereBetween('tanggal_po', [$start->toDateString(), $end->toDateString()]);

            $userId = auth()->id();
            $user   = auth()->user();
            if ($user->can('lihat-po') || $user->can('approve-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting'])) {
                $query->where(function($q) use ($userId) {
                    $q->where('status', '!=', 'Draft')
                      ->orWhere('created_by', $userId);
                });
            } else {
                $query->where('created_by', $userId);
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            if (!empty($tenderId)) {
                if ($tenderId === 'non_tender') {
                    $query->whereNull('tender_id');
                } else {
                    $query->where('tender_id', $tenderId);
                }
            }

            if (!empty($currencyId)) {
                $query->where('currency_id', $currencyId);
            }

            $pos = $query->orderBy('tanggal_po', 'asc')->get();

            if ($pos->isEmpty()) {
                return response("<script>alert('Tidak ada data PO yang ditemukan pada rentang tanggal tersebut.'); history.back();</script>");
            }

            $headers = [
                'No',
                'Nomor PO',
                'Tanggal PO',
                'Supplier',
                'Tender / Project',
                'Sumber PR',
                'Jumlah Item',
                'Rincian Barang',
                'Total Qty',
                'Subtotal PO',
                'Diskon Barang',
                'Diskon Header (PO)',
                'Total Diskon PO',
                'Biaya Lain-Lain',
                'DPP (Dasar Pengenaan Pajak)',
                'PPN (Pajak) PO',
                'Grand Total PO',
                'Down Payment (DP)',
                'Sisa Pembayaran',
                'Mata Uang',
                'Kurs Rupiah',
                'Grand Total (Rupiah)',
                'Status',
                'Dibuat Oleh',
                'Disetujui Oleh',
                'Catatan / Keterangan PO'
            ];

            $rows = [$headers];
            $no = 1;

            $sumItemCount      = 0;
            $sumTotalQty       = 0;
            $sumGrossSubtotal  = 0;
            $sumItemDiskon     = 0;
            $sumHeaderDiskon   = 0;
            $sumTotalDiskon    = 0;
            $sumDpp            = 0;
            $sumPajak          = 0;
            $sumBiayaLain      = 0;
            $sumGrandTotal     = 0;
            $sumDp             = 0;
            $sumSisaBayar      = 0;
            $sumGrandTotalIdr  = 0;

            $fmtMoney = function($val, $sym) {
                $num = floatval($val);
                if ($sym === '$') {
                    return '$ ' . number_format($num, 2, '.', ',');
                } elseif ($sym === 'Rp') {
                    return 'Rp ' . number_format($num, 2, ',', '.');
                } else {
                    return $sym . ' ' . number_format($num, 2, ',', '.');
                }
            };

            foreach ($pos as $po) {
                $tenderFmt = $po->tender ? $po->tender->nama_tender . ' (' . ($po->tender->nomor_kontrak ?: '-') . ')' : '-';
                $prFmt = $po->pr ? ($po->pr->nomor_pr . ($po->pr->requester ? ' (' . $po->pr->requester->nama_lengkap . ')' : '')) : ($po->manual_pr_number ? $po->manual_pr_number . ' (Manual)' : 'Manual');
                
                $itemNames = [];
                $totalQtyPo = 0;
                $itemDiskon = 0;
                if ($po->details && $po->details->count() > 0) {
                    foreach ($po->details as $d) {
                        $bName = $d->barang->nama_barang ?? 'Barang';
                        $sat = $d->barang->satuan->nama_satuan ?? '';
                        $qty = (float) $d->jumlah;
                        $totalQtyPo += $qty;
                        $itemDiskon += (float) $d->diskon_item * $qty;
                        $itemNames[] = $bName . ' (' . number_format($qty, 0, ',', '.') . ($sat ? ' ' . $sat : '') . ')';
                    }
                }

                $itemSummaryStr = !empty($itemNames) ? implode(' | ', $itemNames) : '-';

                $savedTotalDiskon = (float) $po->total_diskon;
                $headerDiskon     = max(0, $savedTotalDiskon - $itemDiskon);
                $headerPersen     = (float) $po->diskon_persen;
                $totalDiskon      = max($savedTotalDiskon, $itemDiskon);
                $subtotalGross    = (float) $po->subtotal + $totalDiskon;
                $biayaLain     = (float) $po->biaya_lain;
                $dpp           = max(0, $subtotalGross - $totalDiskon);
                $pajak         = (float) $po->total_pajak;
                $grandTotal    = (float) $po->grand_total;
                $dp            = (float) $po->down_payment;
                $sisaBayar     = max(0, $grandTotal - $dp);
                $kurs          = (float) $po->kurs;
                $gtIdr         = (float) $po->grand_total_idr;
                $itemCount     = $po->details ? $po->details->count() : 0;

                // Accumulate totals
                $sumItemCount      += $itemCount;
                $sumTotalQty       += $totalQtyPo;
                $sumGrossSubtotal  += $subtotalGross;
                $sumItemDiskon     += $itemDiskon;
                $sumHeaderDiskon   += $headerDiskon;
                $sumTotalDiskon    += $totalDiskon;
                $sumDpp            += $dpp;
                $sumPajak          += $pajak;
                $sumBiayaLain      += $biayaLain;
                $sumGrandTotal     += $grandTotal;
                $sumDp             += $dp;
                $sumSisaBayar      += $sisaBayar;
                $sumGrandTotalIdr  += $gtIdr;

                $currCode = strtoupper($po->currency->kode_currency ?? 'IDR');
                $currSimbol = $po->currency->simbol ?? '';

                if ($currCode === 'USD' || strpos($currCode, 'USD') !== false || strpos($currCode, 'DOLAR') !== false || strpos($currCode, 'DOLLAR') !== false) {
                    $sym = '$';
                } elseif ($currCode === 'IDR' || strpos($currCode, 'RUPIAH') !== false) {
                    $sym = 'Rp';
                } elseif (!empty($currSimbol)) {
                    $sym = $currSimbol;
                } else {
                    $sym = $currCode;
                }

                // Format Diskon Barang (Item)
                $fmtItemDiskon = '-';
                if ($itemDiskon > 0) {
                    $itemPct = ($subtotalGross > 0) ? round(($itemDiskon / $subtotalGross) * 100, 2) : 0;
                    $fmtItemPct = ($itemPct == intval($itemPct)) ? intval($itemPct) . '%' : rtrim(rtrim(number_format($itemPct, 2, ',', '.'), '0'), ',') . '%';
                    $fmtItemDiskon = $fmtMoney($itemDiskon, $sym) . ($itemPct > 0 ? ' (' . $fmtItemPct . ')' : '');
                }

                // Format Diskon Header (PO)
                $fmtHeaderDiskon = '-';
                if ($headerDiskon > 0) {
                    $fmtHeaderPct = ($headerPersen == intval($headerPersen)) ? intval($headerPersen) . '%' : rtrim(rtrim(number_format($headerPersen, 2, ',', '.'), '0'), ',') . '%';
                    $fmtHeaderDiskon = $fmtMoney($headerDiskon, $sym) . ($headerPersen > 0 ? ' (' . $fmtHeaderPct . ')' : '');
                }

                // Format Total Diskon PO
                $fmtTotalDiskon = $totalDiskon > 0 ? $fmtMoney($totalDiskon, $sym) : '-';

                $rows[] = [
                    $no++,
                    $po->nomor_po,
                    $po->tanggal_po ? $po->tanggal_po->format('d-m-Y') : '-',
                    $po->supplier->nama_supplier ?? '-',
                    $tenderFmt,
                    $prFmt,
                    $itemCount,
                    $itemSummaryStr,
                    (float) $totalQtyPo,
                    $fmtMoney($subtotalGross, $sym),
                    $fmtItemDiskon,
                    $fmtHeaderDiskon,
                    $fmtTotalDiskon,
                    $fmtMoney($biayaLain, $sym),
                    $fmtMoney($dpp, $sym),
                    $fmtMoney($pajak, $sym),
                    $fmtMoney($grandTotal, $sym),
                    $fmtMoney($dp, $sym),
                    $fmtMoney($sisaBayar, $sym),
                    $currCode,
                    'Rp ' . number_format($kurs, ($kurs == round($kurs, 2) ? 2 : 4), ',', '.'),
                    'Rp ' . number_format($gtIdr, 2, ',', '.'),
                    $po->status,
                    $po->creator->nama_lengkap ?? '-',
                    $po->approver->nama_lengkap ?? '-',
                    $po->keterangan ?? '-'
                ];
            }

            // Separator empty row
            $rows[] = array_fill(0, count($headers), '');

            $allCurrencies = $pos->map(fn($p) => strtoupper($p->currency->kode_currency ?? 'IDR'))->unique();
            $isSingleCurrency = ($allCurrencies->count() === 1);
            $firstPo = $pos->first();
            $summarySymbol = 'Rp';
            if ($isSingleCurrency && $firstPo && $firstPo->currency) {
                $cCode = strtoupper($firstPo->currency->kode_currency ?? '');
                $cSimbol = $firstPo->currency->simbol ?? '';
                if ($cCode === 'USD' || strpos($cCode, 'USD') !== false || strpos($cCode, 'DOLAR') !== false || strpos($cCode, 'DOLLAR') !== false) {
                    $summarySymbol = '$';
                } elseif ($cCode === 'IDR' || strpos($cCode, 'RUPIAH') !== false) {
                    $summarySymbol = 'Rp';
                } elseif (!empty($cSimbol)) {
                    $summarySymbol = $cSimbol;
                } else {
                    $summarySymbol = $cCode;
                }
            }

            // TOTAL KESELURUHAN Summary Row
            $rows[] = [
                'TOTAL KESELURUHAN',
                '',
                '',
                '',
                '',
                '',
                (int) $sumItemCount,
                '',
                (float) $sumTotalQty,
                $fmtMoney($sumGrossSubtotal, $summarySymbol),
                $fmtMoney($sumItemDiskon, $summarySymbol),
                $fmtMoney($sumHeaderDiskon, $summarySymbol),
                $fmtMoney($sumTotalDiskon, $summarySymbol),
                $fmtMoney($sumBiayaLain, $summarySymbol),
                $fmtMoney($sumDpp, $summarySymbol),
                $fmtMoney($sumPajak, $summarySymbol),
                $fmtMoney($sumGrandTotal, $summarySymbol),
                $fmtMoney($sumDp, $summarySymbol),
                $fmtMoney($sumSisaBayar, $summarySymbol),
                $isSingleCurrency ? $allCurrencies->first() : 'CAMPURAN',
                '-',
                'Rp ' . number_format($sumGrandTotalIdr, 2, ',', '.'),
                '',
                '',
                '',
                ''
            ];

            $filename = 'Export_PO_' . date('Ymd', strtotime($tglMulai)) . '_sd_' . date('Ymd', strtotime($tglSelesai)) . '.xlsx';
            if (ob_get_length()) {
                ob_end_clean();
            }
            $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($rows);
            $xlsx->downloadAs($filename);
            exit();

        } catch (\Exception $e) {
            Log::error('Error Export PO: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses export data PO.'
            ], 500);
        }
    }

    /**
     * AJAX: Search active barang for the item picker popup.
     * Replaces the old @json($barang) global data injection.
     */
    public function searchBarang(Request $request)
    {
        $this->authorize('lihat-po');

        $q = trim($request->input('q', ''));

        $barang = MasterBarang::forActiveCompany()
            ->with([
                'satuan:id,nama_satuan,singkatan',
                'konversiSatuan.satuan:id,nama_satuan,singkatan',
                'primaryFoto:id,barang_id,foto_path',
                'fotos:id,barang_id,foto_path'
            ])
            ->active()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_barang', 'like', "%{$q}%")
                        ->orWhere('kode_barang', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_barang')
            ->limit(50)
            ->get(['id', 'kode_barang', 'nama_barang', 'tipe_item', 'satuan_id']);

        return response()->json($barang->map(function ($b) {
            $altUnits = [];
            if ($b->konversiSatuan) {
                foreach ($b->konversiSatuan as $konv) {
                    if ($konv->satuan) {
                        $altUnits[] = [
                            'satuan_id'      => $konv->satuan->id,
                            'nama_satuan'    => $konv->satuan->singkatan ?: $konv->satuan->nama_satuan,
                            'faktor_pengali' => floatval($konv->faktor_pengali),
                        ];
                    }
                }
            }

            return [
                'id'              => $b->id,
                'kode_barang'     => $b->kode_barang,
                'nama_barang'     => $b->nama_barang,
                'tipe_item'       => $b->tipe_item,
                'satuan'          => $b->satuan ? ($b->satuan->singkatan ?: $b->satuan->nama_satuan) : '-',
                'satuan_dasar'    => $b->satuan ? ($b->satuan->singkatan ?: $b->satuan->nama_satuan) : '-',
                'satuan_id'       => $b->satuan_id,
                'alternate_units' => $altUnits,
                'foto_url'        => $b->primaryFoto ? asset('storage/' . $b->primaryFoto->foto_path) : null,
                'fotos'           => $b->fotos->map(fn($f) => asset('storage/' . $f->foto_path))->toArray(),
            ];
        }));
    }

    /**
     * AJAX: Search suppliers by name or code for popup picker.
     */
    public function searchSupplier(Request $request)
    {
        $this->authorize('lihat-po');

        $q = trim($request->input('q', ''));

        $suppliers = MasterSupplier::query()
            ->forActiveCompany()
            ->where('status', 'Active')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_supplier', 'like', "%{$q}%")
                        ->orWhere('kode_supplier', 'like', "%{$q}%")
                        ->orWhere('kota', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_supplier')
            ->limit(20)
            ->get(['id', 'kode_supplier', 'nama_supplier', 'kota', 'telepon', 'status']);

        return response()->json($suppliers);
    }

    /**
     * AJAX: Search approved PRs (with remaining qty) for popup picker.
     */
    public function searchPr(Request $request)
    {
        $this->authorize('lihat-po');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        $q               = trim($request->input('q', ''));

        $prs = PurchaseRequisition::forActiveCompany()
            ->forActiveBranch()
            ->with(['department', 'requester'])
            ->where('status', 'Approved')
            ->whereHas('details', function ($query) {
                $query->whereRaw('jumlah > (select coalesce(sum(pod.jumlah), 0) from purchase_order_details pod join purchase_orders po on po.id = pod.po_id where pod.pr_detail_id = purchase_requisition_details.id and po.status != "Rejected" and po.deleted_at is null)');
            })
            ->when(!empty($q), function ($query) use ($q) {
                $query->where(function($q2) use ($q) {
                    $q2->where('nomor_pr', 'like', "%{$q}%")
                       ->orWhereHas('department', fn($d) => $d->where('department_name', 'like', "%{$q}%"))
                       ->orWhereHas('requester', fn($r) => $r->where('nama_lengkap', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->limit(20)
            ->get();

        $result = $prs->map(fn($pr) => [
            'id'           => $pr->id,
            'nomor_pr'     => $pr->nomor_pr,
            'tanggal_pr'   => $pr->tanggal_pr ? $pr->tanggal_pr->format('d/m/Y') : '-',
            'department'   => optional($pr->department)->department_name ?? '-',
            'requester'    => optional($pr->requester)->nama_lengkap ?? '-',
        ]);

        return response()->json($result);
    }

    /**
     * AJAX: Search active currencies by code or name for popup picker.
     */
    public function searchCurrency(Request $request)
    {
        $this->authorize('lihat-po');

        $q = trim($request->input('q', ''));

        $currencies = MasterCurrency::query()
            ->forActiveCompany()
            ->active()
            ->when($q, function ($query) use ($q) {
                $query->where('kode_currency', 'like', "%{$q}%")
                      ->orWhere('nama_currency', 'like', "%{$q}%");
            })
            ->orderBy('kode_currency')
            ->get(['id', 'kode_currency', 'nama_currency', 'simbol']);

        return response()->json($currencies);
    }

    public function searchTender(Request $request)
    {
        $this->authorize('lihat-po');

        $q = trim($request->get('q', ''));
        $page = (int) $request->get('page', 1);
        if ($page < 1) $page = 1;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $tendersQuery = \App\Models\Tender::with('customer:id,nama_customer,nama_perusahaan')
            ->forActiveCompany()
            ->whereNotIn(DB::raw('LOWER(status)'), ['draft', 'lost', 'rejected', 'kalah'])
            ->where(function($query) {
                $query->whereNull('tahap_tender')
                      ->orWhereNotIn(DB::raw('LOWER(tahap_tender)'), ['kalah lelang (lost)', 'ditolak', 'draft']);
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_tender', 'like', "%{$q}%")
                        ->orWhere('nomor_kontrak', 'like', "%{$q}%")
                        ->orWhereHas('customer', fn($c) => $c->where('nama_customer', 'like', "%{$q}%")
                            ->orWhere('nama_perusahaan', 'like', "%{$q}%"));
                });
            });

        // Fetch perPage + 1 items to determine if a next page exists
        $tenders = $tendersQuery->orderBy('nama_tender')
            ->offset($offset)
            ->limit($perPage + 1)
            ->get();

        $hasMore = false;
        if ($tenders->count() > $perPage) {
            $hasMore = true;
            $tenders->pop();
        }

        $items = $tenders->map(function ($t) {
            $customerName = '-';
            if ($t->customer) {
                $customerName = $t->customer->nama_perusahaan ?: $t->customer->nama_customer;
            }
            return [
                'id' => $t->id,
                'nama_tender' => $t->nama_tender,
                'nomor_kontrak' => $t->nomor_kontrak ?? '-',
                'customer' => $customerName,
                'nilai_estimasi' => number_format($t->nilai_estimasi, 0, ',', '.'),
                'currency' => $t->currency ?? 'IDR',
                'status' => $t->status ?? 'Active',
            ];
        });

        return response()->json([
            'items' => $items,
            'current_page' => $page,
            'has_more' => $hasMore
        ]);
    }

    public function getPrDetails(PurchaseRequisition $pr)
    {
        $this->authorize('lihat-po');

        $user         = auth()->user();
        $isPrivileged = $user->can('lihat-po') || $user->can('approve-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting']);
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if (!empty($activeBranchId)) {
            if (!$isPrivileged && strval($activeBranchId) !== strval($pr->branch_id)) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
            }
        } else {
            if (!$isPrivileged && !empty($pr->branch_id)) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
            }
            if ($activeCompanyId && $pr->company_id && $pr->company_id != $activeCompanyId) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
            }
        }

        if (!in_array($pr->status, ['Approved', 'Closed'])) {
            return response()->json(['message' => 'Hanya pengajuan pembelian yang sudah disetujui (Approved) yang dapat diproses ke Purchase Order.'], 422);
        }

        // Fix N+1: Load all details with relations, then aggregate ordered qty in ONE query
        $details = $pr->details()->with(['barang.satuan', 'barang.primaryFoto', 'barang.fotos', 'satuan'])->get();

        $detailIds = $details->pluck('id');
        $orderedMap = PurchaseOrderDetail::whereIn('pr_detail_id', $detailIds)
            ->whereHas('po', function ($q) {
                $q->where('status', '!=', 'Rejected');
            })
            ->selectRaw('pr_detail_id, SUM(jumlah) as total_ordered')
            ->groupBy('pr_detail_id')
            ->pluck('total_ordered', 'pr_detail_id');

        $items = [];
        foreach ($details as $detail) {
            $ordered   = (float) ($orderedMap[$detail->id] ?? 0);
            $remaining = $detail->jumlah - $ordered;

            if ($remaining > 0 && $detail->barang) {
                $satuanId = $detail->satuan_id ?? $detail->barang->satuan_id;
                $satuanNama = $detail->satuan?->singkatan ?? $detail->satuan?->nama_satuan ?? ($detail->barang->satuan?->singkatan ?? $detail->barang->satuan?->nama_satuan ?? '-');
                $faktorPengali = floatval($detail->faktor_pengali ?? 1);
                $satuanDasarNama = $detail->barang->satuan?->singkatan ?? $detail->barang->satuan?->nama_satuan ?? '-';

                $items[] = [
                    'pr_detail_id'   => $detail->id,
                    'barang_id'      => $detail->barang_id,
                    'nama_barang'    => $detail->barang->nama_barang,
                    'kode_barang'    => $detail->barang->kode_barang,
                    'satuan'         => $satuanNama,
                    'satuan_id'      => $satuanId,
                    'faktor_pengali' => $faktorPengali,
                    'satuan_dasar'   => $satuanDasarNama,
                    'foto'           => $detail->barang->primaryFoto ? asset('storage/' . $detail->barang->primaryFoto->foto_path) : null,
                    'fotos'          => $detail->barang->fotos ? $detail->barang->fotos->map(fn($f) => asset('storage/' . $f->foto_path))->toArray() : [],
                    'qty_pr'         => floatval($detail->jumlah),
                    'qty_ordered'    => floatval($ordered),
                    'qty_remaining'  => floatval($remaining),
                    'harga_estimasi' => floatval($detail->harga_estimasi),
                    'keterangan'     => $detail->keterangan ?? '-',
                    'tipe_item'      => $detail->barang->tipe_item ?? 'Stok',
                ];
            }
        }

        return response()->json([
            'pr'    => $pr,
            'items' => $items,
        ]);
    }
    public function store(Request $request)
    {
        $this->authorize('tambah-po');

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');
        $user            = auth()->user();

        Log::info('Store PO validation start');
        try {
            $request->validate([
                'tanggal_po'       => 'required|date',
                'supplier_id'      => 'required|exists:master_suppliers,id',
                'currency_id'      => 'required|exists:master_currencies,id',
                'kurs'             => 'required|numeric|min:0',
                'pajak_id'         => 'nullable',
                'pph_pajak_id'     => 'nullable|exists:master_pajaks,id',
                'tarif_pph'        => 'nullable|numeric|min:0|max:100',
                'pph_nominal'      => 'nullable|numeric|min:0',
                'pph_coa_id'       => 'nullable|exists:chart_of_accounts,id',
                'payment_term_id'  => 'nullable|exists:payment_terms,id',
                'payment_method_id'=> 'nullable|exists:payment_methods,id',
                'tanggal_pengiriman'=> 'nullable|date',
                'pr_id'            => 'nullable|exists:purchase_requisitions,id',
                'tender_id'        => 'nullable|exists:tenders,id',
                'diskon_persen'    => 'nullable|numeric|min:0|max:100',
                'diskon_nominal'   => 'nullable|numeric|min:0',
                'down_payment'     => 'nullable|numeric|min:0',
                'biaya_lain'       => 'nullable|numeric|min:0',
                'deskripsi_biaya_lain' => 'nullable|string|max:255',
                'alokasi_biaya_lain' => 'nullable|string|in:Beban,HPP',
                'keterangan'       => 'nullable|string|max:40',
                'nomor_po'         => 'nullable|required_if:auto_number,0|string|max:15',
                'items'            => 'required|array|min:1|max:25',
                'items.*.barang_id' => 'required|exists:master_barang,id',
                'items.*.satuan_id' => 'nullable|exists:master_satuan,id',
                'items.*.faktor_pengali' => 'nullable|numeric|min:0.0001',
                'items.*.pr_detail_id' => 'nullable|exists:purchase_requisition_details,id',
                'items.*.jumlah'    => 'required|numeric|min:0.0001',
                'items.*.harga_satuan' => 'required|numeric|min:0.01',
                'items.*.diskon_item' => 'nullable|numeric|min:0',
                'items.*.diskon_persen' => 'nullable|numeric|min:0|max:100',
                'items.*.diskon_tipe' => 'nullable|string|in:percent,nominal',
                'items.*.ppn_pajak_id' => 'nullable|exists:master_pajaks,id',
                'items.*.tarif_ppn' => 'nullable|numeric|min:0',
                'items.*.keterangan' => 'nullable|string|max:255',
                'expense_items'     => 'nullable|array|max:5',
            ], [
                'nomor_po.max'     => 'Nomor PO tidak boleh melebihi 15 karakter yang telah ditentukan.',
                'keterangan.max'   => 'Catatan / Keterangan PO tidak boleh melebihi 40 karakter.',
                'items.max'        => 'Jumlah barang dalam 1 Purchase Order (PO) tidak boleh melebihi 25 item!',
                'items.min'        => 'Purchase Order (PO) minimal harus memiliki 1 item barang.',
                'expense_items.max'=> 'Rincian biaya lain-lain dibatasi maksimal 5 jenis biaya.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('PO Validation Errors: ', $e->errors());
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors' => $e->errors()
            ], 422);
        }

        Log::info('Store PO validation passed');

        // Validate PO Number uniqueness if manual
        if (!$request->auto_number) {
            $exists = PurchaseOrder::where('nomor_po', $request->nomor_po)->exists();
            if ($exists) {
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors' => ['nomor_po' => ['Nomor PO tersebut sudah terdaftar di sistem!']]
                ], 422);
            }
        }

        // Validate all items belong to active company and are active
        $submittedBarangIds = collect($request->items)->pluck('barang_id')->unique()->toArray();
        $validBarangCount = MasterBarang::forActiveCompany()
            ->whereIn('id', $submittedBarangIds)
            ->where('status', 'Active')
            ->count();

        if ($validBarangCount !== count($submittedBarangIds)) {
            return response()->json([
                'message' => 'Satu atau lebih barang yang dipilih tidak valid, tidak aktif, atau bukan milik perusahaan Anda!'
            ], 422);
        }

        // Validate remaining PR quantity limits if PO is tied to a PR
        if ($request->pr_id) {
            $prCheck = PurchaseRequisition::find($request->pr_id);
            if (!$prCheck || ($activeCompanyId && $prCheck->company_id && $prCheck->company_id != $activeCompanyId) || strval($prCheck->branch_id) !== strval(session('active_branch_id'))) {
                return response()->json(['message' => 'Referensi PR tidak valid atau milik cabang lain / pusat!'], 403);
            }
            if (!in_array($prCheck->status, ['Approved', 'Closed'])) {
                return response()->json(['message' => 'Hanya PR yang sudah disetujui (Approved) yang dapat diproses menjadi Purchase Order!'], 422);
            }

            foreach ($request->items as $item) {
                if (!empty($item['pr_detail_id'])) {
                    $ordered = PurchaseOrderDetail::where('pr_detail_id', $item['pr_detail_id'])
                        ->whereHas('po', function ($q) {
                            $q->where('status', '!=', 'Rejected');
                        })
                        ->sum('jumlah');
                    $prDetail = PurchaseRequisitionDetail::find($item['pr_detail_id']);
                    if ($prDetail) {
                        $remaining = $prDetail->jumlah - $ordered;
                        if ($item['jumlah'] > $remaining) {
                            return response()->json([
                                'message' => "Jumlah item {$prDetail->barang->nama_barang} ({$item['jumlah']}) melebihi sisa pengajuan PR ({$remaining})!"
                            ], 422);
                        }
                    }
                }
            }
        }

        Log::info('Store PO transaction starting');

        try {
            DB::beginTransaction();

            $activeBranchId  = session('active_branch_id');
            $activeCompanyId = session('active_company_id');

            $targetBranchId  = !empty($activeBranchId) ? $activeBranchId : null;
            $targetCompanyId = !empty($activeBranchId) ? null : ($activeCompanyId ?: 1);

            // Force IDR currency rate to 1.00 regardless of user input; validate non-IDR
            $kurs = round(floatval($request->kurs), 4); // kolom kurs decimal(16,4)
            $currency = MasterCurrency::find($request->currency_id);
            if ($currency && $currency->kode_currency === 'IDR') {
                $kurs = 1.00;
            } else {
                if ($kurs <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Validasi gagal!',
                        'errors' => ['kurs' => ['Nilai kurs untuk mata uang asing (' . ($currency?->kode_currency ?? '') . ') harus lebih besar dari 0!']]
                    ], 422);
                }
            }

            // Generate auto PO number if requested — uses lockForUpdate() to prevent race condition
            if ($request->auto_number) {
                $year = date('Y', strtotime($request->tanggal_po));
                $month = date('m', strtotime($request->tanggal_po));
                $prefix = "PO/{$year}/{$month}/";
                
                $prefixLen = strlen($prefix);
                $allPos = PurchaseOrder::where('nomor_po', 'like', $prefix . '%')
                    ->lockForUpdate()
                    ->pluck('nomor_po');

                $maxNum = 0;
                foreach ($allPos as $codeStr) {
                    $suffix = substr($codeStr, $prefixLen);
                    if (is_numeric($suffix)) {
                        $val = (int) $suffix;
                        if ($val > $maxNum) {
                            $maxNum = $val;
                        }
                    }
                }

                $num = $maxNum + 1;
                $nomorPo = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);

                while (PurchaseOrder::where('nomor_po', $nomorPo)->exists()) {
                    $num++;
                    $nomorPo = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
                }
            } else {
                $nomorPo = $request->nomor_po;
            }

            // Process per-item calculations (gross, discount, and tax per item)
            $subtotalGross = 0;
            $totalDiskon = 0;
            $totalPajak = 0;
            $itemsData = [];

            // Pre-load tax rates from master (source of truth, scoped to active company)
            $allTaxMap = MasterPajak::forActiveCompany()->pluck('tarif', 'id')->toArray();

            foreach ($request->items as $item) {
                // Samakan presisi dengan kolom DB agar harga x qty = subtotal yang tersimpan
                $qty = round(floatval($item['jumlah']), 4);          // decimal(18,4)
                $hargaSatuan = round(floatval($item['harga_satuan']), 2); // decimal(20,2)
                $diskonTipe = ($item['diskon_tipe'] ?? 'percent') === 'nominal' ? 'nominal' : 'percent';
                $diskonPersen = round(floatval($item['diskon_persen'] ?? 0), 2); // decimal(5,2)
                $diskonItem = round(floatval($item['diskon_item'] ?? 0), 2);

                if ($diskonTipe === 'percent') {
                    if ($diskonPersen > 100) $diskonPersen = 100;
                    $diskonUnit = round($hargaSatuan * ($diskonPersen / 100), 2);
                } else {
                    if ($diskonItem > $hargaSatuan) $diskonItem = $hargaSatuan;
                    $diskonUnit = $diskonItem;
                    $diskonPersen = $hargaSatuan > 0 ? round(($diskonUnit / $hargaSatuan) * 100, 2) : 0;
                }

                $lineGross = round($qty * $hargaSatuan, 2);
                $lineDiskon = round($qty * $diskonUnit, 2);
                $lineSubtotal = max(0, round($lineGross - $lineDiskon, 2));

                // Per-item PPN
                $ppnPajakId = !empty($item['ppn_pajak_id']) ? intval($item['ppn_pajak_id']) : null;
                // Tarif SELALU dari master pajak (jangan percaya nilai dari client)
                if ($ppnPajakId && !isset($allTaxMap[$ppnPajakId])) {
                    $ppnPajakId = null; // pajak bukan milik company aktif
                }
                $tarifPpn = $ppnPajakId ? floatval($allTaxMap[$ppnPajakId]) : 0;
                $ppnNominal = ($ppnPajakId && $tarifPpn > 0) ? round($lineSubtotal * ($tarifPpn / 100), 2) : 0;

                $satuanId = $item['satuan_id'] ?? null;
                $faktorPengali = floatval($item['faktor_pengali'] ?? 1);
                if (!$satuanId) {
                    $barang = MasterBarang::find($item['barang_id']);
                    $satuanId = $barang?->satuan_id;
                }
                $jumlahDasar = $qty * ($faktorPengali ?: 1);

                $subtotalGross += $lineGross;
                $totalDiskon += $lineDiskon;
                $totalPajak += $ppnNominal;

                $itemsData[] = [
                    'barang_id'      => $item['barang_id'],
                    'satuan_id'      => $satuanId,
                    'pr_detail_id'   => $item['pr_detail_id'] ?? null,
                    'jumlah'         => $qty,
                    'faktor_pengali' => $faktorPengali,
                    'jumlah_dasar'   => $jumlahDasar,
                    'harga_satuan'   => $hargaSatuan,
                    'diskon_item'    => $diskonUnit,
                    'diskon_persen'  => $diskonPersen,
                    'diskon_tipe'    => $diskonTipe,
                    'subtotal'       => $lineSubtotal,
                    'ppn_pajak_id'   => $ppnPajakId,
                    'tarif_ppn'      => $tarifPpn,
                    'ppn_nominal'    => $ppnNominal,
                    'keterangan'     => $item['keterangan'] ?? null,
                ];
            }

            $subtotalGross = round($subtotalGross, 2);
            $totalDiskon = round($totalDiskon, 2);
            $totalPajak = round($totalPajak, 2);

            // DPP = Subtotal Barang Kotor - Total Diskon Barang
            $dpp = round(max(0, $subtotalGross - $totalDiskon), 2);

            $expenseItems = $request->input('expense_items', []);
            $biayaLain = 0;
            if (!empty($expenseItems) && is_array($expenseItems)) {
                foreach ($expenseItems as $idx => $exp) {
                    $nom = round(floatval($exp['nominal'] ?? 0), 2);
                    if ($nom < 0) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Validasi gagal!',
                            'errors' => ['expense_items' => ['Nominal biaya lain-lain tidak boleh negatif!']]
                        ], 422);
                    }
                    $expenseItems[$idx]['nominal'] = $nom;
                    $biayaLain += $nom;
                }
            } else {
                $biayaLain = max(0, floatval($request->input('biaya_lain', 0)));
            }
            $biayaLain = round($biayaLain, 2);

            // Calculate PPh (Withholding Tax - Potongan Pajak Penghasilan)
            $pphPajakId = $request->filled('pph_pajak_id') ? (int) $request->pph_pajak_id : null;
            $tarifPph   = 0.0;
            $pphNominal = 0.0;
            $pphCoaId   = $request->filled('pph_coa_id') ? (int) $request->pph_coa_id : null;

            if ($pphPajakId && isset($allTaxMap[$pphPajakId])) {
                $tarifPph = floatval($allTaxMap[$pphPajakId]);
                $pphNominal = round($dpp * ($tarifPph / 100), 2);
                if (!$pphCoaId) {
                    $pphCoaId = \App\Helpers\AccountMappingHelper::getCoaId('tax', "tax_{$pphPajakId}_in") ?? $this->resolveDefaultPphCoaId();
                }
            } else {
                $pphPajakId = null;
                $tarifPph   = 0.0;
                $pphNominal = 0.0;
                $pphCoaId   = null;
            }

            // Grand Total = DPP + Total PPN + Biaya Lain-Lain - Potongan PPh
            $grandTotal = round(max(0, $dpp + $totalPajak + $biayaLain - $pphNominal), 2);
            $downPayment = round(floatval($request->input('down_payment', 0)), 2);

            // Validate down payment doesn't exceed grand total
            if ($downPayment > $grandTotal) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors' => ['down_payment' => ['Nilai uang muka (DP) tidak boleh melebihi Grand Total!']]
                ], 422);
            }

            $grandTotalIdr = round($grandTotal * $kurs, 2);

            // Create Header (No global discount, no global tax)
            $po = PurchaseOrder::create([
                'nomor_po'          => $nomorPo,
                'tanggal_po'        => $request->tanggal_po,
                'company_id'        => $targetCompanyId,
                'branch_id'         => $targetBranchId,
                'supplier_id'       => $request->supplier_id,
                'currency_id'       => $request->currency_id,
                'kurs'              => $kurs,
                'pajak_id'          => null,
                'payment_term_id'   => $request->payment_term_id ?: null,
                'payment_method_id' => $request->payment_method_id ?: null,
                'tanggal_pengiriman'=> $request->tanggal_pengiriman ?: null,
                'pr_id'             => $request->pr_id ?: null,
                'tender_id'         => $request->tender_id ?: null,
                'manual_pr_number'  => $request->pr_id ? null : $request->manual_pr_number,
                'diskon_persen'     => 0,
                'diskon_nominal'    => 0,
                'subtotal'          => $subtotalGross,
                'total_diskon'      => $totalDiskon,
                'total_pajak'       => $totalPajak,
                'pph_pajak_id'      => $pphPajakId,
                'tarif_pph'         => $tarifPph,
                'pph_nominal'       => $pphNominal,
                'total_pph'         => $pphNominal,
                'pph_coa_id'        => $pphCoaId,
                'grand_total'       => $grandTotal,
                'grand_total_idr'   => $grandTotalIdr,
                'down_payment'      => $downPayment,
                'biaya_lain'        => $biayaLain,
                'biaya_lain_details'=> !empty($expenseItems) ? $expenseItems : null,
                'deskripsi_biaya_lain' => $request->input('deskripsi_biaya_lain'),
                'alokasi_biaya_lain'   => $request->input('alokasi_biaya_lain', 'Beban'),
                'keterangan'        => $request->keterangan,
                'status'            => $request->boolean('is_draft') ? 'Draft' : 'Pending',
            ]);

            // Save details
            foreach ($itemsData as $data) {
                $po->details()->create($data);
            }

            // Re-evaluate PR status
            $this->reevaluatePrStatus($request->pr_id);

            DB::commit();

            if ($po->status === 'Pending') {
                \App\Http\Controllers\WhatsAppGatewayController::runAsync(function () use ($po) {
                    \App\Http\Controllers\WhatsAppGatewayController::sendPoApprovalNotification($po);
                });
            }

            $msg = $request->boolean('is_draft') 
                ? 'Purchase Order berhasil disimpan sebagai Draft!' 
                : 'Purchase Order berhasil dibuat dan diajukan untuk persetujuan!';
            return response()->json(['message' => $msg, 'po' => $po]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store PO: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat membuat PO.'], 500);
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('lihat-po');

        $user         = auth()->user();
        $isPrivileged = $user->can('lihat-po') || $user->can('approve-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting']);
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $purchaseOrder->company_id && $purchaseOrder->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseOrder->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if ($purchaseOrder->status === 'Draft' && $purchaseOrder->created_by !== $user->id) {
            return response()->json(['message' => 'Draft Purchase Order ini bersifat privat dan hanya dapat dilihat oleh akun pembuatnya.'], 403);
        }
        
        $purchaseOrder->load([
            'company', 'supplier', 'currency', 'pajak', 'pphPajak', 'pphCoa', 'pr.requester', 'tender.customer',
            'creator', 'updater', 'approver', 'branch', 'paymentTerm', 'paymentMethod',
            'details.barang.satuan', 'details.barang.primaryFoto', 'details.barang.fotos', 'details.satuan', 'details.ppnPajak',
        ]);

        // Resolve biaya_lain_details: enrich with jenis biaya name
        $biayaDetails = $purchaseOrder->biaya_lain_details;
        if (!empty($biayaDetails) && is_array($biayaDetails)) {
            $ids = collect($biayaDetails)->pluck('master_jenis_biaya_id')->filter()->unique()->values();
            $jenisBiayaMap = \App\Models\MasterJenisBiaya::whereIn('id', $ids)
                ->get(['id', 'nama_jenis_biaya', 'kode_biaya', 'masuk_hpp'])
                ->keyBy('id');

            $biayaDetails = array_map(function ($item) use ($jenisBiayaMap) {
                $jbId = $item['master_jenis_biaya_id'] ?? null;
                if ($jbId && isset($jenisBiayaMap[$jbId])) {
                    $jb = $jenisBiayaMap[$jbId];
                    $item['nama_jenis_biaya'] = $jb->nama_jenis_biaya;
                    $item['kode_biaya']       = $jb->kode_biaya;
                    if (!isset($item['masuk_hpp'])) {
                        $item['masuk_hpp'] = $jb->masuk_hpp ? '1' : '0';
                    }
                }
                return $item;
            }, $biayaDetails);

            $purchaseOrder->biaya_lain_details = $biayaDetails;
        }

        return response()->json(['po' => $purchaseOrder]);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('hapus-po');

        $user         = auth()->user();
        $isPrivileged = $user->can('hapus-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting']);
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Owner');

        if ($activeCompanyId && $purchaseOrder->company_id && $purchaseOrder->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseOrder->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if (!$isSuperAdmin && $purchaseOrder->status === 'Draft' && $purchaseOrder->created_by !== $user->id) {
            return response()->json(['message' => 'Draft Purchase Order ini bersifat privat dan hanya dapat dihapus oleh akun pembuatnya.'], 403);
        }

        // Non-Super Admin HANYA boleh menghapus PO jika statusnya Draft
        if (!$isSuperAdmin && $purchaseOrder->status !== 'Draft') {
            return response()->json([
                'message' => 'Hanya Purchase Order dengan status Draft yang dapat dihapus! Dokumen berstatus ' . $purchaseOrder->status . ' tidak dapat dihapus.'
            ], 403);
        }

        // Integrity check: cannot delete if already received in Goods Receipt (LPB) or billed in Invoice
        if (\App\Models\GoodsReceipt::where('po_id', $purchaseOrder->id)->exists()) {
            return response()->json([
                'message' => 'Purchase Order tidak dapat dihapus karena sudah memiliki transaksi Penerimaan Barang (Goods Receipt / LPB)!'
            ], 422);
        }

        if (\App\Models\PurchaseInvoice::where('po_id', $purchaseOrder->id)->exists()) {
            return response()->json([
                'message' => 'Purchase Order tidak dapat dihapus karena sudah terhubung ke Faktur Pembelian (Purchase Invoice)!'
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $prId = $purchaseOrder->pr_id;
            $purchaseOrder->delete();

            // Re-evaluate PR status if deleted PO frees up quantities
            $this->reevaluatePrStatus($prId);

            DB::commit();
            return response()->json(['message' => 'Purchase Order berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete PO: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus PO.'], 500);
        }
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('edit-po');

        $user         = auth()->user();
        $isPrivileged = $user->can('edit-po') || $user->can('approve-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting']);
        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Owner');
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $purchaseOrder->company_id && $purchaseOrder->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseOrder->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if (!$isPrivileged && $purchaseOrder->status === 'Draft' && $purchaseOrder->created_by !== $user->id) {
            return response()->json(['message' => 'Draft Purchase Order ini bersifat privat dan hanya dapat diedit oleh akun pembuatnya.'], 403);
        }

        // Rejected PO: only the creator can edit/resubmit
        if (!$isPrivileged && $purchaseOrder->status === 'Rejected' && $purchaseOrder->created_by !== $user->id) {
            return response()->json(['message' => 'Hanya pembuat PO yang dapat mengedit PO yang ditolak untuk diajukan ulang.'], 403);
        }

        // Non-Super Admin HANYA boleh mengedit Draft, Pending, atau Rejected
        if (!$isSuperAdmin && !in_array($purchaseOrder->status, ['Draft', 'Pending', 'Rejected'])) {
            return response()->json(['message' => 'Purchase Order yang sudah disetujui atau diproses (' . $purchaseOrder->status . ') tidak dapat diedit lagi.'], 403);
        }

        $activeGr = \App\Models\GoodsReceipt::where('po_id', $purchaseOrder->id)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Dibatalkan');
            })
            ->first();
        if ($activeGr) {
            return response()->json(['message' => 'Purchase Order tidak dapat diedit karena barang sudah diterima pada Penerimaan Barang (' . $activeGr->nomor_gr . '). Harap batalkan transaksi Penerimaan Barang terlebih dahulu.'], 422);
        }

        $activeAlokasiPo = \App\Models\AlokasiBiaya::where('po_biaya_id', $purchaseOrder->id)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'dibatalkan');
            })
            ->first();
        if ($activeAlokasiPo) {
            return response()->json(['message' => 'Purchase Order tidak dapat diedit karena sedang digunakan sebagai PO Biaya pada transaksi Alokasi Biaya (' . $activeAlokasiPo->nomor_alokasi . '). Harap batalkan transaksi Alokasi Biaya terlebih dahulu.'], 422);
        }

        $activeAlokasiDetail = \App\Models\AlokasiBiayaDetail::whereHas('goodsReceipt', function ($q) use ($purchaseOrder) {
                $q->where('po_id', $purchaseOrder->id);
            })
            ->whereHas('alokasiBiaya', function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'dibatalkan');
            })
            ->with('alokasiBiaya')
            ->first();
        if ($activeAlokasiDetail) {
            return response()->json(['message' => 'Purchase Order tidak dapat diedit karena barang dari PO ini telah dialokasikan biayanya pada transaksi Alokasi Biaya (' . ($activeAlokasiDetail->alokasiBiaya?->nomor_alokasi ?? '-') . '). Harap batalkan transaksi Alokasi Biaya terlebih dahulu.'], 422);
        }

        $activeInvoice = \App\Models\PurchaseInvoice::where('po_id', $purchaseOrder->id)
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn(DB::raw('LOWER(status)'), ['dibatalkan', 'cancelled', 'rejected']);
            })
            ->first();
        if ($activeInvoice) {
            return response()->json(['message' => 'Purchase Order tidak dapat diedit karena sudah terhubung ke Faktur Pembelian (' . $activeInvoice->nomor_invoice . '). Harap batalkan Faktur Pembelian terlebih dahulu.'], 422);
        }

        $purchaseOrder->load([
            'details.barang.satuan', 'details.barang.primaryFoto', 'details.barang.fotos', 'details.satuan', 'details.ppnPajak',
            'supplier', 'pr', 'tender', 'currency', 'pajak', 'pphPajak', 'pphCoa',
            'paymentTerm', 'paymentMethod',
        ]);
        return response()->json(['po' => $purchaseOrder]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('edit-po');

        $user         = auth()->user();
        $isPrivileged = $user->can('edit-po') || $user->can('approve-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting']);
        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('Owner');
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $purchaseOrder->company_id && $purchaseOrder->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseOrder->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if (!$isPrivileged && $purchaseOrder->status === 'Draft' && $purchaseOrder->created_by !== $user->id) {
            return response()->json(['message' => 'Draft Purchase Order ini bersifat privat dan hanya dapat diubah oleh akun pembuatnya.'], 403);
        }

        // Rejected PO: only the creator can edit/resubmit
        if (!$isPrivileged && $purchaseOrder->status === 'Rejected' && $purchaseOrder->created_by !== $user->id) {
            return response()->json(['message' => 'Hanya pembuat PO yang dapat mengedit PO yang ditolak untuk diajukan ulang.'], 403);
        }

        // Non-Super Admin HANYA boleh mengedit Draft, Pending, atau Rejected
        if (!$isSuperAdmin && !in_array($purchaseOrder->status, ['Draft', 'Pending', 'Rejected'])) {
            return response()->json(['message' => 'Purchase Order yang sudah disetujui atau diproses (' . $purchaseOrder->status . ') tidak dapat diubah lagi.'], 403);
        }

        $activeGr = \App\Models\GoodsReceipt::where('po_id', $purchaseOrder->id)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Dibatalkan');
            })
            ->first();
        if ($activeGr) {
            return response()->json(['message' => 'Purchase Order tidak dapat diubah karena barang sudah diterima pada Penerimaan Barang (' . $activeGr->nomor_gr . '). Harap batalkan transaksi Penerimaan Barang terlebih dahulu.'], 422);
        }

        $activeAlokasiPo = \App\Models\AlokasiBiaya::where('po_biaya_id', $purchaseOrder->id)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'dibatalkan');
            })
            ->first();
        if ($activeAlokasiPo) {
            return response()->json(['message' => 'Purchase Order tidak dapat diubah karena sedang digunakan sebagai PO Biaya pada transaksi Alokasi Biaya (' . $activeAlokasiPo->nomor_alokasi . '). Harap batalkan transaksi Alokasi Biaya terlebih dahulu.'], 422);
        }

        $activeAlokasiDetail = \App\Models\AlokasiBiayaDetail::whereHas('goodsReceipt', function ($q) use ($purchaseOrder) {
                $q->where('po_id', $purchaseOrder->id);
            })
            ->whereHas('alokasiBiaya', function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'dibatalkan');
            })
            ->with('alokasiBiaya')
            ->first();
        if ($activeAlokasiDetail) {
            return response()->json(['message' => 'Purchase Order tidak dapat diubah karena barang dari PO ini telah dialokasikan biayanya pada transaksi Alokasi Biaya (' . ($activeAlokasiDetail->alokasiBiaya?->nomor_alokasi ?? '-') . '). Harap batalkan transaksi Alokasi Biaya terlebih dahulu.'], 422);
        }

        $activeInvoice = \App\Models\PurchaseInvoice::where('po_id', $purchaseOrder->id)
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn(DB::raw('LOWER(status)'), ['dibatalkan', 'cancelled', 'rejected']);
            })
            ->first();
        if ($activeInvoice) {
            return response()->json(['message' => 'Purchase Order tidak dapat diubah karena sudah terhubung ke Faktur Pembelian (' . $activeInvoice->nomor_invoice . '). Harap batalkan Faktur Pembelian terlebih dahulu.'], 422);
        }

        $originalPrId = $purchaseOrder->pr_id;

        // Validate input — harga_satuan wajib > 0
        try {
            $request->validate([
                'tanggal_po'        => 'required|date',
                'supplier_id'       => 'required|exists:master_suppliers,id',
                'currency_id'       => 'required|exists:master_currencies,id',
                'kurs'              => 'required|numeric|min:0',
                'pajak_id'          => 'nullable',
                'pph_pajak_id'      => 'nullable|exists:master_pajaks,id',
                'tarif_pph'         => 'nullable|numeric|min:0|max:100',
                'pph_nominal'       => 'nullable|numeric|min:0',
                'pph_coa_id'        => 'nullable|exists:chart_of_accounts,id',
                'payment_term_id'   => 'nullable|exists:payment_terms,id',
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'tanggal_pengiriman'=> 'nullable|date',
                'pr_id'             => 'nullable|exists:purchase_requisitions,id',
                'tender_id'         => 'nullable|exists:tenders,id',
                'diskon_persen'     => 'nullable|numeric|min:0|max:100',
                'diskon_nominal'    => 'nullable|numeric|min:0',
                'down_payment'      => 'nullable|numeric|min:0',
                'biaya_lain'        => 'nullable|numeric|min:0',
                'deskripsi_biaya_lain' => 'nullable|string|max:255',
                'alokasi_biaya_lain' => 'nullable|string|in:Beban,HPP',
                'keterangan'        => 'nullable|string|max:40',
                'items'             => 'required|array|min:1|max:25',
                'items.*.barang_id'    => 'required|exists:master_barang,id',
                'items.*.satuan_id'    => 'nullable|exists:master_satuan,id',
                'items.*.faktor_pengali'=> 'nullable|numeric|min:0.0001',
                'items.*.pr_detail_id' => 'nullable|exists:purchase_requisition_details,id',
                'items.*.jumlah'       => 'required|numeric|min:0.0001',
                'items.*.harga_satuan' => 'required|numeric|min:0.01',
                'items.*.diskon_item'  => 'nullable|numeric|min:0',
                'items.*.diskon_persen'=> 'nullable|numeric|min:0|max:100',
                'items.*.diskon_tipe'  => 'nullable|string|in:percent,nominal',
                'items.*.ppn_pajak_id' => 'nullable|exists:master_pajaks,id',
                'items.*.tarif_ppn'    => 'nullable|numeric|min:0',
                'items.*.keterangan'   => 'nullable|string|max:255',
                'expense_items'        => 'nullable|array|max:5',
            ], [
                'keterangan.max'   => 'Catatan / Keterangan PO tidak boleh melebihi 40 karakter.',
                'items.max'        => 'Jumlah barang dalam 1 Purchase Order (PO) tidak boleh melebihi 25 item!',
                'items.min'        => 'Purchase Order (PO) minimal harus memiliki 1 item barang.',
                'expense_items.max'=> 'Rincian biaya lain-lain dibatasi maksimal 5 jenis biaya.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors()
            ], 422);
        }

        // Validate all items belong to active company and are active
        $submittedBarangIds = collect($request->items)->pluck('barang_id')->unique()->toArray();
        $validBarangCount = MasterBarang::forActiveCompany()
            ->whereIn('id', $submittedBarangIds)
            ->where('status', 'Active')
            ->count();

        if ($validBarangCount !== count($submittedBarangIds)) {
            return response()->json([
                'message' => 'Satu atau lebih barang yang dipilih tidak valid, tidak aktif, atau bukan milik perusahaan Anda!'
            ], 422);
        }

        // Validate remaining PR quantity limits if PO is tied to a PR (excluding this PO's own existing details to not double-count)
        if ($request->pr_id) {
            $prCheck = PurchaseRequisition::find($request->pr_id);
            if (!$prCheck || ($activeCompanyId && $prCheck->company_id && $prCheck->company_id != $activeCompanyId) || strval($prCheck->branch_id) !== strval(session('active_branch_id'))) {
                return response()->json(['message' => 'Referensi PR tidak valid atau milik cabang lain / pusat!'], 403);
            }
            if (!in_array($prCheck->status, ['Approved', 'Closed'])) {
                return response()->json(['message' => 'Hanya PR yang sudah disetujui (Approved) yang dapat diproses menjadi Purchase Order!'], 422);
            }

            foreach ($request->items as $item) {
                if (!empty($item['pr_detail_id'])) {
                    // Sum other POs details for this PR detail (excluding rejected POs)
                    $ordered = PurchaseOrderDetail::where('pr_detail_id', $item['pr_detail_id'])
                        ->where('po_id', '!=', $purchaseOrder->id)
                        ->whereHas('po', function ($q) {
                            $q->where('status', '!=', 'Rejected');
                        })
                        ->sum('jumlah');
                    $prDetail = PurchaseRequisitionDetail::find($item['pr_detail_id']);
                    if ($prDetail) {
                        $remaining = $prDetail->jumlah - $ordered;
                        if ($item['jumlah'] > $remaining) {
                            return response()->json([
                                'message' => "Jumlah item {$prDetail->barang->nama_barang} ({$item['jumlah']}) melebihi sisa pengajuan PR ({$remaining})!"
                            ], 422);
                        }
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            // Force IDR currency rate to 1.00 regardless of user input; validate non-IDR
            $kurs = round(floatval($request->kurs), 4); // kolom kurs decimal(16,4)
            $currency = MasterCurrency::find($request->currency_id);
            if ($currency && $currency->kode_currency === 'IDR') {
                $kurs = 1.00;
            } else {
                if ($kurs <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Validasi gagal!',
                        'errors' => ['kurs' => ['Nilai kurs untuk mata uang asing (' . ($currency?->kode_currency ?? '') . ') harus lebih besar dari 0!']]
                    ], 422);
                }
            }

            // Remove existing details first
            $purchaseOrder->details()->delete();

            // Process per-item calculations (gross, discount, and tax per item)
            $subtotalGross = 0;
            $totalDiskon = 0;
            $totalPajak = 0;
            $itemsData = [];

            // Pre-load tax rates from master (source of truth, scoped to active company)
            $allTaxMap = MasterPajak::forActiveCompany()->pluck('tarif', 'id')->toArray();

            foreach ($request->items as $item) {
                // Samakan presisi dengan kolom DB agar harga x qty = subtotal yang tersimpan
                $qty = round(floatval($item['jumlah']), 4);          // decimal(18,4)
                $hargaSatuan = round(floatval($item['harga_satuan']), 2); // decimal(20,2)
                $diskonTipe = ($item['diskon_tipe'] ?? 'percent') === 'nominal' ? 'nominal' : 'percent';
                $diskonPersen = round(floatval($item['diskon_persen'] ?? 0), 2); // decimal(5,2)
                $diskonItem = round(floatval($item['diskon_item'] ?? 0), 2);

                if ($diskonTipe === 'percent') {
                    if ($diskonPersen > 100) $diskonPersen = 100;
                    $diskonUnit = round($hargaSatuan * ($diskonPersen / 100), 2);
                } else {
                    if ($diskonItem > $hargaSatuan) $diskonItem = $hargaSatuan;
                    $diskonUnit = $diskonItem;
                    $diskonPersen = $hargaSatuan > 0 ? round(($diskonUnit / $hargaSatuan) * 100, 2) : 0;
                }

                $lineGross = round($qty * $hargaSatuan, 2);
                $lineDiskon = round($qty * $diskonUnit, 2);
                $lineSubtotal = max(0, round($lineGross - $lineDiskon, 2));

                // Per-item PPN
                $ppnPajakId = !empty($item['ppn_pajak_id']) ? intval($item['ppn_pajak_id']) : null;
                // Tarif SELALU dari master pajak (jangan percaya nilai dari client)
                if ($ppnPajakId && !isset($allTaxMap[$ppnPajakId])) {
                    $ppnPajakId = null; // pajak bukan milik company aktif
                }
                $tarifPpn = $ppnPajakId ? floatval($allTaxMap[$ppnPajakId]) : 0;
                $ppnNominal = ($ppnPajakId && $tarifPpn > 0) ? round($lineSubtotal * ($tarifPpn / 100), 2) : 0;

                $satuanId = $item['satuan_id'] ?? null;
                $faktorPengali = floatval($item['faktor_pengali'] ?? 1);
                if (!$satuanId) {
                    $barang = MasterBarang::find($item['barang_id']);
                    $satuanId = $barang?->satuan_id;
                }
                $jumlahDasar = $qty * ($faktorPengali ?: 1);

                $subtotalGross += $lineGross;
                $totalDiskon += $lineDiskon;
                $totalPajak += $ppnNominal;

                $itemsData[] = [
                    'barang_id'      => $item['barang_id'],
                    'satuan_id'      => $satuanId,
                    'pr_detail_id'   => $item['pr_detail_id'] ?? null,
                    'jumlah'         => $qty,
                    'faktor_pengali' => $faktorPengali,
                    'jumlah_dasar'   => $jumlahDasar,
                    'harga_satuan'   => $hargaSatuan,
                    'diskon_item'    => $diskonUnit,
                    'diskon_persen'  => $diskonPersen,
                    'diskon_tipe'    => $diskonTipe,
                    'subtotal'       => $lineSubtotal,
                    'ppn_pajak_id'   => $ppnPajakId,
                    'tarif_ppn'      => $tarifPpn,
                    'ppn_nominal'    => $ppnNominal,
                    'keterangan'     => $item['keterangan'] ?? null,
                ];
            }

            $subtotalGross = round($subtotalGross, 2);
            $totalDiskon = round($totalDiskon, 2);
            $totalPajak = round($totalPajak, 2);

            // DPP = Subtotal Barang Kotor - Total Diskon Barang
            $dpp = round(max(0, $subtotalGross - $totalDiskon), 2);

            $expenseItems = $request->input('expense_items', []);
            $biayaLain = 0;
            if (!empty($expenseItems) && is_array($expenseItems)) {
                foreach ($expenseItems as $idx => $exp) {
                    $nom = round(floatval($exp['nominal'] ?? 0), 2);
                    if ($nom < 0) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Validasi gagal!',
                            'errors' => ['expense_items' => ['Nominal biaya lain-lain tidak boleh negatif!']]
                        ], 422);
                    }
                    $expenseItems[$idx]['nominal'] = $nom;
                    $biayaLain += $nom;
                }
            } else {
                $biayaLain = max(0, floatval($request->input('biaya_lain', 0)));
            }
            $biayaLain = round($biayaLain, 2);

            // Calculate PPh (Withholding Tax - Potongan Pajak Penghasilan)
            $pphPajakId = $request->filled('pph_pajak_id') ? (int) $request->pph_pajak_id : null;
            $tarifPph   = 0.0;
            $pphNominal = 0.0;
            $pphCoaId   = $request->filled('pph_coa_id') ? (int) $request->pph_coa_id : null;

            if ($pphPajakId && isset($allTaxMap[$pphPajakId])) {
                $tarifPph = floatval($allTaxMap[$pphPajakId]);
                $pphNominal = round($dpp * ($tarifPph / 100), 2);
                if (!$pphCoaId) {
                    $pphCoaId = \App\Helpers\AccountMappingHelper::getCoaId('tax', "tax_{$pphPajakId}_in") ?? $this->resolveDefaultPphCoaId();
                }
            } else {
                $pphPajakId = null;
                $tarifPph   = 0.0;
                $pphNominal = 0.0;
                $pphCoaId   = null;
            }

            // Grand Total = DPP + Total PPN + Biaya Lain-Lain - Potongan PPh
            $grandTotal = round(max(0, $dpp + $totalPajak + $biayaLain - $pphNominal), 2);
            $downPayment = round(floatval($request->input('down_payment', 0)), 2);

            // Validate down payment doesn't exceed grand total
            if ($downPayment > $grandTotal) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Validasi gagal!',
                    'errors' => ['down_payment' => ['Nilai uang muka (DP) tidak boleh melebihi Grand Total!']]
                ], 422);
            }

            $grandTotalIdr = round($grandTotal * $kurs, 2);

            // Save details
            foreach ($itemsData as $data) {
                $purchaseOrder->details()->create($data);
            }

            // Update Header values
            $purchaseOrder->update([
                'supplier_id'        => $request->supplier_id,
                'tanggal_po'         => $request->tanggal_po,
                'currency_id'        => $request->currency_id,
                'kurs'               => $kurs,
                'pajak_id'           => null,
                'payment_term_id'    => $request->payment_term_id ?: null,
                'payment_method_id'  => $request->payment_method_id ?: null,
                'tanggal_pengiriman' => $request->tanggal_pengiriman ?: null,
                'pr_id'              => $request->pr_id ?: null,
                'tender_id'          => $request->tender_id ?: null,
                'manual_pr_number'   => $request->pr_id ? null : $request->manual_pr_number,
                'diskon_persen'      => 0,
                'diskon_nominal'     => 0,
                'subtotal'           => $subtotalGross,
                'total_diskon'       => $totalDiskon,
                'total_pajak'        => $totalPajak,
                'pph_pajak_id'       => $pphPajakId,
                'tarif_pph'          => $tarifPph,
                'pph_nominal'        => $pphNominal,
                'total_pph'          => $pphNominal,
                'pph_coa_id'         => $pphCoaId,
                'grand_total'        => $grandTotal,
                'grand_total_idr'    => $grandTotalIdr,
                'down_payment'       => $downPayment,
                'biaya_lain'         => $biayaLain,
                'biaya_lain_details' => !empty($expenseItems) ? $expenseItems : null,
                'deskripsi_biaya_lain' => $request->input('deskripsi_biaya_lain'),
                'alokasi_biaya_lain'  => $request->input('alokasi_biaya_lain', 'Beban'),
                'keterangan'         => $request->keterangan,
                // If PO was Rejected and being resubmitted, reset alasan_ditolak
                'status'          => $request->boolean('is_draft') ? 'Draft' : 'Pending',
                'alasan_ditolak'  => null,
                'updated_by'      => auth()->id(),
            ]);

            // Re-evaluate PR status using single aggregate queries (no N+1 loops)
            $this->reevaluatePrStatus($request->pr_id);
            if ($originalPrId && $originalPrId != $request->pr_id) {
                $this->reevaluatePrStatus($originalPrId);
            }

            DB::commit();

            if ($purchaseOrder->status === 'Pending') {
                \App\Http\Controllers\WhatsAppGatewayController::runAsync(function () use ($purchaseOrder) {
                    \App\Http\Controllers\WhatsAppGatewayController::sendPoApprovalNotification($purchaseOrder);
                });
            }

            $msg = $request->boolean('is_draft')
                ? 'Purchase Order berhasil disimpan sebagai Draft!'
                : 'Purchase Order berhasil diperbarui dan diajukan!';
            return response()->json(['message' => $msg]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update PO: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui PO.'], 500);
        }
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve-po');

        $user         = auth()->user();
        $isPrivileged = $user->can('approve-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting']);
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $purchaseOrder->company_id && $purchaseOrder->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseOrder->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if ($purchaseOrder->status !== 'Pending') {
            return response()->json(['message' => 'Hanya PO dengan status Pending yang dapat disetujui!'], 400);
        }

        // Segregation of Duties (SOD): Creator cannot self-approve their own PO unless Super Admin, Owner, or Director
        $isCreator = ($purchaseOrder->created_by === $user->id);
        $hasBypassRole = $user->hasAnyRole(['Super Admin', 'Owner', 'Director']);
        if ($isCreator && !$hasBypassRole) {
            return response()->json([
                'message' => 'Anda tidak dapat menyetujui Purchase Order yang Anda buat sendiri (Prinsip Segregation of Duties).'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $purchaseOrder = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->first();
            if (!$purchaseOrder || $purchaseOrder->status !== 'Pending') {
                DB::rollBack();
                return response()->json(['message' => 'Hanya PO dengan status Pending yang dapat disetujui!'], 400);
            }

            $purchaseOrder->status = 'Ordered';
            $purchaseOrder->approved_by = auth()->id();
            $purchaseOrder->approved_at = now();
            $purchaseOrder->saveQuietly();

            // Re-evaluate PR status using single aggregate query (no N+1 loop)
            $this->reevaluatePrStatus($purchaseOrder->pr_id);

            DB::commit();

            $approverUser = auth()->user();
            \App\Http\Controllers\WhatsAppGatewayController::runAsync(function () use ($purchaseOrder, $approverUser) {
                \App\Http\Controllers\WhatsAppGatewayController::sendPoApprovedNotification($purchaseOrder, $approverUser);
            });

            return response()->json(['message' => 'Purchase Order berhasil disetujui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approve PO: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menyetujui PO.'], 500);
        }
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve-po');

        $user         = auth()->user();
        $isPrivileged = $user->can('approve-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting']);
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $purchaseOrder->company_id && $purchaseOrder->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseOrder->branch_id)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if ($purchaseOrder->status !== 'Pending') {
            return response()->json(['message' => 'Hanya PO dengan status Pending yang dapat ditolak!'], 400);
        }

        // Segregation of Duties (SOD): Creator cannot reject their own PO unless Super Admin, Owner, or Director
        $isCreator = ($purchaseOrder->created_by === $user->id);
        $hasBypassRole = $user->hasAnyRole(['Super Admin', 'Owner', 'Director']);
        if ($isCreator && !$hasBypassRole) {
            return response()->json([
                'message' => 'Anda tidak dapat memproses penolakan Purchase Order yang Anda buat sendiri (Prinsip Segregation of Duties).'
            ], 403);
        }

        $alasan = trim($request->input('alasan_ditolak', ''));
        if (empty($alasan)) {
            return response()->json(['message' => 'Alasan penolakan wajib diisi!'], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseOrder = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->first();
            if (!$purchaseOrder || $purchaseOrder->status !== 'Pending') {
                DB::rollBack();
                return response()->json(['message' => 'Hanya PO dengan status Pending yang dapat ditolak!'], 400);
            }

            $purchaseOrder->update([
                'status'         => 'Rejected',
                'alasan_ditolak' => $alasan,
            ]);

            // Re-evaluate PR status if PO is rejected (frees up quantities)
            if ($purchaseOrder->pr_id) {
                $pr = PurchaseRequisition::find($purchaseOrder->pr_id);
                if ($pr && $pr->status === 'Closed') {
                    $pr->update(['status' => 'Approved']);
                }
            }

            DB::commit();

            $rejectorUser = auth()->user();
            \App\Http\Controllers\WhatsAppGatewayController::runAsync(function () use ($purchaseOrder, $alasan, $rejectorUser) {
                \App\Http\Controllers\WhatsAppGatewayController::sendPoRejectedNotification($purchaseOrder, $alasan, $rejectorUser);
            });

            return response()->json(['message' => 'Purchase Order berhasil ditolak!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reject PO: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menolak PO.'], 500);
        }
    }

    /**
     * Buka kembali Purchase Order / Kembalikan ke Draft agar bisa diedit kembali.
     * Persyaratan:
     * 1. Tidak memiliki transaksi Penerimaan Barang (GR) aktif (atau harus dibatalkan terlebih dahulu).
     * 2. Tidak sedang digunakan dalam Alokasi Biaya aktif (atau harus dibatalkan terlebih dahulu).
     * 3. Tidak memiliki Faktur Pembelian (Purchase Invoice) aktif (atau harus dibatalkan terlebih dahulu).
     */
    public function reopen(PurchaseOrder $purchaseOrder)
    {
        $user      = auth()->user();
        $canReopen = $user->can('buka-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director']);

        if (!$canReopen) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses (role permission: buka-po) untuk membuka kembali Purchase Order ini ke status draft.'
            ], 403);
        }

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $purchaseOrder->company_id && $purchaseOrder->company_id != $activeCompanyId) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke data perusahaan lain.'], 403);
        }
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Owner') && strval($activeBranchId) !== strval($purchaseOrder->branch_id)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke data cabang lain / pusat.'], 403);
        }

        if ($purchaseOrder->status === 'Draft') {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Order ini sudah berstatus Draft dan dapat langsung diedit.'
            ], 400);
        }

        // 1. Cek Penerimaan Barang (Goods Receipt / LPB)
        $activeGr = \App\Models\GoodsReceipt::where('po_id', $purchaseOrder->id)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', '!=', 'Dibatalkan');
            })
            ->first();

        if ($activeGr) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Order tidak dapat dikembalikan ke draft karena barang sudah diterima pada transaksi <b>Penerimaan Barang (' . e($activeGr->nomor_gr) . ')</b>. Silakan batalkan transaksi Penerimaan Barang tersebut terlebih dahulu!'
            ], 422);
        }

        // 2. Cek Alokasi Biaya (sebagai PO Biaya yang dialokasikan)
        $activeAlokasiPo = \App\Models\AlokasiBiaya::where('po_biaya_id', $purchaseOrder->id)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', '!=', 'dibatalkan');
            })
            ->first();

        if ($activeAlokasiPo) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Order tidak dapat dikembalikan ke draft karena sedang digunakan sebagai PO Biaya pada transaksi <b>Alokasi Biaya (' . e($activeAlokasiPo->nomor_alokasi) . ')</b>. Silakan batalkan transaksi Alokasi Biaya tersebut terlebih dahulu!'
            ], 422);
        }

        // 2b. Cek Alokasi Biaya (barang PO ini menerima alokasi biaya)
        $activeAlokasiDetail = \App\Models\AlokasiBiayaDetail::whereHas('goodsReceipt', function ($q) use ($purchaseOrder) {
                $q->where('po_id', $purchaseOrder->id);
            })
            ->whereHas('alokasiBiaya', function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', '!=', 'dibatalkan');
            })
            ->with('alokasiBiaya')
            ->first();

        if ($activeAlokasiDetail) {
            $nomorAlokasi = $activeAlokasiDetail->alokasiBiaya?->nomor_alokasi ?? '-';
            return response()->json([
                'success' => false,
                'message' => 'Purchase Order tidak dapat dikembalikan ke draft karena barang dari PO ini telah dialokasikan biayanya pada transaksi <b>Alokasi Biaya (' . e($nomorAlokasi) . ')</b>. Silakan batalkan transaksi Alokasi Biaya tersebut terlebih dahulu!'
            ], 422);
        }

        // 3. Cek Faktur Pembelian (Purchase Invoice)
        $activeInvoice = \App\Models\PurchaseInvoice::where('po_id', $purchaseOrder->id)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereNotIn(DB::raw('LOWER(status)'), ['dibatalkan', 'cancelled', 'rejected']);
            })
            ->first();

        if ($activeInvoice) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Order tidak dapat dikembalikan ke draft karena sudah memiliki transaksi <b>Faktur Pembelian (' . e($activeInvoice->nomor_invoice) . ')</b>. Silakan batalkan Faktur Pembelian terlebih dahulu!'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $purchaseOrder->update([
                'status'         => 'Draft',
                'approved_by'    => null,
                'approved_at'    => null,
                'alasan_ditolak' => null,
            ]);

            // Re-evaluasi status PR terkait jika ada
            if ($purchaseOrder->pr_id) {
                $this->reevaluatePrStatus($purchaseOrder->pr_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order <b>' . e($purchaseOrder->nomor_po) . '</b> berhasil dikembalikan ke status <b>Draft</b>! Dokumen PO sekarang dapat diedit kembali.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reopen PO: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat mengembalikan PO ke draft.'], 500);
        }
    }

    /**
     * Helper: Re-evaluate PR status using a SINGLE aggregate query per call.
     * Eliminates N+1 problem in store(), update(), and approve().
     *
     * Logic:
     * - If ALL PR detail quantities are fully covered by PO details → Closed
     * - Otherwise → Approved (still has remaining items to purchase)
     */
    private function reevaluatePrStatus(?int $prId): void
    {
        if (!$prId) return;

        $pr = PurchaseRequisition::with('details')->find($prId);
        if (!$pr) return;

        $prDetailIds = $pr->details->pluck('id');

        // Single aggregate query: sum ordered qty per pr_detail_id
        $orderedMap = PurchaseOrderDetail::whereIn('pr_detail_id', $prDetailIds)
            ->whereHas('po', function ($q) {
                $q->where('status', '!=', 'Rejected');
            })
            ->selectRaw('pr_detail_id, SUM(jumlah) as total_ordered')
            ->groupBy('pr_detail_id')
            ->pluck('total_ordered', 'pr_detail_id');

        $allMet = $pr->details->every(function ($d) use ($orderedMap) {
            return ($orderedMap[$d->id] ?? 0) >= $d->jumlah;
        });

        $pr->update(['status' => $allMet ? 'Closed' : 'Approved']);
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        $user         = auth()->user();
        $isPrivileged = $user->can('cetak-po') || $user->can('lihat-po') || $user->can('approve-po') || $user->hasAnyRole(['Super Admin', 'Admin', 'Owner', 'Director', 'Purchasing', 'Accounting']);

        if (!$user->can('cetak-po') && !$user->can('lihat-po') && !$isPrivileged) {
            $this->authorize('cetak-po');
        }

        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id');

        if ($activeCompanyId && $purchaseOrder->company_id && $purchaseOrder->company_id != $activeCompanyId) {
            abort(403, 'Anda tidak memiliki akses ke data perusahaan lain.');
        }
        if (!$isPrivileged && strval($activeBranchId) !== strval($purchaseOrder->branch_id)) {
            abort(403, 'Anda tidak memiliki akses ke data cabang lain / pusat.');
        }

        if ($purchaseOrder->status === 'Draft' && $purchaseOrder->created_by !== $user->id) {
            abort(403, 'Draft Purchase Order ini bersifat privat dan hanya dapat dicetak oleh akun pembuatnya.');
        }

        $purchaseOrder->load([
            'company', 'supplier', 'currency', 'pajak', 'pphPajak', 'pphCoa', 'pr',
            'creator.activeSignature', 'creator.position',
            'approver.activeSignature', 'approver.position',
            'branch', 'paymentTerm', 'paymentMethod',
            'details.barang.satuan', 'details.satuan', 'details.barang.primaryFoto', 'details.barang.warna', 'details.ppnPajak'
        ]);

        $activeBranch = $purchaseOrder->branch;

        return view('modul_purchasing.pembuatanpo.printpo', compact('purchaseOrder', 'activeBranch'));
    }
}
