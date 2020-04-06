<?php

use Illuminate\Database\Seeder;
// use App\Models\Supplier;
// use DB;

class supplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $supplier = [
            [
                "supplier_code"     => "IGM-2025",
                "supplier_name"     => "FIngram Micro",
                "address"           => "12/F Three World Square, 22 Upper McKinley Road, Taguig City",
                "tin"               => "28754554551-5689-485",
                "contact_person"    => "Rommel C. Dela Pena",
                "contact_number"    => "8596325",
                "email"             => "rommel.delapena@ingrammicro.com",
                "created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s'),
            ],[
                "supplier_code"     => "FSP-2015",
                "supplier_name"     => "FSP Technology Group Inc.",
                "address"           => "Unit 10 ZYD Bldg, Street name, City Name, Zip Code",
                "tin"               => "4478-585884-56",
                "contact_person"    => "Wilson Lee",
                "contact_number"    => "859 6325",
                "email"             => "wlee@fsp.com",
                "created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s'),
            ],[
                "supplier_code"     => "ZB-2016",
                "supplier_name"     => "FSP Technology Group Inc.",
                "address"           => "No. 12 KLM Bldg., Street Name, City Name, Zip Code",
                "tin"               => "8794-210487-89",
                "contact_person"    => "Cathy Cruz",
                "contact_number"    => "547 8569",
                "email"             => "ccruz@zebra.com",
                "created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s'),
            ],[
                "supplier_code"     => "HN-2017",
                "supplier_name"     => "Honeywell Philippines",
                "address"           => "23/F Tower Name, Street Name, City Name, Zip Code",
                "tin"               => "2315-547852-63",
                "contact_person"    => "Grace Santos",
                "contact_number"    => "438 5689",
                "email"             => "gsantos@honeywell.com",
                "created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s'),
            ],[
                "supplier_code"     => "DL-1998",
                "supplier_name"     => "Datalogic Systems Corp.",
                "address"           => "No. 15 HHL Bldg., Street Name, City Name, Zip Code",
                "tin"               => "5412-556151-74",
                "contact_person"    => "Joey Smith",
                "contact_number"    => "542 5856",
                "email"             => "jsmith@datalogic.com",
                "created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s'),
            ],[
                "supplier_code"     => "EP-1992",
                "supplier_name"     => "Epson Philippines",
                "address"           => "14/F WTR Building, Street Name, City Name, Zip Code",
                "tin"               => "9648-521457-63",
                "contact_person"    => "Lester Uy",
                "contact_number"    => "879 8796",
                "email"             => "luy@epson.com.ph",
                "created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s'),
            ],
        ];


        DB::table('supplier')->insert($supplier);
    }
}
