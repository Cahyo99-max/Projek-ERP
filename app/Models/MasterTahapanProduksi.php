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
     * Scope: filter berdasarkan company aktif di session.
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where(function ($q) use ($activeCompanyId) {
                $q->where('master_tahapan_produksi.company_id', $activeCompanyId)
                  ->orWhereNull('master_tahapan_produksi.company_id');
            });
        }
        return $query;
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
