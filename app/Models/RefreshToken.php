<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'revoked',
        'device_info',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    /**
     * Get the user that owns the refresh token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if refresh token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if refresh token is valid (not expired and not revoked)
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->revoked;
    }

    /**
     * Revoke the refresh token
     */
    public function revoke(): bool
    {
        $this->revoked = true;
        return $this->save();
    }
}
