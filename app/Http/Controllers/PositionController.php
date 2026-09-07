<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class PositionController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-jabatan');
        $departments = Department::active()->forActiveCompany()->orderBy('department_name')->get();
        return view('master_organisasi.jabatan.viewpositions', compact('departments'));
    }

    /**
     * DataTables AJAX endpoint — server-side processing.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-jabatan');

        $draw     = (int) $request->get('draw', 1);
        $start    = (int) $request->get('start', 0);
        $length   = (int) $request->get('length', 10);
        $search   = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [
            0 => 'id',
            1 => 'position_code',
            2 => 'position_name',
            3 => 'department_id',
            4 => 'description',
            5 => 'status'
        ];
        $orderColumn = $columns[$orderCol] ?? 'id';
        $activeCompanyId = session('active_company_id');

        $query = Position::forActiveCompany()->with('department');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('position_code', 'like', "%{$search}%")
                  ->orWhere('position_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('department', function ($d) use ($search) {
                      $d->where('department_name', 'like', "%{$search}%");
                  });
            });
        }

        $cacheKey = 'master_jabatan_total_count_comp_' . ($activeCompanyId ?? 0);
        $totalData = Cache::remember($cacheKey, 60, function () {
            return Position::forActiveCompany()->count();
        });

        if ($search === '') {
            $totalFiltered = $totalData;
        } else {
            $totalFiltered = $query->count();
        }

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($p, $index) use ($start) {
            return [
                'no'              => $start + $index + 1,
                'id'              => $p->id,
                'position_code'   => e($p->position_code),
                'position_name'   => e($p->position_name),
                'department_name' => $p->department ? e($p->department->department_name) : null,
                'description'     => $p->description ? e($p->description) : null,
                'status'          => $p->status,
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
        $this->authorize('tambah-jabatan');
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_code' => [
                'nullable', 'string', 'max:4',
                \Illuminate\Validation\Rule::unique('positions', 'position_code')
                    ->where(function ($q) use ($activeCompanyId) {
                        if ($activeCompanyId) $q->where('company_id', $activeCompanyId);
                        return $q->whereNull('deleted_at');
                    })
            ],
            'position_name' => 'required|string|max:30',
            'description'   => 'nullable|string|max:30',
            'status'        => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $code = $request->position_code;
            if (empty($code)) {
                $code = CodeGeneratorHelper::generate('positions', 'J', 'position_code', 3);
            } else {
                $code = strtoupper($code);
            }

            Position::create([
                'company_id'    => $activeCompanyId,
                'department_id' => $request->department_id,
                'position_code' => $code,
                'position_name' => $request->position_name,
                'description'   => $request->description,
                'status'        => $request->status,
            ]);

            DB::commit();
            Cache::forget('master_jabatan_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Jabatan berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store position: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah jabatan.'], 500);
        }
    }

    public function edit(Position $position)
    {
        $this->authorize('edit-jabatan');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $position->company_id && $position->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Jabatan ini milik PT lain.'], 403);
        }
        $departments = Department::active()->forActiveCompany()->orderBy('department_name')->get();
        return response()->json([
            'position'    => $position,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, Position $position)
    {
        $this->authorize('edit-jabatan');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $position->company_id && $position->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit jabatan milik PT lain.'], 403);
        }

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_code' => [
                'required', 'string', 'max:4',
                \Illuminate\Validation\Rule::unique('positions', 'position_code')
                    ->ignore($position->id)
                    ->where(function ($q) use ($activeCompanyId) {
                        if ($activeCompanyId) $q->where('company_id', $activeCompanyId);
                        return $q->whereNull('deleted_at');
                    })
            ],
            'position_name' => 'required|string|max:30',
            'description'   => 'nullable|string|max:30',
            'status'        => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $position->update([
                'department_id' => $request->department_id,
                'position_code' => strtoupper($request->position_code),
                'position_name' => $request->position_name,
                'description'   => $request->description,
                'status'        => $request->status,
            ]);

            DB::commit();
            Cache::forget('master_jabatan_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Jabatan berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update position: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate jabatan.'], 500);
        }
    }

    public function destroy(Position $position)
    {
        $this->authorize('hapus-jabatan');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $position->company_id && $position->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus jabatan milik PT lain.'], 403);
        }

        // Business rules check
        $karyawanCount = \App\Models\Karyawan::where('jabatan_id', $position->id)->count();
        if ($karyawanCount > 0) {
            return response()->json([
                'message' => "Jabatan \"{$position->position_name}\" tidak dapat dihapus karena digunakan oleh {$karyawanCount} data karyawan."
            ], 422);
        }

        try {
            DB::beginTransaction();
            $position->delete();
            DB::commit();
            Cache::forget('master_jabatan_total_count_comp_' . ($activeCompanyId ?? 0));
            
            return response()->json(['message' => 'Jabatan berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete position: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus jabatan.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-jabatan');
        $activeCompanyId = session('active_company_id');

        set_time_limit(0);

        // Use cursor to stream rows efficiently, scoped to active company
        $positions = DB::table('positions')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('positions.position_code', 'positions.position_name', 'departments.department_name', 'positions.description', 'positions.status')
            ->whereNull('positions.deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('positions.company_id', $activeCompanyId))
            ->orderBy('positions.id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Jabatan</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Jabatan</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Departemen</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Deskripsi</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($positions as $p) {
            $statusStyle = $p->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $p->position_code . '</style><center>',
                '<style border="thin">' . $p->position_name . '</style><left>',
                '<style border="thin">' . ($p->department_name ?? '') . '</style><left>',
                '<style border="thin">' . ($p->description ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_jabatan_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-jabatan');

        $rows = [
            ['Kode Jabatan', 'Nama Jabatan', 'Nama Departemen', 'Deskripsi', 'Status'],
            ['MGR', 'Manager IT', 'Human Resource', 'Manajer divisi IT', 'Active'],
            ['', 'Staff Keuangan', 'Finansial', 'Staff keuangan', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_jabatan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-jabatan');

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

            // Load all departments scoped to active company
            $departmentsMap = DB::table('departments')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(department_name)'))
                ->toArray();

            // Get latest code number from DB starting with 'J' (scoped to active company)
            $latestCode = DB::table('positions')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->where('position_code', 'like', 'J%')
                ->orderBy('position_code', 'desc')
                ->value('position_code');

            $maxNumber = 0;
            if ($latestCode) {
                $suffix = substr($latestCode, 1); // skip 'J'
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
                $rawDept   = isset($row[2]) ? trim($row[2]) : '';
                $rawDesk   = isset($row[3]) ? trim($row[3]) : '';
                $rawStatus = isset($row[4]) ? trim($row[4]) : '';
                $rowNum    = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Jabatan wajib diisi.");
                }
                if (strlen($rawNama) > 30) {
                    throw new \Exception("Baris {$rowNum}: Nama Jabatan maksimal 30 karakter.");
                }
                if (!empty($rawKode) && strlen($rawKode) > 4) {
                    throw new \Exception("Baris {$rowNum}: Kode Jabatan '{$rawKode}' maksimal 4 karakter.");
                }
                if (empty($rawDept)) {
                    throw new \Exception("Baris {$rowNum}: Nama Departemen wajib diisi.");
                }
                if (strlen($rawDesk) > 30) {
                    throw new \Exception("Baris {$rowNum}: Deskripsi maksimal 30 karakter.");
                }
                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                $deptLower = strtolower($rawDept);
                if (!isset($departmentsMap[$deptLower])) {
                    throw new \Exception("Baris {$rowNum}: Departemen '{$rawDept}' tidak terdaftar di database.");
                }
                $deptId = $departmentsMap[$deptLower];

                $namaLower = strtolower($rawNama);
                if (isset($processedNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Jabatan '{$rawNama}' duplikat di dalam file.");
                }
                $processedNames[$namaLower] = true;

                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $kode = 'J' . str_pad($maxNumber, 3, '0', STR_PAD_LEFT);
                } else {
                    if (str_starts_with($kode, 'J')) {
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
                    throw new \Exception("Baris {$rowNum}: Kode Jabatan '{$kode}' duplikat di dalam file.");
                }
                $processedCodes[$kodeLower] = true;

                $insertData[] = [
                    'row_num'       => $rowNum,
                    'company_id'    => $activeCompanyId,
                    'department_id' => $deptId,
                    'position_code' => strtoupper($kode),
                    'position_name' => $rawNama,
                    'description'   => $rawDesk ?: null,
                    'status'        => $rawStatus,
                    'created_at'    => $now,
                    'updated_at'    => $now,
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
            Cache::forget('master_jabatan_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Data jabatan berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import positions: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor jabatan.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import positions: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function validateAndInsertChunks(array $chunk)
    {
        $activeCompanyId = session('active_company_id');
        $chunkCodes = array_map(fn($item) => $item['position_code'], $chunk);
        $chunkNames = array_map(fn($item) => $item['position_name'], $chunk);

        // Scope duplicate check to active company only
        $dbDuplicates = DB::table('positions')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->where(function ($q) use ($chunkCodes, $chunkNames) {
                $q->whereIn('position_code', $chunkCodes)
                  ->orWhereIn('position_name', $chunkNames);
            })
            ->get(['position_code', 'position_name']);

        if ($dbDuplicates->isNotEmpty()) {
            foreach ($chunk as $item) {
                foreach ($dbDuplicates as $dup) {
                    if (strtolower($item['position_name']) === strtolower($dup->position_name)) {
                        throw new \Exception("Baris {$item['row_num']}: Nama Jabatan '{$item['position_name']}' sudah ada di database.");
                    }
                    if (strtolower($item['position_code']) === strtolower($dup->position_code)) {
                        throw new \Exception("Baris {$item['row_num']}: Kode Jabatan '{$item['position_code']}' sudah ada di database.");
                    }
                }
            }
        }

        $finalInsert = array_map(function ($item) {
            unset($item['row_num']);
            return $item;
        }, $chunk);

        DB::table('positions')->insert($finalInsert);
    }
}
