<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class BranchController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-cabang');
        $totalCabang    = Branch::count();
        $aktifCabang    = Branch::where('status', 'Active')->count();
        $nonaktifCabang = Branch::where('status', 'Inactive')->count();
        $companies      = \App\Models\Company::active()->orderBy('nama_perusahaan', 'asc')->get();

        return view('master_gudang.cabang.viewbranches', compact(
            'totalCabang',
            'aktifCabang',
            'nonaktifCabang',
            'companies'
        ));
    }

    /**
     * DataTables AJAX endpoint — server-side processing.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-cabang');

        $draw     = (int) $request->get('draw', 1);
        $start    = (int) $request->get('start', 0);
        $length   = (int) $request->get('length', 10);
        $search   = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [
            0 => 'id',
            1 => 'kode_cabang',
            2 => 'nama_cabang',
            3 => 'city',
            4 => 'manager_name',
            5 => 'phone',
            6 => 'status'
        ];
        $orderColumn = $columns[$orderCol] ?? 'id';

        $query = Branch::with('company');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_cabang', 'like', "%{$search}%")
                  ->orWhere('nama_cabang', 'like', "%{$search}%")
                  ->orWhere('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhereHas('company', function ($c) use ($search) {
                      $c->where('nama_perusahaan', 'like', "%{$search}%");
                  })
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();
        $totalData     = Branch::count();

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($b, $index) use ($start) {
            $namaPerusahaan = $b->company ? $b->company->nama_perusahaan : ($b->nama_perusahaan ?? null);
            return [
                'no'              => $start + $index + 1,
                'id'              => $b->id,
                'kode_cabang'     => e($b->kode_cabang),
                'nama_cabang'     => e($b->nama_cabang),
                'company_id'      => $b->company_id,
                'nama_perusahaan' => $namaPerusahaan ? e($namaPerusahaan) : null,
                'city'            => $b->city ? e($b->city) : null,
                'province'        => $b->province ? e($b->province) : null,
                'manager_name'    => $b->manager_name ? e($b->manager_name) : null,
                'phone'           => $b->phone ? e($b->phone) : null,
                'status'          => $b->status,
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
        $this->authorize('tambah-cabang');

        if ($request->has('kode_cabang') && !empty($request->kode_cabang)) {
            $request->merge(['kode_cabang' => strtoupper(trim($request->kode_cabang))]);
        }

        $request->validate([
            'kode_cabang'     => 'nullable|string|max:6|unique:branches,kode_cabang',
            'company_id'      => 'nullable|exists:companies,id',
            'nama_cabang'     => 'required|string|max:30',
            'nama_perusahaan' => 'nullable|string|max:150',
            'phone'           => 'nullable|regex:/^[0-9]+$/|max:13',
            'email'           => 'nullable|email|max:50',
            'address'         => 'nullable|string|max:60',
            'city'            => 'nullable|string|max:20',
            'province'        => 'nullable|string|max:20',
            'postal_code'     => 'nullable|regex:/^[0-9]+$/|max:10',
            'manager_name'    => 'nullable|string|max:40',
            'status'          => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $kode = $request->kode_cabang;
            if (empty($kode)) {
                $kode = CodeGeneratorHelper::generate('branches', 'CBG-', 'kode_cabang', 2);
            }

            $companyName = $request->nama_perusahaan;
            if ($request->filled('company_id')) {
                $comp = \App\Models\Company::find($request->company_id);
                if ($comp) {
                    $companyName = $comp->nama_perusahaan;
                }
            }

            Branch::create([
                'kode_cabang'     => strtoupper($kode),
                'company_id'      => $request->company_id,
                'nama_cabang'     => $request->nama_cabang,
                'nama_perusahaan' => $companyName,
                'phone'           => $request->phone,
                'email'           => $request->email,
                'address'         => $request->address,
                'city'            => $request->city,
                'province'        => $request->province,
                'postal_code'     => $request->postal_code,
                'manager_name'    => $request->manager_name,
                'status'          => $request->status,
                'created_by'      => auth()->id(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Cabang berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store cabang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah cabang.'], 500);
        }
    }

    public function show(Branch $branch)
    {
        $this->authorize('lihat-cabang');
        $branch->load('company');
        return response()->json(['branch' => $branch]);
    }

    public function edit(Branch $branch)
    {
        $this->authorize('edit-cabang');
        $branch->load('company');
        return response()->json(['branch' => $branch]);
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorize('edit-cabang');

        if ($request->has('kode_cabang') && !empty($request->kode_cabang)) {
            $request->merge(['kode_cabang' => strtoupper(trim($request->kode_cabang))]);
        }

        $request->validate([
            'kode_cabang'     => 'nullable|string|max:6|unique:branches,kode_cabang,' . $branch->id,
            'company_id'      => 'nullable|exists:companies,id',
            'nama_cabang'     => 'required|string|max:30',
            'nama_perusahaan' => 'nullable|string|max:150',
            'phone'           => 'nullable|regex:/^[0-9]+$/|max:13',
            'email'           => 'nullable|email|max:50',
            'address'         => 'nullable|string|max:60',
            'city'            => 'nullable|string|max:20',
            'province'        => 'nullable|string|max:20',
            'postal_code'     => 'nullable|regex:/^[0-9]+$/|max:10',
            'manager_name'    => 'nullable|string|max:40',
            'status'          => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $kode = $request->kode_cabang;
            if (empty($kode)) {
                $kode = CodeGeneratorHelper::generate('branches', 'CBG-', 'kode_cabang', 2);
            }

            $companyName = $request->nama_perusahaan;
            if ($request->filled('company_id')) {
                $comp = \App\Models\Company::find($request->company_id);
                if ($comp) {
                    $companyName = $comp->nama_perusahaan;
                }
            }

            $branch->update([
                'kode_cabang'     => strtoupper($kode),
                'company_id'      => $request->company_id,
                'nama_cabang'     => $request->nama_cabang,
                'nama_perusahaan' => $companyName,
                'phone'           => $request->phone,
                'email'           => $request->email,
                'address'         => $request->address,
                'city'            => $request->city,
                'province'        => $request->province,
                'postal_code'     => $request->postal_code,
                'manager_name'    => $request->manager_name,
                'status'          => $request->status,
                'updated_by'      => auth()->id(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Cabang berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update cabang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate cabang.'], 500);
        }
    }

    public function destroy(Branch $branch)
    {
        $this->authorize('hapus-cabang');

        // Business rule check: check usage in MasterGudang
        $usedCount = \App\Models\MasterGudang::where('cabang_id', $branch->id)->count();
        if ($usedCount > 0) {
            return response()->json([
                'message' => "Cabang \"{$branch->nama_cabang}\" tidak dapat dihapus karena digunakan oleh {$usedCount} data gudang."
            ], 422);
        }

        try {
            DB::beginTransaction();
            $branch->delete();
            DB::commit();

            return response()->json(['message' => 'Cabang berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete cabang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus cabang.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-cabang');

        $branches = DB::table('branches')
            ->select('kode_cabang', 'nama_cabang', 'nama_perusahaan', 'phone', 'email', 'address', 'city', 'province', 'postal_code', 'manager_name', 'status')
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Cabang</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Cabang</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Perusahaan</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>No Telepon</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Email</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Alamat</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kota</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Provinsi</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Pos</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kepala Cabang</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($branches as $b) {
            $statusStyle = $b->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $b->kode_cabang . '</style><center>',
                '<style border="thin">' . $b->nama_cabang . '</style><left>',
                '<style border="thin">' . ($b->nama_perusahaan ?? '') . '</style><left>',
                '<style border="thin">' . ($b->phone ?? '') . '</style><center>',
                '<style border="thin">' . ($b->email ?? '') . '</style><left>',
                '<style border="thin">' . ($b->address ?? '') . '</style><left>',
                '<style border="thin">' . ($b->city ?? '') . '</style><left>',
                '<style border="thin">' . ($b->province ?? '') . '</style><left>',
                '<style border="thin">' . ($b->postal_code ?? '') . '</style><center>',
                '<style border="thin">' . ($b->manager_name ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_cabang_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-cabang');

        $rows = [
            ['Kode Cabang', 'Nama Cabang', 'Nama Perusahaan', 'No Telepon', 'Email', 'Alamat', 'Kota', 'Provinsi', 'Kode Pos', 'Nama Kepala Cabang', 'Status'],
            ['CBG001', 'Cabang Bandung', 'PT. Contoh Maju Bersama', '081234567890', 'bandung@perusahaan.com', 'Jl. Jend Sudirman No 12', 'Bandung', 'Jawa Barat', '40111', 'Asep Saepudin', 'Active'],
            ['', 'Cabang Surabaya', 'PT. Contoh Maju Bersama', '089876543210', 'surabaya@perusahaan.com', 'Jl. Basuki Rahmat No 45', 'Surabaya', 'Jawa Timur', '60271', 'Budi Santoso', 'Active'],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_cabang.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-cabang');

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

            $existingCodes = DB::table('branches')
                ->pluck('kode_cabang')
                ->map(fn($c) => strtolower($c))
                ->flip()
                ->toArray();

            $existingNames = DB::table('branches')
                ->whereNull('deleted_at')
                ->pluck('nama_cabang')
                ->map(fn($n) => strtolower($n))
                ->flip()
                ->toArray();

            $now = now()->toDateTimeString();

            // Calculate starting numeric suffix once in memory
            $maxNumber = 0;
            foreach ($existingCodes as $c => $val) {
                if (str_starts_with($c, 'cbg-')) {
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

                $rawKode        = isset($row[0]) ? trim($row[0]) : '';
                $rawNama        = isset($row[1]) ? trim($row[1]) : '';
                $rawPerusahaan  = isset($row[2]) ? trim($row[2]) : '';
                $rawPhone       = isset($row[3]) ? trim($row[3]) : '';
                $rawEmail       = isset($row[4]) ? trim($row[4]) : '';
                $rawAddress     = isset($row[5]) ? trim($row[5]) : '';
                $rawCity        = isset($row[6]) ? trim($row[6]) : '';
                $rawProvince    = isset($row[7]) ? trim($row[7]) : '';
                $rawPostalCode  = isset($row[8]) ? trim($row[8]) : '';
                $rawManager     = isset($row[9]) ? trim($row[9]) : '';
                $rawStatus      = isset($row[10]) ? trim($row[10]) : 'Active';
                $rowNum         = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Cabang wajib diisi.");
                }
                if (strlen($rawNama) > 30) {
                    throw new \Exception("Baris {$rowNum}: Nama Cabang maksimal 30 karakter.");
                }

                $namaLower = strtolower($rawNama);
                if (isset($processedNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Cabang '{$rawNama}' duplikat di dalam file.");
                }
                if (isset($existingNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Cabang '{$rawNama}' sudah terdaftar di database.");
                }
                $processedNames[$namaLower] = true;

                if (empty($rawPerusahaan)) {
                    throw new \Exception("Baris {$rowNum}: Nama Perusahaan wajib diisi.");
                }
                if (strlen($rawPerusahaan) > 50) {
                    throw new \Exception("Baris {$rowNum}: Nama Perusahaan maksimal 50 karakter.");
                }

                if (!empty($rawPhone)) {
                    $rawPhone = str_replace([' ', '-', '+', '(', ')'], '', $rawPhone);
                    if (!ctype_digit($rawPhone)) {
                        throw new \Exception("Baris {$rowNum}: No Telepon harus berupa angka saja.");
                    }
                    if (strlen($rawPhone) > 13) {
                        throw new \Exception("Baris {$rowNum}: No Telepon maksimal 13 karakter.");
                    }
                }

                if (!empty($rawEmail)) {
                    if (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception("Baris {$rowNum}: Format Email '{$rawEmail}' tidak valid.");
                    }
                    if (strlen($rawEmail) > 50) {
                        throw new \Exception("Baris {$rowNum}: Email maksimal 50 karakter.");
                    }
                }

                if (strlen($rawAddress) > 60) {
                    throw new \Exception("Baris {$rowNum}: Alamat maksimal 60 karakter.");
                }

                if (strlen($rawCity) > 20) {
                    throw new \Exception("Baris {$rowNum}: Kota maksimal 20 karakter.");
                }

                if (strlen($rawProvince) > 20) {
                    throw new \Exception("Baris {$rowNum}: Provinsi maksimal 20 karakter.");
                }

                if (!empty($rawPostalCode)) {
                    if (!ctype_digit($rawPostalCode)) {
                        throw new \Exception("Baris {$rowNum}: Kode Pos harus berupa angka saja.");
                    }
                    if (strlen($rawPostalCode) > 10) {
                        throw new \Exception("Baris {$rowNum}: Kode Pos maksimal 10 karakter.");
                    }
                }

                if (strlen($rawManager) > 40) {
                    throw new \Exception("Baris {$rowNum}: Nama Kepala Cabang maksimal 40 karakter.");
                }

                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                // Memory-based code generation
                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $generatedCode = 'CBG-' . str_pad($maxNumber, 2, '0', STR_PAD_LEFT);
                    while (isset($existingCodes[strtolower($generatedCode)]) || isset($processedCodes[strtolower($generatedCode)])) {
                        $maxNumber++;
                        $generatedCode = 'CBG-' . str_pad($maxNumber, 2, '0', STR_PAD_LEFT);
                    }
                    $kode = $generatedCode;
                } else {
                    if (isset($existingCodes[strtolower($kode)])) {
                        throw new \Exception("Baris {$rowNum}: Kode Cabang '{$kode}' sudah terdaftar.");
                    }
                    if (isset($processedCodes[strtolower($kode)])) {
                        throw new \Exception("Baris {$rowNum}: Kode Cabang '{$kode}' ganda di dalam file.");
                    }
                }
                $processedCodes[strtolower($kode)] = true;

                $insertData[] = [
                    'kode_cabang'     => strtoupper($kode),
                    'nama_cabang'     => $rawNama,
                    'nama_perusahaan' => $rawPerusahaan,
                    'phone'           => $rawPhone ?: null,
                    'email'           => $rawEmail ?: null,
                    'address'         => $rawAddress ?: null,
                    'city'            => $rawCity ?: null,
                    'province'        => $rawProvince ?: null,
                    'postal_code'     => $rawPostalCode ?: null,
                    'manager_name'    => $rawManager ?: null,
                    'status'          => $rawStatus,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];

                if (count($insertData) >= $chunkSize) {
                    $this->validateAndInsertBranchChunks($insertData);
                    $insertData = [];
                }
            }

            if (!empty($insertData)) {
                $this->validateAndInsertBranchChunks($insertData);
            }

            DB::commit();
            return response()->json(['message' => 'Data cabang berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import cabang: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor data.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import cabang: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal Import: ' . $e->getMessage()], 422);
        }
    }

    private function validateAndInsertBranchChunks(array $chunk)
    {
        DB::table('branches')->insert($chunk);
    }
}
