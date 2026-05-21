<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInRequestItem extends Model
{
    protected $guarded = [];

    public function stockInRequest()
    {
        return $this->belongsTo(StockInRequest::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}