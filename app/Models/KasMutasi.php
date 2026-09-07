<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KasMutasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kas_mutasis';

    protected $fillable = [
        'nomor_transaksi',
        'kas_bank_id',
        'tipe',
        'kategori',
        'lawan_coa_id',
        'jumlah',
        'saldo_sebelum',
        'saldo_sesudah',
        'tanggal',
        'nomor_referensi',
        'kontak_tipe',
        'kontak_id',
        'nama_kontak',
        'keterangan',
        'lampiran',
        'transfer_to_kas_bank_id',
        'status',
        'jurnal_id',
        'company_id',
        'branch_id',
        'created_by',
        'cancelled_by',
        'cancelled_at',
        'alasan_pembatalan',
    ];

    protected $casts = [
        'jumlah'        => 'decimal:2',
        'saldo_sebelum' => 'decimal:2',
        'saldo_sesudah' => 'decimal:2',
        'tanggal'       => 'date',
        'cancelled_at'  => 'datetime',
    ];

    /**
     * Scope Active Company
     */
    public function scopeForActiveCompany($query)
    {
        $companyId = session('active_company_id') ?? auth()->user()?->company_id;
        if (!empty($companyId)) {
            return $query->where('company_id', $companyId);
        }
        return $query;
    }

    /**
     * Scope Active Branch (Strict Isolation: PT Pusat vs Cabang)
     */
    public function scopeForActiveBranch($query)
    {
        $branchId = session('active_branch_id');
        if (!empty($branchId)) {
            return $query->where('branch_id', $branchId);
        }
        return $query->whereNull('branch_id');
    }

    public function kasBank()
    {
        return $this->belongsTo(KasBank::class, 'kas_bank_id');
    }

    public function transferToKasBank()
    {
        return $this->belongsTo(KasBank::class, 'transfer_to_kas_bank_id');
    }

    public function lawanCoa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'lawan_coa_id');
    }

    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceler()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
