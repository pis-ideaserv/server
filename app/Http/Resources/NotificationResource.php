<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            =>  $this->id,
            'user'          =>  $this->user,
            'status'        =>  $this->status,
            'type'          =>  $this->type,
            'filename'      =>  $this->filename,
            'result'        =>  json_decode($this->result),
            'created_at'    =>  $this->created_at->toDateTimeString(),
            'updated_at'    =>  $this->updated_at->toDateTimeString(),
        ];
    }
}
