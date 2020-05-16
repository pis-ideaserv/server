<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Imports\ProductImport;
use App\Imports\MasterFileImport;
use App\Imports\SupplierImport;
use Maatwebsite\Excel\HeadingRowImport;
use Storage;


class processUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $productHeader = [
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
        $masterFileHeader = [
            'product_code',
            'product_name',
            'category',
        ];
        $supplierHeader = [
            'supplier_code',
            'supplier_name',
            'address',
            'tin',
            'contact_person',
            'contact_number',
            'email',
        ];

        $processing = Notification::where('status','=','processing');
        $queue = Notification::where('status','=','queue');
        if($processing->count() == 0 && $queue->count() != 0){
            
            $filename = $queue->first()->filename;
            $id       = $queue->first()->user;
            $type     = $queue->first()->type;

            //get header on first sheet and filter to remove empty values
            $headings = array_filter((new HeadingRowImport)->toArray(storage_path()."/app/temp/".$filename)[0][0]);
            

            $notification = Notification::where('filename','=',$filename);
            $notification->update(['status' => 'processing']);

            switch($type){
                case "product":
                    if(sizeof(array_diff($productHeader,$headings)) != 0){
                        $notification->update([
                            'result' => json_encode(['message' => 'Invalid sheet file format']),
                            'status' => 'failed'
                        ]);
                        break;
                    }

                    $a = new ProductImport($id,$filename);
                    $a->queue(storage_path()."/app/temp/".$filename);
                    break;
                case "masterfile":
                    if(sizeof(array_diff($masterFileHeader,$headings)) != 0) {
                        $notification->update([
                            'result' => json_encode(['message' => 'Invalid sheet file format']),
                            'status' => 'failed'
                        ]);
                        break;
                    }
                    $a = new MasterFileImport($id,$filename);
                    $a->queue(storage_path()."/app/temp/".$filename);
                    break;
                case "supplier":
                    if(sizeof(array_diff($supplierHeader,$headings)) != 0){
                        $notification->update([
                            'result' => json_encode(['message' => 'Invalid sheet file format']),
                            'status' => 'failed'
                        ]);
                        break;
                    }
                    $a = new SupplierImport($id,$filename);
                    $a->queue(storage_path()."/app/temp/".$filename);
                    break;
            }

            $notification = Notification::where('filename','=',$filename);
            if($notification->first()->status == "processing"){
                $notification->update([
                    'status' => 'done'
                ]);
            }
            Storage::delete('temp/'.$filename);
        }
        return;
    }
}
