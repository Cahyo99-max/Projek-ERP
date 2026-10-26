<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
            if (!Schema::hasColumn('spk_produksi', 'is_fg_closed')) {
                $table->boolean('is_fg_closed')->default(false)->after('is_hpp_closed');
            }
            if (!Schema::hasColumn('spk_produksi', 'fg_closing_status')) {
                $table->string('fg_closing_status', 30)->default('Open')->after('is_fg_closed');
            }
            if (!Schema::hasColumn('spk_produksi', 'fg_closing_requested_at')) {
                $table->dateTime('fg_closing_requested_at')->nullable()->after('fg_closing_status');
            }
            if (!Schema::hasColumn('spk_produksi', 'fg_closing_requested_by')) {
                $table->foreignId('fg_closing_requested_by')->nullable()->constrained('users')->nullOnDelete()->after('fg_closing_requested_at');
            }
            if (!Schema::hasColumn('spk_produksi', 'fg_closing_note')) {
                $table->text('fg_closing_note')->nullable()->after('fg_closing_requested_by');
            }
            if (!Schema::hasColumn('spk_produksi', 'fg_closing_reject_reason')) {
                $table->text('fg_closing_reject_reason')->nullable()->after('fg_closing_note');
            }
            if (!Schema::hasColumn('spk_produksi', 'fg_closing_rejected_at')) {
                $table->dateTime('fg_closing_rejected_at')->nullable()->after('fg_closing_reject_reason');
            }
            if (!Schema::hasColumn('spk_produksi', 'fg_closing_rejected_by')) {
                $table->foreignId('fg_closing_rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('fg_closing_rejected_at');
            }
            if (!Schema::hasColumn('spk_produksi', 'fg_closed_at')) {
                $table->dateTime('fg_closed_at')->nullable()->after('fg_closing_rejected_by');
            }
            if (!Schema::hasColumn('spk_produksi', 'fg_closed_by')) {
                $table->foreignId('fg_closed_by')->nullable()->constrained('users')->nullOnDelete()->after('fg_closed_at');
            }
        });

        // Register Spatie permissions
        $permissions = [
            'ajukan-closing-penerimaan-barang-jadi',
            'batal-ajukan-closing-penerimaan-barang-jadi',
            'closing-penerimaan-barang-jadi',
            'tolak-closing-penerimaan-barang-jadi',
            'buka-closing-penerimaan-barang-jadi',
            'lihat-hpp-penerimaan-barang-jadi',
            'lihat-jurnal-penerimaan-barang-jadi',
        ];

        $roles = Role::whereIn('name', ['Super Admin', 'Owner'])->get();

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);

            foreach ($roles as $role) {
                $role->givePermissionTo($perm);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spk_produksi', function (Blueprint $table) {
            $columns = [
                'is_fg_closed',
                'fg_closing_status',
                'fg_closing_requested_at',
                'fg_closing_requested_by',
                'fg_closing_note',
                'fg_closing_reject_reason',
                'fg_closing_rejected_at',
                'fg_closing_rejected_by',
                'fg_closed_at',
                'fg_closed_by',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('spk_produksi', $col)) {
                    if (str_ends_with($col, '_by')) {
                        $table->dropForeign(['spk_produksi_' . $col . '_foreign']);
                    }
                    $table->dropColumn($col);
                }
            }
        });

        $permissions = [
            'ajukan-closing-penerimaan-barang-jadi',
            'batal-ajukan-closing-penerimaan-barang-jadi',
            'closing-penerimaan-barang-jadi',
            'tolak-closing-penerimaan-barang-jadi',
            'buka-closing-penerimaan-barang-jadi',
            'lihat-hpp-penerimaan-barang-jadi',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
