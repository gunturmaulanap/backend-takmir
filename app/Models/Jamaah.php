<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasMasjid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use App\Models\ProfileMasjid;


class Jamaah extends Model
{
    use HasMasjid;
    protected $fillable = [
        'profile_masjid_id',
        'nama',
        'no_handphone',
        'alamat',
        'umur',
        'slug',
        'jenis_kelamin',
        'aktivitas_jamaah',
        'created_by',
        'updated_by',
    ];

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

    /**
     * Relasi many-to-many dengan Asatidz
     * Jamaah bisa memiliki banyak asatidz (guru)
     */
    public function asatidz(): BelongsToMany
    {
        return $this->belongsToMany(Asatidz::class, 'asatidz_jamaah', 'jamaah_id', 'asatidz_id')
            ->withTimestamps()
            ->withPivot('created_at', 'updated_at');
    }

    /**
     * Cek apakah jamaah adalah murid TPQ
     */
    public function isMuridTPQ(): bool
    {
        return str_contains($this->aktivitas_jamaah, 'TPQ');
    }
}

