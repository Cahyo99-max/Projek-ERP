<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FakturPajak extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'faktur_pajaks';

    protected $fillable = [
        'nomor_faktur_pajak',
        'tanggal_faktur_pajak',
        'masa_pajak',
        'tahun_pajak',
        'jenis_transaksi',
        'jenis_faktur',
        'is_gabungan',
        'customer_id',
        'nama_customer',
        'npwp_customer',
        'nik_customer',
        'alamat_customer',
        'branch_id',
        'company_id',
        'jumlah_dpp',
        'jumlah_ppn',
        'jumlah_ppnbm',
        'status',
        'referensi',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_faktur_pajak' => 'date',
        'is_gabungan'          => 'boolean',
        'jumlah_dpp'           => 'decimal:2',
        'jumlah_ppn'           => 'decimal:2',
        'jumlah_ppnbm'         => 'decimal:2',
    ];

    protected static function booted()
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
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(FakturPajakDetail::class, 'faktur_pajak_id');
    }

    public function invoiceLinks(): HasMany
    {
        return $this->hasMany(FakturPajakInvoice::class, 'faktur_pajak_id');
    }

    public function salesInvoices(): BelongsToMany
    {
        return $this->belongsToMany(SalesInvoice::class, 'faktur_pajak_invoices', 'faktur_pajak_id', 'sales_invoice_id')
            ->withPivot('dpp', 'ppn')
            ->withTimestamps();
    }
}
