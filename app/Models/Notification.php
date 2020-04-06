<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\UserResource;

class Notification extends Model
{
    protected $table = "notification";


    public function user(){
        return UserResource::collection($this->hasOne('App\Models\User','id','created_by')->get())->first();
    }
}
