<?php

/**
 * @method null|\App\Models\ProfileMasjid getMasjidProfile()
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Traits\HasMasjid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Takmir extends Model
{
    use HasMasjid;

    protected $fillable = [
        'user_id',
        'profile_masjid_id', // Pastikan ini ada
        'nama',
        'no_handphone',
        'umur',
        'jabatan',
        'deskripsi_tugas',
        'image',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($image) => $image ? url('/storage/photos/' . $image) : null,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    public function profileMasjid(): BelongsTo
    {
        return $this->belongsTo(ProfileMasjid::class);
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
