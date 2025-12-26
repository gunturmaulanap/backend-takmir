<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImamDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'profile_masjid' => [
                'id' => $this->profileMasjid?->id,
                'nama' => $this->profileMasjid?->nama,
            ],
            'nama' => $this->nama,
            'slug' => $this->slug,
            'no_handphone' => $this->no_handphone,
            'alamat' => $this->alamat,
            'tugas' => $this->tugas,
            'is_active' => (bool) $this->is_active,
            'is_active_label' => $this->is_active ? 'Aktif' : 'Tidak Aktif',
            'created_by' => [
                'id' => $this->createdBy?->id,
                'name' => $this->createdBy?->name,
            ],
            'updated_by' => [
                'id' => $this->updatedBy?->id,
                'name' => $this->updatedBy?->name,
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}