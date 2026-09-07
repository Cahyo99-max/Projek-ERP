<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class DepartemenController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-departemen');
        return view('master_organisasi.departemen.viewdepartemen');
    }

    /**
     * DataTables AJAX endpoint — server-side processing.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-departemen');

        $draw     = (int) $request->get('draw', 1);
        $start    = (int) $request->get('start', 0);
        $length   = (int) $request->get('length', 10);
        $search   = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [
            0 => 'id',
            1 => 'department_code',
            2 => 'department_name',
            3 => 'description',
            4 => 'status'
        ];
        $orderColumn = $columns[$orderCol] ?? 'id';
        $activeCompanyId = session('active_company_id');

        $query = Department::forActiveCompany();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('department_code', 'like', "%{$search}%")
                  ->orWhere('department_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $cacheKey = 'master_departemen_total_count_comp_' . ($activeCompanyId ?? 0);
        $totalData = Cache::remember($cacheKey, 60, function () use ($activeCompanyId) {
            return Department::forActiveCompany()->count();
        });

        if ($search === '') {
            $totalFiltered = $totalData;
        } else {
            $totalFiltered = $query->count();
        }

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($d, $index) use ($start) {
            return [
                'no'              => $start + $index + 1,
                'id'              => $d->id,
                'department_code' => e($d->department_code),
                'department_name' => e($d->department_name),
                'description'     => $d->description ? e($d->description) : null,
                'status'          => $d->status,
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
        $this->authorize('tambah-departemen');
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'department_code' => [
                'nullable',
                'string',
                'max:4',
                \Illuminate\Validation\Rule::unique('departments', 'department_code')
                    ->where(function($q) use ($activeCompanyId) {
                        if ($activeCompanyId) $q->where('company_id', $activeCompanyId);
                        return $q->whereNull('deleted_at');
                    })
            ],
            'department_name' => 'required|string|max:30',
            'description'     => 'nullable|string|max:30',
            'status'          => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $code = $request->department_code;
            if (empty($code)) {
                $code = CodeGeneratorHelper::generate('departments', 'D', 'department_code', 3);
            } else {
                $code = strtoupper($code);
            }

            Department::create([
                'company_id'      => $activeCompanyId,
                'department_code' => $code,
                'department_name' => $request->department_name,
                'description'     => $request->description,
                'status'          => $request->status,
            ]);

            DB::commit();
            Cache::forget('master_departemen_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Departemen berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store departemen: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah departemen.'], 500);
        }
    }

    public function edit(Department $departemen)
    {
        $this->authorize('edit-departemen');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $departemen->company_id && $departemen->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Departemen ini milik PT lain.'], 403);
        }
        return response()->json(['departemen' => $departemen]);
    }

    public function update(Request $request, Department $departemen)
    {
        $this->authorize('edit-departemen');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $departemen->company_id && $departemen->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit departemen milik PT lain.'], 403);
        }

        $request->validate([
            'department_code' => [
                'required',
                'string',
                'max:4',
                \Illuminate\Validation\Rule::unique('departments', 'department_code')
                    ->ignore($departemen->id)
                    ->where(function($q) use ($activeCompanyId) {
                        if ($activeCompanyId) $q->where('company_id', $activeCompanyId);
                        return $q->whereNull('deleted_at');
                    })
            ],
            'department_name' => 'required|string|max:30',
            'description'     => 'nullable|string|max:30',
            'status'          => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $departemen->update([
                'department_code' => strtoupper($request->department_code),
                'department_name' => $request->department_name,
                'description'     => $request->description,
                'status'          => $request->status,
            ]);

            DB::commit();
            Cache::forget('master_departemen_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Departemen berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update departemen: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate departemen.'], 500);
        }
    }

    public function destroy(Department $departemen)
    {
        $this->authorize('hapus-departemen');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $departemen->company_id && $departemen->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus departemen milik PT lain.'], 403);
        }

        // Business rules check
        $positionCount = \App\Models\Position::where('department_id', $departemen->id)->count();
        if ($positionCount > 0) {
            return response()->json([
                'message' => "Departemen \"{$departemen->department_name}\" tidak dapat dihapus karena digunakan oleh {$positionCount} data jabatan."
            ], 422);
        }

        $karyawanCount = \App\Models\Karyawan::where('departemen_id', $departemen->id)->count();
        if ($karyawanCount > 0) {
            return response()->json([
                'message' => "Departemen \"{$departemen->department_name}\" tidak dapat dihapus karena digunakan oleh {$karyawanCount} data karyawan."
            ], 422);
        }

        try {
            DB::beginTransaction();
            $departemen->delete();
            DB::commit();
            Cache::forget('master_departemen_total_count_comp_' . ($activeCompanyId ?? 0));
            
            return response()->json(['message' => 'Departemen berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete departemen: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus departemen.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-departemen');
        $activeCompanyId = session('active_company_id');

        set_time_limit(0);

        // Use cursor to stream rows efficiently
        $depts = DB::table('departments')
            ->select('department_code', 'department_name', 'description', 'status')
            ->whereNull('deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->orderBy('id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Departemen</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Departemen</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Deskripsi</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($depts as $d) {
            $statusStyle = $d->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $d->department_code . '</style><center>',
                '<style border="thin">' . $d->department_name . '</style><left>',
                '<style border="thin">' . ($d->description ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_departemen_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-departemen');

        $rows = [
            ['Kode Departemen', 'Nama Departemen', 'Deskripsi', 'Status'],
            ['HRD', 'Human Resource', 'Divisi SDM', 'Active'],
            ['', 'Finansial', 'Divisi Keuangan', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_departemen.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-departemen');

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

        $rows      = [];
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv' || $extension === 'txt') {
            if (($handle = fopen($file->path(), 'r')) !== false) {
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") rewind($handle);
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            if (!$xlsx = SimpleXLSX::parse($file->path())) {
                return response()->json(['message' => 'Gagal membaca file Excel: ' . SimpleXLSX::parseError()], 422);
            }
            $rows = $xlsx->rows();
        }

        if (count($rows) < 2) {
            return response()->json(['message' => 'File kosong atau tidak memiliki data!'], 422);
        }

        $processedCodes = [];
        $processedNames = [];
        $chunkSize      = 2000;

        try {
            DB::beginTransaction();
            $activeCompanyId = session('active_company_id');

            // Get latest code number from DB starting with 'D' (scoped to active company)
            $latestCode = DB::table('departments')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->where('department_code', 'like', 'D%')
                ->orderBy('department_code', 'desc')
                ->value('department_code');

            $maxNumber = 0;
            if ($latestCode) {
                $suffix = substr($latestCode, 1); // skip 'D'
                if (is_numeric($suffix)) {
                    $maxNumber = (int) $suffix;
                }
            }

            $insertData = [];
            $now        = now()->toDateTimeString();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;

                $rawKode   = isset($row[0]) ? trim($row[0]) : '';
                $rawNama   = isset($row[1]) ? trim($row[1]) : '';
                $rawDesk   = isset($row[2]) ? trim($row[2]) : '';
                $rawStatus = isset($row[3]) ? trim($row[3]) : '';
                $rowNum    = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Departemen wajib diisi.");
                }
                if (strlen($rawNama) > 30) {
                    throw new \Exception("Baris {$rowNum}: Nama Departemen maksimal 30 karakter.");
                }
                if (!empty($rawKode) && strlen($rawKode) > 4) {
                    throw new \Exception("Baris {$rowNum}: Kode Departemen '{$rawKode}' maksimal 4 karakter.");
                }
                if (strlen($rawDesk) > 30) {
                    throw new \Exception("Baris {$rowNum}: Deskripsi maksimal 30 karakter.");
                }
                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                $namaLower = strtolower($rawNama);
                if (isset($processedNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Departemen '{$rawNama}' duplikat di dalam file.");
                }
                $processedNames[$namaLower] = true;

                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $kode = 'D' . str_pad($maxNumber, 3, '0', STR_PAD_LEFT);
                } else {
                    if (str_starts_with($kode, 'D')) {
                        $suffix = substr($kode, 1);
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
                    throw new \Exception("Baris {$rowNum}: Kode Departemen '{$kode}' duplikat di dalam file.");
                }
                $processedCodes[$kodeLower] = true;

                $insertData[] = [
                    'row_num'         => $rowNum,
                    'company_id'      => $activeCompanyId,
                    'department_code' => strtoupper($kode),
                    'department_name' => $rawNama,
                    'description'     => $rawDesk ?: null,
                    'status'          => $rawStatus,
                    'created_at'      => $now,
                    'updated_at'      => $now,
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
            Cache::forget('master_departemen_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Data departemen berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import departemen: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor departemen.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import departemen: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function validateAndInsertChunks(array $chunk)
    {
        $activeCompanyId = session('active_company_id');
        $chunkCodes = array_map(fn($item) => $item['department_code'], $chunk);
        $chunkNames = array_map(fn($item) => $item['department_name'], $chunk);

        // Scope duplicate check to active company only
        $dbDuplicates = DB::table('departments')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->where(function ($q) use ($chunkCodes, $chunkNames) {
                $q->whereIn('department_code', $chunkCodes)
                  ->orWhereIn('department_name', $chunkNames);
            })
            ->get(['department_code', 'department_name']);

        if ($dbDuplicates->isNotEmpty()) {
            foreach ($chunk as $item) {
                foreach ($dbDuplicates as $dup) {
                    if (strtolower($item['department_name']) === strtolower($dup->department_name)) {
                        throw new \Exception("Baris {$item['row_num']}: Nama Departemen '{$item['department_name']}' sudah ada di database.");
                    }
                    if (strtolower($item['department_code']) === strtolower($dup->department_code)) {
                        throw new \Exception("Baris {$item['row_num']}: Kode Departemen '{$item['department_code']}' sudah ada di database.");
                    }
                }
            }
        }

        $finalInsert = array_map(function ($item) {
            unset($item['row_num']);
            return $item;
        }, $chunk);

        DB::table('departments')->insert($finalInsert);
    }
}
