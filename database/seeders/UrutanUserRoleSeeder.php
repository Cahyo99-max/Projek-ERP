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
            'Director Produksi',
            'Director Accounting',
            'Director Sales',
            'Admin',
            'Purchasing',
            'Manager Purchasing',
            'Warehouse',
            'Supervisor Warehouse',
            'Staf Gudang',
            'Produksi',
            'Manager Produksi',
            'QC',
            'Accounting',
            'Finance',
            'Manager Finance',
            'HRD',
            'Sales',
            'Manager Sales',
            'Keuangan',
            'PIC Helm',
            'PIC Garmen',
            'Ruang Meeting',
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

        $user = User::where('username', 'superadmin')->orWhere('email', 'Pastacerp828.pas@gmail.com')->orWhere('email', 'admin@gmail.com')->first();
        if (!$user) {
            $user = User::create([
                'kode_karyawan' => 'SA-001',
                'nama_lengkap'  => 'Pastactical828',
                'username'      => 'superadmin',      // <-- Buat login via username
                'email'         => 'Pastacerp828.pas@gmail.com', // <-- Buat login via email
                'password'      => Hash::make('Pastacerp828#*'),
                'status'        => 'Aktif',
            ]);
        } else {
            $user->update([
                'nama_lengkap' => 'Pastactical828',
                'email'        => 'Pastacerp828.pas@gmail.com',
                'password'     => Hash::make('Pastacerp828#*'),
            ]);
        }

        // Pasangkan role Super Admin ke user ini
        $user->assignRole($superAdminRole);
    }
}
