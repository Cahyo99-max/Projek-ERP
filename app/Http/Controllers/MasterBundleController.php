<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\MasterBarangBundle;
use App\Models\MasterKategoriBarang;
use App\Models\MasterBrand;
use App\Models\MasterSatuan;
use App\Models\MasterTipeBarang;
use App\Models\StokGudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MasterBundleController extends Controller
{
    /**
     * Display Master Bundle Produk index page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && 
            !$user->can('lihat-bundle') && !$user->can('tambah-bundle')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka Master Bundle Produk.');
        }

        $allowedBundleKategoriIds = MasterKategoriBarang::forActiveCompany()
            ->where('status', 'Active')
            ->where('tampil_di_bundle', true)
            ->pluck('id');

        $kategoriOptions = MasterKategoriBarang::forActiveCompany()
            ->where('status', 'Active')
            ->where('tampil_di_bundle', true)
            ->orderBy('nama_kategori', 'asc')
            ->get();

        $brands = MasterBrand::forActiveCompany()
            ->where('status', 'Active')
            ->orderBy('nama_brand', 'asc')
            ->get();

        $satuans = MasterSatuan::forActiveCompany()
            ->orderBy('nama_satuan', 'asc')
            ->get();

        $tipes = MasterTipeBarang::forActiveCompany()
            ->where('status', 'Active')
            ->orderBy('nama_tipe', 'asc')
            ->get();

        // Summary Counts for Cards
        $companyId = session('active_company_id');
        $queryBase = MasterBarang::forActiveCompany()->where('tipe_item', 'Bundle');

        $totalBundleCount = (clone $queryBase)->count();
        $totalActiveBundle = (clone $queryBase)->where('status', 'Active')->count();

        // Items available to be included as components (Filtered by allowed bundle categories)
        // Eager-load stokGudangs to get real HPP (harga_pokok_rata_rata / harga_beli_terakhir) and stok aktual per gudang
        $availableComponents = MasterBarang::forActiveCompany()
            ->whereIn('tipe_item', ['Stok', 'Barang Jadi'])
            ->where('status', 'Active')
            ->whereIn('kategori_barang_id', $allowedBundleKategoriIds)
            ->with(['satuan', 'kategori', 'brand', 'fotos', 'stokGudangs.gudang'])
            ->orderBy('nama_barang', 'asc')
            ->get()
            ->map(function ($b) {
                $b->stok_total      = $this->resolveChildStock($b);
                $b->hpp_dari_gudang = $this->resolveChildHpp($b);
                return $b;
            });

        // Warehouses list for Assembly Transaction (Strictly scoped: PT Pusat = cabang_id NULL, Cabang = cabang_id matching)
        $activeBranchId = session('active_branch_id');
        $gudangsQuery = \App\Models\MasterGudang::forActiveCompany()
            ->where('status', 'Active');

        if ($activeBranchId) {
            $gudangsQuery->where('cabang_id', $activeBranchId);
        } else {
            $gudangsQuery->whereNull('cabang_id');
        }

        $gudangs = $gudangsQuery->orderBy('nama_gudang', 'asc')->get();

        // Active Bundles list for Assembly Modal
        $activeBundles = MasterBarang::forActiveCompany()
            ->where('tipe_item', 'Bundle')
            ->where('status', 'Active')
            ->orderBy('nama_barang', 'asc')
            ->get();

        return view('data_master.bundle.index', compact(
            'kategoriOptions',
            'brands',
            'satuans',
            'tipes',
            'totalBundleCount',
            'totalActiveBundle',
            'availableComponents',
            'gudangs',
            'activeBundles'
        ));
    }

    /**
     * DataTables JSON server-side endpoint.
     */
    public function getData(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-bundle')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = MasterBarang::forActiveCompany()
            ->where('tipe_item', 'Bundle')
            ->with([
                'kategori',
                'brand',
                'satuan',
                'fotos',
                'bundleComponents.childBarang.satuan',
                'bundleComponents.childBarang.stokGudangs.gudang'
            ])
            ->orderBy('id', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori_id') && $request->kategori_id !== 'all') {
            $query->where('kategori_barang_id', $request->kategori_id);
        }

        if ($request->filled('satuan_id') && $request->satuan_id !== 'all') {
            $query->where('satuan_id', $request->satuan_id);
        }

        if ($request->filled('brand_id') && $request->brand_id !== 'all') {
            $query->where('brand_id', $request->brand_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('bundle_info', function ($b) {
                $fotoPath = $b->fotos->first()?->foto_path;
                $imgHtml = $fotoPath 
                    ? '<img src="' . asset('storage/' . $fotoPath) . '" class="rounded-3 border shadow-xs me-3 flex-shrink-0" style="width:48px; height:48px; object-fit:cover;">'
                    : '<div class="rounded-3 border bg-secondary bg-opacity-10 me-3 d-inline-flex align-items-center justify-content-center text-muted flex-shrink-0" style="width:48px; height:48px;"><i class="bi bi-boxes fs-4 text-info"></i></div>';

                $katNama = $b->kategori ? $b->kategori->nama_kategori : '-';
                $brandNama = $b->brand ? $b->brand->nama_brand : '-';
                $satNama = $b->satuan ? $b->satuan->singkatan : 'Set';

                return '
                    <div class="d-flex align-items-center py-1">
                        ' . $imgHtml . '
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center gap-1.5 mb-1">
                                <span class="badge spk-badge-bundle-code font-monospace px-2 py-0.5"><i class="bi bi-qr-code me-1"></i>' . e($b->kode_barang) . '</span>
                                <span class="badge spk-badge-bundle-unit font-monospace px-1.5 py-0.5">' . e($satNama) . '</span>
                            </div>
                            <span class="fw-bold spk-bundle-title" style="font-size:14px;">' . e($b->nama_barang) . '</span>
                            <small class="spk-bundle-meta" style="font-size:11.5px;"><i class="bi bi-folder2-open me-1 text-info"></i>' . e($katNama) . ' &bull; <span class="opacity-75">' . e($brandNama) . '</span></small>
                        </div>
                    </div>
                ';
            })
            ->addColumn('harga_jual', function ($b) {
                $retail = (float)($b->harga_jual_retail ?? 0);
                return '
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-success font-monospace fs-6" style="text-shadow: 0 0 10px rgba(16, 185, 129, 0.2);">Rp ' . number_format($retail, 0, ',', '.') . '</span>
                        <small class="spk-bundle-meta" style="font-size:11px;"><i class="bi bi-tag-fill me-1 text-success"></i>Harga Retail / Satuan</small>
                    </div>
                ';
            })
            ->addColumn('komposisi', function ($b) {
                $count = MasterBarangBundle::where('parent_barang_id', $b->id)->count();
                if ($count === 0) {
                    return '<span class="badge bg-secondary bg-opacity-20 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1 font-monospace">Kosong</span>';
                }
                return '
                    <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 font-monospace fw-bold shadow-xs spk-btn-glow" onclick="showBundleDetail(' . $b->id . ')" title="Klik untuk melihat rincian komponen & skema harga">
                        <i class="bi bi-boxes me-1"></i>' . $count . ' Komponen
                    </button>
                ';
            })
            ->addColumn('estimasi_stok_bundle', function ($b) {
                // Calculate max assembleable bundle packs based on lowest child component stock
                $bundleItems = $b->bundleComponents;
                if ($bundleItems->isEmpty()) {
                    return '<span class="badge bg-secondary bg-opacity-20 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1 font-monospace">0 Set</span>';
                }

                $maxBundlesPossible = PHP_INT_MAX;
                foreach ($bundleItems as $item) {
                    $qtyNeeded = (float)$item->qty;
                    if ($qtyNeeded <= 0) continue;

                    $totalChildStock = (float)($item->childBarang
                        ? $this->resolveChildStock($item->childBarang)
                        : 0);
                    $possibleForThisChild = floor($totalChildStock / $qtyNeeded);

                    if ($possibleForThisChild < $maxBundlesPossible) {
                        $maxBundlesPossible = $possibleForThisChild;
                    }
                }

                if ($maxBundlesPossible === PHP_INT_MAX) $maxBundlesPossible = 0;

                $satNama = $b->satuan ? $b->satuan->nama_satuan : 'Set';
                
                if ($maxBundlesPossible > 0) {
                    return '
                        <span class="badge rounded-pill px-3 py-1.5 font-monospace fw-bold shadow-xs" style="background: rgba(14, 165, 233, 0.18); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); font-size: 11.5px;">
                            <i class="bi bi-box-seam me-1"></i>' . number_format($maxBundlesPossible, 0, ',', '.') . ' ' . e($satNama) . ' Siap Dirakit
                        </span>
                    ';
                } else {
                    return '
                        <span class="badge rounded-pill px-3 py-1.5 font-monospace fw-bold shadow-xs" style="background: rgba(239, 68, 68, 0.18); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.35); font-size: 11.5px;">
                            <i class="bi bi-exclamation-triangle me-1"></i>0 ' . e($satNama) . ' (Komponen Habis)
                        </span>
                    ';
                }
            })
            ->addColumn('status', function ($b) {
                if ($b->status === 'Active') {
                    return '<span class="badge rounded-pill px-3 py-1.5 font-monospace fw-bold shadow-xs" style="background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.35); font-size: 11px;"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>';
                } else {
                    return '<span class="badge rounded-pill px-3 py-1.5 font-monospace fw-bold shadow-xs" style="background: rgba(148, 163, 184, 0.18); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.35); font-size: 11px;"><i class="bi bi-x-circle-fill me-1"></i>Nonaktif</span>';
                }
            })
            ->addColumn('action', function ($b) {
                $user = auth()->user();
                $isSuperOrOwner = $user->hasAnyRole(['Super Admin', 'Owner']);
                
                $html = '<div class="d-flex align-items-center justify-content-center gap-1.5">';
                $html .= '<button type="button" class="btn btn-sm btn-outline-info rounded-circle p-1.5 shadow-xs spk-action-btn" onclick="showBundleDetail(' . $b->id . ')" title="Lihat Rincian Lengkap & Harga"><i class="bi bi-eye-fill"></i></button>';

                if ($isSuperOrOwner || $user->can('edit-bundle')) {
                    $html .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-circle p-1.5 shadow-xs spk-action-btn" onclick="editBundleModal(' . $b->id . ')" title="Edit Master Bundle"><i class="bi bi-pencil-square"></i></button>';
                }
                if ($isSuperOrOwner || $user->can('hapus-bundle')) {
                    $html .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1.5 shadow-xs spk-action-btn" onclick="deleteBundle(' . $b->id . ', \'' . e($b->nama_barang) . '\')" title="Hapus Master Bundle"><i class="bi bi-trash-fill"></i></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['bundle_info', 'harga_jual', 'komposisi', 'estimasi_stok_bundle', 'status', 'action'])
            ->make(true);
    }

    /**
     * Store new Master Bundle & Components.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('tambah-bundle')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menambah Master Bundle (tambah-bundle).'], 403);
        }

        $validated = $request->validate([
            'kode_barang'        => 'nullable|string|max:20',
            'nama_barang'        => 'required|string|max:150',
            'nama_alias'         => 'nullable|string|max:100',
            'kategori_barang_id' => 'required|exists:master_kategori_barang,id',
            'brand_id'           => 'nullable|exists:master_brand,id',
            'tipe_barang_id'     => 'required|exists:master_tipe_barang,id',
            'satuan_id'          => 'required|exists:master_satuan,id',
            'harga_jual_retail'  => 'nullable|numeric|min:0',
            'harga_jual_grosir'  => 'nullable|numeric|min:0',
            'harga_jual_tender'  => 'nullable|numeric|min:0',
            'spesifikasi'        => 'nullable|string|max:500',
            'status'             => 'required|in:Active,Inactive',
            'komponen'           => 'required|array|min:1|max:100',
            'komponen.*.child_id'=> 'required|exists:master_barang,id',
            'komponen.*.qty'     => 'required|numeric|min:0.01',
            'fotos'              => 'nullable|array|max:4',
            'fotos.*'            => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'nama_barang.required'        => 'Nama Paket Bundle Wajib diisi!',
            'kategori_barang_id.required' => 'Kategori Paket Wajib dipilih!',
            'satuan_id.required'          => 'Satuan Paket Wajib dipilih!',
            'komponen.required'           => 'Minimal 1 barang penyusun paket WAJIB ditambahkan!',
            'komponen.min'                => 'Minimal 1 barang penyusun paket WAJIB ditambahkan!',
            'komponen.max'                => 'Maksimal barang penyusun dalam 1 paket bundle adalah 100 item!',
        ]);

        try {
            DB::beginTransaction();

            // Auto-generate code if empty
            $kodeBarang = $validated['kode_barang'];
            if (empty($kodeBarang)) {
                $kodeBarang = \App\Helpers\CodeGeneratorHelper::generate('master_barang', 'BDL-', 'kode_barang');
            }

            $activeBranchId = session('active_branch_id');
            $locationLabel = $activeBranchId ? 'Cabang' : 'PT Pusat';

            $totalBundleHpp = 0.0;

            // Validasi Keamanan & HPP Ketat: Pastikan semua komponen terdaftar di PT & memiliki HPP > 0 di entitas ini
            foreach ($validated['komponen'] as $comp) {
                $childItem = MasterBarang::forActiveCompany()
                    ->with(['stokGudangs.gudang'])
                    ->find($comp['child_id']);

                if (!$childItem) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Barang komponen tidak ditemukan di database.'
                    ], 422);
                }

                $childHpp = $this->resolveChildHpp($childItem);
                if ($childHpp <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal Menyimpan: Komponen '{$childItem->nama_barang}' ({$childItem->kode_barang}) belum memiliki nilai HPP (Rp 0) di {$locationLabel}. Silakan lakukan penerimaan/pembelian stok komponen di {$locationLabel} terlebih dahulu!"
                    ], 422);
                }

                $totalBundleHpp += ($childHpp * (float)$comp['qty']);
            }

            // Validasi Margin Rugi (HPP vs Harga Jual)
            $hargaRetail = (float)($validated['harga_jual_retail'] ?? 0);
            $hargaGrosir = (float)($validated['harga_jual_grosir'] ?? 0);
            $hargaTender = (float)($validated['harga_jual_tender'] ?? 0);

            $invalidTiers = [];
            if ($hargaRetail > 0 && $hargaRetail < $totalBundleHpp) {
                $rugiRp = number_format($totalBundleHpp - $hargaRetail, 0, ',', '.');
                $invalidTiers[] = "Harga Retail (Rp " . number_format($hargaRetail, 0, ',', '.') . " - Rugi Rp {$rugiRp})";
            }

            if ($hargaGrosir > 0 && $hargaGrosir < $totalBundleHpp) {
                $rugiRp = number_format($totalBundleHpp - $hargaGrosir, 0, ',', '.');
                $invalidTiers[] = "Harga Grosir (Rp " . number_format($hargaGrosir, 0, ',', '.') . " - Rugi Rp {$rugiRp})";
            }

            if ($hargaTender > 0 && $hargaTender < $totalBundleHpp) {
                $rugiRp = number_format($totalBundleHpp - $hargaTender, 0, ',', '.');
                $invalidTiers[] = "Harga Tender (Rp " . number_format($hargaTender, 0, ',', '.') . " - Rugi Rp {$rugiRp})";
            }

            if (!empty($invalidTiers)) {
                DB::rollBack();
                $hppFormatted = number_format($totalBundleHpp, 0, ',', '.');
                $tierListStr = implode(', ', $invalidTiers);
                return response()->json([
                    'success' => false,
                    'message' => "Gagal Menyimpan: Terdeteksi MARGIN RUGI! Total HPP Bundle adalah Rp {$hppFormatted}. Skema harga jual berikut lebih rendah dari HPP: {$tierListStr}. Silakan naikkan harga jual atau sesuaikan komposisi!"
                ], 422);
            }

            $bundle = MasterBarang::create([
                'company_id'         => session('active_company_id'),
                'kode_barang'        => $kodeBarang,
                'nama_barang'        => $validated['nama_barang'],
                'nama_alias'         => $validated['nama_alias'] ?? null,
                'kategori_barang_id' => $validated['kategori_barang_id'],
                'brand_id'           => $validated['brand_id'] ?? null,
                'tipe_barang_id'     => $validated['tipe_barang_id'],
                'satuan_id'          => $validated['satuan_id'],
                'tipe_item'          => 'Bundle',
                'harga_jual_retail'  => $validated['harga_jual_retail'] ?? 0,
                'harga_jual_grosir'  => $validated['harga_jual_grosir'] ?? 0,
                'harga_jual_tender'  => $validated['harga_jual_tender'] ?? 0,
                'spesifikasi'        => $validated['spesifikasi'] ?? null,
                'status'             => $validated['status'],
                'created_by'         => auth()->id(),
            ]);

            // Save components
            foreach ($validated['komponen'] as $comp) {
                MasterBarangBundle::create([
                    'parent_barang_id' => $bundle->id,
                    'child_barang_id'  => $comp['child_id'],
                    'qty'              => (float)$comp['qty'],
                ]);
            }

            // Save multi-photos for bundle
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $index => $file) {
                    $path = $file->store('barang', 'public');
                    $bundle->fotos()->create([
                        'foto_path'  => $path,
                        'is_primary' => $index === 0,
                        'urutan'     => $index,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Master Bundle Paket '{$bundle->nama_barang}' ({$bundle->kode_barang}) berhasil dibuat!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Master Bundle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detailed bundle information & schema modal.
     */
    public function show(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('lihat-bundle')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $gudangId = $request->query('gudang_id');
        if ($gudangId) {
            $validGudang = \App\Models\MasterGudang::forActiveCompany()->find($gudangId);
            if (!$validGudang) {
                $gudangId = null;
            }
        }

        $bundle = MasterBarang::forActiveCompany()
            ->where('tipe_item', 'Bundle')
            ->with([
                'kategori',
                'brand',
                'satuan',
                'fotos'
            ])
            ->findOrFail($id);

        $totalRealtimeHpp = 0;
        $components = MasterBarangBundle::where('parent_barang_id', $bundle->id)
            ->with(['childBarang.satuan', 'childBarang.kategori', 'childBarang.primaryFoto', 'childBarang.fotos', 'childBarang.stokGudangs.gudang'])
            ->get()
            ->map(function($comp) use ($gudangId, &$totalRealtimeHpp) {
                $child = $comp->childBarang;

                $stockInWarehouses = $this->resolveChildStock($child, $gudangId);
                $childHpp          = $this->resolveChildHpp($child, $gudangId);

                $subtotalHpp = $childHpp * (float)$comp->qty;
                $totalRealtimeHpp += $subtotalHpp;

                $fotoUrl = $child->primaryFoto ? asset('storage/' . $child->primaryFoto->foto_path) : ($child->fotos->first() ? asset('storage/' . $child->fotos->first()->foto_path) : null);

                return [
                    'id'                  => $comp->id,
                    'child_id'            => $comp->child_barang_id,
                    'kode_barang'         => $child ? $child->kode_barang : '-',
                    'nama_barang'         => $child ? $child->nama_barang : '-',
                    'kategori'            => ($child && $child->kategori) ? $child->kategori->nama_kategori : '-',
                    'satuan'              => ($child && $child->satuan) ? ($child->satuan->singkatan ?: $child->satuan->nama_satuan) : 'Pcs',
                    'hpp_satuan'          => $childHpp,
                    'qty_per_bundle'      => (float)$comp->qty,
                    'subtotal_hpp'        => $subtotalHpp,
                    'stok_aktual_gudang'  => $stockInWarehouses,
                    'foto_url'            => $fotoUrl,
                    'possible_bundles'    => (float)$comp->qty > 0 ? floor($stockInWarehouses / (float)$comp->qty) : 0,
                ];
            });

        $maxAssembleable = $components->isEmpty() ? 0 : $components->min('possible_bundles');

        $payload = [
            'id'                => $bundle->id,
            'kode_barang'       => $bundle->kode_barang,
            'nama_barang'       => $bundle->nama_barang,
            'nama_alias'        => $bundle->nama_alias,
            'kategori_nama'     => $bundle->kategori ? $bundle->kategori->nama_kategori : '-',
            'brand_nama'        => $bundle->brand ? $bundle->brand->nama_brand : '-',
            'satuan_nama'       => $bundle->satuan ? $bundle->satuan->nama_satuan : 'Set',
            'realtime_hpp'      => $totalRealtimeHpp,
            'harga_jual_retail' => (float)($bundle->harga_jual_retail ?? 0),
            'harga_jual_grosir' => (float)($bundle->harga_jual_grosir ?? 0),
            'harga_jual_tender' => (float)($bundle->harga_jual_tender ?? 0),
            'spesifikasi'       => $bundle->spesifikasi,
            'status'            => $bundle->status,
            'foto_url'          => $bundle->primaryFoto ? asset('storage/' . $bundle->primaryFoto->foto_path) : ($bundle->fotos->first() ? asset('storage/' . $bundle->fotos->first()->foto_path) : null),
            'fotos'             => $bundle->fotos->map(fn($f) => ['id' => $f->id, 'url' => asset('storage/' . $f->foto_path), 'is_primary' => $f->is_primary]),
            'max_assembleable'  => $maxAssembleable,
            'components'        => $components
        ];

        return response()->json([
            'success' => true,
            'data'    => $payload,
            'bundle'  => $payload
        ]);
    }

    /**
     * Edit bundle data.
     */
    public function edit($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-bundle')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengedit Master Bundle (edit-bundle).'], 403);
        }

        $bundle = MasterBarang::forActiveCompany()
            ->where('tipe_item', 'Bundle')
            ->with(['fotos', 'kategori', 'brand', 'satuan', 'tipeBarang'])
            ->findOrFail($id);

        $components = MasterBarangBundle::where('parent_barang_id', $bundle->id)
            ->with(['childBarang.satuan', 'childBarang.stokGudangs.gudang'])
            ->get()
            ->map(function($comp) {
                $child = $comp->childBarang;
                $childHpp = $this->resolveChildHpp($child);

                return [
                    'id'             => $comp->id,
                    'child_id'       => $comp->child_barang_id,
                    'kode_barang'    => $child ? $child->kode_barang : '',
                    'nama_barang'    => $child ? $child->nama_barang : '',
                    'satuan'         => ($child && $child->satuan) ? ($child->satuan->singkatan ?: $child->satuan->nama_satuan) : 'Pcs',
                    'hpp_satuan'     => $childHpp,
                    'qty_per_bundle' => (float)$comp->qty,
                ];
            });

        return response()->json([
            'success' => true,
            'bundle' => $bundle,
            'components' => $components
        ]);
    }

    /**
     * Update Master Bundle & Components.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('edit-bundle')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengedit Master Bundle (edit-bundle).'], 403);
        }

        $bundle = MasterBarang::forActiveCompany()
            ->where('tipe_item', 'Bundle')
            ->findOrFail($id);

        $validated = $request->validate([
            'kode_barang'        => 'required|string|max:20',
            'nama_barang'        => 'required|string|max:150',
            'nama_alias'         => 'nullable|string|max:100',
            'kategori_barang_id' => 'required|exists:master_kategori_barang,id',
            'brand_id'           => 'nullable|exists:master_brand,id',
            'tipe_barang_id'     => 'required|exists:master_tipe_barang,id',
            'satuan_id'          => 'required|exists:master_satuan,id',
            'harga_jual_retail'  => 'nullable|numeric|min:0',
            'harga_jual_grosir'  => 'nullable|numeric|min:0',
            'harga_jual_tender'  => 'nullable|numeric|min:0',
            'spesifikasi'        => 'nullable|string|max:500',
            'status'             => 'required|in:Active,Inactive',
            'komponen'           => 'required|array|min:1|max:100',
            'komponen.*.child_id'=> 'required|exists:master_barang,id',
            'komponen.*.qty'     => 'required|numeric|min:0.01',
            'fotos'              => 'nullable|array|max:4',
            'fotos.*'            => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'nama_barang.required'        => 'Nama Paket Bundle Wajib diisi!',
            'kategori_barang_id.required' => 'Kategori Paket Wajib dipilih!',
            'satuan_id.required'          => 'Satuan Paket Wajib dipilih!',
            'komponen.required'           => 'Minimal 1 barang penyusun paket WAJIB ditambahkan!',
            'komponen.min'                => 'Minimal 1 barang penyusun paket WAJIB ditambahkan!',
            'komponen.max'                => 'Maksimal barang penyusun dalam 1 paket bundle adalah 100 item!',
        ]);

        try {
            DB::beginTransaction();

            $bundle->update([
                'kode_barang'        => $validated['kode_barang'],
                'nama_barang'        => $validated['nama_barang'],
                'nama_alias'         => $validated['nama_alias'] ?? null,
                'kategori_barang_id' => $validated['kategori_barang_id'],
                'brand_id'           => $validated['brand_id'] ?? null,
                'tipe_barang_id'     => $validated['tipe_barang_id'],
                'satuan_id'          => $validated['satuan_id'],
                'harga_jual_retail'  => $validated['harga_jual_retail'] ?? 0,
                'harga_jual_grosir'  => $validated['harga_jual_grosir'] ?? 0,
                'harga_jual_tender'  => $validated['harga_jual_tender'] ?? 0,
                'spesifikasi'        => $validated['spesifikasi'] ?? null,
                'status'             => $validated['status'],
                'updated_by'         => auth()->id(),
            ]);

            $activeBranchId = session('active_branch_id');
            $locationLabel = $activeBranchId ? 'Cabang' : 'PT Pusat';

            $totalBundleHpp = 0.0;

            // Validasi Keamanan & HPP Ketat: Pastikan semua komponen terdaftar di PT & memiliki HPP > 0 di entitas ini
            foreach ($validated['komponen'] as $comp) {
                $childItem = MasterBarang::forActiveCompany()
                    ->with(['stokGudangs.gudang'])
                    ->find($comp['child_id']);

                if (!$childItem) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Barang komponen tidak ditemukan di database.'
                    ], 422);
                }

                $childHpp = $this->resolveChildHpp($childItem);
                if ($childHpp <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal Perbarui: Komponen '{$childItem->nama_barang}' ({$childItem->kode_barang}) belum memiliki nilai HPP (Rp 0) di {$locationLabel}. Silakan lakukan penerimaan/pembelian stok komponen di {$locationLabel} terlebih dahulu!"
                    ], 422);
                }

                $totalBundleHpp += ($childHpp * (float)$comp['qty']);
            }

            // Validasi Margin Rugi (HPP vs Harga Jual)
            $hargaRetail = (float)($validated['harga_jual_retail'] ?? 0);
            $hargaGrosir = (float)($validated['harga_jual_grosir'] ?? 0);
            $hargaTender = (float)($validated['harga_jual_tender'] ?? 0);

            $invalidTiers = [];
            if ($hargaRetail > 0 && $hargaRetail < $totalBundleHpp) {
                $rugiRp = number_format($totalBundleHpp - $hargaRetail, 0, ',', '.');
                $invalidTiers[] = "Harga Retail (Rp " . number_format($hargaRetail, 0, ',', '.') . " - Rugi Rp {$rugiRp})";
            }

            if ($hargaGrosir > 0 && $hargaGrosir < $totalBundleHpp) {
                $rugiRp = number_format($totalBundleHpp - $hargaGrosir, 0, ',', '.');
                $invalidTiers[] = "Harga Grosir (Rp " . number_format($hargaGrosir, 0, ',', '.') . " - Rugi Rp {$rugiRp})";
            }

            if ($hargaTender > 0 && $hargaTender < $totalBundleHpp) {
                $rugiRp = number_format($totalBundleHpp - $hargaTender, 0, ',', '.');
                $invalidTiers[] = "Harga Tender (Rp " . number_format($hargaTender, 0, ',', '.') . " - Rugi Rp {$rugiRp})";
            }

            if (!empty($invalidTiers)) {
                DB::rollBack();
                $hppFormatted = number_format($totalBundleHpp, 0, ',', '.');
                $tierListStr = implode(', ', $invalidTiers);
                return response()->json([
                    'success' => false,
                    'message' => "Gagal Perbarui: Terdeteksi MARGIN RUGI! Total HPP Bundle adalah Rp {$hppFormatted}. Skema harga jual berikut lebih rendah dari HPP: {$tierListStr}. Silakan naikkan harga jual atau sesuaikan komposisi!"
                ], 422);
            }

            // Replace component mappings
            MasterBarangBundle::where('parent_barang_id', $bundle->id)->delete();

            foreach ($validated['komponen'] as $comp) {
                MasterBarangBundle::create([
                    'parent_barang_id' => $bundle->id,
                    'child_barang_id'  => $comp['child_id'],
                    'qty'              => (float)$comp['qty'],
                ]);
            }

            // Save additional multi-photos for bundle
            if ($request->hasFile('fotos')) {
                $hasPrimary = $bundle->fotos()->where('is_primary', true)->exists();
                $startingIndex = $bundle->fotos()->count();

                foreach ($request->file('fotos') as $index => $file) {
                    $path = $file->store('barang', 'public');
                    $bundle->fotos()->create([
                        'foto_path'  => $path,
                        'is_primary' => !$hasPrimary && ($index === 0),
                        'urutan'     => $startingIndex + $index,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Master Bundle Paket '{$bundle->nama_barang}' berhasil diperbarui!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Master Bundle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Master Bundle.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('hapus-bundle')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk menghapus Master Bundle (hapus-bundle).'], 403);
        }

        $bundle = MasterBarang::forActiveCompany()
            ->where('tipe_item', 'Bundle')
            ->findOrFail($id);

        try {
            DB::beginTransaction();
            MasterBarangBundle::where('parent_barang_id', $bundle->id)->delete();
            $bundle->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Master Bundle Paket '{$bundle->nama_barang}' berhasil dihapus."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Master Bundle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Bundle Assembly / Kitting Transaction (Input Perakitan Paket Set).
     */
    public function rakitBundle(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && 
            !$user->can('rakit-bundle')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk merakit Paket Bundle (rakit-bundle).'], 403);
        }

        $validated = $request->validate([
            'parent_barang_id'   => 'required|exists:master_barang,id',
            'gudang_komponen_id' => 'required|exists:master_gudang,id',
            'gudang_bundle_id'   => 'required|exists:master_gudang,id',
            'jumlah_paket'       => 'required|numeric|min:1|max:999999',
            'tanggal'            => 'required|date',
            'keterangan'         => 'nullable|string|max:255',
        ], [
            'parent_barang_id.required'   => 'Paket Bundle Wajib dipilih!',
            'gudang_komponen_id.required' => 'Gudang Asal Komponen Wajib dipilih!',
            'gudang_bundle_id.required'   => 'Gudang Tujuan Hasil Bundle Wajib dipilih!',
            'jumlah_paket.required'       => 'Jumlah Paket yang dirakit Wajib diisi!',
            'jumlah_paket.min'            => 'Jumlah paket minimal 1 Set!',
        ]);

        $bundle = MasterBarang::forActiveCompany()
            ->where('tipe_item', 'Bundle')
            ->findOrFail($validated['parent_barang_id']);

        $activeBranchId = session('active_branch_id');
        $locationLabel  = $activeBranchId ? 'Cabang' : 'PT Pusat';

        $gudangKomponen = \App\Models\MasterGudang::forActiveCompany()->find($validated['gudang_komponen_id']);
        if (!$gudangKomponen || ($activeBranchId ? $gudangKomponen->cabang_id != $activeBranchId : !is_null($gudangKomponen->cabang_id))) {
            return response()->json([
                'success' => false,
                'message' => "Gudang asal komponen tidak valid untuk entitas {$locationLabel} ini."
            ], 422);
        }

        $gudangBundle = \App\Models\MasterGudang::forActiveCompany()->find($validated['gudang_bundle_id']);
        if (!$gudangBundle || ($activeBranchId ? $gudangBundle->cabang_id != $activeBranchId : !is_null($gudangBundle->cabang_id))) {
            return response()->json([
                'success' => false,
                'message' => "Gudang tujuan bundle tidak valid untuk entitas {$locationLabel} ini."
            ], 422);
        }

        $komponens = MasterBarangBundle::where('parent_barang_id', $bundle->id)
            ->with(['childBarang.satuan'])
            ->get();

        if ($komponens->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Gagal: Paket Bundle '{$bundle->nama_barang}' belum memiliki rincian barang penyusun (komponen)!"
            ], 422);
        }

        $jumlahPaket = (float)$validated['jumlah_paket'];
        $nomorRakit = 'RKT-' . date('Y/m/') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        try {
            DB::beginTransaction();

            $totalBundleHppPerSet = 0.0;

            // 1. Validate component stock in origin warehouse & decrease stock + calculate valuation
            foreach ($komponens as $comp) {
                $child = $comp->childBarang;
                $qtyPerSet = (float)$comp->qty;
                $totalQtyNeeded = $qtyPerSet * $jumlahPaket;

                $stokComp = StokGudang::where('gudang_id', $validated['gudang_komponen_id'])
                    ->where('barang_id', $child->id)
                    ->lockForUpdate()
                    ->first();

                $currentStock = $stokComp ? (float)$stokComp->stok_aktual : 0;

                if ($currentStock < $totalQtyNeeded) {
                    $satNama = $child->satuan ? $child->satuan->singkatan : 'Pcs';
                    $maxPossible = floor($currentStock / $qtyPerSet);
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok gudang asal tidak mencukupi untuk komponen '{$child->nama_barang}'. Dibutuhkan {$totalQtyNeeded} {$satNama}, tersedia hanya {$currentStock} {$satNama} (Maksimal perakitan: {$maxPossible} Set)."
                    ], 422);
                }

                // Determine HPP of child component (strictly from origin warehouse)
                $hppChild = (float)($stokComp && (float)$stokComp->harga_pokok_rata_rata > 0 
                    ? $stokComp->harga_pokok_rata_rata 
                    : ($stokComp && (float)$stokComp->harga_beli_terakhir > 0 
                        ? $stokComp->harga_beli_terakhir 
                        : 0));

                if ($hppChild <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal Merakit: Komponen '{$child->nama_barang}' ({$child->kode_barang}) belum memiliki nilai HPP (Rp 0) di gudang asal '{$gudangKomponen->nama_gudang}'. Perakitan dibatalkan!"
                    ], 422);
                }
                $nilaiTotalChild = round($totalQtyNeeded * $hppChild, 4);
                $totalBundleHppPerSet += ($qtyPerSet * $hppChild);

                // Decrement stock and inventory valuation
                $stokAwal = $currentStock;
                $stokAkhir = $stokAwal - $totalQtyNeeded;
                $nilaiAwalChild = (float)($stokComp->nilai_persediaan ?: 0);
                $nilaiAkhirChild = max(0, round($nilaiAwalChild - $nilaiTotalChild, 4));

                $stokComp->update([
                    'stok_aktual'      => $stokAkhir,
                    'nilai_persediaan' => $nilaiAkhirChild,
                ]);
                StokGudang::syncMasterHpp($child->id);

                // Record KartuStok Out
                \App\Models\KartuStok::create([
                    'barang_id'       => $child->id,
                    'gudang_id'       => $validated['gudang_komponen_id'],
                    'tipe'            => 'Keluar',
                    'jumlah'          => $totalQtyNeeded,
                    'stok_awal'       => $stokAwal,
                    'stok_akhir'      => $stokAkhir,
                    'harga_satuan'    => $hppChild,
                    'nilai_total'     => $nilaiTotalChild,
                    'referensi_jenis' => 'Perakitan Bundle',
                    'referensi_nomor' => $nomorRakit,
                    'keterangan'      => "Pengeluaran komponen penyusun untuk perakitan " . number_format($jumlahPaket, 0, ',', '.') . " Set Paket {$bundle->nama_barang}",
                    'created_at'      => $validated['tanggal'] . ' ' . date('H:i:s'),
                ]);
            }

            // 2. Increase Parent Bundle Stock & Valuation in destination warehouse
            $stokBundle = StokGudang::where('gudang_id', $validated['gudang_bundle_id'])
                ->where('barang_id', $bundle->id)
                ->lockForUpdate()
                ->first();

            if (!$stokBundle) {
                $stokBundle = StokGudang::create([
                    'gudang_id'             => $validated['gudang_bundle_id'],
                    'barang_id'             => $bundle->id,
                    'stok_aktual'           => 0,
                    'nilai_persediaan'      => 0,
                    'harga_pokok_rata_rata' => $totalBundleHppPerSet,
                ]);
            }

            $totalNilaiBundleCreated = round($jumlahPaket * $totalBundleHppPerSet, 4);
            $stokAwalBundle = (float)($stokBundle->stok_aktual ?: 0);
            $stokAkhirBundle = $stokAwalBundle + $jumlahPaket;
            $nilaiAwalBundle = (float)($stokBundle->nilai_persediaan ?: 0);
            $nilaiAkhirBundle = round($nilaiAwalBundle + $totalNilaiBundleCreated, 4);
            $hppAvgBundleNew = $stokAkhirBundle > 0 ? round($nilaiAkhirBundle / $stokAkhirBundle, 6) : $totalBundleHppPerSet;

            $stokBundle->update([
                'stok_aktual'            => $stokAkhirBundle,
                'nilai_persediaan'       => $nilaiAkhirBundle,
                'harga_pokok_rata_rata'  => $hppAvgBundleNew,
            ]);
            StokGudang::syncMasterHpp($bundle->id);

            // Record KartuStok In
            \App\Models\KartuStok::create([
                'barang_id'       => $bundle->id,
                'gudang_id'       => $validated['gudang_bundle_id'],
                'tipe'            => 'Masuk',
                'jumlah'          => $jumlahPaket,
                'stok_awal'       => $stokAwalBundle,
                'stok_akhir'      => $stokAkhirBundle,
                'harga_satuan'    => $totalBundleHppPerSet,
                'nilai_total'     => $totalNilaiBundleCreated,
                'referensi_jenis' => 'Perakitan Bundle',
                'referensi_nomor' => $nomorRakit,
                'keterangan'      => $validated['keterangan'] ?: "Penerimaan stok hasil perakitan " . number_format($jumlahPaket, 0, ',', '.') . " Set Paket {$bundle->nama_barang}",
                'created_at'      => $validated['tanggal'] . ' ' . date('H:i:s'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil Merakit " . number_format($jumlahPaket, 0, ',', '.') . " Set Paket '{$bundle->nama_barang}'! Stok komponen telah dipotong & stok paket bundle bertambah.",
                'referensi_nomor' => $nomorRakit
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses perakitan bundle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the Category Visibility Cockpit (Setting Visibilitas Kategori)
     */
    public function pengaturanKategori(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('kelola-kategori-bundle')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengatur visibilitas kategori (kelola-kategori-bundle).');
        }

        $categories = MasterKategoriBarang::forActiveCompany()
            ->withCount([
                'barangs as total_barang_count',
                'barangs as barang_jadi_count' => function ($q) {
                    $q->whereIn('tipe_item', ['Stok', 'Barang Jadi']);
                },
                'barangs as bundle_count' => function ($q) {
                    $q->where('tipe_item', 'Bundle');
                }
            ])
            ->orderBy('nama_kategori', 'asc')
            ->get();

        // Calculate cockpit KPI stats
        $totalKategori = $categories->count();
        $totalActiveBundleKategori = $categories->where('tampil_di_bundle', true)->where('status', 'Active')->count();
        $totalActiveSalesKategori = $categories->where('tampil_di_sales', true)->where('status', 'Active')->count();
        $totalInternalKategori = $categories->where('tampil_di_bundle', false)->where('tampil_di_sales', false)->count();

        return view('data_master.bundle.pengaturan_kategori', compact(
            'categories',
            'totalKategori',
            'totalActiveBundleKategori',
            'totalActiveSalesKategori',
            'totalInternalKategori'
        ));
    }

    /**
     * Single toggle category visibility via AJAX
     */
    public function toggleKategoriVisibility(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('kelola-kategori-bundle')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengubah visibilitas kategori (kelola-kategori-bundle).'], 403);
        }

        $request->validate([
            'kategori_id' => 'required|exists:master_kategori_barang,id',
            'field'       => 'required|in:tampil_di_bundle,tampil_di_sales',
            'value'       => 'required|boolean',
        ]);

        try {
            $category = MasterKategoriBarang::forActiveCompany()->findOrFail($request->kategori_id);
            $field = $request->field;
            $category->{$field} = (bool)$request->value;
            $category->save();

            $targetName = $field === 'tampil_di_bundle' ? 'Master Bundle' : 'Modul Penjualan (Sales)';
            $statusText = $category->{$field} ? 'Aktif (Dapat Ditampilkan)' : 'Dinonaktifkan (Disembunyikan)';

            return response()->json([
                'success' => true,
                'message' => "Visibilitas kategori '{$category->nama_kategori}' untuk {$targetName} berhasil diubah menjadi {$statusText}!",
                'kategori_id' => $category->id,
                'field' => $field,
                'value' => $category->{$field}
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah visibilitas kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update category visibility (e.g. enable all, disable all)
     */
    public function bulkUpdateKategoriVisibility(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Owner']) && !$user->can('kelola-kategori-bundle')) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk mengubah visibilitas kategori (kelola-kategori-bundle).'], 403);
        }

        $request->validate([
            'action' => 'required|in:enable_all_bundle,disable_all_bundle,enable_all_sales,disable_all_sales,enable_all,disable_all',
        ]);

        try {
            $action = $request->action;
            $companyId = session('active_company_id');

            if ($action === 'enable_all_bundle') {
                MasterKategoriBarang::forActiveCompany()->update(['tampil_di_bundle' => true]);
                $msg = 'Semua kategori berhasil diaktifkan untuk Master Bundle!';
            } elseif ($action === 'disable_all_bundle') {
                MasterKategoriBarang::forActiveCompany()->update(['tampil_di_bundle' => false]);
                $msg = 'Semua kategori berhasil dinonaktifkan dari Master Bundle!';
            } elseif ($action === 'enable_all_sales') {
                MasterKategoriBarang::forActiveCompany()->update(['tampil_di_sales' => true]);
                $msg = 'Semua kategori berhasil diaktifkan untuk Modul Penjualan!';
            } elseif ($action === 'disable_all_sales') {
                MasterKategoriBarang::forActiveCompany()->update(['tampil_di_sales' => false]);
                $msg = 'Semua kategori berhasil dinonaktifkan dari Modul Penjualan!';
            } elseif ($action === 'enable_all') {
                MasterKategoriBarang::forActiveCompany()->update(['tampil_di_bundle' => true, 'tampil_di_sales' => true]);
                $msg = 'Semua kategori berhasil diaktifkan untuk Bundle & Penjualan!';
            } elseif ($action === 'disable_all') {
                MasterKategoriBarang::forActiveCompany()->update(['tampil_di_bundle' => false, 'tampil_di_sales' => false]);
                $msg = 'Semua kategori berhasil dinonaktifkan (Mode Internal)!';
            }

            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses bulk update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve HPP for a child component with STRICT isolation:
     * 1. If $gudangId provided: check ONLY that warehouse.
     * 2. If logged in at Cabang (active_branch_id): check ONLY warehouses of that Cabang.
     * 3. If logged in at PT Pusat (no active_branch_id): check ONLY PT Pusat warehouses (cabang_id null).
     * Returns 0.0 if HPP is not found/0 in active scope (Strict No-Bypass).
     */
    private function resolveChildHpp($child, $gudangId = null): float
    {
        if (!$child || !$child->relationLoaded('stokGudangs')) return 0.0;

        // 1. Gudang Spesifik (jika dipilih)
        if ($gudangId) {
            $sgTarget = $child->stokGudangs->firstWhere('gudang_id', $gudangId);
            if ($sgTarget) {
                if ((float)$sgTarget->harga_pokok_rata_rata > 0) {
                    return (float)$sgTarget->harga_pokok_rata_rata;
                }
                if ((float)$sgTarget->harga_beli_terakhir > 0) {
                    return (float)$sgTarget->harga_beli_terakhir;
                }
            }
            return 0.0;
        }

        $activeBranchId = session('active_branch_id');

        // 2. KETAT CABANG: Jika user login di Cabang, HANYA cek gudang cabang ini
        if ($activeBranchId) {
            $sgBranchValid = $child->stokGudangs->first(function($sg) use ($activeBranchId) {
                return $sg->gudang && $sg->gudang->cabang_id == $activeBranchId && (float)$sg->harga_pokok_rata_rata > 0;
            });
            if ($sgBranchValid) {
                return (float)$sgBranchValid->harga_pokok_rata_rata;
            }

            $sgBranchBeli = $child->stokGudangs->first(function($sg) use ($activeBranchId) {
                return $sg->gudang && $sg->gudang->cabang_id == $activeBranchId && (float)$sg->harga_beli_terakhir > 0;
            });
            if ($sgBranchBeli) {
                return (float)$sgBranchBeli->harga_beli_terakhir;
            }

            // KETAT: Tidak ada fallback ke PT Pusat/Cabang lain!
            return 0.0;
        }

        // 3. KETAT PT PUSAT: Jika user login di PT Pusat (cabang_id null), HANYA cek gudang PT Pusat
        $sgPusatValid = $child->stokGudangs->first(function($sg) {
            return $sg->gudang && is_null($sg->gudang->cabang_id) && (float)$sg->harga_pokok_rata_rata > 0;
        });
        if ($sgPusatValid) {
            return (float)$sgPusatValid->harga_pokok_rata_rata;
        }

        $sgPusatBeli = $child->stokGudangs->first(function($sg) {
            return $sg->gudang && is_null($sg->gudang->cabang_id) && (float)$sg->harga_beli_terakhir > 0;
        });
        if ($sgPusatBeli) {
            return (float)$sgPusatBeli->harga_beli_terakhir;
        }

        // KETAT: Tidak ada HPP di PT Pusat
        return 0.0;
    }

    /**
     * Resolve stock for a child component prioritized by Active Branch or target warehouse.
     */
    private function resolveChildStock($child, $gudangId = null): float
    {
        if (!$child || !$child->relationLoaded('stokGudangs')) return 0.0;

        if ($gudangId) {
            $sgTarget = $child->stokGudangs->firstWhere('gudang_id', $gudangId);
            return $sgTarget ? (float)$sgTarget->stok_aktual : 0.0;
        }

        $activeBranchId = session('active_branch_id');
        if ($activeBranchId) {
            return (float)$child->stokGudangs->filter(function($sg) use ($activeBranchId) {
                return $sg->gudang && $sg->gudang->cabang_id == $activeBranchId;
            })->sum('stok_aktual');
        }

        return (float)$child->stokGudangs->filter(function($sg) {
            return $sg->gudang && is_null($sg->gudang->cabang_id);
        })->sum('stok_aktual');
    }
}
