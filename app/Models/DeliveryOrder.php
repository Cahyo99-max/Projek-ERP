<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'delivery_orders';

    protected $fillable = [
        'nomor_do',
        'sales_order_id',
        'customer_id',
        'nama_penerima',
        'telepon_penerima',
        'alamat_tujuan',
        'company_id',
        'branch_id',
        'gudang_id',
        'tanggal_kirim',
        'estimasi_tiba',
        'metode_pengiriman',
        'nama_ekspedisi',
        'nomor_resi',
        'nama_sopir',
        'plat_nomor',
        'biaya_ongkir',
        'catatan_pengiriman',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'estimasi_tiba' => 'date',
        'biaya_ongkir'   => 'decimal:2',
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
        $activeCompanyId = session('active_company_id') ?? auth()->user()?->company_id;
        if (!empty($activeCompanyId)) {
            $query->where('company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        if (!empty($activeBranchId)) {
            $query->where('branch_id', $activeBranchId);
        } else {
            $query->whereNull('branch_id');
        }
        return $query;
    }

    // ── Relationships ──────────────────────────────────────────────────────
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

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

    public function details(): HasMany
    {
        return $this->hasMany(DeliveryOrderDetail::class, 'delivery_order_id');
    }
}
