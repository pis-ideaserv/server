<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductMasterList as PMF;
use App\Models\ProductMasterList as PF;
use Validator;
use App\Helpers\Utils;
use Status;
use App\Models\Category;
use App\Imports\ExcelSheet;
use App\Models\Logs;
use App\Http\Resources\SnapshotResource;


class ProductMasterListController extends Controller
{
    public function index(Request $request){

        if($request->filter){
            $filt = json_decode($request->filter);

            if(!is_object($filt)){
                return [
                    "status" => false,
                    "errors" => "Filter must be an object"
                ];
            }

            $filter = [
                'product_code'          =>  property_exists($filt,$filt->product_code) ? $filt->product_code : null,
                'product_name'          =>  property_exists($filt,$filt->product_name) ? $filt->product_name : null,
                'category'              =>  property_exists($filt,$filt->category) ? $filt->category : null
            ];


            $where = [];

            foreach ($filter as $key => $value) {
                if($value != null){
                    switch($value->filter){
                        case "iet" :
                            if($key == "category"){
                                array_push($where, ['name', '=',$value->key]);
                            }else{
                                array_push($where, [$key, '=',$value->key]);
                            }
                            
                            break;
                        case "inet" :
                            if($key == "category"){
                                array_push($where, ['name', '!=',$value->key]);
                            }else{
                                array_push($where, [$key, '!=',$value->key]);
                            }
                            break;
                        case "c" :
                            if($key == "category"){
                                array_push($where, ['name', 'like','%'.$value->key.'%']);
                            }else{
                                array_push($where, [$key, 'like','%'.$value->key.'%']);
                            }
                            break;
                        case "dnc" :
                            if($key == "category"){
                                array_push($where, ['name', 'not like','%'.$value->key.'%']);
                            }else{
                                array_push($where, [$key, 'not like','%'.$value->key.'%']);
                            }
                            break;
                        case "sw" :
                            if($key == "category"){
                                array_push($where, ['name', 'like',$value->key.'%']);
                            }else{
                                array_push($where, [$key, 'like',$value->key.'%']);
                            }
                            break;
                        case "ew" :
                            if($key == "category"){
                                array_push($where, ['name', 'like','%'.$value->key]);
                            }else{
                                array_push($where, [$key, 'like','%'.$value->key]);
                            }
                            break;
                    }
                }
            }

            $per_page = $request->per_page != null ? (int)$request->per_page : 10;
            return PMF::collection(
                PF::select('product_master_list.*')
                    ->leftJoin('category as cat','product_master_list.category','=','cat.id')
                    ->where($where)
                    ->orderBy('product_master_list.updated_at', 'desc')
                    ->paginate($per_page)
            );
        }

        if($request->search){
            $query = PF::where('product_code','like','%'.$request->search.'%')
                    // ->orWhere('product_name','like','%'.$request->search.'%')
                    ->paginate(10);
            return PMF::collection($query);
        }

        if($request->snapshot != null && is_numeric($request->snapshot)) {
            $id = (int)$request->snapshot;
            $per_page = $request->per_page != null ? (int)$request->per_page : 1000;

            if($id == 0) return PMF::collection(PF::orderBy('updated_at', 'desc')->paginate($per_page));

            return SnapshotResource::collection(
                Logs::where('id','>',$id)
                    ->where('target','=','ProductMasterList')
                    ->paginate($per_page)
            );
        }

        $per_page = $request->per_page != null ? (int)$request->per_page : 10;
        return PMF::collection(PF::orderBy('updated_at', 'desc')->paginate($per_page));
    }

    public function show($id){
    	return PMF::collection(PF::where('id','=',$id)->get())->first();
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'product_code'                 	=>  'required|unique:product_master_list,product_code',
            'product_name'                  =>  'required',
            'category'           			=>  'required',
        ]);

        if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return [
                "status" => false,
                "errors" => Utils::RemakeArray($a)
            ];
        }


        $pa = new PF();
        $pa->product_code = $request->product_code;
        $pa->product_name = $request->product_name;
        $pa->category = $request->category;
        $pa->save();

        return response()->json([
            "message" => "Product Master List successfully created",
        ]);
    }

    public function update(Request $request, $id){

    	$pa = PF::find($id);

        if($pa == null){
            return response()->json(['message' => 'Product not found'], Status::HTTP_NOT_FOUND);
        }

		$validator = Validator::make($request->all(), [
            'product_code'                 	=>  'required|unique:product_master_list,product_code,'.$id,
            'product_name'                  =>  'required',
            'category'           			=>  'required',
        ]);

		if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return [
                "status" => false,
                "errors" => Utils::RemakeArray($a)
            ];
        }

       	$pa->product_code = $request->product_code;
       	$pa->product_name = $request->product_name;
       	$pa->category = $request->category;
       	$pa->save();

       	return response()->json([
            "message" => "Product Master List successfully updated",
        ]);
    }

    public function destroy($id){
    	$product = PF::find($id);

        if($product == null){
            return [
                'status'  => false,
                'message' => 'Product not found'
            ];
        }

        try{
            $product->delete();    
        }catch(\Illuminate\Database\QueryException $e){
            return [
                'status'  => false,
                'message' => 'Cannot delete, currently linked'
            ];
        }

        return [
            "message" => 'Product successfully deleted'
        ];
    }	
}
  