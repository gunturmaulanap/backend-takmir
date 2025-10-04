<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MuadzinResource extends JsonResource
{
    /**
     * Indicates if the resource's collection keys should be preserved.
     *
     * @var bool
     */
    public $preserveKeys = true;

    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'data';

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Create a custom response with additional data.
     *
     * @param  bool  $success
     * @param  string  $message
     * @param  mixed  $resource
     * @return array
     */
    public static function customResponse($success, $message, $resource)
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $resource
        ];
    }

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
    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'success' => $this->additional['success'] ?? true,
            'message' => $this->additional['message'] ?? '',
        ];
    }
}
