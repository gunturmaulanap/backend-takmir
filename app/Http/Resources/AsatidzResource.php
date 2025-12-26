<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsatidzResource extends JsonResource
{
    /**
     * @var bool
     */
    public $status;
    /**
     * @var string
     */
    public $message;

    /**
     * __construct
     *
     * @param  bool  $status
     * @param  string  $message
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($status, $message, $resource)
    {
        parent::__construct($resource);
        $this->status  = $status;
        $this->message = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success'   => $this->status,
            'message'   => $this->message,
            'data'      => [
                'id' => $this->id,
                'nama' => $this->nama,
                'slug' => $this->slug,
                'no_handphone' => $this->no_handphone,
                'alamat' => $this->alamat,
                'umur' => $this->umur,
                'jenis_kelamin' => $this->jenis_kelamin,
                'keahlian' => $this->keahlian,
                'keterangan' => $this->keterangan,
                'jumlah_murid_tpq' => $this->when($this->relationLoaded('murid') && isset($this->murid), function() {
                    return $this->murid->count();
                }, 0),
                'murid' => $this->when($this->relationLoaded('murid') && isset($this->murid), function() {
                    return $this->murid->map(function($murid) {
                        return [
                            'id' => $murid->id,
                            'nama' => $murid->nama,
                            'no_handphone' => $murid->no_handphone,
                            'umur' => $murid->umur,
                            'jenis_kelamin' => $murid->jenis_kelamin,
                            'aktivitas_jamaah' => $murid->aktivitas_jamaah,
                        ];
                    });
                }, []),
                'profile_masjid_id' => $this->profile_masjid_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }

    /**
     * Custom response for collection
     */
    public static function collection($resource)
    {
        return [
            'success' => true,
            'message' => 'List Data Asatidz',
            'data' => collect($resource->items())->map(function ($asatidz) {
                return [
                    'id' => $asatidz->id,
                    'nama' => $asatidz->nama,
                    'slug' => $asatidz->slug,
                    'no_handphone' => $asatidz->no_handphone,
                    'alamat' => $asatidz->alamat,
                    'umur' => $asatidz->umur,
                    'jenis_kelamin' => $asatidz->jenis_kelamin,
                    'keahlian' => $asatidz->keahlian,
                    'keterangan' => $asatidz->keterangan,
                    'jumlah_murid_tpq' => $asatidz->relationLoaded('murid') && isset($asatidz->murid) ? $asatidz->murid->count() : 0,
                    'murid' => $asatidz->relationLoaded('murid') && isset($asatidz->murid) ? $asatidz->murid->map(function($murid) {
                        return [
                            'id' => $murid->id,
                            'nama' => $murid->nama,
                            'no_handphone' => $murid->no_handphone,
                            'umur' => $murid->umur,
                            'jenis_kelamin' => $murid->jenis_kelamin,
                            'aktivitas_jamaah' => $murid->aktivitas_jamaah,
                        ];
                    }) : [],
                    'profile_masjid_id' => $asatidz->profile_masjid_id,
                    'created_at' => $asatidz->created_at,
                    'updated_at' => $asatidz->updated_at,
                ];
            }),
            'pagination' => [
                'current_page' => $resource->currentPage(),
                'last_page' => $resource->lastPage(),
                'per_page' => $resource->perPage(),
                'total' => $resource->total(),
                'from' => $resource->firstItem(),
                'to' => $resource->lastItem(),
            ]
        ];
    }
}
