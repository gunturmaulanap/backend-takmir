<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasMasjid; // Import trait
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini


class Muadzin extends Model
{
    use HasMasjid;
    protected $fillable = [
        'profile_masjid_id',
        'nama',
        'slug',
        'no_handphone',
        'alamat',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public function profileMasjid()
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
