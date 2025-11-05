<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JadwalKhutbahResource extends JsonResource
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
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'tanggal_formatted' => $this->tanggal?->format('d/m/Y'),
            'hari' => $this->hari,
            'day_name' => $this->tanggal?->format('l'),
            'day_name_id' => $this->tanggal?->locale('id')->isoFormat('dddd'),
            'imam' => [
                'id' => $this->imam?->id,
                'nama' => $this->imam?->nama,
                'no_handphone' => $this->imam?->no_handphone,
            ],
            'khatib' => [
                'id' => $this->khatib?->id,
                'nama' => $this->khatib?->nama,
                'no_handphone' => $this->khatib?->no_handphone,
            ],
            'muadzin' => [
                'id' => $this->muadzin?->id,
                'nama' => $this->muadzin?->nama,
                'no_handphone' => $this->muadzin?->no_handphone,
            ],
            'tema_khutbah' => $this->tema_khutbah,
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
