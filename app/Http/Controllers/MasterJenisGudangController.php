<?php

namespace App\Http\Controllers;

use App\Models\MasterJenisGudang;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterJenisGudangController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-jenis-gudang');
        return view('master_gudang.jenis_gudang.viewjenisgudang');
    }

    /**
     * DataTables AJAX endpoint — server-side processing.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-jenis-gudang');

        $draw     = (int) $request->get('draw', 1);
        $start    = (int) $request->get('start', 0);
        $length   = (int) $request->get('length', 10);
        $search   = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [
            0 => 'id',
            1 => 'kode_jenis_gudang',
            2 => 'nama_jenis_gudang',
            3 => 'urutan',
            4 => 'deskripsi',
            5 => 'status'
        ];
        $orderColumn = $columns[$orderCol] ?? 'urutan';
        $activeCompanyId = session('active_company_id');

        $query = MasterJenisGudang::forActiveCompany();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_jenis_gudang', 'like', "%{$search}%")
                  ->orWhere('nama_jenis_gudang', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();
        $totalData     = MasterJenisGudang::forActiveCompany()->count();

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($jg, $index) use ($start) {
            return [
                'no'                => $start + $index + 1,
                'id'                => $jg->id,
                'kode_jenis_gudang' => e($jg->kode_jenis_gudang),
                'nama_jenis_gudang' => e($jg->nama_jenis_gudang),
                'urutan'            => $jg->urutan,
                'deskripsi'         => $jg->deskripsi ? e($jg->deskripsi) : null,
                'status'            => $jg->status,
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
        $this->authorize('tambah-jenis-gudang');
        $activeCompanyId = session('active_company_id');
        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'nama_jenis_gudang' => 'required|string|max:30|unique:master_jenis_gudang,nama_jenis_gudang,NULL,id,deleted_at,NULL' . $compWhere,
            'deskripsi'         => 'nullable|string|max:30',
            'urutan'            => 'nullable|integer|min:1',
            'status'            => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();
            $kode = CodeGeneratorHelper::generate('master_jenis_gudang', 'JGD-', 'kode_jenis_gudang');
            MasterJenisGudang::create([
                'company_id'        => $activeCompanyId,
                'kode_jenis_gudang' => $kode,
                'nama_jenis_gudang' => $request->nama_jenis_gudang,
                'deskripsi'         => $request->deskripsi,
                'urutan'            => $request->urutan,
                'status'            => $request->status,
            ]);
            DB::commit();
            return response()->json(['message' => 'Jenis gudang berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store jenis gudang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah jenis gudang.'], 500);
        }
    }

    public function edit(MasterJenisGudang $jenisGudang)
    {
        $this->authorize('edit-jenis-gudang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $jenisGudang->company_id && $jenisGudang->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Jenis gudang ini milik PT lain.'], 403);
        }
        return response()->json(['jenisGudang' => $jenisGudang]);
    }

    public function update(Request $request, MasterJenisGudang $jenisGudang)
    {
        $this->authorize('edit-jenis-gudang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $jenisGudang->company_id && $jenisGudang->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit jenis gudang milik PT lain.'], 403);
        }

        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'nama_jenis_gudang' => 'required|string|max:30|unique:master_jenis_gudang,nama_jenis_gudang,' . $jenisGudang->id . ',id,deleted_at,NULL' . $compWhere,
            'deskripsi'         => 'nullable|string|max:30',
            'urutan'            => 'nullable|integer|min:1',
            'status'            => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();
            $jenisGudang->update([
                'nama_jenis_gudang' => $request->nama_jenis_gudang,
                'deskripsi'         => $request->deskripsi,
                'urutan'            => $request->urutan,
                'status'            => $request->status,
            ]);
            DB::commit();
            return response()->json(['message' => 'Jenis gudang berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update jenis gudang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate jenis gudang.'], 500);
        }
    }

    public function destroy(MasterJenisGudang $jenisGudang)
    {
        $this->authorize('hapus-jenis-gudang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $jenisGudang->company_id && $jenisGudang->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus jenis gudang milik PT lain.'], 403);
        }

        // Business rule check: check usage in MasterGudang
        $usedCount = \App\Models\MasterGudang::where('jenis_gudang_id', $jenisGudang->id)->count();
        if ($usedCount > 0) {
            return response()->json([
                'message' => "Jenis gudang \"{$jenisGudang->nama_jenis_gudang}\" tidak dapat dihapus karena sedang digunakan oleh {$usedCount} data gudang."
            ], 422);
        }

        try {
            DB::beginTransaction();
            $jenisGudang->delete();
            DB::commit();
            return response()->json(['message' => 'Jenis gudang berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete jenis gudang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus jenis gudang.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-jenis-gudang');
        $activeCompanyId = session('active_company_id');

        set_time_limit(0);

        $jenisGudangs = DB::table('master_jenis_gudang')
            ->select('kode_jenis_gudang', 'nama_jenis_gudang', 'urutan', 'deskripsi', 'status')
            ->whereNull('deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->orderBy('urutan', 'asc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Jenis</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Jenis Gudang</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Urutan</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Deskripsi</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($jenisGudangs as $jg) {
            $statusStyle = $jg->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $jg->kode_jenis_gudang . '</style><center>',
                '<style border="thin">' . $jg->nama_jenis_gudang . '</style><left>',
                '<style border="thin">' . ($jg->urutan ?? '') . '</style><center>',
                '<style border="thin">' . ($jg->deskripsi ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_jenis_gudang_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-jenis-gudang');

        $rows = [
            ['Kode Jenis', 'Nama Jenis Gudang', 'Urutan', 'Deskripsi', 'Status'],
            ['JGD-EXAMPLE01', 'Gudang Utama', '1', 'Untuk penyimpanan barang utama', 'Active'],
            ['', 'Gudang Transit', '2', 'Untuk transit pengiriman barang', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_jenis_gudang.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-jenis-gudang');

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
        $chunkSize      = 1000;
        $insertData     = [];

        try {
            DB::beginTransaction();
            $activeCompanyId = session('active_company_id');

            $existingCodes = DB::table('master_jenis_gudang')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->pluck('kode_jenis_gudang')
                ->map(fn($c) => strtolower($c))
                ->flip()
                ->toArray();

            $existingNames = DB::table('master_jenis_gudang')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereNull('deleted_at')
                ->pluck('nama_jenis_gudang')
                ->map(fn($n) => strtolower($n))
                ->flip()
                ->toArray();

            $now = now()->toDateTimeString();

            // Calculate starting numeric suffix once in memory
            $maxNumber = 0;
            foreach ($existingCodes as $c => $val) {
                if (str_starts_with($c, 'jgd-')) {
                    $suffix = substr($c, 4);
                    if (ctype_digit($suffix)) {
                        $numVal = (int) $suffix;
                        if ($numVal > $maxNumber) {
                            $maxNumber = $numVal;
                        }
                    }
                }
            }

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;

                $rawKode   = isset($row[0]) ? trim($row[0]) : '';
                $rawNama   = isset($row[1]) ? trim($row[1]) : '';
                $rawUrutan = isset($row[2]) ? trim($row[2]) : '';
                $rawDesk   = isset($row[3]) ? trim($row[3]) : '';
                $rawStatus = isset($row[4]) ? trim($row[4]) : 'Active';
                $rowNum    = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Jenis Gudang wajib diisi.");
                }
                if (strlen($rawNama) > 30) {
                    throw new \Exception("Baris {$rowNum}: Nama Jenis Gudang maksimal 30 karakter.");
                }

                $namaLower = strtolower($rawNama);
                if (isset($processedNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Jenis Gudang '{$rawNama}' duplikat di dalam file.");
                }
                if (isset($existingNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Jenis Gudang '{$rawNama}' sudah terdaftar di database.");
                }
                $processedNames[$namaLower] = true;

                if (!empty($rawUrutan) && !ctype_digit($rawUrutan)) {
                    throw new \Exception("Baris {$rowNum}: Urutan harus berupa angka bulat positif.");
                }

                if (strlen($rawDesk) > 30) {
                    throw new \Exception("Baris {$rowNum}: Deskripsi maksimal 30 karakter.");
                }

                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                // Memory-based code generation
                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $generatedCode = 'JGD-' . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
                    while (isset($existingCodes[strtolower($generatedCode)]) || isset($processedCodes[strtolower($generatedCode)])) {
                        $maxNumber++;
                        $generatedCode = 'JGD-' . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
                    }
                    $kode = $generatedCode;
                } else {
                    if (isset($existingCodes[strtolower($kode)])) {
                        throw new \Exception("Baris {$rowNum}: Kode Jenis '{$kode}' sudah terdaftar.");
                    }
                    if (isset($processedCodes[strtolower($kode)])) {
                        throw new \Exception("Baris {$rowNum}: Kode Jenis '{$kode}' ganda di dalam file.");
                    }
                }
                $processedCodes[strtolower($kode)] = true;

                $insertData[] = [
                    'company_id'        => $activeCompanyId,
                    'kode_jenis_gudang' => $kode,
                    'nama_jenis_gudang' => $rawNama,
                    'urutan'            => $rawUrutan ?: null,
                    'deskripsi'         => $rawDesk ?: null,
                    'status'            => $rawStatus,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];

                if (count($insertData) >= $chunkSize) {
                    $this->validateAndInsertJenisGudangChunks($insertData);
                    $insertData = [];
                }
            }

            if (!empty($insertData)) {
                $this->validateAndInsertJenisGudangChunks($insertData);
            }

            DB::commit();
            return response()->json(['message' => 'Data jenis gudang berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import jenis gudang: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor data.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import jenis gudang: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal Import: ' . $e->getMessage()], 422);
        }
    }

    private function validateAndInsertJenisGudangChunks(array $chunk)
    {
        DB::table('master_jenis_gudang')->insert($chunk);
    }
}
