<?php

namespace App\Http\Controllers;

use App\Models\SalesQuotation;
use App\Models\SalesQuotationItem;
use App\Models\Tender;
use App\Models\MasterCustomer;
use App\Models\MasterBarang;
use App\Models\MasterKategoriBarang;
use App\Models\MasterCurrency;
use App\Models\MasterPajak;
use App\Models\PaymentTerm;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Karyawan;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Http\Controllers\WhatsAppGatewayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SalesQuotationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || (!$user->hasAnyRole(['Super Admin', 'Owner', 'Admin']) && !$user->can('lihat-penawaran'))) {
            $this->authorize('lihat-penawaran');
        }

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $customers = MasterCustomer::forActiveCompany()->where('status', 'Active')->select('id', 'nama_customer', 'nama_perusahaan', 'kode_customer')->orderBy('nama_perusahaan', 'asc')->orderBy('nama_customer', 'asc')->get();
        $tenders = Tender::forActiveCompany()->select('id', 'nomor_kontrak', 'nama_tender')->orderBy('id', 'desc')->get();

        // Calculate KPI Metrics for Top Summary
        $kpis = [
            'total_penawaran' => SalesQuotation::forActiveCompany()->where('status', '!=', 'Revised')->count(),
            'menunggu'        => SalesQuotation::forActiveCompany()->whereIn('status', ['Draft', 'Sent'])->count(),
            'approved'        => SalesQuotation::forActiveCompany()->where('status', 'Approved')->count(),
            'so'              => SalesQuotation::forActiveCompany()->where('status', 'Converted_to_SO')->count(),
            'total_nilai'     => (float) SalesQuotation::forActiveCompany()->where('status', '!=', 'Revised')->where('status', '!=', 'Rejected')->sum('grand_total'),
        ];

        return view('modul_marketing.penawaran.index', compact('customers', 'tenders', 'kpis'));
    }

    public function getData(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->hasAnyRole(['Super Admin', 'Owner', 'Admin']) && !$user->can('lihat-penawaran'))) {
            $this->authorize('lihat-penawaran');
        }

        $canSeeHpp = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-penawaran') || $user->can('lihat-hpp-tender') || $user->can('akses-accounting')) : false;

        $query = SalesQuotation::forActiveCompany()
            ->with([
                'customer',
                'tender',
                'salesOrder'
            ])
            ->select([
                'sales_quotations.id',
                'sales_quotations.company_id',
                'sales_quotations.branch_id',
                'sales_quotations.nomor_quotation',
                'sales_quotations.revisi',
                'sales_quotations.parent_quotation_id',
                'sales_quotations.customer_id',
                'sales_quotations.tender_id',
                'sales_quotations.sales_id',
                'sales_quotations.tanggal_quotation',
                'sales_quotations.berlaku_hingga',
                'sales_quotations.status',
                'sales_quotations.grand_total',
                'sales_quotations.total_hpp',
                'sales_quotations.margin_persen',
                'sales_quotations.sales_order_id',
                'sales_quotations.created_at'
            ]);

        // Status Filtering
        if ($request->filled('status')) {
            $query->where('sales_quotations.status', $request->status);
        } else {
            // Default view: Exclude superseded/archived revisions unless user toggles "include_revisions"
            if (!$request->boolean('include_revisions')) {
                $query->where('sales_quotations.status', '!=', 'Revised');
            }
        }

        // Filters
        if ($request->filled('customer_id')) {
            $query->where('sales_quotations.customer_id', $request->customer_id);
        }
        if ($request->filled('tender_id')) {
            $query->where('sales_quotations.tender_id', $request->tender_id);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('sales_quotations.tanggal_quotation', [$request->start_date, $request->end_date]);
        }

        return DataTables::of($query)
            ->filterColumn('nomor_formatted', function($query, $keyword) {
                $query->where('sales_quotations.nomor_quotation', 'like', "%{$keyword}%");
            })
            ->filterColumn('customer_info', function($query, $keyword) {
                $query->whereHas('customer', function($q) use ($keyword) {
                    $q->where('nama_customer', 'like', "%{$keyword}%")
                      ->orWhere('nama_perusahaan', 'like', "%{$keyword}%");
                })->orWhereHas('tender', function($q) use ($keyword) {
                    $q->where('nama_tender', 'like', "%{$keyword}%")
                      ->orWhere('nomor_kontrak', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('nomor_formatted', function($query, $order) {
                $query->orderBy('sales_quotations.nomor_quotation', $order);
            })
            ->orderColumn('customer_info', function($query, $order) {
                $query->orderBy('sales_quotations.customer_id', $order);
            })
            ->orderColumn('tanggal_formatted', function($query, $order) {
                $query->orderBy('sales_quotations.tanggal_quotation', $order);
            })
            ->orderColumn('grand_total_formatted', function($query, $order) {
                $query->orderBy('sales_quotations.grand_total', $order);
            })
            ->orderColumn('hpp_margin', function($query, $order) {
                $query->orderBy('sales_quotations.margin_persen', $order);
            })
            ->orderColumn('status_badge', function($query, $order) {
                $query->orderBy('sales_quotations.status', $order);
            })
            ->addColumn('nomor_formatted', function ($q) {
                $revBadge = $q->revisi > 0 
                    ? '<span class="badge badge-soft-indigo fs-9 px-2 py-0.5 rounded-pill fw-semibold"><i class="bi bi-clock-history me-0.5"></i>Rev.' . $q->revisi . '</span>' 
                    : '<span class="badge badge-soft-neutral fs-9 px-2 py-0.5 rounded-pill text-muted">Rev.0</span>';
                return '<div class="d-flex align-items-center gap-1.5"><span class="fw-bold text-primary font-monospace fs-8">' . e($q->nomor_quotation) . '</span>' . $revBadge . '</div>';
            })
            ->addColumn('customer_info', function ($q) {
                $corp = $q->customer ? ($q->customer->nama_perusahaan ?: $q->customer->nama_customer) : '-';
                $custName = e($corp);
                $tenderInfo = '';
                if ($q->tender) {
                    $tenderInfo = '<div class="fs-9 text-muted text-truncate mt-0.5" style="max-width: 220px;"><i class="bi bi-briefcase me-1 text-info"></i>' . e($q->tender->nama_tender) . '</div>';
                }
                return '<div><div class="fw-semibold text-body fs-8">' . $custName . '</div>' . $tenderInfo . '</div>';
            })
            ->addColumn('tanggal_formatted', function ($q) {
                return '<span class="fs-8 text-secondary">' . ($q->tanggal_quotation ? $q->tanggal_quotation->format('d/m/Y') : '-') . '</span>';
            })
            ->addColumn('grand_total_formatted', function ($q) {
                return '<span class="fw-bold fs-8 text-body font-monospace">Rp ' . number_format($q->grand_total, 0, ',', '.') . '</span>';
            })
            ->addColumn('hpp_margin', function ($q) use ($canSeeHpp) {
                if (!$canSeeHpp) return '<span class="text-muted fs-8">-</span>';
                $margin = (float)$q->margin_persen;
                $badgeClass = $margin >= 20 ? 'text-success' : ($margin >= 10 ? 'text-warning' : 'text-danger');
                return '<div class="fs-8 text-secondary">HPP: Rp ' . number_format($q->total_hpp, 0, ',', '.') . '</div>
                        <div class="fs-9 fw-bold ' . $badgeClass . '"><i class="bi bi-graph-up-arrow me-1"></i>Margin: ' . number_format($margin, 1) . '%</div>';
            })
            ->addColumn('status_badge', function ($q) {
                $badges = [
                    'Draft'           => '<span class="badge badge-soft-neutral rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-pencil-square me-1"></i>Draft</span>',
                    'Sent'            => '<span class="badge badge-soft-sky rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-send me-1"></i>Terkirim</span>',
                    'Revised'         => '<span class="badge badge-soft-amber rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-arrow-repeat me-1"></i>Direvisi</span>',
                    'Approved'        => '<span class="badge badge-soft-emerald rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Disetujui</span>',
                    'Rejected'        => '<span class="badge badge-soft-rose rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Ditolak</span>',
                    'Converted_to_SO' => '<span class="badge badge-soft-indigo rounded-pill px-2.5 py-1 fs-8 fw-semibold"><i class="bi bi-cart-check-fill me-1"></i>Jadi SO</span>' . ($q->salesOrder ? '<div class="fs-9 font-monospace text-primary mt-0.5">' . e($q->salesOrder->nomor_so) . '</div>' : ''),
                ];
                return $badges[$q->status] ?? '<span class="badge badge-soft-neutral rounded-pill px-2.5 py-1 fs-8">' . e($q->status) . '</span>';
            })
            ->addColumn('action', function ($q) {
                $user = Auth::user();
                $btn = '<div class="d-flex align-items-center gap-1 justify-content-end">';

                // Detail
                $btn .= '<button type="button" class="btn btn-sm btn-light border shadow-xs btn-detail" data-id="' . $q->id . '" title="Lihat Detail Penawaran"><i class="bi bi-eye text-primary"></i></button>';

                // Print
                if ($user->can('cetak-penawaran') || $user->hasRole('Super Admin')) {
                    $btn .= '<a href="' . route('penawaran.print', $q->id) . '" target="_blank" class="btn btn-sm btn-outline-secondary shadow-xs" title="Cetak Surat Penawaran PDF"><i class="bi bi-printer"></i></a>';
                }

                // Edit (Draft / Sent)
                if (in_array($q->status, ['Draft', 'Sent']) && ($user->can('edit-penawaran') || $user->hasAnyRole(['Super Admin', 'Owner']))) {
                    $btn .= '<a href="' . route('penawaran.edit', $q->id) . '" class="btn btn-sm btn-outline-primary shadow-xs" title="Edit Penawaran"><i class="bi bi-pencil-square"></i></a>';
                }

                // Buat Revisi (Draft / Sent / Approved / Rejected)
                if (in_array($q->status, ['Draft', 'Sent', 'Approved', 'Rejected']) && ($user->can('revisi-penawaran') || $user->hasAnyRole(['Super Admin', 'Owner']))) {
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-warning shadow-xs btn-revisi" data-id="' . $q->id . '" data-nomor="' . e($q->nomor_quotation) . '" title="Buat Revisi Penawaran"><i class="bi bi-arrow-repeat"></i></button>';
                }

                // Convert to SO (Approved)
                if ($q->status === 'Approved' && !$q->sales_order_id && ($user->can('tambah-sales-order') || $user->hasRole('Super Admin'))) {
                    $btn .= '<button type="button" class="btn btn-sm btn-success text-white shadow-xs btn-convert-so" data-id="' . $q->id . '" data-nomor="' . e($q->nomor_quotation) . '" title="Terbitkan Sales Order (SO)"><i class="bi bi-cart-plus me-1"></i> SO</button>';
                }

                // Delete (Draft / Rejected)
                if (in_array($q->status, ['Draft', 'Rejected']) && ($user->can('hapus-penawaran') || $user->hasRole('Super Admin'))) {
                    $btn .= '<button type="button" class="btn btn-sm btn-outline-danger shadow-xs btn-delete" data-id="' . $q->id . '" data-nomor="' . e($q->nomor_quotation) . '" title="Hapus Penawaran"><i class="bi bi-trash"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['nomor_formatted', 'customer_info', 'tanggal_formatted', 'grand_total_formatted', 'hpp_margin', 'status_badge', 'action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $this->authorize('tambah-penawaran');

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $user = Auth::user();
        $canSeeHpp = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-penawaran') || $user->can('lihat-hpp-tender') || $user->can('akses-accounting')) : false;

        $customers = MasterCustomer::forActiveCompany()->where('status', 'Active')->orderBy('nama_perusahaan', 'asc')->orderBy('nama_customer', 'asc')->get();
        $tenders = Tender::forActiveCompany()
            ->with([
                'customer',
                'items.barang.satuan',
                'items.barang.kategori',
                'items.barang.primaryFoto'
            ])
            ->whereNotIn('status', ['Lost', 'Cancelled'])
            ->orderBy('id', 'desc')
            ->get();
        $salesList = Karyawan::forActiveCompany()->where('status', 'Aktif')->get();
        $currencies = MasterCurrency::where('status', 'Active')->get();
        $paymentTerms = PaymentTerm::forActiveCompany()->orderBy('jumlah_hari', 'asc')->get();
        $masterPajaks = MasterPajak::forActiveCompany()->active()->orderBy('nama_pajak')->get();
        $pajakPpnList = $masterPajaks->filter(function ($p) {
            return stripos($p->nama_pajak, 'ppn') !== false || stripos($p->kode_pajak, 'ppn') !== false;
        });
        if ($pajakPpnList->isEmpty()) {
            $pajakPpnList = $masterPajaks;
        }

        // Active Categories and Items
        $kategoriList = MasterKategoriBarang::forActiveCompany()
            ->where('status', 'Active')
            ->orderBy('nama_kategori', 'asc')
            ->get();
        $allowedKategoriIds = $kategoriList->pluck('id')->toArray();

        $barangList = MasterBarang::where('status', 'Active')
            ->whereNull('parent_id')
            ->when($activeCompanyId, function ($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId);
            })
            ->where(function ($q) use ($allowedKategoriIds) {
                $q->whereIn('kategori_barang_id', $allowedKategoriIds)
                  ->orWhereNull('kategori_barang_id');
            })
            ->with(['satuan', 'kategori', 'primaryFoto'])
            ->orderBy('nama_barang', 'asc')
            ->get();

        $barangIds = $barangList->pluck('id')->toArray();
        $hppMap = $this->resolveBarangHppMap($barangIds, $canSeeHpp);

        $barangList->transform(function ($b) use ($hppMap, $canSeeHpp) {
            $b->hpp_referensi = $canSeeHpp ? ($hppMap[$b->id] ?? 0) : 0;
            $b->resolved_photo_url = $this->resolvePhotoUrl($b->primaryFoto ? $b->primaryFoto->foto_path : null);
            $b->harga_retail = (float)($b->harga_jual_retail ?: 0);
            $b->harga_grosir = (float)($b->harga_jual_grosir ?: 0);
            $b->harga_tender = (float)($b->harga_jual_tender ?: 0);
            return $b;
        });

        $tendersMap = $tenders->mapWithKeys(function ($t) use ($hppMap, $canSeeHpp) {
            return [
                $t->id => [
                    'id'             => $t->id,
                    'nomor_kontrak'  => $t->nomor_kontrak,
                    'nama_tender'    => $t->nama_tender,
                    'customer_id'    => $t->customer_id,
                    'sales_id'       => $t->sales_id,
                    'nilai_estimasi' => (float)$t->nilai_estimasi,
                    'items'          => $t->items->map(function ($it) use ($hppMap, $canSeeHpp) {
                        $b = $it->barang;
                        $photoUrl = $this->resolvePhotoUrl($b?->primaryFoto ? $b->primaryFoto->foto_path : null);
                        $hargaRetail = (float)($b?->harga_jual_retail ?: 0);
                        $hargaGrosir = (float)($b?->harga_jual_grosir ?: 0);
                        $hargaTender = (float)($b?->harga_jual_tender ?: 0);
                        $harga = (float)($it->harga_estimasi ?: ($hargaTender ?: ($hargaGrosir ?: ($hargaRetail ?: 0))));
                        $hpp = ($b && $canSeeHpp) ? ($hppMap[$b->id] ?? 0) : 0;

                        return [
                            'barang_id'          => $it->barang_id,
                            'is_custom'          => empty($it->barang_id),
                            'kode'               => $b?->kode_barang ?? 'CUSTOM',
                            'nama'               => $b?->nama_barang ?? $it->nama_barang_custom,
                            'nama_barang_custom' => $it->nama_barang_custom,
                            'deskripsi_item'     => $it->catatan_item,
                            'kategori_id'        => $b?->kategori_barang_id,
                            'satuan_id'          => $b?->satuan_id,
                            'satuan_nama'        => $it->satuan ?: ($b?->satuan?->nama_satuan ?? 'Pcs'),
                            'qty'                => (float)$it->qty ?: 1,
                            'harga'              => $harga,
                            'harga_retail'       => $hargaRetail,
                            'harga_grosir'       => $hargaGrosir,
                            'harga_tender'       => $hargaTender,
                            'tier_default'       => 'tender',
                            'hpp'                => (float)$hpp,
                            'photo_url'          => $photoUrl
                        ];
                    })->values()
                ]
            ];
        });

        // Preselected Tender / Customer if passed via Query
        $selectedTender = null;
        if ($request->filled('tender_id')) {
            $selectedTender = Tender::with('items.barang')->find($request->tender_id);
        }

        $selectedCustomerId = $request->query('customer_id', $selectedTender?->customer_id);
        $nomorQuotation = SalesQuotation::generateNomor($activeCompanyId);

        return view('modul_marketing.penawaran.tambah', compact(
            'customers',
            'tenders',
            'tendersMap',
            'salesList',
            'currencies',
            'paymentTerms',
            'masterPajaks',
            'pajakPpnList',
            'kategoriList',
            'barangList',
            'canSeeHpp',
            'selectedTender',
            'selectedCustomerId',
            'nomorQuotation'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-penawaran');
        $this->sanitizeCurrencyInputs($request);

        $request->validate([
            'customer_id'       => 'required|exists:master_customers,id',
            'tanggal_quotation' => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.qty'       => 'required|numeric|min:0.0001',
            'items.*.harga'     => 'required|numeric|min:0',
        ], [
            'customer_id.required'       => 'Customer wajib dipilih!',
            'tanggal_quotation.required' => 'Tanggal penawaran wajib diisi!',
            'items.required'             => 'Minimal tambahkan 1 item barang dalam penawaran!',
            'items.min'                  => 'Minimal tambahkan 1 item barang dalam penawaran!',
        ]);

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $activeBranchId = session('active_branch_id');

        try {
            DB::beginTransaction();

            $nomorQuotation = $request->nomor_quotation ?: SalesQuotation::generateNomor($activeCompanyId);

            // Calculation
            $subtotal = 0;
            $totalHpp = 0;
            $itemsData = [];

            foreach ($request->items as $idx => $item) {
                $qty = (float)($item['qty'] ?? 1);
                $harga = (float)($item['harga'] ?? 0);
                $diskonPersen = (float)($item['diskon_persen'] ?? 0);
                $diskonNominal = (float)($item['diskon_nominal'] ?? 0);

                if ($diskonPersen > 0) {
                    $diskonNominal = ($harga * $qty) * ($diskonPersen / 100);
                }
                $itemSubtotal = max(0, ($harga * $qty) - $diskonNominal);
                $subtotal += $itemSubtotal;

                $hppSatuan = (float)($item['hpp_satuan'] ?? 0);
                $itemTotalHpp = $hppSatuan * $qty;
                $totalHpp += $itemTotalHpp;

                $itemLaba = $itemSubtotal - $itemTotalHpp;
                $itemMargin = $itemSubtotal > 0 ? ($itemLaba / $itemSubtotal) * 100 : 0;

                $itemsData[] = [
                    'barang_id'          => !empty($item['barang_id']) ? $item['barang_id'] : null,
                    'nama_barang_custom' => $item['nama_barang_custom'] ?? null,
                    'deskripsi_item'     => $item['deskripsi_item'] ?? null,
                    'kategori_id'        => !empty($item['kategori_id']) ? $item['kategori_id'] : null,
                    'satuan_id'          => !empty($item['satuan_id']) ? $item['satuan_id'] : null,
                    'nama_satuan'        => $item['nama_satuan'] ?? null,
                    'qty'                => $qty,
                    'harga_satuan'       => $harga,
                    'diskon_persen'      => $diskonPersen,
                    'diskon_nominal'     => $diskonNominal,
                    'subtotal'           => $itemSubtotal,
                    'hpp_satuan'         => $hppSatuan,
                    'total_hpp'          => $itemTotalHpp,
                    'estimasi_laba'      => $itemLaba,
                    'margin_persen'      => $itemMargin,
                    'catatan'            => $item['catatan'] ?? null,
                    'urutan'             => $idx + 1,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }

            // Global Discount
            $diskonTipe = $request->diskon_tipe ?: 'Nominal';
            $diskonPersen = (float)($request->diskon_persen ?: 0);
            $diskonNominal = (float)($request->diskon_nominal ?: 0);
            if ($diskonTipe === 'Persen' && $diskonPersen > 0) {
                $diskonNominal = $subtotal * ($diskonPersen / 100);
            }
            $dpp = max(0, $subtotal - $diskonNominal);

            // PPN & PPh
            $tipePpn = $request->tipe_ppn ?: 'None';
            $tarifPpn = (float)($request->tarif_ppn ?: 0);
            if ($tipePpn === 'Include' && $tarifPpn > 0) {
                $nilaiPpn = $dpp - ($dpp / (1 + ($tarifPpn / 100)));
            } elseif ($tipePpn === 'Exclude' && $tarifPpn > 0) {
                $nilaiPpn = $dpp * ($tarifPpn / 100);
            } else {
                $nilaiPpn = 0;
            }

            $tipePph = $request->tipe_pph ?: 'None';
            $tarifPph = (float)($request->tarif_pph ?: 0);
            $nilaiPph = ($tipePph === 'Exclude' && $tarifPph > 0) ? ($dpp * ($tarifPph / 100)) : 0;

            $biayaPengiriman = (float)($request->biaya_pengiriman ?: 0);
            $grandTotal = ($tipePpn === 'Include') ? ($dpp + $biayaPengiriman) : ($dpp + $nilaiPpn + $biayaPengiriman);

            $estimasiLabaKotor = $grandTotal - $totalHpp;
            $marginPersen = $grandTotal > 0 ? ($estimasiLabaKotor / $grandTotal) * 100 : 0;

            if ($totalHpp > 0 && ($estimasiLabaKotor < 0 || $grandTotal < $totalHpp)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Penawaran tidak dapat disimpan karena menghasilkan Margin Negatif (Estimasi Rugi Rp ' . number_format(abs($estimasiLabaKotor), 0, ',', '.') . '). Total penawaran harga jual tidak boleh lebih rendah dari total HPP modal pokok.');
            }

            // DP & Sisa
            $tipeDp = $request->tipe_dp ?: 'None';
            $dpPersen = (float)($request->dp_persen ?: 0);
            $dpNominal = (float)($request->dp_nominal ?: 0);
            if ($tipeDp === 'Persen' && $dpPersen > 0) {
                $dpNominal = $grandTotal * ($dpPersen / 100);
            }
            $sisaTagihan = max(0, $grandTotal - $dpNominal);

            $quotation = SalesQuotation::create([
                'company_id'               => $activeCompanyId,
                'branch_id'                => $activeBranchId,
                'nomor_quotation'          => $nomorQuotation,
                'revisi'                   => 0,
                'customer_id'              => $request->customer_id,
                'tender_id'                => $request->tender_id ?: null,
                'sales_id'                 => $request->sales_id ?: null,
                'tanggal_quotation'        => $request->tanggal_quotation,
                'berlaku_hingga'           => $request->berlaku_hingga ?: null,
                'perihal'                  => $request->perihal ?: 'Penawaran Harga',
                'status'                   => $request->status ?: 'Draft',
                'payment_term_id'          => $request->payment_term_id ?: null,
                'tipe_dp'                  => $tipeDp,
                'dp_persen'                => $dpPersen,
                'dp_nominal'               => $dpNominal,
                'sisa_tagihan'             => $sisaTagihan,
                'catatan_pembayaran'       => $request->catatan_pembayaran,
                'currency'                 => $request->currency ?: 'IDR',
                'subtotal'                 => $subtotal,
                'diskon_tipe'              => $diskonTipe,
                'diskon_persen'            => $diskonPersen,
                'diskon_nominal'           => $diskonNominal,
                'ppn_pajak_id'             => $request->ppn_pajak_id ?: null,
                'tipe_ppn'                 => $tipePpn,
                'tarif_ppn'                => $tarifPpn,
                'nilai_ppn'                => $nilaiPpn,
                'pph_pajak_id'             => $request->pph_pajak_id ?: null,
                'tipe_pph'                 => $tipePph,
                'tarif_pph'                => $tarifPph,
                'nilai_pph'                => $nilaiPph,
                'biaya_pengiriman'         => $biayaPengiriman,
                'catatan_biaya_pengiriman' => $request->catatan_biaya_pengiriman,
                'grand_total'              => $grandTotal,
                'total_hpp'                => $totalHpp,
                'estimasi_laba_kotor'      => $estimasiLabaKotor,
                'margin_persen'            => $marginPersen,
                'catatan'                  => $request->catatan,
                'syarat_ketentuan'         => $request->syarat_ketentuan,
                'created_by'               => Auth::id(),
            ]);

            foreach ($itemsData as $it) {
                $it['sales_quotation_id'] = $quotation->id;
                SalesQuotationItem::create($it);
            }

            // If linked to tender, update tender's nilai_estimasi or deal if needed
            if ($quotation->tender_id && $quotation->tender) {
                $quotation->tender->update([
                    'nilai_deal' => $grandTotal
                ]);
            }

            DB::commit();

            // Send WhatsApp Notification to Approvers if status is Sent
            if ($status === 'Sent') {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendQuotationSubmissionNotification($quotation)
                );
            }

            return redirect()->route('penawaran.index')->with('success', 'Penawaran Harga ' . $quotation->nomor_quotation . ' berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store SalesQuotation: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan penawaran: ' . $e->getMessage());
        }
    }

    public function show(SalesQuotation $quotation)
    {
        $user = Auth::user();
        if (!$user || (!$user->hasAnyRole(['Super Admin', 'Owner', 'Admin']) && !$user->can('lihat-penawaran'))) {
            $this->authorize('lihat-penawaran');
        }
        $this->authorizeCompanyAccess($quotation);

        $quotation->load([
            'customer.defaultAddress',
            'tender',
            'sales',
            'paymentTerm',
            'ppnPajak',
            'pphPajak',
            'items.barang.satuan',
            'items.barang.primaryFoto',
            'items.kategori',
            'items.satuan',
            'parentQuotation',
            'revisions',
            'salesOrder',
            'creator',
            'approver'
        ]);

        // Get full revision timeline
        $rootId = $quotation->parent_quotation_id ?: $quotation->id;
        $allRevisions = SalesQuotation::where('id', $rootId)
            ->orWhere('parent_quotation_id', $rootId)
            ->orderBy('revisi', 'asc')
            ->get(['id', 'nomor_quotation', 'revisi', 'status', 'tanggal_quotation', 'grand_total', 'created_at']);

        $user = Auth::user();
        $canSeeHpp = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-penawaran') || $user->can('lihat-hpp-tender') || $user->can('akses-accounting')) : false;
        $canApprove = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('approve-penawaran')) : false;
        $canConvertSo = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('tambah-sales-order')) : false;
        $canPrint = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('cetak-penawaran')) : false;

        return response()->json([
            'data'           => $quotation,
            'all_revisions'  => $allRevisions,
            'can_see_hpp'    => $canSeeHpp,
            'can_approve'    => $canApprove,
            'can_convert_so' => $canConvertSo,
            'can_print'      => $canPrint,
            'terbilang'      => $this->terbilang($quotation->grand_total) . ' Rupiah',
        ]);
    }

    public function edit(SalesQuotation $quotation)
    {
        $this->authorize('edit-penawaran');
        $this->authorizeCompanyAccess($quotation);

        if (!in_array($quotation->status, ['Draft', 'Sent'])) {
            return redirect()->route('penawaran.index')->with('error', 'Penawaran berstatus ' . $quotation->status . ' tidak dapat diedit langsung. Silakan gunakan tombol Buat Revisi.');
        }

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $user = Auth::user();
        $canSeeHpp = $user ? ($user->hasAnyRole(['Super Admin', 'Owner']) || $user->can('lihat-hpp-penawaran') || $user->can('lihat-hpp-tender') || $user->can('akses-accounting')) : false;

        $customers = MasterCustomer::forActiveCompany()->where('status', 'Active')->orderBy('nama_perusahaan', 'asc')->orderBy('nama_customer', 'asc')->get();
        $tenders = Tender::forActiveCompany()
            ->with([
                'customer',
                'items.barang.satuan',
                'items.barang.kategori',
                'items.barang.primaryFoto'
            ])
            ->orderBy('id', 'desc')
            ->get();
        $salesList = Karyawan::forActiveCompany()->where('status', 'Aktif')->get();
        $currencies = MasterCurrency::where('status', 'Active')->get();
        $paymentTerms = PaymentTerm::forActiveCompany()->orderBy('jumlah_hari', 'asc')->get();
        $masterPajaks = MasterPajak::forActiveCompany()->active()->orderBy('nama_pajak')->get();
        $pajakPpnList = $masterPajaks->filter(function ($p) {
            return stripos($p->nama_pajak, 'ppn') !== false || stripos($p->kode_pajak, 'ppn') !== false;
        });
        if ($pajakPpnList->isEmpty()) {
            $pajakPpnList = $masterPajaks;
        }

        $kategoriList = MasterKategoriBarang::forActiveCompany()->where('status', 'Active')->orderBy('nama_kategori', 'asc')->get();
        $allowedKategoriIds = $kategoriList->pluck('id')->toArray();

        $barangList = MasterBarang::where('status', 'Active')
            ->whereNull('parent_id')
            ->when($activeCompanyId, function ($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId);
            })
            ->where(function ($q) use ($allowedKategoriIds) {
                $q->whereIn('kategori_barang_id', $allowedKategoriIds)
                  ->orWhereNull('kategori_barang_id');
            })
            ->with(['satuan', 'kategori', 'primaryFoto'])
            ->orderBy('nama_barang', 'asc')
            ->get();

        $barangIds = $barangList->pluck('id')->toArray();
        $hppMap = $this->resolveBarangHppMap($barangIds, $canSeeHpp);

        $barangList->transform(function ($b) use ($hppMap, $canSeeHpp) {
            $b->hpp_referensi = $canSeeHpp ? ($hppMap[$b->id] ?? 0) : 0;
            $b->resolved_photo_url = $this->resolvePhotoUrl($b->primaryFoto ? $b->primaryFoto->foto_path : null);
            $b->harga_retail = (float)($b->harga_jual_retail ?: 0);
            $b->harga_grosir = (float)($b->harga_jual_grosir ?: 0);
            $b->harga_tender = (float)($b->harga_jual_tender ?: 0);
            return $b;
        });

        $tendersMap = $tenders->mapWithKeys(function ($t) use ($hppMap, $canSeeHpp) {
            return [
                $t->id => [
                    'id'             => $t->id,
                    'nomor_kontrak'  => $t->nomor_kontrak,
                    'nama_tender'    => $t->nama_tender,
                    'customer_id'    => $t->customer_id,
                    'sales_id'       => $t->sales_id,
                    'nilai_estimasi' => (float)$t->nilai_estimasi,
                    'items'          => $t->items->map(function ($it) use ($hppMap, $canSeeHpp) {
                        $b = $it->barang;
                        $photoUrl = $this->resolvePhotoUrl($b?->primaryFoto ? $b->primaryFoto->foto_path : null);
                        $hargaRetail = (float)($b?->harga_jual_retail ?: 0);
                        $hargaGrosir = (float)($b?->harga_jual_grosir ?: 0);
                        $hargaTender = (float)($b?->harga_jual_tender ?: 0);
                        $harga = (float)($it->harga_estimasi ?: ($hargaTender ?: ($hargaGrosir ?: ($hargaRetail ?: 0))));
                        $hpp = ($b && $canSeeHpp) ? ($hppMap[$b->id] ?? 0) : 0;

                        return [
                            'barang_id'          => $it->barang_id,
                            'is_custom'          => empty($it->barang_id),
                            'kode'               => $b?->kode_barang ?? 'CUSTOM',
                            'nama'               => $b?->nama_barang ?? $it->nama_barang_custom,
                            'nama_barang_custom' => $it->nama_barang_custom,
                            'deskripsi_item'     => $it->catatan_item,
                            'kategori_id'        => $b?->kategori_barang_id,
                            'satuan_id'          => $b?->satuan_id,
                            'satuan_nama'        => $it->satuan ?: ($b?->satuan?->nama_satuan ?? 'Pcs'),
                            'qty'                => (float)$it->qty ?: 1,
                            'harga'              => $harga,
                            'harga_retail'       => $hargaRetail,
                            'harga_grosir'       => $hargaGrosir,
                            'harga_tender'       => $hargaTender,
                            'tier_default'       => 'tender',
                            'hpp'                => (float)$hpp,
                            'photo_url'          => $photoUrl
                        ];
                    })->values()
                ]
            ];
        });

        $quotation->load(['items.barang.primaryFoto', 'items.satuan']);

        return view('modul_marketing.penawaran.edit', compact(
            'quotation',
            'customers',
            'tenders',
            'tendersMap',
            'salesList',
            'currencies',
            'paymentTerms',
            'masterPajaks',
            'pajakPpnList',
            'kategoriList',
            'barangList',
            'canSeeHpp'
        ));
    }

    public function update(Request $request, SalesQuotation $quotation)
    {
        $this->authorize('edit-penawaran');
        $this->authorizeCompanyAccess($quotation);
        $this->sanitizeCurrencyInputs($request);

        $request->validate([
            'customer_id'       => 'required|exists:master_customers,id',
            'tanggal_quotation' => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.qty'       => 'required|numeric|min:0.0001',
            'items.*.harga'     => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $totalHpp = 0;
            $itemsData = [];

            foreach ($request->items as $idx => $item) {
                $qty = (float)($item['qty'] ?? 1);
                $harga = (float)($item['harga'] ?? 0);
                $diskonPersen = (float)($item['diskon_persen'] ?? 0);
                $diskonNominal = (float)($item['diskon_nominal'] ?? 0);

                if ($diskonPersen > 0) {
                    $diskonNominal = ($harga * $qty) * ($diskonPersen / 100);
                }
                $itemSubtotal = max(0, ($harga * $qty) - $diskonNominal);
                $subtotal += $itemSubtotal;

                $hppSatuan = (float)($item['hpp_satuan'] ?? 0);
                $itemTotalHpp = $hppSatuan * $qty;
                $totalHpp += $itemTotalHpp;

                $itemLaba = $itemSubtotal - $itemTotalHpp;
                $itemMargin = $itemSubtotal > 0 ? ($itemLaba / $itemSubtotal) * 100 : 0;

                $itemsData[] = [
                    'sales_quotation_id' => $quotation->id,
                    'barang_id'          => !empty($item['barang_id']) ? $item['barang_id'] : null,
                    'nama_barang_custom' => $item['nama_barang_custom'] ?? null,
                    'deskripsi_item'     => $item['deskripsi_item'] ?? null,
                    'kategori_id'        => !empty($item['kategori_id']) ? $item['kategori_id'] : null,
                    'satuan_id'          => !empty($item['satuan_id']) ? $item['satuan_id'] : null,
                    'nama_satuan'        => $item['nama_satuan'] ?? null,
                    'qty'                => $qty,
                    'harga_satuan'       => $harga,
                    'diskon_persen'      => $diskonPersen,
                    'diskon_nominal'     => $diskonNominal,
                    'subtotal'           => $itemSubtotal,
                    'hpp_satuan'         => $hppSatuan,
                    'total_hpp'          => $itemTotalHpp,
                    'estimasi_laba'      => $itemLaba,
                    'margin_persen'      => $itemMargin,
                    'catatan'            => $item['catatan'] ?? null,
                    'urutan'             => $idx + 1,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }

            // Global Discount
            $diskonTipe = $request->diskon_tipe ?: 'Nominal';
            $diskonPersen = (float)($request->diskon_persen ?: 0);
            $diskonNominal = (float)($request->diskon_nominal ?: 0);
            if ($diskonTipe === 'Persen' && $diskonPersen > 0) {
                $diskonNominal = $subtotal * ($diskonPersen / 100);
            }
            $dpp = max(0, $subtotal - $diskonNominal);

            // PPN & PPh
            $tipePpn = $request->tipe_ppn ?: 'None';
            $tarifPpn = (float)($request->tarif_ppn ?: 0);
            if ($tipePpn === 'Include' && $tarifPpn > 0) {
                $nilaiPpn = $dpp - ($dpp / (1 + ($tarifPpn / 100)));
            } elseif ($tipePpn === 'Exclude' && $tarifPpn > 0) {
                $nilaiPpn = $dpp * ($tarifPpn / 100);
            } else {
                $nilaiPpn = 0;
            }

            $tipePph = $request->tipe_pph ?: 'None';
            $tarifPph = (float)($request->tarif_pph ?: 0);
            $nilaiPph = ($tipePph === 'Exclude' && $tarifPph > 0) ? ($dpp * ($tarifPph / 100)) : 0;

            $biayaPengiriman = (float)($request->biaya_pengiriman ?: 0);
            $grandTotal = ($tipePpn === 'Include') ? ($dpp + $biayaPengiriman) : ($dpp + $nilaiPpn + $biayaPengiriman);

            $estimasiLabaKotor = $grandTotal - $totalHpp;
            $marginPersen = $grandTotal > 0 ? ($estimasiLabaKotor / $grandTotal) * 100 : 0;

            if ($totalHpp > 0 && ($estimasiLabaKotor < 0 || $grandTotal < $totalHpp)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Penawaran tidak dapat disimpan karena menghasilkan Margin Negatif (Estimasi Rugi Rp ' . number_format(abs($estimasiLabaKotor), 0, ',', '.') . '). Total penawaran harga jual tidak boleh lebih rendah dari total HPP modal pokok.');
            }

            // DP & Sisa
            $tipeDp = $request->tipe_dp ?: 'None';
            $dpPersen = (float)($request->dp_persen ?: 0);
            $dpNominal = (float)($request->dp_nominal ?: 0);
            if ($tipeDp === 'Persen' && $dpPersen > 0) {
                $dpNominal = $grandTotal * ($dpPersen / 100);
            }
            $sisaTagihan = max(0, $grandTotal - $dpNominal);

            $quotation->update([
                'nomor_quotation'          => $request->nomor_quotation ?: $quotation->nomor_quotation,
                'customer_id'              => $request->customer_id,
                'tender_id'                => $request->tender_id ?: null,
                'sales_id'                 => $request->sales_id ?: null,
                'tanggal_quotation'        => $request->tanggal_quotation,
                'berlaku_hingga'           => $request->berlaku_hingga ?: null,
                'perihal'                  => $request->perihal ?: 'Penawaran Harga',
                'status'                   => $request->status ?: $quotation->status,
                'payment_term_id'          => $request->payment_term_id ?: null,
                'tipe_dp'                  => $tipeDp,
                'dp_persen'                => $dpPersen,
                'dp_nominal'               => $dpNominal,
                'sisa_tagihan'             => $sisaTagihan,
                'catatan_pembayaran'       => $request->catatan_pembayaran,
                'currency'                 => $request->currency ?: 'IDR',
                'subtotal'                 => $subtotal,
                'diskon_tipe'              => $diskonTipe,
                'diskon_persen'            => $diskonPersen,
                'diskon_nominal'           => $diskonNominal,
                'ppn_pajak_id'             => $request->ppn_pajak_id ?: null,
                'tipe_ppn'                 => $tipePpn,
                'tarif_ppn'                => $tarifPpn,
                'nilai_ppn'                => $nilaiPpn,
                'pph_pajak_id'             => $request->pph_pajak_id ?: null,
                'tipe_pph'                 => $tipePph,
                'tarif_pph'                => $tarifPph,
                'nilai_pph'                => $nilaiPph,
                'biaya_pengiriman'         => $biayaPengiriman,
                'catatan_biaya_pengiriman' => $request->catatan_biaya_pengiriman,
                'grand_total'              => $grandTotal,
                'total_hpp'                => $totalHpp,
                'estimasi_laba_kotor'      => $estimasiLabaKotor,
                'margin_persen'            => $marginPersen,
                'catatan'                  => $request->catatan,
                'syarat_ketentuan'         => $request->syarat_ketentuan,
            ]);

            // Sync Items
            $quotation->items()->delete();
            foreach ($itemsData as $it) {
                SalesQuotationItem::create($it);
            }

            // Sync deal value on tender if linked
            if ($quotation->tender_id && $quotation->tender) {
                $quotation->tender->update([
                    'nilai_deal' => $grandTotal
                ]);
            }

            DB::commit();

            // Send WhatsApp Notification to Approvers if status is Sent
            if ($quotation->status === 'Sent') {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendQuotationSubmissionNotification($quotation)
                );
            }

            return redirect()->route('penawaran.index')->with('success', 'Penawaran Harga ' . $quotation->nomor_quotation . ' berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update SalesQuotation: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui penawaran: ' . $e->getMessage());
        }
    }

    public function destroy(SalesQuotation $quotation)
    {
        $this->authorize('hapus-penawaran');
        $this->authorizeCompanyAccess($quotation);

        if (!in_array($quotation->status, ['Draft', 'Rejected'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya penawaran berstatus Draft atau Ditolak yang dapat dihapus.'
            ], 422);
        }

        try {
            $nomor = $quotation->nomor_quotation;
            $quotation->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Penawaran ' . $nomor . ' berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error destroy SalesQuotation: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus penawaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createRevision(Request $request, SalesQuotation $quotation)
    {
        $this->authorize('revisi-penawaran');
        $this->authorizeCompanyAccess($quotation);

        try {
            DB::beginTransaction();

            $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
            $newRevisiNum = $quotation->revisi + 1;

            // Mark old quotation as Revised
            $quotation->update(['status' => 'Revised']);

            // Clone to new quotation with increased revision count
            $newQuotation = $quotation->replicate(['sales_order_id', 'created_at', 'updated_at']);
            $newQuotation->parent_quotation_id = $quotation->parent_quotation_id ?: $quotation->id;
            $newQuotation->revisi = $newRevisiNum;
            $newQuotation->status = 'Draft';
            $newQuotation->tanggal_quotation = now()->toDateString();
            $newQuotation->created_by = Auth::id();
            $newQuotation->approved_by = null;
            $newQuotation->approved_at = null;
            $newQuotation->save();

            // Clone items
            foreach ($quotation->items as $item) {
                $newItem = $item->replicate(['created_at', 'updated_at']);
                $newItem->sales_quotation_id = $newQuotation->id;
                $newItem->save();
            }

            DB::commit();

            return response()->json([
                'status'       => 'success',
                'message'      => 'Revisi ' . $newRevisiNum . ' berhasil dibuat!',
                'redirect_url' => route('penawaran.edit', $newQuotation->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error createRevision SalesQuotation: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat revisi penawaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request, SalesQuotation $quotation)
    {
        $this->authorize('approve-penawaran');
        $this->authorizeCompanyAccess($quotation);

        if (!in_array($quotation->status, ['Draft', 'Sent'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya penawaran berstatus Draft atau Terkirim yang dapat disetujui / ditolak.'
            ], 422);
        }

        $newStatus = $request->status === 'Rejected' ? 'Rejected' : 'Approved';

        try {
            $quotation->update([
                'status'      => $newStatus,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            $approverUser = Auth::user();
            if ($newStatus === 'Approved') {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendQuotationApprovedNotification($quotation, $approverUser)
                );
            } else {
                WhatsAppGatewayController::runAsync(fn() =>
                    WhatsAppGatewayController::sendQuotationRejectedNotification($quotation, $approverUser, $request->alasan ?? null)
                );
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Penawaran ' . $quotation->nomor_quotation . ' berhasil ' . ($newStatus === 'Approved' ? 'disetujui (Approved)!' : 'ditolak (Rejected)!')
            ]);
        } catch (\Exception $e) {
            Log::error('Error approve SalesQuotation: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui status penawaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function convertToSalesOrder(Request $request, SalesQuotation $quotation)
    {
        $this->authorize('tambah-sales-order');
        $this->authorizeCompanyAccess($quotation);

        if ($quotation->status !== 'Approved') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Penawaran harus berstatus Disetujui (Approved) terlebih dahulu sebelum dapat dikonversi ke Sales Order!'
            ], 422);
        }

        if ($quotation->sales_order_id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Penawaran ini sudah pernah dikonversi ke Sales Order.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $activeCompanyId = $quotation->company_id;
            $activeBranchId = $quotation->branch_id;

            // Generate SO number
            $nomorSo = 'SO/' . date('Ymd') . '/' . str_pad(SalesOrder::where('company_id', $activeCompanyId)->count() + 1, 4, '0', STR_PAD_LEFT);

            $salesOrder = SalesOrder::create([
                'company_id'               => $activeCompanyId,
                'branch_id'                => $activeBranchId,
                'sales_quotation_id'       => $quotation->id,
                'tender_id'                => $quotation->tender_id,
                'nomor_so'                 => $nomorSo,
                'customer_id'              => $quotation->customer_id,
                'sales_id'                 => $quotation->sales_id,
                'tanggal_so'               => now()->toDateString(),
                'status'                   => 'Draft',
                'payment_term_id'          => $quotation->payment_term_id,
                'tipe_dp'                  => $quotation->tipe_dp,
                'dp_persen'                => $quotation->dp_persen,
                'dp_nominal'               => $quotation->dp_nominal,
                'sisa_tagihan'             => $quotation->sisa_tagihan,
                'subtotal'                 => $quotation->subtotal,
                'diskon_tipe'              => $quotation->diskon_tipe,
                'diskon_persen'            => $quotation->diskon_persen,
                'diskon_nominal'           => $quotation->diskon_nominal,
                'ppn_pajak_id'             => $quotation->ppn_pajak_id,
                'tipe_ppn'                 => $quotation->tipe_ppn,
                'tarif_ppn'                => $quotation->tarif_ppn,
                'nilai_ppn'                => $quotation->nilai_ppn,
                'pph_pajak_id'             => $quotation->pph_pajak_id,
                'tipe_pph'                 => $quotation->tipe_pph,
                'tarif_pph'                => $quotation->tarif_pph,
                'nilai_pph'                => $quotation->nilai_pph,
                'biaya_pengiriman'         => $quotation->biaya_pengiriman,
                'grand_total'              => $quotation->grand_total,
                'total_hpp'                => $quotation->total_hpp,
                'estimasi_laba_kotor'      => $quotation->estimasi_laba_kotor,
                'margin_persen'            => $quotation->margin_persen,
                'catatan'                  => $quotation->catatan,
                'syarat_ketentuan'         => $quotation->syarat_ketentuan,
                'created_by'               => Auth::id(),
            ]);

            foreach ($quotation->items as $idx => $qItem) {
                SalesOrderDetail::create([
                    'sales_order_id'     => $salesOrder->id,
                    'barang_id'          => $qItem->barang_id,
                    'nama_barang_custom' => $qItem->nama_barang_custom,
                    'deskripsi_item'     => $qItem->deskripsi_item,
                    'kategori_id'        => $qItem->kategori_id,
                    'satuan_id'          => $qItem->satuan_id,
                    'nama_satuan'        => $qItem->nama_satuan,
                    'qty'                => $qItem->qty,
                    'harga_satuan'       => $qItem->harga_satuan,
                    'diskon_persen'      => $qItem->diskon_persen,
                    'diskon_nominal'     => $qItem->diskon_nominal,
                    'subtotal'           => $qItem->subtotal,
                    'hpp_satuan'         => $qItem->hpp_satuan,
                    'total_hpp'          => $qItem->total_hpp,
                    'estimasi_laba'      => $qItem->estimasi_laba,
                    'margin_persen'      => $qItem->margin_persen,
                    'catatan'            => $qItem->catatan,
                    'urutan'             => $idx + 1,
                ]);
            }

            // Update quotation status and relation
            $quotation->update([
                'status'         => 'Converted_to_SO',
                'sales_order_id' => $salesOrder->id
            ]);

            // Also update tender's sales_order_id if linked
            if ($quotation->tender_id && $quotation->tender) {
                $quotation->tender->update([
                    'sales_order_id' => $salesOrder->id,
                    'status'         => 'Won'
                ]);
            }

            DB::commit();

            // Send WhatsApp Notification for SO Conversion
            WhatsAppGatewayController::runAsync(fn() =>
                WhatsAppGatewayController::sendQuotationConvertedToSoNotification($quotation, $salesOrder)
            );

            return response()->json([
                'status'       => 'success',
                'message'      => 'Penawaran berhasil dikonversi menjadi Sales Order ' . $salesOrder->nomor_so . '!',
                'redirect_url' => route('marketing.sales-order.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error convertToSalesOrder: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengonversi ke Sales Order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function printPdf(Request $request, SalesQuotation $quotation)
    {
        $this->authorize('cetak-penawaran');
        $this->authorizeCompanyAccess($quotation);

        $quotation->load([
            'customer.defaultAddress',
            'tender',
            'sales.department',
            'paymentTerm',
            'ppnPajak',
            'pphPajak',
            'items.barang.satuan',
            'items.satuan',
            'company',
            'branch',
            'creator',
            'approver'
        ]);

        $companies = \App\Models\Company::active()->get();
        if ($companies->isEmpty()) {
            $companies = \App\Models\Company::all();
        }

        $selectedCompanyId = $request->query('company_id', session('active_company_id') ?? $quotation->company_id ?? auth()->user()?->company_id);
        $company = $companies->firstWhere('id', $selectedCompanyId)
            ?? \App\Models\Company::find($selectedCompanyId)
            ?? $quotation->company
            ?? $companies->first()
            ?? \App\Models\Company::first();

        $appLogo = asset('gambar/logo.png');
        if (\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
            $settingLogo = \Illuminate\Support\Facades\DB::table('app_settings')->where('key', 'app_logo')->value('value');
            if ($settingLogo) {
                $appLogo = file_exists(public_path('storage/' . $settingLogo)) 
                    ? asset('storage/' . $settingLogo) 
                    : (file_exists(public_path($settingLogo)) ? asset($settingLogo) : $appLogo);
            }
        }

        $companyLogo = null;
        if ($company && $company->logo) {
            if (file_exists(public_path('storage/' . $company->logo))) {
                $companyLogo = asset('storage/' . $company->logo);
            } elseif (file_exists(public_path($company->logo))) {
                $companyLogo = asset($company->logo);
            }
        }

        $terbilang = $this->terbilang($quotation->grand_total) . ' Rupiah';

        return view('modul_marketing.penawaran.print_penawaran', compact('quotation', 'company', 'companies', 'companyLogo', 'appLogo', 'terbilang'));
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
            $terbilang = $this->terbilang((int)($angka / 1000000000)) . ' Miliar ' . $this->terbilang(fmod($angka, 1000000000));
        } elseif ($angka < 1000000000000000) {
            $terbilang = $this->terbilang((int)($angka / 1000000000000)) . ' Triliun ' . $this->terbilang(fmod($angka, 1000000000000));
        }

        return preg_replace('/\s+/', ' ', trim($terbilang));
    }

    private function parseCurrencyValue($val): float
    {
        if (is_null($val) || $val === '') {
            return 0.0;
        }
        if (!is_string($val)) {
            return is_numeric($val) ? (float)$val : 0.0;
        }
        $val = trim($val);

        // If formatted with both dots and commas, e.g. "1.234.567,89" or "1,234,567.89"
        if (str_contains($val, '.') && str_contains($val, ',')) {
            $lastDot = strrpos($val, '.');
            $lastComma = strrpos($val, ',');
            if ($lastComma > $lastDot) {
                // Indonesian: 1.234.567,89
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } else {
                // US: 1,234,567.89
                $val = str_replace(',', '', $val);
            }
            return (float)preg_replace('/[^0-9.-]/', '', $val);
        }

        // If contains comma (e.g. "50000,50")
        if (str_contains($val, ',')) {
            if (substr_count($val, ',') > 1) {
                $val = str_replace(',', '', $val);
            } else {
                $parts = explode(',', $val);
                if (strlen($parts[1]) === 3 && !str_contains($val, '.')) {
                    $val = str_replace(',', '', $val);
                } else {
                    $val = str_replace(',', '.', $val);
                }
            }
            return (float)preg_replace('/[^0-9.-]/', '', $val);
        }

        // If contains dot (e.g. "600.000" vs "50329.36")
        if (str_contains($val, '.')) {
            if (substr_count($val, '.') > 1) {
                $val = str_replace('.', '', $val);
            } else {
                $parts = explode('.', $val);
                if (strlen($parts[1]) === 3) {
                    // Indonesian thousand separator: "600.000" -> 600000
                    $val = str_replace('.', '', $val);
                } else {
                    // Standard float decimal: "50329.36" -> 50329.36
                }
            }
            return (float)preg_replace('/[^0-9.-]/', '', $val);
        }

        if (is_numeric($val)) {
            return (float)$val;
        }

        return (float)preg_replace('/[^0-9.-]/', '', $val);
    }

    private function sanitizeCurrencyInputs(Request $request): void
    {
        $rawItems = $request->input('items', []);
        if (is_array($rawItems)) {
            foreach ($rawItems as $k => $it) {
                if (isset($it['harga'])) {
                    $rawItems[$k]['harga'] = $this->parseCurrencyValue($it['harga']);
                }
                if (isset($it['diskon_nominal'])) {
                    $rawItems[$k]['diskon_nominal'] = $this->parseCurrencyValue($it['diskon_nominal']);
                }
                if (isset($it['hpp_satuan'])) {
                    $rawItems[$k]['hpp_satuan'] = $this->parseCurrencyValue($it['hpp_satuan']);
                }
                if (isset($it['qty'])) {
                    $rawItems[$k]['qty'] = $this->parseCurrencyValue($it['qty']);
                }
            }
            $request->merge(['items' => $rawItems]);
        }
        if ($request->has('diskon_nominal')) {
            $request->merge(['diskon_nominal' => $this->parseCurrencyValue($request->diskon_nominal)]);
        }
        if ($request->has('biaya_pengiriman')) {
            $request->merge(['biaya_pengiriman' => $this->parseCurrencyValue($request->biaya_pengiriman)]);
        }
        if ($request->has('dp_nominal')) {
            $request->merge(['dp_nominal' => $this->parseCurrencyValue($request->dp_nominal)]);
        }
    }

    private function resolvePhotoUrl(?string $path): string
    {
        if (empty($path)) {
            return asset('uploads/barang/placeholder_garment.png');
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'uploads/') || str_starts_with($path, '/uploads/')) {
            return asset(ltrim($path, '/'));
        }
        if (str_starts_with($path, 'storage/') || str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }
        return asset('storage/' . ltrim($path, '/'));
    }

    private function resolveBarangHppMap(array $barangIds, bool $canSeeHpp): array
    {
        if (!$canSeeHpp || empty($barangIds)) {
            return [];
        }

        $stokInfoMap = DB::table('stok_gudangs')
            ->whereIn('barang_id', $barangIds)
            ->groupBy('barang_id')
            ->selectRaw('barang_id, AVG(harga_pokok_rata_rata) as avg_hpp, AVG(harga_beli_terakhir) as last_buy_price, SUM(stok_aktual) as total_stok, SUM(nilai_persediaan) as total_nilai')
            ->get()
            ->keyBy('barang_id');

        $latestKartuMap = DB::table('kartu_stoks')
            ->whereIn('barang_id', $barangIds)
            ->whereIn('id', function ($q) use ($barangIds) {
                $q->selectRaw('MAX(id)')
                    ->from('kartu_stoks')
                    ->whereIn('barang_id', $barangIds)
                    ->groupBy('barang_id');
            })
            ->pluck('harga_satuan', 'barang_id');

        $hppMap = [];
        foreach ($barangIds as $bId) {
            $hpp = 0;
            if (isset($stokInfoMap[$bId])) {
                $stokRow = $stokInfoMap[$bId];
                if ((float)$stokRow->avg_hpp > 0) {
                    $hpp = (float)$stokRow->avg_hpp;
                } elseif ((float)$stokRow->last_buy_price > 0) {
                    $hpp = (float)$stokRow->last_buy_price;
                } elseif ((float)$stokRow->total_stok > 0 && (float)$stokRow->total_nilai > 0) {
                    $hpp = (float)$stokRow->total_nilai / (float)$stokRow->total_stok;
                }
            }

            if ($hpp <= 0 && isset($latestKartuMap[$bId]) && (float)$latestKartuMap[$bId] > 0) {
                $hpp = (float)$latestKartuMap[$bId];
            }

            $hppMap[$bId] = round($hpp, 2);
        }

        return $hppMap;
    }

    private function authorizeCompanyAccess(SalesQuotation $quotation): void
    {
        $user = Auth::user();
        if ($user->hasRole('Super Admin')) {
            return;
        }

        $activeCompanyId = session('active_company_id') ?: ($user->company_id ?? 1);
        if ($quotation->company_id != $activeCompanyId) {
            abort(403, 'Akses tidak diizinkan untuk data perusahaan ini.');
        }
    }
}
