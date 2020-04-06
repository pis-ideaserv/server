<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\Category; 

class ProductMasterList extends Model
{
    protected $table = "product_master_list";


    public function category(){
    	return Category::collection($this->hasOne('App\Models\Category','id','category')->get())->first();
    }
}
