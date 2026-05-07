<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $casts = [
        'request_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Relasi pengajuan barang masuk ke supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi pengajuan barang masuk ke gudang tujuan.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relasi ke user pembuat pengajuan.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Relasi ke user yang menyetujui atau menolak pengajuan.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi pengajuan dengan detail barang masuk.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockInRequestItem::class);
    }
}