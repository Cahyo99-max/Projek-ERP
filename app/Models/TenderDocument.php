<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderDocument extends Model
{
    use HasFactory;

    protected $table = 'tender_documents';

    protected $fillable = [
        'tender_id',
        'nama_file',
        'jenis_dokumen',
        'path',
    ];

    public function tender()
    {
        return $this->belongsTo(Tender::class, 'tender_id');
    }
}
