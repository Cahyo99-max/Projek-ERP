<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UrutanUserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset Cache Spatie biar gak bentrok
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Permission (Fitur Master Dasar)
        // Nanti ini yang bakal muncul di samping checkbox Admin
        $permissions = [
            'akses-data-master',
            'akses-gudang',
            'akses-hrd',
            'akses-purchasing',
            'akses-produksi',
            'akses-qc',
            'akses-accounting',
            'akses-sales',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 3. Buat Daftar Role Sesuai Request Kamu + Ajuan Gua
        $roles = [
            'Super Admin',
            'Owner',
            'Director',
            'Admin',
            'Purchasing',
            'Warehouse',
            'Supervisor Warehouse',
            'Produksi',
            'Manager Produksi',
            'QC',
            'Accounting',
            'Finance',
            'Manager Finance',
            'HRD',
            'Sales',
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            // Kalau perannya Super Admin atau Owner, otomatis kita kasih SEMUA permission
            if ($roleName === 'Super Admin' || $roleName === 'Owner') {
                $role->givePermissionTo(Permission::all());
            }
        }

        // 4. Buat Akun Master Super Admin untuk Testing Login
        $superAdminRole = Role::findByName('Super Admin');

        $user = User::where('username', 'superadmin')->orWhere('email', 'admin@gmail.com')->first();
        if (!$user) {
            $user = User::create([
                'kode_karyawan' => 'SA-001',
                'nama_lengkap' => 'Soni Super Admin',
                'username' => 'superadmin',      // <-- Buat login via username
                'email' => 'admin@gmail.com', // <-- Buat login via email
                'password' => Hash::make('password123'),
                'status' => 'Aktif',
            ]);
        }

        // Pasangkan role Super Admin ke user ini
        $user->assignRole($superAdminRole);
    }
}
