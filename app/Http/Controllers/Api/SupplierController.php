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
                return response()->json([
                    "errors" => "Filter must be an object"
                ],Status::HTTP_NOT_ACCEPTABLE);
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

            if($request->per_page != null){
                $per_page = (int)$request->per_page;
                return SupplierResource::collection($supplier->paginate($per_page));
            }
            return SupplierResource::collection($supplier->paginate(10));
        }

        if($request->search){
                $query = Supplier::where('supplier_code','like','%'.$request->search.'%')
                            ->orWhere('supplier_name','like','%'.$request->search.'%')
                            ->paginate(20);
                return SupplierResource::collection($query);
        }

        if($request->per_page != null){
            $per_page = (int)$request->per_page;
            return SupplierResource::collection(Supplier::paginate($per_page));
        }
        return SupplierResource::collection(Supplier::paginate(10));
    }

    public function show($id){
        return SupplierResource::collection(Supplier::where('id','=',$id)->get())->first();
    }

    public function store(Request $request){
        if(!$request->hasFile('file')){
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

                return response()->json([
                    "errors" => Utils::RemakeArray($a)
                ],Status::HTTP_NOT_ACCEPTABLE);
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
        }else{

            ini_set('max_execution_time', 0);


            $column = 7;
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

            // dd($a->get(0));

            

            //check sheet if empty
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
                if($row[0] == null && $row[1] == null && $row[2] == null && $row[3] == null && $row[4] == null && $row[5] == null && $row[6] == null){
                    break;
                }

                //if true file is valid
                if($row[0] != null && $row[1] != null && $row[2] != null 
                    // && $row[3] != null /*tin*/ 
                    && $row[4] != null && $row[5] != null && $row[6] != null){

                    array_push($array,[
                        'supplier_code'  => $row[0],
                        'supplier_name'  => $row[1],
                        'address'        => $row[2],
                        'tin'            => $row[3],
                        'contact_person' => $row[4],
                        'contact_number' => $row[5],
                        'email'          => $row[6],
                    ]);
                    continue;
                }

                return response()->json([
                    'errors' => [
                        "message" => "Sheet column format is invalid!!",
                    ]   
                ],Status::HTTP_NOT_ACCEPTABLE);
            }


            $error = [];
            $success = [];

            foreach ($array as $sheet) {

                $supplier = Supplier::where('supplier_code','=',$sheet['supplier_code'])->get();

                if(sizeof($supplier) != 0){
                    array_push($error, [
                        'data'      => [
                            'supplier_code'      => $sheet['supplier_code'],
                            'supplier_name'      => $sheet['supplier_name'],
                            'address'            => $sheet['address'],
                            'tin'                => $sheet['tin'],
                            'contact_person'     => $sheet['contact_person'],
                            'contact_number'     => $sheet['contact_number'],
                            'email'              => $sheet['email']
                        ],
                        'message'   => 'Supplier code '. $sheet['supplier_code'] .' already exist!!!',
                    ]);
                    continue;
                }

                $supplier = new Supplier();
                $supplier->supplier_code    =   $sheet['supplier_code'];
                $supplier->supplier_name    =   $sheet['supplier_name'];
                $supplier->address          =   $sheet['address'];
                

                // $supplier->tin              =   $sheet['tin'];

                if($sheet['tin'] != null ){
                    $supplier->tin              =   $sheet['tin'];
                }


                $supplier->contact_person   =   $sheet['contact_person'];
                $supplier->contact_number   =   $sheet['contact_number'];
                $supplier->email            =   $sheet['email'];
                $supplier->save();

                array_push($success, 
                    [
                        'supplier_code'      => $sheet['supplier_code'],
                        'supplier_name'      => $sheet['supplier_name'],
                        'address'            => $sheet['address'],
                        'tin'                => $sheet['tin'],
                        'contact_person'     => $sheet['contact_person'],
                        'contact_number'     => $sheet['contact_number'],
                        'email'              => $sheet['email'],
                    ]
                );
            }

            return response()->json([
                    'errors' => $error,
                    'success'=> $success
            ]);

        }
    }

    public function update(Request $request,$id){

        $supplier = Supplier::find($id);

        if($supplier == null){
            return response()->json(['message' => 'Supplier not found'], Status::HTTP_NOT_FOUND);
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

            return response()->json([
                "errors" => Utils::RemakeArray($a)
            ],Status::HTTP_NOT_ACCEPTABLE);
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

        return response()->json([
            "message" => "Supplier successfully updated",
        ]);

    }

    public function destroy($id){

        $supplier = Supplier::find($id);

        if($supplier == null){
            return response()->json(['message' => 'Supplier not found'], Status::HTTP_NOT_FOUND);
        }

        try{
            $supplier->delete();    
        }catch(\Illuminate\Database\QueryException $e){
            return response()->json(['message' => 'Cannot delete, currently linked'], Status::HTTP_METHOD_NOT_ALLOWED);
        }
        return response()->json([
            "message" => 'Supplier successfully deleted'
        ]);
    }

}
