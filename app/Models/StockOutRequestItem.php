<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOutRequestItem extends Model
{
    protected $fillable = [
        'stock_out_request_id',
        'item_id',
        'unit_id',
        'quantity',
        'note',
    ];

    public function stockOutRequest()
    {
        return $this->belongsTo(StockOutRequest::class);
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
