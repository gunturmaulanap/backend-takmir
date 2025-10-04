<?php

namespace App\Models;

use App\Models\Traits\HasMasjid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini


class JadwalKhutbah extends Model
{
    use HasMasjid;

    protected $table = 'jadwal_khutbahs';

    protected $fillable = [
        'tanggal',
        'hari',
        'imam_id',
        'khatib_id',
        'muadzin_id',
        'profile_masjid_id',
        'is_active',
        'tema_khutbah',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_active' => 'boolean',
    ];

    public function imam()
    {
        return $this->belongsTo(Imam::class, 'imam_id');
    }

    public function khatib()
    {
        return $this->belongsTo(Khatib::class, 'khatib_id');
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function muadzin()
    {
        return $this->belongsTo(Muadzin::class, 'muadzin_id');
    }

    public function profileMasjid(): BelongsTo
    {
        return $this->belongsTo(ProfileMasjid::class, 'profile_masjid_id');
    }
}
