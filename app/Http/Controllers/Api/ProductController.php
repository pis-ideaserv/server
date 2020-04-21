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
use App\Models\Logs;
use App\Http\Resources\SnapshotResource;

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
                return [
                    "status"    => false,
                    "message"   => "Filter must be an object"
                ];
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

            $per_page               = $request->per_page != null ? (int)$request->per_page : 10;
            $created_by             = Utils::Filter($filter,$filt,DB::raw('concat(use.first_name," ",use.last_name)'),"created_by");
            $supplier               = Utils::Filter($filter,$filt,"su.supplier_code","supplier");
            $supplier_name          = Utils::Filter($filter,$filt,"su.supplier_name","supplier_name");
            $category               = Utils::Filter($filter,$filt,"cat.name","category");
            $where                  = Utils::Filter($filter);
            $product                = Utils::Filter($filter,$filt,"pml.product_code","product");
            $product_description    = Utils::Filter($filter,$filt,"pml.product_name","product","product_description");

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
            
            return ProductResource::collection($product);
            
        }

        if($request->search){
            $query = Product::select('product.*')->leftJoin('product_master_list as pml','pml.id','=','product.product')
                        ->where('pml.product_code','like','%'.$request->search.'%')
                        ->orWhere('pml.product_name','like','%'.$request->search.'%')
                        ->paginate(10);
            return ProductResource::collection($query);
        }

        if($request->snapshot != null && is_numeric($request->snapshot)) {
            $id = (int)$request->snapshot;
            $per_page = $request->per_page != null ? (int)$request->per_page : 1000;
            $log = Logs::orderBy('updated_at', 'desc')->first();
            
            
            return SnapshotResource::collection(
                Logs::where('id','>',$id)
                    ->orderBy('updated_at', 'desc')
                    ->paginate($per_page)
            )->additional(['snapshot' => $log !== null ? $log->id : 0]);
        }
        
        $per_page = $request->per_page != null ? (int)$request->per_page : 10;
        $log = Logs::orderBy('updated_at', 'desc')->first();
        return ProductResource::collection(Product::orderBy('updated_at', 'desc')->paginate($per_page))->additional(['snapshot' => $log !== null ? $log->id : 0]);
    }

    public function show($id){
        return ProductResource::collection(Product::where('id','=',$id)->get())->first();
    }

    public function store(Request $request){
        // if(!$request->hasFile('file')){
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

                return [
                    "status" => false,
                    "errors" => Utils::RemakeArray($a)
                ];
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
    }
    
    public function update(Request $request, $id){
        $product = Product::find($id);

        if($product == null){
            return [
                "status"  => false,
                'message' => 'Product not found'
            ];
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

            return [
                "status" => false,
                "errors" => Utils::RemakeArray($a)
            ];
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
            return [
                'status'  => false,
                'message' => 'Product not found'
            ];
        }

        $product->delete();

        return [
            "message" => 'Product successfully deleted'
        ];
    }

}
