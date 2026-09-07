<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'master_tipe_akun_id',
        'normal_balance',
        'parent_id',
        'is_header',
        'level',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_header' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
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
     * Master Tipe Akun (Optional FK)
     */
    public function masterTipeAkun()
    {
        return $this->belongsTo(MasterTipeAkun::class, 'master_tipe_akun_id');
    }

    /**
     * Parent COA (Induk Akun)
     */
    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Child COA (Anak Akun)
     */
    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id')->orderBy('code');
    }

    /**
     * Account Mappings associated with this COA
     */
    public function mappings()
    {
        return $this->hasMany(AccountMapping::class, 'coa_id');
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for detail (non-header) accounts
     */
    public function scopeDetailOnly($query)
    {
        return $query->where('is_header', false);
    }

    /**
     * Label Type
     */
    public function getTypeLabelAttribute(): string
    {
        if ($this->masterTipeAkun) {
            return $this->masterTipeAkun->nama_tipe;
        }

        return match ($this->type) {
            'asset' => 'Aset / Aktiva',
            'liability' => 'Kewajiban / Utang',
            'equity' => 'Ekuitas / Modal',
            'revenue' => 'Pendapatan',
            'cogs' => 'Harga Pokok Penjualan (HPP)',
            'expense' => 'Beban Operasional',
            'other_revenue' => 'Pendapatan Lain-lain',
            'other_expense' => 'Beban Lain-lain',
            default => ucfirst($this->type ?? 'Lain-lain'),
        };
    }

    /**
     * Normal Balance Label (Debit / Kredit)
     */
    public function getNormalBalanceLabelAttribute(): string
    {
        return ucfirst($this->normal_balance ?? ($this->masterTipeAkun ? $this->masterTipeAkun->saldo_normal_default : ($this->type === 'asset' || $this->type === 'cogs' || $this->type === 'expense' ? 'debit' : 'kredit')));
    }
}
