<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use File;

class FileUploadController extends Controller
{
	public function upload(){
        $response = app('tus-server')->serve();
        $response->send();
        return null;
    }

    public function  index($name){
        
        $file =storage_path()."/app/temp/".$name;

        if(!File::exists($file)){
            return abort(404);
        }

        return Response::make(File::get($file).$name,200)
            ->header('Content-Type',File::mimeType($file));
    }

    public function destroy($name){
        File::delete(storage_path()."/app/temp/".$name);
    }    
}
