<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_opnames';

    protected $fillable = [
        'nomor_opname',
        'tanggal_opname',
        'tanggal_mulai',
        'tanggal_selesai',
        'gudang_id',
        'company_id',
        'branch_id',
        'jenis_opname',
        'keterangan',
        'alasan_reject',
        'status',
        'disetujui_oleh',
        'disetujui_pada',
        'diclosing_oleh',
        'diclosing_pada',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_opname'  => 'date',
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'disetujui_pada'  => 'datetime',
        'diclosing_pada'  => 'datetime',
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

    // ── Scopes ─────────────────────────────────────────────────────────────
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        if ($activeBranchId) {
            return $query->where($this->getTable() . '.branch_id', $activeBranchId);
        } else {
            return $query->whereNull($this->getTable() . '.branch_id');
        }
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diclosing_oleh');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockOpnameDetail::class, 'opname_id');
    }

    // ── Status Helpers ──────────────────────────────────────────────────────
    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'Menunggu Persetujuan';
    }

    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    public function isRunning(): bool
    {
        return $this->status === 'Sedang Berjalan';
    }

    public function isPendingClosing(): bool
    {
        return $this->status === 'Menunggu Closing';
    }

    public function isClosed(): bool
    {
        return $this->status === 'Closed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }
}
