<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'unit_id',
        'item_code',
        'name',
        'barcode',
        'minimum_stock',
        'image',
        'description',
        'is_active',
    ];

    protected $casts = [
        'minimum_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi barang ke kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi barang ke satuan.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi barang dengan stok per gudang.
     */
    public function itemStocks(): HasMany
    {
        return $this->hasMany(ItemStock::class);
    }

    /**
     * Relasi barang dengan detail pengajuan barang masuk.
     */
    public function stockInRequestItems(): HasMany
    {
        return $this->hasMany(StockInRequestItem::class);
    }

    /**
     * Relasi barang dengan detail realisasi barang masuk.
     */
    public function stockInItems(): HasMany
    {
        return $this->hasMany(StockInItem::class);
    }

    /**
     * Relasi barang dengan riwayat mutasi stok.
     */
    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }
}