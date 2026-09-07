<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_requisitions';

    protected $fillable = [
        'company_id',
        'nomor_pr',
        'branch_id',
        'tanggal_pr',
        'tanggal_dibutuhkan',
        'departemen_id',
        'diminta_oleh',
        'keterangan',
        'alasan_penolakan',
        'status',
        'dibuat_oleh',
        'disetujui_oleh',
        'tanggal_disetujui',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
        });
    }

    protected $casts = [
        'tanggal_pr' => 'date',
        'tanggal_dibutuhkan' => 'date',
        'tanggal_disetujui' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'departemen_id');
    }

    public function requester()
    {
        return $this->belongsTo(Karyawan::class, 'diminta_oleh');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'diminta_oleh');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function details()
    {
        return $this->hasMany(PurchaseRequisitionDetail::class, 'pr_id');
    }

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        if (!empty($activeBranchId)) {
            return $query->where($this->getTable() . '.branch_id', intval($activeBranchId));
        }
        return $query->whereNull($this->getTable() . '.branch_id');
    }
}
