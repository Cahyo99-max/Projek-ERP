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
            if (empty($model->dibuat_oleh) && auth()->check()) {
                $model->dibuat_oleh = auth()->id();
            }

            $branchId  = $model->branch_id ?: session('active_branch_id');
            $companyId = $model->company_id ?: session('active_company_id');

            if (!empty($branchId)) {
                $model->branch_id  = $branchId;
                $model->company_id = null;
            } else {
                $model->branch_id  = null;
                $model->company_id = $companyId ?: 1;
            }
        });

        static::updating(function ($model) {
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } else if (!empty($model->company_id)) {
                $model->branch_id = null;
            }
        });
    }

    protected $casts = [
        'company_id'         => 'integer',
        'branch_id'          => 'integer',
        'departemen_id'      => 'integer',
        'diminta_oleh'       => 'integer',
        'dibuat_oleh'        => 'integer',
        'disetujui_oleh'     => 'integer',
        'tanggal_pr'         => 'date',
        'tanggal_dibutuhkan' => 'date',
        'tanggal_disetujui'  => 'datetime',
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
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeBranchId)) {
            return $query;
        }

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
