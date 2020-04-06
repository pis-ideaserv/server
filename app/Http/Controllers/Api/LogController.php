<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\LogResource;
use App\Models\Logs;
use App\Helpers\Utils;
use Status;
use Validator;

class LogController extends Controller
{
	public function index(Request $request){

		$validator = Validator::make($request->all(), [
	        'per_page'                 	=>  'required',
	    ]);
    
		if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return response()->json([
                "errors" => Utils::RemakeArray($a)
            ],Status::HTTP_NOT_ACCEPTABLE);
        }


		return LogResource::collection(Logs::orderBy('updated_at', 'desc')->paginate($request->per_page));
	}
}
