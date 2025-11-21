<?php


namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable, HasFactory, HasRoles;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getPermissionArray()
    {
        return $this->getAllPermissions()->mapWithKeys(function ($pr) {
            return [$pr['name'] => true];
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // relasi untuk role 'admin'
    public function profileMasjid(): HasOne
    {
        return $this->hasOne(ProfileMasjid::class);
    }

    // relasi untuk role 'takmir'
    public function takmir(): HasOne
    {
        return $this->hasOne(Takmir::class);
    }

    // relasi 'hasOneThrough' untuk mengakses profile masjid dari user takmir
    public function takmirProfileMasjid(): HasOneThrough
    {
        return $this->hasOneThrough(ProfileMasjid::class, Takmir::class, 'user_id', 'id', 'id', 'profile_masjid_id');
    }

    // relasi dengan refresh tokens
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    /**
     * Dapatkan profil masjid yang terkait dengan user, terlepas dari rolenya.
     *
     * @return \App\Models\ProfileMasjid|null
     */
    public function getMasjidProfile()
    {
        if ($this->roles->contains('name', 'admin')) {
            return $this->profileMasjid;
        }

        if ($this->roles->contains('name', 'takmir')) {
            return $this->takmirProfileMasjid;
        }

        return null;
    }

    /**
     * Scope untuk mendapatkan user yang aktif saja
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
