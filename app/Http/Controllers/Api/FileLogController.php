<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FileLog;
use App\Http\Resources\FileLogResource;

class FileLogController extends Controller
{
    public function show(Request $request,$filename){
        return FileLogResource::collection(
            FileLog::where('filename','=',$filename)
                    ->paginate($request->per_page != null ? (int)$request->per_page : 10)
        );
    }
}
