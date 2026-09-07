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
        // Create opening_stocks
        Schema::create('opening_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_opening_stock')->unique();
            $table->date('tanggal_opening_stock');
            $table->unsignedBigInteger('gudang_id');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['Draft', 'Confirmed'])->default('Draft');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('gudang_id')->references('id')->on('master_gudang');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

        // Create opening_stock_details
        Schema::create('opening_stock_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('opening_stock_id');
            $table->unsignedBigInteger('barang_id');
            $table->decimal('jumlah', 15, 2);
            $table->decimal('harga_pokok', 15, 2); // Initial HPP per unit
            $table->timestamps();

            $table->foreign('opening_stock_id')->references('id')->on('opening_stocks')->onDelete('cascade');
            $table->foreign('barang_id')->references('id')->on('master_barang');
        });

        // Register spatie permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = ['lihat-opening-stock', 'tambah-opening-stock', 'konfirmasi-opening-stock', 'hapus-opening-stock'];
        $createdPermissions = [];
        foreach ($permissions as $name) {
            $createdPermissions[] = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $rolesToAssign = ['Super Admin', 'Owner', 'Admin', 'Warehouse', 'Supervisor Warehouse'];
        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($createdPermissions as $perm) {
                    $role->givePermissionTo($perm);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_stock_details');
        Schema::dropIfExists('opening_stocks');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = ['lihat-opening-stock', 'tambah-opening-stock', 'konfirmasi-opening-stock', 'hapus-opening-stock'];
        foreach ($permissions as $name) {
            $perm = Permission::where('name', $name)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
