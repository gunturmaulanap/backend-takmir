<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'created_by',
        'updated_by',
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
