<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_adjustments';

    protected $fillable = [
        'nomor_adjustment',
        'tanggal_adjustment',
        'gudang_id',
        'company_id',
        'branch_id',
        'tipe_adjustment',
        'kategori_alasan',
        'keterangan',
        'status',
        'total_qty',
        'total_nilai',
        'disetujui_oleh',
        'disetujui_pada',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_adjustment' => 'date',
        'disetujui_pada'     => 'datetime',
        'total_qty'          => 'decimal:2',
        'total_nilai'        => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }

            $activeBranchId  = session('active_branch_id');
            $activeCompanyId = session('active_company_id');

            // Strict Isolation: Jika di Cabang maka company_id = NULL, jika di PT maka branch_id = NULL
            if (!empty($activeBranchId) || !empty($model->branch_id)) {
                $model->branch_id  = $model->branch_id ?: $activeBranchId;
                $model->company_id = null;
            } else {
                $model->company_id = $model->company_id ?: ($activeCompanyId ?: (auth()->user()?->company_id ?? 1));
                $model->branch_id  = null;
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

    /**
     * Scope Filter Active Company (Strict Isolation: PT Pusat vs Cabang)
     */
    public function scopeForActiveCompany($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            // Ketika sesi aktif adalah Cabang: company_id = NULL
            return $query->whereNull('stock_adjustments.company_id');
        }

        $companyId = session('active_company_id') ?? auth()->user()?->company_id;
        if (!empty($companyId)) {
            return $query->where('stock_adjustments.company_id', $companyId);
        }
        return $query;
    }

    /**
     * Scope Filter Active Branch (Strict Isolation: PT Pusat vs Cabang)
     */
    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            return $query->where('stock_adjustments.branch_id', $branchId);
        }
        return $query->whereNull('stock_adjustments.branch_id');
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

    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }

    // ── Relations ─────────────────────────────────────────
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_id');
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockAdjustmentDetail::class, 'stock_adjustment_id');
    }
}
