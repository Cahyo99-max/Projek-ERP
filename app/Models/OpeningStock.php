<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpeningStock extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'opening_stocks';

    protected $fillable = [
        'company_id',
        'nomor_opening_stock',
        'tanggal_opening_stock',
        'gudang_id',
        'keterangan',
        'status',
        'branch_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_opening_stock' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (!$model->company_id && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
            if (!$model->branch_id && session()->has('active_branch_id')) {
                $model->branch_id = session('active_branch_id');
            }
            // Auto fallback from Gudang if branch_id or company_id is missing
            if ($model->gudang_id && (!$model->branch_id || !$model->company_id)) {
                $gudang = MasterGudang::find($model->gudang_id);
                if ($gudang) {
                    if (!$model->branch_id) $model->branch_id = $gudang->cabang_id ?: 1;
                    if (!$model->company_id) $model->company_id = $gudang->company_id ?: 1;
                }
            }
            if (!$model->branch_id) {
                $model->branch_id = 1;
            }
            if (!$model->company_id) {
                $model->company_id = 1;
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_id');
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OpeningStockDetail::class, 'opening_stock_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'Confirmed';
    }
}
