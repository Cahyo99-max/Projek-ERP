<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'jenis_alamat',
        'nama_penerima',
        'telepon',
        'alamat',
        'kota',
        'provinsi',
        'kode_pos',
        'negara',
        'is_default',
    ];

    protected $appends = ['alamat_lengkap'];

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    public function getAlamatLengkapAttribute()
    {
        $parts = array_filter([
            $this->alamat,
            $this->kota,
            $this->provinsi,
            $this->kode_pos ? ('Kode Pos ' . $this->kode_pos) : null,
        ]);
        return implode(', ', $parts);
    }
}
