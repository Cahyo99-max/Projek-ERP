<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterSupplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_suppliers';

    protected $fillable = [
        'company_id',
        'kode_supplier',
        'nama_supplier',
        'nama_kontak',
        'telepon',
        'email',
        'npwp',
        'alamat',
        'kota',
        'provinsi',
        'kode_pos',
        'term_pembayaran',
        'limit_kredit',
        'status',
        'keterangan',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function banks()
    {
        return $this->hasMany(MasterSupplierBank::class, 'supplier_id');
    }

    public function primaryBank()
    {
        return $this->hasOne(MasterSupplierBank::class, 'supplier_id')->where('is_primary', true);
    }

    /**
     * Scope active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope for active company
     */
    public function scopeForActiveCompany($query)
    {
        $companyId = session('active_company_id');
        if ($companyId) {
            return $query->where('company_id', $companyId);
        }
        return $query;
    }
}
