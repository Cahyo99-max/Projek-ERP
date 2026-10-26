<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class SalesPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_payments';

    protected $fillable = [
        'nomor_pembayaran',
        'tanggal_bayar',
        'customer_id',
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
    ];

    protected $casts = [
        'tanggal_bayar'  => 'date',
        'kurs'           => 'decimal:4',
        'total_faktur'   => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'total_bayar'    => 'decimal:2',
        'posted_at'      => 'datetime',
        'cancelled_at'   => 'datetime',
    ];

    protected $appends = [
        'lampiran_url',
        'lebih_bayar',
    ];

    public function getLampiranUrlAttribute(): ?string
    {
        if (!empty($this->lampiran)) {
            return asset('storage/' . $this->lampiran);
        }
        return null;
    }

    public function getLebihBayarAttribute(): float
    {
        if ($this->relationLoaded('details') && $this->details->isNotEmpty()) {
            $totalLebih = $this->details->sum(function ($d) {
                return (float)$d->lebih_bayar;
            });
            if ($totalLebih > 0.01) {
                return round($totalLebih, 2);
            }
        }
        $netTagihan = max(0, (float)$this->total_faktur - (float)$this->total_potongan);
        $diff = (float)$this->total_bayar - $netTagihan;
        return $diff > 0.01 ? round($diff, 2) : 0.0;
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }

            // Isolasi Ketat: Jika Cabang aktif -> company_id NULL. Jika PT Pusat aktif -> branch_id NULL.
            $activeBranchId = session('active_branch_id');
            if (!empty($model->branch_id) || !empty($activeBranchId)) {
                $model->branch_id  = $model->branch_id ?: $activeBranchId;
                $model->company_id = null; // MUTUAL EXCLUSIVITY: Cabang -> PT (company_id) harus NULL
            } else {
                $model->branch_id  = null; // MUTUAL EXCLUSIVITY: PT Pusat -> branch_id harus NULL
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });

        static::updating(function ($model) {
            // Jangan anggap perubahan status alur kerja (Posting/Submit/Batal) sebagai "Edit Data"
            $dirtyKeys = array_keys($model->getDirty());
            $workflowKeys = ['status', 'posted_by', 'posted_at', 'cancelled_by', 'cancelled_at', 'alasan_pembatalan'];
            $isOnlyWorkflow = empty(array_diff($dirtyKeys, $workflowKeys));

            if (!$isOnlyWorkflow && auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
            $model->details()->delete();
        });
    }

    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function isPosted(): bool
    {
        return $this->status === 'Posted';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalesPaymentDetail::class, 'sales_payment_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(KasBank::class, 'kas_bank_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function potonganCoa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'potongan_coa_id');
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

    public function jurnal(): HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Sales Payment');
    }

    public function kasMutasi(): HasOne
    {
        return $this->hasOne(KasMutasi::class, 'nomor_referensi', 'nomor_pembayaran');
    }

    /**
     * Generate Nomor Pembayaran Otomatis
     * Format: CR-SLS/{PREFIX}/{YYYY}/{MM}/{SEQ} (Customer Receipt - Sales)
     */
    public static function generateNomorPembayaran($companyId = null, $branchId = null, $tanggalBayar = null)
    {
        $activeBranchId = $branchId !== null ? $branchId : session('active_branch_id');
        $date = $tanggalBayar ? \Carbon\Carbon::parse($tanggalBayar) : now();
        $year = $date->format('Y');
        $month = $date->format('m');

        if (!empty($activeBranchId)) {
            $branch = Branch::find($activeBranchId);
            $prefixEntity = $branch ? ($branch->kode_cabang ?: 'CBG') : 'CBG';
        } else {
            $companyId = $companyId ?: (session('active_company_id') ?: (auth()->user()?->company_id ?? 1));
            $company = Company::find($companyId);
            $prefixEntity = $company ? ($company->kode_perusahaan ?: 'CP') : 'CP';
        }

        $prefix = "CR-SLS/{$prefixEntity}/{$year}/{$month}/";

        $lockKey = 'sales_payment_lock_' . md5($prefix);
        $gotLock = false;
        try {
            $lockRes = DB::select("SELECT GET_LOCK(?, 10) as lk", [$lockKey]);
            $gotLock = !empty($lockRes) && $lockRes[0]->lk == 1;

            $latestNumber = self::withTrashed()
                ->where('nomor_pembayaran', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->value('nomor_pembayaran');

            $maxSeq = 0;
            if ($latestNumber) {
                $parts = explode('/', $latestNumber);
                $lastPart = end($parts);
                if (is_numeric($lastPart)) {
                    $maxSeq = (int) $lastPart;
                }
            }

            return $prefix . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
        } finally {
            if ($gotLock) {
                DB::select("SELECT RELEASE_LOCK(?)", [$lockKey]);
            }
        }
    }

    /**
     * Scope for multi-company tenancy & branch isolation
     * - Jika aktif sebagai Cabang (branch_id ada)  → branch_id = active_branch_id & company_id (PT) NULL.
     * - Jika aktif sebagai PT Pusat (branch_id null) → branch_id (Cabang) NULL & company_id = active_company_id.
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id') ?: auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            $query->where($table . '.branch_id', $activeBranchId)
                  ->whereNull($table . '.company_id');
        } else {
            $query->whereNull($table . '.branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($table . '.company_id', $activeCompanyId);
            } else {
                $query->where($table . '.company_id', 0);
            }
        }

        return $query;
    }
}
