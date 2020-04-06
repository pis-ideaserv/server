<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return[
            'id'            =>  $this->id,
            'username'      =>  $this->username,
            'first_name'    =>  $this->first_name,
            'last_name'     =>  $this->last_name,
            'email'         =>  $this->email,
            'company'       =>  $this->company,
            'activated'     =>  $this->activated == 0 ? false : true,
            'level'         =>  $this->level,
            'created_at'    =>  $this->created_at->toDateTimeString(),
            'updated_at'    =>  $this->updated_at->toDateTimeString()
        ];
    }
}
