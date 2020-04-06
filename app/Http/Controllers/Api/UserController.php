<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Helpers\Utils;
use Validator;
use Status;
use DB;

class UserController extends Controller
{
    public function __construct(){
        // $this->middleware('adminOnlyPermission');
    }

    public function index(Request $request){

        if($request->filter){
            $filt = json_decode($request->filter);

            if(!is_object($filt)){
                return response()->json([
                    "errors" => "Filter must be an object"
                ],Status::HTTP_NOT_ACCEPTABLE);
            }

            $filter = [
                'username'      =>  property_exists($filt,'username') ? $filt->username : null,
                'company'       =>  property_exists($filt,'company') ? $filt->company : null,
                'email'         =>  property_exists($filt,'email') ? $filt->email : null,
                'level'         =>  property_exists($filt,'level') ? $filt->level : null,
                'activated'     =>  property_exists($filt,'activated') ? $filt->activated : null,
            ];

            $where = [];
            
            foreach ($filter as $key => $value) {
                // dd($filt);
                if($value != null){
                    if($key == 'level' || $key == 'activated'){
                        if($key == 'activated'){
                            if($value->key == 2){
                                array_push($where, [$key, '=',0]);
                            }else{
                                array_push($where, [$key, '=',$value->key]);
                            }
                        }else{
                            array_push($where, [$key, '=',$value->key]);
                        }
                    }else{
                         switch($value->filter){
                            case "iet" :
                                array_push($where, [$key, '=',$value->key]);
                                break;
                            case "inet" :
                                array_push($where, [$key, '!=',$value->key]);
                                break;
                            case "c" :
                                array_push($where, [$key, 'like','%'.$value->key.'%']);
                                break;
                            case "dnc" :
                                array_push($where, [$key, 'not like','%'.$value->key.'%']);
                                break;
                            case "sw" :
                                array_push($where, [$key, 'like',$value->key.'%']);
                                break;
                            case "ew" :
                                array_push($where, [$key, 'like','%'.$value->key]);
                                break;
                        }
                    }
                }
            }

            if($filt->name != null){
                
                $name = [];

                switch ($filt->name->filter) {
                    case "iet" :
                        array_push($name, [DB::raw('concat(first_name," ",last_name)'), '=',$filt->name->key]);
                        break;
                    case "inet" :
                        array_push($name, [DB::raw('concat(first_name," ",last_name)'), '!=',$filt->name->key]);
                        break;
                    case "c" :
                        array_push($name, [DB::raw('concat(first_name," ",last_name)'), 'like','%'.$filt->name->key.'%']);
                        break;
                    case "dnc" :
                        array_push($name, [DB::raw('concat(first_name," ",last_name)'), 'not like','%'.$filt->name->key.'%']);
                        break;
                    case "sw" :
                        array_push($name, [DB::raw('concat(first_name," ",last_name)'), 'like',$filt->name->key.'%']);
                        break;
                    case "ew" :
                        array_push($name, [DB::raw('concat(first_name," ",last_name)'), 'like','%'.$filt->name->key]);
                        break;
                }

                $user = User::where($where)
                        ->where($name)
                        ->orderBy('updated_at', 'desc');
            }else{
                $user = User::where($where)
                        ->orderBy('updated_at', 'desc');
            }

            if($request->per_page != null){
                $per_page = (int)$request->per_page;
                return UserResource::collection($user->paginate($per_page));
            }

            return UserResource::collection($user->paginate(10));
        }

        if($request->per_page != null){
            $per_page = (int)$request->per_page;
            return UserResource::collection(User::orderBy('updated_at', 'desc')->paginate($per_page));
        }

        return UserResource::collection(User::orderBy('updated_at', 'desc')->paginate(10));
    }


    public function show($id){
        return UserResource::collection(User::where('id','=',$id)->get())->first();
    }


    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'username'         =>  'required|unique:user,username',
            'first_name'        =>  'required',
            'last_name'         =>  'required',
            'email'             =>  'required|email|unique:user,email',
            'company'           =>  'required',
            'activated'         =>  'required',
            'level'             =>  'required',
            'password'          =>  'required'
        ]);

        if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return response()->json([
                "errors" => Utils::RemakeArray($a)
            ],Status::HTTP_NOT_ACCEPTABLE);
        }

        $user = new User();
        $user->username         = $request->username;
        $user->first_name       = $request->first_name;
        $user->last_name        = $request->last_name;
        $user->email            = $request->email;
        $user->company          = $request->company;
        $user->level            = $request->level;
        $user->activated        = $request->activated;
        $user->password         = bcrypt($request->password);

        $user->save();

        return response()->json([
            "message" => "User successfully created",
        ]);

    }

    public function update(Request $request,$id){

        $user = User::find($id);

        if($user == null){
            return response()->json(['message' => 'User not found'], Status::HTTP_NOT_FOUND);
        }


        $validator = Validator::make($request->all(), [
            'username'          =>  'required|unique:user,username,'.$id,
            'first_name'        =>  'required',
            'last_name'         =>  'required',
            'email'             =>  'required|email|unique:user,email,'.$id,
            'company'           =>  'required',
            // 'activated'         =>  'required',
            // 'level'             =>  'required',   
        ]);

        if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return response()->json([
                "errors" => Utils::RemakeArray($a)
            ],Status::HTTP_NOT_ACCEPTABLE);
        }


        $user->username         = $request->username;
        $user->first_name       = $request->first_name;
        $user->last_name        = $request->last_name;
        $user->email            = $request->email;
        $user->company          = $request->company;
        
        if($request->activated) 
            $user->activated = $request->activated;

        if($request->level) 
            $user->level = $request->level;

        if($request->password != null){
            $user->password     = bcrypt($request->password);
        }


        $user->save();

        return response()->json([
            "message" => 'User successfully updated'
        ]);
    }

    public function destroy($id){
        $user = User::find($id);

        if($user == null){
            return response()->json(['message' => 'User not found'], Status::HTTP_NOT_FOUND);
        }

        $user->delete();

        return response()->json([
            "message" => 'User successfully deleted'
        ]);
    }
}
