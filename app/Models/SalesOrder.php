<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    /**
     * Auto generate format nomor SO: SO-YYMM-XXXX
     */
    public static function generateNomorSo($companyId)
    {
        $prefix = 'SO-' . date('ym') . '-';
        $lastOrder = self::where('company_id', $companyId)
            ->where('nomor_so', 'LIKE', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->nomor_so, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }
}
