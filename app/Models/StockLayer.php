<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class StockLayer extends Model
{
    protected $fillable = [
        'item_id',
        'warehouse_id',
        'stock_in_item_id',
        'quantity_in',
        'quantity_remaining',
        'price',
        'received_at',
    ];

    protected $casts = [
        'quantity_in' => 'integer',
        'quantity_remaining' => 'integer',
        'price' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockInItem(): BelongsTo
    {
        return $this->belongsTo(StockInItem::class);
    }

    public function stockOutItemLayer(): HasMany {
        return $this->hasMany(StockOutItemLayer::class);
    }
}
