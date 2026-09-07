<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterJenisBiaya extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_jenis_biayas';

    protected $fillable = [
        'company_id',
        'kode_biaya',
        'nama_jenis_biaya',
        'masuk_hpp',
        'alokasi_biaya',
        'coa_id',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'masuk_hpp' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
        });
    }

    /**
     * Scope query ke PT / Company yang sedang aktif di session
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where('company_id', $activeCompanyId);
        }
        return $query;
    }

    /**
     * Relasi ke Company (PT)
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Relationship to COA
     */
    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    /**
     * Active scope
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
}
