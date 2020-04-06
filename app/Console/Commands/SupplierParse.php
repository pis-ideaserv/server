<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Imports\ExcelSheet;
use Excel;
use ExcelDate;
use DB;
use App\Models\Supplier;
use Storage;

class SupplierParse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:supplier {id} {updated_by}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'parse the excel files in supplier';

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
        $column = 7;
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
                ]),
                'status' => 'failed',
            ]);
            Storage::delete('temp/'.$filename);
            return;
        }

        for($i=1;$i<sizeof($a->toArray()[0]);$i++){

            $row = $a->toArray()[0][$i];
            
            for($y=0;$y<sizeof($row);$y++){
                if($y+1 > $column && $row[$y] != null){
                    $notification->update([
                        'result' => json_encode([
                            'errors'    =>  [
                                "message"   =>  "Sheet column format is invalid!!"
                            ]
                        ]),
                        'status' => 'failed',
                    ]);
                    Storage::delete('temp/'.$filename);
                    return;
                }
            }

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

            $notification->update([
                'result' => json_encode([
                    'errors'    =>  [
                        "message"   =>  "Sheet column format is invalid!!"
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

            // $supplier = new Supplier();
            // $supplier->supplier_code    =   $sheet['supplier_code'];
            // $supplier->supplier_name    =   $sheet['supplier_name'];
            // $supplier->address          =   $sheet['address'];
            

            // // $supplier->tin              =   $sheet['tin'];

            // if($sheet['tin'] != null ){
            //     $supplier->tin              =   $sheet['tin'];
            // }


            // $supplier->contact_person   =   $sheet['contact_person'];
            // $supplier->contact_number   =   $sheet['contact_number'];
            // $supplier->email            =   $sheet['email'];
            // $supplier->save();

            DB::table('supplier')->insert([
                'supplier_code'     =>  $sheet['supplier_code'],
                'supplier_name'     =>  $sheet['supplier_name'],
                'address'           =>  $sheet['address'],
                'tin'               =>  $sheet['tin'],
                'contact_person'    =>  $sheet['contact_person'],
                'contact_number'    =>  $sheet['contact_number'],
                'email'             =>  $sheet['email'],
                'created_at'        =>  date('Y-m-d H:i:s'),
                'updated_at'        =>  date('Y-m-d H:i:s')
            ]);




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
