<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpkPenerimaanBarangJadiSerial extends Model
{
    use HasFactory;

    protected $table = 'spk_penerimaan_barang_jadi_serials';

    protected $fillable = [
        'penerimaan_id',
        'spk_id',
        'barang_jadi_id',
        'gudang_id',
        'stock_adjustment_id',
        'stock_opname_id',
        'nomor_batch',
        'serial_number',
        'status',
        'catatan',
    ];

    /**
     * Scope Filter Active Company: Strict Isolation PT Pusat vs Cabang
     * - Sesi Cabang: penerimaan/spk memiliki branch_id = active_branch_id & company_id IS NULL
     * - Sesi PT Pusat: penerimaan/spk memiliki branch_id IS NULL & company_id = active_company_id
     */
    public function scopeForActiveCompany($query)
    {
        $activeBranchId  = session('active_branch_id');
        $activeCompanyId = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);

        if (!empty($activeBranchId)) {
            // Sesi Cabang: Hanya data milik cabang ini
            return $query->where(function ($q) use ($activeBranchId) {
                $q->whereHas('penerimaan', function ($p) use ($activeBranchId) {
                    $p->where('branch_id', $activeBranchId)
                      ->whereNull('company_id');
                })->orWhereHas('spk', function ($s) use ($activeBranchId) {
                    $s->where('branch_id', $activeBranchId)
                      ->whereNull('company_id');
                })->orWhereHas('gudang', function ($g) use ($activeBranchId) {
                    $g->where('cabang_id', $activeBranchId);
                })->orWhereHas('adjustment', function ($a) use ($activeBranchId) {
                    $a->where('branch_id', $activeBranchId);
                })->orWhereHas('opname', function ($o) use ($activeBranchId) {
                    $o->where('branch_id', $activeBranchId);
                });
            });
        }

        // Sesi PT Pusat: Hanya data level PT Pusat (tanpa cabang)
        return $query->where(function ($q) use ($activeCompanyId) {
            $q->whereHas('penerimaan', function ($p) use ($activeCompanyId) {
                $p->whereNull('branch_id')
                  ->where('company_id', $activeCompanyId);
            })->orWhereHas('spk', function ($s) use ($activeCompanyId) {
                $s->whereNull('branch_id')
                  ->where('company_id', $activeCompanyId);
            })->orWhereHas('gudang', function ($g) use ($activeCompanyId) {
                $g->whereNull('cabang_id')
                  ->where('company_id', $activeCompanyId);
            })->orWhereHas('adjustment', function ($a) use ($activeCompanyId) {
                $a->whereNull('branch_id')
                  ->where('company_id', $activeCompanyId);
            })->orWhereHas('opname', function ($o) use ($activeCompanyId) {
                $o->whereNull('branch_id')
                  ->where('company_id', $activeCompanyId);
            });
        });
    }

    public function scopeForActiveBranch($query)
    {
        return $this->scopeForActiveCompany($query);
    }

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(SpkPenerimaanBarangJadi::class, 'penerimaan_id');
    }

    public function spk(): BelongsTo
    {
        return $this->belongsTo(SpkProduksi::class, 'spk_id');
    }

    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_jadi_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_id');
    }

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }
}
