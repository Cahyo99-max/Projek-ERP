<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['company_id', 'key', 'value'];

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        if ($activeCompanyId) {
            return $query->where('company_id', $activeCompanyId);
        }
        return $query->where('company_id', auth()->user()->company_id ?? 1);
    }
}
