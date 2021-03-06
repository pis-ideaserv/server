<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Helpers\Utils;
use Status;
use Auth;
use Artisan;
use Validator;
use Illuminate\Validation\Rule;

class NotificationController extends Controller{

	public function __construct(){
		// $this->middleware('adminOnlyPermission');
	}
	
	public function index(Request $request){

		return NotificationResource::collection(
			Notification::where('user','=',Auth::user()->id)
						->orderBy('updated_at', 'desc')
						->paginate($request->per_page != null ? (int)$request->per_page : 10)
		);
	}

	public function show($id){
		return NotificationResource::collection(Notification::where('id','=',$id)->get())->first();
	}

	public function store(Request $request){

		$validator = Validator::make($request->all(), [
	        'filename'	=> 'required',
	        'type'		=> ['required',Rule::in(['product', 'masterfile','supplier'])],
	    ]);

		if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return [
				"status" => false,
                "errors" => Utils::RemakeArray($a)
            ];
		}
		
		$notify = new Notification();
		$notify->user = Auth::user()->id;
		$notify->type = $request->type;
		$notify->status = "queue";
		$notify->filename = $request->filename;
		$notify->save();

		$path = exec("realpath ../artisan");
		$command = 'php '.$path.' process:uploads';

		$process = shell_exec("ps aux | grep 'process:uploads' | grep -v grep | awk '{print $2}'");
        if($process == null){
            shell_exec("nohup ".$command." > /dev/null 2>&1 &");
        }
		
		return [
			"message" => "success"
		];
	}
}
