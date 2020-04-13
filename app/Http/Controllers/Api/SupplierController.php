<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Helpers\Utils;
use Status;
use Validator;
use Auth;
use App\Imports\ExcelSheet;
use App\Models\Logs;
use App\Http\Resources\SnapshotResource;

class SupplierController extends Controller
{


    public function __construct(){
        $this->middleware('viewOnlyPermission')->except(['index','show']);
        $this->middleware('adminOnlyPermission')->only('destroy');
    }

    public function index(Request $request){

        if($request->filter){

            $filt = json_decode(stripslashes($request->filter));

            if(!is_object($filt)){
                return [
                    "status" => false,
                    "errors" => "Filter must be an object"
                ];
            }

            $filter = [
                'supplier_code'   =>  property_exists($filt,'supplier_code') ? $filt->supplier_code : null,
                'supplier_name'   =>  property_exists($filt,'supplier_name') ? $filt->supplier_name : null,
                'address'         =>  property_exists($filt,'address') ? $filt->address : null,
                'contact_person'  =>  property_exists($filt,'contact_person') ? $filt->contact_person : null,
                'contact_number'  =>  property_exists($filt,'contact_number') ? $filt->contact_number : null,
                'email'           =>  property_exists($filt,'email') ? $filt->email : null,
            ];

            $where = [];

            foreach ($filter as $key => $value) {
                if($value != null){

                    // dd($value->filter);

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

            
            $supplier = Supplier::where($where);
            $per_page = $request->per_page != null ? (int)$request->per_page : 10;
            
            return SupplierResource::collection($supplier->paginate($per_page));
        }

        if($request->search){
                $query = Supplier::where('supplier_code','like','%'.$request->search.'%')
                            ->orWhere('supplier_name','like','%'.$request->search.'%')
                            ->paginate(20);
                return SupplierResource::collection($query);
        }

        if($request->snapshot != null && is_numeric($request->snapshot)) {
            $id = (int)$request->snapshot;
            $per_page = $request->per_page != null ? (int)$request->per_page : 1000;

            if($id == 0) return SupplierResource::collection(Supplier::orderBy('updated_at', 'desc')->paginate($per_page));

            return SnapshotResource::collection(
                Logs::where('id','>',$id)
                    ->where('target','=','Supplier')
                    ->orderBy('updated_at', 'desc')
                    ->paginate($per_page)
            );
        }

        $per_page = $request->per_page != null ? (int)$request->per_page : 10;
        return SupplierResource::collection(Supplier::paginate($per_page));
    }

    public function show($id){
        return SupplierResource::collection(Supplier::where('id','=',$id)->get())->first();
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'supplier_code'                 =>  'required|unique:supplier,supplier_code',
            'supplier_name'                 =>  'required',
            'address'                       =>  'required',
            // 'tin'                           =>  'required',
            'contact_person'                =>  'required',
            'contact_number'                =>  'required|unique:supplier,contact_number',
            'email'                         =>  'required|email|unique:supplier,email'
        ]);

        if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return [
                "status" => false,
                "errors" => Utils::RemakeArray($a)
            ];
        }

        $supplier = new Supplier();
        $supplier->supplier_code    =   $request->supplier_code;
        $supplier->supplier_name    =   $request->supplier_name;
        $supplier->address          =   $request->address;
        // $supplier->tin              =   $request->tin;
        
        if($request->tin != null){
            $supplier->tin              =   $request->tin;
        }

        $supplier->contact_person   =   $request->contact_person;
        $supplier->contact_number   =   $request->contact_number;
        $supplier->email            =   $request->email;
        $supplier->save();

        return response()->json([
            "message" => "Supplier successfully created",
        ]);
    }

    public function update(Request $request,$id){

        $supplier = Supplier::find($id);

        if($supplier == null){
            return [
                'status'  => false,
                'message' => 'Supplier not found'
            ];
        }

        $validator = Validator::make($request->all(), [
            'supplier_code'                 =>  'required|unique:supplier,supplier_code,'.$id,
            'supplier_name'                 =>  'required',
            'address'                       =>  'required',
            // 'tin'                           =>  'required',
            'contact_person'                =>  'required',
            'contact_number'                =>  'required|unique:supplier,contact_number,'.$id,
            'email'                         =>  'required|email|unique:supplier,email,'.$id
        ]);

        if ($validator->fails()){
            $a = $validator->errors()->toArray();

            return [
                "status" => false,
                "errors" => Utils::RemakeArray($a)
            ];
        }

        $supplier->supplier_code    =   $request->supplier_code;
        $supplier->supplier_name    =   $request->supplier_name;
        $supplier->address          =   $request->address;
        
        if($request->tin != null){
            $supplier->tin              =   $request->tin;
        }

        $supplier->contact_person   =   $request->contact_person;
        $supplier->contact_number   =   $request->contact_number;
        $supplier->email            =   $request->email;
        $supplier->save();

        return [
            "message" => "Supplier successfully updated",
        ];

    }

    public function destroy($id){

        $supplier = Supplier::find($id);

        if($supplier == null){
            return [
                'status'  => false,
                'message' => 'Supplier not found'
            ];
        }

        try{
            $supplier->delete();    
        }catch(\Illuminate\Database\QueryException $e){
            return [
                'status'  => false,
                'message' => 'Cannot delete, currently linked'
            ];
        }
        return response()->json([
            "message" => 'Supplier successfully deleted'
        ]);
    }

}
