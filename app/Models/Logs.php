<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\UserResource;

class Logs extends Model
{
    protected $table = "log";

    public function user(){
        return UserResource::collection($this->hasOne('App\Models\User','id','user')->get())->first();
    }
}
