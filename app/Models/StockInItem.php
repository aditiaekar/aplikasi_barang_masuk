<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInItem extends Model
{
    protected $fillable = [
        'stock_in_id',
        'item_id',
        'unit_id',
        'quantity',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Relasi detail barang ke realisasi barang masuk.
     */
    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class);
    }

    /**
     * Relasi detail realisasi ke barang.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relasi detail realisasi ke satuan barang.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}