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
use DB;
use Illuminate\Validation\Rule;


use App\Models\Notification;
use App\Models\FileLog;
use App\Models\Logs;
use App\Models\Category;



class MasterFileImport implements ToModel,WithChunkReading, ShouldQueue, WithHeadingRow,WithValidation, SkipsOnFailure, WithMapping
{
    use importable;

    private $filename;
    private $id;
    private $header = [
        'product_code',
        'product_name',
        'category',
    ];

    public function __construct($id,$filename){
        $this->id = $id;
        $this->filename = $filename;
    }

    //Primary Checkpoint
    public function map($row): array
    {   
        $category_code = Category::where('name','=',$row['category'])->get();
        if(sizeof($category_code) == 0){
            $catVal = [
                'name'          =>  $row['category'],
                'created_at'    =>  date('Y-m-d H:i:s'),
                'updated_at'    =>  date('Y-m-d H:i:s'),
            ];
            $row['category'] = DB::table('category')->insertGetId($catVal);
        }else{
            $row['category'] = $category_code[0]->id;
        }
        return $row;
    }

    public function chunkSize(): int{
        return 1000;
    }

    public function rules(): array{
        return [
            'product_code'  =>  'required|unique:product_master_list,product_code',
            'product_name'  =>  'required',
            'category'      =>  'required',
        ];
    }

    public function customValidationMessages(){
        return [
            '*.unique'  =>  'unique',
            '*.required'=>  'required',
        ];
    }

    public function model(array $collection){
        $pmlVar = [
            'product_code' => $collection['product_code'],
            'product_name' => $collection['product_name'],
            'category'     => $collection['category'],
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];


        DB::table('product_master_list')->insert($pmlVar);

        $logs = new Logs();
        $logs->user = $this->id;
        $logs->action = "create";
        $logs->target = "ProductMasterList";
        $logs->update = json_encode($pmlVar);
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
