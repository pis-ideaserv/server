<?php

use Illuminate\Database\Seeder;
// use App\Models\Product;
// use DB;
use Faker\Factory as Faker;

class productSeeder extends Seeder
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
                "supplier"                      => 1,
                "product"                       => rand(1,5),
                "delivery_date"                 => date("Y-m-d",strtotime("12/9/2017")),
                "reference_delivery_document"   => "PO NO AP989099",
                "serial_number"                 => "SX554856631254",
                "warranty"                      => 3,
                "warranty_start"                => date("Y-m-d",strtotime("12/9/2017")),
                "warranty_end"                  => date("Y-m-d",strtotime("+3 year 12/9/2017")),
                "created_by"                    => 2,
                "updated_by"                    => 2,
                "status"                        => 4,
                "remarks"                       => "",
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "supplier"                      => 2,
                "product"                       => rand(1,5),
                "delivery_date"                 => date("Y-m-d",strtotime("10/11/2018")),
                "reference_delivery_document"   => "PO NO AP878998",
                "serial_number"                 => "GF424092428120",
                "warranty"                      => 1,
                "warranty_start"                => date("Y-m-d",strtotime("10/11/2018")),
                "warranty_end"                  => date("Y-m-d",strtotime("+3 year 12/9/2017")),
                "created_by"                    => 2,
                "updated_by"                    => 1,
                "status"                        => 2,
                "remarks"                       => "",
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "supplier"                      => 3,
                "product"                       => rand(1,5),
                "delivery_date"                 => date("Y-m-d",strtotime("12/9/2019")),
                "reference_delivery_document"   => "PO NO ID5242421",
                "serial_number"                 => "SX554856631254",
                "warranty"                      => 3,
                "warranty_start"                => date("Y-m-d",strtotime("12/9/2019")),
                "warranty_end"                  => date("Y-m-d",strtotime("+3 year 12/9/2017")),
                "created_by"                    => 2,
                "updated_by"                    => 1,
                "status"                        => 3,
                "remarks"                       => "",
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "supplier"                      => 1,
                "product"                       => rand(1,5),
                "delivery_date"                 => date("Y-m-d",strtotime("12/9/2016")),
                "reference_delivery_document"   => "PO NO PH491099",
                "serial_number"                 => "SX554856631254",
                "warranty"                      => 6,
                "warranty_start"                => date("Y-m-d",strtotime("12/9/2016")),
                "warranty_end"                  => date("Y-m-d",strtotime("+3 year 12/9/2017")),
                "created_by"                    => 4,
                "updated_by"                    => 1,
                "status"                        => 2,
                "remarks"                       => "",
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "supplier"                      => 1,
                "product"                       => rand(1,5),
                "delivery_date"                 => date("Y-m-d",strtotime("9/9/2017")),
                "reference_delivery_document"   => "PO NO PH636360",
                "serial_number"                 => "SX554856631254",
                "warranty"                      => 5,
                "warranty_start"                => date("Y-m-d",strtotime("9/9/2017")),
                "warranty_end"                  => date("Y-m-d",strtotime("+3 year 12/9/2017")),
                "created_by"                    => 2,
                "updated_by"                    => 5,
                "status"                        => 3,
                "remarks"                       => "",
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "supplier"                      => 3,
                "product"                       => rand(1,5),
                "delivery_date"                 => date("Y-m-d",strtotime("9/9/2007")),
                "reference_delivery_document"   => "PO NO PH6396360",
                "serial_number"                 => "SX55485690631254",
                "warranty"                      => 2,
                "warranty_start"                => date("Y-m-d",strtotime("9/9/2007")),
                "warranty_end"                  => date("Y-m-d",strtotime("+3 year 12/9/2017")),
                "created_by"                    => 2,
                "updated_by"                    => 3,
                "status"                        => 1,
                "remarks"                       => "",
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],[
                "supplier"                      => 1,
                "product"                       => rand(1,5),
                "delivery_date"                 => date("Y-m-d",strtotime("9/9/2017")),
                "reference_delivery_document"   => "PO NO PH636360L",
                "serial_number"                 => "SX554856631254L",
                "warranty"                      => 5,
                "warranty_start"                => date("Y-m-d",strtotime("9/9/2017")),
                "warranty_end"                  => date("Y-m-d",strtotime("+3 year 12/9/2017")),
                "created_by"                    => 2,
                "updated_by"                    => 5,
                "status"                        => 1,
                "remarks"                       => "",
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ],
        ];
        DB::table('product')->insert($product);


        $faker = Faker::create();

        for($i = 0 ; $i < 20; $i++){

            $warranty = $faker->randomDigit;
            $start = $faker->date('Y-m-d','2020-12-31');
            $end = date("Y-m-d",strtotime('+'.$warranty.' year '.$start));


            DB::table('product')->insert([
                "supplier"                      => $faker->numberBetween(1,6),
                "product"                       => rand(1,5),
                "delivery_date"                 => $faker->date('Y-m-d','2020-12-31'),
                "reference_delivery_document"   => 'PO NO '.$faker->bothify('??#######??'),
                "serial_number"                 => $faker->bothify('####??#######??'),
                "warranty"                      => $warranty,
                "warranty_start"                => $start,
                "warranty_end"                  => $end,
                "created_by"                    => $faker->numberBetween(1,5),
                "updated_by"                    => $faker->numberBetween(1,5),
                "status"                        => $faker->numberBetween(1,3),
                "remarks"                       => "",
                "created_at"                    => date('Y-m-d H:i:s'),
                "updated_at"                    => date('Y-m-d H:i:s'),
            ]);


            // $product = new Product();
            // $product->supplier = ;
            // $product->product = ;
            // $product->delivery_date = $faker->date('Y-m-d','2020-12-31');
            // $product->reference_delivery_document = ;
            // $product->serial_number = ;
            // $product->status = $faker->numberBetween(1,3);
            // $product->remarks = '';
            
            // $product->created_by = $faker->numberBetween(1,5);
            // $product->updated_by = $faker->numberBetween(1,5);
            // $product->created_at = date('Y-m-d H:i:s');
            // $product->updated_at = date('Y-m-d H:i:s');

            

            // $product->warranty = $warranty;
            // $product->warranty_start = $start;
            // $product->warranty_end   = $end;

            // $product->save();
        }
    }
}
