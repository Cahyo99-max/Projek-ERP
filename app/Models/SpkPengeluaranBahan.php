<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpkPengeluaranBahan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'spk_pengeluaran_bahans';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_pengeluaran',
        'tanggal_pengeluaran',
        'spk_id',
        'gudang_id',
        'penyerah_karyawan_id',
        'penyerah_nama',
        'penerima_karyawan_id',
        'penerima_nama',
        'status',
        'catatan',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_pengeluaran' => 'date',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
            if (empty($model->branch_id) && session()->has('active_branch_id')) {
                $model->branch_id = session('active_branch_id');
            }
        });
    }

    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeCompanyId)) {
            $query->where('spk_pengeluaran_bahans.company_id', $activeCompanyId);
        }

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang → hanya transaksi milik Cabang ini
            $query->where('spk_pengeluaran_bahans.branch_id', $activeBranchId);
        } else {
            // Aktif sebagai PT Pusat → hanya transaksi level PT Pusat (branch_id IS NULL)
            $query->whereNull('spk_pengeluaran_bahans.branch_id');
        }

        return $query;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function spk(): BelongsTo
    {
        return $this->belongsTo(SpkProduksi::class, 'spk_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_id');
    }

    public function penyerah(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penyerah_karyawan_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'penerima_karyawan_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SpkPengeluaranBahanDetail::class, 'pengeluaran_id');
    }

    public function jurnal()
    {
        return $this->hasOne(Jurnal::class, 'referensi_id')->where('referensi_tipe', 'Pengeluaran Bahan SPK');
    }
}
