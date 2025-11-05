<?php

/**
 * @method null|\App\Models\ProfileMasjid getMasjidProfile()
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasMasjid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Takmir extends Model
{
    use HasMasjid;

    protected $fillable = [
        'user_id',
        'profile_masjid_id',
        'nama',
        'slug',
        'is_active',
        'no_handphone',
        'umur',
        'jabatan',
        'deskripsi_tugas',
        'created_by',
        'updated_by',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    public function profileMasjid(): BelongsTo
    {
        return $this->belongsTo(ProfileMasjid::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
