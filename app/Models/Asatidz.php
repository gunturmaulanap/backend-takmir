<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Traits\HasMasjid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asatidz extends Model
{
    use HasMasjid;

    protected $fillable = [
        'profile_masjid_id',
        'nama',
        'slug',
        'no_handphone',
        'alamat',
        'umur',
        'jenis_kelamin',
        'keahlian',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($image) => $image ? url('/storage/photos/' . $image) : null,
        );
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

    /**
     * Relasi many-to-many dengan Jamaah (murid)
     * Asatidz memiliki banyak murid dari jamaah
     */
    public function murid(): BelongsToMany
    {
        return $this->belongsToMany(Jamaah::class, 'asatidz_jamaah', 'asatidz_id', 'jamaah_id')
            ->withTimestamps()
            ->withPivot('created_at', 'updated_at');
    }

    /**
     * Scope untuk mendapatkan murid yang statusnya TPQ
     */
    public function scopeWithMuridTPQ($query)
    {
        return $query->with(['murid' => function ($query) {
            $query->where('aktivitas_jamaah', 'like', '%TPQ%');
        }]);
    }

    /**
     * Get jumlah murid TPQ
     */
    public function getJumlahMuridTPQAttribute(): int
    {
        return $this->murid()->where('aktivitas_jamaah', 'like', '%TPQ%')->count();
    }
}

