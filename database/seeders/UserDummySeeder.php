<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dummyUsers = [
            [
                'kode_karyawan' => 'SA-001',
                'nama_lengkap'  => 'Soni Super Admin',
                'username'      => 'superadmin',
                'email'         => 'superadmin@gmail.com',
                'password'      => 'password123',
                'role'          => 'Super Admin',
            ],
            [
                'kode_karyawan' => 'OWN-001',
                'nama_lengkap'  => 'Budi Owner Utama',
                'username'      => 'owner',
                'email'         => 'owner@gmail.com',
                'password'      => 'password123',
                'role'          => 'Owner',
            ],
            [
                'kode_karyawan' => 'GDG-001',
                'nama_lengkap'  => 'Ahmad Kepala Gudang',
                'username'      => 'gudang',
                'email'         => 'gudang@gmail.com',
                'password'      => 'password123',
                'role'          => 'Supervisor Warehouse',
            ],
            [
                'kode_karyawan' => 'GDG-002',
                'nama_lengkap'  => 'Bambang Staff Gudang',
                'username'      => 'staffgudang',
                'email'         => 'staffgudang@gmail.com',
                'password'      => 'password123',
                'role'          => 'Warehouse',
            ],
            [
                'kode_karyawan' => 'PUR-001',
                'nama_lengkap'  => 'Citra Purchasing Manager',
                'username'      => 'purchasing',
                'email'         => 'purchasing@gmail.com',
                'password'      => 'password123',
                'role'          => 'Purchasing',
            ],
            [
                'kode_karyawan' => 'ACC-001',
                'nama_lengkap'  => 'Dedi Finance Accounting',
                'username'      => 'accounting',
                'email'         => 'accounting@gmail.com',
                'password'      => 'password123',
                'role'          => 'Accounting',
            ],
            [
                'kode_karyawan' => 'HRD-001',
                'nama_lengkap'  => 'Eka Manager HRD',
                'username'      => 'hrd',
                'email'         => 'hrd@gmail.com',
                'password'      => 'password123',
                'role'          => 'HRD',
            ],
            [
                'kode_karyawan' => 'SLS-001',
                'nama_lengkap'  => 'Fajar Sales Executive',
                'username'      => 'sales',
                'email'         => 'sales@gmail.com',
                'password'      => 'password123',
                'role'          => 'Sales',
            ],
            [
                'kode_karyawan' => 'PRD-001',
                'nama_lengkap'  => 'Gilang Supervisor Produksi',
                'username'      => 'produksi',
                'email'         => 'produksi@gmail.com',
                'password'      => 'password123',
                'role'          => 'Produksi',
            ],
        ];

        $companies = Company::pluck('id')->toArray();
        if (empty($companies)) {
            $companyId = DB::table('companies')->insertGetId([
                'kode_perusahaan' => 'PT-SATRIA',
                'nama_perusahaan' => 'PT. SATRIA KARYA UTAMA',
                'status'          => 'Active',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            $companies = [$companyId];
        }

        foreach ($dummyUsers as $data) {
            $user = User::where('username', $data['username'])
                ->orWhere('email', $data['email'])
                ->first();

            if (!$user) {
                $user = User::create([
                    'kode_karyawan' => $data['kode_karyawan'],
                    'nama_lengkap'  => $data['nama_lengkap'],
                    'username'      => $data['username'],
                    'email'         => $data['email'],
                    'password'      => Hash::make($data['password']),
                    'status'        => 'Aktif',
                ]);
            } else {
                $user->update([
                    'kode_karyawan' => $data['kode_karyawan'],
                    'nama_lengkap'  => $data['nama_lengkap'],
                    'password'      => Hash::make($data['password']),
                    'status'        => 'Aktif',
                ]);
            }

            // Assign Spatie Role
            $roleObj = Role::where('name', $data['role'])->first();
            if ($roleObj) {
                $user->syncRoles([$roleObj->name]);
            }

            // Assign User Companies
            foreach ($companies as $idx => $compVal) {
                DB::table('user_companies')->updateOrInsert(
                    ['user_id' => $user->id, 'company_id' => $compVal],
                    ['is_default' => ($idx === 0 ? 1 : 0), 'updated_at' => now()]
                );
            }
        }
    }
}
