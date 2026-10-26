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
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
        'catatan_approval',
        'catatan_penerimaan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'estimasi_tiba' => 'date',
        'approved_at'   => 'datetime',
        'received_at'   => 'datetime',
        'biaya_ongkir'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?: auth()->id();
            }
            $activeBranchId = session('active_branch_id');
            if (!empty($model->branch_id) || !empty($activeBranchId)) {
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
            if (!empty($model->branch_id)) {
                $model->company_id = null;
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
        $activeCompanyId = session('active_company_id') ?: auth()->user()?->company_id;
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
            return $query->where($this->getTable() . '.branch_id', $activeBranchId);
        }
        return $query->whereNull($this->getTable() . '.branch_id');
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DeliveryOrderDetail::class, 'delivery_order_id');
    }

    public function jurnal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Delivery Order');
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class, 'delivery_order_id');
    }
}
