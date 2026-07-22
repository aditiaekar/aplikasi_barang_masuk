<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'recipient_name',
        'recipient_postal_code',
        'recipient_address',
        'recipient_phone',
        'ems_number',
        'sender_name',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockOutItem::class);
    }
}
