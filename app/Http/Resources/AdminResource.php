<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'username'      => $this->username,
            'email'         => $this->email,
            'is_active'     => $this->is_active,
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),
            'roles'         => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id'    => $role->id,
                        'name'  => $role->name,
                    ];
                });
            }),
            'profile_masjid' => $this->whenLoaded('profileMasjid', function () {
                return $this->profileMasjid ? [
                    'id'        => $this->profileMasjid->id,
                    'nama'      => $this->profileMasjid->nama,
                    'slug'      => $this->profileMasjid->slug,
                    'alamat'    => $this->profileMasjid->alamat,
                    'image'     => $this->profileMasjid->image ? asset('storage/photos/' . $this->profileMasjid->image) : null,
                ] : null;
            }),
        ];
    }
}