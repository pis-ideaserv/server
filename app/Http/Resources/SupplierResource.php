<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'id'                =>  $this->id,
            'supplier_code'     =>  $this->supplier_code,
            'supplier_name'     =>  $this->supplier_name,
            'address'           =>  $this->address,
            'tin'               =>  $this->tin,
            'contact_person'    =>  $this->contact_person,
            'contact_number'    =>  $this->contact_number,
            'email'             =>  $this->email,
            'created_at'        =>  $this->created_at->toDateTimeString(),
            'updated_at'        =>  $this->updated_at->toDateTimeString(),
        ];
    }
}
