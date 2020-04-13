<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\LogResource;
use App\Models\Logs;
use App\Http\Resources\SnapshotResource;
use App\Helpers\Utils;
use Status;
use Validator;


class LogController extends Controller
{
	public function index(Request $request){
		
		if($request->snapshot != null && is_numeric($request->snapshot)) {
            $id = (int)$request->snapshot;
            $per_page = $request->per_page != null ? (int)$request->per_page : 1000;

            if($id == 0) return LogResource::collection(Logs::orderBy('updated_at', 'desc')->paginate($per_page));

            return LogResource::collection(
                Logs::where('id','>', $id)
                    ->orderBy('updated_at', 'desc')
                    ->paginate($per_page)
            );
        }

		$per_page = $request->per_page != null ? (int)$request->per_page : 10;
		return LogResource::collection(Logs::orderBy('updated_at', 'desc')->paginate($per_page));
	}
}
