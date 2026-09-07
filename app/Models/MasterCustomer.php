<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_customers';

    protected $fillable = [
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
        'created_by',
        'updated_by',
    ];

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class, 'customer_id')->where('is_default', true);
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
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId && \Illuminate\Support\Facades\Schema::hasColumn('master_customers', 'company_id')) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
