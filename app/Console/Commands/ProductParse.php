<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Imports\ExcelSheet;
use Excel;
use ExcelDate;
use DB;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductMasterList;
use Auth;
use Storage;


class ProductParse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:product {id} {updated_by}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'parse the excel files in product';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        // dd($this->argument('updated_by'));
        $updated_by = (int)$this->argument('updated_by');
        $column = 11;
        $array = [];

        $notification = Notification::where('id','=',$this->argument('id'));
        $filename = $notification->first()->filename;

        $a = (new ExcelSheet)->toCollection(storage_path()."/app/temp/".$filename);

       
        if(sizeof($a->toArray()[0]) < 2){

            $notification->update([
                'result' => json_encode([
                    'errors'    =>  [
                        "message"   =>  "Sheet file is empty"
                    ]
                ])
            ]);
            Storage::delete('temp/'.$filename);
            return;
        }

        // dd(array_key_exists(9,$a->toArray()[0][0]));

        
        for($i=1;$i<sizeof($a->toArray()[0]);$i++){

            $row = $a->toArray()[0][$i];
                
            for($y=0;$y<sizeof($row);$y++){
                if($y+1 > $column && $row[$y] != null){                    
                    $notification->update([
                        'result' => json_encode([
                            'errors' => [
                                "message" => "Sheet column format is invalid!!",
                            ]
                        ]),
                        'status' => 'failed',
                    ]);
                    Storage::delete('temp/'.$filename);
                    return;
                }
            }

            if(!array_key_exists(9,$row)){
                $notification->update([
                    'result' => json_encode([
                        'errors' => [
                            "message" => "Sheet column format is invalid!!",
                        ]
                    ]),
                    'status' => 'failed',
                ]);
                Storage::delete('temp/'.$filename);
                return;
            }

            if(
                $row[0] == null && $row[1] == null && 
                $row[2] == null && $row[3] == null && 
                $row[4] == null && $row[5] == null && 
                $row[6] == null && $row[7] == null && 
                $row[8] == null && $row[9] == null
            ){break;}

            
            if(
                $row[0] != null && $row[1] != null && 
                $row[2] != null && $row[3] != null && 
                $row[4] != null && //-- for serial number 
                $row[5] != null && $row[6] != null && $row[7] != null
            ){

                array_push($array,[
                    'supplier_code'                 => $row[0],
                    'product_code'                  => $row[1],
                    
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


            $notification->update([
                'result' => json_encode([
                    'errors' => [
                        "message" => "Sheet column format is invalid!!",
                    ]
                ]),
                'status' => 'failed',
            ]);
            Storage::delete('temp/'.$filename);
            return;
        }


           
        $error = [];
        $success = [];

        foreach ($array as $sheet) {

            $supplier = Supplier::where('supplier_code','=',$sheet['supplier_code'])->get();        
            $product_code = Product::where('serial_number','=',$sheet['serial_number'])->get();
            $products = ProductMasterList::where('product_code','=',$sheet['product_code'])->get();
            
            if(sizeof($products) == 0){
                array_push($error, [
                    'data'      => [
                        'supplier_code'                 => $sheet['supplier_code'],
                        'product_code'                  => $sheet['product_code'],
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



            // $product                                = new Product();
            // $product->supplier                      = $supplier[0]->id;
            // $product->product                       = $products[0]->id;
            // $product->delivery_date                 = date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['delivery_date'])));
            // $product->reference_delivery_document   = $sheet['reference_delivery_document'];
            // $product->serial_number                 = $sheet['serial_number'];   
            // $product->created_by                    = Auth::user()->id;
            // $product->updated_by                    = Auth::user()->id;

            // $warranty = $sheet['warranty'];
            // $start = date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_start'])));
            // $end = date("Y-m-d",strtotime("+ ".$warranty." months ".$start));


            // $product->warranty                      = $warranty;
            // $product->warranty_start                = $start;
            // $product->warranty_end                  = $end;
            // $product->remarks                       = $sheet['remarks'];


            // switch(trim(strtolower($sheet['status']))){
            //     case null :
            //         $product->status = 1;
            //         break;
            //     case "new" :
            //         $product->status = 1;
            //         break;
            //     case "replaced" :
            //         $product->status = 2;
            //         break;
            //     case "returned" :
            //         $product->status = 3;
            //         break;
            //     case "repaired" :
            //         $product->status = 4;
            //         break;
            // }

            // $product->save();

            $warranty = $sheet['warranty'];
            $start = date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['warranty_start'])));
            $end = date("Y-m-d",strtotime("+ ".$warranty." months ".$start));


            $status = 0;

            switch(trim(strtolower($sheet['status']))){
                case null :
                    $status = 1;
                    break;
                case "new" :
                    $status = 1;
                    break;
                case "replaced" :
                    $status = 2;
                    break;
                case "returned" :
                    $status = 3;
                    break;
                case "repaired" :
                    $status = 4;
                    break;
            }



            DB::table('product')->insert([
                'supplier'                      => $supplier[0]->id,
                'product'                       => $products[0]->id,
                'delivery_date'                 => date("Y-m-d" ,strtotime('@'.ExcelDate::excelToTimestamp($sheet['delivery_date']))),
                'reference_delivery_document'   => $sheet['reference_delivery_document'],
                'serial_number'                 => $sheet['serial_number'],
                'created_by'                    => $updated_by,
                'updated_by'                    => $updated_by,
                'warranty'                      => $warranty,
                'warranty_start'                => $start,
                'warranty_end'                  => $end,
                'remarks'                       => $sheet['remarks'],
                'status'                        => $status,
                'created_at'                    => date('Y-m-d H:i:s'),
                'updated_at'                    => date('Y-m-d H:i:s')
            ]);


            array_push($success, 
                [
                    'supplier_code'                 => $sheet['supplier_code'],
                    'product_code'                  => $sheet['product_code'],
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

        $notification->update([
            'result' => json_encode([
                'errors' => $error,
                'success'=> $success
            ]),
            'status' => 'done'
        ]);
        Storage::delete('temp/'.$filename);
        return;
    }
}
