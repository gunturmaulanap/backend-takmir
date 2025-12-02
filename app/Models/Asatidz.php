<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Traits\HasMasjid; // Import trait
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini




class Asatidz extends Model
{
    use HasMasjid;

    protected $fillable = [
        'nama',
        'profile_masjid_id',
        'no_handphone',
        'alamat',
        'jenis_kelamin',
        'created_by',
        'updated_by',
    ];
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($image) => $image ? url('/storage/photos/' . $image) : null,
        );
    }

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
