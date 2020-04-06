<?php

use Illuminate\Database\Seeder;
// use DB;
// use App\Models\Category as categ;

class category extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = [
        	[
        		"name"   			=> "Scanner",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s'),
        	],[
        		"name"   			=> "Printer",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	],[
        		"name"   			=> "Keyboard",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	],[
        		"name"   			=> "Mouse",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	],[
        		"name"   			=> "VFD",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	],[
        		"name"   			=> "Cash Drawer",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	],[
        		"name"   			=> "System Unit",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	],[
        		"name"   			=> "Monitor",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	],[
        		"name"   			=> "POS Terminal",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	],[
        		"name"   			=> "Mobile POS",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	],[
        		"name"   			=> "Mobile Printer",
        		"created_at"        => date('Y-m-d H:i:s'),
                "updated_at"        => date('Y-m-d H:i:s')
        	]
        ];
        
       	DB::table('category')->insert($category);
    }
}
