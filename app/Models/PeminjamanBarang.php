<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeminjamanBarang extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'peminjaman_barangs';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_peminjaman',
        'tanggal_pinjam',
        'tanggal_kembali_rencana',
        'tanggal_kembali_aktual',
        'nama_acara',
        'lokasi_acara',
        'penanggung_jawab',
        'kontak_pj',
        'gudang_asal_id',
        'gudang_pameran_id',
        'status',
        'keterangan',
        'diajukan_oleh',
        'diajukan_at',
        'tanda_tangan_pengaju',
        'diketahui_oleh',
        'diketahui_at',
        'tanda_tangan_mengetahui',
        'catatan_mengetahui',
        'disiapkan_oleh',
        'disiapkan_at',
        'tanda_tangan_menyiapkan',
        'diserahkan_at',
        'diterima_kembali_oleh',
        'diterima_kembali_at',
        'tanda_tangan_penerima',
        'catatan_pengembalian',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_pinjam'          => 'date',
        'tanggal_kembali_rencana' => 'date',
        'tanggal_kembali_aktual'  => 'datetime',
        'diajukan_at'             => 'datetime',
        'diketahui_at'            => 'datetime',
        'disiapkan_at'            => 'datetime',
        'diserahkan_at'           => 'datetime',
        'diterima_kembali_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
            if (empty($model->branch_id) && session()->has('active_branch_id')) {
                $model->branch_id = session('active_branch_id');
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────────────────
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->branch();
    }

    public function gudangAsal(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_asal_id');
    }

    public function gudangPameran(): BelongsTo
    {
        return $this->belongsTo(MasterGudang::class, 'gudang_pameran_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PeminjamanBarangDetail::class, 'peminjaman_id');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function pengetahu(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diketahui_oleh');
    }

    public function penyiap(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disiapkan_oleh');
    }

    public function penerimaKembali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterima_kembali_oleh');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Entity Scopes ──────────────────────────────────────────────────────
    public function scopeForActiveEntity($query)
    {
        $activeCompanyId = session('active_company_id');
        $activeBranchId  = session('active_branch_id');
        $table           = $this->getTable();

        if ($activeCompanyId) {
            $query->where($table . '.company_id', $activeCompanyId);
        }

        if (!empty($activeBranchId)) {
            $query->where($table . '.branch_id', $activeBranchId);
        } else {
            $query->whereNull($table . '.branch_id');
        }

        return $query;
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
        $table          = $this->getTable();

        if (!empty($activeBranchId)) {
            return $query->where($table . '.branch_id', $activeBranchId);
        }
        return $query->whereNull($table . '.branch_id');
    }

    // ── Status Helpers ─────────────────────────────────────────────────────
    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function isMenunggu(): bool
    {
        return $this->status === 'Menunggu';
    }

    public function isBarangDisiapkan(): bool
    {
        return $this->status === 'Barang Disiapkan';
    }

    public function isBarangSiap(): bool
    {
        return $this->status === 'Barang Siap';
    }

    public function isSedangDipinjam(): bool
    {
        return $this->status === 'Sedang Dipinjam';
    }

    public function isSelesai(): bool
    {
        return in_array($this->status, ['Selesai', 'Selesai (Ada Masalah)']);
    }

    public function isDitolak(): bool
    {
        return $this->status === 'Ditolak';
    }
}
