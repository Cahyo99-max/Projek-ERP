<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PettyCashKategoriMapping extends Model
{
    use HasFactory;

    protected $table = 'petty_cash_kategori_mappings';

    protected $fillable = [
        'company_id',
        'nama_kategori',
        'coa_id',
        'icon',
        'warna_badge',
        'deskripsi',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
            if (empty($model->company_id)) {
                $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? null);
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────────────────
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);
        if ($activeCompanyId) {
            return $query->where(function ($q) use ($activeCompanyId) {
                $q->where($this->getTable() . '.company_id', $activeCompanyId)
                  ->orWhereNull($this->getTable() . '.company_id');
            });
        }
        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
