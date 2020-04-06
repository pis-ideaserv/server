<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductMasterList;
use App\Helpers\Utils;
use Status;
use Validator;
use Auth;
use App\Imports\ExcelSheet;
use Excel;
use ExcelDate;
use DB;

class ProductController extends Controller
{

    public function __construct(){
        $this->middleware('viewOnlyPermission')->except(['index','show']);
        $this->middleware('adminOnlyPermission')->only('destroy');
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
                'serial_number'                 =>  property_exists($filt,'serial_number') ? $filt->serial_number : null,
                'warranty'                      =>  property_exists($filt,'warranty') ? $filt->warranty : null,
                'warranty_start'                =>  property_exists($filt,'warranty_start') ? $filt->warranty_start : null,
                'warranty_end'                  =>  property_exists($filt,'warranty_end') ? $filt->warranty_end : null,
                'status'                        =>  property_exists($filt,'status') ? $filt->status : null,
                'delivery_date'                 =>  property_exists($filt,'delivery_date') ? $filt->delivery_date : null,
                'reference_delivery_document'   =>  property_exists($filt,'reference_delivery_document') ? $filt->reference_delivery_document : null,
                'remarks'                       =>  property_exists($filt,'remarks') ? $filt->remarks : null
            ];

            $where = [];

            foreach ($filter as $key => $value) {
                if($value != null){
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

            $supplier = [];
            if(property_exists($filt,'supplier')){
                switch ($filt->supplier->filter) {
                    case "iet" :
                        array_push($supplier, ['su.supplier_code', '=',$filt->supplier->key]);
                        break;
                    case "inet" :
                        array_push($supplier, ['su.supplier_code', '!=',$filt->supplier->key]);
                        break;
                    case "c" :
                        array_push($supplier, ['su.supplier_code', 'like','%'.$filt->supplier->key.'%']);
                        break;
                    case "dnc" :
                        array_push($supplier, ['su.supplier_code', 'not like','%'.$filt->supplier->key.'%']);
                        break;
                    case "sw" :
                        array_push($supplier, ['su.supplier_code', 'like',$filt->supplier->key.'%']);
                        break;
                    case "ew" :
                        array_push($supplier, ['su.supplier_code', 'like','%'.$filt->supplier->key]);
                        break;
                }
            }

            $supplier_name = [];
            if(property_exists($filt,'supplier_name')){
                switch ($filt->supplier_name->filter) {
                    case "iet" :
                        array_push($supplier_name, ['su.supplier_name', '=',$filt->supplier_name->key]);
                        break;
                    case "inet" :
                        array_push($supplier_name, ['su.supplier_name', '!=',$filt->supplier_name->key]);
                        break;
                    case "c" :
                        array_push($supplier_name, ['su.supplier_name', 'like','%'.$filt->supplier_name->key.'%']);
                        break;
                    case "dnc" :
                        array_push($supplier_name, ['su.supplier_name', 'not like','%'.$filt->supplier_name->key.'%']);
                        break;
                    case "sw" :
                        array_push($supplier_name, ['su.supplier_name', 'like',$filt->supplier_name->key.'%']);
                        break;
                    case "ew" :
                        array_push($supplier_name, ['su.supplier_name', 'like','%'.$filt->supplier_name->key]);
                        break;
                }
            }


            $product = [];
            if(property_exists($filt,'product')){
                switch ($filt->product->filter) {
                    case "iet" :
                        array_push($product, ['pml.product_code', '=',$filt->product->key]);
                        break;
                    case "inet" :
                        array_push($product, ['pml.product_code', '!=',$filt->product->key]);
                        break;
                    case "c" :
                        array_push($product, ['pml.product_code', 'like','%'.$filt->product->key.'%']);
                        break;
                    case "dnc" :
                        array_push($product, ['pml.product_code', 'not like','%'.$filt->product->key.'%']);
                        break;
                    case "sw" :
                        array_push($product, ['pml.product_code', 'like',$filt->product->key.'%']);
                        break;
                    case "ew" :
                        array_push($product, ['pml.product_code', 'like','%'.$filt->product->key]);
                        break;
                }
            }

            $product_description = [];
            if(property_exists($filt,'product_description')){
                switch ($filt->product_description->filter) {
                    case "iet" :
                        array_push($product_description, ['pml.product_name', '=',$filt->product_description->key]);
                        break;
                    case "inet" :
                        array_push($product_description, ['pml.product_name', '!=',$filt->product_description->key]);
                        break;
                    case "c" :
                        array_push($product_description, ['pml.product_name', 'like','%'.$filt->product_description->key.'%']);
                        break;
                    case "dnc" :
                        array_push($product_description, ['pml.product_name', 'not like','%'.$filt->product_description->key.'%']);
                        break;
                    case "sw" :
                        array_push($product_description, ['pml.product_name', 'like',$filt->product_description->key.'%']);
                        break;
                    case "ew" :
                        array_push($product_description, ['pml.product_name', 'like','%'.$filt->product_description->key]);
                        break;
                }
            }

            $category = [];
            if(property_exists($filt,'category')){
                switch ($filt->category->filter) {
                    case "iet" :
                        array_push($category, ['cat.name', '=',$filt->category->key]);
                        break;
                    case "inet" :
                        array_push($category, ['cat.name', '!=',$filt->category->key]);
                        break;
                    case "c" :
                        array_push($category, ['cat.name', 'like','%'.$filt->category->key.'%']);
                        break;
                    case "dnc" :
                        array_push($category, ['cat.name', 'not like','%'.$filt->category->key.'%']);
                        break;
                    case "sw" :
                        array_push($category, ['cat.name', 'like',$filt->category->key.'%']);
                        break;
                    case "ew" :
                        array_push($category, ['cat.name', 'like','%'.$filt->category->key]);
                        break;
                }
            }

            $created_by = [];
            if(property_exists($filt,'created_by')){
                switch ($filt->created_by->filter) {
                    case "iet" :
                        array_push($created_by, [DB::raw('concat(use.first_name," ",use.last_name)'), '=',$filt->created_by->key]);
                        break;
                    case "inet" :
                        array_push($created_by, [DB::raw('concat(use.first_name," ",use.last_name)'), '!=',$filt->created_by->key]);
                        break;
                    case "c" :
                        // array_push($category, ['cat.name', 'like','%'.$filt->category->key.'%']);
                        array_push($created_by, [DB::raw('concat(use.first_name," ",use.last_name)'), 'like','%'.$filt->created_by->key.'%']);
                        break;
                    case "dnc" :
                        // array_push($category, ['cat.name', 'not like','%'.$filt->category->key.'%']);
                        array_push($created_by, [DB::raw('concat(use.first_name," ",use.last_name)'), 'not like','%'.$filt->created_by->key.'%']);
                        break;
                    case "sw" :
                        // array_push($category, ['cat.name', 'like',$filt->category->key.'%']);
                        array_push($created_by, [DB::raw('concat(use.first_name," ",use.last_name)'), 'like',$filt->created_by->key.'%']);
                        break;
                    case "ew" :
                        // array_push($category, ['cat.name', 'like','%'.$filt->category->key]);
                        array_push($created_by, [DB::raw('concat(use.first_name," ",use.last_name)'), 'like','%'.$filt->created_by->key]);
                        break;
                }
            }


 


            if($request->per_page != null){
                $per_page = (int)$request->per_page;
                $product = Product::select('product.*')
                        ->leftJoin('product_master_list as pml','product.product','=','pml.id')
                        ->leftJoin('supplier as su','product.supplier','=','su.id')
                        ->leftJoin('category as cat','pml.category','=','cat.id')
                        ->leftJoin('user as use','use.id','=','product.created_by')
                        ->where($created_by)
                        ->where($supplier)
                        ->where($supplier_name)
                        ->where($category)
                        ->where($where)
                        ->where($product)
                        ->where($product_description)
                        ->orderBy('product.updated_at', 'desc')
                        ->paginate($per_page);
            }else{
                $product = Product::select('product.*')
                        ->leftJoin('product_master_list as pml','product.product','=','pml.id')
                        ->leftJoin('supplier as su','product.supplier','=','su.id')
                        ->leftJoin('category as cat','pml.category','=','cat.id')
                        ->where($created_by)
                        ->where($supplier)
                        ->where($supplier_name)
                        ->where($category)
                        ->where($where)
                        ->where($product)
                        ->where($product_description)
                        ->orderBy('product.updated_at', 'desc')
                        ->paginate(10);
            }
            return ProductResource::collection($product);
            
        }


        if($request->search){
            $query = Product::select('product.*')->leftJoin('product_master_list as pml','pml.id','=','product.product')
                        ->where('pml.product_code','like','%'.$request->search.'%')
                        ->orWhere('pml.product_name','like','%'.$request->search.'%')
                        ->paginate(20);
            return ProductResource::collection($query);
        }

        if($request->per_page != null){
            $per_page = (int)$request->per_page;
            return ProductResource::collection(Product::orderBy('updated_at', 'desc')->paginate($per_page));
        }

        return ProductResource::collection(Product::orderBy('updated_at', 'desc')->paginate(10));
        
    }

    public function show($id){
        return ProductResource::collection(Product::where('id','=',$id)->get())->first();
    }

    public function store(Request $request){
        if(!$request->hasFile('file')){
            $validator = Validator::make($request->all(), [
                'supplier'                      =>  'required',
                'product'                       =>  'required',
                'delivery_date'                 =>  'required',
                'reference_delivery_document'   =>  'required',
                //'reference_delivery_document'   =>  'required|unique:product,reference_delivery_document',
                'serial_number'                 =>  'required|unique:product,serial_number',
                'warranty'                      =>  'required',
                'warranty_start'                =>  'required',
                'status'                        =>  'required'
            ]);

            if ($validator->fails()){
                $a = $validator->errors()->toArray();

                return response()->json([
                    "errors" => Utils::RemakeArray($a)
                ],Status::HTTP_NOT_ACCEPTABLE);
            }

            $product                                = new Product();
            $product->supplier                      = $request->supplier;
            $product->product                       = $request->product;
            // $product->product_description           = $request->product_description;
            $product->delivery_date                 = date("Y-m-d" ,strtotime($request->delivery_date));
            $product->reference_delivery_document   = $request->reference_delivery_document;
            
            // $product->serial_number                 = $request->serial_number;
            if($request->serial_number != null ){
                $product->serial_number                 = $request->serial_number;
            }

            $product->status                        = $request->status;
            $product->remarks                       = $request->remarks;
            $product->created_by                    = Auth::user()->id;
            $product->updated_by                    = Auth::user()->id;

            $warranty = $request->warranty;
            $start =  date("Y-m-d", strtotime($request->warranty_start));

            $product->warranty                      = $warranty;
            $product->warranty_start                = $start;
            $product->warranty_end                  = date("Y-m-d",strtotime(" + ".$warranty." months ".$start));

            $product->save();

            return response()->json([
                "message" => "Product successfully created",
            ]);
        }else{

            ini_set('max_execution_time', 0);
            
            $column = 11;
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
                if(
                    $row[0] == null && $row[1] == null && 
                    $row[2] == null && $row[3] == null && 
                    $row[4] == null && $row[5] == null && 
                    $row[6] == null && $row[7] == null && 
                    $row[8] == null && $row[9] == null
                ){
                    break;
                }

                //if true file is valid
                if(
                    $row[0] != null && $row[1] != null && 
                    $row[2] != null && $row[3] != null && 
                    $row[4] != null && //-- for serial number 
                    $row[5] != null && 
                    $row[6] != null && $row[7] != null
                ){

                    array_push($array,[
                        'supplier_code'                 => $row[0],
                        'product_code'                  => $row[1],
                        // 'product_description'           => $row[2],
                        'delivery_date'                 => $row[2],
                        'reference_delivery_document'   => $row[3],
                        'serial_number'                 => $row[4],
                        'warranty'                      => $row[5],
                        'warranty_start'                => $row[6],
                        'warranty_end'                  => $row[7],
                        'status'                        => $row[8],
                        'remarks'                       => $row[9],
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

                $supplier = Supplier::where('supplier_code','=',$sheet['supplier_code'])->get();
                
                // $product_code = Product::where('reference_delivery_document','=',$sheet['reference_delivery_document'])
                //                         ->orWhere('serial_number','=',$sheet['serial_number'])
                //                         ->get();
                $product_code = Product::where('serial_number','=',$sheet['serial_number'])->get();

                $products = ProductMasterList::where('product_code','=',$sheet['product_code'])->get();



                if(sizeof($products) == 0){
                     array_push($error, [
                        'data'      => [
                            'supplier_code'                 => $sheet['supplier_code'],
                            'product_code'                  => $sheet['product_code'],
                            // 'product_description'           => $sheet['product_description'],
                            'delivery_date'                 => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['delivery_date']))),
                            'reference_delivery_document'   => $sheet['reference_delivery_document'],
                            'serial_number'                 => $sheet['serial_number'],
                            'warranty'                      => $sheet['warranty'],
                            'warranty_start'                => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_start']))),
                            'warranty_end'                  => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_end']))),
                            'status'                        => $sheet['status'] == null ? "New" : $sheet['status'],
                            'remarks'                       => $sheet['remarks'],
                        ],
                        'message'   => 'Product code '. $sheet['product_code'] .' dont exist!!!',
                    ]);
                    continue;
                }


                if(sizeof($supplier) == 0){
                    array_push($error, [
                        'data'      => [
                            'supplier_code'                 => $sheet['supplier_code'],
                            'product_code'                  => $sheet['product_code'],
                            // 'product_description'           => $sheet['product_description'],
                            'delivery_date'                 => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['delivery_date']))),
                            'reference_delivery_document'   => $sheet['reference_delivery_document'],
                            'serial_number'                 => $sheet['serial_number'],
                            'warranty'                      => $sheet['warranty'],
                            'warranty_start'                => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_start']))),
                            'warranty_end'                  => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_end']))),
                            'status'                        => $sheet['status'] == null ? "New" : $sheet['status'],
                            'remarks'                       => $sheet['remarks'],
                        ],
                        'message'   => 'Supplier code '. $sheet['supplier_code'] .' dont exist!!!',
                    ]);
                    continue;
                }

                if(sizeof($product_code) != 0){
                     array_push($error, [
                        'data'      => [
                            'supplier_code'                 => $sheet['supplier_code'],
                            'product_code'                  => $sheet['product_code'],
                            // 'product_description'           => $sheet['product_description'],
                            'delivery_date'                 => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['delivery_date']))),
                            'reference_delivery_document'   => $sheet['reference_delivery_document'],
                            'serial_number'                 => $sheet['serial_number'],
                            'warranty'                      => $sheet['warranty'],
                            'warranty_start'                => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_start']))),
                            'warranty_end'                  => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_end']))),
                            'status'                        => $sheet['status'] == null ? "New" : $sheet['status'],
                            'remarks'                       => $sheet['remarks'],
                        ],
                        'message'   => 'Product code '. $sheet['product_code'] ."'s serial number already exist!!!",
                    ]);
                    continue;
                }



                $product                                = new Product();
                $product->supplier                      = $supplier[0]->id;
                $product->product                       = $products[0]->id;
                // $product->product_description           = $sheet['product_description'];
                $product->delivery_date                 = date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['delivery_date'])));
                $product->reference_delivery_document   = $sheet['reference_delivery_document'];
                $product->serial_number                 = $sheet['serial_number'];   
                $product->created_by                    = Auth::user()->id;
                $product->updated_by                    = Auth::user()->id;

                $warranty = $sheet['warranty'];
                $start = date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_start'])));
                $end = date("Y-m-d",strtotime("+ ".$warranty." months ".$start));


                $product->warranty                      = $warranty;
                $product->warranty_start                = $start;
                $product->warranty_end                  = $end;
                $product->remarks                       = $sheet['remarks'];


                switch(trim(strtolower($sheet['status']))){
                    case null :
                        $product->status = 1;
                        break;
                    case "new" :
                        $product->status = 1;
                        break;
                    case "replaced" :
                        $product->status = 2;
                        break;
                    case "returned" :
                        $product->status = 3;
                        break;
                    case "repaired" :
                        $product->status = 4;
                        break;
                }



                $product->save();

                array_push($success, 
                    [
                        'supplier_code'                 => $sheet['supplier_code'],
                        'product_code'                  => $sheet['product_code'],
                        // 'product_description'           => $sheet['product_description'],
                        'delivery_date'                 => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['delivery_date']))),
                        'reference_delivery_document'   => $sheet['reference_delivery_document'],
                        'serial_number'                 => $sheet['serial_number'],
                        'warranty'                      => $sheet['warranty'],
                        'warranty_start'                => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_start']))),
                        'warranty_end'                  => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_end']))),
                        'status'                        => $sheet['status'] == null ? "New" : $sheet['status'],
                        'remarks'                       => $sheet['remarks'],
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
        $product = Product::find($id);

        if($product == null){
            return response()->json(['message' => 'Product not found'], Status::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'supplier'                      =>  'required',
            'product'                       =>  'required',
            'delivery_date'                 =>  'required',
            // 'reference_delivery_document'   =>  'required|unique:product,reference_delivery_document,'.$id,
            'reference_delivery_document'   =>  'required',
            'serial_number'                 =>  'required|unique:product,serial_number,'.$id,
            'warranty'                      =>  'required',
            'warranty_start'                =>  'required',
            'status'                        =>  'required'
        ]);

        if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return response()->json([
                "errors" => Utils::RemakeArray($a)
            ],Status::HTTP_NOT_ACCEPTABLE);
        }

        $product->supplier                      = $request->supplier;
        $product->product                       = $request->product;
        $product->delivery_date                 = date("Y-m-d" ,strtotime($request->delivery_date));
        $product->reference_delivery_document   = $request->reference_delivery_document;

        if($request->serial_number != null ){
            $product->serial_number                 = $request->serial_number;
        }

        $product->updated_by                    = Auth::user()->id;


        if($request->remarks != null){
            $product->remarks = $request->remarks;
        }
            
        $warranty = $request->warranty;
        $start =  date("Y-m-d", strtotime($request->warranty_start));

        $product->warranty                      = $warranty;
        $product->warranty_start                = $start;
        $product->warranty_end                  = date("Y-m-d",strtotime(" + ".$warranty." months ".$start));
        $product->status                        = $request->status;

        $product->save();

        return response()->json([
            "message" => "Product successfully updated",
        ]);
    }

    public function destroy($id){
        $product = Product::find($id);

        if($product == null){
            return response()->json(['message' => 'Product not found'], Status::HTTP_NOT_FOUND);
        }

        $product->delete();

        return response()->json([
            "message" => 'Product successfully deleted'
        ]);
    }

}
