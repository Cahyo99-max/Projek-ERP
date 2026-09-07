<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderFollowup extends Model
{
    use HasFactory;

    protected $table = 'tender_followups';

    protected $fillable = [
        'tender_id',
        'tanggal',
        'aktivitas',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
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
