<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\MasterBarang;
use App\Models\MasterCustomer;
use App\Models\MasterGudang;
use App\Models\StokGudang;
use App\Models\KartuStok;
use App\Models\Company;
use App\Models\Branch;
use App\Models\SpkPenerimaanBarangJadiSerial;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\ChartOfAccount;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Tender;
use App\Helpers\AccountMappingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class DeliveryOrderController extends Controller
{
    /**
     * Tampilkan halaman utama Pengiriman & Surat Jalan (Delivery Order)
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->can('lihat-delivery-order')) {
            abort(403, 'Anda tidak memiliki akses untuk melihat halaman Pengiriman & Surat Jalan.');
        }

        // Keamanan Tenancy: Pisahkan secara ketat gudang milik PT Pusat (cabang_id NULL) vs Cabang (cabang_id = active_branch_id)
        $gudangs = MasterGudang::active()
            ->forActiveEntity()
            ->orderBy('nama_gudang')
            ->get();

        // Data Tender aktif untuk filter pengiriman
        $tenders = Tender::forActiveCompany()
            ->orderBy('id', 'desc')
            ->get(['id', 'nomor_kontrak', 'nama_tender']);

        return view('modul_marketing.pengiriman.index', compact('gudangs', 'tenders'));
    }

    /**
     * JSON DataTables untuk Riwayat Pengiriman / Surat Jalan
     */
    public function getData(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->can('lihat-delivery-order')) {
            abort(403);
        }

        $query = DeliveryOrder::forActiveCompany()
            ->with([
                'salesOrder:id,nomor_so,customer_id,status',
                'customer:id,nama_customer,nama_perusahaan',
                'customer.defaultAddress',
                'gudang:id,nama_gudang',
                'creator:id,nama_lengkap,username',
            ])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gudang_id')) {
            $query->where('gudang_id', $request->gudang_id);
        }

        if ($request->filled('tender_id')) {
            $tenderId = $request->tender_id;
            if ($tenderId === 'non_tender') {
                $query->whereNull('sales_order_id');
            } else {
                $tenderSoIds = SalesOrder::forActiveCompany()
                    ->where(function($tq) use ($tenderId) {
                        $tq->where('tender_id', $tenderId)
                           ->orWhereHas('quotation', fn($qq) => $qq->where('tender_id', $tenderId));
                    })
                    ->pluck('id');
                $query->whereIn('sales_order_id', $tenderSoIds);
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_kirim', [$request->start_date, $request->end_date]);
        }

        $dataTable = DataTables::eloquent($query)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->input('search.value'))) {
                    $search = trim($request->input('search.value'));

                    // Pre-pluck matching SO & Customer IDs agar query tetap instan pada 7 juta data tanpa full scan tabel
                    $soQuery = SalesOrder::forActiveCompany()->where('nomor_so', 'LIKE', "%{$search}%");
                    $activeBranchId = session('active_branch_id');
                    if (!empty($activeBranchId) && \Illuminate\Support\Facades\Schema::hasColumn('sales_orders', 'branch_id')) {
                        $soQuery->where('branch_id', $activeBranchId);
                    }
                    $matchedSoIds = $soQuery->limit(200)->pluck('id');

                    $matchedCustomerIds = MasterCustomer::forActiveCompany()
                        ->where(function($mq) use ($search) {
                            $mq->where('nama_customer', 'LIKE', "%{$search}%")
                               ->orWhere('nama_perusahaan', 'LIKE', "%{$search}%");
                        })
                        ->limit(200)
                        ->pluck('id');

                    $query->where(function($q) use ($search, $matchedSoIds, $matchedCustomerIds) {
                        $q->where('nomor_do', 'LIKE', "%{$search}%")
                          ->orWhere('nama_penerima', 'LIKE', "%{$search}%");

                        if ($matchedSoIds->isNotEmpty()) {
                            $q->orWhereIn('sales_order_id', $matchedSoIds);
                        }
                        if ($matchedCustomerIds->isNotEmpty()) {
                            $q->orWhereIn('customer_id', $matchedCustomerIds);
                        }
                    });
                }
            }, true);

        // Optimasi Skala 7 Juta Baris: Hitung qty kirim & total item HANYA untuk baris yang tampil pada halaman saat ini (10-25 baris)
        // Mencegah MySQL menjalankan 14 juta subquery pada COUNT(*) total tabel
        $dataTable->results()->loadCount('details')->loadSum('details as details_sum_qty_kirim', 'qty_kirim');

        return $dataTable
            ->addIndexColumn()
            ->editColumn('nomor_do', function ($row) {
                return '<span class="do-code-badge font-monospace fw-bold"><i class="bi bi-box-seam me-1"></i>' . e($row->nomor_do) . '</span>';
            })
            ->addColumn('nomor_so', function ($row) {
                if ($row->salesOrder) {
                    return '<span class="font-monospace fw-semibold text-primary"><i class="bi bi-receipt me-1"></i>' . e($row->salesOrder->nomor_so) . '</span>';
                }
                return '<span class="text-muted small fst-italic">Manual / Tanpa SO</span>';
            })
            ->addColumn('customer_info', function ($row) {
                $comp = $row->customer?->nama_perusahaan;
                $pic  = $row->nama_penerima ?: ($row->customer?->nama_customer ?: '');

                // Bersihkan duplikasi jika nama_penerima diawali nama perusahaan
                if ($comp && $pic) {
                    if (str_starts_with($pic, $comp)) {
                        $pic = trim(substr($pic, strlen($comp)));
                        $pic = trim($pic, " \t\n\r\0\x0B-:(),");
                    }
                }

                $html = '';
                if ($comp) {
                    $html .= '<div class="fw-bold text-body-emphasis d-flex align-items-center gap-1.5"><i class="bi bi-building text-primary small"></i> ' . e($comp) . '</div>';
                    if ($pic && $pic !== $comp) {
                        $html .= '<div class="text-muted small mt-0.5"><i class="bi bi-person me-1"></i>' . e($pic) . '</div>';
                    }
                } else {
                    $html .= '<div class="fw-bold text-body-emphasis d-flex align-items-center gap-1.5"><i class="bi bi-person text-primary small"></i> ' . e($pic ?: '-') . '</div>';
                }
                return $html;
            })
            ->addColumn('items_count', function ($row) {
                $totalQty = (float) ($row->details_sum_qty_kirim ?? 0);
                $countItem = (int) ($row->details_count ?? 0);
                return '<span class="do-item-pill font-monospace"><i class="bi bi-box me-1"></i>' . $countItem . ' Produk <span class="opacity-75">(' . number_format($totalQty, 0, ',', '.') . ' pcs)</span></span>';
            })
            ->editColumn('status', function ($row) {
                $dotColors = [
                    'Draft'                => '#94a3b8',
                    'Menunggu Persetujuan' => '#f59e0b',
                    'Disetujui'            => '#06b6d4',
                    'Siap Dikirim'         => '#0ea5e9',
                    'Dalam Perjalanan'     => '#3b82f6',
                    'Terkirim'             => '#10b981',
                    'Dibatalkan'           => '#ef4444',
                ];
                $dot = $dotColors[$row->status] ?? '#64748b';
                return '<span class="do-status-badge font-monospace" style="--dot-color: ' . $dot . ';"><span class="do-status-dot"></span>' . e($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $user = auth()->user();
                $canApprove  = $user ? $user->can('approve-delivery-order') : false;
                $canComplete = $user ? ($user->can('selesai-delivery-order') || $user->can('approve-delivery-order')) : false;
                $canPrint    = $user ? $user->can('cetak-delivery-order') : false;
                $canEdit     = $user ? $user->can('edit-delivery-order') : false;

                $btnDetail = '<button type="button" class="btn btn-sm btn-do-action btn-do-detail btn-detail-do" data-id="' . $row->id . '" title="Lihat Rincian Pengiriman"><i class="bi bi-eye"></i> Detail</button>';
                
                $btnEdit = '';
                $nonEditableStatuses = ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Selesai'];
                if ($canEdit && !in_array($row->status, $nonEditableStatuses)) {
                    $isRevisi = in_array($row->status, ['Dibatalkan', 'Ditolak']);
                    $btnEdit = '<button type="button" class="btn btn-sm btn-do-action btn-do-edit btn-edit-do" data-id="' . $row->id . '" title="' . ($isRevisi ? 'Revisi Surat Jalan' : 'Edit Surat Jalan') . '">'
                        . ($isRevisi ? '<i class="bi bi-arrow-repeat text-warning"></i> Revisi' : '<i class="bi bi-pencil-square text-warning"></i> Edit')
                        . '</button>';
                }

                $btnPrint  = $canPrint
                    ? '<a href="' . route('marketing.delivery-order.print', $row->id) . '" target="_blank" class="btn btn-sm btn-do-action btn-do-print" title="Cetak Surat Jalan A4"><i class="bi bi-printer"></i> Print</a>'
                    : '';

                $btnExtra = '';
                if ($row->status === 'Menunggu Persetujuan') {
                    if ($canApprove) {
                        $btnExtra = '<button type="button" class="btn btn-sm btn-success px-2.5 py-1 d-inline-flex align-items-center gap-1 btn-approve-do rounded-3" data-id="' . $row->id . '" data-nomor="' . e($row->nomor_do) . '" title="ACC Pengiriman"><i class="bi bi-check-lg"></i> ACC</button>'
                                  . '<button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 d-inline-flex align-items-center btn-reject-do rounded-3" data-id="' . $row->id . '" data-nomor="' . e($row->nomor_do) . '" title="Tolak Pengajuan"><i class="bi bi-x-lg"></i></button>';
                    }
                } elseif (in_array($row->status, ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan'])) {
                    if ($canComplete) {
                        $btnExtra = '<button type="button" class="btn btn-sm btn-primary px-2.5 py-1 d-inline-flex align-items-center gap-1 btn-confirm-arrival rounded-3" data-id="' . $row->id . '" data-nomor="' . e($row->nomor_do) . '" title="Konfirmasi Barang Telah Sampai"><i class="bi bi-box-seam-fill me-1"></i>Sampai</button>';
                    }
                }

                return '<div class="d-inline-flex align-items-center gap-1.5 flex-nowrap">' . $btnDetail . $btnEdit . $btnPrint . $btnExtra . '</div>';
            })
            ->rawColumns(['nomor_do', 'nomor_so', 'customer_info', 'tanggal_kirim', 'ekspedisi_info', 'items_count', 'status', 'action'])
            ->make(true);
    }

    /**
     * Dapatkan daftar Sales Order yang siap dikirim
     */
    public function getAvailableSalesOrders(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        // Isolasi ketat PT Pusat vs Cabang menggunakan scopeForActiveCompany bawaan model SalesOrder
        $query = SalesOrder::forActiveCompany()
            ->whereNotIn('status', ['Draft', 'Batal', 'Cancelled', 'Dibatalkan', 'Selesai', 'Completed'])
            ->whereHas('details');

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('nomor_so', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('nama_customer', 'LIKE', "%{$search}%")
                         ->orWhere('nama_perusahaan', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Limit maksimal 100 data teratas agar memori server stabil saat jutaan data
        $orders = $query->with([
                'customer:id,nama_customer,nama_perusahaan,telepon,hp',
                'tender:id,nomor_kontrak,nama_tender',
                'quotation.tender:id,nomor_kontrak,nama_tender',
                'details:id,sales_order_id,qty',
                'details.deliveryOrderDetails' => function($q) {
                    $q->whereHas('deliveryOrder', function($dq) {
                        $dq->where('status', '!=', 'Dibatalkan');
                    });
                }
            ])
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        $soIds = $orders->pluck('id')->filter()->all();
        $tendersBySoMap = !empty($soIds) 
            ? \App\Models\Tender::whereIn('sales_order_id', $soIds)->get(['id', 'sales_order_id', 'nomor_kontrak', 'nama_tender'])->keyBy('sales_order_id')
            : collect();

        // Hanya tampilkan SO yang itemnya masih memiliki sisa kirim (belum lunas terkirim)
        $availableOrders = $orders->filter(function($o) {
            if ($o->details->isEmpty()) return false;

            $hasRemaining = false;
            foreach ($o->details as $d) {
                $alreadyDelivered = (float) $d->deliveryOrderDetails->sum('qty_kirim');
                if (((float)$d->qty - $alreadyDelivered) > 0.001) {
                    $hasRemaining = true;
                    break;
                }
            }
            return $hasRemaining;
        })->map(function($o) use ($tendersBySoMap) {
            $tenderNo = null;
            if ($o->tender) {
                $tenderNo = $o->tender->nomor_kontrak ?: $o->tender->nama_tender;
            } elseif ($o->sales_quotation_id && $o->quotation && $o->quotation->tender) {
                $tenderNo = $o->quotation->tender->nomor_kontrak ?: $o->quotation->tender->nama_tender;
            } else {
                $tenderBySo = $tendersBySoMap->get($o->id);
                if ($tenderBySo) {
                    $tenderNo = $tenderBySo->nomor_kontrak ?: $tenderBySo->nama_tender;
                }
            }

            return [
                'id'                      => $o->id,
                'nomor_so'                => $o->nomor_so,
                'tanggal_so'              => $o->tanggal_so?->format('d/m/Y') ?? '-',
                'customer_id'             => $o->customer_id,
                'nama_customer_walkin'    => $o->nama_customer_walkin,
                'telepon_customer_walkin' => $o->telepon_customer_walkin,
                'alamat_pengiriman'       => $o->alamat_pengiriman,
                'status'                  => $o->status,
                'pricing_tier'            => $o->pricing_tier,
                'tender_nomor'            => $tenderNo,
                'customer'                => $o->customer ? [
                    'id'              => $o->customer->id,
                    'nama_customer'   => $o->customer->nama_customer,
                    'nama_perusahaan' => $o->customer->nama_perusahaan,
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'orders'  => $availableOrders,
        ]);
    }

    /**
     * Dapatkan detail rincian Sales Order untuk pembuatan Delivery Order (dengan kalkulasi Pengiriman Parsial & Stok Gudang)
     */
    public function getSalesOrderDetails(SalesOrder $order, Request $request)
    {
        // ── SECURITY: Auth check ────────────────────────────────────────────
        $user = auth()->user();
        if (!$user || !$user->can('tambah-delivery-order')) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // ── SECURITY: Tenancy Isolation — pastikan SO milik company/branch aktif ──
        $activeCompanyId = session('active_company_id') ?? $user->company_id;
        $activeBranchId  = session('active_branch_id');

        $ownershipOk = false;
        if (!empty($activeBranchId)) {
            // Akses cabang: SO harus milik cabang yang sama (branch_id) atau PT induk yang sama
            $ownershipOk = ($order->branch_id == $activeBranchId)
                        || ($order->company_id == $activeCompanyId && empty($order->branch_id));
        } else {
            // Akses PT Pusat: SO harus milik company ini dan bukan milik cabang tertentu
            $ownershipOk = ($order->company_id == $activeCompanyId);
        }

        if (!$ownershipOk) {
            return response()->json(['success' => false, 'message' => 'Sales Order tidak ditemukan atau bukan milik entitas aktif.'], 403);
        }

        $order->load([
            'customer:id,nama_customer,nama_perusahaan,telepon,hp',
            'customer.defaultAddress',
            'tender:id,nomor_kontrak,nama_tender',
            'quotation.tender:id,nomor_kontrak,nama_tender',
            'details.barang:id,kode_barang,nama_barang,satuan_id,tipe_pelacakan',
            'details.barang.satuan:id,nama_satuan',
        ]);

        if (!$order->tender) {
            if ($order->quotation && $order->quotation->tender) {
                $order->setRelation('tender', $order->quotation->tender);
            } else {
                $tenderBySo = \App\Models\Tender::where('sales_order_id', $order->id)->first(['id', 'nomor_kontrak', 'nama_tender']);
                if ($tenderBySo) {
                    $order->setRelation('tender', $tenderBySo);
                }
            }
        }

        $gudangId = $request->gudang_id;

        $detailIds = $order->details->pluck('id')->filter()->all();
        $barangIds = $order->details->pluck('barang_id')->filter()->unique()->all();

        // 1. Bulk Delivered Qty
        $deliveredMap = !empty($detailIds)
            ? DeliveryOrderDetail::whereIn('sales_order_detail_id', $detailIds)
                ->whereHas('deliveryOrder', fn($q) => $q->where('status', '!=', 'Dibatalkan'))
                ->selectRaw('sales_order_detail_id, SUM(qty_kirim) as total_delivered')
                ->groupBy('sales_order_detail_id')
                ->pluck('total_delivered', 'sales_order_detail_id')
            : collect();

        // 2. Bulk Stok Gudang
        $stokGudangMap = (!empty($barangIds) && $gudangId)
            ? StokGudang::whereIn('barang_id', $barangIds)
                ->where('gudang_id', $gudangId)
                ->pluck('stok_aktual', 'barang_id')
            : collect();

        // 3. Bulk Serial Available Count
        $serialsCountMap = collect();
        if (!empty($barangIds)) {
            $snQuery = SpkPenerimaanBarangJadiSerial::forActiveCompany()
                ->whereIn('barang_jadi_id', $barangIds)
                ->where('status', 'Tersedia');
            if ($gudangId) {
                $snQuery->whereHas('penerimaan', fn($q) => $q->where('gudang_id', $gudangId));
            }
            $serialsCountMap = $snQuery->selectRaw('barang_jadi_id, count(*) as count_available')
                ->groupBy('barang_jadi_id')
                ->pluck('count_available', 'barang_jadi_id');
        }

        // Hitung akumulasi qty yang sudah pernah dikirim untuk setiap item
        foreach ($order->details as $d) {
            $deliveredQty = (float) ($deliveredMap->get($d->id) ?? 0);
            $qtyOrder     = (float) $d->qty;
            $qtySisa      = max(0, $qtyOrder - $deliveredQty);

            $d->qty_delivered = $deliveredQty;
            $d->qty_sisa      = $qtySisa;
            $d->is_fulfilled  = ($qtySisa <= 0);

            $d->stok_gudang = (float) ($d->barang_id ? ($stokGudangMap->get($d->barang_id) ?? 0) : 0);

            $availableSerialsCount = (int) ($d->barang_id ? ($serialsCountMap->get($d->barang_id) ?? 0) : 0);
            $isTrackingSn          = $d->barang && $d->barang->tipe_pelacakan === 'Serial Number';

            $d->available_serials_count = $availableSerialsCount;
            $d->is_tracking_sn          = $isTrackingSn;
            $d->has_available_sn        = ($availableSerialsCount > 0);
        }

        return response()->json([
            'success' => true,
            'order'   => $order,
        ]);
    }

    /**
     * Dapatkan stok fisik aktual produk di gudang tertentu (AJAX live update saat pilih gudang)
     */
    public function getGudangStocks(Request $request)
    {
        // ── SECURITY: Auth check ────────────────────────────────────────────
        $user = auth()->user();
        if (!$user || (!$user->can('tambah-delivery-order') && !$user->can('edit-delivery-order'))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'gudang_id'    => 'required|integer',
            'barang_ids'   => 'required|array|max:200',
            'barang_ids.*' => 'nullable|integer|min:1',
        ]);

        // ── SECURITY: Tenancy — pastikan gudang_id milik entitas aktif ─────
        $validGudang = MasterGudang::active()->forActiveEntity()->where('id', $request->gudang_id)->exists();
        if (!$validGudang) {
            return response()->json(['success' => false, 'message' => 'Gudang tidak valid atau bukan milik entitas aktif.'], 403);
        }

        $barangIds = array_filter(array_map('intval', (array) $request->barang_ids));

        $stocks = [];
        if (!empty($barangIds)) {
            $stocks = StokGudang::where('gudang_id', $request->gudang_id)
                ->whereIn('barang_id', $barangIds)
                ->pluck('stok_aktual', 'barang_id')
                ->map(fn($val) => (float)$val)
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'stocks'  => $stocks,
        ]);
    }

    /**
     * Dapatkan daftar Serial Number yang tersedia (status Tersedia) untuk suatu produk & gudang
     */
    public function getAvailableSerials(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|integer',
        ]);

        $query = SpkPenerimaanBarangJadiSerial::forActiveCompany()
            ->where('barang_jadi_id', $request->barang_id)
            ->where('status', 'Tersedia')
            ->whereHas('penerimaan.gudang', function($q) {
                $q->forActiveEntity();
            })
            ->with(['penerimaan.gudang:id,nama_gudang,kode_gudang']);

        if ($request->filled('gudang_id')) {
            $query->whereHas('penerimaan', function($q) use ($request) {
                $q->where('gudang_id', $request->gudang_id);
            });
        }

        // Integritas Data: Kecualikan nomor seri yang sedang dialokasikan di DO lain yang berstatus aktif / pending
        $ignoreDoId = $request->input('delivery_order_id');
        $allocatedSerialsQuery = DeliveryOrderDetail::where('barang_id', $request->barang_id)
            ->whereHas('deliveryOrder', function($q) use ($ignoreDoId) {
                $q->whereIn('status', ['Draft', 'Menunggu Persetujuan', 'Disetujui', 'Siap Dikirim', 'Dalam Perjalanan']);
                if ($ignoreDoId) {
                    $q->where('id', '!=', $ignoreDoId);
                }
            })
            ->whereNotNull('serial_numbers');

        $allocatedSerials = [];
        foreach ($allocatedSerialsQuery->pluck('serial_numbers') as $sns) {
            if (is_array($sns)) {
                foreach ($sns as $sn) {
                    if (!empty($sn)) $allocatedSerials[] = $sn;
                }
            } elseif (is_string($sns)) {
                $decoded = json_decode($sns, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $sn) {
                        if (!empty($sn)) $allocatedSerials[] = $sn;
                    }
                }
            }
        }

        if (!empty($allocatedSerials)) {
            $query->whereNotIn('serial_number', array_values(array_unique($allocatedSerials)));
        }

        $serials = $query->orderBy('id', 'asc')->limit(500)->get()->map(function($s) {
            return [
                'id'            => $s->id,
                'serial_number' => $s->serial_number,
                'nomor_batch'   => $s->nomor_batch,
                'gudang'        => $s->penerimaan?->gudang?->nama_gudang ?? 'Gudang Utama',
                'penerimaan_no' => $s->penerimaan?->nomor_penerimaan ?? '-',
            ];
        });

        return response()->json([
            'success' => true,
            'serials' => $serials,
        ]);
    }

    /**
     * Cek ketersediaan Nomor Surat Jalan (DO) manual secara live via AJAX
     * Mengunci pengecekan anti-duplikasi di seluruh sistem (termasuk yang pernah di-soft-delete)
     */
    public function checkNomorDo(Request $request)
    {
        // ── SECURITY: Auth check — mencegah enumerasi nomor DO oleh pihak luar ──
        $user = auth()->user();
        if (!$user || !$user->can('tambah-delivery-order')) {
            return response()->json(['available' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // ── SECURITY: Sanitasi & panjang input ─────────────────────────────
        $nomorDo = trim((string) $request->nomor_do);
        if (empty($nomorDo)) {
            return response()->json(['available' => true]);
        }
        if (mb_strlen($nomorDo) > 100) {
            return response()->json(['available' => false, 'message' => 'Nomor DO terlalu panjang.']);
        }

        // Cek ketat pada seluruh tabel tanpa terpengaruh soft-delete atau isolasi cabang
        $isDuplicate = DeliveryOrder::withTrashed()->where('nomor_do', $nomorDo)->exists();

        return response()->json([
            'available' => !$isDuplicate,
            'message'   => $isDuplicate 
                ? "Nomor DO \"{$nomorDo}\" sudah pernah digunakan (termasuk arsip/data yang pernah dihapus)!" 
                : "Nomor DO \"{$nomorDo}\" tersedia & siap digunakan."
        ]);
    }

    /**
     * Simpan Surat Jalan / Delivery Order baru (Mendukung Pengiriman Parsial)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->can('tambah-delivery-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk membuat Surat Jalan / Pengiriman baru.'], 403);
        }

        $request->validate([
            'tanggal_kirim'      => 'required|date',
            'gudang_id'          => 'required|exists:master_gudang,id',
            'metode_pengiriman'  => 'required|string|max:50',
            'items'              => 'required|array|min:1',
            'items.*.qty_kirim'  => 'required|numeric|min:0.01',
        ]);

        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');

        // Keamanan Tenancy: Validasi kepemilikan gudang asal terhadap entitas aktif (PT Pusat vs Cabang)
        $validGudang = MasterGudang::active()->forActiveEntity()->where('id', $request->gudang_id)->first();
        if (!$validGudang) {
            $entitas = !empty($activeBranchId) ? 'Cabang yang sedang aktif' : 'PT Pusat yang sedang aktif';
            return response()->json([
                'success' => false,
                'message' => "Akses Ditolak: Gudang asal yang dipilih tidak terdaftar atau bukan milik {$entitas}!"
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Validasi & Penentuan Nomor DO (Otomatis vs Manual)
            $modeNomorDo   = $request->input('mode_nomor_do', 'auto');
            $customNomorDo = trim((string) $request->input('nomor_do', ''));

            if ($modeNomorDo === 'manual') {
                if (empty($customNomorDo)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Nomor Surat Jalan (DO) manual wajib diisi!'
                    ], 422);
                }

                // Validasi duplikasi ketat: Tidak boleh duplikat di seluruh sistem (termasuk yang pernah di-soft-delete)
                $isDuplicate = DeliveryOrder::withTrashed()->where('nomor_do', $customNomorDo)->exists();
                if ($isDuplicate) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Nomor Surat Jalan (DO) \"{$customNomorDo}\" sudah pernah digunakan (termasuk pada arsip/data yang pernah dihapus). Mohon gunakan nomor yang berbeda agar tidak terjadi duplikasi."
                    ], 422);
                }
                $nomorDo = $customNomorDo;
            } else {
                // Mode Otomatis:
                // Prefix: DO-YYMM- (Global di seluruh sistem PT dan Cabang agar urutan konsisten dan tidak duplikat)
                $prefix = 'DO-' . date('ym') . '-';

                // Kunci nomor urut dengan lockForUpdate untuk mencegah race-condition jika PT & Cabang klik simpan di milidetik bersamaan
                // Menggunakan orderBy('nomor_do', 'desc') memanfaatkan covering index range scan super cepat (<0.1ms)
                $lastDo = DeliveryOrder::withTrashed()
                    ->where('nomor_do', 'LIKE', $prefix . '%')
                    ->orderBy('nomor_do', 'desc')
                    ->lockForUpdate()
                    ->first();

                $nextNum = 1;
                if ($lastDo) {
                    $parts = explode('-', $lastDo->nomor_do);
                    $lastSeq = (int) end($parts);
                    $nextNum = $lastSeq + 1;
                }

                // Loop safety check against withTrashed()
                do {
                    $nomorDo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
                    $exists = DeliveryOrder::withTrashed()->where('nomor_do', $nomorDo)->exists();
                    if ($exists) {
                        $nextNum++;
                    }
                } while ($exists);
            }

            $salesOrder = null;
            if ($request->filled('sales_order_id')) {
                // ── SECURITY: Tenancy — SO harus milik entitas aktif (cegah SO-spoofing cross-company) ──
                $salesOrder = SalesOrder::forActiveCompany()->find($request->sales_order_id);
                if (!$salesOrder) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Sales Order tidak ditemukan atau tidak dapat digunakan oleh entitas aktif.',
                    ], 422);
                }
            }

            // VALIDASI KETIDAKCUKUPAN STOK GUDANG & KELENGKAPAN SERIAL NUMBER SEBELUM PROSES
            $itemDetailIds = collect($request->items)->pluck('sales_order_detail_id')->filter()->all();
            $soDetailsMap = !empty($itemDetailIds) ? SalesOrderDetail::whereIn('id', $itemDetailIds)->get()->keyBy('id') : collect();

            $alreadyDeliveredMap = !empty($itemDetailIds)
                ? DeliveryOrderDetail::whereIn('sales_order_detail_id', $itemDetailIds)
                    ->whereHas('deliveryOrder', fn($q) => $q->where('status', '!=', 'Dibatalkan'))
                    ->selectRaw('sales_order_detail_id, SUM(qty_kirim) as total_delivered')
                    ->groupBy('sales_order_detail_id')
                    ->pluck('total_delivered', 'sales_order_detail_id')
                : collect();

            $itemBarangIds = collect($request->items)->pluck('barang_id')->filter()->unique()->all();
            $barangModelsMap = !empty($itemBarangIds) ? MasterBarang::whereIn('id', $itemBarangIds)->get()->keyBy('id') : collect();
            $stokGudangMap = !empty($itemBarangIds)
                ? StokGudang::whereIn('barang_id', $itemBarangIds)
                    ->where('gudang_id', $request->gudang_id)
                    ->pluck('stok_aktual', 'barang_id')
                : collect();

            foreach ($request->items as $item) {
                $qtyKirim = (float) ($item['qty_kirim'] ?? 0);
                $barangId = $item['barang_id'] ?? null;
                if ($qtyKirim <= 0) continue;

                $barangModel = !empty($barangId) ? $barangModelsMap->get($barangId) : null;
                $namaBarang  = $item['nama_barang_custom'] ?? ($barangModel ? $barangModel->nama_barang : 'Produk');
                $satuan      = $item['nama_satuan'] ?? 'Unit';

                // Validasi: Qty Kirim TIDAK BOLEH melebihi sisa pesanan Sales Order
                if (!empty($item['sales_order_detail_id'])) {
                    $soDetail = $soDetailsMap->get($item['sales_order_detail_id']);
                    if ($soDetail) {
                        $alreadyDelivered = (float) ($alreadyDeliveredMap->get($soDetail->id) ?? 0);
                        $sisaSo = max(0, (float)$soDetail->qty - $alreadyDelivered);
                        if ($qtyKirim > $sisaSo) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Jumlah kirim untuk produk \"{$namaBarang}\" ({$qtyKirim} {$satuan}) melebihi sisa pesanan Sales Order! Sisa pesanan yang dapat dikirim: {$sisaSo} {$satuan}.",
                            ], 422);
                        }
                    }
                }

                // Pengecekan Ketat Stok Fisik di Gudang Asal: jika tidak cukup, maka TIDAK BISA diajukan/diproses!
                if (!empty($barangId)) {
                    $stokTersedia = (float) ($stokGudangMap->get($barangId) ?? 0);

                    if ($qtyKirim > $stokTersedia) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Stok di gudang tidak mencukupi untuk \"{$namaBarang}\"! Stok fisik tersedia: {$stokTersedia} {$satuan}, permintaan kirim: {$qtyKirim} {$satuan}. Silakan sesuaikan jumlah kirim atau isi stok gudang terlebih dahulu.",
                        ], 422);
                    }
                }

                // Parsing Serial Numbers yang dikirim
                $rawSerials = $item['serial_numbers'] ?? [];
                if (is_string($rawSerials)) {
                    $decoded = json_decode($rawSerials, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $rawSerials = $decoded;
                    } else {
                        $rawSerials = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $rawSerials)));
                    }
                }
                $serials = [];
                if (is_array($rawSerials)) {
                    foreach ($rawSerials as $s) {
                        $sTrim = trim((string)$s);
                        if ($sTrim !== '') $serials[] = $sTrim;
                    }
                    $serials = array_values(array_unique($serials));
                }

                // Penggunaan Serial Number bersifat fleksibel (opsional jika user tidak ingin menginput SN)
                $maxAllowedSn = (int) round($qtyKirim);
                if (!empty($serials) && count($serials) > $maxAllowedSn) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Jumlah Serial Number untuk \"{$namaBarang}\" (" . count($serials) . " SN) melebihi Jumlah Kirim ({$maxAllowedSn} {$satuan}).",
                    ], 422);
                }

                // Validasi Ketat (Opsi C): Setiap nomor seri yang dikirim WAJIB telah terdaftar di gudang untuk kode barang tersebut
                if (!empty($serials)) {
                    if (empty($barangId)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Item custom \"{$namaBarang}\" tidak dapat menggunakan nomor seri karena tidak memiliki kode master barang di sistem.",
                        ], 422);
                    }

                    $kodeBarang = $barangModel ? $barangModel->kode_barang : '-';

                    // Cek apakah produk ini memiliki serial number yang terdaftar di gudang
                    $totalRegisteredCount = SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $barangId)->count();
                    if ($totalRegisteredCount === 0) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Kode barang \"{$kodeBarang}\" ({$namaBarang}) tidak memiliki Serial Number yang terdaftar di sistem gudang. Nomor seri tidak dapat ditambahkan untuk barang ini.",
                        ], 422);
                    }

                    $matchedRecords = SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $barangId)
                        ->whereIn('serial_number', $serials)
                        ->get();

                    $foundMap = $matchedRecords->keyBy('serial_number');
                    $unregisteredSerials = [];
                    $unavailableSerials = [];

                    foreach ($serials as $sn) {
                        if (!$foundMap->has($sn)) {
                            $unregisteredSerials[] = $sn;
                        } else {
                            $rec = $foundMap->get($sn);
                            if ($rec->status !== 'Tersedia') {
                                $unavailableSerials[] = "{$sn} (status: {$rec->status})";
                            }
                        }
                    }

                    if (!empty($unregisteredSerials)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Serial Number berikut TIDAK TERDAFTAR untuk kode barang \"{$kodeBarang}\" ({$namaBarang}) di gudang: " 
                                . implode(', ', $unregisteredSerials) 
                                . ". Sesuai SOP sistem, seluruh nomor seri yang dikirim wajib sudah resmi terdaftar di gudang terlebih dahulu.",
                        ], 422);
                    }

                    if (!empty($unavailableSerials)) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Serial Number untuk kode barang \"{$kodeBarang}\" ({$namaBarang}) berikut tidak dapat dikirim karena tidak berstatus Tersedia: " 
                                . implode(', ', $unavailableSerials) 
                                . ". Silakan pilih nomor seri yang masih tersedia di stok gudang.",
                        ], 422);
                    }
                }
            }

            $deliveryOrder = DeliveryOrder::create([
                'nomor_do'           => $nomorDo,
                'sales_order_id'     => $salesOrder?->id,
                'customer_id'        => $salesOrder?->customer_id ?? $request->customer_id,
                'nama_penerima'      => $request->nama_penerima ?: ($salesOrder?->customer?->nama_customer ?: $salesOrder?->nama_customer_walkin),
                'telepon_penerima'   => $request->telepon_penerima ?: ($salesOrder?->customer?->telepon ?: $salesOrder?->telepon_customer_walkin),
                'alamat_tujuan'      => $request->alamat_tujuan ?: $salesOrder?->alamat_pengiriman,
                'company_id'         => !empty($activeBranchId) ? null : $activeCompanyId,
                'branch_id'          => !empty($activeBranchId) ? $activeBranchId : null,
                'gudang_id'          => $request->gudang_id,
                'tanggal_kirim'      => $request->tanggal_kirim,
                'estimasi_tiba'      => $request->estimasi_tiba,
                'metode_pengiriman'  => $request->metode_pengiriman,
                'nama_ekspedisi'     => $request->nama_ekspedisi,
                'nomor_resi'         => $request->nomor_resi,
                'nama_sopir'         => $request->nama_sopir,
                'plat_nomor'         => $request->plat_nomor,
                'biaya_ongkir'       => $request->biaya_ongkir ?: 0,
                'catatan_pengiriman' => $request->catatan_pengiriman,
                'status'             => 'Menunggu Persetujuan',
            ]);

            foreach ($request->items as $item) {
                $qtyKirim = (float) $item['qty_kirim'];
                if ($qtyKirim <= 0) continue;

                // Parsing Serial Numbers untuk disimpan di detail item
                $rawSerials = $item['serial_numbers'] ?? [];
                if (is_string($rawSerials)) {
                    $decoded = json_decode($rawSerials, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $rawSerials = $decoded;
                    } else {
                        $rawSerials = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $rawSerials)));
                    }
                }
                $serials = [];
                if (is_array($rawSerials)) {
                    foreach ($rawSerials as $s) {
                        $sTrim = trim((string)$s);
                        if ($sTrim !== '') $serials[] = $sTrim;
                    }
                    $serials = array_values(array_unique($serials));
                }

                $doDetail = DeliveryOrderDetail::create([
                    'delivery_order_id'     => $deliveryOrder->id,
                    'sales_order_detail_id' => $item['sales_order_detail_id'] ?? null,
                    'barang_id'             => $item['barang_id'] ?? null,
                    'nama_barang_custom'    => $item['nama_barang_custom'] ?? null,
                    'qty_order'             => (float) ($item['qty_order'] ?? $qtyKirim),
                    'qty_kirim'             => $qtyKirim,
                    'nama_satuan'           => $item['nama_satuan'] ?? 'Pcs',
                    'catatan_item'          => $item['catatan_item'] ?? null,
                    'serial_numbers'        => !empty($serials) ? $serials : null,
                ]);

                // Jika status langsung Disetujui / Siap Dikirim / Dalam Perjalanan / Terkirim: potong stok gudang
                if (in_array($deliveryOrder->status, ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim']) && !empty($item['barang_id'])) {
                    $this->potongStokGudang($deliveryOrder, $item['barang_id'], $qtyKirim, $request->gudang_id);

                    if (!empty($serials)) {
                        $custName = $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan');
                        SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $item['barang_id'])
                            ->whereIn('serial_number', $serials)
                            ->where('status', 'Tersedia')
                            ->update([
                                'status'  => 'Terjual',
                                'catatan' => "Terkirim via Surat Jalan {$nomorDo} ke {$custName}",
                            ]);
                    }
                }
            }

            // Sinkronisasi status Sales Order berdasarkan kelengkapan pengiriman
            if ($salesOrder) {
                $this->syncSalesOrderStatus($salesOrder);
            }

            DB::commit();

            // Pemicu Notifikasi WhatsApp ke Atasan / Approver
            if ($deliveryOrder->status === 'Menunggu Persetujuan') {
                WhatsAppGatewayController::sendDeliveryOrderSubmissionNotification($deliveryOrder);
            }

            return response()->json([
                'success' => true,
                'message' => "Surat Jalan {$nomorDo} berhasil diajukan! Notifikasi WhatsApp telah dikirimkan ke atasan untuk persetujuan (ACC).",
                'do_id'   => $deliveryOrder->id,
            ]);
        } catch (\Illuminate\Database\QueryException $qe) {
            DB::rollBack();
            if ($qe->getCode() == 23000 || str_contains($qe->getMessage(), 'Duplicate entry') || str_contains($qe->getMessage(), 'delivery_orders_nomor_do_unique')) {
                return response()->json([
                    'success' => false,
                    'message' => "Nomor Surat Jalan (DO) \"{$nomorDo}\" terdeteksi duplikat atau baru saja digunakan oleh transaksi lain. Mohon gunakan nomor DO lain atau pilih mode otomatis.",
                ], 422);
            }
            Log::error('QueryException store DeliveryOrder: ' . $qe->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan basis data: ' . $qe->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store DeliveryOrder: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan Surat Jalan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lihat Rincian Surat Jalan (Modal Detail JSON)
     */
    public function show(DeliveryOrder $deliveryOrder)
    {
        $user = auth()->user();
        if (!$user || !$user->can('lihat-delivery-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk melihat rincian Pengiriman.'], 403);
        }

        // ── SECURITY: Anti-IDOR — pastikan DO milik entitas aktif ──────────
        $activeCompanyId = session('active_company_id') ?? $user->company_id;
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeBranchId)) {
            // Mode Cabang: DO harus punya branch_id sama
            if ((string)$deliveryOrder->branch_id !== (string)$activeBranchId) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data ini bukan milik cabang Anda.'], 403);
            }
        } else {
            // Mode PT Pusat: DO harus punya company_id sama dan branch_id null
            if ((string)$deliveryOrder->company_id !== (string)$activeCompanyId || !is_null($deliveryOrder->branch_id)) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data ini bukan milik PT yang sedang aktif.'], 403);
            }
        }

        $deliveryOrder->load([
            'salesOrder:id,nomor_so,tanggal_so,status,grand_total,tender_id,sales_quotation_id',
            'salesOrder.tender:id,nomor_kontrak,nama_tender',
            'salesOrder.quotation.tender:id,nomor_kontrak,nama_tender',
            'customer:id,nama_customer,nama_perusahaan,telepon,hp',
            'customer.defaultAddress',
            'company:id,nama_perusahaan,logo,address,phone,email,npwp',
            'branch:id,nama_cabang,address,phone,email',
            'gudang:id,nama_gudang,kode_gudang,alamat',
            'creator:id,nama_lengkap,username',
            'approver:id,nama_lengkap,username',
            'receiver:id,nama_lengkap,username',
            'details.barang:id,kode_barang,nama_barang,satuan_id,tipe_pelacakan',
            'details.barang.satuan:id,nama_satuan',
            'jurnal.details.account:id,code,name,type',
            'jurnal.branch:id,nama_cabang',
            'jurnal.company:id,nama_perusahaan',
        ]);

        // Resolusi cadangan No. Tender jika tender_id langsung pada SO kosong
        if ($deliveryOrder->salesOrder && !$deliveryOrder->salesOrder->tender) {
            if ($deliveryOrder->salesOrder->quotation && $deliveryOrder->salesOrder->quotation->tender) {
                $deliveryOrder->salesOrder->setRelation('tender', $deliveryOrder->salesOrder->quotation->tender);
            } else {
                $tenderBySo = \App\Models\Tender::where('sales_order_id', $deliveryOrder->salesOrder->id)->first(['id', 'nomor_kontrak', 'nama_tender']);
                if ($tenderBySo) {
                    $deliveryOrder->salesOrder->setRelation('tender', $tenderBySo);
                }
            }
        }

        // Auto-sync no_tender ke jurnal jika jurnal ada dan DO terkait tender
        if ($deliveryOrder->jurnal && empty($deliveryOrder->jurnal->no_tender)) {
            $tenderObj = $deliveryOrder->salesOrder?->tender;
            if ($tenderObj) {
                $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                    ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                    : ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id));

                $deliveryOrder->jurnal->update(['no_tender' => $nomorTender]);
                $deliveryOrder->jurnal->no_tender = $nomorTender;
            }
        }

        return response()->json([
            'success' => true,
            'do'      => $deliveryOrder,
        ]);
    }

    /**
     * Dapatkan Data Surat Jalan untuk Form Edit (Modal Edit JSON)
     */
    public function edit(DeliveryOrder $deliveryOrder)
    {
        $user = auth()->user();
        if (!$user || !$user->can('edit-delivery-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk mengedit Surat Jalan.'], 403);
        }

        // ── SECURITY: Anti-IDOR — pastikan DO milik entitas aktif ──────────
        $activeCompanyId = session('active_company_id') ?? $user->company_id;
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeBranchId)) {
            if ((string)$deliveryOrder->branch_id !== (string)$activeBranchId) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data ini bukan milik cabang Anda.'], 403);
            }
        } else {
            if ((string)$deliveryOrder->company_id !== (string)$activeCompanyId || !is_null($deliveryOrder->branch_id)) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data ini bukan milik PT yang sedang aktif.'], 403);
            }
        }

        $nonEditableStatuses = ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Selesai'];
        if (in_array($deliveryOrder->status, $nonEditableStatuses)) {
            return response()->json([
                'success' => false,
                'message' => "Surat Jalan berstatus \"{$deliveryOrder->status}\" telah disetujui / diproses dan tidak dapat diedit atau direvisi.",
            ], 422);
        }

        $deliveryOrder->load([
            'salesOrder:id,nomor_so,tanggal_so,status,customer_id,nama_customer_walkin,telepon_customer_walkin,alamat_pengiriman,pricing_tier,tender_id,sales_quotation_id',
            'salesOrder.tender:id,nomor_kontrak,nama_tender',
            'salesOrder.quotation.tender:id,nomor_kontrak,nama_tender',
            'customer:id,nama_customer,nama_perusahaan,telepon,hp',
            'customer.defaultAddress',
            'gudang:id,nama_gudang,kode_gudang',
            'details.barang:id,kode_barang,nama_barang,satuan_id,tipe_pelacakan',
            'details.barang.satuan:id,nama_satuan',
            'details.salesOrderDetail',
        ]);

        // Resolusi cadangan No. Tender jika tender_id langsung pada SO kosong
        if ($deliveryOrder->salesOrder && !$deliveryOrder->salesOrder->tender) {
            if ($deliveryOrder->salesOrder->quotation && $deliveryOrder->salesOrder->quotation->tender) {
                $deliveryOrder->salesOrder->setRelation('tender', $deliveryOrder->salesOrder->quotation->tender);
            } else {
                $tenderBySo = \App\Models\Tender::where('sales_order_id', $deliveryOrder->salesOrder->id)->first(['id', 'nomor_kontrak', 'nama_tender']);
                if ($tenderBySo) {
                    $deliveryOrder->salesOrder->setRelation('tender', $tenderBySo);
                }
            }
        }

        $canEditItems = in_array($deliveryOrder->status, ['Draft', 'Menunggu Persetujuan', 'Dibatalkan', 'Ditolak']);

        // Persiapkan data sisa pesanan & stok untuk setiap item detail secara bulk
        $details = $deliveryOrder->details;
        $soDetailIds = $details->pluck('sales_order_detail_id')->filter()->unique()->all();
        $barangIds   = $details->pluck('barang_id')->filter()->unique()->all();

        // 1. Bulk query pengiriman oleh DO lain yang aktif
        $deliveredByOthersMap = !empty($soDetailIds)
            ? DeliveryOrderDetail::whereIn('sales_order_detail_id', $soDetailIds)
                ->where('delivery_order_id', '!=', $deliveryOrder->id)
                ->whereHas('deliveryOrder', function($q) {
                    $q->where('status', '!=', 'Dibatalkan');
                })
                ->groupBy('sales_order_detail_id')
                ->selectRaw('sales_order_detail_id, SUM(qty_kirim) as total_delivered')
                ->pluck('total_delivered', 'sales_order_detail_id')
            : collect();

        // 2. Bulk query stok aktual gudang
        $stokGudangMap = (!empty($barangIds) && $deliveryOrder->gudang_id)
            ? StokGudang::whereIn('barang_id', $barangIds)
                ->where('gudang_id', $deliveryOrder->gudang_id)
                ->pluck('stok_aktual', 'barang_id')
            : collect();

        $itemsData = [];
        foreach ($details as $d) {
            $maxAllowed = (float) $d->qty_kirim;
            $sisaBelumKirim = 0;
            $totalOrder = (float) $d->qty_order;

            if ($d->sales_order_detail_id && $d->salesOrderDetail) {
                $soDetail = $d->salesOrderDetail;
                $totalOrder = (float) $soDetail->qty;
                
                $deliveredByOthers = (float) ($deliveredByOthersMap->get($soDetail->id, 0));
                $maxAllowed = max(0, (float)$soDetail->qty - $deliveredByOthers);
                $sisaBelumKirim = max(0, $maxAllowed - (float)$d->qty_kirim);
            }

            // Cek stok gudang aktual dari bulk map
            $stokGudang = (float) ($stokGudangMap->get($d->barang_id, 0));

            $itemsData[] = [
                'id'                     => $d->id,
                'sales_order_detail_id' => $d->sales_order_detail_id,
                'barang_id'              => $d->barang_id,
                'kode_barang'            => $d->barang?->kode_barang ?? '-',
                'nama_barang'            => $d->nama_barang_custom ?: ($d->barang?->nama_barang ?? 'Item'),
                'nama_satuan'            => $d->nama_satuan ?: ($d->barang?->satuan?->nama_satuan ?? 'Pcs'),
                'tipe_pelacakan'         => $d->barang?->tipe_pelacakan ?? 'None',
                'qty_order'              => $totalOrder,
                'qty_kirim'              => (float) $d->qty_kirim,
                'max_allowed'            => $maxAllowed,
                'sisa_belum_kirim'       => $sisaBelumKirim,
                'stok_gudang'            => $stokGudang,
                'catatan_item'           => $d->catatan_item ?? '',
                'serial_numbers'         => $d->serial_numbers ?? [],
            ];
        }

        // Ambil daftar gudang milik entitas aktif
        $gudangs = MasterGudang::active()
            ->forActiveEntity()
            ->orderBy('nama_gudang')
            ->get(['id', 'nama_gudang', 'kode_gudang']);

        return response()->json([
            'success'        => true,
            'do'             => $deliveryOrder,
            'can_edit_items' => $canEditItems,
            'items'          => $itemsData,
            'gudangs'        => $gudangs,
        ]);
    }

    /**
     * Simpan Perubahan Surat Jalan (Update Delivery Order)
     */
    public function update(DeliveryOrder $deliveryOrder, Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->can('edit-delivery-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk mengedit Surat Jalan.'], 403);
        }

        // ── SECURITY: Anti-IDOR — pastikan DO milik entitas aktif ──────────
        $activeCompanyId = session('active_company_id') ?? $user->company_id;
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeBranchId)) {
            if ((string)$deliveryOrder->branch_id !== (string)$activeBranchId) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data ini bukan milik cabang Anda.'], 403);
            }
        } else {
            if ((string)$deliveryOrder->company_id !== (string)$activeCompanyId || !is_null($deliveryOrder->branch_id)) {
                return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data ini bukan milik PT yang sedang aktif.'], 403);
            }
        }

        $nonEditableStatuses = ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Selesai'];
        if (in_array($deliveryOrder->status, $nonEditableStatuses)) {
            return response()->json([
                'success' => false,
                'message' => "Surat Jalan berstatus \"{$deliveryOrder->status}\" telah disetujui / diproses dan tidak dapat diedit atau direvisi.",
            ], 422);
        }

        $isRevision   = in_array($deliveryOrder->status, ['Dibatalkan', 'Ditolak']);
        $canEditItems = in_array($deliveryOrder->status, ['Draft', 'Menunggu Persetujuan', 'Dibatalkan', 'Ditolak']);

        $request->validate([
            'tanggal_kirim'      => 'required|date',
            'metode_pengiriman'  => 'nullable|string|max:100',
            'nama_ekspedisi'     => 'nullable|string|max:150',
            'nomor_resi'         => 'nullable|string|max:100',
            'nama_sopir'         => 'nullable|string|max:150',
            'plat_nomor'         => 'nullable|string|max:50',
            'catatan_pengiriman' => 'nullable|string|max:1000',
            'nama_penerima'      => 'nullable|string|max:255',
            'telepon_penerima'   => 'nullable|string|max:50',
            'alamat_tujuan'      => 'nullable|string',
            'gudang_id'          => $canEditItems ? 'required|exists:master_gudang,id' : 'nullable',
            'items'              => $canEditItems ? 'required|array|min:1' : 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Update data header logistik
            $updateHeader = [
                'tanggal_kirim'      => $request->tanggal_kirim,
                'metode_pengiriman'  => $request->metode_pengiriman,
                'nama_ekspedisi'     => $request->nama_ekspedisi,
                'nomor_resi'         => $request->nomor_resi,
                'nama_sopir'         => $request->nama_sopir,
                'plat_nomor'         => $request->plat_nomor,
                'catatan_pengiriman' => $request->catatan_pengiriman,
                'nama_penerima'      => $request->nama_penerima,
                'telepon_penerima'   => $request->telepon_penerima,
                'alamat_tujuan'      => $request->alamat_tujuan,
            ];

            // Jika DO sebelumnya DITOLAK / DIBATALKAN, kembalikan ke Menunggu Persetujuan untuk workflow persetujuan ulang
            if ($isRevision) {
                $updateHeader['status'] = 'Menunggu Persetujuan';
                $updateHeader['approved_by'] = null;
                $updateHeader['approved_at'] = null;
                $prevCatatan = $deliveryOrder->catatan_approval ? " [Catatan sebelumnya: {$deliveryOrder->catatan_approval}]" : '';
                $namaUser = $user->nama_lengkap ?: $user->username;
                $updateHeader['catatan_approval'] = "Revisi diajukan kembali oleh {$namaUser} pada " . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . $prevCatatan;
            }

            if ($canEditItems) {
                // Keamanan Tenancy: Validasi kepemilikan gudang asal
                $validGudang = MasterGudang::active()->forActiveEntity()->where('id', $request->gudang_id)->first();
                if (!$validGudang) {
                    $entitas = !empty($activeBranchId) ? 'Cabang yang sedang aktif' : 'PT Pusat yang sedang aktif';
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Akses Ditolak: Gudang asal yang dipilih tidak terdaftar atau bukan milik {$entitas}!"
                    ], 422);
                }
                $updateHeader['gudang_id'] = $request->gudang_id;

                // VALIDASI ITEMS
                // Bulk pre-fetch models untuk eliminasi query N+1 pada update
                $itemBarangIds = collect($request->items)->pluck('barang_id')->filter()->unique()->all();
                $itemSoDetailIds = collect($request->items)->pluck('sales_order_detail_id')->filter()->unique()->all();

                $barangModels = !empty($itemBarangIds)
                    ? MasterBarang::whereIn('id', $itemBarangIds)->get()->keyBy('id')
                    : collect();

                $soDetails = !empty($itemSoDetailIds)
                    ? SalesOrderDetail::whereIn('id', $itemSoDetailIds)->get()->keyBy('id')
                    : collect();

                $deliveredByOthersMap = !empty($itemSoDetailIds)
                    ? DeliveryOrderDetail::whereIn('sales_order_detail_id', $itemSoDetailIds)
                        ->where('delivery_order_id', '!=', $deliveryOrder->id)
                        ->whereHas('deliveryOrder', function($q) {
                            $q->where('status', '!=', 'Dibatalkan');
                        })
                        ->groupBy('sales_order_detail_id')
                        ->selectRaw('sales_order_detail_id, SUM(qty_kirim) as total_delivered')
                        ->pluck('total_delivered', 'sales_order_detail_id')
                    : collect();

                $stokGudangMap = (!empty($itemBarangIds) && $request->filled('gudang_id'))
                    ? StokGudang::whereIn('barang_id', $itemBarangIds)
                        ->where('gudang_id', $request->gudang_id)
                        ->pluck('stok_aktual', 'barang_id')
                    : collect();

                $totalValidItems = 0;
                foreach ($request->items as $item) {
                    $qtyKirim = (float) ($item['qty_kirim'] ?? 0);
                    if ($qtyKirim <= 0) continue;
                    $totalValidItems++;

                    $barangId    = $item['barang_id'] ?? null;
                    $barangModel = !empty($barangId) ? $barangModels->get($barangId) : null;
                    $namaBarang  = $item['nama_barang_custom'] ?? ($barangModel ? $barangModel->nama_barang : 'Produk');
                    $satuan      = $item['nama_satuan'] ?? 'Unit';

                    // Validasi: Qty Kirim TIDAK BOLEH melebihi sisa pesanan Sales Order
                    if (!empty($item['sales_order_detail_id'])) {
                        $soDetail = $soDetails->get($item['sales_order_detail_id']);
                        if ($soDetail) {
                            $alreadyDeliveredByOthers = (float) ($deliveredByOthersMap->get($soDetail->id, 0));
                            $maxAllowed = max(0, (float)$soDetail->qty - $alreadyDeliveredByOthers);
                            if ($qtyKirim > $maxAllowed) {
                                DB::rollBack();
                                return response()->json([
                                    'success' => false,
                                    'message' => "Jumlah kirim untuk produk \"{$namaBarang}\" ({$qtyKirim} {$satuan}) melebihi batas pesanan Sales Order! Maksimal yang dapat dikirim: {$maxAllowed} {$satuan}.",
                                ], 422);
                            }
                        }
                    }

                    // Pengecekan Ketat Stok Fisik di Gudang Asal
                    if (!empty($barangId)) {
                        $stokTersedia = (float) ($stokGudangMap->get($barangId, 0));
                        if ($qtyKirim > $stokTersedia) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Stok di gudang tidak mencukupi untuk \"{$namaBarang}\"! Stok fisik tersedia: {$stokTersedia} {$satuan}, permintaan kirim: {$qtyKirim} {$satuan}.",
                            ], 422);
                        }
                    }

                    // Parsing & validasi Serial Numbers
                    $rawSerials = $item['serial_numbers'] ?? [];
                    if (is_string($rawSerials)) {
                        $decoded = json_decode($rawSerials, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $rawSerials = $decoded;
                        } else {
                            $rawSerials = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $rawSerials)));
                        }
                    }
                    $serials = [];
                    if (is_array($rawSerials)) {
                        foreach ($rawSerials as $s) {
                            $sTrim = trim((string)$s);
                            if ($sTrim !== '') $serials[] = $sTrim;
                        }
                        $serials = array_values(array_unique($serials));
                    }

                    $maxAllowedSn = (int) round($qtyKirim);
                    if (!empty($serials) && count($serials) > $maxAllowedSn) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Jumlah Serial Number untuk \"{$namaBarang}\" (" . count($serials) . " SN) melebihi Jumlah Kirim ({$maxAllowedSn} {$satuan}).",
                        ], 422);
                    }
                }

                if ($totalValidItems === 0) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Minimal harus ada 1 item produk dengan jumlah kirim lebih dari 0!',
                    ], 422);
                }

                // Hapus item detail lama dan masukkan item detail baru
                $deliveryOrder->details()->delete();

                foreach ($request->items as $item) {
                    $qtyKirim = (float) $item['qty_kirim'];
                    if ($qtyKirim <= 0) continue;

                    $rawSerials = $item['serial_numbers'] ?? [];
                    if (is_string($rawSerials)) {
                        $decoded = json_decode($rawSerials, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $rawSerials = $decoded;
                        } else {
                            $rawSerials = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $rawSerials)));
                        }
                    }
                    $serials = [];
                    if (is_array($rawSerials)) {
                        foreach ($rawSerials as $s) {
                            $sTrim = trim((string)$s);
                            if ($sTrim !== '') $serials[] = $sTrim;
                        }
                        $serials = array_values(array_unique($serials));
                    }

                    DeliveryOrderDetail::create([
                        'delivery_order_id'     => $deliveryOrder->id,
                        'sales_order_detail_id' => $item['sales_order_detail_id'] ?? null,
                        'barang_id'             => $item['barang_id'] ?? null,
                        'nama_barang_custom'    => $item['nama_barang_custom'] ?? null,
                        'qty_order'             => (float) ($item['qty_order'] ?? $qtyKirim),
                        'qty_kirim'             => $qtyKirim,
                        'nama_satuan'           => $item['nama_satuan'] ?? 'Pcs',
                        'catatan_item'          => $item['catatan_item'] ?? null,
                        'serial_numbers'        => !empty($serials) ? $serials : null,
                    ]);
                }
            }

            $deliveryOrder->update($updateHeader);

            // Sinkronisasi status Sales Order
            if ($deliveryOrder->sales_order_id && $deliveryOrder->salesOrder) {
                $this->syncSalesOrderStatus($deliveryOrder->salesOrder);
            }

            DB::commit();

            $successMsg = $isRevision
                ? "Surat Jalan {$deliveryOrder->nomor_do} berhasil direvisi dan diajukan ulang untuk persetujuan!"
                : "Surat Jalan {$deliveryOrder->nomor_do} berhasil diperbarui!";

            return response()->json([
                'success' => true,
                'message' => $successMsg,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update DeliveryOrder: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Surat Jalan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Setujui / ACC Pengajuan Pengiriman (Oleh Atasan)
     */
    public function approve(DeliveryOrder $deliveryOrder, Request $request)
    {
        $user = auth()->user();
        $isApprover = $user && $user->can('approve-delivery-order');
        if (!$isApprover) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk menyetujui (ACC) pengiriman ini.'], 403);
        }

        // ── SECURITY: Anti-IDOR — pastikan DO milik entitas aktif ──────────
        if (!$this->checkEntityAccess($deliveryOrder, $user)) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data pengiriman bukan milik entitas/cabang Anda yang sedang aktif.'], 403);
        }

        if (!in_array($deliveryOrder->status, ['Menunggu Persetujuan', 'Draft'])) {
            return response()->json(['success' => false, 'message' => "Surat Jalan ini tidak dalam status Menunggu Persetujuan."], 422);
        }

        DB::beginTransaction();
        try {
            $deliveryOrder->load('details.barang');

            // Validasi kecukupan stok gudang sebelum di-approve
            foreach ($deliveryOrder->details as $d) {
                if ($d->barang_id) {
                    $stokGudang = StokGudang::where('barang_id', $d->barang_id)
                        ->where('gudang_id', $deliveryOrder->gudang_id)
                        ->first();
                    $stokTersedia = $stokGudang ? (float) $stokGudang->stok_aktual : 0;
                    if ((float) $d->qty_kirim > $stokTersedia) {
                        $brgNama = $d->nama_barang_custom ?: ($d->barang ? $d->barang->nama_barang : 'Produk');
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Gagal ACC: Stok untuk \"{$brgNama}\" di gudang tidak mencukupi! Tersedia: {$stokTersedia} {$d->nama_satuan}, dibutuhkan: {$d->qty_kirim} {$d->nama_satuan}.",
                        ], 422);
                    }
                }
            }

            // Potong stok gudang saat di-ACC (barang siap dikirim)
            foreach ($deliveryOrder->details as $d) {
                if ($d->barang_id) {
                    $this->potongStokGudang($deliveryOrder, $d->barang_id, (float) $d->qty_kirim, $deliveryOrder->gudang_id);

                    if (!empty($d->serial_numbers) && is_array($d->serial_numbers)) {
                        $custName = $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan');
                        SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $d->barang_id)
                            ->whereIn('serial_number', $d->serial_numbers)
                            ->where('status', 'Tersedia')
                            ->update([
                                'status'  => 'Terjual',
                                'catatan' => "Terkirim via Surat Jalan {$deliveryOrder->nomor_do} ke {$custName}",
                            ]);
                    }
                }
            }

            $deliveryOrder->update([
                'status'           => 'Disetujui',
                'approved_by'      => $user->id,
                'approved_at'      => now(),
                'catatan_approval' => $request->catatan_approval ?: 'Disetujui oleh atasan',
            ]);

            if ($deliveryOrder->sales_order_id && $deliveryOrder->salesOrder) {
                $this->syncSalesOrderStatus($deliveryOrder->salesOrder);
            }

            // Otomatis Bukukan Jurnal Akuntansi Pengiriman Barang (HPP & Persediaan)
            $this->postJournalForDeliveryOrder($deliveryOrder);

            DB::commit();

            // Pemicu Notifikasi WhatsApp ke Pembuat (Creator) bahwa sudah di-ACC
            WhatsAppGatewayController::sendDeliveryOrderApprovedNotification($deliveryOrder, $user);

            return response()->json([
                'success' => true,
                'message' => "Surat Jalan {$deliveryOrder->nomor_do} berhasil di-ACC (Disetujui)! Jurnal akuntansi telah dibukukan dan notifikasi WhatsApp telah otomatis dikirimkan ke pembuat pengiriman.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approve DO: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyetujui pengiriman: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Tolak Pengajuan Pengiriman
     */
    public function reject(DeliveryOrder $deliveryOrder, Request $request)
    {
        $user = auth()->user();
        $isApprover = $user && $user->can('approve-delivery-order');
        if (!$isApprover) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk menolak pengajuan ini.'], 403);
        }

        // ── SECURITY: Anti-IDOR — pastikan DO milik entitas aktif ──────────
        if (!$this->checkEntityAccess($deliveryOrder, $user)) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data pengiriman bukan milik entitas/cabang Anda yang sedang aktif.'], 403);
        }

        // ── SECURITY: State guard — hanya bisa menolak jika status masih pending/draft ──
        if (!in_array($deliveryOrder->status, ['Draft', 'Menunggu Persetujuan'])) {
            return response()->json([
                'success' => false,
                'message' => "Pengiriman tidak dapat ditolak karena sudah dalam status \"{$deliveryOrder->status}\"."
            ], 422);
        }

        // ── SECURITY: Sanitasi alasan (max 500 karakter) ───────────────────
        $alasan = mb_substr(strip_tags($request->alasan ?? ''), 0, 500) ?: 'Pengajuan pengiriman ditolak oleh atasan.';

        DB::beginTransaction();
        try {
            // Hapus jurnal akuntansi jika sempat terbentuk
            $this->deleteJournalForDeliveryOrder($deliveryOrder);

            $deliveryOrder->update([
                'status'           => 'Dibatalkan',
                'catatan_approval' => "Ditolak oleh {$user->nama_lengkap}: {$alasan}",
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Pengajuan Surat Jalan {$deliveryOrder->nomor_do} telah ditolak.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menolak pengajuan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Konfirmasi Barang Telah Sampai (Penyelesaian Pengiriman)
     */
    public function confirmArrival(DeliveryOrder $deliveryOrder, Request $request)
    {
        $user = auth()->user();
        $canComplete = $user && ($user->can('selesai-delivery-order') || $user->can('approve-delivery-order'));
        if (!$canComplete) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk mengonfirmasi penerimaan / menyelesaikan pengiriman ini.'], 403);
        }

        // ── SECURITY: Anti-IDOR — pastikan DO milik entitas aktif ──────────
        if (!$this->checkEntityAccess($deliveryOrder, $user)) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data pengiriman bukan milik entitas/cabang Anda yang sedang aktif.'], 403);
        }

        if (in_array($deliveryOrder->status, ['Terkirim', 'Dibatalkan'])) {
            return response()->json(['success' => false, 'message' => "Pengiriman ini sudah dalam status {$deliveryOrder->status}."], 422);
        }

        DB::beginTransaction();
        try {

            // Jika status sebelumnya belum memotong stok (masih Menunggu Persetujuan atau Draft)
            if (in_array($deliveryOrder->status, ['Draft', 'Menunggu Persetujuan'])) {
                $deliveryOrder->load('details');
                foreach ($deliveryOrder->details as $d) {
                    if ($d->barang_id) {
                        $this->potongStokGudang($deliveryOrder, $d->barang_id, (float) $d->qty_kirim, $deliveryOrder->gudang_id);
                        if (!empty($d->serial_numbers) && is_array($d->serial_numbers)) {
                            $custName = $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan');
                            SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $d->barang_id)
                                ->whereIn('serial_number', $d->serial_numbers)
                                ->where('status', 'Tersedia')
                                ->update([
                                    'status'  => 'Terjual',
                                    'catatan' => "Terkirim via Surat Jalan {$deliveryOrder->nomor_do} ke {$custName}",
                                ]);
                        }
                    }
                }
            }

            $catatanTerima = $request->catatan_penerimaan ?: ('Barang telah diterima di lokasi pelanggan pada ' . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i'));

            $deliveryOrder->update([
                'status'             => 'Terkirim',
                'received_by'        => $user?->id,
                'received_at'        => now(),
                'catatan_penerimaan' => $catatanTerima,
            ]);

            if ($deliveryOrder->sales_order_id && $deliveryOrder->salesOrder) {
                $this->syncSalesOrderStatus($deliveryOrder->salesOrder);
            }

            // Pastikan Jurnal Akuntansi Pengiriman sudah terbentuk
            $existingJurnal = Jurnal::where('referensi_tipe', 'Delivery Order')
                ->where('referensi_id', $deliveryOrder->id)
                ->first();
            if (!$existingJurnal) {
                $this->postJournalForDeliveryOrder($deliveryOrder);
            }

            DB::commit();

            // Pemicu Notifikasi WhatsApp ke Pembuat (Creator) DAN Atasan (Approver)
            WhatsAppGatewayController::sendDeliveryOrderCompletedNotification($deliveryOrder, $user);

            return response()->json([
                'success' => true,
                'message' => "Konfirmasi barang sampai berhasil disimpan! Status pengiriman selesai dan notifikasi WhatsApp telah dikirimkan ke pembuat & atasan.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirm arrival DO: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengonfirmasi barang sampai: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Status Pengiriman (Siap Dikirim, Dalam Perjalanan, Terkirim, Dibatalkan)
     */
    public function updateStatus(DeliveryOrder $deliveryOrder, Request $request)
    {
        // ── SECURITY: Role-based auth check ────────────────────────────────
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid, silakan login kembali.'], 401);
        }

        // Hanya approver atau pembuat sendiri yang boleh update status
        $isApprover = $user->can('approve-delivery-order');
        $isCreator  = $deliveryOrder->created_by == $user->id;
        if (!$isApprover && !$isCreator) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk mengubah status pengiriman ini.'], 403);
        }

        // ── SECURITY: Anti-IDOR — tenancy check ────────────────────────────
        if (!$this->checkEntityAccess($deliveryOrder, $user)) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data pengiriman bukan milik entitas/cabang Anda yang sedang aktif.'], 403);
        }

        $request->validate([
            'status' => 'required|in:Draft,Menunggu Persetujuan,Disetujui,Siap Dikirim,Dalam Perjalanan,Terkirim,Dibatalkan',
        ]);

        $oldStatus = $deliveryOrder->status;
        $newStatus = $request->status;

        if ($oldStatus === $newStatus) {
            return response()->json(['success' => true, 'message' => 'Status tidak berubah.']);
        }

        // Cegah penurunan status yang tidak aman (mencegah pemotongan stok ganda)
        if (in_array($oldStatus, ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim']) && in_array($newStatus, ['Draft', 'Menunggu Persetujuan'])) {
            return response()->json([
                'success' => false,
                'message' => "Surat Jalan yang telah diproses / disetujui tidak dapat dikembalikan ke status \"{$newStatus}\". Jika ingin merevisi atau membatalkan, silakan gunakan alur pembatalan/revisi resmi.",
            ], 422);
        }

        // Keamanan: Hanya approver yang boleh memajukan status dari pending ke Disetujui / Siap Dikirim / Terkirim
        if (in_array($oldStatus, ['Draft', 'Menunggu Persetujuan']) && in_array($newStatus, ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim']) && !$isApprover) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya atasan / approver yang berhak menyetujui pengiriman ini.',
            ], 403);
        }

        // Integritas Data: DO yang sudah terikat Faktur Penjualan aktif tidak boleh dibatalkan
        if ($newStatus === 'Dibatalkan') {
            $activeInvoice = SalesInvoice::where('delivery_order_id', $deliveryOrder->id)
                ->where('status', '!=', 'Cancelled')
                ->first();
            if ($activeInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => "Pengiriman tidak dapat dibatalkan karena sudah diterbitkan Faktur Penjualan aktif ({$activeInvoice->nomor_invoice}). Silakan batalkan Faktur Penjualan terlebih dahulu.",
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Jika status berubah dari Draft/Menunggu Persetujuan ke Siap Dikirim/Dalam Perjalanan/Terkirim -> validasi stok & kurangi stok
            if (in_array($oldStatus, ['Draft', 'Menunggu Persetujuan']) && in_array($newStatus, ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim'])) {
                // Validasi stok sebelum dipotong
                foreach ($deliveryOrder->details as $d) {
                    if ($d->barang_id) {
                        $stokGudang = StokGudang::where('barang_id', $d->barang_id)
                            ->where('gudang_id', $deliveryOrder->gudang_id)
                            ->first();

                        $stokTersedia = $stokGudang ? (float) $stokGudang->stok_aktual : 0;
                        $qtyKirim = (float) $d->qty_kirim;

                        if ($qtyKirim > $stokTersedia) {
                            $brgNama = $d->nama_barang_custom ?: ($d->barang ? $d->barang->nama_barang : 'Produk');
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Gagal mengubah status: Stok untuk \"{$brgNama}\" tidak mencukupi di gudang! Tersedia: {$stokTersedia} {$d->nama_satuan}, dibutuhkan: {$qtyKirim} {$d->nama_satuan}.",
                            ], 422);
                        }
                    }
                }

                foreach ($deliveryOrder->details as $d) {
                    if ($d->barang_id) {
                        $this->potongStokGudang($deliveryOrder, $d->barang_id, (float) $d->qty_kirim, $deliveryOrder->gudang_id);

                        // Tandai nomor seri sebagai Terjual di database pelacakan serial number
                        if (!empty($d->serial_numbers) && is_array($d->serial_numbers)) {
                            $custName = $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan');
                            SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $d->barang_id)
                                ->whereIn('serial_number', $d->serial_numbers)
                                ->where('status', 'Tersedia')
                                ->update([
                                    'status'  => 'Terjual',
                                    'catatan' => "Terkirim via Surat Jalan {$deliveryOrder->nomor_do} ke {$custName}",
                                ]);
                        }
                    }
                }
            }

            // Jika status dibatalkan dan sebelumnya sudah potong stok -> kembalikan stok & revert serial numbers
            if ($newStatus === 'Dibatalkan' && in_array($oldStatus, ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim'])) {
                foreach ($deliveryOrder->details as $d) {
                    if ($d->barang_id) {
                        $this->kembalikanStokGudang($deliveryOrder, $d->barang_id, (float) $d->qty_kirim, $deliveryOrder->gudang_id);

                        // Kembalikan status nomor seri ke Tersedia
                        if (!empty($d->serial_numbers) && is_array($d->serial_numbers)) {
                            SpkPenerimaanBarangJadiSerial::where('barang_jadi_id', $d->barang_id)
                                ->whereIn('serial_number', $d->serial_numbers)
                                ->where('status', 'Terjual')
                                ->update([
                                    'status'  => 'Tersedia',
                                    'catatan' => "Batal kirim Surat Jalan {$deliveryOrder->nomor_do}",
                                ]);
                        }
                    }
                }
            }

            // Jika dibatalkan, hapus jurnal
            if ($newStatus === 'Dibatalkan') {
                $this->deleteJournalForDeliveryOrder($deliveryOrder);
            } elseif (in_array($oldStatus, ['Draft', 'Menunggu Persetujuan']) && in_array($newStatus, ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim'])) {
                $this->postJournalForDeliveryOrder($deliveryOrder);
            }

            $deliveryOrder->update(['status' => $newStatus]);

            // Sinkronisasi status SO
            if ($deliveryOrder->sales_order_id && $deliveryOrder->salesOrder) {
                $this->syncSalesOrderStatus($deliveryOrder->salesOrder);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Status Surat Jalan {$deliveryOrder->nomor_do} berhasil diperbarui menjadi {$newStatus}.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update status DO: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cetak Surat Jalan A4 Resmi (Print View)
     */
    public function printSuratJalan(DeliveryOrder $deliveryOrder, Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->can('cetak-delivery-order')) {
            abort(403, 'Anda tidak memiliki izin untuk mencetak Surat Jalan.');
        }

        // ── SECURITY: Anti-IDOR — pastikan DO milik entitas aktif ──────────
        if (!$this->checkEntityAccess($deliveryOrder, $user)) {
            abort(403, 'Akses Ditolak: Surat Jalan bukan milik entitas/cabang Anda yang sedang aktif.');
        }

        $deliveryOrder->load([
            'salesOrder:id,nomor_so,tanggal_so,catatan,tender_id,sales_quotation_id',
            'salesOrder.tender:id,nomor_kontrak,nama_tender',
            'salesOrder.quotation.tender:id,nomor_kontrak,nama_tender',
            'customer:id,nama_customer,nama_perusahaan,telepon,hp',
            'customer.defaultAddress',
            'company:id,nama_perusahaan,logo,address,city,province,postal_code,phone,email,npwp',
            'branch:id,company_id,nama_cabang,nama_perusahaan,logo,address,city,province,postal_code,phone,email',
            'gudang:id,nama_gudang,kode_gudang,alamat',
            'creator:id,nama_lengkap,username',
            'approver:id,nama_lengkap,username,jabatan_id',
            'approver.position:id,position_name',
            'details.barang:id,kode_barang,nama_barang,satuan_id,tipe_pelacakan',
            'details.barang.satuan:id,nama_satuan',
        ]);

        // Resolusi cadangan No. Tender pada printout Surat Jalan jika relasi langsung kosong
        if ($deliveryOrder->salesOrder && !$deliveryOrder->salesOrder->tender) {
            if ($deliveryOrder->salesOrder->quotation && $deliveryOrder->salesOrder->quotation->tender) {
                $deliveryOrder->salesOrder->setRelation('tender', $deliveryOrder->salesOrder->quotation->tender);
            } else {
                $tenderBySo = \App\Models\Tender::where('sales_order_id', $deliveryOrder->salesOrder->id)->first(['id', 'nomor_kontrak', 'nama_tender']);
                if ($tenderBySo) {
                    $deliveryOrder->salesOrder->setRelation('tender', $tenderBySo);
                }
            }
        }

        // Cek konteks entitas: Prioritaskan Cabang jika sesi aktif Cabang atau DO milik Cabang
        $activeBranchId  = session('active_branch_id') ?? auth()->user()?->branch_id ?? $deliveryOrder->branch_id;
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id ?? $deliveryOrder->company_id;

        $branch = null;
        if ($activeBranchId) {
            $branch = \App\Models\Branch::find($activeBranchId);
        } elseif ($deliveryOrder->branch_id) {
            $branch = $deliveryOrder->branch;
        }

        $company = null;
        if ($branch && $branch->company_id) {
            $company = \App\Models\Company::find($branch->company_id);
        } elseif ($activeCompanyId) {
            $company = \App\Models\Company::find($activeCompanyId);
        } else {
            $company = $deliveryOrder->company ?? \App\Models\Company::first();
        }

        $isCabang = !empty($branch);

        $appLogo = asset('gambar/logo.png');
        if (\Illuminate\Support\Facades\Schema::hasTable('app_settings') || \Illuminate\Support\Facades\Schema::hasTable('app_settings_tb')) {
            $settingLogo = \Illuminate\Support\Facades\DB::table('app_settings')->where('key', 'app_logo')->value('value');
            if ($settingLogo) {
                $appLogo = file_exists(public_path('storage/' . $settingLogo)) 
                    ? asset('storage/' . $settingLogo) 
                    : (file_exists(public_path($settingLogo)) ? asset($settingLogo) : $appLogo);
            }
        }

        $companyLogo = null;

        // JIKA AKTIF DI CABANG / DO CABANG -> UTAMAKAN LOGO CABANG
        if ($isCabang && $branch && $branch->logo) {
            if (file_exists(public_path('storage/' . $branch->logo))) {
                $companyLogo = asset('storage/' . $branch->logo);
            } elseif (file_exists(public_path($branch->logo))) {
                $companyLogo = asset($branch->logo);
            }
        }

        // JIKA LOGO CABANG BELUM DIDAPAT, GUNAKAN LOGO PERUSAHAAN (PT)
        if (!$companyLogo && $company && $company->logo) {
            if (file_exists(public_path('storage/' . $company->logo))) {
                $companyLogo = asset('storage/' . $company->logo);
            } elseif (file_exists(public_path($company->logo))) {
                $companyLogo = asset($company->logo);
            }
        }

        // FALLBACK TERAKHIR: LOGO DEFAULT APLIKASI
        if (!$companyLogo) {
            $companyLogo = $appLogo;
        }

        return view('modul_marketing.pengiriman.print_surat_jalan', compact('deliveryOrder', 'company', 'branch', 'isCabang', 'appLogo', 'companyLogo'));
    }

    /**
     * Helper: Potong Stok Aktual & Buat Mutasi Kartu Stok (Concurrency Safe with Pessimistic Locking)
     */
    private function potongStokGudang(DeliveryOrder $do, int $barangId, float $qty, int $gudangId)
    {
        $stokGudang = StokGudang::where('barang_id', $barangId)
            ->where('gudang_id', $gudangId)
            ->lockForUpdate()
            ->first();

        if (!$stokGudang) {
            $stokGudang = StokGudang::firstOrCreate(
                ['barang_id' => $barangId, 'gudang_id' => $gudangId],
                ['stok_aktual' => 0]
            );
            $stokGudang = StokGudang::where('id', $stokGudang->id)->lockForUpdate()->first();
        }

        $stokSebelum = (float) $stokGudang->stok_aktual;
        $stokSesudah = $stokSebelum - $qty;

        $hpp = (float) ($stokGudang->harga_pokok_rata_rata ?? 0);
        if ($hpp <= 0) {
            $barang = MasterBarang::find($barangId);
            if ($barang) {
                $hpp = (float) ($barang->harga_pokok_rata_rata ?? $barang->harga_pokok ?? $barang->harga_beli ?? 0);
            }
        }
        $nilaiKeluar = round($qty * $hpp, 2);

        $stokGudang->stok_aktual = $stokSesudah;
        if (isset($stokGudang->nilai_persediaan)) {
            $stokGudang->nilai_persediaan = max(0, (float)$stokGudang->nilai_persediaan - $nilaiKeluar);
        }
        $stokGudang->save();

        if (method_exists(StokGudang::class, 'syncMasterHpp')) {
            StokGudang::syncMasterHpp($barangId);
        }

        if (class_exists(KartuStok::class)) {
            KartuStok::create([
                'barang_id'       => $barangId,
                'gudang_id'       => $gudangId,
                'tipe'            => 'Keluar',
                'jumlah'          => $qty,
                'stok_awal'       => $stokSebelum,
                'stok_akhir'      => $stokSesudah,
                'harga_satuan'    => $hpp,
                'nilai_total'     => $nilaiKeluar,
                'referensi_jenis' => 'Delivery Order',
                'referensi_id'    => $do->id,
                'referensi_nomor' => $do->nomor_do,
                'keterangan'      => "Pengiriman ke " . ($do->customer?->nama_perusahaan ?: ($do->nama_penerima ?: 'Pelanggan')),
            ]);
        }
    }

    /**
     * Helper: Kembalikan Stok Gudang jika DO dibatalkan (Concurrency Safe with Pessimistic Locking)
     */
    private function kembalikanStokGudang(DeliveryOrder $do, int $barangId, float $qty, int $gudangId)
    {
        $stokGudang = StokGudang::where('barang_id', $barangId)
            ->where('gudang_id', $gudangId)
            ->lockForUpdate()
            ->first();

        if ($stokGudang) {
            $stokSebelum = (float) $stokGudang->stok_aktual;
            $stokSesudah = $stokSebelum + $qty;

            $hpp = (float) ($stokGudang->harga_pokok_rata_rata ?? 0);
            if ($hpp <= 0) {
                $barang = MasterBarang::find($barangId);
                if ($barang) {
                    $hpp = (float) ($barang->harga_pokok_rata_rata ?? $barang->harga_pokok ?? $barang->harga_beli ?? 0);
                }
            }
            $nilaiMasuk = round($qty * $hpp, 2);

            $stokGudang->stok_aktual = $stokSesudah;
            if (isset($stokGudang->nilai_persediaan)) {
                $stokGudang->nilai_persediaan = (float)$stokGudang->nilai_persediaan + $nilaiMasuk;
            }
            $stokGudang->save();

            if (method_exists(StokGudang::class, 'syncMasterHpp')) {
                StokGudang::syncMasterHpp($barangId);
            }

            if (class_exists(KartuStok::class)) {
                KartuStok::create([
                    'barang_id'       => $barangId,
                    'gudang_id'       => $gudangId,
                    'tipe'            => 'Masuk',
                    'jumlah'          => $qty,
                    'stok_awal'       => $stokSebelum,
                    'stok_akhir'      => $stokSesudah,
                    'harga_satuan'    => $hpp,
                    'nilai_total'     => $nilaiMasuk,
                    'referensi_jenis' => 'Pembatalan Delivery Order',
                    'referensi_id'    => $do->id,
                    'referensi_nomor' => $do->nomor_do,
                    'keterangan'      => "Pembatalan Surat Jalan {$do->nomor_do}",
                ]);
            }
        }
    }

    /**
     * Helper: Sinkronisasi Status Sales Order berdasarkan Akumulasi Pengiriman (Parsial / Penuh)
     */
    private function syncSalesOrderStatus(SalesOrder $salesOrder)
    {
        $salesOrder->load('details');
        $details = $salesOrder->details;
        if ($details->isEmpty()) return;

        $soDetailIds = $details->pluck('id')->all();

        // Pre-aggregate queries: 2 queries instead of 2N queries
        $deliveredMap = DeliveryOrderDetail::whereIn('sales_order_detail_id', $soDetailIds)
            ->whereHas('deliveryOrder', fn($q) => $q->where('status', 'Terkirim'))
            ->groupBy('sales_order_detail_id')
            ->selectRaw('sales_order_detail_id, SUM(qty_kirim) as total')
            ->pluck('total', 'sales_order_detail_id');

        $inShippingMap = DeliveryOrderDetail::whereIn('sales_order_detail_id', $soDetailIds)
            ->whereHas('deliveryOrder', fn($q) => $q->whereIn('status', ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim']))
            ->groupBy('sales_order_detail_id')
            ->selectRaw('sales_order_detail_id, SUM(qty_kirim) as total')
            ->pluck('total', 'sales_order_detail_id');

        $allDelivered = true;
        $hasAnyShippingOrDelivered = false;

        foreach ($details as $d) {
            $deliveredQty  = (float) ($deliveredMap->get($d->id, 0));
            $inShippingQty = (float) ($inShippingMap->get($d->id, 0));

            if ($inShippingQty > 0) {
                $hasAnyShippingOrDelivered = true;
            }

            if ($deliveredQty < (float) $d->qty) {
                $allDelivered = false;
            }
        }

        if ($allDelivered && count($details) > 0) {
            $salesOrder->update(['status' => 'Delivered']);
        } elseif ($hasAnyShippingOrDelivered) {
            $salesOrder->update(['status' => 'Processing']);
        } else {
            // Jika belum ada pengiriman yang disetujui / jalan (semua batal atau masih draft)
            if (!in_array($salesOrder->status, ['Cancelled', 'Completed'])) {
                $salesOrder->update(['status' => 'Confirmed']);
            }
        }
    }

    /**
     * Dapatkan data Jurnal Akuntansi untuk Surat Jalan / Delivery Order (JSON)
     */
    public function getJurnal(DeliveryOrder $deliveryOrder)
    {
        $user = auth()->user();
        if (!$user || !$user->can('lihat-jurnal-delivery-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk melihat jurnal pengiriman.'], 403);
        }

        // ── SECURITY: Anti-IDOR — pastikan DO milik entitas aktif ──────────
        if (!$this->checkEntityAccess($deliveryOrder, $user)) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak: Data pengiriman bukan milik entitas/cabang Anda yang sedang aktif.'], 403);
        }

        $jurnal = Jurnal::with(['details.account', 'creator', 'branch', 'company'])
            ->where('referensi_tipe', 'Delivery Order')
            ->where('referensi_id', $deliveryOrder->id)
            ->first();

        if (!$jurnal) {
            return response()->json([
                'success' => false,
                'message' => "Belum ada Jurnal Akuntansi yang dibukukan untuk Surat Jalan {$deliveryOrder->nomor_do}."
            ], 404);
        }

        // Auto-sync jika jurnal belum memiliki no_tender padahal DO terkait tender
        if (empty($jurnal->no_tender)) {
            $deliveryOrder->loadMissing([
                'salesOrder.tender:id,nomor_kontrak,nama_tender',
                'salesOrder.quotation.tender:id,nomor_kontrak,nama_tender',
            ]);
            $tenderObj = $deliveryOrder->salesOrder?->tender 
                ?? $deliveryOrder->salesOrder?->quotation?->tender;
            if (!$tenderObj && $deliveryOrder->sales_order_id) {
                $tenderObj = \App\Models\Tender::where('sales_order_id', $deliveryOrder->sales_order_id)->first(['id', 'nomor_kontrak', 'nama_tender']);
            }
            if ($tenderObj) {
                $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                    ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                    : ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id));

                $jurnal->update(['no_tender' => $nomorTender]);
                $jurnal->no_tender = $nomorTender;
            }
        }

        return response()->json([
            'success' => true,
            'jurnal'  => $jurnal
        ]);
    }

    /**
     * Posting Jurnal Otomatis untuk Pengiriman (Delivery Order)
     * - Memisahkan PT dan Cabang secara ketat:
     *   Jika PT -> company_id terisi, branch_id = null
     *   Jika Cabang -> branch_id terisi, company_id = null
     * - Menjurnal HPP (Beban Pokok Penjualan) di DEBIT dan Persediaan di KREDIT
     */
    private function postJournalForDeliveryOrder(DeliveryOrder $deliveryOrder)
    {
        // Hapus jurnal lama jika sudah ada (mencegah duplikasi data saat re-approval)
        $existingJurnals = Jurnal::withTrashed()
            ->where('referensi_tipe', 'Delivery Order')
            ->where('referensi_id', $deliveryOrder->id)
            ->get();

        foreach ($existingJurnals as $ej) {
            $ej->details()->delete();
            $ej->forceDelete();
        }

        $deliveryOrder->loadMissing([
            'details.barang.kategori',
            'details.salesOrderDetail',
            'salesOrder',
            'customer',
            'gudang'
        ]);

        // Tenancy Isolation:
        // "bedakan antara pt dan cabang jika pt maka cabang null jika cabang maka pt null"
        $rawBranchId = $deliveryOrder->branch_id !== null ? $deliveryOrder->branch_id : ($deliveryOrder->gudang ? $deliveryOrder->gudang->cabang_id : session('active_branch_id'));
        $isBranch    = !empty($rawBranchId);

        $jurnalCompanyId = $isBranch ? null : ($deliveryOrder->company_id ?: (session('active_company_id') ?: (auth()->user()?->company_id ?? 1)));
        $jurnalBranchId  = $isBranch ? $rawBranchId : null;

        // ID PT untuk resolusi akun COA & Mapping (mengacu ke PT induk jika cabang)
        $coaCompanyId = $deliveryOrder->company_id 
            ?: (session('active_company_id') ?? auth()->user()?->company_id ?? ($isBranch ? Branch::where('id', $jurnalBranchId)->value('company_id') : 1));

        // Hitung total nilai HPP dan kumpulkan item per COA (HPP Debit & Persediaan Kredit)
        $totalDebit = 0.0;
        $groupedDebitEntries = [];
        $groupedCreditEntries = [];

        // Fallback default COA Persediaan (Credit)
        $defaultCreditCoaId = AccountMappingHelper::getCoaId('default', 'default_inventory', null, $coaCompanyId);
        if (!$defaultCreditCoaId) {
            $defaultCreditCoaId = ChartOfAccount::where('company_id', $coaCompanyId)
                ->where('code', '1105')
                ->where('is_header', false)
                ->value('id');
        }
        if (!$defaultCreditCoaId) {
            $defaultCreditCoaId = ChartOfAccount::where('company_id', $coaCompanyId)
                ->whereIn('type', ['ast', 'Aset', 'Asset', 'Persediaan', 'Inventory'])
                ->where('name', 'like', '%Persediaan%')
                ->where('is_header', false)
                ->value('id');
        }

        // Fallback default COA HPP (Debit)
        $defaultDebitCoaId = AccountMappingHelper::getCoaId('default', 'default_cogs', null, $coaCompanyId);
        if (!$defaultDebitCoaId) {
            $defaultDebitCoaId = ChartOfAccount::where('company_id', $coaCompanyId)
                ->where('code', '5101')
                ->where('is_header', false)
                ->value('id');
        }
        if (!$defaultDebitCoaId) {
            $defaultDebitCoaId = ChartOfAccount::where('company_id', $coaCompanyId)
                ->whereIn('type', ['cogs', 'HPP', 'Beban', 'Expense', 'Cost of Goods Sold'])
                ->where('name', 'like', '%HPP%')
                ->where('is_header', false)
                ->value('id');
        }

        foreach ($deliveryOrder->details as $d) {
            $qty = (float) $d->qty_kirim;
            if ($qty <= 0) continue;

            // Prioritas pencarian HPP satuan:
            // 1. Dari salesOrderDetail (hpp_satuan)
            // 2. Dari stok gudang (harga_pokok_rata_rata)
            // 3. Dari master barang (harga_pokok_rata_rata / harga_pokok / harga_beli)
            // 4. Dari harga satuan jual
            $hppSatuan = 0;
            if ($d->salesOrderDetail && (float)$d->salesOrderDetail->hpp_satuan > 0) {
                $hppSatuan = (float) $d->salesOrderDetail->hpp_satuan;
            } elseif ($d->barang_id && $deliveryOrder->gudang_id) {
                $stokGudang = StokGudang::where('barang_id', $d->barang_id)
                    ->where('gudang_id', $deliveryOrder->gudang_id)
                    ->first();
                if ($stokGudang && (float)$stokGudang->harga_pokok_rata_rata > 0) {
                    $hppSatuan = (float) $stokGudang->harga_pokok_rata_rata;
                }
            }

            if ($hppSatuan <= 0 && $d->barang) {
                $hppSatuan = (float) ($d->barang->harga_pokok_rata_rata ?? $d->barang->harga_pokok ?? $d->barang->harga_beli ?? 0);
            }

            if ($hppSatuan <= 0 && $d->salesOrderDetail && (float)$d->salesOrderDetail->harga_satuan > 0) {
                // Fallback estimasi jika data HPP belum terisi sama sekali
                $hppSatuan = (float) $d->salesOrderDetail->harga_satuan;
            }

            $nilaiHppItem = round($qty * $hppSatuan, 2);
            if ($nilaiHppItem <= 0) continue;

            $totalDebit += $nilaiHppItem;

            $catId = $d->barang ? $d->barang->kategori_barang_id : null;
            $namaBrg = $d->nama_barang_custom ?: ($d->barang ? $d->barang->nama_barang : 'Barang');
            $satuan  = $d->nama_satuan ?: ($d->barang?->satuan?->nama_satuan ?? 'pcs');
            $itemDesc = "{$namaBrg} ({$qty} {$satuan})";

            // 1. Cari COA HPP (Debit) sesuai Mapping Kategori Barang (cat_{id}_cogs), fallback ke default
            $itemDebitCoaId = $catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_cogs", null, $coaCompanyId) : null;
            if (!$itemDebitCoaId) {
                $itemDebitCoaId = $defaultDebitCoaId;
            }

            if (!isset($groupedDebitEntries[$itemDebitCoaId])) {
                $groupedDebitEntries[$itemDebitCoaId] = [
                    'debit' => 0.0,
                    'items' => []
                ];
            }
            $groupedDebitEntries[$itemDebitCoaId]['debit'] += $nilaiHppItem;
            $groupedDebitEntries[$itemDebitCoaId]['items'][] = $itemDesc;

            // 2. Cari COA Persediaan (Kredit) sesuai Mapping Kategori Barang (cat_{id}_inventory), fallback ke default
            $itemCreditCoaId = $catId ? AccountMappingHelper::getCoaId('inventory_category', "cat_{$catId}_inventory", null, $coaCompanyId) : null;
            if (!$itemCreditCoaId) {
                $itemCreditCoaId = $defaultCreditCoaId;
            }

            if (!isset($groupedCreditEntries[$itemCreditCoaId])) {
                $groupedCreditEntries[$itemCreditCoaId] = [
                    'kredit' => 0.0,
                    'items'  => []
                ];
            }
            $groupedCreditEntries[$itemCreditCoaId]['kredit'] += $nilaiHppItem;
            $groupedCreditEntries[$itemCreditCoaId]['items'][] = $itemDesc;
        }

        // Jika tidak ada nilai yang dijurnal, return
        if ($totalDebit <= 0 || empty($groupedDebitEntries) || empty($groupedCreditEntries)) {
            Log::info("Jurnal Delivery Order {$deliveryOrder->nomor_do} dilewati: total nilai HPP Rp 0 atau akun COA tidak ditemukan.");
            return null;
        }

        // Auto-generate Jurnal Number JNL/YYYY/MM/XXXX
        $tglJurnal = $deliveryOrder->tanggal_kirim ? $deliveryOrder->tanggal_kirim->format('Y-m-d') : now()->toDateString();
        $year   = date('Y', strtotime($tglJurnal));
        $month  = date('m', strtotime($tglJurnal));
        $prefix = "JNL/{$year}/{$month}/";

        $latestJnlQuery = Jurnal::withTrashed()
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
        while (Jurnal::withTrashed()->where('nomor_jurnal', $nomorJurnal)->exists()) {
            $nextNum++;
            $nomorJurnal = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        $deliveryOrder->loadMissing([
            'salesOrder.tender:id,nomor_kontrak,nama_tender',
            'salesOrder.quotation.tender:id,nomor_kontrak,nama_tender',
            'customer:id,nama_perusahaan,nama_customer'
        ]);

        $tenderObj = $deliveryOrder->salesOrder?->tender 
            ?? $deliveryOrder->salesOrder?->quotation?->tender;

        if (!$tenderObj && $deliveryOrder->sales_order_id) {
            $tenderObj = \App\Models\Tender::where('sales_order_id', $deliveryOrder->sales_order_id)->first(['id', 'nomor_kontrak', 'nama_tender']);
        }

        $nomorTender = null;
        if ($tenderObj) {
            $nomorTender = !empty($tenderObj->nomor_kontrak) && $tenderObj->nomor_kontrak !== '-'
                ? ($tenderObj->nomor_kontrak . (!empty($tenderObj->nama_tender) ? ' - ' . $tenderObj->nama_tender : ''))
                : ($tenderObj->nama_tender ?: ('Tender #' . $tenderObj->id));
        }

        $tenderTag  = $nomorTender ? " [Tender: {$nomorTender}]" : "";
        $soTag      = $deliveryOrder->salesOrder ? " (SO: {$deliveryOrder->salesOrder->nomor_so})" : "";
        $custName   = $deliveryOrder->customer?->nama_perusahaan ?: ($deliveryOrder->nama_penerima ?: 'Pelanggan');
        $keterangan = "Pengiriman Barang {$deliveryOrder->nomor_do}{$soTag} ke {$custName}{$tenderTag}";

        // Simpan Header Jurnal
        $jurnal = Jurnal::create([
            'nomor_jurnal'   => $nomorJurnal,
            'tanggal_jurnal' => $tglJurnal,
            'referensi_tipe' => 'Delivery Order',
            'referensi_id'   => $deliveryOrder->id,
            'no_tender'      => $nomorTender,
            'company_id'     => $jurnalCompanyId,
            'branch_id'      => $jurnalBranchId,
            'total_debit'    => $totalDebit,
            'total_kredit'   => $totalDebit,
            'keterangan'     => $keterangan,
            'created_by'     => auth()->id() ?? $deliveryOrder->created_by,
            'updated_by'     => auth()->id(),
        ]);

        // Simpan Detail DEBIT (HPP sesuai mapping kategori masing-masing)
        foreach ($groupedDebitEntries as $coaId => $entry) {
            $itemDesc = implode(', ', array_slice($entry['items'], 0, 3));
            if (count($entry['items']) > 3) {
                $itemDesc .= ' dkk.';
            }
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $coaId,
                'debit'      => $entry['debit'],
                'kredit'     => 0,
                'keterangan' => "HPP Pengiriman: {$itemDesc}",
            ]);
        }

        // Simpan Detail KREDIT (Persediaan sesuai mapping kategori masing-masing)
        foreach ($groupedCreditEntries as $coaId => $entry) {
            $itemDesc = implode(', ', array_slice($entry['items'], 0, 3));
            if (count($entry['items']) > 3) {
                $itemDesc .= ' dkk.';
            }
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'account_id' => $coaId,
                'debit'      => 0,
                'kredit'     => $entry['kredit'],
                'keterangan' => "Persediaan Keluar: {$itemDesc}",
            ]);
        }

        return $jurnal;
    }

    /**
     * Hapus Jurnal Akuntansi Pengiriman Barang jika status dibatalkan / ditolak
     */
    private function deleteJournalForDeliveryOrder(DeliveryOrder $deliveryOrder)
    {
        $jurnals = Jurnal::where('referensi_tipe', 'Delivery Order')
            ->where('referensi_id', $deliveryOrder->id)
            ->get();

        foreach ($jurnals as $j) {
            $j->details()->delete();
            $j->forceDelete();
        }
    }

    /**
     * Helper: Validasi Akses Tenancy (Anti-IDOR) untuk entitas aktif (PT Pusat vs Cabang)
     */
    private function checkEntityAccess(DeliveryOrder $deliveryOrder, ?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        $activeCompanyId = session('active_company_id') ?? $user->company_id;
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeBranchId)) {
            // Mode Cabang: DO harus milik cabang aktif
            return (string)$deliveryOrder->branch_id === (string)$activeBranchId;
        }

        // Mode PT Pusat: DO harus milik company_id aktif dan branch_id harus NULL
        return (string)$deliveryOrder->company_id === (string)$activeCompanyId && is_null($deliveryOrder->branch_id);
    }
}
