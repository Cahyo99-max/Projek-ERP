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
        'gudang_id',
        'status',
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

    protected $casts = [
        'tanggal_gr' => 'date',
        'kurs'       => 'decimal:4',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
            if (empty($model->branch_id) && session()->has('active_branch_id')) {
                $model->branch_id = session('active_branch_id');
            }
            if (auth()->check()) {
                $model->created_by = auth()->id();
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

    public function company(): BelongsTo { return $this->belongsTo(Company::class, 'company_id'); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class, 'branch_id'); }
    public function po(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'po_id'); }
    public function gudang(): BelongsTo { return $this->belongsTo(MasterGudang::class, 'gudang_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function deleter(): BelongsTo { return $this->belongsTo(User::class, 'deleted_by'); }

    public function details(): HasMany { return $this->hasMany(GoodsReceiptDetail::class, 'gr_id'); }

    public function jurnal(): \Illuminate\Database\Eloquent\Relations\HasOne {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Goods Receipt');
    }

    public function purchaseInvoice(): \Illuminate\Database\Eloquent\Relations\HasOne {
        return $this->hasOne(PurchaseInvoice::class, 'gr_id');
    }

    /**
     * Scope for active company
     */
    public function scopeForActiveCompany($query)
    {
        $companyId = session('active_company_id');
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
