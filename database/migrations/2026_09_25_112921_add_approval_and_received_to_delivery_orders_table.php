<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah tipe status menjadi VARCHAR(50) agar fleksibel mendukung alur approval
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE delivery_orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Menunggu Persetujuan'");

        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->foreignId('received_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable()->after('received_by');
            $table->text('catatan_approval')->nullable()->after('received_at');
            $table->text('catatan_penerimaan')->nullable()->after('catatan_approval');
        });

        // Buat permission approve-delivery-order jika belum ada
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'approve-delivery-order']);
            $roles = \Spatie\Permission\Models\Role::whereIn('name', ['Super Admin', 'Owner', 'Director', 'Supervisor Warehouse'])->get();
            foreach ($roles as $role) {
                if (!$role->hasPermissionTo('approve-delivery-order')) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'received_by', 'received_at', 'catatan_approval', 'catatan_penerimaan']);
        });

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE delivery_orders MODIFY COLUMN status ENUM('Draft', 'Siap Dikirim', 'Dalam Perjalanan', 'Terkirim', 'Dibatalkan') NOT NULL DEFAULT 'Draft'");
    }
};
