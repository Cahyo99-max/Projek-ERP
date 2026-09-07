<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SalesQuotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_quotations';

    protected $fillable = [
        'company_id',
        'branch_id',
        'nomor_quotation',
        'revisi',
        'parent_quotation_id',
        'customer_id',
        'tender_id',
        'sales_id',
        'tanggal_quotation',
        'berlaku_hingga',
        'perihal',
        'status',
        'payment_term_id',
        'tipe_dp',
        'dp_persen',
        'dp_nominal',
        'sisa_tagihan',
        'catatan_pembayaran',
        'currency',
        'subtotal',
        'diskon_tipe',
        'diskon_persen',
        'diskon_nominal',
        'ppn_pajak_id',
        'tipe_ppn',
        'tarif_ppn',
        'nilai_ppn',
        'pph_pajak_id',
        'tipe_pph',
        'tarif_pph',
        'nilai_pph',
        'biaya_pengiriman',
        'catatan_biaya_pengiriman',
        'grand_total',
        'total_hpp',
        'estimasi_laba_kotor',
        'margin_persen',
        'catatan',
        'syarat_ketentuan',
        'sales_order_id',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_quotation' => 'date',
        'berlaku_hingga'    => 'date',
        'approved_at'        => 'datetime',
        'dp_persen'         => 'decimal:2',
        'dp_nominal'        => 'decimal:2',
        'sisa_tagihan'      => 'decimal:2',
        'subtotal'          => 'decimal:2',
        'diskon_persen'     => 'decimal:2',
        'diskon_nominal'    => 'decimal:2',
        'tarif_ppn'         => 'decimal:2',
        'nilai_ppn'         => 'decimal:2',
        'tarif_pph'         => 'decimal:2',
        'nilai_pph'         => 'decimal:2',
        'biaya_pengiriman'  => 'decimal:2',
        'grand_total'       => 'decimal:2',
        'total_hpp'         => 'decimal:2',
        'estimasi_laba_kotor' => 'decimal:2',
        'margin_persen'     => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (empty($model->company_id) && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
            if (!isset($model->branch_id) && session()->has('active_branch_id')) {
                $model->branch_id = session('active_branch_id');
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    /**
     * Scope for multi-company tenancy & branch isolation
     */
    public function scopeForActiveCompany($query)
    {
        $activeCompanyId = session('active_company_id') ?: (Auth::user()->company_id ?? 1);
        $activeBranchId  = session('active_branch_id');

        if (!empty($activeCompanyId)) {
            $query->where(function ($q) use ($activeCompanyId) {
                $q->where('sales_quotations.company_id', $activeCompanyId)
                  ->orWhereNull('sales_quotations.company_id');
            });
        }

        if (!empty($activeBranchId)) {
            $query->where('sales_quotations.branch_id', $activeBranchId);
        } else {
            $query->where(function ($q) {
                $q->whereNull('sales_quotations.branch_id')
                  ->orWhere('sales_quotations.branch_id', 0);
            });
        }

        return $query;
    }

    // Relationships
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

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function sales()
    {
        return $this->belongsTo(Karyawan::class, 'sales_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }

    public function ppnPajak()
    {
        return $this->belongsTo(MasterPajak::class, 'ppn_pajak_id');
    }

    public function pphPajak()
    {
        return $this->belongsTo(MasterPajak::class, 'pph_pajak_id');
    }

    public function items()
    {
        return $this->hasMany(SalesQuotationItem::class, 'sales_quotation_id')->orderBy('urutan', 'asc');
    }

    public function parentQuotation()
    {
        return $this->belongsTo(SalesQuotation::class, 'parent_quotation_id');
    }

    public function revisions()
    {
        return $this->hasMany(SalesQuotation::class, 'parent_quotation_id')->orderBy('revisi', 'asc');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Auto generate Quotation Number: SQ/YYYY/MM/XXXX
     */
    public static function generateNomor($companyId = null)
    {
        $companyId = $companyId ?: (session('active_company_id') ?: (Auth::user()->company_id ?? 1));
        $company = Company::find($companyId);
        $prefixCompany = $company ? ($company->kode_perusahaan ?: 'CP') : 'CP';

        $year = date('Y');
        $month = date('m');
        $prefix = "SQ/{$prefixCompany}/{$year}/{$month}/";

        $lastQuotation = self::where('company_id', $companyId)
            ->where('nomor_quotation', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastQuotation) {
            $parts = explode('/', $lastQuotation->nomor_quotation);
            $lastCode = end($parts);
            $nextNum = intval($lastCode) + 1;
        }

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
