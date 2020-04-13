<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Imports\ExcelSheet;
use Excel;
use ExcelDate;
use DB;
use App\Models\Category;
use App\Models\ProductMasterList as PF;
use Storage;
use App\Models\Logs;

class MasterFileParse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:masterfile {id} {updated_by}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'parse the excel files in Master File';

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
        $column = 3;
        $array = [];

        $notification = Notification::where('id','=',$this->argument('id'));
        $filename = $notification->first()->filename;
        $updated_by = (int)$this->argument('updated_by');
        
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

            if($row[0] == null && $row[1] == null && $row[2] == null){
                break;
            }

            if( $row[0] != null && $row[1] != null && $row[2] != null){

                array_push($array,[
                    'product_code'      => $row[0],
                    'product_name'      => $row[1],
                    'category'          => $row[2],
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

            $category_code = Category::where('name','=',$sheet['category'])->get();
            $category = 0;

            if(sizeof($category_code) == 0){
                $catVal = [
                    'name'          =>  $sheet['category'],
                    'created_at'    =>  date('Y-m-d H:i:s'),
                    'updated_at'    =>  date('Y-m-d H:i:s'),
                ];
                $category = DB::table('category')->insertGetId($catVal);
                
                $logs = new Logs();
                $logs->user = $updated_by;
                $logs->action = "create";
                $logs->target = "Category";
                $logs->update = json_encode($catVal);
                $logs->save();

            }else{
                $category = $category_code[0]->id;
            }

            $pmlVar = [
                'product_code' => $sheet['product_code'],
                'product_name' => $sheet['product_code'],
                'category'     => $category,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];

            DB::table('product_master_list')->insert($pmlVar);

            $logs = new Logs();
            $logs->user = $updated_by;
            $logs->action = "create";
            $logs->target = "ProductMasterList";
            $logs->update = json_encode($pmlVar);
            $logs->save();


            array_push($success, 
                [
                    'product_code'                  => $sheet['product_code'],
                    'product_name'                  => $sheet['product_name'],
                    'category'                      => $sheet['category'],
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
