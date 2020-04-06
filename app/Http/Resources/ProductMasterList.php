<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductMasterList extends JsonResource
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
            'product_code'                  =>  $this->product_code,
            'product_name'                  =>  $this->product_name,
            'category'                      =>  $this->category()
        ];
    }
}
