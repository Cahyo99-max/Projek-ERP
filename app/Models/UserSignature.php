<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSignature extends Model
{
    protected $table = 'user_signatures';

    protected $fillable = [
        'user_id',
        'signature_path',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSignatureUrlAttribute()
    {
        if (empty($this->signature_path)) return null;

        $path = $this->signature_path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:image')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        $storagePath = storage_path('app/public/' . preg_replace('/^storage\//', '', $cleanPath));
        if (file_exists($storagePath)) {
            return asset(str_starts_with($cleanPath, 'storage/') ? $cleanPath : 'storage/' . $cleanPath);
        }

        return asset($cleanPath);
    }

    public function getSignatureBase64Attribute()
    {
        if (empty($this->signature_path)) return null;

        $cleanPath = ltrim($this->signature_path, '/');
        $fullPath = public_path($cleanPath);

        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/public/' . preg_replace('/^storage\//', '', $cleanPath));
        }

        if (file_exists($fullPath)) {
            $mime = mime_content_type($fullPath) ?: 'image/png';
            $data = file_get_contents($fullPath);
            return 'data:' . $mime . ';base64,' . base64_encode($data);
        }

        return $this->signature_url;
    }
}
