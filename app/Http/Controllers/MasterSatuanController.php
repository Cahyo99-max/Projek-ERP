<?php

namespace App\Http\Controllers;

use App\Models\MasterSatuan;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterSatuanController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-satuan');
        return view('data_master.satuan_barang.viewsatuan');
    }

    /**
     * DataTables AJAX endpoint — server-side processing for performance.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-satuan');

        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);
        $search = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir']
                    : 'asc';

        // Map DataTables column index → DB column
        $columns = [
            0 => 'id',
            1 => 'kode_satuan',
            2 => 'singkatan',
            3 => 'nama_satuan',
            4 => 'deskripsi',
            5 => 'status',
        ];
        $orderColumn = $columns[$orderCol] ?? 'id';
        $activeCompanyId = session('active_company_id');

        $query = MasterSatuan::forActiveCompany();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_satuan', 'like', "%{$search}%")
                  ->orWhere('singkatan', 'like', "%{$search}%")
                  ->orWhere('nama_satuan', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $cacheKey = 'master_satuan_total_count_comp_' . ($activeCompanyId ?? 0);
        $totalData = Cache::remember($cacheKey, 60, function () use ($activeCompanyId) {
            return MasterSatuan::forActiveCompany()->count();
        });

        if ($search === '') {
            $totalFiltered = $totalData;
        } else {
            $totalFiltered = $query->count();
        }

        $rows = $query->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function ($s, $index) use ($start) {
            return [
                'no'          => $start + $index + 1,
                'id'          => $s->id,
                'kode_satuan' => e($s->kode_satuan),
                'singkatan'   => e($s->singkatan),
                'nama_satuan' => e($s->nama_satuan),
                'deskripsi'   => $s->deskripsi ? e($s->deskripsi) : null,
                'status'      => $s->status,
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
        $this->authorize('tambah-satuan');
        $activeCompanyId = session('active_company_id');
        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'singkatan'   => 'required|string|max:4|unique:master_satuan,singkatan,NULL,id,deleted_at,NULL' . $compWhere,
            'nama_satuan' => 'required|string|max:30',
            'deskripsi'   => 'nullable|string|max:30',
            'status'      => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $kode = CodeGeneratorHelper::generate('master_satuan', 'STN-', 'kode_satuan');

            MasterSatuan::create([
                'company_id'  => $activeCompanyId,
                'kode_satuan' => $kode,
                'singkatan'   => strtoupper($request->singkatan),
                'nama_satuan' => $request->nama_satuan,
                'deskripsi'   => $request->deskripsi,
                'status'      => $request->status,
            ]);

            DB::commit();
            Cache::forget('master_satuan_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Satuan berhasil ditambahkan!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error store satuan: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat menambah satuan.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store satuan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah satuan.'], 500);
        }
    }

    public function edit(MasterSatuan $satuan)
    {
        $this->authorize('edit-satuan');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $satuan->company_id && $satuan->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Satuan ini milik PT lain.'], 403);
        }
        return response()->json(['satuan' => $satuan]);
    }

    public function update(Request $request, MasterSatuan $satuan)
    {
        $this->authorize('edit-satuan');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $satuan->company_id && $satuan->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit satuan milik PT lain.'], 403);
        }

        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'singkatan'   => 'required|string|max:4|unique:master_satuan,singkatan,' . $satuan->id . ',id,deleted_at,NULL' . $compWhere,
            'nama_satuan' => 'required|string|max:30',
            'deskripsi'   => 'nullable|string|max:30',
            'status'      => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $satuan->update([
                'singkatan'   => strtoupper($request->singkatan),
                'nama_satuan' => $request->nama_satuan,
                'deskripsi'   => $request->deskripsi,
                'status'      => $request->status,
            ]);

            DB::commit();
            Cache::forget('master_satuan_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Satuan berhasil diperbarui!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error update satuan: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengupdate satuan.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update satuan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate satuan.'], 500);
        }
    }

    public function destroy(MasterSatuan $satuan)
    {
        $this->authorize('hapus-satuan');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $satuan->company_id && $satuan->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus satuan milik PT lain.'], 403);
        }

        // Business rule: prevent delete if satuan is used in any barang
        $usedCount = \App\Models\MasterBarang::where('satuan_id', $satuan->id)->count();
        if ($usedCount > 0) {
            return response()->json([
                'message' => "Satuan \"{$satuan->nama_satuan}\" tidak dapat dihapus karena sudah digunakan oleh {$usedCount} data barang."
            ], 422);
        }

        try {
            DB::beginTransaction();
            $satuan->delete();
            DB::commit();
            Cache::forget('master_satuan_total_count_comp_' . ($activeCompanyId ?? 0));

            return response()->json(['message' => 'Satuan berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete satuan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus satuan.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-satuan');

        $satuans = MasterSatuan::forActiveCompany()->orderBy('id', 'desc')->get();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Satuan</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Singkatan</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Satuan</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Deskripsi</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>'
            ]
        ];

        foreach ($satuans as $s) {
            $statusStyle = $s->status === 'Active' 
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>' 
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $s->kode_satuan . '</style><center>',
                '<style border="thin">' . $s->singkatan . '</style><center>',
                '<style border="thin">' . $s->nama_satuan . '</style><left>',
                '<style border="thin">' . ($s->deskripsi ?? '') . '</style><left>',
                $statusStyle
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_satuan_barang_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-satuan');

        $rows = [
            ['Kode Satuan', 'Singkatan', 'Nama Satuan', 'Deskripsi', 'Status'],
            ['STN-EXAMPLE01', 'PCS', 'Pieces', 'Satuan bijian standar', 'Active'],
            ['', 'KG', 'Kilogram', 'Satuan berat kilogram', 'Active']
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_satuan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-satuan');

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
        $processedSingkatans = [];

        try {
            DB::beginTransaction();

            // Get the latest number from DB first (including soft deletes)
            $latestCode = DB::table('master_satuan')
                ->where('kode_satuan', 'like', 'STN-%')
                ->orderBy('kode_satuan', 'desc')
                ->value('kode_satuan');

            $maxNumber = 0;
            if ($latestCode) {
                $suffix = substr($latestCode, strlen('STN-'));
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
                $rawSingkatan = isset($row[1]) ? trim($row[1]) : '';
                $rawNama      = isset($row[2]) ? trim($row[2]) : '';
                $rawDeskripsi = isset($row[3]) ? trim($row[3]) : '';
                $rawStatus    = isset($row[4]) ? trim($row[4]) : '';

                $rowNum = $i + 1;

                if (empty($rawSingkatan)) {
                    throw new \Exception("Baris {$rowNum}: Singkatan wajib diisi.");
                }
                if (strlen($rawSingkatan) > 4) {
                    throw new \Exception("Baris {$rowNum}: Singkatan maksimal 4 karakter.");
                }
                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Satuan wajib diisi.");
                }
                if (strlen($rawNama) > 30) {
                    throw new \Exception("Baris {$rowNum}: Nama Satuan maksimal 30 karakter.");
                }
                if (strlen($rawDeskripsi) > 30) {
                    throw new \Exception("Baris {$rowNum}: Deskripsi maksimal 30 karakter.");
                }
                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus diisi 'Active' atau 'Inactive'.");
                }

                $singkatanLower = strtolower($rawSingkatan);

                if (isset($processedSingkatans[$singkatanLower])) {
                    throw new \Exception("Baris {$rowNum}: Singkatan '{$rawSingkatan}' duplikat di dalam file.");
                }
                $processedSingkatans[$singkatanLower] = true;

                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $kode = 'STN-' . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
                } else {
                    // Update maxNumber if user defined a larger one
                    if (str_starts_with($kode, 'STN-')) {
                        $suffix = substr($kode, strlen('STN-'));
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
                    throw new \Exception("Baris {$rowNum}: Kode Satuan '{$kode}' duplikat di dalam file.");
                }
                $processedCodes[$kodeLower] = true;

                $insertData[] = [
                    'company_id'  => session('active_company_id'),
                    'row_num'     => $rowNum,
                    'kode_satuan' => $kode,
                    'singkatan'   => strtoupper($rawSingkatan),
                    'nama_satuan' => $rawNama,
                    'deskripsi'   => $rawDeskripsi,
                    'status'      => $rawStatus,
                    'created_at'  => $now,
                    'updated_at'  => $now
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
            Cache::forget('master_satuan_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Data satuan berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import Excel satuan: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor satuan.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import Excel satuan: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function validateAndInsertChunks(array $chunk)
    {
        $chunkCodes = array_map(fn($item) => $item['kode_satuan'], $chunk);
        $chunkSingkatans = array_map(fn($item) => $item['singkatan'], $chunk);
        $activeCompanyId = session('active_company_id');

        // Batch unique check in single database call
        $dbDuplicates = DB::table('master_satuan')
            ->where(function($q) use ($chunkCodes, $chunkSingkatans) {
                $q->whereIn('kode_satuan', $chunkCodes)
                  ->orWhereIn('singkatan', $chunkSingkatans);
            })
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->whereNull('deleted_at')
            ->get(['kode_satuan', 'singkatan']);

        if ($dbDuplicates->isNotEmpty()) {
            foreach ($chunk as $item) {
                foreach ($dbDuplicates as $dup) {
                    if (strtolower($item['singkatan']) === strtolower($dup->singkatan)) {
                        throw new \Exception("Baris {$item['row_num']}: Singkatan '{$item['singkatan']}' sudah digunakan di database.");
                    }
                    if (strtolower($item['kode_satuan']) === strtolower($dup->kode_satuan)) {
                        throw new \Exception("Baris {$item['row_num']}: Kode Satuan '{$item['kode_satuan']}' sudah digunakan di database.");
                    }
                }
            }
        }

        $finalInsert = array_map(function ($item) {
            unset($item['row_num']);
            return $item;
        }, $chunk);

        DB::table('master_satuan')->insert($finalInsert);
    }
}
