<?php

use Illuminate\Database\Seeder;
// use DB;
// use App\Models\ProductMasterList as PML;

class productMasterList extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $product = [
            [
                "product_code"                  => "EPSON-L120",
                "product_name"                  => "EPSON L120",
                "category"		                => rand(1,10),
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "product_code"                  => "ZEBRA-ZT410",
                "product_name"                  => "Zebra zt410",
                "category"		                => rand(1,10),
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "product_code"                  => "HONEYWELL-PC42T",
                "product_name"                  => "Honeywell pc42t",
                "category"		                => rand(1,10),
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "product_code"                  => "LOGITECH-200s",
                "product_name"                  => "Logitech ScanMan 2000",
                "category"		                => rand(1,10),
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "product_code"                  => "LENOVO-4ZQ0R38016",
                "product_name"                  => "Lenovo 4ZQ0R38016 MePOS Pro Wi-Fi POS Tablet",
                "category"		                => rand(1,10),
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "product_code"                  => "MUNBYN-6.0-PDA",
                "product_name"                  => "MUNBYN POS Printers",
                "category"		                => rand(1,10),
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],
        ];
        DB::table('product_master_list')->insert($product);
    }
}
