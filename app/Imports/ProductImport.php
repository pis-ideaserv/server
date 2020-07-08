<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithMapping;

use ExcelDate;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\ProductMasterList;
use App\Models\FileLog;
use Illuminate\Validation\Rule;
use App\Models\Logs;
use App\Models\Status;
use DB;


class ProductImport implements ToModel,WithChunkReading, ShouldQueue, WithHeadingRow,WithValidation, SkipsOnFailure, WithMapping
{
    use Importable;
    
    private $filename;
    private $id;
    private $header = [
        'supplier_code',
        'product_code',
        'delivery_date',
        'reference_delivery_document',
        'serial_number',
        'warranty',
        'warranty_start',
        'warranty_end',
        'status',
        'remarks',
    ];


    public function __construct($id,$filename){
        $this->id = $id;
        $this->filename = $filename;
    }


    //Primary Checkpoint
    public function map($row): array
    {  
        $filter = [
            'delivery_date',
            'warranty_start',
            'warranty_end',
            'status',
            'remarks',
        ];

        
        foreach($row as $key => $value){
            foreach($filter as $filt){
                //transform spreadsheet time to string date
                if($key == $filt && gettype($value) == 'double'){            
                    $row[$key] = date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($row[$key])));
                    continue;
                }

                //transform some special elements
                if($key == $filt && array_key_exists($filt,$row)){
                    if($filt == 'status'){
                        switch(trim(strtolower($row[$key]))){
                            case null :
                                $row[$key] = 1;
                                break;
                            case "new" :
                                $row[$key] = 1;
                                break;
                            case "replaced" :
                                $row[$key] = 2;
                                break;
                            case "returned" :
                                $row[$key] = 3;
                                break;
                            case "repaired" :
                                $row[$key] = 4;
                                break;
                        }
                    }
                    //we'll going to transform remarks to a string to get advantage of laravel validation required if it is null
                    // if($filt == 'remarks' && $row[$key] == null ){
                    //     $row[$key] = "empty";
                    // }
                }
            }
            //remove element with empty key and value
            if(!$key){
                unset($row[$key]);
            }
        }
        return $row;
    }

    

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            'supplier_code'                 =>  'required|exists:supplier,supplier_code',
            'product_code'                  =>  'required|exists:product_master_list,product_code',
            'delivery_date'                 =>  'required|date',
            'reference_delivery_document'   =>  'required',
            'serial_number'                 =>  'required|unique:product,serial_number',
            'warranty'                      =>  'required',
            'warranty_start'                =>  'required|date',
            'warranty_end'                  =>  'required|date',
            'status'                        =>  ['required',Rule::in([1,2,3,4])],  // set the rules here if some inputs are unknown
            'remarks'                       =>  'nullable',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.exists'  =>  'exists',
            '*.unique'  =>  'unique',
            '*.required'=>  'required',
            '*.date'    =>  'date',
            '*.in'      =>  'invalid',
        ];
    }

    public function model(array $collection)
    {
        $productsVar = [
            'supplier'                      => Supplier::where('supplier_code','=',$collection['supplier_code'])->get()->first()->toArray(),
            'product'                       => ProductMasterList::where('product_code','=',$collection['product_code'])->get()->first()->toArray(),
            'delivery_date'                 => $collection['delivery_date'],
            'reference_delivery_document'   => $collection['reference_delivery_document'],
            'serial_number'                 => $collection['serial_number'],
            'created_by'                    => $this->id,
            'updated_by'                    => $this->id,
            'warranty'                      => $collection['warranty'],
            'warranty_start'                => $collection['warranty_start'],
            'warranty_end'                  => $collection['warranty_end'],
            'remarks'                       => $collection['remarks'],
            'status'                        => Status::where('name','=',$collection['status'])->get()->first()->toArray(),
            'created_at'                    => date('Y-m-d H:i:s'),
            'updated_at'                    => date('Y-m-d H:i:s')
        ];
        DB::table('product')->insert($productsVar);
        
        $logs = new Logs();
        $logs->user = $this->id;
        $logs->action = "create";
        $logs->target = "Product";
        $logs->update = json_encode($productsVar);
        $logs->save();


        $notification = Notification::where('filename','=',$this->filename);
        $result = json_decode($notification->first()->result);
        if($result != null){
            $notification->update([
                'result' => json_encode([
                    'total'     => $result->total+1,
                    'success'   => $result->success+1,
                ])
            ]);
            return;
        }

        $notification->update([
            'result'    => json_encode([
                'total'     => 1,
                'success'   => 1,
            ])
        ]);
        return;
    }

    public function onFailure(Failure ...$failures)
    {
        $message = $failures[0]->errors()[0];
        $values  = $failures[0]->values();
        $attribute = $failures[0]->attribute();

        switch($message){
            case 'exists':
                FileLog::insert([
                    'filename'  =>  $this->filename,
                    'message'   =>  $attribute." <b>".$values[$attribute]."</b> doesn't exist.",    
                ]);
                break;
            case 'unique':
                FileLog::insert([
                    'filename'  =>  $this->filename,
                    'message'   =>  $attribute." <b>".$values[$attribute]."</b> already exist.",    
                ]);
                break;
            case 'required':
                FileLog::insert([
                    'filename'  =>  $this->filename,
                    'message'   =>  $attribute." <b>".$values[$attribute]."</b> should not be empty.",    
                ]);
                break;
            case 'date':
                FileLog::insert([
                    'filename'  =>  $this->filename,
                    'message'   =>  $attribute." <b>".$values[$attribute]."</b> should be in Date format.",    
                ]);
                break;
            case 'invalid':
                FileLog::insert([
                    'filename'  =>  $this->filename,
                    'message'   =>  $attribute." <b>".$values[$attribute]."</b> is invalid.",    
                ]);
                break;
        }
        
        $notification = Notification::where('filename','=',$this->filename);
        $result = json_decode($notification->first()->result);

        
        if($result != null){
            $notification->update([
                'result' => json_encode([
                    'total'     => $result->total+1,
                    'success'   => $result->success,
                ])
            ]);
            return;
        }

        $notification->update([
            'result'    => json_encode([
                'total'     => 1,
                'success'   => 0,
            ])
        ]);
        return;
    }
}
