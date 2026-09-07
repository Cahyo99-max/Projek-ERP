<?php

namespace App\Http\Controllers;

use App\Models\MasterKategoriBarang;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterKategoriBarangController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-kategori-barang');

        // Build flat category tree for select option dropdown (only active ones)
        $parentOptions = $this->getCategoryTreeOptions();

        return view('data_master.kategori_barang.viewkategori', compact('parentOptions'));
    }

    /**
     * DataTables AJAX endpoint — server-side processing for performance.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-kategori-barang');

        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);
        $search = trim($request->get('search')['value'] ?? '');
        $orderCol   = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir   = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                        ? $request->get('order')[0]['dir']
                        : 'asc';

        // Allowed sortable columns mapped to DB columns
        $columns = [
            0 => 'master_kategori_barang.id',
            1 => 'master_kategori_barang.kode_kategori',
            2 => 'master_kategori_barang.nama_kategori',
            3 => 'parent.nama_kategori',
            4 => 'master_kategori_barang.urutan',
            5 => 'master_kategori_barang.deskripsi',
            6 => 'master_kategori_barang.status',
        ];
        $orderColumn = $columns[$orderCol] ?? 'master_kategori_barang.id';
        $activeCompanyId = session('active_company_id');

        $query = MasterKategoriBarang::query()
            ->forActiveCompany()
            ->withTrashed(false)
            ->leftJoin('master_kategori_barang as parent', 'master_kategori_barang.parent_id', '=', 'parent.id')
            ->select([
                'master_kategori_barang.id',
                'master_kategori_barang.kode_kategori',
                'master_kategori_barang.nama_kategori',
                'master_kategori_barang.parent_id',
                'master_kategori_barang.urutan',
                'master_kategori_barang.deskripsi',
                'master_kategori_barang.status',
                'parent.nama_kategori as parent_nama',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (strlen($search) <= 8) {
                    $q->where('master_kategori_barang.kode_kategori', 'like', "{$search}%")
                      ->orWhere('master_kategori_barang.nama_kategori', 'like', "{$search}%");
                } else {
                    $q->where('master_kategori_barang.kode_kategori', 'like', "%{$search}%")
                      ->orWhere('master_kategori_barang.nama_kategori', 'like', "%{$search}%")
                      ->orWhere('parent.nama_kategori', 'like', "%{$search}%")
                      ->orWhere('master_kategori_barang.deskripsi', 'like', "%{$search}%");
                }
            });
            $totalFiltered = $query->count();
            $totalData     = $totalFiltered;
        } else {
            // Highly optimized fast COUNT estimate using EXPLAIN to scale to 7M+ rows
            $cacheKey = 'kategori_barang_count_unfiltered_comp_' . ($activeCompanyId ?? 0);
            $totalData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($activeCompanyId) {
                try {
                    $sql = "SELECT id FROM master_kategori_barang WHERE deleted_at IS NULL";
                    if ($activeCompanyId) {
                        $sql .= " AND company_id = " . intval($activeCompanyId);
                    }
                    $explain = DB::select("EXPLAIN " . $sql);
                    return $explain[0]->rows ?? 10000;
                } catch (\Exception $e) {
                    return 10000;
                }
            });
            $totalFiltered = $totalData;
        }

        $rows = $query->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function ($k, $index) use ($start) {
            return [
                'no'           => $start + $index + 1,
                'id'           => $k->id,
                'kode_kategori'=> e($k->kode_kategori),
                'nama_kategori'=> e($k->nama_kategori),
                'parent_nama'  => $k->parent_nama ? e($k->parent_nama) : null,
                'urutan'       => $k->urutan,
                'deskripsi'    => $k->deskripsi ? e($k->deskripsi) : null,
                'status'       => $k->status,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('tambah-kategori-barang');
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'nama_kategori' => 'required|string|max:30',
            'parent_id'     => 'nullable|integer|exists:master_kategori_barang,id',
            'deskripsi'     => 'nullable|string|max:30',
            'urutan'        => 'nullable|integer|min:1',
            'status'        => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $kode = CodeGeneratorHelper::generate('master_kategori_barang', 'KTG-', 'kode_kategori');

            MasterKategoriBarang::create([
                'company_id'    => $activeCompanyId,
                'kode_kategori' => $kode,
                'nama_kategori' => $request->nama_kategori,
                'parent_id'     => $request->parent_id ?: null,
                'deskripsi'     => $request->deskripsi,
                'urutan'        => $request->urutan,
                'status'        => $request->status,
            ]);

            DB::commit();
            return response()->json(['message' => 'Kategori barang berhasil ditambahkan!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error store kategori: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat menyimpan kategori.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store kategori: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah kategori.'], 500);
        }
    }

    public function edit(MasterKategoriBarang $kategori)
    {
        $this->authorize('edit-kategori-barang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $kategori->company_id && $kategori->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Kategori ini milik PT lain.'], 403);
        }
        return response()->json(['kategori' => $kategori]);
    }

    public function update(Request $request, MasterKategoriBarang $kategori)
    {
        $this->authorize('edit-kategori-barang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $kategori->company_id && $kategori->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit kategori milik PT lain.'], 403);
        }

        $request->validate([
            'nama_kategori' => 'required|string|max:30',
            'parent_id'     => 'nullable|integer|exists:master_kategori_barang,id',
            'deskripsi'     => 'nullable|string|max:30',
            'urutan'        => 'nullable|integer|min:1',
            'status'        => 'required|in:Active,Inactive',
        ]);

        // Prevent circular reference: a category cannot be its own parent
        if ($request->parent_id && (int)$request->parent_id === $kategori->id) {
            return response()->json(['message' => 'Gagal: Kategori tidak boleh menjadi induk dari dirinya sendiri.'], 422);
        }

        // Prevent setting a child as parent (circular reference deeper)
        if ($request->parent_id) {
            $parent = MasterKategoriBarang::find($request->parent_id);
            if ($parent && $this->isDescendantOf($parent, $kategori->id)) {
                return response()->json(['message' => 'Gagal: Kategori induk tidak boleh merupakan subkategori dari dirinya sendiri.'], 422);
            }
        }

        try {
            DB::beginTransaction();

            $kategori->update([
                'nama_kategori' => $request->nama_kategori,
                'parent_id'     => $request->parent_id ?: null,
                'deskripsi'     => $request->deskripsi,
                'urutan'        => $request->urutan,
                'status'        => $request->status,
            ]);

            DB::commit();
            return response()->json(['message' => 'Kategori barang berhasil diperbarui!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error update kategori: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengupdate kategori.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update kategori: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate kategori.'], 500);
        }
    }

    public function destroy(MasterKategoriBarang $kategori)
    {
        $this->authorize('hapus-kategori-barang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $kategori->company_id && $kategori->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus kategori milik PT lain.'], 403);
        }

        // Business Rule: restrict deletion if category has children/subcategories
        if ($kategori->children()->count() > 0) {
            return response()->json(['message' => 'Kategori tidak dapat dihapus karena memiliki subkategori aktif.'], 422);
        }

        try {
            DB::beginTransaction();
            $kategori->delete();
            DB::commit();

            return response()->json(['message' => 'Kategori barang berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete kategori: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus kategori.'], 500);
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

    /**
     * Check if a category is descendant of another
     */
    private function isDescendantOf($category, $possibleAncestorId)
    {
        if ($category->parent_id == $possibleAncestorId) {
            return true;
        }
        if ($category->parent) {
            return $this->isDescendantOf($category->parent, $possibleAncestorId);
        }
        return false;
    }

    public function exportExcel()
    {
        $this->authorize('lihat-kategori-barang');

        $categories = MasterKategoriBarang::forActiveCompany()
            ->with('parent')
            ->orderBy('urutan', 'asc')
            ->cursor();

        $rows = [
            ['Kode Kategori', 'Nama Kategori', 'Kategori Induk (Parent)', 'Urutan', 'Deskripsi', 'Status']
        ];

        foreach ($categories as $k) {
            $rows[] = [
                $k->kode_kategori,
                $k->nama_kategori,
                $k->parent->nama_kategori ?? '',
                $k->urutan !== null ? (int) $k->urutan : '',
                $k->deskripsi ?? '',
                $k->status
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_kategori_barang_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-kategori-barang');

        $rows = [
            ['Kode Kategori', 'Nama Kategori', 'Kategori Induk (Parent)', 'Urutan', 'Deskripsi', 'Status'],
            ['KTG-EXAMPLE01', 'Elektronik', '', 1, 'Kategori utama barang elektronik', 'Active'],
            ['', 'Laptop', 'Elektronik', 1, 'Subkategori laptop di bawah Elektronik', 'Active']
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_kategori.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-kategori-barang');

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

        // Collect unique codes and parent names from Excel rows to optimize lookups
        $parentNames = [];
        $excelCodes = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) {
                continue;
            }
            if (isset($row[0]) && trim($row[0]) !== '') {
                $excelCodes[] = trim($row[0]);
            }
            if (isset($row[2]) && trim($row[2]) !== '') {
                $parentNames[] = trim($row[2]);
            }
        }

        $activeCompanyId = session('active_company_id');

        // Cache parents only (scoped to active company)
        $categoriesMap = [];
        if (!empty($parentNames)) {
            $categoriesMap = MasterKategoriBarang::forActiveCompany()
                ->whereIn('nama_kategori', array_unique($parentNames))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [strtolower(trim($item->nama_kategori)) => $item->id];
                })->toArray();
        }

        // Fetch existing database codes in one single query for active company
        $existingDbCodes = [];
        if (!empty($excelCodes)) {
            $existingDbCodes = DB::table('master_kategori_barang')
                ->whereIn('kode_kategori', array_unique($excelCodes))
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->pluck('kode_kategori')
                ->map(fn($item) => strtolower($item))
                ->toArray();
        }

        $processedCodes = [];

        try {
            DB::beginTransaction();

            // Skip header (row index 0)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Stop if row is completely empty
                if (empty(array_filter($row))) {
                    continue;
                }

                $rawKode      = isset($row[0]) ? trim($row[0]) : '';
                $rawNama      = isset($row[1]) ? trim($row[1]) : '';
                $rawParent    = isset($row[2]) ? trim($row[2]) : '';
                $rawUrutan    = isset($row[3]) && trim($row[3]) !== '' ? (int) trim($row[3]) : null;
                $rawDeskripsi = isset($row[4]) ? trim($row[4]) : '';
                $rawStatus    = isset($row[5]) ? trim($row[5]) : '';

                $rowNum = $i + 1;

                // ── Required Fields Validation ──
                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Kategori wajib diisi.");
                }

                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus diisi 'Active' atau 'Inactive'.");
                }

                // ── Parent Lookup ──
                $parentId = null;
                if (!empty($rawParent)) {
                    $lcParent = strtolower($rawParent);
                    if (!isset($categoriesMap[$lcParent])) {
                        throw new \Exception("Baris {$rowNum}: Kategori Induk '{$rawParent}' tidak ditemukan. Pastikan ditulis dengan benar atau taruh baris Kategori Induk di atas subkategori.");
                    }
                    $parentId = $categoriesMap[$lcParent];
                }

                // ── Uniqueness / Code Generation ──
                $kode = $rawKode;
                if (empty($kode)) {
                    $kode = CodeGeneratorHelper::generate('master_kategori_barang', 'KTG-', 'kode_kategori');
                }

                if (in_array(strtolower($kode), $processedCodes)) {
                    throw new \Exception("Baris {$rowNum}: Kode Kategori '{$kode}' duplikat di dalam file.");
                }
                $processedCodes[] = strtolower($kode);

                if (!empty($rawKode) && in_array(strtolower($kode), $existingDbCodes)) {
                    throw new \Exception("Baris {$rowNum}: Kode Kategori '{$kode}' sudah digunakan di database.");
                }

                // ── Create Record ──
                $newKategori = MasterKategoriBarang::create([
                    'company_id'    => $activeCompanyId,
                    'kode_kategori' => $kode,
                    'nama_kategori' => $rawNama,
                    'parent_id'     => $parentId,
                    'urutan'        => $rawUrutan,
                    'deskripsi'     => $rawDeskripsi,
                    'status'        => $rawStatus,
                ]);

                // Update in-memory map for subsequently processed child categories in the same file
                $categoriesMap[strtolower(trim($rawNama))] = $newKategori->id;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import data kategori barang berhasil dilakukan!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import Kategori Excel Error: ' . $e->getMessage());
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
