<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\MasterBarang;
use App\Models\MasterBrand;
use App\Models\MasterCustomer;
use App\Models\MasterKategoriBarang;
use App\Models\MasterPajak;
use App\Models\PaymentMethod;
use App\Models\PaymentTerm;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-sales-order') && !$user->can('tambah-sales-order') && !$user->can('akses-sales')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka Modul Order Penjualan.');
        }

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $activeBranchId = session('active_branch_id');

        $activeCompany = Company::find($activeCompanyId);
        $activeBranch = $activeBranchId ? Branch::find($activeBranchId) : null;

        $categories = MasterKategoriBarang::where(function ($q) use ($activeCompanyId) {
                if ($activeCompanyId) {
                    $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                }
            })
            ->where('status', 'Active')
            ->where('tampil_di_sales', true)
            ->orderBy('nama_kategori', 'asc')
            ->get();

        $brands = MasterBrand::where(function ($q) use ($activeCompanyId) {
                if ($activeCompanyId) {
                    $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                }
            })
            ->where('status', 'Active')
            ->orderBy('nama_brand', 'asc')
            ->get();

        $customers = MasterCustomer::where('status', 'Active')
            ->orderBy('nama_customer', 'asc')
            ->get();

        $paymentTerms = PaymentTerm::where(function ($q) use ($activeCompanyId) {
                if ($activeCompanyId) {
                    $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                }
            })
            ->where('status', 'Active')
            ->orderBy('nama_term', 'asc')
            ->get();

        $paymentMethods = PaymentMethod::where(function ($q) use ($activeCompanyId) {
                if ($activeCompanyId) {
                    $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                }
            })
            ->where('status', 'Active')
            ->orderBy('nama_metode', 'asc')
            ->get();

        $ppnList = MasterPajak::where('status', 'Active')
            ->where(function ($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
            })
            ->where(function ($q) {
                $q->where('kode_pajak', 'like', 'PPN%')
                  ->orWhere('nama_pajak', 'like', '%PPN%');
            })
            ->orderBy('tarif', 'asc')
            ->get();

        return view('modul_marketing.order.index', compact(
            'categories',
            'brands',
            'customers',
            'paymentTerms',
            'paymentMethods',
            'ppnList',
            'activeCompany',
            'activeBranch'
        ));
    }

    /**
     * Get Products for Tokopedia E-Commerce Catalog Grid (AJAX JSON)
     */
    public function getProducts(Request $request)
    {
        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);

        $query = MasterBarang::with(['kategori', 'brand', 'satuan', 'primaryFoto', 'fotos', 'variants.warna'])
            ->whereNull('parent_id') // Root products / parents
            ->where('status', 'Active');

        // Strictly isolate by active PT / Company
        if ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId);
        }

        // Apply Category Visibility Filter (tampil_di_sales = true)
        $query->where(function ($q) {
            $q->whereNull('kategori_barang_id')
              ->orWhereHas('kategori', function ($kq) {
                  $kq->where('tampil_di_sales', true)->where('status', 'Active');
              });
        });

        // Search Filter
        if ($request->filled('search')) {
            $keyword = '%' . $request->search . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_barang', 'LIKE', $keyword)
                  ->orWhere('kode_barang', 'LIKE', $keyword)
                  ->orWhere('barcode', 'LIKE', $keyword)
                  ->orWhere('nama_alias', 'LIKE', $keyword);
            });
        }

        // Category Filter
        if ($request->filled('kategori_id') && $request->kategori_id !== 'all') {
            $query->where('kategori_barang_id', $request->kategori_id);
        }

        // Brand Filter
        if ($request->filled('brand_id') && $request->brand_id !== 'all') {
            $query->where('brand_id', $request->brand_id);
        }

        // Item Type Filter (Stok, Bundle, Aset, Jasa)
        if ($request->filled('tipe_item') && $request->tipe_item !== 'all') {
            $query->where('tipe_item', $request->tipe_item);
        }

        $perPage = $request->input('per_page', 24);
        $products = $query->orderBy('id', 'desc')->paginate($perPage);

        // Fetch Live Stock strictly for the active company's warehouses
        $barangIds = $products->pluck('id')->toArray();
        $stockMap = DB::table('stok_gudangs')
            ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
            ->whereIn('stok_gudangs.barang_id', $barangIds)
            ->when($activeCompanyId, function ($q) use ($activeCompanyId) {
                $q->where('master_gudang.company_id', $activeCompanyId);
            })
            ->groupBy('stok_gudangs.barang_id')
            ->selectRaw('stok_gudangs.barang_id, SUM(stok_gudangs.stok_aktual) as total_stok')
            ->pluck('total_stok', 'stok_gudangs.barang_id')
            ->toArray();

        $items = $products->getCollection()->map(function ($item) use ($stockMap) {
            $totalStok = isset($stockMap[$item->id]) ? (float)$stockMap[$item->id] : 0;
            
            // Format photo url
            $photoUrl = null;
            if ($item->primaryFoto) {
                $p = $item->primaryFoto->foto_path;
                $photoUrl = str_starts_with($p, 'uploads/') ? asset($p) : asset('storage/' . $p);
            } elseif ($item->fotos && $item->fotos->count() > 0) {
                $p = $item->fotos->first()->foto_path;
                $photoUrl = str_starts_with($p, 'uploads/') ? asset($p) : asset('storage/' . $p);
            }

            return [
                'id' => $item->id,
                'kode_barang' => $item->kode_barang,
                'barcode' => $item->barcode,
                'nama_barang' => $item->nama_barang,
                'tipe_item' => $item->tipe_item,
                'kategori_name' => $item->kategori ? $item->kategori->nama_kategori : '-',
                'brand_name' => $item->brand ? $item->brand->nama_brand : '-',
                'satuan_name' => $item->satuan ? $item->satuan->nama_satuan : 'Pcs',
                'ukuran' => $item->ukuran ?: '-',
                'photo_url' => $photoUrl,
                'total_stok' => $totalStok,
                'harga_retail' => (float)$item->harga_jual_retail,
                'harga_grosir' => (float)$item->harga_jual_grosir,
                'harga_tender' => (float)$item->harga_jual_tender,
                'has_variants' => $item->variants && $item->variants->count() > 0,
                'variants_count' => $item->variants ? $item->variants->count() : 0,
            ];
        });

        return response()->json([
            'data' => $items,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total(),
            'per_page' => $products->perPage(),
        ]);
    }

    /**
     * Get Complete Product Details for E-Commerce Product Detail Modal
     */
    public function getProductDetail(MasterBarang $barang)
    {
        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);

        $barang->load([
            'kategori',
            'brand',
            'satuan',
            'warna',
            'fotos' => function ($q) {
                $q->orderBy('is_primary', 'desc')->orderBy('urutan', 'asc');
            },
            'variants.warna',
            'bundleComponents.childBarang.satuan',
            'bundleComponents.childBarang.primaryFoto'
        ]);

        // Live Stock in active company's warehouses
        $stokGudang = DB::table('stok_gudangs')
            ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
            ->where('stok_gudangs.barang_id', $barang->id)
            ->when($activeCompanyId, function ($q) use ($activeCompanyId) {
                $q->where('master_gudang.company_id', $activeCompanyId);
            })
            ->sum('stok_gudangs.stok_aktual');

        $photos = $barang->fotos->map(function ($f) {
            $path = $f->foto_path;
            $url = str_starts_with($path, 'uploads/') ? asset($path) : asset('storage/' . $path);
            return [
                'id' => $f->id,
                'url' => $url,
                'is_primary' => (bool)$f->is_primary,
            ];
        });

        // If bundle, map components
        $bundleComponents = collect();
        if ($barang->tipe_item === 'Bundle' && $barang->bundleComponents) {
            $bundleComponents = $barang->bundleComponents->map(function ($bc) {
                $child = $bc->childBarang;
                $pUrl = null;
                if ($child && $child->primaryFoto) {
                    $p = $child->primaryFoto->foto_path;
                    $pUrl = str_starts_with($p, 'uploads/') ? asset($p) : asset('storage/' . $p);
                }
                return [
                    'id' => $child ? $child->id : null,
                    'kode_barang' => $child ? $child->kode_barang : '-',
                    'nama_barang' => $child ? $child->nama_barang : '-',
                    'qty' => (float)$bc->qty,
                    'satuan' => $child && $child->satuan ? $child->satuan->nama_satuan : 'Pcs',
                    'photo_url' => $pUrl,
                ];
            });
        }

        return response()->json([
            'product' => [
                'id' => $barang->id,
                'kode_barang' => $barang->kode_barang,
                'barcode' => $barang->barcode,
                'nama_barang' => $barang->nama_barang,
                'nama_alias' => $barang->nama_alias,
                'tipe_item' => $barang->tipe_item,
                'kategori_name' => $barang->kategori ? $barang->kategori->nama_kategori : '-',
                'brand_name' => $barang->brand ? $barang->brand->nama_brand : '-',
                'satuan_name' => $barang->satuan ? $barang->satuan->nama_satuan : 'Pcs',
                'warna_name' => $barang->warna ? $barang->warna->nama_warna : '-',
                'warna_hex' => $barang->warna ? $barang->warna->kode_hex : null,
                'ukuran' => $barang->ukuran ?: '-',
                'panjang' => (float)$barang->panjang,
                'lebar' => (float)$barang->lebar,
                'tinggi' => (float)$barang->tinggi,
                'berat' => (float)$barang->berat,
                'spesifikasi' => $barang->spesifikasi ?: 'Tidak ada spesifikasi khusus.',
                'total_stok' => (float)$stokGudang,
                'harga_retail' => (float)$barang->harga_jual_retail,
                'harga_grosir' => (float)$barang->harga_jual_grosir,
                'harga_tender' => (float)$barang->harga_jual_tender,
                'photos' => $photos,
                'bundle_components' => $bundleComponents,
                'variants' => $barang->variants,
            ]
        ]);
    }

    /**
     * Store Sales Order (Checkout)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-sales-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk membuat pesanan (tambah-sales-order).'], 403);
        }

        $activeCompanyId = session('active_company_id');
        $activeBranchId = session('active_branch_id');

        if (!$activeCompanyId) {
            $userCompany = DB::table('user_companies')->where('user_id', Auth::id())->first();
            $activeCompanyId = $userCompany ? $userCompany->company_id : Company::first()->id;
        }

        $request->validate([
            'pricing_tier' => 'required|in:Retail,Grosir,Tender,Custom',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:master_barang,id',
            'items.*.qty' => 'required|numeric|min:0.0001',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Validasi Ketersediaan Stok Fisik di Gudang Aktif
            $barangIds = collect($request->items)->pluck('barang_id')->toArray();
            $liveStocks = DB::table('stok_gudangs')
                ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
                ->whereIn('stok_gudangs.barang_id', $barangIds)
                ->when($activeCompanyId, function ($q) use ($activeCompanyId) {
                    $q->where('master_gudang.company_id', $activeCompanyId);
                })
                ->groupBy('stok_gudangs.barang_id')
                ->selectRaw('stok_gudangs.barang_id, SUM(stok_gudangs.stok_aktual) as total_stok')
                ->pluck('total_stok', 'stok_gudangs.barang_id')
                ->toArray();

            foreach ($request->items as $item) {
                $barang = MasterBarang::find($item['barang_id']);
                if ($barang && $barang->tipe_item !== 'Jasa') {
                    $available = isset($liveStocks[$barang->id]) ? (float)$liveStocks[$barang->id] : 0;
                    $satuanNama = $barang->satuan ? $barang->satuan->nama_satuan : 'Unit';
                    if ($available <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => "Gagal memproses pesanan: Persediaan untuk produk '{$barang->nama_barang}' ({$barang->kode_barang}) saat ini tidak tersedia di gudang."
                        ], 422);
                    }
                    if ($item['qty'] > $available) {
                        return response()->json([
                            'success' => false,
                            'message' => "Gagal memproses pesanan: Kuantitas pesanan untuk '{$barang->nama_barang}' ({$item['qty']} {$satuanNama}) melebihi sisa persediaan yang tersedia ({$available} {$satuanNama})."
                        ], 422);
                    }
                }
            }

            $nomorSo = SalesOrder::generateNomorSo($activeCompanyId);

            $order = SalesOrder::create([
                'company_id' => $activeCompanyId,
                'branch_id' => $activeBranchId,
                'nomor_so' => $nomorSo,
                'tanggal_so' => $request->input('tanggal_so', date('Y-m-d')),
                'customer_id' => $request->customer_id ?: null,
                'nama_customer_walkin' => $request->customer_id ? null : ($request->nama_customer_walkin ?: 'Pelanggan Umum'),
                'telepon_customer_walkin' => $request->telepon_customer_walkin,
                'pricing_tier' => $request->pricing_tier,
                'payment_term_id' => $request->payment_term_id ?: null,
                'payment_method_id' => $request->payment_method_id ?: null,
                'status' => $request->input('status', 'Confirmed'),
                'subtotal' => $request->subtotal,
                'diskon_tipe' => $request->input('diskon_tipe', 'Nominal'),
                'diskon_nilai' => $request->input('diskon_nilai', 0),
                'diskon_nominal' => $request->input('diskon_nominal', 0),
                'pajak_tipe' => $request->input('pajak_tipe', 'None'),
                'pajak_persen' => $request->input('pajak_persen', 0),
                'pajak_nominal' => $request->input('pajak_nominal', 0),
                'biaya_pengiriman' => $request->input('biaya_pengiriman', 0),
                'total' => $request->total,
                'grand_total' => $request->input('grand_total', $request->total),
                'tipe_dp' => $request->input('tipe_dp'),
                'dp_persen' => $request->input('dp_persen', 0),
                'dp_nominal' => $request->input('dp_nominal', 0),
                'sisa_tagihan' => $request->input('sisa_tagihan', $request->total),
                'catatan' => $request->catatan,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $barang = MasterBarang::find($item['barang_id']);
                SalesOrderDetail::create([
                    'sales_order_id' => $order->id,
                    'barang_id' => $item['barang_id'],
                    'qty' => $item['qty'],
                    'satuan_id' => $barang ? $barang->satuan_id : null,
                    'pricing_tier' => $request->pricing_tier,
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon_persen' => $item['diskon_persen'] ?? 0,
                    'diskon_nominal' => $item['diskon_nominal'] ?? 0,
                    'subtotal' => $item['subtotal'],
                    'catatan' => $item['catatan'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pesanan {$order->nomor_so} berhasil dibuat!",
                'order' => $order->load(['customer', 'paymentTerm', 'paymentMethod', 'details.barang.satuan']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Order History (DataTables JSON)
     */
    public function getOrders(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-sales-order') && !$user->can('akses-sales')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $activeCompanyId = session('active_company_id');

        $query = SalesOrder::with(['customer', 'paymentTerm', 'paymentMethod', 'creator', 'details.barang'])
            ->where(function ($q) use ($activeCompanyId) {
                if ($activeCompanyId) {
                    $q->where('company_id', $activeCompanyId);
                }
            });

        if ($request->filled('status_filter') && $request->status_filter !== 'all') {
            $query->where('status', $request->status_filter);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('customer_name', function ($row) {
                $nama = $row->customer ? e($row->customer->nama_customer) : e($row->nama_customer_walkin ?: 'Pelanggan Umum');
                $pt = $row->customer && $row->customer->nama_perusahaan 
                    ? '<div class="small text-primary fw-semibold mt-0.5"><i class="bi bi-building me-1 opacity-75"></i>' . e($row->customer->nama_perusahaan) . '</div>' 
                    : '';
                return "<div><span class='fw-bold'>{$nama}</span>{$pt}</div>";
            })
            ->addColumn('tier_badge', function ($row) {
                $map = [
                    'Retail'  => 'bg-info-subtle text-info border border-info-subtle',
                    'Grosir'  => 'bg-primary-subtle text-primary border border-primary-subtle',
                    'Tender'  => 'bg-warning-subtle text-warning border border-warning-subtle',
                    'Custom'  => 'bg-purple-subtle text-purple border border-purple-subtle',
                ];
                $tier = $row->pricing_tier ?: 'Retail';
                $cls  = $map[$tier] ?? 'bg-secondary-subtle text-secondary';
                return "<span class='badge {$cls} px-2.5 py-1 rounded-pill fw-semibold font-monospace'>{$tier}</span>";
            })
            ->addColumn('status_badge', function ($row) {
                $statusMap = [
                    'Draft'       => ['bg-secondary-subtle text-secondary border border-secondary-subtle', 'Draft', 'bi-file-earmark'],
                    'Confirmed'   => ['bg-primary-subtle text-primary border border-primary-subtle', 'Terkonfirmasi', 'bi-check2-circle'],
                    'Processing'  => ['bg-warning-subtle text-warning border border-warning-subtle', 'Diproses', 'bi-arrow-repeat'],
                    'Delivered'   => ['bg-info-subtle text-info border border-info-subtle', 'Dikirim', 'bi-truck'],
                    'Completed'   => ['bg-success-subtle text-success border border-success-subtle', 'Selesai', 'bi-patch-check-fill'],
                    'Cancelled'   => ['bg-danger-subtle text-danger border border-danger-subtle', 'Dibatalkan', 'bi-x-circle-fill'],
                ];
                $info = $statusMap[$row->status] ?? ['bg-secondary-subtle text-secondary', $row->status, 'bi-dot'];
                return "<span class='badge {$info[0]} px-2.5 py-1 rounded-pill fw-semibold d-inline-flex align-items-center gap-1'><i class='bi {$info[2]}'></i> {$info[1]}</span>";
            })
            ->addColumn('items_count', function ($row) {
                $count = $row->details->count();
                return "<span class='badge bg-body-tertiary text-body border px-2.5 py-1 rounded-pill font-monospace'>{$count} item</span>";
            })
            ->addColumn('formatted_total', function ($row) {
                $grandTotal = (float)($row->grand_total ?? 0);
                $total      = (float)($row->total ?? 0);
                $effectiveTotal = $grandTotal > 0 ? $grandTotal : $total;
                return '<span class="fw-bold font-monospace text-primary">Rp ' . number_format($effectiveTotal, 0, ',', '.') . '</span>';
            })
            ->addColumn('ppn_info', function ($row) {
                $nilaiPpn = (float)($row->nilai_ppn ?? 0);
                $pajakNominal = (float)($row->pajak_nominal ?? 0);
                $ppn = $nilaiPpn > 0 ? $nilaiPpn : $pajakNominal;
                return $ppn > 0 ? 'Rp ' . number_format($ppn, 0, ',', '.') : '-';
            })
            ->rawColumns(['customer_name', 'tier_badge', 'status_badge', 'items_count', 'formatted_total'])
            ->make(true);
    }

    public function getOrdersData(Request $request)
    {
        return $this->getOrders($request);
    }

    /**
     * Show Order Detail (JSON for Invoice Modal)
     */
    public function show(SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-sales-order') && !$user->can('akses-sales')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $order->load([
            'company',
            'branch',
            'customer',
            'paymentTerm',
            'paymentMethod',
            'creator',
            'details.barang.satuan',
            'details.barang.primaryFoto',
        ]);

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Update Sales Order (Edit Header + Items)
     */
    public function update(Request $request, SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-sales-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk mengubah pesanan ini.'], 403);
        }

        if (in_array($order->status, ['Completed', 'Cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Pesanan dengan status "' . $order->status . '" tidak dapat diedit.'], 422);
        }

        $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.detail_id'      => 'required|exists:sales_order_details,id',
            'items.*.qty'            => 'required|numeric|min:0.0001',
            'items.*.harga_satuan'   => 'required|numeric|min:0',
            'items.*.diskon_nominal' => 'nullable|numeric|min:0',
            'items.*.subtotal'       => 'required|numeric|min:0',
            'subtotal'               => 'required|numeric|min:0',
            'diskon_nominal'         => 'nullable|numeric|min:0',
            'pajak_nominal'          => 'nullable|numeric|min:0',
            'total'                  => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Validasi Ketersediaan Stok Fisik untuk Item yang Diupdate
            $detailIds = collect($request->items)->pluck('detail_id')->toArray();
            $existingDetails = SalesOrderDetail::whereIn('id', $detailIds)
                ->where('sales_order_id', $order->id)
                ->get()
                ->keyBy('id');

            $barangIds = $existingDetails->pluck('barang_id')->unique()->toArray();
            $activeCompanyId = $order->company_id ?: session('active_company_id');

            $liveStocks = DB::table('stok_gudangs')
                ->join('master_gudang', 'stok_gudangs.gudang_id', '=', 'master_gudang.id')
                ->whereIn('stok_gudangs.barang_id', $barangIds)
                ->when($activeCompanyId, function ($q) use ($activeCompanyId) {
                    $q->where('master_gudang.company_id', $activeCompanyId);
                })
                ->groupBy('stok_gudangs.barang_id')
                ->selectRaw('stok_gudangs.barang_id, SUM(stok_gudangs.stok_aktual) as total_stok')
                ->pluck('total_stok', 'stok_gudangs.barang_id')
                ->toArray();

            foreach ($request->items as $item) {
                $detail = $existingDetails->get($item['detail_id']);
                if ($detail) {
                    $barang = MasterBarang::find($detail->barang_id);
                    if ($barang && $barang->tipe_item !== 'Jasa') {
                        $available = isset($liveStocks[$barang->id]) ? (float)$liveStocks[$barang->id] : 0;
                        $satuanNama = $barang->satuan ? $barang->satuan->nama_satuan : 'Unit';

                        if ($available <= 0) {
                            return response()->json([
                                'success' => false,
                                'message' => "Gagal memperbarui pesanan: Persediaan untuk produk '{$barang->nama_barang}' ({$barang->kode_barang}) saat ini tidak tersedia di gudang."
                            ], 422);
                        }

                        if ($item['qty'] > $available) {
                            return response()->json([
                                'success' => false,
                                'message' => "Gagal memperbarui pesanan: Kuantitas pesanan untuk '{$barang->nama_barang}' ({$item['qty']} {$satuanNama}) melebihi sisa persediaan yang tersedia ({$available} {$satuanNama})."
                            ], 422);
                        }
                    }
                }
            }

            $order->update([
                'customer_id'             => $request->customer_id ?: null,
                'nama_customer_walkin'    => $request->customer_id ? null : ($request->nama_customer_walkin ?: $order->nama_customer_walkin),
                'telepon_customer_walkin' => $request->telepon_customer_walkin ?? $order->telepon_customer_walkin,
                'payment_method_id'       => $request->payment_method_id ?: null,
                'payment_term_id'         => $request->payment_term_id ?: null,
                'catatan'                 => $request->catatan,
                'alamat_pengiriman'       => $request->alamat_pengiriman,
                'subtotal'                => $request->subtotal,
                'diskon_tipe'             => $request->input('diskon_tipe', $order->diskon_tipe),
                'diskon_nilai'            => $request->input('diskon_nilai', 0),
                'diskon_nominal'          => $request->input('diskon_nominal', 0),
                'pajak_tipe'              => $request->input('pajak_tipe', $order->pajak_tipe),
                'pajak_persen'            => $request->input('pajak_persen', 0),
                'pajak_nominal'           => $request->input('pajak_nominal', 0),
                'biaya_pengiriman'        => $request->input('biaya_pengiriman', $order->biaya_pengiriman),
                'total'                   => $request->total,
                'grand_total'             => $request->input('grand_total', $request->total),
                'updated_by'              => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $detail = SalesOrderDetail::where('id', $item['detail_id'])
                    ->where('sales_order_id', $order->id)
                    ->first();
                if ($detail) {
                    $detail->update([
                        'qty'            => $item['qty'],
                        'harga_satuan'   => $item['harga_satuan'],
                        'diskon_nominal' => $item['diskon_nominal'] ?? 0,
                        'subtotal'       => $item['subtotal'],
                        'catatan'        => $item['catatan'] ?? $detail->catatan,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pesanan {$order->nomor_so} berhasil diperbarui.",
                'order'   => $order->fresh()->load(['customer', 'paymentTerm', 'paymentMethod', 'details.barang.satuan']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Order Status
     */
    public function updateStatus(Request $request, SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-sales-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk mengubah status pesanan (edit-sales-order).'], 403);
        }

        $request->validate([
            'status' => 'required|in:Draft,Confirmed,Processing,Delivered,Completed,Cancelled',
        ]);

        $order->update([
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Status pesanan {$order->nomor_so} diubah menjadi {$order->status}.",
            'order' => $order,
        ]);
    }

    /**
     * Cancel Order (with reason)
     */
    public function cancelOrder(Request $request, SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-sales-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk membatalkan pesanan.'], 403);
        }

        if ($order->status === 'Completed') {
            return response()->json(['success' => false, 'message' => 'Pesanan yang sudah selesai tidak dapat dibatalkan.'], 422);
        }

        if ($order->status === 'Cancelled') {
            return response()->json(['success' => false, 'message' => 'Pesanan ini sudah berstatus dibatalkan.'], 422);
        }

        $order->update([
            'status'            => 'Cancelled',
            'alasan_pembatalan' => $request->input('alasan_pembatalan'),
            'updated_by'        => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pesanan {$order->nomor_so} berhasil dibatalkan.",
        ]);
    }

    public function printInvoicePdf(Request $request, SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-sales-order') && !$user->can('akses-sales')) {
            abort(403, 'Akses ditolak.');
        }

        $order->load([
            'company',
            'branch',
            'customer',
            'paymentTerm',
            'paymentMethod',
            'creator',
            'details.barang.satuan',
        ]);

        $companies = \App\Models\Company::active()->get();
        if ($companies->isEmpty()) {
            $companies = \App\Models\Company::all();
        }

        $selectedCompanyId = $request->query('company_id', session('active_company_id') ?? $order->company_id ?? auth()->user()?->company_id);
        $company = $companies->firstWhere('id', $selectedCompanyId)
            ?? \App\Models\Company::find($selectedCompanyId)
            ?? $order->company
            ?? $companies->first()
            ?? \App\Models\Company::first();

        $branch = $order->branch ?? auth()->user()?->branch;

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
        } elseif ($branch && $branch->logo) {
            if (file_exists(public_path('storage/' . $branch->logo))) {
                $companyLogo = asset('storage/' . $branch->logo);
            } elseif (file_exists(public_path($branch->logo))) {
                $companyLogo = asset($branch->logo);
            }
        }

        if (!$companyLogo) {
            $companyLogo = $appLogo;
        }

        return view('modul_marketing.order.print_faktur', compact('order', 'company', 'branch', 'companies', 'companyLogo', 'appLogo'));
    }

    /**
     * Print Thermal Receipt
     */
    public function printThermalReceipt(SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-sales-order') && !$user->can('akses-sales')) {
            abort(403, 'Akses ditolak.');
        }

        $order->load([
            'company',
            'branch',
            'customer',
            'paymentTerm',
            'paymentMethod',
            'creator',
            'details.barang.satuan',
        ]);

        return response()->json([
            'order' => $order,
        ]);
    }
}
