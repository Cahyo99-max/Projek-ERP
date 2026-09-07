<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsIssueDetail extends Model
{
    use HasFactory;

    protected $table = 'goods_issue_details';

    protected $fillable = [
        'gi_id',
        'barang_id',
        'jumlah',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
    ];

    public function gi(): BelongsTo
    {
        return $this->belongsTo(GoodsIssue::class, 'gi_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
