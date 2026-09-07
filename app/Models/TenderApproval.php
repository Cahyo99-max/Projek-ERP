<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderApproval extends Model
{
    use HasFactory;

    protected $table = 'tender_approvals';

    protected $fillable = [
        'tender_id',
        'user_id',
        'status',
        'approved_at',
        'catatan',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
