<?php

namespace App\Http\Resources; // Ganti namespace yang salah

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'success'   => $this->status,
            'message'   => $this->message,
            'data'      => $this->resource
        ];
    }
}