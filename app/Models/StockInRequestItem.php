<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInRequestItem extends Model
{
    protected $fillable = [
        'stock_in_request_id',
        'item_id',
        'unit_id',
        'quantity',
        'price',
        'note',
    ];

    public function stockInRequest()
    {
        return $this->belongsTo(StockInRequest::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
