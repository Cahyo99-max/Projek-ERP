<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterPajak extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_pajaks';

    protected $fillable = [
        'company_id',
        'kode_pajak',
        'nama_pajak',
        'tarif',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tarif' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) $model->created_by = auth()->id();
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
        });
        static::updating(function ($model) {
            if (auth()->check()) $model->updated_by = auth()->id();
        });
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class, 'company_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function deleter(): BelongsTo { return $this->belongsTo(User::class, 'deleted_by'); }

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if (!$activeCompanyId && session('active_branch_id')) {
            $branch = Branch::find(session('active_branch_id'));
            $activeCompanyId = $branch ? $branch->company_id : null;
        }
        if (!$activeCompanyId && auth()->check()) {
            $activeCompanyId = auth()->user()->company_id ?? 1;
        }
        if ($activeCompanyId) {
            return $query->where(function($q) use ($activeCompanyId) {
                $q->where($this->getTable() . '.company_id', $activeCompanyId)
                  ->orWhereNull($this->getTable() . '.company_id');
            });
        }
        return $query;
    }

    public function scopeActive($query)
    {
        return $query->where($this->getTable() . '.status', 'Active');
    }
}

