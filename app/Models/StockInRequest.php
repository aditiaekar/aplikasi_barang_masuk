<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInRequest extends Model
{
    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(StockInRequestItem::class);
    }

    public function details()
    {
        return $this->hasMany(StockInRequestItem::class);
    }
}