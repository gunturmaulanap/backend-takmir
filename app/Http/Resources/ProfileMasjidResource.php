<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileMasjidResource extends JsonResource
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
            'user_id'       => $this->user_id,
            'nama'          => $this->nama,
            'slug'          => $this->slug,
            'alamat'        => $this->alamat,
            'image'         => $this->image ? asset('storage/photos/' . $this->image) : null,
            'created_at'    => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at->format('Y-m-d H:i:s'),
            'user'          => $this->whenLoaded('user', function () {
                return [
                    'id'        => $this->user->id,
                    'name'      => $this->user->name,
                    'email'     => $this->user->email,
                    'is_active' => $this->user->is_active,
                ];
            }),
            'created_by'    => $this->whenLoaded('createdBy', function () {
                return [
                    'id'    => $this->createdBy->id,
                    'name'  => $this->createdBy->name,
                ];
            }),
        ];
    }
}