<?php

namespace App\Http\Controllers;

use App\Models\MasterGudang;
use App\Models\Branch;
use App\Models\Company;
use App\Models\MasterJenisGudang;
use App\Models\User;
use App\Models\Karyawan;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterGudangController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-gudang');
        $activeCompanyId = session('active_company_id');

        // Branches options: all active if Super Admin, otherwise only user's branches in active company
        $branches = auth()->user()->hasRole('Super Admin')
            ? Branch::active()->forActiveCompany()->get()
            : auth()->user()->branches()->active()->forActiveCompany()->get();

        $jenisGudangs = MasterJenisGudang::active()->forActiveCompany()->orderBy('urutan', 'asc')->get();
        
        $employees = Karyawan::where('status', 'Aktif')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        // Company name for PT Pusat label in dropdown
        $activeCompany = Company::find($activeCompanyId);

        return view('master_gudang.gudang.viewgudang', compact('branches', 'jenisGudangs', 'employees', 'activeCompany'));
    }

    /**
     * DataTables AJAX endpoint — server-side processing.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-gudang');

        $draw     = (int) $request->get('draw', 1);
        $start    = (int) $request->get('start', 0);
        $length   = (int) $request->get('length', 10);
        $search   = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [
            0 => 'id',
            1 => 'kode_gudang',
            2 => 'nama_gudang',
            3 => 'cabang_id',
            4 => 'jenis_gudang_id',
            5 => 'penanggung_jawab_id',
            6 => 'gunakan_lokasi_rak',
            7 => 'izinkan_stok_negatif',
            8 => 'status'
        ];
        $orderColumn = $columns[$orderCol] ?? 'id';
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        // Load company for PT Pusat name
        $activeCompany = Company::find($activeCompanyId);
        $ptName = $activeCompany ? $activeCompany->nama_perusahaan : 'PT Pusat';

        $query = MasterGudang::forActiveCompany()->with(['cabang', 'jenisGudang', 'penanggungJawab']);

        if ($activeBranchId) {
            $query->where(function ($q) use ($activeBranchId) {
                $q->where('cabang_id', $activeBranchId)
                  ->orWhereNull('cabang_id');
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_gudang', 'like', "%{$search}%")
                  ->orWhere('nama_gudang', 'like', "%{$search}%")
                  ->orWhereHas('cabang', function ($bc) use ($search) {
                      $bc->where('nama_cabang', 'like', "%{$search}%");
                  })
                  ->orWhereHas('jenisGudang', function ($jg) use ($search) {
                      $jg->where('nama_jenis_gudang', 'like', "%{$search}%");
                  })
                  ->orWhereHas('penanggungJawab', function ($u) use ($search) {
                      $u->where('nama_lengkap', 'like', "%{$search}%");
                  });
            });
        }

        $totalFiltered = $query->count();
        $totalDataQuery = MasterGudang::forActiveCompany();
        if ($activeBranchId) {
            $totalDataQuery->where(function ($q) use ($activeBranchId) {
                $q->where('cabang_id', $activeBranchId)
                  ->orWhereNull('cabang_id');
            });
        }
        $totalData = $totalDataQuery->count();

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($g, $index) use ($start, $ptName) {
            return [
                'no'                    => $start + $index + 1,
                'id'                    => $g->id,
                'kode_gudang'           => e($g->kode_gudang),
                'nama_gudang'           => e($g->nama_gudang),
                'cabang_nama'           => $g->cabang ? e($g->cabang->nama_cabang) : $ptName,
                'is_pt_pusat'           => is_null($g->cabang_id),
                'jenis_gudang_nama'     => $g->jenisGudang ? e($g->jenisGudang->nama_jenis_gudang) : null,
                'penanggung_jawab_nama' => $g->penanggungJawab ? e($g->penanggungJawab->nama_lengkap) : null,
                'gunakan_lokasi_rak'    => (bool) $g->gunakan_lokasi_rak,
                'izinkan_stok_negatif'  => (bool) $g->izinkan_stok_negatif,
                'status'                => $g->status,
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
        $this->authorize('tambah-gudang');
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'nama_gudang'         => 'required|string|max:30',
            'cabang_id'           => 'nullable|integer|exists:branches,id',
            'jenis_gudang_id'     => 'required|integer|exists:master_jenis_gudang,id',
            'penanggung_jawab_id' => 'nullable|integer|exists:karyawans,id',
            'gunakan_lokasi_rak'  => 'nullable|boolean',
            'izinkan_stok_negatif'=> 'nullable|boolean',
            'alamat'              => 'nullable|string|max:60',
            'telepon'             => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'email'               => 'nullable|email|max:50',
            'keterangan'          => 'nullable|string|max:30',
            'status'              => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $kode = CodeGeneratorHelper::generate('master_gudang', 'GDG-', 'kode_gudang');

            MasterGudang::create([
                'company_id'           => $activeCompanyId,
                'kode_gudang'          => $kode,
                'nama_gudang'          => $request->nama_gudang,
                'cabang_id'            => $request->cabang_id,
                'jenis_gudang_id'      => $request->jenis_gudang_id,
                'penanggung_jawab_id'  => $request->penanggung_jawab_id,
                'gunakan_lokasi_rak'   => $request->boolean('gunakan_lokasi_rak'),
                'izinkan_stok_negatif' => $request->boolean('izinkan_stok_negatif'),
                'alamat'               => $request->alamat,
                'telepon'              => $request->telepon,
                'email'                => $request->email,
                'keterangan'           => $request->keterangan,
                'status'               => $request->status,
            ]);

            DB::commit();
            return response()->json(['message' => 'Gudang berhasil ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store gudang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menambah gudang.'], 500);
        }
    }

    public function edit(MasterGudang $gudang)
    {
        $this->authorize('edit-gudang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $gudang->company_id && $gudang->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Gudang ini milik PT lain.'], 403);
        }
        return response()->json(['gudang' => $gudang]);
    }

    public function update(Request $request, MasterGudang $gudang)
    {
        $this->authorize('edit-gudang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $gudang->company_id && $gudang->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit gudang milik PT lain.'], 403);
        }

        $request->validate([
            'nama_gudang'         => 'required|string|max:30',
            'cabang_id'           => 'nullable|integer|exists:branches,id',
            'jenis_gudang_id'     => 'required|integer|exists:master_jenis_gudang,id',
            'penanggung_jawab_id' => 'nullable|integer|exists:karyawans,id',
            'gunakan_lokasi_rak'  => 'nullable|boolean',
            'izinkan_stok_negatif'=> 'nullable|boolean',
            'alamat'              => 'nullable|string|max:60',
            'telepon'             => 'nullable|string|max:13|regex:/^[0-9]+$/',
            'email'               => 'nullable|email|max:50',
            'keterangan'          => 'nullable|string|max:30',
            'status'              => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $gudang->update([
                'nama_gudang'          => $request->nama_gudang,
                'cabang_id'            => $request->cabang_id,
                'jenis_gudang_id'      => $request->jenis_gudang_id,
                'penanggung_jawab_id'  => $request->penanggung_jawab_id,
                'gunakan_lokasi_rak'   => $request->boolean('gunakan_lokasi_rak'),
                'izinkan_stok_negatif' => $request->boolean('izinkan_stok_negatif'),
                'alamat'               => $request->alamat,
                'telepon'              => $request->telepon,
                'email'                => $request->email,
                'keterangan'           => $request->keterangan,
                'status'               => $request->status,
            ]);

            DB::commit();
            return response()->json(['message' => 'Gudang berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update gudang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate gudang.'], 500);
        }
    }

    public function destroy(MasterGudang $gudang)
    {
        $this->authorize('hapus-gudang');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $gudang->company_id && $gudang->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus gudang milik PT lain.'], 403);
        }

        // Business rule constraint check
        $stokCount = \App\Models\StokGudang::where('gudang_id', $gudang->id)->where('stok_aktual', '>', 0)->count();
        if ($stokCount > 0) {
            return response()->json([
                'message' => "Gudang \"{$gudang->nama_gudang}\" tidak dapat dihapus karena masih memiliki stok barang di dalamnya."
            ], 422);
        }

        $receiptCount = \App\Models\GoodsReceipt::where('gudang_id', $gudang->id)->count();
        $issueCount   = \App\Models\GoodsIssue::where('gudang_id', $gudang->id)->count();
        if ($receiptCount > 0 || $issueCount > 0) {
            return response()->json([
                'message' => "Gudang \"{$gudang->nama_gudang}\" tidak dapat dihapus karena sudah memiliki riwayat transaksi penerimaan/pengeluaran barang."
            ], 422);
        }

        try {
            DB::beginTransaction();
            $gudang->delete();
            DB::commit();

            return response()->json(['message' => 'Gudang berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete gudang: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus gudang.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-gudang');
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        set_time_limit(0);

        $gudangs = MasterGudang::forActiveCompany()
            ->with(['cabang', 'jenisGudang', 'penanggungJawab'])
            ->when($activeBranchId, fn($q) => $q->where(function ($q2) use ($activeBranchId) {
                $q2->where('cabang_id', $activeBranchId)
                   ->orWhereNull('cabang_id');
            }))
            ->orderBy('id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Gudang</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Gudang</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Cabang</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Jenis Gudang</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Penanggung Jawab (PIC)</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Lokasi Rak</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Stok Negatif</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Alamat</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>No. Telepon</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Email</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Keterangan</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($gudangs as $g) {
            $statusStyle = $g->status === 'Active'
                ? '<style border="thin" color="#10b981"><b>Active</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Inactive</b></style><center>';

            $rows[] = [
                '<style border="thin">' . $g->kode_gudang . '</style><center>',
                '<style border="thin">' . $g->nama_gudang . '</style><left>',
                '<style border="thin">' . ($g->cabang ? $g->cabang->nama_cabang : '') . '</style><left>',
                '<style border="thin">' . ($g->jenisGudang ? $g->jenisGudang->nama_jenis_gudang : '') . '</style><left>',
                '<style border="thin">' . ($g->penanggungJawab ? $g->penanggungJawab->nama_lengkap : '') . '</style><left>',
                '<style border="thin">' . ($g->gunakan_lokasi_rak ? 'Aktif' : 'Tidak') . '</style><center>',
                '<style border="thin">' . ($g->izinkan_stok_negatif ? 'Izinkan' : 'Proteksi') . '</style><center>',
                '<style border="thin">' . ($g->alamat ?? '') . '</style><left>',
                '<style border="thin">' . ($g->telepon ?? '') . '</style><center>',
                '<style border="thin">' . ($g->email ?? '') . '</style><left>',
                '<style border="thin">' . ($g->keterangan ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_gudang_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-gudang');

        $rows = [
            [
                'Kode Gudang',
                'Nama Gudang',
                'Cabang',
                'Jenis Gudang',
                'Penanggung Jawab (PIC)',
                'Lokasi Rak',
                'Stok Negatif',
                'Alamat',
                'No. Telepon',
                'Email',
                'Keterangan',
                'Status'
            ],
            [
                'GDG-EXAMPLE01',
                'Gudang Utama',
                'PASTAC',
                'Gudang Utama',
                'Cahyo Prasetyo',
                'Aktif',
                'Proteksi',
                'Alamat Gudang 1',
                '081234567890',
                'gudang@pastac.com',
                'Gudang pusat',
                'Active'
            ],
            [
                '',
                'Gudang Transit',
                'PASTAC',
                'Gudang Utama',
                '',
                'Tidak',
                'Proteksi',
                'Alamat Gudang 2',
                '',
                '',
                '',
                'Active'
            ],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_gudang.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-gudang');

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

            // Load existing codes into memory for O(1) checks
            $existingCodes = DB::table('master_gudang')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->pluck('kode_gudang')
                ->map(fn($c) => strtolower($c))
                ->flip()
                ->toArray();

            $existingNames = DB::table('master_gudang')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereNull('deleted_at')
                ->pluck('nama_gudang')
                ->map(fn($n) => strtolower($n))
                ->flip()
                ->toArray();

            $branchesMap = DB::table('branches')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(nama_cabang)'))
                ->toArray();

            $jenisGudangsMap = DB::table('master_jenis_gudang')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(nama_jenis_gudang)'))
                ->toArray();

            $karyawansMap = DB::table('karyawans')
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(nama_lengkap)'))
                ->toArray();

            $now = now()->toDateTimeString();
            $authId = auth()->id();

            // Calculate starting numeric suffix once in memory
            $maxNumber = 0;
            foreach ($existingCodes as $c => $val) {
                if (str_starts_with($c, 'gdg-')) {
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

                $rawKode         = isset($row[0]) ? trim($row[0]) : '';
                $rawNama         = isset($row[1]) ? trim($row[1]) : '';
                $rawCabang       = isset($row[2]) ? trim($row[2]) : '';
                $rawJenisGudang  = isset($row[3]) ? trim($row[3]) : '';
                $rawPic          = isset($row[4]) ? trim($row[4]) : '';
                $rawLokasiRak    = isset($row[5]) ? trim($row[5]) : '';
                $rawStokNegatif  = isset($row[6]) ? trim($row[6]) : '';
                $rawAlamat       = isset($row[7]) ? trim($row[7]) : '';
                $rawTelepon      = isset($row[8]) ? trim($row[8]) : '';
                $rawEmail        = isset($row[9]) ? trim($row[9]) : '';
                $rawKeterangan   = isset($row[10]) ? trim($row[10]) : '';
                $rawStatus       = isset($row[11]) ? trim($row[11]) : 'Active';
                $rowNum          = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Gudang wajib diisi.");
                }
                if (strlen($rawNama) > 30) {
                    throw new \Exception("Baris {$rowNum}: Nama Gudang maksimal 30 karakter.");
                }

                $namaLower = strtolower($rawNama);
                if (isset($processedNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Gudang '{$rawNama}' duplikat di dalam file.");
                }
                if (isset($existingNames[$namaLower])) {
                    throw new \Exception("Baris {$rowNum}: Nama Gudang '{$rawNama}' sudah terdaftar di database.");
                }
                $processedNames[$namaLower] = true;

                if (empty($rawCabang)) {
                    throw new \Exception("Baris {$rowNum}: Cabang wajib diisi.");
                }
                $cabangKey = strtolower($rawCabang);
                if (!isset($branchesMap[$cabangKey])) {
                    throw new \Exception("Baris {$rowNum}: Cabang '{$rawCabang}' tidak ditemukan.");
                }
                $cabangId = $branchesMap[$cabangKey];

                if (empty($rawJenisGudang)) {
                    throw new \Exception("Baris {$rowNum}: Jenis Gudang wajib diisi.");
                }
                $jgKey = strtolower($rawJenisGudang);
                if (!isset($jenisGudangsMap[$jgKey])) {
                    throw new \Exception("Baris {$rowNum}: Jenis Gudang '{$rawJenisGudang}' tidak ditemukan.");
                }
                $jgId = $jenisGudangsMap[$jgKey];

                $picId = null;
                if (!empty($rawPic)) {
                    $picKey = strtolower($rawPic);
                    if (!isset($karyawansMap[$picKey])) {
                        throw new \Exception("Baris {$rowNum}: Penanggung Jawab '{$rawPic}' tidak ditemukan.");
                    }
                    $picId = $karyawansMap[$picKey];
                }

                $gunakanLokasiRak = 0;
                if (!empty($rawLokasiRak)) {
                    if (!in_array($rawLokasiRak, ['Aktif', 'Tidak'])) {
                        throw new \Exception("Baris {$rowNum}: Lokasi Rak harus berisi 'Aktif' atau 'Tidak'.");
                    }
                    $gunakanLokasiRak = $rawLokasiRak === 'Aktif' ? 1 : 0;
                }

                $izinkanStokNegatif = 0;
                if (!empty($rawStokNegatif)) {
                    if (!in_array($rawStokNegatif, ['Izinkan', 'Proteksi'])) {
                        throw new \Exception("Baris {$rowNum}: Stok Negatif harus berisi 'Izinkan' atau 'Proteksi'.");
                    }
                    $izinkanStokNegatif = $rawStokNegatif === 'Izinkan' ? 1 : 0;
                }

                if (strlen($rawAlamat) > 60) {
                    throw new \Exception("Baris {$rowNum}: Alamat maksimal 60 karakter.");
                }

                if (!empty($rawTelepon)) {
                    if (!preg_match('/^[0-9]+$/', $rawTelepon)) {
                        throw new \Exception("Baris {$rowNum}: No. Telepon hanya boleh berisi angka.");
                    }
                    if (strlen($rawTelepon) > 13) {
                        throw new \Exception("Baris {$rowNum}: No. Telepon maksimal 13 karakter.");
                    }
                }

                if (!empty($rawEmail)) {
                    if (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception("Baris {$rowNum}: Format Email tidak valid.");
                    }
                    if (strlen($rawEmail) > 50) {
                        throw new \Exception("Baris {$rowNum}: Email maksimal 50 karakter.");
                    }
                }

                if (strlen($rawKeterangan) > 30) {
                    throw new \Exception("Baris {$rowNum}: Keterangan maksimal 30 karakter.");
                }

                if (!in_array($rawStatus, ['Active', 'Inactive'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Active' atau 'Inactive'.");
                }

                // Memory-based code generation
                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $generatedCode = 'GDG-' . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
                    while (isset($existingCodes[strtolower($generatedCode)]) || isset($processedCodes[strtolower($generatedCode)])) {
                        $maxNumber++;
                        $generatedCode = 'GDG-' . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
                    }
                    $kode = $generatedCode;
                } else {
                    if (isset($existingCodes[strtolower($kode)])) {
                        throw new \Exception("Baris {$rowNum}: Kode Gudang '{$kode}' sudah terdaftar.");
                    }
                    if (isset($processedCodes[strtolower($kode)])) {
                        throw new \Exception("Baris {$rowNum}: Kode Gudang '{$kode}' ganda di dalam file.");
                    }
                }
                $processedCodes[strtolower($kode)] = true;

                $insertData[] = [
                    'company_id'           => $activeCompanyId,
                    'kode_gudang'          => $kode,
                    'nama_gudang'          => $rawNama,
                    'cabang_id'            => $cabangId,
                    'jenis_gudang_id'      => $jgId,
                    'penanggung_jawab_id'  => $picId,
                    'gunakan_lokasi_rak'   => $gunakanLokasiRak,
                    'izinkan_stok_negatif' => $izinkanStokNegatif,
                    'alamat'               => $rawAlamat ?: null,
                    'telepon'              => $rawTelepon ?: null,
                    'email'                => $rawEmail ?: null,
                    'keterangan'           => $rawKeterangan ?: null,
                    'status'               => $rawStatus,
                    'created_by'           => $authId,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];

                if (count($insertData) >= $chunkSize) {
                    $this->validateAndInsertWarehouseChunks($insertData);
                    $insertData = [];
                }
            }

            if (!empty($insertData)) {
                $this->validateAndInsertWarehouseChunks($insertData);
            }

            DB::commit();
            return response()->json(['message' => 'Data gudang berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import gudang: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor data.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import gudang: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal Import: ' . $e->getMessage()], 422);
        }
    }

    private function validateAndInsertWarehouseChunks(array $chunk)
    {
        DB::table('master_gudang')->insert($chunk);
    }
}
