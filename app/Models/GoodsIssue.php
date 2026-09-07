<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsIssue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'goods_issues';

    protected $fillable = [
        'nomor_gi',
        'tanggal_gi',
        'gudang_id',
        'company_id',
        'branch_id',
        'tujuan',
        'tipe_pengeluaran',
        'keterangan',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_gi'  => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
            if (empty($model->branch_id) && session()->has('active_branch_id')) {
                $model->branch_id = session('active_branch_id');
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

    // ── Relationships ──────────────────────────────────────────────────────
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(GoodsIssueDetail::class, 'gi_id');
    }

    public function jurnal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Goods Issue');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        return $query->whereHas('gudang', function ($q) use ($activeCompanyId) {
            if (!empty($activeCompanyId)) {
                $q->where('company_id', $activeCompanyId);
            }
        });
    }

    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        if (!empty($activeBranchId)) {
            return $query->where('branch_id', $activeBranchId);
        }
        return $query->whereNull('branch_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    /**
     * Returns true if the GI is in Draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    /**
     * Returns true if the GI is in Pending Approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    /**
     * Returns true if the GI is Confirmed (Stock Deducted & Journal Posted).
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'Confirmed';
    }

    /**
     * Returns true if the GI is still editable by users with edit-gi permission.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['Draft', 'Pending']);
    }

    /**
     * Returns true if the GI is deletable by users with hapus-gi permission.
     */
    public function isDeletable(): bool
    {
        return $this->status === 'Draft';
    }
}
