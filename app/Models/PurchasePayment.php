<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchasePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_payments';

    protected $fillable = [
        'nomor_pembayaran',
        'tanggal_bayar',
        'supplier_id',
        'kas_bank_id',
        'payment_method_id',
        'nomor_referensi',
        'branch_id',
        'company_id',
        'currency_id',
        'kurs',
        'total_faktur',
        'total_potongan',
        'potongan_coa_id',
        'total_bayar',
        'status',
        'keterangan',
        'lampiran',
        'created_by',
        'updated_by',
        'deleted_by',
        'posted_by',
        'posted_at',
        'cancelled_by',
        'cancelled_at',
        'alasan_pembatalan',
        // Approval workflow
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'tanggal_bayar'  => 'date',
        'kurs'           => 'decimal:4',
        'total_faktur'   => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'total_bayar'    => 'decimal:2',
        'posted_at'      => 'datetime',
        'cancelled_at'   => 'datetime',
        'submitted_at'   => 'datetime',
        'approved_at'    => 'datetime',
        'rejected_at'    => 'datetime',
    ];

    protected $appends = [
        'lampiran_url',
    ];

    public function getLampiranUrlAttribute(): ?string
    {
        if (!empty($this->lampiran)) {
            return asset('storage/' . $this->lampiran);
        }
        return null;
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?: auth()->id();
            }
            $activeBranchId = session('active_branch_id');
            // Strict Tenancy Isolation:
            // Jika Cabang -> branch_id terisi, company_id WAJIB NULL
            // Jika PT Pusat -> company_id terisi, branch_id WAJIB NULL
            if (!empty($model->branch_id) || (!empty($activeBranchId) && empty($model->company_id))) {
                $model->branch_id  = $model->branch_id ?: $activeBranchId;
                $model->company_id = null;
            } else {
                $model->branch_id = null;
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            // Strict Tenancy Isolation pada update
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } elseif (!empty($model->company_id)) {
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

    /**
     * Scope: Filter berdasarkan entitas aktif (PT Pusat vs Cabang)
     */
    public function scopeForActiveCompany($query)
    {
        $branchId  = session('active_branch_id');
        $companyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
        $table = $this->getTable();

        if (!empty($branchId)) {
            // Aktif sebagai Cabang -> company_id WAJIB NULL
            return $query->whereNull($table . '.company_id');
        }

        // Aktif sebagai PT Pusat -> branch_id WAJIB NULL, company_id = active_company_id
        $query->whereNull($table . '.branch_id');
        if (!empty($companyId)) {
            $query->where($table . '.company_id', $companyId);
        }

        return $query;
    }

    /**
     * Scope Active Branch (Strict Isolation: PT Pusat vs Cabang)
     */
    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($branchId)) {
            return $query->where($table . '.branch_id', intval($branchId))
                         ->whereNull($table . '.company_id');
        }

        return $query->whereNull($table . '.branch_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'Diajukan';
    }

    public function isRejected(): bool
    {
        return $this->status === 'Ditolak';
    }

    public function isPosted(): bool
    {
        return $this->status === 'Posted';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }

    public function canBeSubmitted(): bool
    {
        return in_array($this->status, ['Draft', 'Ditolak']);
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'Diajukan';
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'Diajukan';
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchasePaymentDetail::class, 'purchase_payment_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(KasBank::class, 'kas_bank_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(MasterCurrency::class, 'currency_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function jurnal(): HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Purchase Payment');
    }

    public function reversalJurnal(): HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Purchase Payment Reversal');
    }

    public function kasMutasi(): HasOne
    {
        return $this->hasOne(KasMutasi::class, 'nomor_referensi', 'nomor_pembayaran');
    }

    public function potonganCoa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'potongan_coa_id');
    }
}
