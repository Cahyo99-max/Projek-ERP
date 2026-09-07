<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tender extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tenders';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_kontrak',
        'customer_id',
        'kategori_instansi',
        'sales_id',
        'nama_tender',
        'tanggal',
        'deadline',
        'estimasi_closing',
        'currency',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejected_reason',
        'submitted_at',
        'lost_reason',
        'winning_competitor',
        'tahap_tender',
        'priority',
        'nilai_estimasi',
        'catatan',
        'sales_order_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'deadline' => 'date',
        'estimasi_closing' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'submitted_at' => 'datetime',
        'nilai_estimasi' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
            if (!isset($model->branch_id) && session()->has('active_branch_id')) {
                $model->branch_id = session('active_branch_id');
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'sales_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TenderItem::class, 'tender_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TenderDocument::class, 'tender_id');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(TenderFollowup::class, 'tender_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TenderApproval::class, 'tender_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(SalesQuotation::class, 'tender_id')->orderBy('revisi', 'desc');
    }

    public function latestQuotation(): HasOne
    {
        return $this->hasOne(SalesQuotation::class, 'tender_id')->latestOfMany();
    }

    /**
     * Scope: Filter berdasarkan active company + pisah PT Pusat vs Cabang.
     * - Jika aktif sebagai Cabang -> hanya tampilkan Tender milik cabang tersebut (branch_id = active_branch_id)
     * - Jika aktif sebagai PT Pusat -> hanya tampilkan Tender milik PT (branch_id IS NULL)
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeCompanyId)) {
            $query->where(function ($q) use ($activeCompanyId) {
                $q->where('tenders.company_id', $activeCompanyId)
                  ->orWhereNull('tenders.company_id');
            });
        }

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang -> hanya data cabang ini
            $query->where('tenders.branch_id', $activeBranchId);
        } else {
            // Aktif sebagai PT Pusat -> hanya data level PT (branch_id IS NULL)
            $query->whereNull('tenders.branch_id');
        }

        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        if (!empty($activeBranchId)) {
            return $query->where('tenders.branch_id', $activeBranchId);
        }
        return $query->whereNull('tenders.branch_id');
    }

    /**
     * Accessor untuk nama entitas pemilik tender (PT Pusat atau Nama Cabang)
     */
    public function getEntitasNameAttribute(): string
    {
        if ($this->branch) {
            return 'Cabang ' . $this->branch->nama_cabang;
        }
        return $this->company ? $this->company->nama_perusahaan . ' (Pusat)' : 'PT Pusat';
    }
}
