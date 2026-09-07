<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Department;
use App\Models\Position;
use App\Helpers\CodeGeneratorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class MasterKaryawanController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-karyawan');
        $activeCompanyId = session('active_company_id');
        $departments = Department::active()->forActiveCompany()->orderBy('department_name')->get();
        $jabatans = Position::active()->forActiveCompany()->orderBy('position_name')->get();

        $cacheKeyTotal    = 'master_karyawan_total_count_comp_'    . ($activeCompanyId ?? 0);
        $cacheKeyAktif    = 'master_karyawan_aktif_count_comp_'    . ($activeCompanyId ?? 0);
        $cacheKeyNonaktif = 'master_karyawan_nonaktif_count_comp_' . ($activeCompanyId ?? 0);

        $totalKaryawan = Cache::remember($cacheKeyTotal, 60, function () {
            return Karyawan::forActiveCompany()->count();
        });
        $aktifKaryawan = Cache::remember($cacheKeyAktif, 60, function () {
            return Karyawan::forActiveCompany()->where('status', 'Aktif')->count();
        });
        $nonaktifKaryawan = Cache::remember($cacheKeyNonaktif, 60, function () {
            return Karyawan::forActiveCompany()->where('status', 'Nonaktif')->count();
        });

        return view('master_organisasi.karyawan.viewkaryawan', compact('departments', 'jabatans', 'totalKaryawan', 'aktifKaryawan', 'nonaktifKaryawan'));
    }

    /**
     * DataTables AJAX endpoint — server-side processing.
     */
    public function data(Request $request)
    {
        $this->authorize('lihat-karyawan');

        $draw     = (int) $request->get('draw', 1);
        $start    = (int) $request->get('start', 0);
        $length   = (int) $request->get('length', 10);
        $search   = trim($request->get('search')['value'] ?? '');
        $orderCol = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir = in_array($request->get('order')[0]['dir'] ?? 'asc', ['asc', 'desc'])
                    ? $request->get('order')[0]['dir'] : 'asc';

        $columns = [
            0 => 'id',
            1 => 'kode_karyawan',
            2 => 'nama_lengkap',
            3 => 'nik',
            4 => 'departemen_id',
            5 => 'jabatan_id',
            6 => 'no_hp',
            7 => 'status'
        ];
        $orderColumn = $columns[$orderCol] ?? 'id';
        $activeCompanyId = session('active_company_id');

        $query = Karyawan::forActiveCompany()->with(['department', 'position', 'user']);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_karyawan', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhereHas('department', function ($d) use ($search) {
                      $d->where('department_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('position', function ($p) use ($search) {
                      $p->where('position_name', 'like', "%{$search}%");
                  });
            });
        }

        $cacheKeyTotal = 'master_karyawan_total_count_comp_' . ($activeCompanyId ?? 0);
        $totalData = Cache::remember($cacheKeyTotal, 60, function () {
            return Karyawan::forActiveCompany()->count();
        });

        if ($search === '') {
            $totalFiltered = $totalData;
        } else {
            $totalFiltered = $query->count();
        }

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($k, $index) use ($start) {
            return [
                'no'              => $start + $index + 1,
                'id'              => $k->id,
                'foto'            => $k->foto ? asset($k->foto) : ($k->jenis_kelamin === 'P' ? asset('gambar/perempuan.jpg') : asset('gambar/laki.jpg')),
                'kode_karyawan'   => $k->kode_karyawan ? e($k->kode_karyawan) : null,
                'nama_lengkap'    => e($k->nama_lengkap),
                'inisial'         => $k->inisial ? e($k->inisial) : null,
                'nik'             => $k->nik ? e($k->nik) : null,
                'department_name' => $k->department ? e($k->department->department_name) : null,
                'position_name'   => $k->position ? e($k->position->position_name) : null,
                'no_hp'           => $k->no_hp ? e($k->no_hp) : null,
                'status'          => $k->status,
                'has_user'        => $k->user ? true : false,
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
        $this->authorize('tambah-karyawan');
        $activeCompanyId = session('active_company_id');

        $request->validate([
            'nik' => [
                'nullable', 'string', 'max:17',
                \Illuminate\Validation\Rule::unique('karyawans', 'nik')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'kode_karyawan' => [
                'nullable', 'string', 'max:4',
                \Illuminate\Validation\Rule::unique('karyawans', 'kode_karyawan')
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'nama_lengkap' => 'required|string|max:50',
            'inisial' => 'required|string|max:4',
            'no_hp' => 'nullable|regex:/^[0-9]+$/|max:13',
            'alamat' => 'nullable|string|max:60',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'tanggal_masuk' => 'nullable|date',
            'departemen_id' => 'required|exists:departments,id',
            'jabatan_id' => 'required|exists:positions,id',
            'status' => 'required|in:Aktif,Nonaktif',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoFile = $request->file('foto');
                $fileName = time() . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                $fotoFile->move(public_path('uploads/karyawan'), $fileName);
                $fotoPath = 'uploads/karyawan/' . $fileName;
            }

            $code = $request->kode_karyawan;
            if (empty($code)) {
                $code = CodeGeneratorHelper::generate('karyawans', 'K', 'kode_karyawan', 3);
            } else {
                $code = strtoupper($code);
            }

            $newKaryawan = Karyawan::create([
                'company_id'    => $activeCompanyId,
                'nik'           => $request->nik,
                'kode_karyawan' => $code,
                'nama_lengkap'  => $request->nama_lengkap,
                'inisial'       => $request->inisial ? strtoupper($request->inisial) : null,
                'no_hp'         => $request->no_hp,
                'alamat'        => $request->alamat,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tanggal_masuk' => $request->tanggal_masuk,
                'departemen_id' => $request->departemen_id,
                'jabatan_id'    => $request->jabatan_id,
                'status'        => $request->status,
                'foto'          => $fotoPath,
                'created_by'    => auth()->id()
            ]);

            $newKaryawan->load(['department', 'position']);

            DB::commit();
            Cache::forget('master_karyawan_total_count');
            Cache::forget('master_karyawan_aktif_count');
            Cache::forget('master_karyawan_nonaktif_count');
            return response()->json([
                'message'      => 'Data Karyawan berhasil disimpan!',
                'karyawan'     => [
                    'id'            => $newKaryawan->id,
                    'nama_lengkap'  => $newKaryawan->nama_lengkap,
                    'departemen_id' => $newKaryawan->departemen_id,
                    'department_name' => $newKaryawan->department ? $newKaryawan->department->department_name : '-',
                    'position_name' => $newKaryawan->position ? $newKaryawan->position->position_name : '-',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store master karyawan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menyimpan data karyawan.'], 500);
        }
    }

    public function edit(Karyawan $karyawan)
    {
        $this->authorize('edit-karyawan');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $karyawan->company_id && $karyawan->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Karyawan ini milik PT lain.'], 403);
        }
        $karyawan->load(['department', 'position']);

        $departments = Department::active()->forActiveCompany()->orderBy('department_name')->get();
        $jabatans    = Position::active()->forActiveCompany()->orderBy('position_name')->get();

        return response()->json([
            'karyawan'    => $karyawan,
            'departments' => $departments,
            'jabatans'    => $jabatans
        ]);
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $this->authorize('edit-karyawan');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $karyawan->company_id && $karyawan->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin mengedit karyawan milik PT lain.'], 403);
        }

        $request->validate([
            'nik' => [
                'nullable', 'string', 'max:17',
                \Illuminate\Validation\Rule::unique('karyawans', 'nik')->ignore($karyawan->id)
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'kode_karyawan' => [
                'required', 'string', 'max:4',
                \Illuminate\Validation\Rule::unique('karyawans', 'kode_karyawan')->ignore($karyawan->id)
                    ->where(fn($q) => $activeCompanyId ? $q->where('company_id', $activeCompanyId)->whereNull('deleted_at') : $q->whereNull('deleted_at'))
            ],
            'nama_lengkap' => 'required|string|max:50',
            'inisial' => 'required|string|max:4',
            'no_hp' => 'nullable|regex:/^[0-9]+$/|max:13',
            'alamat' => 'nullable|string|max:60',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'tanggal_masuk' => 'nullable|date',
            'departemen_id' => 'required|exists:departments,id',
            'jabatan_id' => 'required|exists:positions,id',
            'status' => 'required|in:Aktif,Nonaktif',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = [
            'nik' => $request->nik,
            'kode_karyawan' => $request->kode_karyawan,
            'nama_lengkap' => $request->nama_lengkap,
            'inisial' => $request->inisial ? strtoupper($request->inisial) : null,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tanggal_lahir' => $request->tanggal_lahir,
            'tanggal_masuk' => $request->tanggal_masuk,
            'departemen_id' => $request->departemen_id,
            'jabatan_id' => $request->jabatan_id,
            'status' => $request->status,
            'updated_by' => auth()->id()
        ];

        try {
            DB::beginTransaction();

            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($karyawan->foto && file_exists(public_path($karyawan->foto))) {
                    @unlink(public_path($karyawan->foto));
                }
                $fotoFile = $request->file('foto');
                $fileName = time() . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                $fotoFile->move(public_path('uploads/karyawan'), $fileName);
                $data['foto'] = 'uploads/karyawan/' . $fileName;
            }

            $karyawan->update($data);

            // Sinkronisasi dengan user
            if ($karyawan->user) {
                $karyawan->user->update([
                    'kode_karyawan' => $karyawan->kode_karyawan,
                    'nama_lengkap' => $karyawan->nama_lengkap,
                    'foto' => $karyawan->foto,
                    'departemen_id' => $karyawan->departemen_id,
                    'jabatan_id' => $karyawan->jabatan_id,
                    'no_hp' => $karyawan->no_hp,
                    'jenis_kelamin' => $karyawan->jenis_kelamin,
                    'status' => $karyawan->status,
                ]);
            }

            DB::commit();
            $cid = $activeCompanyId ?? 0;
            Cache::forget('master_karyawan_total_count_comp_' . $cid);
            Cache::forget('master_karyawan_aktif_count_comp_' . $cid);
            Cache::forget('master_karyawan_nonaktif_count_comp_' . $cid);
            return response()->json(['message' => 'Data Karyawan berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update master karyawan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat memperbarui data karyawan.'], 500);
        }
    }

    public function destroy(Karyawan $karyawan)
    {
        $this->authorize('hapus-karyawan');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && $karyawan->company_id && $karyawan->company_id != $activeCompanyId) {
            return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki izin menghapus karyawan milik PT lain.'], 403);
        }

        // Business rules check
        if ($karyawan->user) {
            $userId = $karyawan->user->id;
            
            $gudangCount = \App\Models\MasterGudang::where('penanggung_jawab_id', $userId)->count();
            if ($gudangCount > 0) {
                return response()->json([
                    'message' => "Karyawan \"{$karyawan->nama_lengkap}\" tidak dapat dihapus karena akun user-nya terdaftar sebagai penanggung jawab di {$gudangCount} gudang."
                ], 422);
            }

            $prCount = \App\Models\PurchaseRequisition::where('created_by', $userId)
                ->orWhere('updated_by', $userId)
                ->count();
            if ($prCount > 0) {
                return response()->json([
                    'message' => "Karyawan \"{$karyawan->nama_lengkap}\" tidak dapat dihapus karena akun user-nya terkait dengan {$prCount} data pengajuan barang (PR)."
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Hapus foto jika ada
            if ($karyawan->foto && file_exists(public_path($karyawan->foto))) {
                @unlink(public_path($karyawan->foto));
            }

            if ($karyawan->user) {
                $karyawan->user->delete();
            }

            $karyawan->delete();

            DB::commit();
            $cid = $activeCompanyId ?? 0;
            Cache::forget('master_karyawan_total_count_comp_' . $cid);
            Cache::forget('master_karyawan_aktif_count_comp_' . $cid);
            Cache::forget('master_karyawan_nonaktif_count_comp_' . $cid);
            return response()->json(['message' => 'Data Karyawan berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete master karyawan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus data karyawan.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-karyawan');
        $activeCompanyId = session('active_company_id');

        set_time_limit(0);

        // Use cursor to stream rows efficiently, scoped to active company
        $karyawans = DB::table('karyawans')
            ->leftJoin('departments', 'karyawans.departemen_id', '=', 'departments.id')
            ->leftJoin('positions', 'karyawans.jabatan_id', '=', 'positions.id')
            ->select(
                'karyawans.nik',
                'karyawans.kode_karyawan',
                'karyawans.nama_lengkap',
                'karyawans.inisial',
                'departments.department_name',
                'positions.position_name',
                'karyawans.no_hp',
                'karyawans.jenis_kelamin',
                'karyawans.tanggal_lahir',
                'karyawans.tanggal_masuk',
                'karyawans.alamat',
                'karyawans.status'
            )
            ->whereNull('karyawans.deleted_at')
            ->when($activeCompanyId, fn($q) => $q->where('karyawans.company_id', $activeCompanyId))
            ->orderBy('karyawans.id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>NIK</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Karyawan</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Lengkap</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Inisial</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Departemen</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Jabatan</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>No HP</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Jenis Kelamin</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Tanggal Lahir</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Tanggal Masuk</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Alamat</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($karyawans as $k) {
            $statusStyle = $k->status === 'Aktif'
                ? '<style border="thin" color="#10b981"><b>Aktif</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Nonaktif</b></style><center>';

            $jkLabel = $k->jenis_kelamin === 'L' ? 'Laki-laki' : ($k->jenis_kelamin === 'P' ? 'Perempuan' : '');

            $rows[] = [
                '<style border="thin">' . ($k->nik ?? '') . '</style><center>',
                '<style border="thin">' . ($k->kode_karyawan ?? '') . '</style><center>',
                '<style border="thin">' . $k->nama_lengkap . '</style><left>',
                '<style border="thin">' . ($k->inisial ?? '') . '</style><center>',
                '<style border="thin">' . ($k->department_name ?? '') . '</style><left>',
                '<style border="thin">' . ($k->position_name ?? '') . '</style><left>',
                '<style border="thin">' . ($k->no_hp ?? '') . '</style><center>',
                '<style border="thin">' . $jkLabel . '</style><center>',
                '<style border="thin">' . ($k->tanggal_lahir ?? '') . '</style><center>',
                '<style border="thin">' . ($k->tanggal_masuk ?? '') . '</style><center>',
                '<style border="thin">' . ($k->alamat ?? '') . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_karyawan_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-karyawan');

        $rows = [
            [
                'NIK',
                'Kode Karyawan',
                'Nama Lengkap',
                'Inisial',
                'Nama Departemen',
                'Nama Jabatan',
                'No HP',
                'Jenis Kelamin',
                'Tanggal Lahir',
                'Tanggal Masuk',
                'Alamat',
                'Status'
            ],
            [
                '3212345678901234',
                'K001',
                'Ahmad Dani',
                'AD',
                'Human Resource',
                'Manager IT',
                '081234567890',
                'L',
                '1995-05-12',
                '2022-01-15',
                'Jl. Mawar No. 12, Bandung',
                'Aktif'
            ],
            [
                '3212345678905678',
                '',
                'Siti Sarah',
                'SS',
                'Finansial',
                'Staff Keuangan',
                '087712345678',
                'P',
                '1998-09-20',
                '2023-03-01',
                'Jl. Melati No. 45, Jakarta',
                'Aktif'
            ],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_karyawan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importExcel(Request $request)
    {
        $this->authorize('tambah-karyawan');

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
        $processedNIKs  = [];
        $chunkSize      = 2000;

        try {
            DB::beginTransaction();
            $activeCompanyId = session('active_company_id');

            // Load departments scoped to active company
            $departmentsMap = DB::table('departments')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(department_name)'))
                ->toArray();

            // Load positions scoped to active company
            $positionsMap = DB::table('positions')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(position_name)'))
                ->toArray();

            // Get latest code number starting with 'K', scoped to active company
            $latestCode = DB::table('karyawans')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->where('kode_karyawan', 'like', 'K%')
                ->orderBy('kode_karyawan', 'desc')
                ->value('kode_karyawan');

            $maxNumber = 0;
            if ($latestCode) {
                $suffix = substr($latestCode, 1); // skip 'K'
                if (is_numeric($suffix)) {
                    $maxNumber = (int) $suffix;
                }
            }

            $insertData = [];
            $now        = now()->toDateTimeString();
            $creatorId  = auth()->id();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;

                $rawNIK    = isset($row[0]) ? trim($row[0]) : '';
                $rawKode   = isset($row[1]) ? trim($row[1]) : '';
                $rawNama   = isset($row[2]) ? trim($row[2]) : '';
                $rawInit   = isset($row[3]) ? trim($row[3]) : '';
                $rawDept   = isset($row[4]) ? trim($row[4]) : '';
                $rawJab    = isset($row[5]) ? trim($row[5]) : '';
                $rawNoHp   = isset($row[6]) ? trim($row[6]) : '';
                $rawJK     = isset($row[7]) ? trim($row[7]) : '';
                $rawLahir  = isset($row[8]) ? trim($row[8]) : '';
                $rawMasuk  = isset($row[9]) ? trim($row[9]) : '';
                $rawAlamat = isset($row[10]) ? trim($row[10]) : '';
                $rawStatus = isset($row[11]) ? trim($row[11]) : '';
                $rowNum    = $i + 1;

                if (empty($rawNama)) {
                    throw new \Exception("Baris {$rowNum}: Nama Lengkap wajib diisi.");
                }
                if (strlen($rawNama) > 50) {
                    throw new \Exception("Baris {$rowNum}: Nama Lengkap '{$rawNama}' maksimal 50 karakter.");
                }
                if (!empty($rawNIK) && strlen($rawNIK) > 17) {
                    throw new \Exception("Baris {$rowNum}: NIK '{$rawNIK}' maksimal 17 karakter.");
                }
                if (!empty($rawKode) && strlen($rawKode) > 4) {
                    throw new \Exception("Baris {$rowNum}: Kode Karyawan '{$rawKode}' maksimal 4 karakter.");
                }
                if (empty($rawInit)) {
                    throw new \Exception("Baris {$rowNum}: Inisial wajib diisi.");
                }
                if (strlen($rawInit) > 4) {
                    throw new \Exception("Baris {$rowNum}: Inisial '{$rawInit}' maksimal 4 karakter.");
                }
                if (strlen($rawAlamat) > 60) {
                    throw new \Exception("Baris {$rowNum}: Alamat maksimal 60 karakter.");
                }
                if (!empty($rawNoHp)) {
                    if (strlen($rawNoHp) > 13) {
                        throw new \Exception("Baris {$rowNum}: No HP '{$rawNoHp}' maksimal 13 karakter.");
                    }
                    if (!preg_match('/^[0-9]+$/', $rawNoHp)) {
                        throw new \Exception("Baris {$rowNum}: No HP '{$rawNoHp}' hanya boleh berisi angka.");
                    }
                }
                if (empty($rawDept)) {
                    throw new \Exception("Baris {$rowNum}: Nama Departemen wajib diisi.");
                }
                if (empty($rawJab)) {
                    throw new \Exception("Baris {$rowNum}: Nama Jabatan wajib diisi.");
                }
                if (!in_array($rawJK, ['L', 'P'])) {
                    throw new \Exception("Baris {$rowNum}: Jenis Kelamin harus 'L' atau 'P'.");
                }
                if (!in_array($rawStatus, ['Aktif', 'Nonaktif'])) {
                    throw new \Exception("Baris {$rowNum}: Status harus 'Aktif' atau 'Nonaktif'.");
                }

                $deptLower = strtolower($rawDept);
                if (!isset($departmentsMap[$deptLower])) {
                    throw new \Exception("Baris {$rowNum}: Departemen '{$rawDept}' tidak terdaftar.");
                }
                $deptId = $departmentsMap[$deptLower];

                $jabLower = strtolower($rawJab);
                if (!isset($positionsMap[$jabLower])) {
                    throw new \Exception("Baris {$rowNum}: Jabatan '{$rawJab}' tidak terdaftar.");
                }
                $jabId = $positionsMap[$jabLower];

                if (!empty($rawNIK)) {
                    $nikVal = strtolower($rawNIK);
                    if (isset($processedNIKs[$nikVal])) {
                        throw new \Exception("Baris {$rowNum}: NIK '{$rawNIK}' duplikat di dalam file.");
                    }
                    $processedNIKs[$nikVal] = true;
                }

                $kode = $rawKode;
                if (empty($kode)) {
                    $maxNumber++;
                    $kode = 'K' . str_pad($maxNumber, 3, '0', STR_PAD_LEFT);
                } else {
                    if (str_starts_with($kode, 'K')) {
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
                    throw new \Exception("Baris {$rowNum}: Kode Karyawan '{$kode}' duplikat di dalam file.");
                }
                $processedCodes[$kodeLower] = true;

                $insertData[] = [
                    'row_num'       => $rowNum,
                    'company_id'    => $activeCompanyId,
                    'nik'           => $rawNIK ?: null,
                    'kode_karyawan' => strtoupper($kode),
                    'nama_lengkap'  => $rawNama,
                    'inisial'       => $rawInit ? strtoupper($rawInit) : null,
                    'departemen_id' => $deptId,
                    'jabatan_id'    => $jabId,
                    'no_hp'         => $rawNoHp ?: null,
                    'jenis_kelamin' => $rawJK,
                    'tanggal_lahir' => $rawLahir ?: null,
                    'tanggal_masuk' => $rawMasuk ?: null,
                    'alamat'        => $rawAlamat ?: null,
                    'status'        => $rawStatus,
                    'created_by'    => $creatorId,
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
            $cid = $activeCompanyId ?? 0;
            Cache::forget('master_karyawan_total_count_comp_' . $cid);
            Cache::forget('master_karyawan_aktif_count_comp_' . $cid);
            Cache::forget('master_karyawan_nonaktif_count_comp_' . $cid);
            return response()->json(['message' => 'Data karyawan berhasil diimpor!']);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('SQL Error import karyawan: ' . $e->getMessage());
            if ($e->errorInfo[1] == 1062 || $e->getCode() == 23000) {
                return response()->json(['message' => 'Data ini sudah pernah disimpan di database.'], 422);
            }
            return response()->json(['message' => 'Terjadi kesalahan basis data saat mengimpor karyawan.'], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import karyawan: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function validateAndInsertChunks(array $chunk)
    {
        $activeCompanyId = session('active_company_id');
        $chunkCodes = array_map(fn($item) => $item['kode_karyawan'], $chunk);
        $chunkNIKs  = array_filter(array_map(fn($item) => $item['nik'], $chunk));

        // Check duplicates for code, scoped to active company
        $dbCodes = DB::table('karyawans')
            ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
            ->whereIn('kode_karyawan', $chunkCodes)
            ->pluck('kode_karyawan')
            ->toArray();

        // Check duplicates for NIK, scoped to active company
        $dbNIKs = [];
        if (!empty($chunkNIKs)) {
            $dbNIKs = DB::table('karyawans')
                ->when($activeCompanyId, fn($q) => $q->where('company_id', $activeCompanyId))
                ->whereIn('nik', $chunkNIKs)
                ->pluck('nik')
                ->toArray();
        }

        if (!empty($dbCodes) || !empty($dbNIKs)) {
            $dbCodesLower = array_map('strtolower', $dbCodes);
            $dbNIKsLower  = array_map('strtolower', $dbNIKs);

            foreach ($chunk as $item) {
                if (in_array(strtolower($item['kode_karyawan']), $dbCodesLower)) {
                    throw new \Exception("Baris {$item['row_num']}: Kode Karyawan '{$item['kode_karyawan']}' sudah ada di database.");
                }
                if ($item['nik'] && in_array(strtolower($item['nik']), $dbNIKsLower)) {
                    throw new \Exception("Baris {$item['row_num']}: NIK '{$item['nik']}' sudah ada di database.");
                }
            }
        }

        $finalInsert = array_map(function ($item) {
            unset($item['row_num']);
            return $item;
        }, $chunk);

        DB::table('karyawans')->insert($finalInsert);
    }
}
