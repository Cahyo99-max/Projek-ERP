<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'company_id',
        'nomor_po',
        'tanggal_po',
        'branch_id',
        'supplier_id',
        'currency_id',
        'kurs',
        'pajak_id',
        'payment_term_id',
        'payment_method_id',
        'tanggal_pengiriman',
        'pr_id',
        'tender_id',
        'manual_pr_number',
        'diskon_persen',
        'diskon_nominal',
        'subtotal',
        'total_diskon',
        'total_pajak',
        'pph_pajak_id',
        'tarif_pph',
        'pph_nominal',
        'total_pph',
        'pph_coa_id',
        'grand_total',
        'grand_total_idr',
        'down_payment',
        'biaya_lain',
        'deskripsi_biaya_lain',
        'alokasi_biaya_lain',
        'biaya_lain_details',
        'keterangan',
        'status',
        'alasan_ditolak',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_po'         => 'date',
        'tanggal_pengiriman' => 'date',
        'approved_at'        => 'datetime',
        'kurs'               => 'decimal:4',
        'diskon_persen'      => 'decimal:2',
        'diskon_nominal'     => 'decimal:2',
        'subtotal'           => 'decimal:2',
        'total_diskon'       => 'decimal:2',
        'total_pajak'        => 'decimal:2',
        'tarif_pph'          => 'decimal:2',
        'pph_nominal'        => 'decimal:2',
        'total_pph'          => 'decimal:2',
        'grand_total'        => 'decimal:2',
        'grand_total_idr'    => 'decimal:2',
        'down_payment'       => 'decimal:2',
        'biaya_lain'         => 'decimal:2',
        'biaya_lain_details' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            $activeBranchId = session('active_branch_id');
            if (!empty($activeBranchId)) {
                $model->branch_id = $activeBranchId;
                $model->company_id = null;
            } else {
                $model->branch_id = null;
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: 1;
                }
            }
        });
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
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

    public function company(): BelongsTo { return $this->belongsTo(Company::class, 'company_id'); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class, 'branch_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(MasterSupplier::class, 'supplier_id'); }
    public function currency(): BelongsTo { return $this->belongsTo(MasterCurrency::class, 'currency_id'); }
    public function pajak(): BelongsTo { return $this->belongsTo(MasterPajak::class, 'pajak_id'); }
    public function pphPajak(): BelongsTo { return $this->belongsTo(MasterPajak::class, 'pph_pajak_id'); }
    public function pphCoa(): BelongsTo { return $this->belongsTo(ChartOfAccount::class, 'pph_coa_id'); }
    public function paymentTerm(): BelongsTo { return $this->belongsTo(PaymentTerm::class, 'payment_term_id'); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class, 'payment_method_id'); }
    public function pr(): BelongsTo { return $this->belongsTo(PurchaseRequisition::class, 'pr_id'); }
    public function tender(): BelongsTo { return $this->belongsTo(Tender::class, 'tender_id'); }
    
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function deleter(): BelongsTo { return $this->belongsTo(User::class, 'deleted_by'); }

    public function details(): HasMany { return $this->hasMany(PurchaseOrderDetail::class, 'po_id'); }
    public function goodsReceipts(): HasMany { return $this->hasMany(GoodsReceipt::class, 'po_id'); }
    public function purchaseInvoices(): HasMany { return $this->hasMany(PurchaseInvoice::class, 'po_id'); }
    public function invoices(): HasMany { return $this->hasMany(PurchaseInvoice::class, 'po_id'); }

    public function scopeForActiveCompany($query)
    {
        if (!empty(session('active_branch_id'))) {
            return $query;
        }
        $companyId = session('active_company_id');
        if ($companyId) {
            return $query->where('company_id', $companyId);
        }
        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            return $query->where('purchase_orders.branch_id', intval($branchId));
        }
        return $query->whereNull('purchase_orders.branch_id');
    }
}
