<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_orders';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_so',
        'tanggal_so',
        'customer_id',
        'sales_quotation_id',
        'tender_id',
        'sales_id',
        'nama_customer_walkin',
        'telepon_customer_walkin',
        'pricing_tier',
        'payment_term_id',
        'payment_method_id',
        'status',
        'subtotal',
        'diskon_tipe',
        'diskon_persen',
        'diskon_nilai',
        'diskon_nominal',
        'pajak_tipe',
        'pajak_persen',
        'pajak_nominal',
        'ppn_pajak_id',
        'tipe_ppn',
        'tarif_ppn',
        'nilai_ppn',
        'pph_pajak_id',
        'tipe_pph',
        'tarif_pph',
        'nilai_pph',
        'biaya_pengiriman',
        'grand_total',
        'total',
        'total_hpp',
        'estimasi_laba_kotor',
        'margin_persen',
        'tipe_dp',
        'dp_persen',
        'dp_nominal',
        'sisa_tagihan',
        'catatan',
        'alasan_pembatalan',
        'syarat_ketentuan',
        'alamat_pengiriman',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = $model->created_by ?: auth()->id();
            }
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            } else {
                if (empty($model->company_id)) {
                    $model->company_id = session('active_company_id') ?: (auth()->user()?->company_id ?? 1);
                }
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            if (!empty($model->branch_id)) {
                $model->company_id = null;
            }
        });
    }

    /**
     * Scope for multi-company tenancy & branch isolation
     * - Jika aktif sebagai Cabang (branch_id ada)  → branch_id = active_branch_id & company_id (PT) NULL.
     * - Jika aktif sebagai PT Pusat (branch_id null) → branch_id (Cabang) NULL & company_id = active_company_id.
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id') ?: auth()->user()?->company_id;
        $activeBranchId  = session('active_branch_id');
        $table = $this->getTable();

        if (!empty($activeBranchId)) {
            // Aktif sebagai Cabang -> hanya data cabang ini (company_id NULL)
            $query->where($table . '.branch_id', $activeBranchId)
                  ->whereNull($table . '.company_id');
        } else {
            // Aktif sebagai PT Pusat -> hanya data level PT (branch_id NULL)
            $query->whereNull($table . '.branch_id');
            if (!empty($activeCompanyId)) {
                $query->where($table . '.company_id', $activeCompanyId);
            } else {
                $query->where($table . '.company_id', 0);
            }
        }

        return $query;
    }

    protected $casts = [
        'tanggal_so'         => 'date',
        'subtotal'           => 'decimal:2',
        'diskon_persen'      => 'decimal:2',
        'diskon_nilai'       => 'decimal:2',
        'diskon_nominal'     => 'decimal:2',
        'pajak_persen'       => 'decimal:2',
        'pajak_nominal'      => 'decimal:2',
        'tarif_ppn'          => 'decimal:2',
        'nilai_ppn'          => 'decimal:2',
        'tarif_pph'          => 'decimal:2',
        'nilai_pph'          => 'decimal:2',
        'biaya_pengiriman'   => 'decimal:2',
        'grand_total'        => 'decimal:2',
        'total'              => 'decimal:2',
        'total_hpp'          => 'decimal:2',
        'estimasi_laba_kotor'=> 'decimal:2',
        'margin_persen'      => 'decimal:2',
        'dp_persen'          => 'decimal:2',
        'dp_nominal'         => 'decimal:2',
        'sisa_tagihan'       => 'decimal:2',
    ];

    /**
     * Accessor: ambil grand_total jika ada, fallback ke total (untuk backward compat POS)
     */
    public function getEffectiveTotalAttribute(): float
    {
        $gt = (float)($this->grand_total ?? 0);
        $t  = (float)($this->total ?? 0);
        return $gt > 0 ? $gt : $t;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function quotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'sales_quotation_id');
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function sales()
    {
        return $this->belongsTo(Karyawan::class, 'sales_id');
    }

    public function ppnPajak()
    {
        return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id');
    }

    public function pphPajak()
    {
        return $this->belongsTo(MasterPajak::class, 'pph_pajak_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function details()
    {
        return $this->hasMany(SalesOrderDetail::class, 'sales_order_id');
    }

    public function currency()
    {
        return $this->belongsTo(MasterCurrency::class, 'currency_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class, 'sales_order_id');
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class, 'sales_order_id');
    }

    /**
     * Cek & ambil Faktur Penjualan / Sales Invoice aktif (bukan Cancelled atau Rejected)
     */
    public function getActiveInvoice()
    {
        $inactiveStatuses = ['Cancelled', 'Rejected'];

        // 1. Cek langsung via salesInvoices
        if ($this->relationLoaded('salesInvoices')) {
            $direct = $this->salesInvoices->first(fn($i) => !in_array($i->status, $inactiveStatuses));
            if ($direct) return $direct;
        } else {
            $direct = $this->salesInvoices()->whereNotIn('status', $inactiveStatuses)->first();
            if ($direct) return $direct;
        }

        // 2. Cek via deliveryOrders -> salesInvoices
        if ($this->relationLoaded('deliveryOrders')) {
            foreach ($this->deliveryOrders as $do) {
                if ($do->relationLoaded('salesInvoices')) {
                    $doInv = $do->salesInvoices->first(fn($i) => !in_array($i->status, $inactiveStatuses));
                    if ($doInv) return $doInv;
                } else {
                    $doInv = $do->salesInvoices()->whereNotIn('status', $inactiveStatuses)->first();
                    if ($doInv) return $doInv;
                }
            }
        } else {
            $doInvoice = SalesInvoice::whereHas('deliveryOrder', function ($q) {
                $q->where('sales_order_id', $this->id);
            })->whereNotIn('status', $inactiveStatuses)->first();

            if ($doInvoice) return $doInvoice;
        }

        return null;
    }

    /**
     * Auto generate format nomor SO: SO/{PREFIX}/{YYYY}/{MM}/{XXXX}
     * - Jika di Cabang: Prefix kode cabang (SO/CBG001/2026/09/0001)
     * - Jika di PT Pusat: Prefix kode PT (SO/PT-MB/2026/09/0001)
     * Dilengkapi MySQL Advisory Lock & while-loop fallback untuk mencegah duplikasi saat input bersamaan.
     */
    public static function generateNomorSo($companyId = null, $branchId = null)
    {
        $activeBranchId = $branchId !== null ? $branchId : session('active_branch_id');
        $year = date('Y');
        $month = date('m');

        if (!empty($activeBranchId)) {
            $branch = Branch::find($activeBranchId);
            $prefixBranch = $branch ? ($branch->kode_cabang ?: 'CBG') : 'CBG';
            $prefix = "SO/{$prefixBranch}/{$year}/{$month}/";
        } else {
            $companyId = $companyId ?: (session('active_company_id') ?: (Auth::user()?->company_id ?? 1));
            $company = Company::find($companyId);
            $prefixCompany = $company ? ($company->kode_perusahaan ?: 'CP') : 'CP';
            $prefix = "SO/{$prefixCompany}/{$year}/{$month}/";
        }

        // Lock to ensure concurrency safety across simultaneous requests
        $lockKey = 'so_generate_lock_' . md5($prefix);
        $gotLock = false;
        try {
            $lockRes = DB::select("SELECT GET_LOCK(?, 10) as lk", [$lockKey]);
            $gotLock = !empty($lockRes) && $lockRes[0]->lk == 1;

            // Find all existing records with this prefix (including soft-deleted)
            $existingNumbers = self::withTrashed()
                ->where('nomor_so', 'like', $prefix . '%')
                ->pluck('nomor_so');

            $maxSeq = 0;
            foreach ($existingNumbers as $num) {
                $parts = explode('/', $num);
                $lastPart = end($parts);
                if (is_numeric($lastPart)) {
                    $val = (int)$lastPart;
                    if ($val > $maxSeq) {
                        $maxSeq = $val;
                    }
                }
            }

            $nextNum = $maxSeq + 1;
            $candidate = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Double check existence to guarantee 100% uniqueness
            while (self::withTrashed()->where('nomor_so', $candidate)->exists()) {
                $nextNum++;
                $candidate = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }

            return $candidate;
        } finally {
            if ($gotLock) {
                DB::select("SELECT RELEASE_LOCK(?)", [$lockKey]);
            }
        }
    }
}
