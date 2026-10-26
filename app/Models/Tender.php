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
        'nilai_deal',
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
        'nilai_deal' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            // Strict Separation: if branch_id is filled, company_id MUST be null. If branch_id is null, company_id MUST be active_company_id
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } elseif (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
                $model->branch_id = null;
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            // Strict Separation on update as well
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } elseif (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
                $model->branch_id = null;
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
        return $this->hasMany(TenderFollowup::class, 'tender_id')->latest('id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TenderApproval::class, 'tender_id')->latest('id');
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
        $activeCompanyId = session('active_company_id') ?: (auth()->check() ? auth()->user()->company_id : null);
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang -> hanya data cabang ini (company_id NULL)
            $query->where($table . '.branch_id', $activeBranchId)
                  ->whereNull($table . '.company_id');
        } else {
            // Aktif sebagai PT Pusat -> hanya data level PT (branch_id NULL)
            $query->whereNull($table . '.branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($table . '.company_id', $activeCompanyId);
            } else {
                $query->where($table . '.company_id', 0);
            }
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
