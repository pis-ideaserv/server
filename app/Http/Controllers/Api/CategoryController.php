<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\Category as CategoryResources;
use Validator;
use App\Helpers\Utils;
use App\Models\Logs;
use Status;

class CategoryController extends Controller
{
    public function index(Request $request){

        if($request->search){
            $query = Category::where('name','like','%'.$request->search.'%')->paginate(20);
            return CategoryResources::collection($query);
        }

        if($request->per_page != null) {
            $per_page = (int)$request->per_page;
            return CategoryResources::collection(Category::orderBy('updated_at', 'desc')->paginate($per_page));
        }else{
            return CategoryResources::collection(Category::orderBy('updated_at', 'desc')->paginate(10));
        }
    	
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

            return response()->json([
                "errors" => Utils::RemakeArray($a)
            ],Status::HTTP_NOT_ACCEPTABLE);
        }


        $la = Category::where('name','=',$request->name)->get();

        if(sizeof($la) != 0){
            return response()->json(['message' => 'Category name exists'], Status::HTTP_NOT_ACCEPTABLE);
        }


        $cat = new Category();
        $cat->name = $request->name;
        $cat->save();

        return response()->json([
            "message" => 'Product category successfully created'
        ]);

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

            return response()->json([
                "errors" => Utils::RemakeArray($a)
            ],Status::HTTP_NOT_ACCEPTABLE);
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
            return response()->json(['message' => 'Product category not found'], Status::HTTP_NOT_FOUND);
        }

        try{
        	$category->delete();	
        }catch(\Illuminate\Database\QueryException $e){
        	return response()->json(['message' => 'Cannot delete, currently linked'], Status::HTTP_METHOD_NOT_ALLOWED);
        }
        

        return response()->json([
            "message" => 'Product category  successfully deleted'
        ]);

    }
}
