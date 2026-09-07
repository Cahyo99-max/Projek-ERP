<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class KaryawanController extends Controller
{
    public function index()
    {
        $this->authorize('lihat-karyawan');
        $totalUsers = User::count();
        $aktifUsers = User::where('status', 'Aktif')->count();
        $nonaktifUsers = User::where('status', 'Nonaktif')->count();
        
        $roles = Role::all();
        $departments = Department::where('status', 'Active')->get();
        $jabatans = Position::where('status', 'Active')->get();
        $companies = \App\Models\Company::where('status', 'Active')->get();
        $branches = Branch::with('company')->where('status', 'Active')->get();
        $availableKaryawan = \App\Models\Karyawan::forActiveCompany()
            ->where('status', 'Aktif')
            ->whereDoesntHave('user')
            ->orderBy('nama_lengkap')
            ->get();

        return view('user.viewuser', compact('totalUsers', 'aktifUsers', 'nonaktifUsers', 'roles', 'departments', 'jabatans', 'companies', 'branches', 'availableKaryawan'));
    }

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
            1 => 'id',
            2 => 'kode_karyawan',
            3 => 'nama_lengkap',
            4 => 'username',
            5 => 'email',
            6 => 'status',
        ];
        $orderColumn = $columns[$orderCol] ?? 'id';

        $query = User::with(['companies', 'branches']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('kode_karyawan', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $totalData = User::count();
        $totalFiltered = $search === '' ? $totalData : $query->count();

        $rows = $query->orderBy($orderColumn, $orderDir)->skip($start)->take($length)->get();

        $data = $rows->map(function ($u, $index) use ($start) {
            $roleCanEdit = auth()->user()->can('edit-karyawan');
            $roleCanHapus = auth()->user()->can('hapus-karyawan');

            $userCompanies = $u->companies->pluck('nama_perusahaan')->toArray();
            $userBranches  = $u->branches->pluck('nama_cabang')->toArray();

            return [
                'no' => $start + $index + 1,
                'id' => $u->id,
                'foto' => $u->foto ? asset($u->foto) : ($u->jenis_kelamin == 'P' ? asset('gambar/perempuan.jpg') : asset('gambar/laki.jpg')),
                'kode_karyawan' => $u->kode_karyawan ?? '-',
                'nama_lengkap' => e($u->nama_lengkap),
                'inisial' => $u->inisial ?? null,
                'companies_list' => !empty($userCompanies) ? implode(', ', $userCompanies) : null,
                'branches_list'  => !empty($userBranches)  ? implode(', ', $userBranches)  : null,
                'username' => e($u->username),
                'email' => e($u->email),
                'status' => $u->status,
                'can_edit' => $roleCanEdit,
                'can_hapus' => $roleCanHapus,
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
        $request->validate([
            'karyawan_id' => 'nullable|exists:karyawans,id|unique:users,karyawan_id',
            'kode_karyawan' => 'nullable|string|max:4|unique:users,kode_karyawan',
            'nama_lengkap' => 'required_without:karyawan_id|nullable|string|max:50',
            'inisial' => 'required_without:karyawan_id|nullable|string|max:4',
            'username' => 'required|string|max:20|unique:users',
            'email' => 'required|email|max:50|unique:users',
            'password' => 'nullable|min:6|max:30',
            'role_id' => 'required|exists:roles,id',
            'departemen_id' => 'required_without:karyawan_id|nullable|exists:departments,id',
            'jabatan_id' => 'required_without:karyawan_id|nullable|exists:positions,id',
            'no_hp' => 'nullable|regex:/^[0-9]+$/|max:13',
            'jenis_kelamin' => 'required_without:karyawan_id|nullable|in:L,P',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'company_ids' => 'required|array|min:1',
            'company_ids.*' => 'exists:companies,id',
            'default_company_id' => 'required|exists:companies,id',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
            'default_branch_id' => 'nullable|exists:branches,id',
            'signature' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Jika dihubungkan dengan data Karyawan HRD
            $karyawan = null;
            if ($request->filled('karyawan_id')) {
                $karyawan = \App\Models\Karyawan::find($request->karyawan_id);
            }

            // Simpan File Foto jika di-upload, jika tidak dan ada Karyawan, gunakan foto Karyawan
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoFile = $request->file('foto');
                $fileName = time() . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                $fotoFile->move(public_path('uploads/karyawan'), $fileName);
                $fotoPath = 'uploads/karyawan/' . $fileName;
            } elseif ($karyawan) {
                $fotoPath = $karyawan->foto;
            }

            // 2. Simpan Data User
            $password = $request->filled('password') ? $request->password : 'Satriaerp828#';
            $kodeKaryawan = $karyawan ? $karyawan->kode_karyawan : $request->kode_karyawan;
            if (empty($kodeKaryawan)) {
                $kodeKaryawan = \App\Helpers\CodeGeneratorHelper::generate('users', 'U', 'kode_karyawan', 3);
            }

            $user = User::create([
                'karyawan_id' => $request->karyawan_id,
                'kode_karyawan' => $kodeKaryawan,
                'nama_lengkap' => $karyawan ? $karyawan->nama_lengkap : $request->nama_lengkap,
                'inisial' => $karyawan ? $karyawan->inisial : ($request->inisial ? strtoupper($request->inisial) : null),
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($password),
                'role_id' => $request->role_id,
                'departemen_id' => $karyawan ? $karyawan->departemen_id : $request->departemen_id,
                'jabatan_id' => $karyawan ? $karyawan->jabatan_id : $request->jabatan_id,
                'no_hp' => $karyawan ? $karyawan->no_hp : $request->no_hp,
                'jenis_kelamin' => $karyawan ? $karyawan->jenis_kelamin : $request->jenis_kelamin,
                'status' => $karyawan ? $karyawan->status : 'Aktif',
                'foto' => $fotoPath,
            ]);

            // 3. Assign Role Spatie (Wajib)
            $role = Role::findById($request->role_id);
            $user->assignRole($role->name);

            // 4. Sync Companies Access
            $syncCompanies = [];
            foreach ($request->company_ids as $cId) {
                $syncCompanies[$cId] = [
                    'is_default' => ($cId == $request->default_company_id)
                ];
            }
            $user->companies()->sync($syncCompanies);

            // 5. Sync Branches Access (if provided)
            $syncData = [];
            if ($request->filled('branch_ids') && is_array($request->branch_ids)) {
                $defaultBranchId = $request->default_branch_id ?? ($request->branch_ids[0] ?? null);
                foreach ($request->branch_ids as $bId) {
                    $syncData[$bId] = [
                        'is_default' => ($bId == $defaultBranchId)
                    ];
                }
            }
            $user->branches()->sync($syncData);

            // 5. Simpan Tanda Tangan Digital jika ada
            if ($request->filled('signature')) {
                $sigPath = $this->uploadBase64Signature($user->id, $request->signature);
                if ($sigPath) {
                    \App\Models\UserSignature::create([
                        'user_id' => $user->id,
                        'signature_path' => $sigPath,
                        'is_active' => true,
                        'created_by' => auth()->id()
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Data Karyawan berhasil disimpan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store karyawan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menyimpan karyawan.'], 500);
        }
    }

    public function edit(User $karyawan)
    {
        $this->authorize('edit-karyawan');
        $karyawan->load(['department', 'position', 'branches', 'companies', 'roles', 'activeSignature']);
        
        $roles = Role::all();
        $departments = Department::where('status', 'Active')->get();
        $jabatans = Position::where('status', 'Active')->get();
        $companies = \App\Models\Company::where('status', 'Active')->get();
        $branches = Branch::with('company')->where('status', 'Active')->get();
        $availableKaryawan = \App\Models\Karyawan::forActiveCompany()
            ->where('status', 'Aktif')
            ->where(function($query) use ($karyawan) {
                $query->whereDoesntHave('user')
                      ->orWhere('id', $karyawan->karyawan_id);
            })
            ->orderBy('nama_lengkap')
            ->get();
        
        // Dapatkan nama role
        $karyawan->role_name = $karyawan->roles->pluck('name')->first();
        
        // Dapatkan path tanda tangan aktif
        $activeSig = $karyawan->activeSignature;
        $karyawan->active_signature_path = $activeSig ? asset($activeSig->signature_path) : null;

        return response()->json([
            'user' => $karyawan,
            'roles' => $roles,
            'departments' => $departments,
            'jabatans' => $jabatans,
            'availableKaryawan' => $availableKaryawan,
            'companies' => $companies,
            'branches' => $branches
        ]);
    }

    public function update(Request $request, User $karyawan)
    {
        $this->authorize('edit-karyawan');
        $request->validate([
            'karyawan_id' => 'nullable|exists:karyawans,id|unique:users,karyawan_id,' . $karyawan->id,
            'kode_karyawan' => 'nullable|string|max:4|unique:users,kode_karyawan,' . $karyawan->id,
            'nama_lengkap' => 'required_without:karyawan_id|nullable|string|max:50',
            'inisial' => 'required_without:karyawan_id|nullable|string|max:4',
            'username' => 'required|string|max:20|unique:users,username,' . $karyawan->id,
            'email' => 'required|email|max:50|unique:users,email,' . $karyawan->id,
            'password' => 'nullable|min:6|max:30',
            'role_id' => 'required|exists:roles,id',
            'departemen_id' => 'required_without:karyawan_id|nullable|exists:departments,id',
            'jabatan_id' => 'required_without:karyawan_id|nullable|exists:positions,id',
            'no_hp' => 'nullable|regex:/^[0-9]+$/|max:13',
            'jenis_kelamin' => 'required_without:karyawan_id|nullable|in:L,P',
            'status' => 'required|in:Aktif,Nonaktif',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'company_ids' => 'required|array|min:1',
            'company_ids.*' => 'exists:companies,id',
            'default_company_id' => 'required|exists:companies,id',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
            'default_branch_id' => 'nullable|exists:branches,id',
            'signature' => 'nullable|string',
        ]);

        // Jika dihubungkan dengan data Karyawan HRD
        $emp = null;
        if ($request->filled('karyawan_id')) {
            $emp = \App\Models\Karyawan::find($request->karyawan_id);
        }

        $data = [
            'karyawan_id' => $request->karyawan_id,
            'kode_karyawan' => $emp ? $emp->kode_karyawan : $request->kode_karyawan,
            'nama_lengkap' => $emp ? $emp->nama_lengkap : $request->nama_lengkap,
            'inisial' => $emp ? $emp->inisial : ($request->inisial ? strtoupper($request->inisial) : null),
            'username' => $request->username,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'departemen_id' => $emp ? $emp->departemen_id : $request->departemen_id,
            'jabatan_id' => $emp ? $emp->jabatan_id : $request->jabatan_id,
            'no_hp' => $emp ? $emp->no_hp : $request->no_hp,
            'jenis_kelamin' => $emp ? $emp->jenis_kelamin : $request->jenis_kelamin,
            'status' => $request->status ?? ($emp ? $emp->status : 'Aktif'),
        ];

        try {
            DB::beginTransaction();

            // Update password hanya jika diisi
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }

            // Update foto jika ada file baru, atau gunakan foto Karyawan jika dihubungkan
            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($karyawan->foto && file_exists(public_path($karyawan->foto))) {
                    @unlink(public_path($karyawan->foto));
                }
                $fotoFile = $request->file('foto');
                $fileName = time() . '_' . uniqid() . '.' . $fotoFile->getClientOriginalExtension();
                $fotoFile->move(public_path('uploads/karyawan'), $fileName);
                $data['foto'] = 'uploads/karyawan/' . $fileName;
            } elseif ($emp) {
                $data['foto'] = $emp->foto;
            }

            $karyawan->update($data);

            // Update Role Spatie
            $role = Role::findById($request->role_id);
            $karyawan->syncRoles([$role->name]);

            // Sync Companies Access
            $syncCompanies = [];
            foreach ($request->company_ids as $cId) {
                $syncCompanies[$cId] = [
                    'is_default' => ($cId == $request->default_company_id)
                ];
            }
            $karyawan->companies()->sync($syncCompanies);

            // Sync Branches Access (if provided)
            $syncData = [];
            if ($request->filled('branch_ids') && is_array($request->branch_ids)) {
                $defaultBranchId = $request->default_branch_id ?? ($request->branch_ids[0] ?? null);
                foreach ($request->branch_ids as $bId) {
                    $syncData[$bId] = [
                        'is_default' => ($bId == $defaultBranchId)
                    ];
                }
            }
            $karyawan->branches()->sync($syncData);

            // Update Tanda Tangan jika ada yang baru digambar
            if ($request->filled('signature')) {
                $sigPath = $this->uploadBase64Signature($karyawan->id, $request->signature);
                if ($sigPath) {
                    // Nonaktifkan tanda tangan lama
                    \App\Models\UserSignature::where('user_id', $karyawan->id)->update(['is_active' => false]);
                    // Tambah tanda tangan aktif baru
                    \App\Models\UserSignature::create([
                        'user_id' => $karyawan->id,
                        'signature_path' => $sigPath,
                        'is_active' => true,
                        'created_by' => auth()->id()
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Data Karyawan berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update karyawan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mengupdate karyawan.'], 500);
        }
    }

    public function destroy(User $karyawan)
    {
        $this->authorize('hapus-karyawan');
        try {
            DB::beginTransaction();

            // Hapus foto jika ada
            if ($karyawan->foto && file_exists(public_path($karyawan->foto))) {
                unlink(public_path($karyawan->foto));
            }

            $karyawan->delete();

            DB::commit();
            return response()->json(['message' => 'Data Karyawan berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete karyawan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat menghapus karyawan.'], 500);
        }
    }

    public function exportExcel()
    {
        $this->authorize('lihat-karyawan');

        $users = User::with(['roles', 'department', 'position', 'branches'])
            ->orderBy('id', 'desc')
            ->cursor();

        $rows = [
            [
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Kode Karyawan</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Nama Lengkap</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Inisial</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Username</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Email</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Role</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Departemen</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Jabatan</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>No HP</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Jenis Kelamin</b></style><center>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Akses Cabang</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Cabang Default</b></style><left>',
                '<style bgcolor="#4f46e5" color="#ffffff"><b>Status</b></style><center>',
            ]
        ];

        foreach ($users as $u) {
            $statusStyle = $u->status === 'Aktif'
                ? '<style border="thin" color="#10b981"><b>Aktif</b></style><center>'
                : '<style border="thin" color="#6b7280"><b>Nonaktif</b></style><center>';

            $roleName = $u->roles->pluck('name')->first() ?? '';
            $deptName = $u->department->department_name ?? '';
            $jabatanName = $u->position->position_name ?? '';
            
            $branchNames = $u->branches->pluck('nama_cabang')->toArray();
            $aksesCabang = implode(', ', $branchNames);
            
            $defaultBranch = $u->branches->where('pivot.is_default', true)->first();
            $defaultBranchName = $defaultBranch ? $defaultBranch->nama_cabang : '';

            $rows[] = [
                '<style border="thin">' . ($u->kode_karyawan ?? '') . '</style><center>',
                '<style border="thin">' . ($u->nama_lengkap ?? '') . '</style><left>',
                '<style border="thin">' . ($u->inisial ?? '') . '</style><center>',
                '<style border="thin">' . ($u->username ?? '') . '</style><left>',
                '<style border="thin">' . ($u->email ?? '') . '</style><left>',
                '<style border="thin">' . $roleName . '</style><left>',
                '<style border="thin">' . $deptName . '</style><left>',
                '<style border="thin">' . $jabatanName . '</style><left>',
                '<style border="thin">' . ($u->no_hp ?? '') . '</style><center>',
                '<style border="thin">' . ($u->jenis_kelamin ?? '') . '</style><center>',
                '<style border="thin">' . $aksesCabang . '</style><left>',
                '<style border="thin">' . $defaultBranchName . '</style><left>',
                $statusStyle,
            ];
        }

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'data_user_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadTemplate()
    {
        $this->authorize('lihat-karyawan');

        $rows = [
            [
                'Kode Karyawan',
                'Nama Lengkap',
                'Inisial',
                'Username',
                'Email',
                'Role',
                'Departemen',
                'Jabatan',
                'No HP',
                'Jenis Kelamin',
                'Akses Cabang',
                'Cabang Default',
                'Status'
            ],
            [
                'U001',
                'Satria Administrator',
                'SA',
                'satria_admin',
                'satria@perusahaan.com',
                'Super Admin',
                'Human Resource',
                'Manager IT',
                '081234567890',
                'L',
                'HO Bandung, Cabang Jakarta',
                'HO Bandung',
                'Aktif'
            ],
            [
                'U002',
                'Dewi Lestari',
                'DL',
                'dewi_l',
                'dewi@perusahaan.com',
                'Staff',
                'Finansial',
                'Staff Keuangan',
                '087712345678',
                'P',
                'Cabang Jakarta',
                'Cabang Jakarta',
                'Aktif'
            ],
        ];

        return response()->streamDownload(function () use ($rows) {
            SimpleXLSXGen::fromArray($rows)->saveAs('php://output');
        }, 'template_import_user.xlsx', [
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

        try {
            DB::beginTransaction();

            $rolesMap = DB::table('roles')
                ->pluck('id', DB::raw('LOWER(name)'))
                ->toArray();

            $departmentsMap = DB::table('departments')
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(department_name)'))
                ->toArray();

            $positionsMap = DB::table('positions')
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(position_name)'))
                ->toArray();

            $branchesMap = DB::table('branches')
                ->whereNull('deleted_at')
                ->pluck('id', DB::raw('LOWER(nama_cabang)'))
                ->toArray();

            $processedCodes = [];
            $processedUsernames = [];
            $processedEmails = [];

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue;

                $kodeKaryawan = isset($row[0]) ? trim($row[0]) : null;
                $namaLengkap = isset($row[1]) ? trim($row[1]) : '';
                $inisial = isset($row[2]) ? strtoupper(trim($row[2])) : null;
                $username = isset($row[3]) ? trim($row[3]) : '';
                $email = isset($row[4]) ? trim($row[4]) : '';
                $roleName = isset($row[5]) ? trim($row[5]) : '';
                $deptName = isset($row[6]) ? trim($row[6]) : '';
                $jabatanName = isset($row[7]) ? trim($row[7]) : '';
                $noHp = isset($row[8]) ? trim($row[8]) : null;
                $jenisKelamin = isset($row[9]) ? strtoupper(trim($row[9])) : null;
                $aksesCabangRaw = isset($row[10]) ? trim($row[10]) : '';
                $cabangDefaultRaw = isset($row[11]) ? trim($row[11]) : '';
                $status = isset($row[12]) ? trim($row[12]) : 'Aktif';
                
                $rowNum = $i + 1;

                if (empty($namaLengkap)) {
                    throw new \Exception("Baris {$rowNum}: Nama Lengkap wajib diisi.");
                }
                if (empty($username)) {
                    throw new \Exception("Baris {$rowNum}: Username wajib diisi.");
                }
                if (empty($email)) {
                    throw new \Exception("Baris {$rowNum}: Email wajib diisi.");
                }
                if (empty($roleName)) {
                    throw new \Exception("Baris {$rowNum}: Role wajib diisi.");
                }
                if (empty($aksesCabangRaw)) {
                    throw new \Exception("Baris {$rowNum}: Akses Cabang wajib diisi.");
                }
                if (empty($cabangDefaultRaw)) {
                    throw new \Exception("Baris {$rowNum}: Cabang Default wajib diisi.");
                }

                if (empty($kodeKaryawan)) {
                    $generatedCode = \App\Helpers\CodeGeneratorHelper::generate('users', 'U', 'kode_karyawan', 3);
                    $maxSuffix = 0;
                    if (preg_match('/^U(\d+)$/', $generatedCode, $m)) {
                        $maxSuffix = (int)$m[1];
                    }
                    while (
                        in_array(strtolower($generatedCode), $processedCodes) ||
                        DB::table('users')->where('kode_karyawan', $generatedCode)->exists()
                    ) {
                        $maxSuffix++;
                        $generatedCode = 'U' . str_pad($maxSuffix, 3, '0', STR_PAD_LEFT);
                    }
                    $kodeKaryawan = $generatedCode;
                } else {
                    if (DB::table('users')->where('kode_karyawan', $kodeKaryawan)->exists()) {
                        throw new \Exception("Baris {$rowNum}: Kode Karyawan '{$kodeKaryawan}' sudah terdaftar.");
                    }
                    if (in_array(strtolower($kodeKaryawan), $processedCodes)) {
                        throw new \Exception("Baris {$rowNum}: Kode Karyawan '{$kodeKaryawan}' ganda di dalam file.");
                    }
                }
                $processedCodes[] = strtolower($kodeKaryawan);

                if (DB::table('users')->where('username', $username)->exists()) {
                    throw new \Exception("Baris {$rowNum}: Username '{$username}' sudah terdaftar.");
                }
                if (in_array(strtolower($username), $processedUsernames)) {
                    throw new \Exception("Baris {$rowNum}: Username '{$username}' ganda di dalam file.");
                }
                $processedUsernames[] = strtolower($username);

                if (DB::table('users')->where('email', $email)->exists()) {
                    throw new \Exception("Baris {$rowNum}: Email '{$email}' sudah terdaftar.");
                }
                if (in_array(strtolower($email), $processedEmails)) {
                    throw new \Exception("Baris {$rowNum}: Email '{$email}' ganda di dalam file.");
                }
                $processedEmails[] = strtolower($email);

                $roleKey = strtolower($roleName);
                if (!isset($rolesMap[$roleKey])) {
                    throw new \Exception("Baris {$rowNum}: Role '{$roleName}' tidak ditemukan.");
                }
                $roleId = $rolesMap[$roleKey];

                $deptId = null;
                if (!empty($deptName)) {
                    $deptKey = strtolower($deptName);
                    if (!isset($departmentsMap[$deptKey])) {
                        throw new \Exception("Baris {$rowNum}: Departemen '{$deptName}' tidak ditemukan.");
                    }
                    $deptId = $departmentsMap[$deptKey];
                }

                $jabatanId = null;
                if (!empty($jabatanName)) {
                    $jabatanKey = strtolower($jabatanName);
                    if (!isset($positionsMap[$jabatanKey])) {
                        throw new \Exception("Baris {$rowNum}: Jabatan '{$jabatanName}' tidak ditemukan.");
                    }
                    $jabatanId = $positionsMap[$jabatanKey];
                }

                $cabangNames = array_map('trim', explode(',', $aksesCabangRaw));
                $branchIds = [];
                foreach ($cabangNames as $cName) {
                    $cKey = strtolower($cName);
                    if (!isset($branchesMap[$cKey])) {
                        throw new \Exception("Baris {$rowNum}: Cabang '{$cName}' tidak ditemukan.");
                    }
                    $branchIds[] = $branchesMap[$cKey];
                }

                $defKey = strtolower($cabangDefaultRaw);
                if (!isset($branchesMap[$defKey])) {
                    throw new \Exception("Baris {$rowNum}: Cabang Default '{$cabangDefaultRaw}' tidak ditemukan.");
                }
                $defaultBranchId = $branchesMap[$defKey];

                if (!in_array($defaultBranchId, $branchIds)) {
                    throw new \Exception("Baris {$rowNum}: Cabang Default '{$cabangDefaultRaw}' harus termasuk dalam Akses Cabang.");
                }

                if (!in_array($status, ['Aktif', 'Nonaktif'])) {
                    $status = 'Aktif';
                }

                $user = User::create([
                    'kode_karyawan' => $kodeKaryawan,
                    'nama_lengkap' => $namaLengkap,
                    'inisial' => $inisial,
                    'username' => $username,
                    'email' => $email,
                    'password' => bcrypt('Satriaerp828#'),
                    'role_id' => $roleId,
                    'departemen_id' => $deptId,
                    'jabatan_id' => $jabatanId,
                    'no_hp' => $noHp,
                    'jenis_kelamin' => $jenisKelamin,
                    'status' => $status,
                ]);

                $role = \Spatie\Permission\Models\Role::findById($roleId);
                $user->assignRole($role->name);

                $syncData = [];
                foreach ($branchIds as $bId) {
                    $syncData[$bId] = [
                        'is_default' => ($bId == $defaultBranchId)
                    ];
                }
                $user->branches()->sync($syncData);
            }

            DB::commit();
            return response()->json(['message' => 'Data User berhasil diimport!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error import user: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal Import: ' . $e->getMessage()], 500);
        }
    }

    public function resetPassword(User $karyawan)
    {
        $this->authorize('edit-karyawan');
        
        try {
            $defaultPassword = 'Satriaerp828#';
            $karyawan->update([
                'password' => bcrypt($defaultPassword)
            ]);
            
            // Force other devices session logout
            \Illuminate\Support\Facades\Cache::forget('user-session-' . $karyawan->id);
            
            return response()->json([
                'message' => 'Password untuk <b>' . e($karyawan->nama_lengkap) . '</b> berhasil di-reset ke password default.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error reset password karyawan: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan sistem saat mereset password.'], 500);
        }
    }

    /**
     * Helper: Decode base64 gambar signature dan simpan ke file lokal.
     */
    private function uploadBase64Signature($userId, $base64String)
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
                $base64String = substr($base64String, strpos($base64String, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['png', 'jpg', 'jpeg'])) {
                    return null;
                }

                $data = base64_decode($base64String);
                if ($data === false) {
                    return null;
                }

                $folder = public_path('uploads/signatures');
                if (!file_exists($folder)) {
                    mkdir($folder, 0755, true);
                }

                $fileName = 'sig_' . $userId . '_' . time() . '_' . uniqid() . '.' . $type;
                $filePath = $folder . '/' . $fileName;
                file_put_contents($filePath, $data);

                return 'uploads/signatures/' . $fileName;
            }
        } catch (\Exception $e) {
            Log::error('Error upload base64 signature helper: ' . $e->getMessage());
        }
        return null;
    }
}
