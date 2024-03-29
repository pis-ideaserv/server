<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\Category as CategoryResources;
use Validator;
use App\Helpers\Utils;
use App\Models\Logs;
use App\Http\Resources\SnapshotResource;
use Status;

class CategoryController extends Controller
{
    public function index(Request $request){

        if($request->search){
            $query = Category::where('name','like','%'.$request->search.'%')->paginate(20);
            return CategoryResources::collection($query);
        }

        if($request->snapshot != null && is_numeric($request->snapshot)) {
            $id = (int)$request->snapshot;
            $per_page = $request->per_page != null ? (int)$request->per_page : 1000;
            $log = Logs::orderBy('updated_at', 'desc')->first();
            
            return SnapshotResource::collection(
                Logs::where('id','>',$id)
                    ->where('target','=','Category')
                    ->paginate($per_page)
            )->additional(['snapshot' => $log !== null ? $log->id : 0]);
        }

        $per_page = $request->per_page != null ? (int)$request->per_page : 10;
        $log = Logs::orderBy('updated_at', 'desc')->first();
        return CategoryResources::collection(Category::orderBy('updated_at', 'desc')->paginate($per_page))->additional(['snapshot' => $log !== null ? $log->id : 0]);
    }

    public function show($id){
    	return CategoryResources::collection(Category::where('id','=',$id)->get())->first();
    }

    public function store(Request $request){


		$validator = Validator::make($request->all(), [
	        'name'                 	=>  'required',
	    ]);
    
		if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return [
                "status" => false,
                "errors" => Utils::RemakeArray($a)
            ];
        }


        $la = Category::where('name','=',$request->name)->get();

        if(sizeof($la) != 0){
            return [
                "status"    => false,
                'message'   => 'Category name exists'
            ];
        }


        $cat = new Category();
        $cat->name = $request->name;
        $cat->save();

        return [
            "message" => 'Product category successfully created'
        ];

    }

    public function update(Request $request, $id){
    	$category = Category::find($id);

        if($category == null){
            return response()->json(['message' => 'Product category not found'], Status::HTTP_NOT_FOUND);
        }

		$validator = Validator::make($request->all(), [
	        'name'                 	=>  'required',
	    ]);
    
		if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return [
                "status" => false,
                "errors" => Utils::RemakeArray($a)
            ];
        }

        
        $category->name = $request->name;
        $category->save();

        return response()->json([
            "message" => 'Product category successfully updated'
        ]);        

    }
    public function destroy($id){

    	$category = Category::find($id);

        if($category == null){
            return [
                "status" => false,
                'message' => 'Product category not found'
            ];
        }

        try{
        	$category->delete();	
        }catch(\Illuminate\Database\QueryException $e){
        	return [
                "status"    => false,
                'message'   => 'Cannot delete, currently linked'
            ];
        }
        

        return response()->json([
            "message" => 'Product category  successfully deleted'
        ]);

    }
}
