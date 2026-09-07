<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tambah kolom approval ke tabel purchase_payments ──
        Schema::table('purchase_payments', function (Blueprint $table) {
            // Extend status column — tambah Diajukan & Ditolak
            // MySQL ENUM tidak bisa di-alter mudah, pakai string (VARCHAR) lebih fleksibel
            $table->string('status', 30)->default('Draft')->change();

            // Kolom approval tracking
            $table->unsignedBigInteger('submitted_by')->nullable()->after('cancelled_at');
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('submitted_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');

            // Foreign keys
            $table->foreign('submitted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── 2. Tambah permissions approval ──
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $newPermissions = [
            'ajukan-pembayaran-utang',
            'approve-pembayaran-utang',
            'tolak-pembayaran-utang',
        ];

        $created = [];
        foreach ($newPermissions as $permName) {
            $created[] = Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web',
            ]);
        }

        // Hanya Super Admin & Owner yang otomatis dapat permission approval
        $rootRoles = ['Super Admin', 'Owner'];
        foreach ($rootRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($created as $perm) {
                    $role->givePermissionTo($perm);
                }
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['submitted_by', 'submitted_at', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        foreach (['ajukan-pembayaran-utang', 'approve-pembayaran-utang', 'tolak-pembayaran-utang'] as $permName) {
            Permission::where('name', $permName)->delete();
        }
    }
};
