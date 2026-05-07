<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInRequestItem extends Model
{
    protected $fillable = [
        'stock_in_request_id',
        'item_id',
        'unit_id',
        'quantity',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Relasi detail barang ke pengajuan barang masuk.
     */
    public function stockInRequest(): BelongsTo
    {
        return $this->belongsTo(StockInRequest::class);
    }

    /**
     * Relasi detail pengajuan ke barang.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relasi detail pengajuan ke satuan barang.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}