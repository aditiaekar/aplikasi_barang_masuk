<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIn extends Model
{
    protected $fillable = [
        'stock_in_request_id',
        'stock_in_number',
        'supplier_id',
        'warehouse_id',
        'received_by',
        'approved_by',
        'stock_in_date',
        'note',
    ];

    protected $casts = [
        'stock_in_date' => 'date',
    ];

    /**
     * Relasi realisasi barang masuk ke pengajuan barang masuk.
     */
    public function stockInRequest(): BelongsTo
    {
        return $this->belongsTo(StockInRequest::class);
    }

    /**
     * Relasi realisasi barang masuk ke supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi realisasi barang masuk ke gudang tujuan.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relasi ke user yang mencatat atau menerima barang masuk.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Relasi ke user yang menyetujui barang masuk.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi realisasi barang masuk dengan detail barang.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockInItem::class);
    }

    /**
     * Relasi realisasi barang masuk dengan mutasi stok.
     */
    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }
}