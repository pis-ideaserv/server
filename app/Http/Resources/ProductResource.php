<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'id'                            =>  $this->id,
            'supplier'                      =>  $this->supplier(),
            'product'                       =>  $this->product(),
            'delivery_date'                 =>  $this->delivery_date,
            'reference_delivery_document'   =>  $this->reference_delivery_document,
            'serial_number'                 =>  $this->serial_number,
            'warranty'                      =>  $this->warranty,
            'warranty_start'                =>  $this->warranty_start,
            'warranty_end'                  =>  $this->warranty_end,
            'status'                        =>  $this->status,
            'remarks'                       =>  $this->remarks,
            'created_by'                    =>  $this->createdBy(),
            'updated_by'                    =>  $this->updatedBy(),
            'created_at'                    =>  $this->created_at->toDateTimeString(),
            'updated_at'                    =>  $this->updated_at->toDateTimeString(),
        ];
    }
}
