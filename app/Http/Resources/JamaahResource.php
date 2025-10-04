<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JamaahResource extends JsonResource
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
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'profile_masjid' => [
                'id' => $this->profileMasjid?->id,
                'nama' => $this->profileMasjid?->nama,
            ],
            'slug' => $this->slug,
            'nama' => $this->nama,
            'no_handphone' => $this->no_handphone,
            'alamat' => $this->alamat,
            'umur' => $this->umur,
            'jenis_kelamin' => $this->jenis_kelamin,
            'aktivitas_jamaah' => $this->aktivitas_jamaah,
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
