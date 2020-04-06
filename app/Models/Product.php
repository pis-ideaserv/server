<?php

namespace App\Models;

use App\Http\Resources\SupplierResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ProductMasterList;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "product";


    public function product(){
        return ProductMasterList::collection($this->hasOne('App\Models\ProductMasterList','id','product')->get())->first(); 
    }

    public function supplier(){
        return SupplierResource::collection($this->hasOne('App\Models\Supplier','id','supplier')->get())->first();
    }

    public function createdBy(){
        return UserResource::collection($this->hasOne('App\Models\User','id','created_by')->get())->first();
    }

    public function updatedBy(){
        return UserResource::collection($this->hasOne('App\Models\User','id','updated_by')->get())->first();
    }
}
