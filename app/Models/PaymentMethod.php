<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payment_methods';

    protected $fillable = [
        'company_id',
        'kode_metode',
        'nama_metode',
        'status',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
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

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $query;
    }

    public function scopeActive($query)
    {
        $q = $query->where($this->getTable() . '.status', 'Active');
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            $q->where($this->getTable() . '.company_id', $activeCompanyId);
        }
        return $q;
    }
}
