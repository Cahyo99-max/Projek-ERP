<?php

namespace App\Http\Controllers;

use App\Models\MasterBarang;
use App\Models\MasterBarangKonversi;
use App\Models\MasterKategoriBarang;
use App\Models\MasterBrand;
use App\Models\MasterTipeBarang;
use App\Models\MasterSatuan;
use App\Models\MasterWarna;
use App\Models\MasterBarangFoto;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterBarangController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-barang');

        // Set default counts to 0 for initial page load (counts will load via lazy AJAX for instant rendering)
        $countAll    = 0;
        $countStok   = 0;
        $countJasa   = 0;
        $countAset   = 0;
        $countBundle = 0;
        $countActive = 0;

        // Categories for modern Lookup Modal and dropdown
        $categoriesList = MasterKategoriBarang::forActiveCompany()->active()->with('parent')->orderBy('nama_kategori', 'asc')->get();
        $kategoriOptions = $categoriesList;
        $brands = MasterBrand::active()->orderBy('nama_brand', 'asc')->get();
        $tipes = MasterTipeBarang::active()->orderBy('nama_tipe', 'asc')->get();
        $satuans = MasterSatuan::active()->orderBy('nama_satuan', 'asc')->get();
        $warnas = MasterWarna::active()->orderBy('nama_warna', 'asc')->get();
        $gudangs = \App\Models\MasterGudang::active()->orderBy('nama_gudang', 'asc')->get();

        // Standalone active stock items + child variants for bundle component selection (disabled pre-load for high performance)
        $componentItems = collect();

        return view('data_master.barang.viewbarang', compact(
            'categoriesList', 'kategoriOptions', 'brands', 'tipes', 'satuans', 'warnas', 'gudangs', 'componentItems',
            'countAll', 'countStok', 'countJasa', 'countAset', 'countBundle', 'countActive'
        ));
    }

    public function getSummaryCounts()
    {
        $this->authorize('lihat-barang');

        $activeCompanyId = session('active_company_id');
        $cacheKey = 'master_barang_summary_totals_comp_' . ($activeCompanyId ?? 0);

        $totals = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function() use ($activeCompanyId) {
            return DB::table('master_barang')
                ->selectRaw("
                    COUNT(*) as count_all,
                    SUM(CASE WHEN tipe_item = 'Stok' THEN 1 ELSE 0 END) as count_stok,
                    SUM(CASE WHEN tipe_item = 'Jasa' THEN 1 ELSE 0 END) as count_jasa,
                    SUM(CASE WHEN tipe_item = 'Aset' THEN 1 ELSE 0 END) as count_aset,
                    SUM(CASE WHEN tipe_item = 'Bundle' THEN 1 ELSE 0 END) as count_bundle,
                    SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as count_active
                ")
                ->whereNull('parent_id')
                ->whereNull('deleted_at')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->first();
        });

        return response()->json([
            'countAll'    => number_format($totals->count_all ?? 0),
            'countStok'   => number_format($totals->count_stok ?? 0),
            'countJasa'   => number_format($totals->count_jasa ?? 0),
            'countAset'   => number_format($totals->count_aset ?? 0),
            'countBundle' => number_format($totals->count_bundle ?? 0),
            'countActive' => number_format($totals->count_active ?? 0),
        ]);
    }

    public function getData(Request $request)
    {
        $this->authorize('lihat-barang');

        $search = $request->input('search.value');
        $start = intval($request->input('start', 0));
        $length = intval($request->input('length', 10));
        $tipeFilter = $request->input('tipe_filter');
        $activeCompanyId = session('active_company_id');

        $query = MasterBarang::with([
            'kategori', 
            'brand', 
            'tipeBarang', 
            'satuan', 
            'warna', 
            'primaryFoto', 
            'variants.warna', 
            'variants.satuan',
            'variants.primaryFoto', 
            'bundleComponents.childBarang.satuan',
            'konversiSatuan.satuan'
        ])
        ->forActiveCompany()
        ->whereNull('parent_id')
        ->where('tipe_item', '!=', 'Bundle')
        ->orderBy('id', 'desc'); // Uses composite index (parent_id, deleted_at, id) for instant pagination sorting

        if (!empty($tipeFilter) && $tipeFilter !== 'Semua') {
            $query->where('tipe_item', $tipeFilter);
        }

        $kategoriId = $request->input('kategori_id');
        if (!empty($kategoriId) && $kategoriId !== 'Semua') {
            $query->where('kategori_barang_id', $kategoriId);
        }

        // Optimized Search Filter for high-volume database (6M+ rows)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                // Short search: exact/prefix match (super fast index range scans)
                if (strlen($search) <= 8) {
                    $q->where('kode_barang', 'like', "{$search}%")
                      ->orWhere('barcode', '=', $search)
                      ->orWhere('nama_barang', 'like', "{$search}%");
                } else {
                    $q->where('kode_barang', 'like', "%{$search}%")
                      ->orWhere('barcode', '=', $search)
                      ->orWhere('nama_barang', 'like', "%{$search}%");
                }
            });
        }

        // Cache the total count using high-performance EXPLAIN query estimation to prevent full table scan on every pagination load
        if (!empty($search)) {
            $total = $query->count();
        } else {
            $cacheKey = 'master_barang_count_unfiltered_comp_' . ($activeCompanyId ?? 0) . '_' . ($tipeFilter ?? 'all');
            $total = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function() use ($tipeFilter, $activeCompanyId) {
                try {
                    $sql = "SELECT id FROM master_barang WHERE parent_id IS NULL AND deleted_at IS NULL";
                    if ($activeCompanyId) {
                        $sql .= " AND company_id = " . intval($activeCompanyId);
                    }
                    if (!empty($tipeFilter) && $tipeFilter !== 'Semua') {
                        $safeFilter = in_array($tipeFilter, ['Stok', 'Bundle', 'Aset', 'Jasa']) ? $tipeFilter : 'Stok';
                        $sql .= " AND tipe_item = '{$safeFilter}'";
                    }
                    $explain = DB::select("EXPLAIN " . $sql);
                    return $explain[0]->rows ?? 100000;
                } catch (\Exception $e) {
                    return 100000;
                }
            });
        }

        $barangs = $query->skip($start)->take($length)->get();

        // Batch calculate inventory moving average HPP for pricing reference
        $barangIds = $barangs->pluck('id')->toArray();
        $stokInfoMap = DB::table('stok_gudangs')
            ->whereIn('barang_id', $barangIds)
            ->groupBy('barang_id')
            ->selectRaw('barang_id, SUM(stok_aktual) as total_stok, SUM(nilai_persediaan) as total_nilai, AVG(CASE WHEN harga_pokok_rata_rata > 0 THEN harga_pokok_rata_rata END) as avg_hpp, MAX(harga_beli_terakhir) as last_buy')
            ->get()
            ->keyBy('barang_id');

        $data = [];
        foreach ($barangs as $b) {
            $sInfo = $stokInfoMap->get($b->id);
            $hppGudang = 0;
            if ($sInfo) {
                if ($sInfo->total_stok > 0 && $sInfo->total_nilai > 0) {
                    $hppGudang = $sInfo->total_nilai / $sInfo->total_stok;
                } elseif ($sInfo->avg_hpp > 0) {
                    $hppGudang = (float)$sInfo->avg_hpp;
                } elseif ($sInfo->last_buy > 0) {
                    $hppGudang = (float)$sInfo->last_buy;
                }
            }

            // Format variants for display in client side expansion
            $variantsData = [];
            foreach ($b->variants as $v) {
                $vFotoUrl = null;
                if ($v->primaryFoto) {
                    $vPath = $v->primaryFoto->foto_path;
                    $vFotoUrl = str_starts_with($vPath, 'uploads/') ? asset($vPath) : asset('storage/' . $vPath);
                }

                $variantsData[] = [
                    'id' => $v->id,
                    'kode_barang' => $v->kode_barang,
                    'barcode' => $v->barcode ?? '-',
                    'nama_barang' => $v->nama_barang,
                    'warna_name' => $v->warna->nama_warna ?? '-',
                    'satuan_name' => $v->satuan->nama_satuan ?? '-',
                    'ukuran' => $v->ukuran ?? '-',
                    'status' => $v->status,
                    'foto_path' => $vFotoUrl,
                ];
            }

            // Format components if bundle
            $componentsData = [];
            if ($b->isBundle()) {
                foreach ($b->bundleComponents as $comp) {
                    if ($comp->childBarang) {
                        $componentsData[] = [
                            'kode_barang' => $comp->childBarang->kode_barang,
                            'nama_barang' => $comp->childBarang->nama_barang,
                            'qty' => floatval($comp->qty),
                            'satuan_name' => $comp->childBarang->satuan->nama_satuan ?? '-',
                        ];
                    }
                }
            }

            $fotoUrl = null;
            if ($b->primaryFoto) {
                $path = $b->primaryFoto->foto_path;
                $fotoUrl = str_starts_with($path, 'uploads/') ? asset($path) : asset('storage/' . $path);
            }

            $allPhotos = [];
            foreach ($b->fotos as $foto) {
                $path = $foto->foto_path;
                $allPhotos[] = [
                    'url' => str_starts_with($path, 'uploads/') ? asset($path) : asset('storage/' . $path),
                    'is_primary' => $foto->is_primary,
                ];
            }

            $data[] = [
                'id' => $b->id,
                'kode_barang' => $b->kode_barang,
                'barcode' => $b->barcode ?? '-',
                'nama_barang' => $b->nama_barang,
                'kategori_name' => $b->kategori->nama_kategori ?? '-',
                'brand_name' => $b->brand->nama_brand ?? 'General',
                'tipe_barang_name' => $b->tipeBarang->nama_tipe ?? '-',
                'satuan_name' => $b->satuan->nama_satuan ?? '-',
                'warna_name' => $b->warna->nama_warna ?? '-',
                'warna_hex' => $b->warna->kode_hex ?? '',
                'ukuran' => $b->ukuran ?? '-',
                'status' => $b->status,
                'tipe_item' => $b->tipe_item,
                'tipe_pelacakan' => $b->tipe_pelacakan ?? 'None',
                'spesifikasi' => $b->spesifikasi ?? '-',
                'panjang' => $b->panjang ? floatval($b->panjang) : null,
                'lebar' => $b->lebar ? floatval($b->lebar) : null,
                'tinggi' => $b->tinggi ? floatval($b->tinggi) : null,
                'berat' => $b->berat ? floatval($b->berat) : null,
                'harga_jual_retail' => $b->harga_jual_retail ? floatval($b->harga_jual_retail) : 0,
                'harga_jual_grosir' => $b->harga_jual_grosir ? floatval($b->harga_jual_grosir) : 0,
                'harga_jual_tender' => $b->harga_jual_tender ? floatval($b->harga_jual_tender) : 0,
                'foto_path' => $fotoUrl,
                'all_photos' => $allPhotos,
                'variants' => $variantsData,
                'components' => $componentsData,
                'konversi_satuan' => $b->konversiSatuan->map(function($k) {
                    return [
                        'id' => $k->id,
                        'satuan_id' => $k->satuan_id,
                        'nama_satuan' => $k->satuan->nama_satuan ?? '-',
                        'singkatan' => $k->satuan->singkatan ?? '-',
                        'faktor_pengali' => floatval($k->faktor_pengali),
                        'is_satuan_beli' => (bool)$k->is_satuan_beli,
                        'is_satuan_jual' => (bool)$k->is_satuan_jual,
                    ];
                }),
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $allowedPermissions = [
            'tambah-barang',
            'tambah-pr',
            'edit-pr',
            'tambah-po',
            'edit-po',
            'tambah-bom',
            'edit-bom',
            'tambah-tender',
            'edit-tender',
        ];

        $isAllowed = false;
        if ($user) {
            foreach ($allowedPermissions as $perm) {
                if ($user->can($perm)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        if (!$isAllowed) {
            $this->authorize('tambah-barang');
        }

        $activeCompanyId = session('active_company_id');
        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'kode_barang'        => 'nullable|string|max:20|unique:master_barang,kode_barang,NULL,id,deleted_at,NULL' . $compWhere,
            'barcode'            => 'nullable|string|max:30|unique:master_barang,barcode,NULL,id,deleted_at,NULL' . $compWhere,
            'nama_barang'        => 'required|string|max:50',
            'nama_alias'         => 'nullable|string|max:20',
            'tipe_item'          => 'required|in:Stok,Aset,Jasa,Bundle',
            'tipe_pelacakan'     => 'nullable|in:None,Serial Number,Batch',
            'kategori_barang_id' => 'required|integer|exists:master_kategori_barang,id',
            'brand_id'           => 'nullable|integer|exists:master_brand,id',
            'tipe_barang_id'     => 'required|integer|exists:master_tipe_barang,id',
            'satuan_id'          => 'required|integer|exists:master_satuan,id',
            'warna_id'           => 'nullable|integer|exists:master_warna,id',
            'ukuran'             => 'nullable|string|max:50',
            'spesifikasi'        => 'nullable|string|max:255',
            'harga_jual_retail'  => 'nullable',
            'harga_jual_grosir'  => 'nullable',
            'harga_jual_tender'  => 'nullable',
            'berat'              => 'nullable|numeric|min:0|max:99999',
            'panjang'            => 'nullable|numeric|min:0|max:99999',
            'lebar'              => 'nullable|numeric|min:0|max:99999',
            'tinggi'             => 'nullable|numeric|min:0|max:99999',
            'minimum_stok'       => 'nullable|numeric|min:0|max:99999',
            'status'             => 'required|in:Active,Inactive',
            'fotos'              => 'nullable|array|max:4',
            'fotos.*'            => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',

            // Validasi Konversi Satuan
            'konversi_satuan'                 => 'nullable|array',
            'konversi_satuan.*.satuan_id'      => 'nullable|integer|exists:master_satuan,id',
            'konversi_satuan.*.faktor_pengali' => 'nullable|numeric|min:0.0001',
            'konversi_satuan.*.is_satuan_beli' => 'nullable',
            'konversi_satuan.*.is_satuan_jual' => 'nullable',

            'has_variants'       => 'nullable',
            'variants'           => 'nullable|array',
            'variants.*.ukuran'  => 'required|string|max:50',
            'variants.*.warna_id'=> 'nullable|integer|exists:master_warna,id',
            'variants.*.kode_barang' => 'nullable|string|max:20',
            'variants.*.barcode' => 'nullable|string|max:30',
            'variants.*.status'  => 'required|in:Active,Inactive',

            'bundle_components'  => 'nullable|array',
            'bundle_components.*.child_barang_id' => 'required|integer|exists:master_barang,id',
            'bundle_components.*.qty' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $kode = $request->kode_barang;
            if (empty($kode)) {
                $kode = CodeGeneratorHelper::generate('master_barang', 'BRG-', 'kode_barang');
            }

            $barcodeVal = $request->barcode;
            if (empty($barcodeVal)) {
                $barcodeVal = $kode;
            }

            $parsePrice = function($val) {
                if (!$val) return 0;
                return (float)str_replace(['.', ','], ['', '.'], (string)$val);
            };

            $barang = MasterBarang::create([
                'company_id'         => $activeCompanyId,
                'kode_barang'        => $kode,
                'barcode'            => $barcodeVal,
                'nama_barang'        => $request->nama_barang,
                'nama_alias'         => $request->nama_alias,
                'tipe_item'          => $request->tipe_item,
                'tipe_pelacakan'     => $request->tipe_pelacakan ?? 'None',
                'kategori_barang_id' => $request->kategori_barang_id,
                'brand_id'           => $request->brand_id,
                'tipe_barang_id'     => $request->tipe_barang_id,
                'satuan_id'          => $request->satuan_id,
                'warna_id'           => $request->warna_id,
                'ukuran'             => $request->ukuran ?? null,
                'spesifikasi'        => $request->spesifikasi,
                'harga_jual_retail'  => $parsePrice($request->harga_jual_retail),
                'harga_jual_grosir'  => $parsePrice($request->harga_jual_grosir),
                'harga_jual_tender'  => $parsePrice($request->harga_jual_tender),
                'berat'              => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->berat ?? 0) : 0,
                'panjang'            => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->panjang ?? 0) : 0,
                'lebar'              => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->lebar ?? 0) : 0,
                'tinggi'             => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->tinggi ?? 0) : 0,
                'minimum_stok'       => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->minimum_stok ?? 0) : 0,
                'status'             => $request->status,
            ]);

            $parseDecimal = function($val) {
                if (!$val) return 0;
                $raw = trim((string)$val);
                if (str_contains($raw, ',') && str_contains($raw, '.')) {
                    $raw = str_replace('.', '', $raw);
                    $raw = str_replace(',', '.', $raw);
                } elseif (str_contains($raw, ',')) {
                    $raw = str_replace(',', '.', $raw);
                }
                return (float)$raw;
            };

            // Simpan Konversi Satuan
            if ($request->has('konversi_satuan') && is_array($request->konversi_satuan)) {
                foreach ($request->konversi_satuan as $konv) {
                    if (!empty($konv['satuan_id']) && !empty($konv['faktor_pengali']) && $konv['satuan_id'] != $barang->satuan_id) {
                        $parseFaktor = $parseDecimal($konv['faktor_pengali']);
                        if ($parseFaktor > 0) {
                            $barang->konversiSatuan()->create([
                                'satuan_id'      => $konv['satuan_id'],
                                'faktor_pengali' => $parseFaktor,
                                'is_satuan_beli' => !empty($konv['is_satuan_beli']),
                                'is_satuan_jual' => !empty($konv['is_satuan_jual']),
                            ]);
                        }
                    }
                }
            }

            if ($request->has_variants && !empty($request->variants)) {
                foreach ($request->variants as $var) {
                    $vKode = $var['kode_barang'];
                    if (empty($vKode)) {
                        $vKode = $kode . '-' . strtoupper($var['ukuran']);
                        $exists = DB::table('master_barang')->where('kode_barang', $vKode)->where('company_id', $barang->company_id)->exists();
                        if ($exists) {
                            $vKode = CodeGeneratorHelper::generate('master_barang', $vKode . '-', 'kode_barang', 2);
                        }
                    } else {
                        $exists = DB::table('master_barang')->where('kode_barang', $vKode)->where('company_id', $barang->company_id)->exists();
                        if ($exists) {
                            $vKode = CodeGeneratorHelper::generate('master_barang', $vKode . '-', 'kode_barang', 2);
                        }
                    }

                    $vBarcode = $var['barcode'] ?? null;
                    if (empty($vBarcode)) {
                        $vBarcode = $vKode;
                    }

                    MasterBarang::create([
                        'company_id'         => $barang->company_id,
                        'parent_id'          => $barang->id,
                        'kode_barang'        => $vKode,
                        'barcode'            => $vBarcode,
                        'nama_barang'        => $barang->nama_barang . ' (' . $var['ukuran'] . ')',
                        'nama_alias'         => $barang->nama_alias,
                        'tipe_item'          => 'Stok',
                        'kategori_barang_id' => $barang->kategori_barang_id,
                        'brand_id'           => $barang->brand_id,
                        'tipe_barang_id'     => $barang->tipe_barang_id,
                        'satuan_id'          => $barang->satuan_id,
                        'warna_id'           => $var['warna_id'] ?? $barang->warna_id,
                        'ukuran'             => $var['ukuran'],
                        'spesifikasi'        => $barang->spesifikasi,
                        'berat'              => $barang->berat,
                        'panjang'            => $barang->panjang,
                        'lebar'              => $barang->lebar,
                        'tinggi'             => $barang->tinggi,
                        'minimum_stok'       => $barang->minimum_stok,
                        'maksimum_stok'      => $barang->maksimum_stok,
                        'reorder_point'      => $barang->reorder_point,
                        'status'             => $var['status'] ?? 'Active',
                    ]);
                }
            }

            if ($request->hasFile('fotos')) {
                $files = $request->file('fotos');
                Log::info('Foto upload count: ' . count($files));
                foreach ($files as $index => $file) {
                    Log::info('Saving foto: ' . $file->getClientOriginalName() . ' size: ' . $file->getSize());
                    $path = $file->store('barang', 'public');
                    $barang->fotos()->create([
                        'foto_path'  => $path,
                        'is_primary' => $index === 0,
                        'urutan'     => $index,
                    ]);
                }
            } else {
                Log::info('No foto files in request for barang store.');
            }

            $barang->load(['satuan', 'warna', 'primaryFoto', 'fotos']);
            DB::commit();
            $this->clearBarangCache();
            return response()->json([
                'message' => 'Barang berhasil ditambahkan!',
                'barang' => $barang
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error store barang: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat menyimpan barang.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store barang: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah barang.'], 500);
        }
    }

    public function edit(MasterBarang $barang)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $barang->company_id && $barang->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Barang ini milik PT lain.'], 403);
        }
        $barang->load(['kategori', 'fotos', 'variants.warna', 'bundleComponents.childBarang', 'konversiSatuan.satuan']);

        $stokInfo = DB::table('stok_gudangs')
            ->where('barang_id', $barang->id)
            ->selectRaw('SUM(stok_aktual) as total_stok, SUM(nilai_persediaan) as total_nilai, AVG(CASE WHEN harga_pokok_rata_rata > 0 THEN harga_pokok_rata_rata END) as avg_hpp, MAX(harga_beli_terakhir) as last_buy')
            ->first();

        $hppGudang = 0;
        if ($stokInfo) {
            if ($stokInfo->total_stok > 0 && $stokInfo->total_nilai > 0) {
                $hppGudang = $stokInfo->total_nilai / $stokInfo->total_stok;
            } elseif ($stokInfo->avg_hpp > 0) {
                $hppGudang = (float)$stokInfo->avg_hpp;
            } elseif ($stokInfo->last_buy > 0) {
                $hppGudang = (float)$stokInfo->last_buy;
            }
        }
        $barang->hpp_referensi = round($hppGudang, 2);

        return response()->json(['barang' => $barang]);
    }

    public function update(Request $request, MasterBarang $barang)
    {
        $this->authorize('edit-barang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $barang->company_id && $barang->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit barang milik PT lain.'], 403);
        }

        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'kode_barang'        => 'required|string|max:20|unique:master_barang,kode_barang,' . $barang->id . ',id,deleted_at,NULL' . $compWhere,
            'barcode'            => 'nullable|string|max:30|unique:master_barang,barcode,' . $barang->id . ',id,deleted_at,NULL' . $compWhere,
            'nama_barang'        => 'required|string|max:50',
            'nama_alias'         => 'nullable|string|max:20',
            'tipe_item'          => 'required|in:Stok,Aset,Jasa,Bundle',
            'tipe_pelacakan'     => 'nullable|in:None,Serial Number,Batch',
            'kategori_barang_id' => 'required|integer|exists:master_kategori_barang,id',
            'brand_id'           => 'nullable|integer|exists:master_brand,id',
            'tipe_barang_id'     => 'required|integer|exists:master_tipe_barang,id',
            'satuan_id'          => 'required|integer|exists:master_satuan,id',
            'warna_id'           => 'nullable|integer|exists:master_warna,id',
            'ukuran'             => 'nullable|string|max:50',
            'spesifikasi'        => 'nullable|string|max:255',
            'harga_jual_retail'  => 'nullable',
            'harga_jual_grosir'  => 'nullable',
            'harga_jual_tender'  => 'nullable',
            'berat'              => 'nullable|numeric|min:0|max:99999',
            'panjang'            => 'nullable|numeric|min:0|max:99999',
            'lebar'              => 'nullable|numeric|min:0|max:99999',
            'tinggi'             => 'nullable|numeric|min:0|max:99999',
            'minimum_stok'       => 'nullable|numeric|min:0|max:99999',
            'status'             => 'required|in:Active,Inactive',
            'fotos'              => 'nullable|array|max:4',
            'fotos.*'            => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',

            // Validasi Konversi Satuan
            'konversi_satuan'                 => 'nullable|array',
            'konversi_satuan.*.id'             => 'nullable|integer|exists:master_barang_konversi,id',
            'konversi_satuan.*.satuan_id'      => 'nullable|integer|exists:master_satuan,id',
            'konversi_satuan.*.faktor_pengali' => 'nullable|numeric|min:0.0001',
            'konversi_satuan.*.is_satuan_beli' => 'nullable',
            'konversi_satuan.*.is_satuan_jual' => 'nullable',

            // Validasi varian
            'has_variants'       => 'nullable',
            'variants'           => 'nullable|array',
            'variants.*.id'      => 'nullable|integer|exists:master_barang,id',
            'variants.*.ukuran'  => 'required|string|max:50',
            'variants.*.warna_id'=> 'nullable|integer|exists:master_warna,id',
            'variants.*.kode_barang' => 'nullable|string|max:20',
            'variants.*.barcode' => 'nullable|string|max:30',
            'variants.*.status'  => 'required|in:Active,Inactive',

            // Validasi bundle
            'bundle_components'  => 'nullable|array',
            'bundle_components.*.child_barang_id' => 'required|integer|exists:master_barang,id',
            'bundle_components.*.qty' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $barcodeVal = $request->barcode;
            if (empty($barcodeVal)) {
                $barcodeVal = $request->kode_barang;
            }

            $parsePrice = function($val) {
                if (!$val) return 0;
                return (float)str_replace(['.', ','], ['', '.'], (string)$val);
            };

            $barang->update([
                'kode_barang'        => $request->kode_barang,
                'barcode'            => $barcodeVal,
                'nama_barang'        => $request->nama_barang,
                'nama_alias'         => $request->nama_alias,
                'tipe_item'          => $request->tipe_item,
                'tipe_pelacakan'     => $request->tipe_pelacakan ?? 'None',
                'kategori_barang_id' => $request->kategori_barang_id,
                'brand_id'           => $request->brand_id,
                'tipe_barang_id'     => $request->tipe_barang_id,
                'satuan_id'          => $request->satuan_id,
                'warna_id'           => $request->warna_id,
                'ukuran'             => $request->ukuran ?? null,
                'spesifikasi'        => $request->spesifikasi,
                'harga_jual_retail'  => $parsePrice($request->harga_jual_retail),
                'harga_jual_grosir'  => $parsePrice($request->harga_jual_grosir),
                'harga_jual_tender'  => $parsePrice($request->harga_jual_tender),
                'berat'              => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->berat ?? 0) : 0,
                'panjang'            => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->panjang ?? 0) : 0,
                'lebar'              => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->lebar ?? 0) : 0,
                'tinggi'             => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->tinggi ?? 0) : 0,
                'minimum_stok'       => in_array($request->tipe_item, ['Stok', 'Bundle']) ? ($request->minimum_stok ?? 0) : 0,
                'status'             => $request->status,
            ]);

            $parseDecimal = function($val) {
                if (!$val) return 0;
                $raw = trim((string)$val);
                if (str_contains($raw, ',') && str_contains($raw, '.')) {
                    $raw = str_replace('.', '', $raw);
                    $raw = str_replace(',', '.', $raw);
                } elseif (str_contains($raw, ',')) {
                    $raw = str_replace(',', '.', $raw);
                }
                return (float)$raw;
            };

            // Sync Konversi Satuan
            $incomingKonversiIds = [];
            if ($request->has('konversi_satuan') && is_array($request->konversi_satuan)) {
                foreach ($request->konversi_satuan as $konv) {
                    if (!empty($konv['satuan_id']) && !empty($konv['faktor_pengali']) && $konv['satuan_id'] != $barang->satuan_id) {
                        $parseFaktor = $parseDecimal($konv['faktor_pengali']);
                        if ($parseFaktor > 0) {
                            $konvData = [
                                'satuan_id'      => $konv['satuan_id'],
                                'faktor_pengali' => $parseFaktor,
                                'is_satuan_beli' => !empty($konv['is_satuan_beli']),
                                'is_satuan_jual' => !empty($konv['is_satuan_jual']),
                            ];

                            if (!empty($konv['id'])) {
                                $existingKonv = $barang->konversiSatuan()->find($konv['id']);
                                if ($existingKonv) {
                                    $existingKonv->update($konvData);
                                    $incomingKonversiIds[] = $existingKonv->id;
                                } else {
                                    $newKonv = $barang->konversiSatuan()->create($konvData);
                                    $incomingKonversiIds[] = $newKonv->id;
                                }
                            } else {
                                $newKonv = $barang->konversiSatuan()->create($konvData);
                                $incomingKonversiIds[] = $newKonv->id;
                            }
                        }
                    }
                }
            }
            $barang->konversiSatuan()->whereNotIn('id', $incomingKonversiIds)->delete();

            // Sync Variants
            $incomingVariantIds = [];
            if ($request->has_variants && !empty($request->variants)) {
                foreach ($request->variants as $var) {
                    $vKode = $var['kode_barang'];
                    $isNew = empty($var['id']);
                    
                    if (empty($vKode)) {
                        $vKode = $barang->kode_barang . '-' . strtoupper($var['ukuran']);
                    }

                    $vBarcode = $var['barcode'] ?? null;

                    $data = [
                        'company_id'         => $barang->company_id,
                        'parent_id'          => $barang->id,
                        'nama_barang'        => $barang->nama_barang . ' (' . $var['ukuran'] . ')',
                        'nama_alias'         => $barang->nama_alias,
                        'tipe_item'          => 'Stok',
                        'kategori_barang_id' => $barang->kategori_barang_id,
                        'brand_id'           => $barang->brand_id,
                        'tipe_barang_id'     => $barang->tipe_barang_id,
                        'satuan_id'          => $barang->satuan_id,
                        'warna_id'           => $var['warna_id'] ?? $barang->warna_id,
                        'ukuran'             => $var['ukuran'],
                        'spesifikasi'        => $barang->spesifikasi,
                        'berat'              => $barang->berat,
                        'panjang'            => $barang->panjang,
                        'lebar'              => $barang->lebar,
                        'tinggi'             => $barang->tinggi,
                        'minimum_stok'       => $barang->minimum_stok,
                        'maksimum_stok'      => $barang->maksimum_stok,
                        'reorder_point'      => $barang->reorder_point,
                        'status'             => $var['status'] ?? 'Active',
                    ];

                    if (!$isNew) {
                        // Update existing child variant
                        $child = MasterBarang::findOrFail($var['id']);
                        if ($child->kode_barang !== $vKode) {
                            $exists = DB::table('master_barang')->where('kode_barang', $vKode)->where('company_id', $barang->company_id)->where('id', '!=', $child->id)->exists();
                            if ($exists) {
                                $vKode = CodeGeneratorHelper::generate('master_barang', $vKode . '-', 'kode_barang', 2);
                            }
                        }
                        $data['kode_barang'] = $vKode;
                        $data['barcode'] = empty($vBarcode) ? $vKode : $vBarcode;
                        $child->update($data);
                        $incomingVariantIds[] = $child->id;
                    } else {
                        // Create new child variant
                        $exists = DB::table('master_barang')->where('kode_barang', $vKode)->where('company_id', $barang->company_id)->exists();
                        if ($exists) {
                            $vKode = CodeGeneratorHelper::generate('master_barang', $vKode . '-', 'kode_barang', 2);
                        }
                        $data['kode_barang'] = $vKode;
                        $data['barcode'] = empty($vBarcode) ? $vKode : $vBarcode;
                        $newChild = MasterBarang::create($data);
                        $incomingVariantIds[] = $newChild->id;
                    }
                }
            }

            // Delete variants that were removed
            $barang->variants()->whereNotIn('id', $incomingVariantIds)->delete();



            // Save additional multi-photos
            if ($request->hasFile('fotos')) {
                $files = $request->file('fotos');

                // Enforce max 4 photos total (existing + new)
                $existingCount = $barang->fotos()->count();
                $maxAllowed = 4 - $existingCount;
                if ($maxAllowed > 0) {
                    $files = array_slice($files, 0, $maxAllowed);

                    // Check if item has a primary photo already
                    $hasPrimary = $barang->fotos()->where('is_primary', true)->exists();
                    $startingIndex = $barang->fotos()->count();

                    foreach ($files as $index => $file) {
                        $path = $file->store('barang', 'public');
                        $barang->fotos()->create([
                            'foto_path'  => $path,
                            'is_primary' => !$hasPrimary && ($index === 0),
                            'urutan'     => $startingIndex + $index,
                        ]);
                    }
                }
            }

            DB::commit();
            $this->clearBarangCache();
            return response()->json(['message' => 'Barang berhasil diperbarui!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error update barang: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengupdate barang.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update barang: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate barang.'], 500);
        }
    }

    public function destroy(MasterBarang $barang)
    {
        $this->authorize('hapus-barang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $barang->company_id && $barang->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Barang ini milik PT lain.'], 403);
        }
        try {
            DB::beginTransaction();
            
            // Delete associated physical images
            foreach ($barang->fotos as $foto) {
                if (Storage::disk('public')->exists($foto->foto_path)) {
                    Storage::disk('public')->delete($foto->foto_path);
                }
            }
            
            $barang->delete(); // This deletes the fotos automatically due to cascadeOnDelete on migration
            DB::commit();
            $this->clearBarangCache();

            return response()->json(['message' => 'Barang berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete barang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus barang.'], 500);
        }
    }

    /**
     * Delete individual photo AJAX
     */
    public function deleteFoto(MasterBarangFoto $foto)
    {
        $this->authorize('edit-barang');
        try {
            DB::beginTransaction();
            if (Storage::disk('public')->exists($foto->foto_path)) {
                Storage::disk('public')->delete($foto->foto_path);
            }
            
            $barangId = $foto->barang_id;
            $wasPrimary = $foto->is_primary;
            $foto->delete();

            // Set another photo as primary if deleted one was primary
            if ($wasPrimary) {
                $next = MasterBarangFoto::where('barang_id', $barangId)->first();
                if ($next) {
                    $next->update(['is_primary' => true]);
                }
            }
            
            DB::commit();
            return response()->json(['message' => 'Foto berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete single photo: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus foto.'], 500);
        }
    }

    /**
     * Make a photo the primary one AJAX
     */
    public function setPrimaryFoto(MasterBarangFoto $foto)
    {
        $this->authorize('edit-barang');
        try {
            DB::beginTransaction();
            MasterBarangFoto::where('barang_id', $foto->barang_id)->update(['is_primary' => false]);
            $foto->update(['is_primary' => true]);
            DB::commit();
            return response()->json(['message' => 'Foto utama berhasil diatur!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error set primary photo: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengatur foto utama.'], 500);
        }
    }

    /**
     * Reorder photos drag-and-drop AJAX
     */
    public function reorderFotos(Request $request)
    {
        $this->authorize('edit-barang');
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:master_barang_foto,id',
        ]);

        try {
            DB::beginTransaction();
            foreach ($request->ids as $index => $id) {
                DB::table('master_barang_foto')
                    ->where('id', $id)
                    ->update([
                        'urutan' => $index,
                        'is_primary' => $index === 0,
                    ]);
            }
            DB::commit();
            $this->clearBarangCache();
            return response()->json(['message' => 'Urutan foto berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reorder fotos: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui urutan foto.'], 500);
        }
    }

    /**
     * Recursive helper to build tree structure options in memory (Single Query)
     */
    private function getCategoryTreeOptions()
    {
        // Avoid OOM crash: Limit tree load if category volume is extremely high (7M+ safety)
        try {
            $count = MasterKategoriBarang::active()->count();
        } catch (\Exception $e) {
            $count = 0;
        }

        if ($count > 1000) {
            // High volume safety: only load top-level categories and limit to 300 to keep UI responsive
            try {
                $categories = MasterKategoriBarang::active()
                    ->whereNull('parent_id')
                    ->orderBy('urutan', 'asc')
                    ->take(300)
                    ->get();
                $options = [];
                foreach ($categories as $cat) {
                    $options[] = ['id' => $cat->id, 'text' => $cat->nama_kategori];
                }
                return $options;
            } catch (\Exception $e) {
                return [];
            }
        }

        $categories = MasterKategoriBarang::active()->orderBy('urutan', 'asc')->get();
        $grouped = $categories->groupBy('parent_id');
        $options = [];
        $roots = $grouped->get(null) ?: collect();
        foreach ($roots as $root) {
            $options[] = ['id' => $root->id, 'text' => $root->nama_kategori];
            $options = array_merge($options, $this->traverseChildrenMemory($root->id, 1, $grouped));
        }
        return $options;
    }

    private function traverseChildrenMemory($parentId, $depth, $grouped)
    {
        $options = [];
        $children = $grouped->get($parentId) ?: collect();
        $prefix = str_repeat('— ', $depth);
        foreach ($children as $child) {
            $options[] = ['id' => $child->id, 'text' => $prefix . $child->nama_kategori];
            $options = array_merge($options, $this->traverseChildrenMemory($child->id, $depth + 1, $grouped));
        }
        return $options;
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('lihat-barang');

        $format = $request->get('format', 'xlsx');
        $activeCompanyId = session('active_company_id');

        // Lift execution time limit for high-volume exports
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        // Subqueries for clean separate conversion columns
        $satuanAltSubquery = DB::table('master_barang_konversi as bk')
            ->join('master_satuan as s2', 'bk.satuan_id', '=', 's2.id')
            ->whereColumn('bk.barang_id', 'b.id')
            ->whereNull('bk.deleted_at')
            ->selectRaw("GROUP_CONCAT(s2.nama_satuan SEPARATOR ', ')");

        $rasioAltSubquery = DB::table('master_barang_konversi as bk')
            ->whereColumn('bk.barang_id', 'b.id')
            ->whereNull('bk.deleted_at')
            ->selectRaw("GROUP_CONCAT((bk.faktor_pengali + 0) SEPARATOR ', ')");

        // Build the basic query
        $query = DB::table('master_barang as b')
            ->leftJoin('master_kategori_barang as k', 'b.kategori_barang_id', '=', 'k.id')
            ->leftJoin('master_brand as br', 'b.brand_id', '=', 'br.id')
            ->leftJoin('master_tipe_barang as tb', 'b.tipe_barang_id', '=', 'tb.id')
            ->leftJoin('master_satuan as s', 'b.satuan_id', '=', 's.id')
            ->leftJoin('master_warna as w', 'b.warna_id', '=', 'w.id')
            ->whereNull('b.parent_id')
            ->whereNull('b.deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('b.company_id', $activeCompanyId))
            ->when($request->filled('tipe_filter') && $request->input('tipe_filter') !== 'Semua', fn($q) => $q->where('b.tipe_item', $request->input('tipe_filter')))
            ->when($request->filled('kategori_id') && $request->input('kategori_id') !== 'Semua', fn($q) => $q->where('b.kategori_barang_id', $request->input('kategori_id')))
            ->orderBy('b.id', 'desc')
            ->select([
                'b.id', 'b.kode_barang', 'b.barcode', 'b.nama_barang', 'b.nama_alias', 'b.tipe_item', 'b.tipe_pelacakan',
                'k.nama_kategori', 'br.nama_brand', 'tb.nama_tipe', 's.nama_satuan', 'w.nama_warna',
                'b.ukuran', 'b.spesifikasi', 'b.berat', 'b.panjang', 'b.lebar', 'b.tinggi',
                'b.harga_jual_retail', 'b.harga_jual_grosir', 'b.harga_jual_tender',
                'b.minimum_stok', 'b.status'
            ])
            ->selectSub($satuanAltSubquery, 'satuan_alt_name')
            ->selectSub($rasioAltSubquery, 'satuan_alt_ratio');

        $count = $query->count();

        $headersArray = [
            'Kode Barang', 'Barcode', 'Nama Barang', 'Nama Alias', 'Tipe Item', 'Tipe Pelacakan',
            'Kategori', 'Brand', 'Tipe Barang', 'Satuan Dasar',
            'Satuan Alternatif', 'Isi Rasio Konversi',
            'Warna', 'Ukuran',
            'Harga Retail (Rp)', 'Harga Grosir (Rp)', 'Harga Tender (Rp)',
            'Spesifikasi', 'Berat (kg)', 'Panjang (cm)', 'Lebar (cm)', 'Tinggi (cm)',
            'Minimum Stok', 'Status'
        ];

        // If dataset is too large (over 50,000 items) or user explicitly requested CSV, force stream CSV
        if ($count > 50000 || $format === 'csv') {
            $fileName = 'data_barang_' . date('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $barangs = $query->cursor();

            $callback = function () use ($barangs, $headersArray) {
                $file = fopen('php://output', 'w');
                
                // Add UTF-8 BOM so Excel opens it with proper column encoding and layouts
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Write Headers
                fputcsv($file, $headersArray, ',');

                // Stream rows one-by-one (constant memory footprint)
                foreach ($barangs as $b) {
                    fputcsv($file, [
                        $b->kode_barang,
                        $b->barcode,
                        $b->nama_barang,
                        $b->nama_alias,
                        $b->tipe_item,
                        $b->tipe_pelacakan ?? 'None',
                        $b->nama_kategori ?? '',
                        $b->nama_brand ?? '',
                        $b->nama_tipe ?? '',
                        $b->nama_satuan ?? '',
                        $b->satuan_alt_name ?? '',
                        $b->satuan_alt_ratio ?? '',
                        $b->nama_warna ?? '',
                        $b->ukuran,
                        (float) $b->harga_jual_retail,
                        (float) $b->harga_jual_grosir,
                        (float) $b->harga_jual_tender,
                        $b->spesifikasi,
                        (float) $b->berat,
                        (float) $b->panjang,
                        (float) $b->lebar,
                        (float) $b->tinggi,
                        (float) $b->minimum_stok,
                        $b->status
                    ], ',');
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Safe threshold (<= 50,000 items) - Export standard Excel (XLSX)
        $barangs = $query->cursor();
        $rows = [$headersArray];

        foreach ($barangs as $b) {
            $rows[] = [
                $b->kode_barang,
                $b->barcode,
                $b->nama_barang,
                $b->nama_alias,
                $b->tipe_item,
                $b->tipe_pelacakan ?? 'None',
                $b->nama_kategori ?? '',
                $b->nama_brand ?? '',
                $b->nama_tipe ?? '',
                $b->nama_satuan ?? '',
                $b->satuan_alt_name ?? '',
                $b->satuan_alt_ratio ?? '',
                $b->nama_warna ?? '',
                $b->ukuran,
                (float) $b->harga_jual_retail,
                (float) $b->harga_jual_grosir,
                (float) $b->harga_jual_tender,
                $b->spesifikasi,
                (float) $b->berat,
                (float) $b->panjang,
                (float) $b->lebar,
                (float) $b->tinggi,
                (float) $b->minimum_stok,
                $b->status
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_barang_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-barang');

        $activeCompanyId = session('active_company_id');

        // Fetch active reference data from database for current company
        $sampleKat = MasterKategoriBarang::forActiveCompany()->where('status', 'Active')->first()?->nama_kategori ?? 'Tekstil & Kain';
        $sampleBrand = MasterBrand::forActiveCompany()->where('status', 'Active')->first()?->nama_brand ?? 'Cordura';
        $sampleTipe = MasterTipeBarang::forActiveCompany()->where('status', 'Active')->first()?->nama_tipe ?? 'Bahan Baku';
        $sampleSatuan = MasterSatuan::forActiveCompany()->first()?->nama_satuan ?? 'Meter';
        $sampleWarna = MasterWarna::forActiveCompany()->where('status', 'Active')->first()?->nama_warna ?? 'Hitam';

        // Sheet 1: Template Import Data Barang (User-Friendly Column Split)
        $templateRows = [
            [
                'Kode Barang', 'Barcode', 'Nama Barang', 'Nama Alias', 'Tipe Item', 'Tipe Pelacakan',
                'Kategori', 'Brand', 'Tipe Barang', 'Satuan Dasar',
                'Satuan Alternatif', 'Isi Rasio Konversi',
                'Warna', 'Ukuran',
                'Harga Retail', 'Harga Grosir', 'Harga Tender',
                'Spesifikasi', 'Berat (kg)', 'Panjang (cm)', 'Lebar (cm)', 'Tinggi (cm)',
                'Minimum Stok', 'Status'
            ],
            [
                'BRG-KCORDURA500', '899123456001', 'Kain Cordura 500D WP', 'Cordura Waterproof', 'Stok', 'Batch',
                $sampleKat, $sampleBrand, $sampleTipe, 'Meter',
                'Rol', '50',
                $sampleWarna, 'Lebar 150cm',
                '85000', '75000', '70000',
                'Kain nilon cordura 500D coating waterproof untuk tas & rompi taktikal', '0.35', '5000', '150', '0.1',
                '100', 'Active'
            ],
            [
                'BRG-HLMTMP-GEN2', '899123456002', 'Helm Tempur Taktis Gen-2', 'Tactical Helmet Gen 2', 'Stok', 'Serial Number',
                $sampleKat, $sampleBrand, $sampleTipe, 'Pcs',
                'Dus', '10',
                'Hijau TNI', 'L',
                '3500000', '3200000', '3000000',
                'Helm balistik level IIIA dengan rel samping M-LOK & mounting NVG', '1.45', '28', '24', '18',
                '10', 'Active'
            ],
            [
                'JSA-MAINT-MESIN', '', 'Jasa Maintenance Mesin Jahit', 'Servis Mesin', 'Jasa', 'None',
                $sampleKat, '', $sampleTipe, 'Jam',
                '', '',
                '', '',
                '150000', '125000', '100000',
                'Jasa tune-up dan perbaikan mesin jahit industri', '0', '0', '0', '0',
                '0', 'Active'
            ]
        ];

        // Sheet 2: Petunjuk & Referensi Nama Master di Database (agar mudah di-copy/paste)
        $allKategoris = MasterKategoriBarang::forActiveCompany()->where('status', 'Active')->pluck('nama_kategori')->implode(', ');
        $allBrands = MasterBrand::forActiveCompany()->where('status', 'Active')->pluck('nama_brand')->implode(', ');
        $allTipes = MasterTipeBarang::forActiveCompany()->where('status', 'Active')->pluck('nama_tipe')->implode(', ');
        $allSatuans = MasterSatuan::forActiveCompany()->pluck('nama_satuan')->implode(', ');
        $allWarnas = MasterWarna::forActiveCompany()->where('status', 'Active')->pluck('nama_warna')->implode(', ');

        $referenceRows = [
            ['PANDUAN & DAFTAR REFERENSI MASTER BARANG ERP'],
            ['1. Kolom Wajib Diisi (*): Nama Barang, Tipe Item, Kategori, Tipe Barang, Satuan Dasar, Status.'],
            ['2. Tipe Item Valid: Stok, Aset, Jasa.'],
            ['3. Tipe Pelacakan: None (barang umum), Serial Number (barang unik per unit), Batch (kain/material per gulung/lot).'],
            ['4. PANDUAN PENGISIAN MULTI-SATUAN & KONVERSI:'],
            ['   - Satuan Dasar: Satuan terkecil untuk stok di gudang (contoh: "Meter" atau "Pcs").'],
            ['   - Satuan Alternatif: Satuan kemasan besar untuk Beli/Jual (contoh: "Rol", "Dus", "Box", "Lusin"). Kosongkan jika tidak ada.'],
            ['   - Isi Rasio Konversi: Isi angka berapa Satuan Dasar di dalamnya (contoh: "50" artinya 1 Rol = 50 Meter, "12" artinya 1 Dus = 12 Pcs).'],
            ['   - Tips: Boleh juga mengisi lebih dari 1 satuan dipisah koma (contoh: Satuan Alternatif: "Rol, Yard", Isi Rasio: "50, 0.9144").'],
            ['5. Jika nama Kategori / Brand / Satuan / Warna belum ada di master data, sistem akan OTOMATIS mendaftarkannya saat import.'],
            [''],
            ['MASTER DATA', 'DAFTAR NAMA YANG SUDAH TERDAFTAR (BISA LANGSUNG DI-COPY)'],
            ['Tipe Item Valid', 'Stok, Aset, Jasa'],
            ['Tipe Pelacakan Valid', 'None, Serial Number, Batch'],
            ['Status Valid', 'Active, Inactive'],
            ['Kategori Barang', $allKategoris ?: 'Belum ada data'],
            ['Brand / Merk', $allBrands ?: 'Tanpa Merk (Kosongkan jika tidak ada)'],
            ['Tipe Barang', $allTipes ?: 'Belum ada data'],
            ['Satuan Dasar', $allSatuans ?: 'Belum ada data'],
            ['Warna', $allWarnas ?: 'Tanpa Warna (Kosongkan jika tidak ada)'],
        ];

        $xlsx = SimpleXLSXGen::create('Template Import Barang');
        $xlsx->addSheet($templateRows, 'Template Import Barang');
        $xlsx->addSheet($referenceRows, 'Referensi Master Data');

        return response()->streamDownload(function () use ($xlsx) {
            $xlsx->saveAs('php://output');
        }, 'template_import_barang.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-barang');

        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        $file = $request->file('excel_file');
        
        if (!$xlsx = SimpleXLSX::parse($file->path())) {
            return response()->json([
                'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError(),
            ], 422);
        }

        $rows = $xlsx->rows();
        if (count($rows) < 2) {
            return response()->json([
                'message' => 'File Excel kosong atau tidak memiliki data!',
            ], 422);
        }

        // Helper decimal parser
        $parseNum = function($val) {
            if ($val === null || trim((string)$val) === '') return 0;
            $clean = trim((string)$val);
            $clean = preg_replace('/[^\d.,]/', '', $clean);
            if (strpos($clean, ',') !== false && strpos($clean, '.') !== false) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } elseif (strpos($clean, ',') !== false) {
                $clean = str_replace(',', '.', $clean);
            }
            return (float) $clean;
        };

        // Collect unique values to optimize queries
        $kategoriNames = [];
        $brandNames = [];
        $tipeNames = [];
        $satuanNames = [];
        $warnaNames = [];
        $excelCodes = [];
        $excelBarcodes = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) {
                continue;
            }
            if (isset($row[0]) && trim($row[0]) !== '') {
                $excelCodes[] = trim($row[0]);
            }
            if (isset($row[1]) && trim($row[1]) !== '') {
                $excelBarcodes[] = trim($row[1]);
            }
            if (isset($row[6]) && trim($row[6]) !== '') {
                $kategoriNames[] = trim($row[6]);
            }
            if (isset($row[7]) && trim($row[7]) !== '') {
                $brandNames[] = trim($row[7]);
            }
            if (isset($row[8]) && trim($row[8]) !== '') {
                $tipeNames[] = trim($row[8]);
            }
            if (isset($row[9]) && trim($row[9]) !== '') {
                $satuanNames[] = trim($row[9]);
            }
            // Collect alternate units from column 10
            if (isset($row[10]) && trim($row[10]) !== '') {
                $altUnits = preg_split('/[,;]+/', trim($row[10]));
                foreach ($altUnits as $u) {
                    $uName = trim(preg_replace('/^(?:1\s+)?([a-zA-Z0-9\s]+?)(?:\s*=|\s*:).*$/', '$1', $u));
                    if (!empty($uName)) {
                        $satuanNames[] = $uName;
                    }
                }
            }
            if (isset($row[12]) && trim($row[12]) !== '') {
                $warnaNames[] = trim($row[12]);
            }
        }

        // Cache parameters (only those found in Excel sheet)
        $categories = [];
        if (!empty($kategoriNames)) {
            $categories = MasterKategoriBarang::active()
                ->whereIn('nama_kategori', array_unique($kategoriNames))
                ->get()
                ->mapWithKeys(fn($item) => [strtolower(trim($item->nama_kategori)) => $item->id])
                ->toArray();
        }

        $brands = [];
        if (!empty($brandNames)) {
            $brands = MasterBrand::active()
                ->whereIn('nama_brand', array_unique($brandNames))
                ->get()
                ->mapWithKeys(fn($item) => [strtolower(trim($item->nama_brand)) => $item->id])
                ->toArray();
        }

        $types = [];
        if (!empty($tipeNames)) {
            $types = MasterTipeBarang::active()
                ->whereIn('nama_tipe', array_unique($tipeNames))
                ->get()
                ->mapWithKeys(fn($item) => [strtolower(trim($item->nama_tipe)) => $item->id])
                ->toArray();
        }

        $satuans = [];
        if (!empty($satuanNames)) {
            $satuanModels = MasterSatuan::active()
                ->where(function($q) use ($satuanNames) {
                    $q->whereIn('nama_satuan', array_unique($satuanNames))
                      ->orWhereIn('singkatan', array_unique($satuanNames));
                })
                ->get();
            foreach ($satuanModels as $item) {
                $satuans[strtolower(trim($item->nama_satuan))] = $item->id;
                $satuans[strtolower(trim($item->singkatan))] = $item->id;
            }
        }

        $warnas = [];
        if (!empty($warnaNames)) {
            $warnas = MasterWarna::active()
                ->whereIn('nama_warna', array_unique($warnaNames))
                ->get()
                ->mapWithKeys(fn($item) => [strtolower(trim($item->nama_warna)) => $item->id])
                ->toArray();
        }

        $activeCompanyId = session('active_company_id');

        // Batch fetch existing DB codes/barcodes for active company
        $existingDbCodes = [];
        if (!empty($excelCodes)) {
            $existingDbCodes = DB::table('master_barang')
                ->whereIn('kode_barang', array_unique($excelCodes))
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->pluck('kode_barang')
                ->map(fn($item) => strtolower($item))
                ->toArray();
        }

        $existingDbBarcodes = [];
        if (!empty($excelBarcodes)) {
            $existingDbBarcodes = DB::table('master_barang')
                ->whereIn('barcode', array_unique($excelBarcodes))
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->pluck('barcode')
                ->map(fn($item) => strtolower($item))
                ->toArray();
        }

        // Track codes/barcodes processed in this file to avoid internal duplicates
        $processedCodes = [];
        $processedBarcodes = [];

        $createdCounts = [
            'categories' => 0,
            'brands'     => 0,
            'types'      => 0,
            'satuans'    => 0,
            'warnas'     => 0,
        ];
        $importedItemsCount = 0;

        try {
            DB::beginTransaction();

            // Skip header (row index 0)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Stop if row is completely empty
                if (empty(array_filter($row))) {
                    continue;
                }

                $rawKode        = isset($row[0]) ? trim($row[0]) : '';
                $rawBarcode     = isset($row[1]) ? trim($row[1]) : '';
                $rawNama        = isset($row[2]) ? trim($row[2]) : '';
                $rawAlias       = isset($row[3]) ? trim($row[3]) : '';
                $rawTipeItem    = isset($row[4]) ? trim($row[4]) : 'Stok';
                $rawPelacakan   = isset($row[5]) ? trim($row[5]) : 'None';
                $rawKategori    = isset($row[6]) ? trim($row[6]) : '';
                $rawBrand       = isset($row[7]) ? trim($row[7]) : '';
                $rawTipeBarang  = isset($row[8]) ? trim($row[8]) : '';
                $rawSatuan      = isset($row[9]) ? trim($row[9]) : '';
                $rawSatuanAlt   = isset($row[10]) ? trim($row[10]) : '';
                $rawRasioAlt    = isset($row[11]) ? trim($row[11]) : '';
                $rawWarna       = isset($row[12]) ? trim($row[12]) : '';
                $rawUkuran      = isset($row[13]) ? trim($row[13]) : '';
                $rawHargaRetail = isset($row[14]) ? $parseNum($row[14]) : 0;
                $rawHargaGrosir = isset($row[15]) ? $parseNum($row[15]) : 0;
                $rawHargaTender = isset($row[16]) ? $parseNum($row[16]) : 0;
                $rawSpesifikasi = isset($row[17]) ? trim($row[17]) : '';
                $rawBerat       = isset($row[18]) ? $parseNum($row[18]) : 0;
                $rawPanjang     = isset($row[19]) ? $parseNum($row[19]) : 0;
                $rawLebar       = isset($row[20]) ? $parseNum($row[20]) : 0;
                $rawTinggi      = isset($row[21]) ? $parseNum($row[21]) : 0;
                $rawMinStok     = isset($row[22]) ? $parseNum($row[22]) : 0;
                $rawStatus      = isset($row[23]) ? trim($row[23]) : 'Active';

                $rowNum = $i + 1;

                // ── Required Fields Validation ──
                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Barang wajib diisi.");
                }

                if (!in_array($rawTipeItem, ['Stok', 'Aset', 'Jasa'])) {
                    throw new \Exception("Baris {$rowNum}: Tipe Item '{$rawTipeItem}' tidak valid (pilihan valid: Stok, Aset, Jasa).");
                }

                if (!in_array($rawPelacakan, ['None', 'Serial Number', 'Batch'])) {
                    $rawPelacakan = 'None';
                }

                // ── Auto-Connect / Auto-Create Missing Kategori ──
                if (empty($rawKategori)) {
                    throw new \Exception("Baris {$rowNum}: Kategori wajib diisi.");
                }
                $lcKat = strtolower($rawKategori);
                if (!isset($categories[$lcKat])) {
                    $newKat = MasterKategoriBarang::create([
                        'company_id'    => $activeCompanyId,
                        'kode_kategori' => CodeGeneratorHelper::generate('master_kategori_barang', 'KAT-', 'kode_kategori'),
                        'nama_kategori' => $rawKategori,
                        'status'        => 'Active',
                    ]);
                    $categories[$lcKat] = $newKat->id;
                    $createdCounts['categories']++;
                }
                $kategoriId = $categories[$lcKat];

                // ── Auto-Connect / Auto-Create Missing Brand ──
                $brandId = null;
                if (!empty($rawBrand)) {
                    $lcBrand = strtolower($rawBrand);
                    if (!isset($brands[$lcBrand])) {
                        $newBrand = MasterBrand::create([
                            'company_id' => $activeCompanyId,
                            'kode_brand' => CodeGeneratorHelper::generate('master_brand', 'BRD-', 'kode_brand'),
                            'nama_brand' => $rawBrand,
                            'status'     => 'Active',
                        ]);
                        $brands[$lcBrand] = $newBrand->id;
                        $createdCounts['brands']++;
                    }
                    $brandId = $brands[$lcBrand];
                }

                // ── Auto-Connect / Auto-Create Missing Tipe Barang ──
                if (empty($rawTipeBarang)) {
                    throw new \Exception("Baris {$rowNum}: Tipe Barang wajib diisi.");
                }
                $lcTipe = strtolower($rawTipeBarang);
                if (!isset($types[$lcTipe])) {
                    $newTipe = MasterTipeBarang::create([
                        'company_id' => $activeCompanyId,
                        'kode_tipe'  => CodeGeneratorHelper::generate('master_tipe_barang', 'TPE-', 'kode_tipe'),
                        'nama_tipe'  => $rawTipeBarang,
                        'status'     => 'Active',
                    ]);
                    $types[$lcTipe] = $newTipe->id;
                    $createdCounts['types']++;
                }
                $tipeBarangId = $types[$lcTipe];

                // ── Auto-Connect / Auto-Create Missing Satuan Dasar ──
                if (empty($rawSatuan)) {
                    throw new \Exception("Baris {$rowNum}: Satuan Dasar wajib diisi.");
                }
                $lcSatuan = strtolower($rawSatuan);
                if (!isset($satuans[$lcSatuan])) {
                    $newSat = MasterSatuan::create([
                        'company_id'  => $activeCompanyId,
                        'kode_satuan' => CodeGeneratorHelper::generate('master_satuan', 'SAT-', 'kode_satuan'),
                        'nama_satuan' => $rawSatuan,
                        'singkatan'   => substr($rawSatuan, 0, 10),
                        'status'      => 'Active',
                    ]);
                    $satuans[$lcSatuan] = $newSat->id;
                    $createdCounts['satuans']++;
                }
                $satuanId = $satuans[$lcSatuan];

                // ── Auto-Connect / Auto-Create Missing Warna ──
                $warnaId = null;
                if (!empty($rawWarna)) {
                    $lcWarna = strtolower($rawWarna);
                    if (!isset($warnas[$lcWarna])) {
                        $newWarna = MasterWarna::create([
                            'company_id' => $activeCompanyId,
                            'kode_warna' => CodeGeneratorHelper::generate('master_warna', 'WRN-', 'kode_warna'),
                            'nama_warna' => $rawWarna,
                            'status'     => 'Active',
                        ]);
                        $warnas[$lcWarna] = $newWarna->id;
                        $createdCounts['warnas']++;
                    }
                    $warnaId = $warnas[$lcWarna];
                }

                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    $rawStatus = 'Active';
                }

                // ── Code & Barcode Generation ──
                $kode = $rawKode;
                if (empty($kode)) {
                    $kode = CodeGeneratorHelper::generate('master_barang', 'BRG-', 'kode_barang');
                }

                // Internal file uniqueness checks
                if (in_array(strtolower($kode), $processedCodes)) {
                    throw new \Exception("Baris {$rowNum}: Kode Barang '{$kode}' duplikat di dalam file.");
                }
                $processedCodes[] = strtolower($kode);

                // DB uniqueness checks
                if (!empty($rawKode) && in_array(strtolower($kode), $existingDbCodes)) {
                    throw new \Exception("Baris {$rowNum}: Kode Barang '{$kode}' sudah digunakan di database.");
                }

                $barcodeVal = $rawBarcode;
                if (empty($barcodeVal)) {
                    $barcodeVal = $kode;
                }

                if (in_array(strtolower($barcodeVal), $processedBarcodes)) {
                    throw new \Exception("Baris {$rowNum}: Barcode '{$barcodeVal}' duplikat di dalam file.");
                }
                $processedBarcodes[] = strtolower($barcodeVal);

                if (!empty($rawBarcode) && in_array(strtolower($barcodeVal), $existingDbBarcodes)) {
                    throw new \Exception("Baris {$rowNum}: Barcode '{$barcodeVal}' sudah digunakan di database.");
                }

                // ── Create Master Record ──
                $newBarang = MasterBarang::create([
                    'company_id'         => $activeCompanyId,
                    'kode_barang'        => $kode,
                    'barcode'            => $barcodeVal,
                    'nama_barang'        => $rawNama,
                    'nama_alias'         => $rawAlias,
                    'tipe_item'          => $rawTipeItem,
                    'tipe_pelacakan'     => $rawPelacakan,
                    'kategori_barang_id' => $kategoriId,
                    'brand_id'           => $brandId,
                    'tipe_barang_id'     => $tipeBarangId,
                    'satuan_id'          => $satuanId,
                    'warna_id'           => $warnaId,
                    'ukuran'             => $rawUkuran,
                    'spesifikasi'        => $rawSpesifikasi,
                    'berat'              => in_array($rawTipeItem, ['Stok', 'Bundle']) ? (float) $rawBerat : 0,
                    'panjang'            => in_array($rawTipeItem, ['Stok', 'Bundle']) ? (float) $rawPanjang : 0,
                    'lebar'              => in_array($rawTipeItem, ['Stok', 'Bundle']) ? (float) $rawLebar : 0,
                    'tinggi'             => in_array($rawTipeItem, ['Stok', 'Bundle']) ? (float) $rawTinggi : 0,
                    'harga_jual_retail'  => (float) $rawHargaRetail,
                    'harga_jual_grosir'  => (float) $rawHargaGrosir,
                    'harga_jual_tender'  => (float) $rawHargaTender,
                    'minimum_stok'       => in_array($rawTipeItem, ['Stok', 'Bundle']) ? (float) $rawMinStok : 0,
                    'status'             => $rawStatus,
                ]);

                // ── Auto Insert Multi-UoM Conversions (Smart Human-Friendly Parser) ──
                if (!empty($rawSatuanAlt)) {
                    $altNames = preg_split('/[,;]+/', $rawSatuanAlt);
                    $altRatios = preg_split('/[,;]+/', $rawRasioAlt);

                    foreach ($altNames as $idx => $altNameRaw) {
                        $altName = trim($altNameRaw);
                        if (empty($altName)) continue;

                        // Check if user wrote in natural format like "1 Rol = 50 Meter" or "Rol: 50"
                        $ratioVal = 1;
                        if (preg_match('/(?:=\s*|:\s*)(\d+(?:[.,]\d+)?)/', $altName, $matchRatio)) {
                            $ratioVal = $parseNum($matchRatio[1]);
                            $altName = trim(preg_replace('/^(?:1\s+)?([a-zA-Z0-9\s]+?)(?:\s*=|\s*:).*$/', '$1', $altName));
                        } elseif (isset($altRatios[$idx]) && trim($altRatios[$idx]) !== '') {
                            $ratioVal = $parseNum($altRatios[$idx]);
                        }
                        if ($ratioVal <= 0) $ratioVal = 1;

                        $lcConvSatuan = strtolower($altName);
                        if (!isset($satuans[$lcConvSatuan])) {
                            $newSat = MasterSatuan::create([
                                'company_id'  => $activeCompanyId,
                                'kode_satuan' => CodeGeneratorHelper::generate('master_satuan', 'SAT-', 'kode_satuan'),
                                'nama_satuan' => $altName,
                                'singkatan'   => substr($altName, 0, 10),
                                'status'      => 'Active',
                            ]);
                            $satuans[$lcConvSatuan] = $newSat->id;
                            $createdCounts['satuans']++;
                        }
                        $convSatuanId = $satuans[$lcConvSatuan];

                        MasterBarangKonversi::create([
                            'barang_id'      => $newBarang->id,
                            'satuan_id'      => $convSatuanId,
                            'faktor_pengali' => $ratioVal,
                            'is_satuan_beli' => true,
                            'is_satuan_jual' => true,
                        ]);
                    }
                }

                $importedItemsCount++;
            }

            DB::commit();
            $this->clearBarangCache();

            // Build informative message
            $msgDetails = [];
            if ($createdCounts['categories'] > 0) $msgDetails[] = "{$createdCounts['categories']} Kategori baru";
            if ($createdCounts['brands'] > 0)     $msgDetails[] = "{$createdCounts['brands']} Brand/Merk baru";
            if ($createdCounts['types'] > 0)      $msgDetails[] = "{$createdCounts['types']} Tipe Barang baru";
            if ($createdCounts['satuans'] > 0)    $msgDetails[] = "{$createdCounts['satuans']} Satuan baru";
            if ($createdCounts['warnas'] > 0)     $msgDetails[] = "{$createdCounts['warnas']} Warna baru";

            $extraMsg = !empty($msgDetails) ? ' (Otomatis mendaftarkan: ' . implode(', ', $msgDetails) . ')' : '';

            return response()->json([
                'success' => true,
                'message' => "Import {$importedItemsCount} data barang berhasil dilakukan!{$extraMsg}",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import Excel Error: ' . $e->getMessage());
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    private function clearBarangCache()
    {
        $cId = session('active_company_id') ?? 0;
        \Illuminate\Support\Facades\Cache::forget('master_barang_summary_totals_comp_' . $cId);
        \Illuminate\Support\Facades\Cache::forget('master_barang_count_unfiltered_comp_' . $cId . '_all');
        \Illuminate\Support\Facades\Cache::forget('master_barang_count_unfiltered_comp_' . $cId . '_Stok');
        \Illuminate\Support\Facades\Cache::forget('master_barang_count_unfiltered_comp_' . $cId . '_Jasa');
        \Illuminate\Support\Facades\Cache::forget('master_barang_count_unfiltered_comp_' . $cId . '_Aset');
        \Illuminate\Support\Facades\Cache::forget('master_barang_count_unfiltered_comp_' . $cId . '_Bundle');
    }
}
