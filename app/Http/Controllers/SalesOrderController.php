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
use App\Models\SalesQuotation;
use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalesOrderController extends Controller
{
    /**
     * Resolve active warehouse IDs strictly according to the active entity
     * - Sesi Cabang Aktif: Ambil seluruh gudang aktif milik cabang ini (cabang_id = active_branch_id)
     * - Sesi PT Pusat Aktif: Ambil seluruh gudang aktif level PT (cabang_id IS NULL and company_id = active_company_id)
     */
    private function resolveActiveGudangIds($companyId = null, $branchId = null): array
    {
        $branchId = $branchId ?: session('active_branch_id');
        $companyId = $companyId ?: (session('active_company_id') ?: (Auth::user()?->company_id ?? 1));

        $query = \App\Models\MasterGudang::where('status', 'Active');

        if (!empty($branchId)) {
            $query->where('cabang_id', $branchId);
        } else {
            $query->whereNull('cabang_id');
            if (!empty($companyId)) {
                $query->where('company_id', $companyId);
            }
        }

        return $query->pluck('id')->toArray();
    }

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

        $customers = MasterCustomer::forActiveCompany()
            ->with(['defaultAddress', 'addresses'])
            ->where('status', 'Active')
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

        $masterPajaks = MasterPajak::where('status', 'Active')
            ->where(function ($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
            })
            ->orderBy('tarif', 'asc')
            ->get();

        $ppnList = $masterPajaks->filter(function ($p) {
            return stripos($p->nama_pajak, 'PPN') !== false || stripos($p->kode_pajak, 'PPN') !== false || stripos($p->kode_pajak, 'TX001') !== false || stripos($p->kode_pajak, 'TX002') !== false || stripos($p->kode_pajak, 'TX004') !== false;
        })->values();

        $pphList = $masterPajaks->filter(function ($p) {
            return stripos($p->nama_pajak, 'PPh') !== false || stripos($p->kode_pajak, 'PPh') !== false || stripos($p->kode_pajak, 'TX003') !== false;
        })->values();

        $salesList = \App\Models\Karyawan::where('status', 'Aktif')
            ->where(function ($q) use ($activeCompanyId) {
                if ($activeCompanyId) {
                    $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                }
            })
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        if ($salesList->isEmpty()) {
            $salesList = \App\Models\Karyawan::where('status', 'Aktif')->orderBy('nama_lengkap', 'asc')->get();
        }

        // Identify active warehouses for initial server-side rendering (Cabang vs PT Pusat)
        $activeGudangs = $this->resolveActiveGudangIds($activeCompanyId, $activeBranchId);

        $barangQuery = \App\Models\MasterBarang::where('status', 'Active')
            ->whereNull('parent_id')
            ->where(function ($q) use ($activeCompanyId) {
                if ($activeCompanyId) {
                    $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                }
            })
            ->where(function ($q) {
                $q->whereNull('kategori_barang_id')
                  ->orWhereHas('kategori', function ($kq) {
                      $kq->where('tampil_di_sales', true)->where('status', 'Active');
                  });
            });

        // Strict Entity Isolation (PT vs Cabang): Only show products with stock in active entity's warehouses, Jasa, or Bundle
        if (!empty($activeGudangs)) {
            $barangQuery->where(function ($q) use ($activeGudangs) {
                $q->whereIn('tipe_item', ['Jasa', 'Bundle'])
                  ->orWhereHas('stokGudangs', function ($sq) use ($activeGudangs) {
                      $sq->whereIn('gudang_id', $activeGudangs)
                         ->where('stok_aktual', '>', 0);
                  })
                  ->orWhereHas('variants.stokGudangs', function ($vsq) use ($activeGudangs) {
                      $vsq->whereIn('gudang_id', $activeGudangs)
                          ->where('stok_aktual', '>', 0);
                  });
            });
        }

        $barangList = $barangQuery->with(['satuan', 'kategori', 'primaryFoto', 'fotos', 'variants', 'bundleComponents'])
            ->orderBy('id', 'desc')
            ->get();

        $barangIds = $barangList->pluck('id')->toArray();
        foreach ($barangList as $p) {
            if ($p->variants && $p->variants->count() > 0) {
                foreach ($p->variants as $v) {
                    $barangIds[] = $v->id;
                }
            }
            if ($p->tipe_item === 'Bundle' && $p->bundleComponents && $p->bundleComponents->count() > 0) {
                foreach ($p->bundleComponents as $bc) {
                    $barangIds[] = $bc->child_barang_id;
                }
            }
        }
        $barangIds = array_unique($barangIds);

        $stockMap = [];
        if (!empty($barangIds) && !empty($activeGudangs)) {
            $stockMap = DB::table('stok_gudangs')
                ->whereIn('barang_id', $barangIds)
                ->whereIn('gudang_id', $activeGudangs)
                ->groupBy('barang_id')
                ->selectRaw('barang_id, SUM(stok_aktual) as total_stok')
                ->pluck('total_stok', 'barang_id')
                ->toArray();
        }

        $barangList->transform(function ($b) use ($stockMap) {
            $pPath = $b->primaryFoto ? $b->primaryFoto->foto_path : ($b->fotos && $b->fotos->count() > 0 ? $b->fotos->first()->foto_path : null);
            $b->resolved_photo_url = $pPath 
                ? (str_starts_with($pPath, 'http') ? $pPath : (str_starts_with($pPath, 'storage/') ? asset($pPath) : asset('storage/' . ltrim($pPath, '/')))) 
                : null;
            $b->harga_retail = (float)($b->harga_retail ?? $b->harga_jual_retail ?? 0);
            $b->harga_grosir = (float)($b->harga_grosir ?? $b->harga_jual_grosir ?? 0);
            $b->harga_tender = (float)($b->harga_tender ?? $b->harga_jual_tender ?? 0);

            if ($b->tipe_item === 'Bundle') {
                if ($b->bundleComponents && $b->bundleComponents->count() > 0) {
                    $minPossible = null;
                    foreach ($b->bundleComponents as $bc) {
                        $childStock = isset($stockMap[$bc->child_barang_id]) ? (float)$stockMap[$bc->child_barang_id] : 0;
                        $qtyReq = (float)($bc->qty ?? 1);
                        $possible = ($qtyReq > 0) ? floor($childStock / $qtyReq) : 0;
                        if ($minPossible === null || $possible < $minPossible) {
                            $minPossible = $possible;
                        }
                    }
                    $stok = $minPossible !== null ? (float)$minPossible : 0;
                } else {
                    $stok = isset($stockMap[$b->id]) ? (float)$stockMap[$b->id] : 0;
                }
            } else {
                $stok = isset($stockMap[$b->id]) ? (float)$stockMap[$b->id] : 0;
                if ($b->variants && $b->variants->count() > 0) {
                    $vSum = 0;
                    foreach ($b->variants as $v) {
                        $vSum += (isset($stockMap[$v->id]) ? (float)$stockMap[$v->id] : 0);
                    }
                    if ($vSum > 0) $stok = $vSum;
                }
            }
            $b->total_stok = $stok;
            return $b;
        });

        return view('modul_marketing.order.index', compact(
            'categories',
            'brands',
            'customers',
            'paymentTerms',
            'paymentMethods',
            'ppnList',
            'pphList',
            'salesList',
            'barangList',
            'activeCompany',
            'activeBranch'
        ));
    }

    /**
     * Get Products for Tokopedia E-Commerce Catalog Grid (AJAX JSON)
     */
    public function getProducts(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-sales-order') && !$user->can('tambah-sales-order') && !$user->can('akses-sales')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $activeBranchId  = session('active_branch_id');

        // Identify active warehouses for this entity (Cabang vs PT Pusat)
        $activeGudangs = $this->resolveActiveGudangIds($activeCompanyId, $activeBranchId);

        $query = MasterBarang::with(['kategori', 'brand', 'satuan', 'primaryFoto', 'fotos', 'variants.warna', 'bundleComponents'])
            ->whereNull('parent_id') // Root products / parents
            ->where('status', 'Active')
            ->where(function ($q) use ($activeCompanyId) {
                if ($activeCompanyId) {
                    $q->where('company_id', $activeCompanyId)->orWhereNull('company_id');
                }
            });

        // Strict Entity Isolation (PT vs Cabang): Only show products with stock in active entity's warehouses, Jasa, or Bundle
        if (!empty($activeGudangs)) {
            $query->where(function ($q) use ($activeGudangs) {
                $q->whereIn('tipe_item', ['Jasa', 'Bundle'])
                  ->orWhereHas('stokGudangs', function ($sq) use ($activeGudangs) {
                      $sq->whereIn('gudang_id', $activeGudangs)
                         ->where('stok_aktual', '>', 0);
                  })
                  ->orWhereHas('variants.stokGudangs', function ($vsq) use ($activeGudangs) {
                      $vsq->whereIn('gudang_id', $activeGudangs)
                          ->where('stok_aktual', '>', 0);
                  });
            });
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

        // Fetch Live Stock strictly for the active entity's warehouses
        $barangIds = $products->pluck('id')->toArray();
        foreach ($products->getCollection() as $p) {
            if ($p->variants && $p->variants->count() > 0) {
                foreach ($p->variants as $v) {
                    $barangIds[] = $v->id;
                }
            }
            if ($p->tipe_item === 'Bundle' && $p->bundleComponents && $p->bundleComponents->count() > 0) {
                foreach ($p->bundleComponents as $bc) {
                    $barangIds[] = $bc->child_barang_id;
                }
            }
        }
        $barangIds = array_unique($barangIds);

        $stockMap = [];
        if (!empty($barangIds) && !empty($activeGudangs)) {
            $stockMap = DB::table('stok_gudangs')
                ->whereIn('barang_id', $barangIds)
                ->whereIn('gudang_id', $activeGudangs)
                ->groupBy('barang_id')
                ->selectRaw('barang_id, SUM(stok_aktual) as total_stok')
                ->pluck('total_stok', 'barang_id')
                ->toArray();
        }

        $items = $products->getCollection()->map(function ($item) use ($stockMap) {
            if ($item->tipe_item === 'Bundle') {
                if ($item->bundleComponents && $item->bundleComponents->count() > 0) {
                    $minPossible = null;
                    foreach ($item->bundleComponents as $bc) {
                        $childStock = isset($stockMap[$bc->child_barang_id]) ? (float)$stockMap[$bc->child_barang_id] : 0;
                        $qtyReq = (float)($bc->qty ?? 1);
                        $possible = ($qtyReq > 0) ? floor($childStock / $qtyReq) : 0;
                        if ($minPossible === null || $possible < $minPossible) {
                            $minPossible = $possible;
                        }
                    }
                    $totalStok = $minPossible !== null ? (float)$minPossible : 0;
                } else {
                    $totalStok = isset($stockMap[$item->id]) ? (float)$stockMap[$item->id] : 0;
                }
            } else {
                $totalStok = isset($stockMap[$item->id]) ? (float)$stockMap[$item->id] : 0;
                if ($item->variants && $item->variants->count() > 0) {
                    $variantStokSum = 0;
                    foreach ($item->variants as $v) {
                        $variantStokSum += (isset($stockMap[$v->id]) ? (float)$stockMap[$v->id] : 0);
                    }
                    if ($variantStokSum > 0) {
                        $totalStok = $variantStokSum;
                    }
                }
            }
            
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
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-sales-order') && !$user->can('tambah-sales-order') && !$user->can('akses-sales')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $activeBranchId  = session('active_branch_id');

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

        // Live Stock strictly in active entity's warehouses
        $activeGudangs = $this->resolveActiveGudangIds($activeCompanyId, $activeBranchId);

        $stokGudang = 0;
        $componentStockMap = [];
        if (!empty($activeGudangs)) {
            if ($barang->tipe_item === 'Bundle') {
                $childIds = $barang->bundleComponents ? $barang->bundleComponents->pluck('child_barang_id')->filter()->toArray() : [];
                if (!empty($childIds)) {
                    $componentStockMap = DB::table('stok_gudangs')
                        ->whereIn('barang_id', $childIds)
                        ->whereIn('gudang_id', $activeGudangs)
                        ->groupBy('barang_id')
                        ->selectRaw('barang_id, SUM(stok_aktual) as total_stok')
                        ->pluck('total_stok', 'barang_id')
                        ->toArray();

                    $minPossible = null;
                    foreach ($barang->bundleComponents as $bc) {
                        $childStock = isset($componentStockMap[$bc->child_barang_id]) ? (float)$componentStockMap[$bc->child_barang_id] : 0;
                        $qtyReq = (float)($bc->qty ?? 1);
                        $possible = ($qtyReq > 0) ? floor($childStock / $qtyReq) : 0;
                        if ($minPossible === null || $possible < $minPossible) {
                            $minPossible = $possible;
                        }
                    }
                    $stokGudang = $minPossible !== null ? (float)$minPossible : 0;
                }
            } else {
                $stokGudang = DB::table('stok_gudangs')
                    ->where('barang_id', $barang->id)
                    ->whereIn('gudang_id', $activeGudangs)
                    ->sum('stok_aktual');
            }
        }

        $photos = $barang->fotos->map(function ($f) {
            $path = $f->foto_path;
            $url = str_starts_with($path, 'uploads/') ? asset($path) : asset('storage/' . $path);
            return [
                'id' => $f->id,
                'url' => $url,
                'is_primary' => (bool)$f->is_primary,
            ];
        });

        // If bundle, map components with live available stock in active entity's warehouses
        $bundleComponents = collect();
        if ($barang->tipe_item === 'Bundle' && $barang->bundleComponents) {
            $bundleComponents = $barang->bundleComponents->map(function ($bc) use ($componentStockMap) {
                $child = $bc->childBarang;
                $pUrl = null;
                if ($child && $child->primaryFoto) {
                    $p = $child->primaryFoto->foto_path;
                    $pUrl = str_starts_with($p, 'uploads/') ? asset($p) : asset('storage/' . $p);
                }
                $cStock = isset($componentStockMap[$bc->child_barang_id]) ? (float)$componentStockMap[$bc->child_barang_id] : 0;
                $neededQty = (float)($bc->qty ?? 1);
                return [
                    'id' => $child ? $child->id : null,
                    'kode_barang' => $child ? $child->kode_barang : '-',
                    'nama_barang' => $child ? $child->nama_barang : '-',
                    'qty' => $neededQty,
                    'satuan' => $child && $child->satuan ? $child->satuan->nama_satuan : 'Pcs',
                    'stok_tersedia' => $cStock,
                    'stok_cukup' => $cStock >= $neededQty,
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
            'pricing_tier'           => 'required|in:Retail,Grosir,Tender,Custom',
            'items'                  => 'required|array|min:1',
            'items.*.barang_id'      => 'required|exists:master_barang,id',
            'items.*.qty'            => 'required|numeric|min:0.0001',
            'items.*.harga_satuan'   => 'required|numeric|min:0',
            'items.*.diskon_nominal' => 'nullable|numeric|min:0',
            'items.*.subtotal'       => 'nullable|numeric|min:0',
            'subtotal'               => 'nullable|numeric|min:0',
            'biaya_pengiriman'       => 'nullable|numeric|min:0',
            'total'                  => 'nullable|numeric|min:0',
            'dp_nominal'             => 'nullable|numeric|min:0',
        ]);

        if (empty($request->customer_id) && empty(trim($request->nama_customer_walkin ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pesanan: Data Pelanggan / Customer wajib dipilih atau diisi.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Validasi Ketersediaan Stok Fisik di Gudang Entitas Aktif (Cabang vs PT Pusat)
            $activeGudangs = $this->resolveActiveGudangIds($activeCompanyId, $activeBranchId);

            $submittedBarangIds = collect($request->items)->pluck('barang_id')->filter()->unique()->toArray();
            
            // Eager load master barang beserta bundleComponents & satuan
            $barangModels = MasterBarang::with(['bundleComponents.childBarang.satuan', 'satuan'])
                ->whereIn('id', $submittedBarangIds)
                ->get()
                ->keyBy('id');

            // Kumpulkan semua ID barang fisik yang perlu dicek stoknya (termasuk komponen dari bundle)
            $allStockBarangIds = $submittedBarangIds;
            foreach ($barangModels as $bModel) {
                if ($bModel->tipe_item === 'Bundle' && $bModel->bundleComponents) {
                    foreach ($bModel->bundleComponents as $bc) {
                        $allStockBarangIds[] = $bc->child_barang_id;
                    }
                }
            }
            $allStockBarangIds = array_unique(array_filter($allStockBarangIds));

            $liveStocks = [];
            if (!empty($allStockBarangIds) && !empty($activeGudangs)) {
                $liveStocks = DB::table('stok_gudangs')
                    ->whereIn('barang_id', $allStockBarangIds)
                    ->whereIn('gudang_id', $activeGudangs)
                    ->groupBy('barang_id')
                    ->selectRaw('barang_id, SUM(stok_aktual) as total_stok')
                    ->pluck('total_stok', 'barang_id')
                    ->toArray();
            }

            // Validasi Ketersediaan Total Demand Fisik Lintas-Item (Bundle + Standalone)
            $totalDemand = [];
            foreach ($request->items as $item) {
                $bId = (int)($item['barang_id'] ?? 0);
                $qty = (float)($item['qty'] ?? 1);
                $bModel = $barangModels->get($bId) ?: MasterBarang::find($bId);
                if (!$bModel) continue;

                if ($bModel->tipe_item === 'Bundle' && $bModel->bundleComponents && $bModel->bundleComponents->count() > 0) {
                    foreach ($bModel->bundleComponents as $bc) {
                        $childId = (int)$bc->child_barang_id;
                        $reqQty = (float)($bc->qty ?? 1) * $qty;
                        $totalDemand[$childId] = ($totalDemand[$childId] ?? 0) + $reqQty;
                    }
                } elseif ($bModel->tipe_item !== 'Jasa') {
                    $totalDemand[$bId] = ($totalDemand[$bId] ?? 0) + $qty;
                }
            }

            foreach ($totalDemand as $demBarangId => $demQty) {
                $stockAvail = isset($liveStocks[$demBarangId]) ? (float)$liveStocks[$demBarangId] : 0;
                if ($demQty > $stockAvail) {
                    $demBarang = MasterBarang::find($demBarangId);
                    $demName = $demBarang ? $demBarang->nama_barang : "Barang #{$demBarangId}";
                    $demSatuan = $demBarang?->satuan?->nama_satuan ?? 'Unit';
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal memproses pesanan: Total kebutuhan untuk '{$demName}' ({$demQty} {$demSatuan}) melebihi sisa persediaan di gudang aktif ({$stockAvail} {$demSatuan})."
                    ], 422);
                }
            }

            // Pre-process items, verifikasi harga satuan sesuai tier, dan hitung ulang matematika per-item
            $computedSubtotal = 0;
            $computedDiskon   = 0;
            $computedTotalPpn = 0;
            $computedTotalHpp = 0;
            $processedItems   = [];

            foreach ($request->items as $item) {
                $barang = $barangModels->get($item['barang_id']) ?: MasterBarang::find($item['barang_id']);
                if (!$barang) {
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal memproses pesanan: Produk #{$item['barang_id']} tidak ditemukan."
                    ], 422);
                }

                $namaBarang = $barang->nama_barang;
                $satuanNama = $barang->satuan ? $barang->satuan->nama_satuan : 'Unit';

                // Verifikasi harga satuan terhadap pricing_tier master barang untuk mencegah price tampering
                $tier = $request->pricing_tier;
                $expectedPrice = 0;
                if ($tier === 'Retail') {
                    $expectedPrice = (float)($barang->harga_jual_retail > 0 ? $barang->harga_jual_retail : ($barang->harga_jual_grosir > 0 ? $barang->harga_jual_grosir : $barang->harga_jual_tender));
                } elseif ($tier === 'Grosir') {
                    $expectedPrice = (float)($barang->harga_jual_grosir > 0 ? $barang->harga_jual_grosir : ($barang->harga_jual_retail > 0 ? $barang->harga_jual_retail : $barang->harga_jual_tender));
                } elseif ($tier === 'Tender') {
                    $expectedPrice = (float)($barang->harga_jual_tender > 0 ? $barang->harga_jual_tender : ($barang->harga_jual_retail > 0 ? $barang->harga_jual_retail : $barang->harga_jual_grosir));
                }

                $submittedPrice = (float)($item['harga_satuan'] ?? 0);
                // Gunakan harga master untuk tier standar (Retail/Grosir/Tender)
                $hargaSatuan = ($tier !== 'Custom' && $expectedPrice > 0) ? $expectedPrice : $submittedPrice;

                if ($hargaSatuan <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal memproses pesanan: Produk '{$namaBarang}' memiliki harga Rp 0. Setiap produk wajib memiliki harga jual lebih dari Rp 0."
                    ], 422);
                }

                // Kalkulasi matematis per item
                $qty = (float)($item['qty'] ?? 1);
                $gross = round($qty * $hargaSatuan, 2);

                $diskonPersenItem  = isset($item['diskon_persen']) ? min(100, max(0, (float)$item['diskon_persen'])) : 0;
                $diskonNominalItem = isset($item['diskon_nominal']) ? (float)$item['diskon_nominal'] : 0;
                if ($diskonPersenItem > 0) {
                    $diskonNominalItem = round($gross * ($diskonPersenItem / 100), 2);
                }
                $diskonNominalItem = min($gross, max(0, $diskonNominalItem));
                $itemSubtotal = round(max(0, $gross - $diskonNominalItem), 2);
                $computedSubtotal += $itemSubtotal;
                $computedDiskon += $diskonNominalItem;

                // Pajak PPN Per Item (Verifikasi dari master pajak jika ada id)
                $ppnPajakId = !empty($item['ppn_pajak_id']) ? $item['ppn_pajak_id'] : null;
                $tarifPpnItem = isset($item['tarif_ppn']) ? max(0, min(100, (float)$item['tarif_ppn'])) : 0;
                if (!empty($ppnPajakId)) {
                    $pajakObj = MasterPajak::find($ppnPajakId);
                    if ($pajakObj) {
                        $tarifPpnItem = (float)$pajakObj->tarif;
                    }
                }
                $itemNilaiPpn = round($itemSubtotal * ($tarifPpnItem / 100), 2);
                $computedTotalPpn += $itemNilaiPpn;

                // HPP & Margin
                $hppSatuan = 0;
                if ($barang->tipe_item === 'Bundle' && $barang->bundleComponents) {
                    foreach ($barang->bundleComponents as $bc) {
                        $cBarang = $bc->childBarang ?: MasterBarang::find($bc->child_barang_id);
                        $cHpp = (float)($cBarang?->harga_beli ?? 0);
                        $hppSatuan += $cHpp * (float)($bc->qty ?? 1);
                    }
                } else {
                    $hppSatuan = (float)($barang->harga_beli ?? 0);
                }
                $itemTotalHpp = round($qty * $hppSatuan, 2);
                $itemEstimasiLaba = round($itemSubtotal - $itemTotalHpp, 2);
                $itemMarginPersen = ($itemSubtotal > 0) ? round(($itemEstimasiLaba / $itemSubtotal) * 100, 2) : 0;
                $computedTotalHpp += $itemTotalHpp;

                $processedItems[] = [
                    'barang_id'      => $barang->id,
                    'qty'            => $qty,
                    'satuan_id'      => $barang->satuan_id,
                    'pricing_tier'   => $request->pricing_tier,
                    'harga_satuan'   => $hargaSatuan,
                    'diskon_persen'  => $diskonPersenItem,
                    'diskon_nominal' => $diskonNominalItem,
                    'subtotal'       => $itemSubtotal,
                    'ppn_pajak_id'   => $ppnPajakId,
                    'tarif_ppn'      => $tarifPpnItem,
                    'nilai_ppn'      => $itemNilaiPpn,
                    'hpp_satuan'     => $hppSatuan,
                    'total_hpp'      => $itemTotalHpp,
                    'estimasi_laba'  => $itemEstimasiLaba,
                    'margin_persen'  => $itemMarginPersen,
                    'catatan'        => !empty($item['catatan']) ? trim(strip_tags($item['catatan'])) : null,
                ];
            }

            // Hitung ulang total pesanan secara akurat
            $subtotal      = $computedSubtotal;
            $diskonNominal = $computedDiskon;
            $diskonPersen  = ($subtotal + $diskonNominal > 0) ? round(($diskonNominal / ($subtotal + $diskonNominal)) * 100, 2) : 0;
            $nilaiPpn      = round($computedTotalPpn, 2);
            $tarifPpn      = ($subtotal > 0 && $nilaiPpn > 0) ? round(($nilaiPpn / $subtotal) * 100, 2) : 0;
            $tipePpn       = ($nilaiPpn > 0) ? 'Item' : 'None';
            $pajakTipe     = ($nilaiPpn > 0) ? 'PPN_Exclude' : 'None';

            $totalHpp          = round($computedTotalHpp, 2);
            $estimasiLabaKotor = round($subtotal - $totalHpp, 2);
            $marginPersen      = ($subtotal > 0) ? round(($estimasiLabaKotor / $subtotal) * 100, 2) : 0;

            $biayaKirim    = max(0, (float)$request->input('biaya_pengiriman', 0));
            $grandTotal    = max(0, round($subtotal + $nilaiPpn + $biayaKirim, 2));

            $tipeDp        = $request->input('tipe_dp');
            $dpPersen      = (float)$request->input('dp_persen', 0);
            $dpNominal     = (float)$request->input('dp_nominal', 0);
            if ($tipeDp === 'Persen') {
                $dpPersen  = min(100, max(0, $dpPersen));
                $dpNominal = round($grandTotal * ($dpPersen / 100), 2);
            } elseif ($tipeDp === 'Nominal') {
                $dpNominal = min($grandTotal, max(0, $dpNominal));
                $dpPersen  = ($grandTotal > 0 && $dpNominal > 0) ? round(($dpNominal / $grandTotal) * 100, 2) : 0;
            } else {
                $tipeDp    = null;
                $dpNominal = 0;
                $dpPersen  = 0;
            }
            $sisaTagihan   = max(0, round($grandTotal - $dpNominal, 2));

            $nomorSo = SalesOrder::generateNomorSo($activeCompanyId, $activeBranchId);

            $alamatPengiriman = $request->input('alamat_pengiriman');
            if ((empty($alamatPengiriman) || trim($alamatPengiriman) === '' || trim($alamatPengiriman) === '-') && !empty($request->customer_id)) {
                $customerObj = MasterCustomer::with(['defaultAddress', 'addresses'])->find($request->customer_id);
                if ($customerObj) {
                    $alamatPengiriman = $customerObj->alamat;
                }
            }
            $alamatPengiriman = !empty($alamatPengiriman) ? trim(strip_tags($alamatPengiriman)) : null;

            $order = SalesOrder::create([
                'company_id'              => $activeCompanyId,
                'branch_id'               => $activeBranchId,
                'nomor_so'                => $nomorSo,
                'tanggal_so'              => $request->input('tanggal_so', date('Y-m-d')),
                'customer_id'             => $request->customer_id ?: null,
                'nama_customer_walkin'    => $request->customer_id ? null : (trim(strip_tags($request->nama_customer_walkin ?: 'Pelanggan Umum'))),
                'telepon_customer_walkin' => $request->filled('telepon_customer_walkin') ? trim(strip_tags($request->telepon_customer_walkin)) : null,
                'pricing_tier'            => $request->pricing_tier,
                'payment_term_id'         => $request->payment_term_id ?: null,
                'payment_method_id'       => $request->payment_method_id ?: null,
                'status'                  => $request->input('status', 'Confirmed'),
                'subtotal'                => $subtotal,
                'diskon_tipe'             => 'Nominal',
                'diskon_nilai'            => $diskonNominal,
                'diskon_persen'           => $diskonPersen,
                'diskon_nominal'          => $diskonNominal,
                'tipe_ppn'                => $tipePpn,
                'ppn_pajak_id'            => null,
                'tarif_ppn'               => $tarifPpn,
                'nilai_ppn'               => $nilaiPpn,
                'pajak_tipe'              => $pajakTipe,
                'pajak_persen'            => $tarifPpn,
                'pajak_nominal'           => $nilaiPpn,
                'pph_pajak_id'            => null,
                'tipe_pph'                => 'Non-PPh',
                'tarif_pph'               => 0,
                'nilai_pph'               => 0,
                'biaya_pengiriman'        => $biayaKirim,
                'total'                   => $grandTotal,
                'grand_total'             => $grandTotal,
                'total_hpp'               => $totalHpp,
                'estimasi_laba_kotor'     => $estimasiLabaKotor,
                'margin_persen'           => $marginPersen,
                'tipe_dp'                 => $tipeDp,
                'dp_persen'               => $dpPersen,
                'dp_nominal'              => $dpNominal,
                'sisa_tagihan'            => $sisaTagihan,
                'catatan'                 => $request->filled('catatan') ? trim(strip_tags($request->catatan)) : null,
                'syarat_ketentuan'        => $request->filled('syarat_ketentuan') ? trim(strip_tags($request->syarat_ketentuan)) : "1. Barang sesuai dengan spesifikasi pesanan.\n2. Pembayaran sah setelah dana efektif masuk ke rekening.",
                'alamat_pengiriman'       => $alamatPengiriman,
                'created_by'              => Auth::id(),
            ]);

            foreach ($processedItems as $pItem) {
                SalesOrderDetail::create([
                    'sales_order_id' => $order->id,
                    'barang_id'      => $pItem['barang_id'],
                    'qty'            => $pItem['qty'],
                    'satuan_id'      => $pItem['satuan_id'],
                    'pricing_tier'   => $pItem['pricing_tier'],
                    'harga_satuan'   => $pItem['harga_satuan'],
                    'diskon_persen'  => $pItem['diskon_persen'],
                    'diskon_nominal' => $pItem['diskon_nominal'],
                    'subtotal'       => $pItem['subtotal'],
                    'ppn_pajak_id'   => $pItem['ppn_pajak_id'],
                    'tarif_ppn'      => $pItem['tarif_ppn'],
                    'nilai_ppn'      => $pItem['nilai_ppn'],
                    'hpp_satuan'     => $pItem['hpp_satuan'],
                    'total_hpp'      => $pItem['total_hpp'],
                    'estimasi_laba'  => $pItem['estimasi_laba'],
                    'margin_persen'  => $pItem['margin_persen'],
                    'catatan'        => $pItem['catatan'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pesanan {$order->nomor_so} berhasil dibuat!",
                'order'   => $order->load(['customer', 'paymentTerm', 'paymentMethod', 'details.barang.satuan']),
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
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-sales-order') && !$user->can('tambah-sales-order') && !$user->can('akses-sales')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $query = SalesOrder::forActiveCompany()
            ->with(['customer', 'paymentTerm', 'paymentMethod', 'creator', 'details.barang', 'deliveryOrders.salesInvoices', 'salesInvoices'])
            ->orderBy('id', 'desc');

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
                if ($row->status === 'Cancelled') {
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill fw-semibold font-monospace d-inline-flex align-items-center gap-1" style="font-size:0.75rem;"><i class="bi bi-x-circle-fill"></i> Batal</span>';
                }

                if ($row->status === 'Completed') {
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold font-monospace d-inline-flex align-items-center gap-1" style="font-size:0.75rem;"><i class="bi bi-patch-check-fill"></i> Selesai</span>';
                }

                // Cek status Surat Jalan / Delivery Order yang terhubung
                $dos = $row->deliveryOrders ? $row->deliveryOrders->where('status', '!=', 'Dibatalkan') : collect();

                if ($dos->isNotEmpty()) {
                    $allTerkirim = $dos->every(fn($d) => $d->status === 'Terkirim');
                    $hasShipped = $dos->contains(fn($d) => in_array($d->status, ['Disetujui', 'Siap Dikirim', 'Dalam Perjalanan']));

                    // Jika di pengiriman semua statusnya Terkirim
                    if ($allTerkirim) {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold font-monospace d-inline-flex align-items-center gap-1" style="font-size:0.75rem;"><i class="bi bi-check-circle-fill"></i> Terkirim</span>';
                    }

                    // Jika sudah disetujui / dalam perjalanan / siap dikirim
                    if ($hasShipped) {
                        return '<span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill fw-semibold font-monospace d-inline-flex align-items-center gap-1" style="font-size:0.75rem;"><i class="bi bi-truck"></i> Dikirim</span>';
                    }

                    // Jika masih Menunggu Persetujuan / Draft
                    return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill fw-semibold font-monospace d-inline-flex align-items-center gap-1" style="font-size:0.75rem;"><i class="bi bi-arrow-repeat"></i> Diproses</span>';
                }

                // Fallback jika tidak ada DO tapi statusnya Delivered
                if ($row->status === 'Delivered') {
                    return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold font-monospace d-inline-flex align-items-center gap-1" style="font-size:0.75rem;"><i class="bi bi-check-circle-fill"></i> Terkirim</span>';
                }

                if ($row->status === 'Processing') {
                    return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill fw-semibold font-monospace d-inline-flex align-items-center gap-1" style="font-size:0.75rem;"><i class="bi bi-arrow-repeat"></i> Diproses</span>';
                }

                return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill fw-semibold font-monospace d-inline-flex align-items-center gap-1" style="font-size:0.75rem;"><i class="bi bi-box-seam"></i> Menunggu Kirim</span>';
            })
            ->addColumn('items_count', function ($row) {
                $count = $row->details->count();
                return "<span class='badge bg-body-tertiary text-body border px-2.5 py-1 rounded-pill font-monospace'>{$count} item</span>";
            })
            ->addColumn('formatted_total', function ($row) {
                $grandTotal = (float)($row->grand_total ?? 0);
                $total      = (float)($row->total ?? 0);
                $effectiveTotal = $grandTotal > 0 ? $grandTotal : $total;
                return '<span class="fw-bold font-monospace text-primary">Rp ' . number_format($effectiveTotal, 2, ',', '.') . '</span>';
            })
            ->addColumn('ppn_info', function ($row) {
                $nilaiPpn = (float)($row->nilai_ppn ?? 0);
                $pajakNominal = (float)($row->pajak_nominal ?? 0);
                $ppn = $nilaiPpn > 0 ? $nilaiPpn : $pajakNominal;
                return $ppn > 0 ? 'Rp ' . number_format($ppn, 2, ',', '.') : '-';
            })
            ->addColumn('has_active_invoice', function ($row) {
                return $row->getActiveInvoice() !== null;
            })
            ->addColumn('active_invoice_no', function ($row) {
                $inv = $row->getActiveInvoice();
                return $inv ? $inv->nomor_faktur : null;
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

        if ($entityErr = $this->checkOrderEntityAccess($order, $user)) {
            return response()->json(['message' => $entityErr], 403);
        }

        $order->load([
            'company',
            'branch.company',
            'customer.defaultAddress',
            'customer.addresses',
            'quotation',
            'tender',
            'sales.department',
            'sales.position',
            'paymentTerm',
            'paymentMethod',
            'ppnPajak',
            'pphPajak',
            'creator.karyawan.position',
            'details.barang.satuan',
            'details.barang.primaryFoto',
            'details.barang.kategori',
            'details.satuan',
            'details.kategori',
            'details.ppnPajak',
            'deliveryOrders',
        ]);

        if ((empty($order->alamat_pengiriman) || trim($order->alamat_pengiriman) === '' || trim($order->alamat_pengiriman) === '-') && $order->customer) {
            $order->alamat_pengiriman = $order->customer->alamat;
        }

        $companies = \App\Models\Company::active()->get();
        if ($companies->isEmpty()) {
            $companies = \App\Models\Company::all();
        }

        // Cek asal input order: apakah diinput di Cabang (branch_id terisi) atau PT Pusat
        $isBranch = !empty($order->branch_id);
        $branch = null;
        $company = null;

        if ($isBranch) {
            $branch = $order->branch ?: \App\Models\Branch::with('company')->find($order->branch_id);
            $company = $branch?->company ?: ($order->company ?: $companies->first());
            $entityName = $branch?->nama_cabang ?: '';
            $parentName = '';
            $alamatParts = array_filter([
                $branch?->address ?: ($branch?->alamat ?? ''),
                $branch?->city ?: ($branch?->kota ?? ''),
                $branch?->province ?: ($branch?->provinsi ?? ''),
                $branch?->postal_code ?: ($branch?->kode_pos ?? '')
            ]);
            $entityAddress = implode(', ', $alamatParts);
            $entityPhone   = $branch?->phone ?: ($branch?->telepon ?? '');
            $entityEmail   = $branch?->email ?: '';
            $entityNpwp    = '';
        } else {
            $selectedCompanyId = $order->company_id ?? auth()->user()?->company_id;
            $company = $companies->firstWhere('id', $selectedCompanyId)
                ?? \App\Models\Company::find($selectedCompanyId)
                ?? $order->company
                ?? $companies->first()
                ?? \App\Models\Company::first();
            $entityName = $company?->nama_perusahaan ?? '';
            $parentName = '';
            $alamatParts = array_filter([
                $company?->address ?: ($company?->alamat ?? ''),
                $company?->city ?: ($company?->kota ?? ''),
                $company?->province ?: ($company?->provinsi ?? ''),
                $company?->postal_code ?: ($company?->kode_pos ?? '')
            ]);
            $entityAddress = implode(', ', $alamatParts);
            $entityPhone   = $company?->phone ?: ($company?->telepon ?? '');
            $entityEmail   = $company?->email ?: '';
            $entityNpwp    = $company?->npwp ?: '';
        }

        $activeLogo = null;
        if ($isBranch && $branch) {
            if (!empty($branch->logo)) {
                if (file_exists(public_path('storage/' . $branch->logo))) {
                    $activeLogo = asset('storage/' . $branch->logo);
                } elseif (file_exists(public_path($branch->logo))) {
                    $activeLogo = asset($branch->logo);
                }
            }
            if (!$activeLogo && $company && !empty($company->logo)) {
                if (file_exists(public_path('storage/' . $company->logo))) {
                    $activeLogo = asset('storage/' . $company->logo);
                } elseif (file_exists(public_path($company->logo))) {
                    $activeLogo = asset($company->logo);
                }
            }
        } else {
            if ($company && !empty($company->logo)) {
                if (file_exists(public_path('storage/' . $company->logo))) {
                    $activeLogo = asset('storage/' . $company->logo);
                } elseif (file_exists(public_path($company->logo))) {
                    $activeLogo = asset($company->logo);
                }
            }
        }

        if (!$activeLogo) {
            $activeLogo = asset('gambar/logo.png');
        }

        // Fetch live stocks in active entity's warehouses for order details
        $detailCompanyId = $order->company_id ?: (session('active_company_id') ?: 1);
        $detailBranchId  = $order->branch_id ?: session('active_branch_id');
        $detailGudangs   = \App\Models\MasterGudang::where('company_id', $detailCompanyId)
            ->where('status', 'Active')
            ->when(!empty($detailBranchId), function ($q) use ($detailBranchId) {
                $q->where('cabang_id', $detailBranchId);
            }, function ($q) {
                $q->whereNull('cabang_id');
            })
            ->pluck('id')
            ->toArray();

        $detailBarangIds = $order->details->pluck('barang_id')->filter()->unique()->toArray();
        $stockMap = [];
        if (!empty($detailBarangIds) && !empty($detailGudangs)) {
            $stockMap = DB::table('stok_gudangs')
                ->whereIn('barang_id', $detailBarangIds)
                ->whereIn('gudang_id', $detailGudangs)
                ->groupBy('barang_id')
                ->selectRaw('barang_id, SUM(stok_aktual) as total_stok')
                ->pluck('total_stok', 'barang_id')
                ->toArray();
        }

        // Add photo urls and live stock info to details
        $order->details->each(function ($d) use ($stockMap) {
            $photoPath = $d->barang?->primaryFoto?->foto_path;
            if (!empty($photoPath)) {
                if (str_starts_with($photoPath, 'http://') || str_starts_with($photoPath, 'https://')) {
                    $d->photo_url = $photoPath;
                } elseif (str_starts_with($photoPath, 'uploads/') || str_starts_with($photoPath, '/uploads/')) {
                    $d->photo_url = asset(ltrim($photoPath, '/'));
                } else {
                    $d->photo_url = asset('storage/' . ltrim($photoPath, '/'));
                }
            } else {
                $d->photo_url = asset('uploads/barang/placeholder_garment.png');
            }

            $bId = $d->barang_id;
            $d->total_stok = isset($stockMap[$bId]) ? (float)$stockMap[$bId] : 0;
            $d->tipe_item  = $d->barang?->tipe_item ?? 'Stok';
        });

        $effectiveTotal = (float)($order->grand_total > 0 ? $order->grand_total : $order->total);
        $terbilang = $this->terbilang($effectiveTotal) . ' Rupiah';
        $activeInvoice = $order->getActiveInvoice();

        return response()->json([
            'order'          => $order,
            'active_invoice' => $activeInvoice ? [
                'id'           => $activeInvoice->id,
                'nomor_faktur' => $activeInvoice->nomor_faktur,
                'status'       => $activeInvoice->status,
            ] : null,
            'entity_name'    => $entityName,
            'parent_name'    => $parentName,
            'entity_address' => $entityAddress,
            'entity_phone'   => $entityPhone,
            'entity_email'   => $entityEmail,
            'entity_npwp'    => $entityNpwp,
            'active_logo'    => $activeLogo,
            'terbilang'      => $terbilang,
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

        // Multi-tenancy & branch isolation check
        if ($entityErr = $this->checkOrderEntityAccess($order, $user)) {
            return response()->json(['success' => false, 'message' => $entityErr], 403);
        }

        // Status check: Processing or Delivered orders can only be edited by Super Admin / Owner
        if (in_array($order->status, ['Processing', 'Delivered']) && !$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json(['success' => false, 'message' => 'Pesanan dengan status "' . $order->status . '" hanya dapat diedit oleh Super Admin.'], 403);
        }

        // Status check: completed or cancelled orders cannot be modified
        if (in_array($order->status, ['Completed', 'Cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Pesanan dengan status "' . $order->status . '" tidak dapat diedit.'], 422);
        }

        // Invoice / Faktur Penjualan check: cegah edit jika sudah ada invoice aktif
        $activeInvoice = $order->getActiveInvoice();
        if ($activeInvoice) {
            return response()->json([
                'success' => false,
                'message' => "Pesanan tidak dapat diedit karena sudah tercatat pada Faktur Penjualan ({$activeInvoice->nomor_faktur}). Harap batalkan atau hapus Faktur Penjualan tersebut terlebih dahulu jika ingin mengubah rincian pesanan."
            ], 422);
        }

        // Delivery Order check: if an active delivery order already exists, prevent editing items to avoid discrepancy
        if ($order->deliveryOrders()->whereNotIn('status', ['Cancelled', 'Dibatalkan'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat diedit karena sudah memiliki Surat Jalan / Pengiriman (Delivery Order) aktif.'
            ], 422);
        }

        $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.detail_id'      => 'nullable',
            'items.*.barang_id'      => 'nullable',
            'items.*.qty'            => 'required|numeric|min:0.0001',
            'items.*.harga_satuan'   => 'required|numeric|gt:0',
            'items.*.diskon_nominal' => 'nullable|numeric|min:0',
            'items.*.subtotal'       => 'required|numeric|min:0',
            'items.*.ppn_pajak_id'   => 'nullable',
            'items.*.tarif_ppn'      => 'nullable|numeric|min:0',
            'items.*.nilai_ppn'      => 'nullable|numeric|min:0',
            'subtotal'               => 'required|numeric|min:0',
            'diskon_nominal'         => 'nullable|numeric|min:0',
            'pajak_nominal'          => 'nullable|numeric|min:0',
            'total'                  => 'required|numeric|min:0',
        ]);

        if (empty($request->customer_id) && empty(trim($request->nama_customer_walkin ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pesanan: Data Pelanggan / Customer wajib dipilih atau diisi.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Validasi Ketersediaan Stok Fisik untuk Item yang Diupdate/Ditambah
            $submittedBarangIds = collect($request->items)->pluck('barang_id')->unique()->filter()->toArray();

            $activeCompanyId = $order->company_id ?: (session('active_company_id') ?: 1);
            $activeBranchId  = $order->branch_id ?: session('active_branch_id');

            $activeGudangs = $this->resolveActiveGudangIds($activeCompanyId, $activeBranchId);

            $barangModels = MasterBarang::with(['bundleComponents.childBarang.satuan', 'satuan'])
                ->whereIn('id', $submittedBarangIds)
                ->get()
                ->keyBy('id');

            $allStockBarangIds = $submittedBarangIds;
            foreach ($barangModels as $bModel) {
                if ($bModel->tipe_item === 'Bundle' && $bModel->bundleComponents) {
                    foreach ($bModel->bundleComponents as $bc) {
                        $allStockBarangIds[] = $bc->child_barang_id;
                    }
                }
            }
            $allStockBarangIds = array_unique(array_filter($allStockBarangIds));

            $liveStocks = [];
            if (!empty($allStockBarangIds) && !empty($activeGudangs)) {
                $liveStocks = DB::table('stok_gudangs')
                    ->whereIn('barang_id', $allStockBarangIds)
                    ->whereIn('gudang_id', $activeGudangs)
                    ->groupBy('barang_id')
                    ->selectRaw('barang_id, SUM(stok_aktual) as total_stok')
                    ->pluck('total_stok', 'barang_id')
                    ->toArray();
            }

            // Validasi Ketersediaan Total Demand Fisik Lintas-Item (Bundle + Standalone)
            $totalDemand = [];
            foreach ($request->items as $item) {
                $bId = (int)($item['barang_id'] ?? 0);
                $qty = (float)($item['qty'] ?? 1);
                $bModel = $barangModels->get($bId) ?: MasterBarang::find($bId);
                if (!$bModel) continue;

                if ($bModel->tipe_item === 'Bundle' && $bModel->bundleComponents && $bModel->bundleComponents->count() > 0) {
                    foreach ($bModel->bundleComponents as $bc) {
                        $childId = (int)$bc->child_barang_id;
                        $reqQty = (float)($bc->qty ?? 1) * $qty;
                        $totalDemand[$childId] = ($totalDemand[$childId] ?? 0) + $reqQty;
                    }
                } elseif ($bModel->tipe_item !== 'Jasa') {
                    $totalDemand[$bId] = ($totalDemand[$bId] ?? 0) + $qty;
                }
            }

            foreach ($totalDemand as $demBarangId => $demQty) {
                $stockAvail = isset($liveStocks[$demBarangId]) ? (float)$liveStocks[$demBarangId] : 0;
                if ($demQty > $stockAvail) {
                    $demBarang = MasterBarang::find($demBarangId);
                    $demName = $demBarang ? $demBarang->nama_barang : "Barang #{$demBarangId}";
                    $demSatuan = $demBarang?->satuan?->nama_satuan ?? 'Unit';
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal memperbarui pesanan: Total kebutuhan untuk '{$demName}' ({$demQty} {$demSatuan}) melebihi sisa persediaan di gudang aktif ({$stockAvail} {$demSatuan})."
                    ], 422);
                }
            }

            // Pre-process items, recalculate accurate subtotals, per-item PPN, and validate discounts
            $computedSubtotal = 0;
            $computedDiskon   = 0;
            $computedTotalPpn = 0;
            $computedTotalHpp = 0;
            $processedItems = [];

            foreach ($request->items as $idx => $item) {
                $barangId = !empty($item['barang_id']) ? $item['barang_id'] : null;
                $barang = $barangId ? ($barangModels->get($barangId) ?: MasterBarang::find($barangId)) : null;
                $satuanId = $barang ? $barang->satuan_id : ($item['satuan_id'] ?? null);
                $satuanNama = $item['satuan_nama'] ?? ($barang && $barang->satuan ? $barang->satuan->nama_satuan : 'Unit');
                $namaCustom = empty($barangId) ? trim(strip_tags($item['nama_barang_custom'] ?? 'Item Custom')) : null;

                $qty = (float)($item['qty'] ?? 1);

                // Tier & Verifikasi Harga Satuan terhadap master untuk mencegah price tampering
                $tierRaw = $item['pricing_tier'] ?? $order->pricing_tier ?? 'Retail';
                if (strtolower($tierRaw) === 'satuan' || strtolower($tierRaw) === 'retail') {
                    $tier = 'Retail';
                } elseif (strtolower($tierRaw) === 'grosir') {
                    $tier = 'Grosir';
                } elseif (strtolower($tierRaw) === 'tender') {
                    $tier = 'Tender';
                } else {
                    $tier = 'Retail';
                }

                $expectedPrice = 0;
                if ($barang) {
                    if ($tier === 'Retail') {
                        $expectedPrice = (float)($barang->harga_jual_retail > 0 ? $barang->harga_jual_retail : ($barang->harga_jual_grosir > 0 ? $barang->harga_jual_grosir : $barang->harga_jual_tender));
                    } elseif ($tier === 'Grosir') {
                        $expectedPrice = (float)($barang->harga_jual_grosir > 0 ? $barang->harga_jual_grosir : ($barang->harga_jual_retail > 0 ? $barang->harga_jual_retail : $barang->harga_jual_tender));
                    } elseif ($tier === 'Tender') {
                        $expectedPrice = (float)($barang->harga_jual_tender > 0 ? $barang->harga_jual_tender : ($barang->harga_jual_retail > 0 ? $barang->harga_jual_retail : $barang->harga_jual_grosir));
                    }
                }
                $submittedPrice = (float)($item['harga_satuan'] ?? 0);
                $hargaSatuan = ($expectedPrice > 0) ? $expectedPrice : $submittedPrice;

                if ($hargaSatuan <= 0) {
                    $namaBarang = $barang ? $barang->nama_barang : ($namaCustom ?: "Barang #{$barangId}");
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal memperbarui pesanan: Produk '{$namaBarang}' memiliki harga Rp 0. Setiap produk wajib memiliki harga jual lebih dari Rp 0."
                    ], 422);
                }

                $gross = round($qty * $hargaSatuan, 2);

                $diskonPersenItem  = isset($item['diskon_persen']) ? min(100, max(0, (float)$item['diskon_persen'])) : 0;
                $diskonNominalItem = isset($item['diskon_nominal']) ? (float)$item['diskon_nominal'] : 0;
                if ($diskonPersenItem > 0) {
                    $diskonNominalItem = round($gross * ($diskonPersenItem / 100), 2);
                }
                $diskonNominalItem = min($gross, max(0, $diskonNominalItem));
                $itemSubtotal = round(max(0, $gross - $diskonNominalItem), 2);
                $computedSubtotal += $itemSubtotal;
                $computedDiskon += $diskonNominalItem;

                // Pajak PPN Per Item (Verifikasi dari master jika ada id)
                $ppnPajakId = !empty($item['ppn_pajak_id']) ? $item['ppn_pajak_id'] : null;
                $tarifPpnItem = isset($item['tarif_ppn']) ? max(0, min(100, (float)$item['tarif_ppn'])) : 0;
                if (!empty($ppnPajakId)) {
                    $pajakObj = MasterPajak::find($ppnPajakId);
                    if ($pajakObj) {
                        $tarifPpnItem = (float)$pajakObj->tarif;
                    }
                }
                $itemNilaiPpn = round($itemSubtotal * ($tarifPpnItem / 100), 2);
                $computedTotalPpn += $itemNilaiPpn;

                // HPP & Margin
                $hppSatuan = 0;
                if ($barang) {
                    if ($barang->tipe_item === 'Bundle' && $barang->bundleComponents) {
                        foreach ($barang->bundleComponents as $bc) {
                            $cBarang = $bc->childBarang ?: MasterBarang::find($bc->child_barang_id);
                            $cHpp = (float)($cBarang?->harga_beli ?? 0);
                            $hppSatuan += $cHpp * (float)($bc->qty ?? 1);
                        }
                    } else {
                        $hppSatuan = (float)($barang->harga_beli ?? 0);
                    }
                }
                $itemTotalHpp = round($qty * $hppSatuan, 2);
                $itemEstimasiLaba = round($itemSubtotal - $itemTotalHpp, 2);
                $itemMarginPersen = ($itemSubtotal > 0) ? round(($itemEstimasiLaba / $itemSubtotal) * 100, 2) : 0;
                $computedTotalHpp += $itemTotalHpp;

                $processedItems[] = [
                    'detail_id'          => $item['detail_id'] ?? null,
                    'barang_id'          => $barangId,
                    'nama_barang_custom' => $namaCustom,
                    'satuan_id'          => $satuanId,
                    'nama_satuan'        => $satuanNama,
                    'pricing_tier'       => $tier,
                    'qty'                => $qty,
                    'harga_satuan'       => $hargaSatuan,
                    'diskon_persen'      => $diskonPersenItem,
                    'diskon_nominal'     => $diskonNominalItem,
                    'subtotal'           => $itemSubtotal,
                    'ppn_pajak_id'       => $ppnPajakId,
                    'tarif_ppn'          => $tarifPpnItem,
                    'nilai_ppn'          => $itemNilaiPpn,
                    'hpp_satuan'         => $hppSatuan,
                    'total_hpp'          => $itemTotalHpp,
                    'estimasi_laba'      => $itemEstimasiLaba,
                    'margin_persen'      => $itemMarginPersen,
                    'catatan'            => !empty($item['catatan']) ? trim(strip_tags($item['catatan'])) : null,
                    'urutan'             => $idx + 1,
                ];
            }

            // Hitung ulang total pesanan secara akurat
            $subtotal      = $computedSubtotal;
            $diskonNominal = $computedDiskon;
            $diskonPersen  = ($subtotal + $diskonNominal > 0) ? round(($diskonNominal / ($subtotal + $diskonNominal)) * 100, 2) : 0;
            $diskonTipe    = 'Nominal';
            $diskonNilai   = $diskonNominal;

            // Per-Item PPN Aggregation
            $nilaiPpn      = round($computedTotalPpn, 2);
            $pajakNominal  = $nilaiPpn;
            $tarifPpn      = ($subtotal > 0 && $nilaiPpn > 0) ? round(($nilaiPpn / $subtotal) * 100, 2) : 0;
            $tipePpn       = ($nilaiPpn > 0) ? 'Item' : 'None';
            $pajakTipe     = ($nilaiPpn > 0) ? 'PPN_Exclude' : 'None';
            $pajakPersen   = $tarifPpn;

            $totalHpp          = round($computedTotalHpp, 2);
            $estimasiLabaKotor = round($subtotal - $totalHpp, 2);
            $marginPersen      = ($subtotal > 0) ? round(($estimasiLabaKotor / $subtotal) * 100, 2) : 0;

            // PPh Non-PPh
            $tipePph       = 'Non-PPh';
            $pphPajakId    = null;
            $tarifPph      = 0;
            $nilaiPph      = 0;

            $biayaKirim    = max(0, (float)$request->input('biaya_pengiriman', 0));
            $grandTotal    = max(0, round($subtotal + $nilaiPpn + $biayaKirim, 2));

            $tipeDp        = $request->input('tipe_dp', $order->tipe_dp ?? 'None');
            $dpPersen      = (float)$request->input('dp_persen', ($order->dp_persen ?: 0));
            $dpNominal     = (float)$request->input('dp_nominal', ($order->dp_nominal ?: 0));
            if ($tipeDp === 'Persen') {
                $dpPersen  = min(100, max(0, $dpPersen));
                $dpNominal = round($grandTotal * ($dpPersen / 100), 2);
            } elseif ($tipeDp === 'Nominal') {
                $dpNominal = min($grandTotal, max(0, $dpNominal));
                $dpPersen  = ($grandTotal > 0 && $dpNominal > 0) ? round(($dpNominal / $grandTotal) * 100, 2) : 0;
            } else {
                $dpNominal = 0;
                $dpPersen  = 0;
            }
            $sisaTagihan   = max(0, round($grandTotal - $dpNominal, 2));

            $tglSo = $request->filled('tanggal_so') ? substr($request->input('tanggal_so'), 0, 10) : ($order->tanggal_so ? \Carbon\Carbon::parse($order->tanggal_so)->format('Y-m-d') : date('Y-m-d'));

            $order->update([
                'tanggal_so'              => $tglSo,
                'sales_id'                => $request->sales_id ?: $order->sales_id,
                'customer_id'             => $request->customer_id ?: null,
                'nama_customer_walkin'    => $request->customer_id ? null : ($request->filled('nama_customer_walkin') ? trim(strip_tags($request->nama_customer_walkin)) : 'Pelanggan Umum'),
                'telepon_customer_walkin' => $request->filled('telepon_customer_walkin') ? trim(strip_tags($request->telepon_customer_walkin)) : $order->telepon_customer_walkin,
                'payment_method_id'       => $request->payment_method_id ?: null,
                'payment_term_id'         => $request->payment_term_id ?: null,
                'catatan'                 => $request->filled('catatan') ? trim(strip_tags($request->catatan)) : null,
                'syarat_ketentuan'        => $request->has('syarat_ketentuan') ? trim(strip_tags($request->syarat_ketentuan)) : $order->syarat_ketentuan,
                'alamat_pengiriman'       => $request->filled('alamat_pengiriman') ? trim(strip_tags($request->alamat_pengiriman)) : $order->alamat_pengiriman,
                'biaya_pengiriman'        => $biayaKirim,
                'subtotal'                => $subtotal,
                'diskon_tipe'             => $diskonTipe,
                'diskon_nilai'            => $diskonNilai,
                'diskon_persen'           => $diskonPersen,
                'diskon_nominal'          => $diskonNominal,
                'tipe_ppn'                => $tipePpn,
                'ppn_pajak_id'            => null,
                'pajak_tipe'              => $pajakTipe,
                'pajak_persen'            => $pajakPersen,
                'pajak_nominal'           => $pajakNominal,
                'tarif_ppn'               => $tarifPpn,
                'nilai_ppn'               => $nilaiPpn,
                'pph_pajak_id'            => null,
                'tipe_pph'                => 'Non-PPh',
                'tarif_pph'               => 0,
                'nilai_pph'               => 0,
                'total_hpp'               => $totalHpp,
                'estimasi_laba_kotor'     => $estimasiLabaKotor,
                'margin_persen'           => $marginPersen,
                'tipe_dp'                 => $tipeDp,
                'dp_persen'               => $dpPersen,
                'dp_nominal'              => $dpNominal,
                'sisa_tagihan'            => $sisaTagihan,
                'total'                   => $grandTotal,
                'grand_total'             => $grandTotal,
                'updated_by'              => Auth::id(),
            ]);

            // Track processed detail IDs to delete removed ones
            $keptDetailIds = [];
            foreach ($processedItems as $pItem) {
                if (!empty($pItem['detail_id'])) {
                    $detail = SalesOrderDetail::where('id', $pItem['detail_id'])
                        ->where('sales_order_id', $order->id)
                        ->first();
                    if ($detail) {
                        $detail->update([
                            'barang_id'          => $pItem['barang_id'],
                            'nama_barang_custom' => $pItem['nama_barang_custom'],
                            'satuan_id'          => $pItem['satuan_id'],
                            'nama_satuan'        => $pItem['nama_satuan'],
                            'pricing_tier'       => $pItem['pricing_tier'],
                            'qty'                => $pItem['qty'],
                            'harga_satuan'       => $pItem['harga_satuan'],
                            'diskon_persen'      => $pItem['diskon_persen'],
                            'diskon_nominal'     => $pItem['diskon_nominal'],
                            'subtotal'           => $pItem['subtotal'],
                            'ppn_pajak_id'       => $pItem['ppn_pajak_id'],
                            'tarif_ppn'          => $pItem['tarif_ppn'],
                            'nilai_ppn'          => $pItem['nilai_ppn'],
                            'hpp_satuan'         => $pItem['hpp_satuan'],
                            'total_hpp'          => $pItem['total_hpp'],
                            'estimasi_laba'      => $pItem['estimasi_laba'],
                            'margin_persen'      => $pItem['margin_persen'],
                            'catatan'            => $pItem['catatan'] ?? $detail->catatan,
                            'urutan'             => $pItem['urutan'],
                        ]);
                        $keptDetailIds[] = $detail->id;
                    }
                } else {
                    // Create newly added detail row
                    $newDetail = SalesOrderDetail::create([
                        'sales_order_id'     => $order->id,
                        'barang_id'          => $pItem['barang_id'],
                        'nama_barang_custom' => $pItem['nama_barang_custom'],
                        'satuan_id'          => $pItem['satuan_id'],
                        'nama_satuan'        => $pItem['nama_satuan'],
                        'pricing_tier'       => $pItem['pricing_tier'],
                        'qty'                => $pItem['qty'],
                        'harga_satuan'       => $pItem['harga_satuan'],
                        'diskon_persen'      => $pItem['diskon_persen'],
                        'diskon_nominal'     => $pItem['diskon_nominal'],
                        'subtotal'           => $pItem['subtotal'],
                        'ppn_pajak_id'       => $pItem['ppn_pajak_id'],
                        'tarif_ppn'          => $pItem['tarif_ppn'],
                        'nilai_ppn'          => $pItem['nilai_ppn'],
                        'hpp_satuan'         => $pItem['hpp_satuan'],
                        'total_hpp'          => $pItem['total_hpp'],
                        'estimasi_laba'      => $pItem['estimasi_laba'],
                        'margin_persen'      => $pItem['margin_persen'],
                        'catatan'            => $pItem['catatan'] ?? null,
                    ]);
                    $keptDetailIds[] = $newDetail->id;
                }
            }

            // Delete removed items
            if (!empty($keptDetailIds)) {
                SalesOrderDetail::where('sales_order_id', $order->id)
                    ->whereNotIn('id', $keptDetailIds)
                    ->delete();
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
     * Update Order Status (Confirmed, Processing, Delivered, Completed, Cancelled)
     */
    public function updateStatus(Request $request, SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-sales-order')) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($entityErr = $this->checkOrderEntityAccess($order, $user)) {
            return response()->json(['success' => false, 'message' => $entityErr], 403);
        }

        $request->validate([
            'status' => 'required|in:Draft,Confirmed,Processing,Delivered,Completed,Cancelled',
        ]);

        // Status check: completed or cancelled orders cannot be modified unless Super Admin / Owner
        if (in_array($order->status, ['Completed', 'Cancelled']) && !$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json([
                'success' => false,
                'message' => "Pesanan dengan status '{$order->status}' tidak dapat diubah lagi."
            ], 422);
        }

        // Jika status diubah ke Cancelled, delegasikan ke cancelOrder agar Penawaran & Tender ikut dibuka kembali
        if ($request->status === 'Cancelled') {
            if (!$request->filled('alasan_pembatalan')) {
                $request->merge(['alasan_pembatalan' => 'Dibatalkan melalui perubahan status pesanan.']);
            }
            return $this->cancelOrder($request, $order);
        }

        $order->update([
            'status'     => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Status pesanan {$order->nomor_so} diubah menjadi {$order->status}.",
        ]);
    }

    /**
     * Cancel Sales Order
     */
    public function cancelOrder(Request $request, SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner', 'Admin']) && !$user->can('hapus-sales-order') && !$user->can('batal-sales-order')) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk membatalkan pesanan ini.'], 403);
        }

        // Multi-tenancy check
        if ($entityErr = $this->checkOrderEntityAccess($order, $user)) {
            return response()->json(['success' => false, 'message' => $entityErr], 403);
        }

        // Status check: Processing or Delivered orders can only be cancelled by Super Admin / Owner
        if (in_array($order->status, ['Processing', 'Delivered']) && !$user->hasAnyRole(['Super Admin', 'Owner'])) {
            return response()->json(['success' => false, 'message' => 'Pesanan dengan status "' . $order->status . '" hanya dapat dibatalkan oleh Super Admin.'], 403);
        }

        if ($order->status === 'Completed') {
            return response()->json(['success' => false, 'message' => 'Pesanan yang sudah selesai (Completed) tidak dapat dibatalkan.'], 422);
        }

        if ($order->status === 'Cancelled') {
            return response()->json(['success' => false, 'message' => 'Pesanan ini sudah berstatus dibatalkan.'], 422);
        }

        // Cek apakah sudah tercatat pada Faktur Penjualan (Invoice) yang aktif
        $activeInvoice = $order->getActiveInvoice();
        if ($activeInvoice) {
            return response()->json([
                'success' => false,
                'message' => "Pesanan tidak dapat dibatalkan karena sudah tercatat pada Faktur Penjualan ({$activeInvoice->nomor_faktur}). Harap batalkan Faktur Penjualan tersebut terlebih dahulu."
            ], 422);
        }

        // Cek apakah ada Surat Jalan (DO) yang aktif
        $activeDo = $order->deliveryOrders()->where('status', '!=', 'Dibatalkan')->first();
        if ($activeDo) {
            return response()->json([
                'success' => false,
                'message' => "Pesanan tidak dapat dibatalkan karena memiliki Surat Jalan (DO) aktif dengan nomor {$activeDo->nomor_do} (Status: {$activeDo->status}). Harap batalkan Surat Jalan tersebut terlebih dahulu."
            ], 422);
        }

        $request->validate([
            'alasan_pembatalan' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $order->update([
                'status'            => 'Cancelled',
                'alasan_pembatalan' => $request->alasan_pembatalan,
                'updated_by'        => $user->id,
            ]);

            $infoQuotation = '';
            // Cek apakah SO ini berasal dari Surat Penawaran (Sales Quotation)
            $quotation = $order->quotation;
            if (!$quotation && !empty($order->sales_quotation_id)) {
                $quotation = SalesQuotation::find($order->sales_quotation_id);
            }
            if (!$quotation) {
                $quotation = SalesQuotation::where('sales_order_id', $order->id)->first();
            }

            if ($quotation) {
                $userName = $user->name ?? 'User';
                $cancelNote = "\n[Pembatalan SO {$order->nomor_so} pada " . now()->format('d/m/Y H:i') . " oleh {$userName}]: Alasan - " . $request->alasan_pembatalan;

                $quotation->update([
                    'status'         => 'Draft',
                    'sales_order_id' => null,
                    'catatan'        => ($quotation->catatan ?? '') . $cancelNote,
                    'updated_by'     => $user->id,
                ]);

                // Lepas keterikatan tender jika SO ini terhubung ke tender
                $tender = $order->tender ?: ($quotation->tender_id ? Tender::find($quotation->tender_id) : Tender::where('sales_order_id', $order->id)->first());
                if ($tender && $tender->sales_order_id == $order->id) {
                    $tender->update([
                        'sales_order_id' => null,
                    ]);
                }

                $infoQuotation = " Surat Penawaran terkait ({$quotation->nomor_quotation}) telah dibuka kembali ke status Draft sehingga dapat diperbaiki atau diedit ulang.";
            }

            DB::commit();

            return response()->json([
                'success'            => true,
                'message'            => "Pesanan {$order->nomor_so} berhasil dibatalkan.{$infoQuotation}",
                'quotation_reopened' => $quotation ? true : false,
                'quotation_number'   => $quotation?->nomor_quotation,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print Invoice PDF View (Tab Baru)
     */
    public function printInvoicePdf(Request $request, SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('cetak-sales-order') && !$user->can('print-sales-order')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak faktur pesanan.');
        }

        if ($entityErr = $this->checkOrderEntityAccess($order, $user)) {
            abort(403, $entityErr);
        }

        if ($order->status === 'Cancelled') {
            abort(403, 'Pesanan ini telah dibatalkan sehingga faktur tidak dapat dicetak.');
        }

        $order->load([
            'company',
            'branch.company',
            'customer.defaultAddress',
            'quotation',
            'tender',
            'sales.department',
            'sales.position',
            'paymentTerm',
            'paymentMethod',
            'ppnPajak',
            'pphPajak',
            'creator.karyawan.position',
            'details.barang.satuan',
            'details.barang.primaryFoto',
            'details.barang.kategori',
            'details.satuan',
            'details.kategori',
            'details.ppnPajak',
        ]);

        $companies = \App\Models\Company::active()->get();
        if ($companies->isEmpty()) {
            $companies = \App\Models\Company::all();
        }

        // Cek asal input order: apakah diinput di Cabang (branch_id terisi) atau PT Pusat
        $isBranch = !empty($order->branch_id);
        $branch = null;
        $company = null;

        if ($isBranch) {
            $branch = $order->branch ?: \App\Models\Branch::with('company')->find($order->branch_id);
            $company = $branch?->company ?: ($order->company ?: $companies->first());
            $entityName = $branch?->nama_cabang ?: '';
            $parentName = '';
            $alamatParts = array_filter([
                $branch?->address ?: ($branch?->alamat ?? ''),
                $branch?->city ?: ($branch?->kota ?? ''),
                $branch?->province ?: ($branch?->provinsi ?? ''),
                $branch?->postal_code ?: ($branch?->kode_pos ?? '')
            ]);
            $entityAddress = implode(', ', $alamatParts);
            $entityPhone   = $branch?->phone ?: ($branch?->telepon ?? '');
            $entityEmail   = $branch?->email ?: '';
            $entityNpwp    = '';
        } else {
            $selectedCompanyId = $request->query('company_id', $order->company_id ?? auth()->user()?->company_id);
            $company = $companies->firstWhere('id', $selectedCompanyId)
                ?? \App\Models\Company::find($selectedCompanyId)
                ?? $order->company
                ?? $companies->first()
                ?? \App\Models\Company::first();
            $entityName = $company?->nama_perusahaan ?? '';
            $parentName = '';
            $alamatParts = array_filter([
                $company?->address ?: ($company?->alamat ?? ''),
                $company?->city ?: ($company?->kota ?? ''),
                $company?->province ?: ($company?->provinsi ?? ''),
                $company?->postal_code ?: ($company?->kode_pos ?? '')
            ]);
            $entityAddress = implode(', ', $alamatParts);
            $entityPhone   = $company?->phone ?: ($company?->telepon ?? '');
            $entityEmail   = $company?->email ?: '';
            $entityNpwp    = $company?->npwp ?: '';
        }

        $activeLogo = null;
        if ($isBranch && $branch) {
            if (!empty($branch->logo)) {
                if (file_exists(public_path('storage/' . $branch->logo))) {
                    $activeLogo = asset('storage/' . $branch->logo);
                } elseif (file_exists(public_path($branch->logo))) {
                    $activeLogo = asset($branch->logo);
                }
            }
            if (!$activeLogo && $company && !empty($company->logo)) {
                if (file_exists(public_path('storage/' . $company->logo))) {
                    $activeLogo = asset('storage/' . $company->logo);
                } elseif (file_exists(public_path($company->logo))) {
                    $activeLogo = asset($company->logo);
                }
            }
        } else {
            if ($company && !empty($company->logo)) {
                if (file_exists(public_path('storage/' . $company->logo))) {
                    $activeLogo = asset('storage/' . $company->logo);
                } elseif (file_exists(public_path($company->logo))) {
                    $activeLogo = asset($company->logo);
                }
            }
        }

        if (!$activeLogo) {
            $activeLogo = asset('gambar/logo.png');
        }

        $effectiveTotal = (float)($order->grand_total > 0 ? $order->grand_total : $order->total);
        $terbilang = $this->terbilang($effectiveTotal) . ' Rupiah';

        return view('modul_marketing.order.print_faktur', compact(
            'order',
            'company',
            'branch',
            'companies',
            'isBranch',
            'entityName',
            'parentName',
            'entityAddress',
            'entityPhone',
            'entityEmail',
            'entityNpwp',
            'activeLogo',
            'terbilang'
        ));
    }

    /**
     * Print Thermal Receipt
     */
    public function printThermalReceipt(SalesOrder $order)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('cetak-sales-order') && !$user->can('print-sales-order')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak struk.');
        }

        if ($entityErr = $this->checkOrderEntityAccess($order, $user)) {
            abort(403, $entityErr);
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

    /**
     * Helper to enforce strict multi-tenancy & branch isolation
     */
    private function checkOrderEntityAccess(SalesOrder $order, $user): ?string
    {
        if ($user->hasAnyRole(['Super Admin', 'Owner'])) {
            return null;
        }

        $activeCompanyId = session('active_company_id') ?: ($user->company_id ?? 1);
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeBranchId)) {
            if ($order->branch_id != $activeBranchId) {
                return 'Pesanan ini bukan milik Cabang aktif Anda.';
            }
        } elseif (!empty($activeCompanyId)) {
            // Sesi PT Pusat Aktif
            if (!empty($order->branch_id)) {
                $orderBranch = $order->branch ?: Branch::find($order->branch_id);
                if ($orderBranch && $orderBranch->company_id != $activeCompanyId) {
                    return 'Pesanan ini bukan milik unit usaha / perusahaan aktif Anda.';
                }
            } elseif (!empty($order->company_id) && $order->company_id != $activeCompanyId) {
                return 'Pesanan ini bukan milik Perusahaan aktif Anda.';
            }
        }

        return null;
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
}
