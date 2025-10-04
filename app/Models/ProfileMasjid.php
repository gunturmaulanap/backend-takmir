<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini

class ProfileMasjid extends Model
{
    // HAPUS trait HasMasjid dari sini
    // use HasMasjid;

    protected $fillable = [
        'user_id',
        'nama',
        'alamat',
        'image',
        'slug',
    ];

    // Relasi yang benar, dari ProfileMasjid ke User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
