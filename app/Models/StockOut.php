<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $fillable = [
        'stock_out_request_id',
        'stock_out_number',
        'warehouse_id',
        'received_by',
        'approved_by',
        'stock_out_date',
        'note',
    ];
}
