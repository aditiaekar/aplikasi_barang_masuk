<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOutRequest extends Model
{
    protected $fillable = [
        'request_number',
        'warehouse_id',
        'requested_by',
        'request_date',
        'status',
        'note',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_reason',
        'recipient_name',
        'recipient_postal_code',
        'recipient_address',
        'recipient_phone',
        'ems_number',
        'sender_name',

    ];

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
        return $this->hasMany(StockOutRequestItem::class);
    }

    public function stockOut()
    {
        return $this->hasOne(StockOut::class);
    }
}
