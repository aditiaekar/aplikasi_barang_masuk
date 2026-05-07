<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'username',
        'email',
        'phone',
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi user ke role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relasi user dengan pengajuan barang masuk yang dibuat.
     */
    public function stockInRequests(): HasMany
    {
        return $this->hasMany(StockInRequest::class, 'requested_by');
    }

    /**
     * Relasi user dengan pengajuan barang masuk yang disetujui atau ditolak.
     */
    public function approvedStockInRequests(): HasMany
    {
        return $this->hasMany(StockInRequest::class, 'approved_by');
    }
}
