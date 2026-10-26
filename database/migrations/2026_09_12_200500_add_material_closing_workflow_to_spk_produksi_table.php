<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('spk_produksi', function (Blueprint $table) {
            if (!Schema::hasColumn('spk_produksi', 'material_closing_status')) {
                $table->string('material_closing_status', 30)->default('Open')->after('is_material_closed');
            }
            if (!Schema::hasColumn('spk_produksi', 'material_closing_requested_at')) {
                $table->dateTime('material_closing_requested_at')->nullable()->after('material_closing_status');
            }
            if (!Schema::hasColumn('spk_produksi', 'material_closing_requested_by')) {
                $table->unsignedBigInteger('material_closing_requested_by')->nullable()->after('material_closing_requested_at');
                $table->foreign('material_closing_requested_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('spk_produksi', 'material_closing_note')) {
                $table->text('material_closing_note')->nullable()->after('material_closing_requested_by');
            }
            if (!Schema::hasColumn('spk_produksi', 'material_closing_reject_reason')) {
                $table->text('material_closing_reject_reason')->nullable()->after('material_closing_note');
            }
            if (!Schema::hasColumn('spk_produksi', 'material_closing_rejected_at')) {
                $table->dateTime('material_closing_rejected_at')->nullable()->after('material_closing_reject_reason');
            }
            if (!Schema::hasColumn('spk_produksi', 'material_closing_rejected_by')) {
                $table->unsignedBigInteger('material_closing_rejected_by')->nullable()->after('material_closing_rejected_at');
                $table->foreign('material_closing_rejected_by')->references('id')->on('users')->nullOnDelete();
            }
        });

        // Sync existing data: Jika is_material_closed = 1, set status = 'Closed'
        DB::table('spk_produksi')
            ->where('is_material_closed', true)
            ->update(['material_closing_status' => 'Closed']);

        // Register Permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'ajukan-closing-pengeluaran-bahan',
            'closing-pengeluaran-bahan',
            'tolak-closing-pengeluaran-bahan',
            'buka-closing-pengeluaran-bahan',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Berikan secara otomatis ke Super Admin dan Owner
        $roles = Role::whereIn('name', ['Super Admin', 'Owner'])->get();
        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_produksi', function (Blueprint $table) {
            if (Schema::hasColumn('spk_produksi', 'material_closing_rejected_by')) {
                $table->dropForeign(['material_closing_rejected_by']);
                $table->dropColumn('material_closing_rejected_by');
            }
            if (Schema::hasColumn('spk_produksi', 'material_closing_rejected_at')) {
                $table->dropColumn('material_closing_rejected_at');
            }
            if (Schema::hasColumn('spk_produksi', 'material_closing_reject_reason')) {
                $table->dropColumn('material_closing_reject_reason');
            }
            if (Schema::hasColumn('spk_produksi', 'material_closing_note')) {
                $table->dropColumn('material_closing_note');
            }
            if (Schema::hasColumn('spk_produksi', 'material_closing_requested_by')) {
                $table->dropForeign(['material_closing_requested_by']);
                $table->dropColumn('material_closing_requested_by');
            }
            if (Schema::hasColumn('spk_produksi', 'material_closing_requested_at')) {
                $table->dropColumn('material_closing_requested_at');
            }
            if (Schema::hasColumn('spk_produksi', 'material_closing_status')) {
                $table->dropColumn('material_closing_status');
            }
        });
    }
};
