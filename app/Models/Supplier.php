<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi supplier dengan banyak pengajuan barang masuk.
     */
    public function stockInRequests(): HasMany
    {
        return $this->hasMany(StockInRequest::class);
    }

    /**
     * Relasi supplier dengan banyak realisasi barang masuk.
     */
    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }
}