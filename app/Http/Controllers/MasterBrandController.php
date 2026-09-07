<?php

namespace App\Http\Controllers;

use App\Models\MasterBrand;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterBrandController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-brand');
        return view('data_master.brand.viewbrand');
    }

    /**
     * DataTables AJAX endpoint — server-side processing for performance.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-brand');

        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);
        $search = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [0 => 'id', 1 => 'kode_brand', 2 => 'nama_brand', 3 => 'deskripsi', 4 => 'status'];
        $orderColumn = $columns[$orderCol] ?? 'id';
        $activeCompanyId = session('active_company_id');

        $query = MasterBrand::forActiveCompany();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_brand', 'like', "%{$search}%")
                  ->orWhere('nama_brand', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $cacheKey = 'master_brand_total_count_comp_' . ($activeCompanyId ?? 0);
        $totalData = Cache::remember($cacheKey, 60, function () use ($activeCompanyId) {
            return MasterBrand::forActiveCompany()->count();
        });

        if ($search === '') {
            $totalFiltered = $totalData;
        } else {
            $totalFiltered = $query->count();
        }

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($b, $index) use ($start) {
            return [
                'no'         => $start + $index + 1,
                'id'         => $b->id,
                'kode_brand' => e($b->kode_brand),
                'nama_brand' => e($b->nama_brand),
                'deskripsi'  => $b->deskripsi ? e($b->deskripsi) : null,
                'status'     => $b->status,
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
        $this->authorize('tambah-brand');
        $activeCompanyId = session('active_company_id');
        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'nama_brand' => 'required|string|max:20|unique:master_brand,nama_brand,NULL,id,deleted_at,NULL' . $compWhere,
            'deskripsi'  => 'nullable|string|max:30',
            'status'     => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();
            $kode = CodeGeneratorHelper::generate('master_brand', 'BRD-', 'kode_brand');
            MasterBrand::create([
                'company_id' => $activeCompanyId,
                'kode_brand' => $kode,
                'nama_brand' => $request->nama_brand,
                'deskripsi'  => $request->deskripsi,
                'status'     => $request->status,
            ]);
            DB::commit();
            Cache::forget('master_brand_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Brand berhasil ditambahkan!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error store brand: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat menambah brand.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store brand: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah brand.'], 500);
        }
    }

    public function edit(MasterBrand $brand)
    {
        $this->authorize('edit-brand');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $brand->company_id && $brand->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Brand ini milik PT lain.'], 403);
        }
        return response()->json(['brand' => $brand]);
    }

    public function update(Request $request, MasterBrand $brand)
    {
        $this->authorize('edit-brand');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $brand->company_id && $brand->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit brand milik PT lain.'], 403);
        }

        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'nama_brand' => 'required|string|max:20|unique:master_brand,nama_brand,' . $brand->id . ',id,deleted_at,NULL' . $compWhere,
            'deskripsi'  => 'nullable|string|max:30',
            'status'     => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();
            $brand->update([
                'nama_brand' => $request->nama_brand,
                'deskripsi'  => $request->deskripsi,
                'status'     => $request->status,
            ]);
            DB::commit();
            Cache::forget('master_brand_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Brand berhasil diperbarui!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error update brand: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengupdate brand.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update brand: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate brand.'], 500);
        }
    }

    public function destroy(MasterBrand $brand)
    {
        $this->authorize('hapus-brand');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $brand->company_id && $brand->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus brand milik PT lain.'], 403);
        }

        // Prevent deletion if brand is used by any barang
        $usedCount = \App\Models\MasterBarang::where('brand_id', $brand->id)->count();
        if ($usedCount > 0) {
            return response()->json([
                'message' => "Brand \"{$brand->nama_brand}\" tidak dapat dihapus karena digunakan oleh {$usedCount} data barang."
            ], 422);
        }
        try {
            DB::beginTransaction();
            $brand->delete();
            DB::commit();
            Cache::forget('master_brand_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Brand berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete brand: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus brand.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-brand');

        $brands = MasterBrand::forActiveCompany()->orderBy('id', 'desc')->get();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Brand</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Brand</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Deskripsi</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>'
            ]
        ];

        foreach ($brands as $b) {
            $statusStyle = $b->status === 'Active' 
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>' 
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $b->kode_brand . '</style><center>',
                '<style border="thin">' . $b->nama_brand . '</style><left>',
                '<style border="thin">' . ($b->deskripsi ?? '') . '</style><left>',
                $statusStyle
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_brand_barang_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-brand');

        $rows = [
            ['Kode Brand', 'Nama Brand', 'Deskripsi', 'Status'],
            ['BRD-SAMSUNG01', 'SAMSUNG', 'Elektronik HP & TV', 'Active'],
            ['', 'TOYOTA', 'Otomotif mobil', 'Active']
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_brand.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-brand');

        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,csv,txt',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal!',
                'errors'  => $e->errors(),
            ], 422);
        }

        $file = $request->file('excel_file');
        
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        DB::disableQueryLog();

        $rows = [];
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv' || $extension === 'txt') {
            if (($handle = fopen($file->path(), 'r')) !== false) {
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            if (!$xlsx = SimpleXLSX::parse($file->path())) {
                return response()->json([
                    'message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError(),
                ], 422);
            }
            $rows = $xlsx->rows();
        }

        if (count($rows) < 2) {
            return response()->json([
                'message' => 'File kosong atau tidak memiliki data!',
            ], 422);
        }

        $totalRows = count($rows);
        $chunkSize = 2000;
        
        $processedCodes = [];
        $processedNames = [];

        try {
            DB::beginTransaction();

            // Get the latest number from DB first (including soft deletes)
            $latestCode = DB::table('master_brand')
                ->where('kode_brand', 'like', 'BRD-%')
                ->orderBy('kode_brand', 'desc')
                ->value('kode_brand');

            $maxNumber = 0;
            if ($latestCode) {
                $suffix = substr($latestCode, strlen('BRD-'));
                if (is_numeric($suffix)) {
                    $maxNumber = (int) $suffix;
                }
            }

            $insertData = [];
            $now = now()->toDateTimeString();

            for ($i = 1; $i < $totalRows; $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) {
                    continue;
                }

                $rawKode      = isset($row[0]) ? trim($row[0]) : '';
                $rawNama      = isset($row[1]) ? trim($row[1]) : '';
                $rawDeskripsi = isset($row[2]) ? trim($row[2]) : '';
                $rawStatus    = isset($row[3]) ? trim($row[3]) : '';

                $rowNum = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Brand wajib diisi.");
                }
                if (strlen($rawNama) > 20) {
                    throw new \Exception("Baris {$rowNum}: Nama Brand maksimal 20 karakter.");
                }
                if (strlen($rawDeskripsi) > 30) {
                    throw new \Exception("Baris {$rowNum}: Deskripsi maksimal 30 karakter.");
                }
                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus diisi 'Active' atau 'Inactive'.");
                }

                $namaLower = strtolower($rawNama);

                if (isset($processedNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Brand '{$rawNama}' duplikat di dalam file.");
                }
                $processedNames[$namaLower] = true;

                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $kode = 'BRD-' . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
                } else {
                    // Update maxNumber if user defined a larger one
                    if (str_starts_with($kode, 'BRD-')) {
                        $suffix = substr($kode, strlen('BRD-'));
                        if (is_numeric($suffix)) {
                            $numVal = (int) $suffix;
                            if ($numVal > $maxNumber) {
                                $maxNumber = $numVal;
                            }
                        }
                    }
                }

                $kodeLower = strtolower($kode);
                if (isset($processedCodes[$kodeLower])) {
                    throw new \Exception("Baris {$rowNum}: Kode Brand '{$kode}' duplikat di dalam file.");
                }
                $processedCodes[$kodeLower] = true;

                $insertData[] = [
                    'company_id' => session('active_company_id'),
                    'row_num'    => $rowNum,
                    'kode_brand' => $kode,
                    'nama_brand' => $rawNama,
                    'deskripsi'  => $rawDeskripsi,
                    'status'     => $rawStatus,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                if (count($insertData) >= $chunkSize) {
                    $this->validateAndInsertChunks($insertData);
                    $insertData = [];
                }
            }

            if (!empty($insertData)) {
                $this->validateAndInsertChunks($insertData);
            }

            DB::commit();
            $activeCompanyId = session('active_company_id');
            Cache::forget('master_brand_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Data brand berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import Excel brand: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor brand.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import Excel brand: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function validateAndInsertChunks(array $chunk)
    {
        $chunkCodes = array_map(fn($item) => $item['kode_brand'], $chunk);
        $chunkNames = array_map(fn($item) => $item['nama_brand'], $chunk);
        $activeCompanyId = session('active_company_id');

        // Check if any exists in DB for active company
        $dbDuplicates = DB::table('master_brand')
            ->where(function($q) use ($chunkCodes, $chunkNames) {
                $q->whereIn('kode_brand', $chunkCodes)
                  ->orWhereIn('nama_brand', $chunkNames);
            })
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->whereNull('deleted_at')
            ->get(['kode_brand', 'nama_brand']);

        if ($dbDuplicates->isNotEmpty()) {
            foreach ($chunk as $item) {
                foreach ($dbDuplicates as $dup) {
                    if (strtolower($item['nama_brand']) === strtolower($dup->nama_brand)) {
                        throw new \Exception("Baris {$item['row_num']}: Nama Brand '{$item['nama_brand']}' sudah digunakan di database.");
                    }
                    if (strtolower($item['kode_brand']) === strtolower($dup->kode_brand)) {
                        throw new \Exception("Baris {$item['row_num']}: Kode Brand '{$item['kode_brand']}' sudah digunakan di database.");
                    }
                }
            }
        }

        $finalInsert = array_map(function ($item) {
            unset($item['row_num']);
            return $item;
        }, $chunk);

        DB::table('master_brand')->insert($finalInsert);
    }
}
