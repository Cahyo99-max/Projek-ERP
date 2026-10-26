<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_customers';

    protected $fillable = [
        'company_id',
        'kode_customer',
        'nama_customer',
        'nama_perusahaan',
        'jenis_customer',
        'contact_person',
        'jabatan',
        'telepon',
        'hp',
        'email',
        'website',
        'npwp',
        'pkp',
        'currency',
        'payment_term_id',
        'credit_limit',
        'sales_id',
        'status',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['alamat'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
            if (empty($model->company_id)) {
                $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
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

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class, 'customer_id')->where('is_default', true);
    }

    public function getAlamatAttribute()
    {
        $addr = $this->defaultAddress ?: $this->addresses()->first();
        if ($addr) {
            return $addr->alamat_lengkap ?: $addr->alamat;
        }
        return null;
    }

    public function getAlamatUtamaAttribute()
    {
        return $this->alamat;
    }

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class, 'customer_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? null);
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class, 'customer_id');
    }

    public function salesPayments()
    {
        return $this->hasMany(SalesPayment::class, 'customer_id');
    }
}
