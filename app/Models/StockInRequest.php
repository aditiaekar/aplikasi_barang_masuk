<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockInRequest extends Model
{
    protected $fillable = [
        'request_number',
        'supplier_id',
        'warehouse_id',
        'requested_by',
        'request_date',
        'status',
        'note',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_reason',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(StockInRequestItem::class);
    }

    public function stockIn()
    {
        return $this->hasOne(StockIn::class);
    }
}