<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpkProduksi extends Model
{
    use SoftDeletes;

    protected $table = 'spk_produksi';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_spk',
        'tanggal_spk',
        'target_selesai',
        'bom_id',
        'barang_jadi_id',
        'tender_id',
        'satuan_id',
        'nomor_batch',
        'serial_numbers',
        'jumlah_rencana',
        'jumlah_selesai',
        'jumlah_reject',
        'tipe_spk',
        'prioritas',
        'penanggung_jawab_id',
        'estimasi_biaya_bahan',
        'estimasi_biaya_tk',
        'estimasi_biaya_overhead',
        'estimasi_total_biaya',
        'is_hpp_closed',
        'hpp_closed_at',
        'hpp_closed_by',
        'is_material_closed',
        'material_closed_at',
        'material_closed_by',
        'material_closing_status',
        'material_closing_requested_at',
        'material_closing_requested_by',
        'material_closing_note',
        'material_closing_reject_reason',
        'material_closing_rejected_at',
        'material_closing_rejected_by',
        'is_fg_closed',
        'fg_closing_status',
        'fg_closing_requested_at',
        'fg_closing_requested_by',
        'fg_closing_note',
        'fg_closing_reject_reason',
        'fg_closing_rejected_at',
        'fg_closing_rejected_by',
        'fg_closed_at',
        'fg_closed_by',
        'status',
        'keterangan',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_spk'                   => 'date:Y-m-d',
        'target_selesai'                => 'date:Y-m-d',
        'jumlah_rencana'                => 'decimal:4',
        'jumlah_selesai'                => 'decimal:4',
        'jumlah_reject'                 => 'decimal:4',
        'estimasi_biaya_bahan'          => 'decimal:4',
        'estimasi_biaya_tk'             => 'decimal:4',
        'estimasi_biaya_overhead'       => 'decimal:4',
        'estimasi_total_biaya'          => 'decimal:4',
        'is_hpp_closed'                 => 'boolean',
        'hpp_closed_at'                 => 'datetime',
        'is_material_closed'            => 'boolean',
        'material_closed_at'            => 'datetime',
        'material_closing_requested_at' => 'datetime',
        'material_closing_rejected_at'  => 'datetime',
        'is_fg_closed'                  => 'boolean',
        'fg_closing_requested_at'       => 'datetime',
        'fg_closing_rejected_at'        => 'datetime',
        'fg_closed_at'                  => 'datetime',
        'approved_at'                   => 'datetime',
        'created_at'                    => 'datetime',
        'updated_at'                    => 'datetime',
        'deleted_at'                    => 'datetime',
    ];

    /**
     * Booted Model Event: Pastikan aturan pemisahan PT vs Cabang selalu konsisten saat pembuatan SPK.
     * - Jika input Cabang (branch_id terisi) → company_id dipastikan NULL.
     * - Jika input PT (branch_id kosong)    → branch_id dipastikan NULL & company_id terisi.
     */
    protected static function booted(): void
    {
        static::creating(function ($spk) {
            if (!empty($spk->branch_id)) {
                $spk->company_id = null;
            } elseif (empty($spk->company_id)) {
                $spk->company_id = session('active_company_id') ?: (auth()->user()->company_id ?? 1);
            }
        });
    }

    /**
     * Scope: Filter berdasarkan active company + pisah PT Pusat vs Cabang.
     * - Jika aktif sebagai Cabang (branch_id ada)  → branch_id = active_branch_id & company_id (PT) NULL.
     * - Jika aktif sebagai PT Pusat (branch_id null) → branch_id (Cabang) NULL & company_id = active_company_id.
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang → hanya data cabang ini (company_id NULL)
            $query->where($table . '.branch_id', $activeBranchId)
                  ->whereNull($table . '.company_id');
        } else {
            // Aktif sebagai PT Pusat → hanya data level PT (branch_id NULL)
            $query->whereNull($table . '.branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($table . '.company_id', $activeCompanyId);
            } else {
                $query->where($table . '.company_id', 0);
            }
        }

        return $query;
    }

    /**
     * Scope tambahan untuk filter branch.
     * - Jika aktif sebagai Cabang → branch_id = active_branch_id & company_id NULL.
     * - Jika aktif sebagai PT Pusat → branch_id NULL.
     */
    public function scopeForActiveBranch($query)
    {
        $activeBranchId = session('active_branch_id');
        $table = $this->getTable();
        return !empty($activeBranchId)
            ? $query->where($table . '.branch_id', $activeBranchId)->whereNull($table . '.company_id')
            : $query->whereNull($table . '.branch_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_jadi_id');
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(MasterSatuan::class, 'satuan_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penanggung_jawab_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(SpkProduksiMaterial::class, 'spk_id');
    }

    public function tahapans(): HasMany
    {
        return $this->hasMany(SpkProduksiTahapan::class, 'spk_id')->orderBy('urutan', 'asc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function hppClosedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hpp_closed_by');
    }

    public function materialClosedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'material_closed_by');
    }

    public function materialClosingRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'material_closing_requested_by');
    }

    public function materialClosingRejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'material_closing_rejected_by');
    }

    public function materialClosingJurnal()
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Pengeluaran Bahan SPK');
    }

    public function fgClosedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fg_closed_by');
    }

    public function fgClosingRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fg_closing_requested_by');
    }

    public function fgClosingRejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fg_closing_rejected_by');
    }

    public function fgClosingJurnal()
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Penerimaan Barang Jadi SPK');
    }

    public function pengeluaranBahans(): HasMany
    {
        return $this->hasMany(SpkPengeluaranBahan::class, 'spk_id')->latest('id');
    }

    public function penerimaanBarangJadis(): HasMany
    {
        return $this->hasMany(SpkPenerimaanBarangJadi::class, 'spk_id')->latest('id');
    }
}
