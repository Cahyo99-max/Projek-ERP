<?php

namespace App\Http\Controllers;

use App\Models\MasterLokasiRak;
use App\Models\MasterGudang;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterLokasiRakController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-lokasi-rak');

        // Retrieve active warehouses for active company
        $gudangs = MasterGudang::active()->forActiveCompany()->orderBy('nama_gudang', 'asc')->get();

        return view('master_gudang.lokasi_rak.viewlokasirak', compact('gudangs'));
    }

    /**
     * DataTables AJAX endpoint — server-side processing.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-lokasi-rak');

        $draw     = (int) $request->get('draw', 1);
        $start    = (int) $request->get('start', 0);
        $length   = (int) $request->get('length', 10);
        $search   = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [
            0 => 'id',
            1 => 'kode_rak',
            2 => 'nama_rak',
            3 => 'gudang_id',
            4 => 'baris',
            5 => 'kolom',
            6 => 'level',
            7 => 'status',
            8 => 'deskripsi'
        ];
        $orderColumn = $columns[$orderCol] ?? 'id';
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        $query = MasterLokasiRak::forActiveCompany()->with(['gudang']);

        if ($activeBranchId) {
            $query->whereHas('gudang', function ($g) use ($activeBranchId) {
                $g->where('cabang_id', $activeBranchId)
                  ->orWhereNull('cabang_id');
            });
        }

        if ($search !== '') {
            // 1. Pre-query matching warehouse IDs to avoid slow subqueries (orWhereHas)
            $matchingGudangIds = DB::table('master_gudang')
                ->where('nama_gudang', 'like', "%{$search}%")
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            $query->where(function ($q) use ($search, $matchingGudangIds) {
                // Trailing wildcard (prefix search) allows utilizing B-tree index on 'kode_rak'
                $q->where('kode_rak', 'like', "{$search}%")
                  ->orWhere('nama_rak', 'like', "%{$search}%");

                if (!empty($matchingGudangIds)) {
                    $q->orWhereIn('gudang_id', $matchingGudangIds);
                }

                // Exact match checks for short fields utilize indexes immediately
                $q->orWhere('baris', '=', $search)
                  ->orWhere('kolom', '=', $search)
                  ->orWhere('level', '=', $search);
            });
        }

        $totalFiltered = $query->count();
        $totalDataQuery = MasterLokasiRak::forActiveCompany();
        if ($activeBranchId) {
            $totalDataQuery->whereHas('gudang', function ($g) use ($activeBranchId) {
                $g->where('cabang_id', $activeBranchId)
                  ->orWhereNull('cabang_id');
            });
        }
        $totalData = $totalDataQuery->count();

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($rak, $index) use ($start) {
            return [
                'no'          => $start + $index + 1,
                'id'          => $rak->id,
                'kode_rak'    => e($rak->kode_rak),
                'nama_rak'    => e($rak->nama_rak),
                'gudang_nama' => $rak->gudang ? e($rak->gudang->nama_gudang) : null,
                'baris'       => e($rak->baris),
                'kolom'       => e($rak->kolom),
                'level'       => e($rak->level),
                'status'      => $rak->status,
                'deskripsi'   => e($rak->deskripsi),
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
        $this->authorize('tambah-lokasi-rak');
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'gudang_id' => 'required|integer|exists:master_gudang,id',
            'kode_rak'  => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\-]+$/',
                \Illuminate\Validation\Rule::unique('master_lokasi_rak', 'kode_rak')
                    ->where(function($q) use ($activeCompanyId) {
                        if ($activeCompanyId) $q->where('company_id', $activeCompanyId);
                        return $q->whereNull('deleted_at');
                    })
            ],
            'nama_rak'  => 'required|string|max:50',
            'baris'     => 'nullable|string|max:10',
            'kolom'     => 'nullable|string|max:10',
            'level'     => 'nullable|string|max:10',
            'status'    => 'required|in:Active,Inactive',
            'deskripsi' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $kode = $request->filled('kode_rak')
                ? strtoupper($request->kode_rak)
                : CodeGeneratorHelper::generate('master_lokasi_rak', 'RAK-', 'kode_rak');

            MasterLokasiRak::create([
                'company_id' => $activeCompanyId,
                'gudang_id'  => $request->gudang_id,
                'kode_rak'   => $kode,
                'nama_rak'   => $request->nama_rak,
                'baris'      => $request->baris,
                'kolom'      => $request->kolom,
                'level'      => $request->level,
                'status'     => $request->status,
                'deskripsi'  => $request->deskripsi,
            ]);

            DB::commit();
            return response()->json(['message' => 'Lokasi rak berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store lokasi rak: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah lokasi rak.'], 500);
        }
    }

    public function edit(MasterLokasiRak $lokasiRak)
    {
        $this->authorize('edit-lokasi-rak');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $lokasiRak->company_id && $lokasiRak->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Lokasi rak ini milik PT lain.'], 403);
        }
        return response()->json(['lokasi_rak' => $lokasiRak]);
    }

    public function update(Request $request, MasterLokasiRak $lokasiRak)
    {
        $this->authorize('edit-lokasi-rak');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $lokasiRak->company_id && $lokasiRak->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit lokasi rak milik PT lain.'], 403);
        }

        $request->validate([
            'gudang_id' => 'required|integer|exists:master_gudang,id',
            'kode_rak'  => [
                'required',
                'string',
                'max:20',
                'regex:/^[a-zA-Z0-9\-]+$/',
                \Illuminate\Validation\Rule::unique('master_lokasi_rak', 'kode_rak')
                    ->ignore($lokasiRak->id)
                    ->where(function($q) use ($activeCompanyId) {
                        if ($activeCompanyId) $q->where('company_id', $activeCompanyId);
                        return $q->whereNull('deleted_at');
                    })
            ],
            'nama_rak'  => 'required|string|max:50',
            'baris'     => 'nullable|string|max:10',
            'kolom'     => 'nullable|string|max:10',
            'level'     => 'nullable|string|max:10',
            'status'    => 'required|in:Active,Inactive',
            'deskripsi' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $lokasiRak->update([
                'gudang_id' => $request->gudang_id,
                'kode_rak'  => strtoupper($request->kode_rak),
                'nama_rak'  => $request->nama_rak,
                'baris'     => $request->baris,
                'kolom'     => $request->kolom,
                'level'     => $request->level,
                'status'    => $request->status,
                'deskripsi' => $request->deskripsi,
            ]);

            DB::commit();
            return response()->json(['message' => 'Lokasi rak berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update lokasi rak: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate lokasi rak.'], 500);
        }
    }

    public function destroy(MasterLokasiRak $lokasiRak)
    {
        $this->authorize('hapus-lokasi-rak');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $lokasiRak->company_id && $lokasiRak->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus lokasi rak milik PT lain.'], 403);
        }

        try {
            DB::beginTransaction();
            $lokasiRak->delete();
            DB::commit();

            return response()->json(['message' => 'Lokasi rak berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete lokasi rak: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus lokasi rak.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-lokasi-rak');
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        // Lift memory and time limits for high-volume exports
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $lokasiRaks = MasterLokasiRak::forActiveCompany()
            ->with(['gudang'])
            ->when($activeBranchId, fn($q) => $q->whereHas('gudang', function ($g) use ($activeBranchId) {
                $g->where('cabang_id', $activeBranchId)
                  ->orWhereNull('cabang_id');
            }))
            ->orderBy('id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Rak</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Rak</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Gudang</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Baris</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kolom</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Level</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Deskripsi</b></style><left>',
            ]
        ];

        foreach ($lokasiRaks as $rak) {
            $statusStyle = $rak->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $rak->kode_rak . '</style><center>',
                '<style border="thin">' . $rak->nama_rak . '</style><left>',
                '<style border="thin">' . ($rak->gudang ? $rak->gudang->nama_gudang : '') . '</style><left>',
                '<style border="thin">' . ($rak->baris ?? '') . '</style><center>',
                '<style border="thin">' . ($rak->kolom ?? '') . '</style><center>',
                '<style border="thin">' . ($rak->level ?? '') . '</style><center>',
                $statusStyle,
                '<style border="thin">' . ($rak->deskripsi ?? '') . '</style><left>',
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_lokasi_rak_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-lokasi-rak');

        $rows = [
            [
                'Kode Rak',
                'Nama Rak',
                'Gudang',
                'Baris',
                'Kolom',
                'Level',
                'Status',
                'Deskripsi'
            ],
            [
                'RAK-EXAMPLE01',
                'Rak Utama 1',
                'Gudang Utama',
                'A',
                '1',
                '1',
                'Active',
                'Rak untuk barang fast-moving'
            ],
            [
                '',
                'Rak Transit 2',
                'Gudang Utama',
                'B',
                '3',
                '1',
                'Active',
                ''
            ],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_lokasi_rak.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-lokasi-rak');

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
        $chunkSize      = 1000;
        $insertData     = [];

        try {
            DB::beginTransaction();
            $activeCompanyId = session('active_company_id');

            // Extract all non-empty uploaded codes to verify uniqueness against DB in ONE query instead of loading all rows
            $uploadedCodes = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;
                $rawKode = isset($row[0]) ? trim($row[0]) : '';
                if ($rawKode !== '') {
                    $uploadedCodes[] = strtoupper($rawKode);
                }
            }

            $existingCodes = [];
            if (!empty($uploadedCodes)) {
                $existingCodes = DB::table('master_lokasi_rak')
                    ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                    ->whereIn('kode_rak', $uploadedCodes)
                    ->pluck('kode_rak')
                    ->map(fn($c) => strtolower($c))
                    ->flip()
                    ->toArray();
            }

            $gudangsMap = DB::table('master_gudang')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(nama_gudang)'))
                ->toArray();

            $now = now()->toDateTimeString();
            $authId = auth()->id();

            // Calculate starting numeric suffix once in memory for auto-generated codes
            $maxCode = DB::table('master_lokasi_rak')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->where('kode_rak', 'like', 'RAK-%')
                ->orderBy('kode_rak', 'desc')
                ->value('kode_rak');
            $maxNumber = 0;
            if ($maxCode) {
                $suffix = substr($maxCode, 4);
                if (ctype_digit($suffix)) {
                    $maxNumber = (int) $suffix;
                }
            }

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;

                $rawKode      = isset($row[0]) ? trim($row[0]) : '';
                $rawNama      = isset($row[1]) ? trim($row[1]) : '';
                $rawGudang    = isset($row[2]) ? trim($row[2]) : '';
                $rawBaris     = isset($row[3]) ? trim($row[3]) : '';
                $rawKolom     = isset($row[4]) ? trim($row[4]) : '';
                $rawLevel     = isset($row[5]) ? trim($row[5]) : '';
                $rawStatus    = isset($row[6]) ? trim($row[6]) : 'Active';
                $rawDeskripsi = isset($row[7]) ? trim($row[7]) : '';
                $rowNum       = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Rak wajib diisi.");
                }
                if (strlen($rawNama) > 50) {
                    throw new \Exception("Baris {$rowNum}: Nama Rak maksimal 50 karakter.");
                }

                if (empty($rawGudang)) {
                    throw new \Exception("Baris {$rowNum}: Gudang wajib diisi.");
                }
                $gudangKey = strtolower($rawGudang);
                if (!isset($gudangsMap[$gudangKey])) {
                    throw new \Exception("Baris {$rowNum}: Gudang '{$rawGudang}' tidak ditemukan.");
                }
                $gudangId = $gudangsMap[$gudangKey];

                if (strlen($rawBaris) > 10) {
                    throw new \Exception("Baris {$rowNum}: Baris (Row) maksimal 10 karakter.");
                }
                if (strlen($rawKolom) > 10) {
                    throw new \Exception("Baris {$rowNum}: Kolom (Column) maksimal 10 karakter.");
                }
                if (strlen($rawLevel) > 10) {
                    throw new \Exception("Baris {$rowNum}: Level (Tier) maksimal 10 karakter.");
                }
                if (strlen($rawDeskripsi) > 100) {
                    throw new \Exception("Baris {$rowNum}: Deskripsi maksimal 100 karakter.");
                }

                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                // Code generation / validation
                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $generatedCode = 'RAK-' . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
                    while (isset($existingCodes[strtolower($generatedCode)]) || isset($processedCodes[strtolower($generatedCode)])) {
                        $maxNumber++;
                        $generatedCode = 'RAK-' . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
                    }
                    $kode = $generatedCode;
                } else {
                    $kode = strtoupper($kode);
                    if (isset($existingCodes[strtolower($kode)])) {
                        throw new \Exception("Baris {$rowNum}: Kode Rak '{$kode}' sudah terdaftar.");
                    }
                    if (isset($processedCodes[strtolower($kode)])) {
                        throw new \Exception("Baris {$rowNum}: Kode Rak '{$kode}' ganda di dalam file.");
                    }
                    if (!preg_match('/^[a-zA-Z0-9\-]+$/', $kode)) {
                        throw new \Exception("Baris {$rowNum}: Kode Rak '{$kode}' hanya boleh berisi huruf, angka, dan tanda hubung (-).");
                    }
                }
                $processedCodes[strtolower($kode)] = true;

                $insertData[] = [
                    'company_id' => $activeCompanyId,
                    'kode_rak'   => $kode,
                    'nama_rak'   => $rawNama,
                    'gudang_id'  => $gudangId,
                    'baris'      => $rawBaris ?: null,
                    'kolom'      => $rawKolom ?: null,
                    'level'      => $rawLevel ?: null,
                    'status'     => $rawStatus,
                    'deskripsi'  => $rawDeskripsi ?: null,
                    'created_by' => $authId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($insertData) >= $chunkSize) {
                    $this->validateAndInsertRackChunks($insertData);
                    $insertData = [];
                }
            }

            if (!empty($insertData)) {
                $this->validateAndInsertRackChunks($insertData);
            }

            DB::commit();
            return response()->json(['message' => 'Data lokasi rak berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import lokasi rak: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor data.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import lokasi rak: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal Import: ' . $e->getMessage()], 422);
        }
    }

    private function validateAndInsertRackChunks(array $chunk)
    {
        DB::table('master_lokasi_rak')->insert($chunk);
    }
}
