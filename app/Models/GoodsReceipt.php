<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'goods_receipts';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_gr',
        'tanggal_gr',
        'po_id',
        'tender_id',
        'gudang_id',
        'status',
        'alasan_pembatalan',
        'canceled_by',
        'canceled_at',
        'nomor_surat_jalan',
        'keterangan',
        'kurs',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function isCanceled(): bool
    {
        return $this->status === 'Dibatalkan';
    }

    protected $casts = [
        'tanggal_gr'  => 'date',
        'canceled_at' => 'datetime',
        'kurs'        => 'decimal:4',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }

            $branchId  = $model->branch_id ?: session('active_branch_id');
            $companyId = $model->company_id ?: session('active_company_id');

            if (!empty($branchId)) {
                $model->branch_id  = $branchId;
                $model->company_id = null;
            } else {
                $model->branch_id  = null;
                $model->company_id = $companyId;
            }
        });
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }

            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } else if (!empty($model->company_id)) {
                $model->branch_id = null;
            }
        });
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class, 'company_id'); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class, 'branch_id'); }
    public function po(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'po_id'); }
    public function tender(): BelongsTo { return $this->belongsTo(Tender::class, 'tender_id'); }
    public function gudang(): BelongsTo { return $this->belongsTo(MasterGudang::class, 'gudang_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function deleter(): BelongsTo { return $this->belongsTo(User::class, 'deleted_by'); }
    public function canceler(): BelongsTo { return $this->belongsTo(User::class, 'canceled_by'); }

    public function details(): HasMany { return $this->hasMany(GoodsReceiptDetail::class, 'gr_id'); }

    public function jurnal(): \Illuminate\Database\Eloquent\Relations\HasOne {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Goods Receipt');
    }

    public function purchaseInvoice(): \Illuminate\Database\Eloquent\Relations\HasOne {
        return $this->hasOne(PurchaseInvoice::class, 'gr_id');
    }

    public function alokasiBiayaDetails(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(AlokasiBiayaDetail::class, 'gr_id');
    }

    /**
     * Scope for active company
     */
    public function scopeForActiveCompany($query)
    {
        $companyId = session('active_company_id');
        $branchId  = session('active_branch_id');

        if (!empty($branchId)) {
            return $query;
        }

        if ($companyId) {
            return $query->where('goods_receipts.company_id', $companyId);
        }
        return $query;
    }

    /**
     * Scope for active branch
     */
    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            return $query->where('goods_receipts.branch_id', intval($branchId));
        }
        return $query->whereNull('goods_receipts.branch_id');
    }
}
