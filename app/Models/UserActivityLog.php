<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'activity',
        'ip_address',
        'user_agent',
        'status',
        'status_message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
