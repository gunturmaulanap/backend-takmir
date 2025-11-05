<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasMasjid;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini



class Category extends Model
{
    use HasMasjid;
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'nama',
        'slug',
        'warna',
        'deskripsi',
        'profile_masjid_id',
        'created_by',
        'updated_by',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
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
