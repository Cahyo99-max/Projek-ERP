<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterTahapanProduksi extends Model
{
    protected $table = 'master_tahapan_produksi';

    protected $fillable = [
        'company_id',
        'branch_id',
        'bom_id',
        'tipe_spk',
        'urutan',
        'nama_tahapan',
        'deskripsi',
        'foto',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'urutan'     => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'foto_url',
    ];

    /**
     * Booted Model Event: Otomatis hapus file fisik foto dari storage saat tahapan dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function ($tahapan) {
            if (!empty($tahapan->foto) && \Illuminate\Support\Facades\Storage::disk('public')->exists($tahapan->foto)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($tahapan->foto);
            }
        });
    }

    /**
     * Accessor URL Foto Tahapan
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return null;
    }

    /**
     * Scope: Filter berdasarkan active company + pisah PT Pusat vs Cabang.
     * - Jika input/sesi Cabang (active_branch_id ada)  → branch_id = active_branch_id & company_id (PT) NULL.
     * - Jika input/sesi PT Pusat (active_branch_id null) → branch_id (Cabang) NULL & company_id = active_company_id.
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            $query->where($table . '.branch_id', $activeBranchId)
                  ->whereNull($table . '.company_id');
        } else {
            $query->whereNull($table . '.branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($table . '.company_id', $activeCompanyId);
            }
        }

        return $query;
    }

    /**
     * Scope: Filter berdasarkan active branch.
     */
    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        $table = $this->getTable();
        return !empty($activeBranchId)
            ? $query->where($table . '.branch_id', $activeBranchId)->whereNull($table . '.company_id')
            : $query->whereNull($table . '.branch_id');
    }

    /**
     * Scope: hanya yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Relations ───────────────────────────────────────────────

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
