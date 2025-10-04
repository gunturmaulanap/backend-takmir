<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasMasjid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventView extends Model
{
    use HasMasjid;

    protected $fillable = [
        'profile_masjid_id',
        'event_id',
        'jadwal_khutbah_id',
        'title',
        'tanggal',
        'waktu',
        'type',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu' => 'datetime:H:i',
    ];

    public function profileMasjid(): BelongsTo
    {
        return $this->belongsTo(ProfileMasjid::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function jadwalKhutbah(): BelongsTo
    {
        return $this->belongsTo(JadwalKhutbah::class);
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
     * Scope untuk filter berdasarkan bulan dan tahun
     */
    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);
    }

    /**
     * Scope untuk filter berdasarkan tipe
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
