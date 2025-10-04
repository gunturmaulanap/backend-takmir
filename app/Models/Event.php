<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Traits\HasMasjid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasMasjid;
    protected $fillable = [
        'category_id',
        'profile_masjid_id',
        'nama',
        'slug',
        'tanggal_event',
        'waktu_event',
        'tempat_event',
        'deskripsi',
        'image',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_event' => 'date',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn($image) => url('/storage/photos/' . $image),
        );
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
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
