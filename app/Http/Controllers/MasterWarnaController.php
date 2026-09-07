<?php

namespace App\Http\Controllers;

use App\Models\MasterWarna;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterWarnaController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-warna');
        return view('data_master.warna_barang.viewwarna');
    }

    /**
     * DataTables AJAX endpoint — server-side processing for performance.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-warna');

        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);
        $search = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [0 => 'id', 1 => 'kode_warna', 2 => 'nama_warna', 3 => 'kode_hex', 4 => 'deskripsi', 5 => 'status'];
        $orderColumn = $columns[$orderCol] ?? 'id';
        $activeCompanyId = session('active_company_id');

        $query = MasterWarna::forActiveCompany();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_warna', 'like', "%{$search}%")
                  ->orWhere('nama_warna', 'like', "%{$search}%")
                  ->orWhere('kode_hex', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $cacheKey = 'master_warna_total_count_comp_' . ($activeCompanyId ?? 0);
        $totalData = Cache::remember($cacheKey, 60, function () use ($activeCompanyId) {
            return MasterWarna::forActiveCompany()->count();
        });

        if ($search === '') {
            $totalFiltered = $totalData;
        } else {
            $totalFiltered = $query->count();
        }

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($w, $index) use ($start) {
            // Sanitize hex: must be valid #RRGGBB, fallback to grey
            $hex = preg_match('/^#[a-fA-F0-9]{6}$/', $w->kode_hex ?? '') ? $w->kode_hex : '#6c757d';
            return [
                'no'         => $start + $index + 1,
                'id'         => $w->id,
                'kode_warna' => e($w->kode_warna),
                'nama_warna' => e($w->nama_warna),
                'kode_hex'   => $hex,
                'deskripsi'  => $w->deskripsi ? e($w->deskripsi) : null,
                'status'     => $w->status,
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
        $this->authorize('tambah-warna');
        $activeCompanyId = session('active_company_id');
        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'nama_warna' => 'required|string|max:30|unique:master_warna,nama_warna,NULL,id,deleted_at,NULL' . $compWhere,
            'kode_hex'   => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'deskripsi'  => 'nullable|string|max:30',
            'status'     => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();
            $kode = CodeGeneratorHelper::generate('master_warna', 'WRN-', 'kode_warna');
            MasterWarna::create([
                'company_id' => $activeCompanyId,
                'kode_warna' => $kode,
                'nama_warna' => $request->nama_warna,
                'kode_hex'   => $request->kode_hex,
                'deskripsi'  => $request->deskripsi,
                'status'     => $request->status,
            ]);
            DB::commit();
            Cache::forget('master_warna_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Warna berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store warna: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah warna.'], 500);
        }
    }

    public function edit(MasterWarna $warna)
    {
        $this->authorize('edit-warna');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $warna->company_id && $warna->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Warna ini milik PT lain.'], 403);
        }
        return response()->json(['warna' => $warna]);
    }

    public function update(Request $request, MasterWarna $warna)
    {
        $this->authorize('edit-warna');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $warna->company_id && $warna->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit warna milik PT lain.'], 403);
        }

        $compWhere = $activeCompanyId ? ',company_id,' . $activeCompanyId : '';

        $request->validate([
            'nama_warna' => 'required|string|max:30|unique:master_warna,nama_warna,' . $warna->id . ',id,deleted_at,NULL' . $compWhere,
            'kode_hex'   => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'deskripsi'  => 'nullable|string|max:30',
            'status'     => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();
            $warna->update([
                'nama_warna' => $request->nama_warna,
                'kode_hex'   => $request->kode_hex,
                'deskripsi'  => $request->deskripsi,
                'status'     => $request->status,
            ]);
            DB::commit();
            Cache::forget('master_warna_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Warna berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update warna: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate warna.'], 500);
        }
    }

    public function destroy(MasterWarna $warna)
    {
        $this->authorize('hapus-warna');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $warna->company_id && $warna->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus warna milik PT lain.'], 403);
        }

        // Prevent deletion if warna is used by any barang
        $usedCount = \App\Models\MasterBarang::where('warna_id', $warna->id)->count();
        if ($usedCount > 0) {
            return response()->json([
                'message' => "Warna \"{$warna->nama_warna}\" tidak dapat dihapus karena digunakan oleh {$usedCount} data barang."
            ], 422);
        }
        try {
            DB::beginTransaction();
            $warna->delete();
            DB::commit();
            Cache::forget('master_warna_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Warna berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete warna: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus warna.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-warna');
        $activeCompanyId = session('active_company_id');

        set_time_limit(0);

        // Use cursor to stream rows efficiently
        $warnas = DB::table('master_warna')
            ->select('kode_warna', 'nama_warna', 'kode_hex', 'deskripsi', 'status')
            ->whereNull('deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->orderBy('id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Warna</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Warna</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode HEX</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Deskripsi</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($warnas as $w) {
            $statusStyle = $w->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $w->kode_warna . '</style><center>',
                '<style border="thin">' . $w->nama_warna . '</style><left>',
                '<style border="thin">' . $w->kode_hex . '</style><center>',
                '<style border="thin">' . ($w->deskripsi ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_warna_barang_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-warna');

        $rows = [
            ['Kode Warna', 'Nama Warna', 'Kode HEX', 'Deskripsi', 'Status'],
            ['WRN-EXAMPLE01', 'Merah', '#FF0000', 'Warna merah terang', 'Active'],
            ['', 'Hitam', '#000000', 'Warna hitam dasar', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_warna_barang.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-warna');

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

            // Get latest code number from DB (including soft deletes)
            $latestCode = DB::table('master_warna')
                ->where('kode_warna', 'like', 'WRN-%')
                ->orderBy('kode_warna', 'desc')
                ->value('kode_warna');

            $maxNumber = 0;
            if ($latestCode) {
                $suffix = substr($latestCode, strlen('WRN-'));
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
                $rawHex    = isset($row[2]) ? trim($row[2]) : '';
                $rawDesk   = isset($row[3]) ? trim($row[3]) : '';
                $rawStatus = isset($row[4]) ? trim($row[4]) : '';
                $rowNum    = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Warna wajib diisi.");
                }
                if (strlen($rawNama) > 30) {
                    throw new \Exception("Baris {$rowNum}: Nama Warna maksimal 30 karakter.");
                }
                if (!empty($rawHex) && !preg_match('/^#[a-fA-F0-9]{6}$/', $rawHex)) {
                    throw new \Exception("Baris {$rowNum}: Kode HEX '{$rawHex}' harus berformat #RRGGBB.");
                }
                if (strlen($rawDesk) > 30) {
                    throw new \Exception("Baris {$rowNum}: Deskripsi maksimal 30 karakter.");
                }
                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                $namaLower = strtolower($rawNama);
                if (isset($processedNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Warna '{$rawNama}' duplikat di dalam file.");
                }
                $processedNames[$namaLower] = true;

                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $kode = 'WRN-' . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
                } else {
                    if (str_starts_with($kode, 'WRN-')) {
                        $suffix = substr($kode, strlen('WRN-'));
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
                    throw new \Exception("Baris {$rowNum}: Kode Warna '{$kode}' duplikat di dalam file.");
                }
                $processedCodes[$kodeLower] = true;

                $insertData[] = [
                    'company_id' => session('active_company_id'),
                    'row_num'    => $rowNum,
                    'kode_warna' => $kode,
                    'nama_warna' => $rawNama,
                    'kode_hex'   => $rawHex ?: '#6C757D',
                    'deskripsi'  => $rawDesk ?: null,
                    'status'     => $rawStatus,
                    'created_at' => $now,
                    'updated_at' => $now,
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
            Cache::forget('master_warna_total_count_comp_' . ($activeCompanyId ?? 0));
            return response()->json(['message' => 'Data warna berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import warna: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor warna.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import warna: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function validateAndInsertChunks(array $chunk)
    {
        $chunkCodes = array_map(fn($item) => $item['kode_warna'], $chunk);
        $chunkNames = array_map(fn($item) => $item['nama_warna'], $chunk);
        $activeCompanyId = session('active_company_id');

        $dbDuplicates = DB::table('master_warna')
            ->where(function($q) use ($chunkCodes, $chunkNames) {
                $q->whereIn('kode_warna', $chunkCodes)
                  ->orWhereIn('nama_warna', $chunkNames);
            })
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->whereNull('deleted_at')
            ->get(['kode_warna', 'nama_warna']);

        if ($dbDuplicates->isNotEmpty()) {
            foreach ($chunk as $item) {
                foreach ($dbDuplicates as $dup) {
                    if (strtolower($item['nama_warna']) === strtolower($dup->nama_warna)) {
                        throw new \Exception("Baris {$item['row_num']}: Nama Warna '{$item['nama_warna']}' sudah ada di database.");
                    }
                    if (strtolower($item['kode_warna']) === strtolower($dup->kode_warna)) {
                        throw new \Exception("Baris {$item['row_num']}: Kode Warna '{$item['kode_warna']}' sudah ada di database.");
                    }
                }
            }
        }

        $finalInsert = array_map(function ($item) {
            unset($item['row_num']);
            return $item;
        }, $chunk);

        DB::table('master_warna')->insert($finalInsert);
    }
}
