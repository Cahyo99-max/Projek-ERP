<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warehouse_transfers';

    protected $fillable = [
        'nomor_transfer',
        'tanggal_transfer',
        'gudang_asal_id',
        'gudang_tujuan_id',
        'keterangan',
        'status',
        'company_id',
        'branch_id',
        'jenis_transfer',
        'coa_debit_id',
        'coa_kredit_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_transfer' => 'date',
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

    public function coaDebit(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_debit_id');
    }

    public function coaKredit(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_kredit_id');
    }

    public function gudangAsal(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_asal_id');
    }

    public function gudangTujuan(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_tujuan_id');
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

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(WarehouseTransferDetail::class, 'transfer_id');
    }

    public function jurnal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Transfer Gudang');
    }

    public function jurnals(): HasMany
    {
        return $this->hasMany(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Transfer Gudang');
    }

    // ── Helpers ────────────────────────────────────────────────────────────
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
