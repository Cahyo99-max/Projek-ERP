<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    use HasFactory;

    protected $table = 'customer_contacts';

    protected $fillable = [
        'customer_id',
        'nama',
        'jabatan',
        'hp',
        'email',
    ];

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }
}
