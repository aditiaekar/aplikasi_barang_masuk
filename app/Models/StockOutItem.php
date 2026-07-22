<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class StockOutItem extends Model
{
    protected $fillable = [
        'stock_out_id',
        'item_id',
        'unit_id',
        'quantity',
        'unit_price',
        'total_price',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    /**
     * Relasi detail barang ke realisasi barang masuk.
     */
    public function stockOut(): BelongsTo
    {
        return $this->belongsTo(StockOut::class);
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

    public function stockOutItemLayers(): HasMany {
        return $this->hasMany(StockOutItemLayer::class);
    }
}
