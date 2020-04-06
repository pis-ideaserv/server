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


class ProductMasterListController extends Controller
{
    public function index(Request $request){

        if($request->filter){
            $filt = json_decode($request->filter);

            if(!is_object($filt)){
                return response()->json([
                    "errors" => "Filter must be an object"
                ],Status::HTTP_NOT_ACCEPTABLE);
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




            if($request->per_page != null) {
                $per_page = (int)$request->per_page;

                return PMF::collection(
                    PF::select('product_master_list.*')
                        ->leftJoin('category as cat','product_master_list.category','=','cat.id')
                        ->where($where)
                        ->orderBy('product_master_list.updated_at', 'desc')
                        ->paginate($per_page)
                );

            }else{


                return PMF::collection(
                    PF::select('product_master_list.*')
                        ->leftJoin('category as cat','product_master_list.category','=','cat.id')
                        ->where($where)
                        ->orderBy('product_master_list.updated_at', 'desc')
                        ->paginate(10)
                ); 
            }

        }

        if($request->search){
            $query = PF::where('product_code','like','%'.$request->search.'%')
                    // ->orWhere('product_name','like','%'.$request->search.'%')
                    ->paginate(10);
            return PMF::collection($query);
        }

        if($request->per_page != null){

            $per_page = (int)$request->per_page;

    	   return PMF::collection(PF::orderBy('updated_at', 'desc')->paginate($per_page));
        }

        return PMF::collection(PF::orderBy('updated_at', 'desc')->paginate(10));
    }

    public function show($id){
    	return PMF::collection(PF::where('id','=',$id)->get())->first();
    }

    public function store(Request $request){
    	if(!$request->hasFile('file')){
    		$validator = Validator::make($request->all(), [
                'product_code'                 	=>  'required|unique:product_master_list,product_code',
                'product_name'                  =>  'required',
                'category'           			=>  'required',
            ]);

    		if ($validator->fails()){
                $a = $validator->errors()->toArray();

                return response()->json([
                    "errors" => Utils::RemakeArray($a)
                ],Status::HTTP_NOT_ACCEPTABLE);
            }


           	$pa = new PF();
           	$pa->product_code = $request->product_code;
           	$pa->product_name = $request->product_name;
           	$pa->category = $request->category;
           	$pa->save();

           	return response()->json([
                "message" => "Product Master List successfully created",
            ]);

    	}else{
            ini_set('max_execution_time', 0);
            
            $column = 3;
            $array = [];

            $validator = Validator::make($request->all(),[
                'file'      =>      'required|file|max:2000|mimes:xlsx,xls',
            ]);

            if ($validator->fails()){
                $a = $validator->errors()->toArray();

                return response()->json([
                    "errors" => Utils::RemakeArray($a)
                ],Status::HTTP_NOT_ACCEPTABLE);
            }


            //process excel
            $a = (new ExcelSheet)->toCollection($request->file('file'));

            //check format if empty
            if(sizeof($a->toArray()[0]) < 2){
                return response()->json([
                    'errors' => [
                        "message" => "Sheet file is empty!!",
                    ]
                ],Status::HTTP_NOT_ACCEPTABLE);
            }


            //check column format
            for($i=1;$i<sizeof($a->toArray()[0]);$i++){

                $row = $a->toArray()[0][$i];
                
                for($y=0;$y<sizeof($row);$y++){
                    if($y+1 > $column && $row[$y] != null){
                        return response()->json([
                            'errors' => [
                                "message" => "Sheet column format is invalid!!",
                            ]   
                        ],Status::HTTP_NOT_ACCEPTABLE);
                    }
                }


                //this is the end of loop if all column in a row is null
                if($row[0] == null && $row[1] == null && $row[2] == null){
                    break;
                }

                //if true file is valid
                if( $row[0] != null && $row[1] != null && $row[2] != null){

                    array_push($array,[
                        'product_code'      => $row[0],
                        'product_name'      => $row[1],
                        'category'          => $row[2],
                    ]);
                    continue;
                }

                return response()->json([
                    'errors' => [
                        "message" => "Sheet column format is invalid!!",
                    ]   
                ],Status::HTTP_NOT_ACCEPTABLE);
            }


            // dd($array);
            //save it

            $error = [];
            $success = [];

            foreach ($array as $sheet) {

                //check for existinf product code
                $product_code = PF::where('product_code','=',$sheet['product_code'])->get();

                if(sizeof($product_code) != 0){
                    array_push($error, [
                        'data'      => [
                            'product_code'                  => $sheet['product_code'],
                            'product_name'                  => $sheet['product_name'],
                            'category'                  => $sheet['category'],
                        ],
                        'message'   => 'Product code '. $sheet['product_code'] .' already exist!!!',
                    ]);
                    continue;
                }


                //check for category if exist and create if it doesnt ;
                $category_code = Category::where('name','=',$sheet['category'])->get();
                $category = 0;

                if(sizeof($category_code) == 0){
                    $a = new Category();
                    $a->name = $sheet['category'];
                    $a->save();
                    $category = $a->id;
                }else{
                    $category = $category_code[0]->id;
                }




                $b = new PF();
                $b->product_code  = $sheet['product_code'];
                $b->product_name  = $sheet['product_name'];
                $b->category = $category;
                $b->save();

                array_push($success, 
                    [
                        'product_code'                  => $sheet['product_code'],
                        'product_name'                  => $sheet['product_name'],
                        'category'                      => $sheet['category'],
                    ]
                );
            }

            return response()->json([
                    'errors' => $error,
                    'success'=> $success
            ]);

        }
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

            return response()->json([
                "errors" => Utils::RemakeArray($a)
            ],Status::HTTP_NOT_ACCEPTABLE);
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
            return response()->json(['message' => 'Product not found'], Status::HTTP_NOT_FOUND);
        }

        try{
            $product->delete();    
        }catch(\Illuminate\Database\QueryException $e){
            return response()->json(['message' => 'Cannot delete, currently linked'], Status::HTTP_METHOD_NOT_ALLOWED);
        }

        return response()->json([
            "message" => 'Product successfully deleted'
        ]);
    }	
}
  