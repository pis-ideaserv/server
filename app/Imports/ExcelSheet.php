<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;

class ExcelSheet implements ToCollection
{
	use Importable;
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
    
        foreach ($Collection as $row) 
        {
            dd($row[0]);
        }
    	// $arr = [];
     //    foreach ($collection as $row) {
     //    	array_push($arr, [
     //    		'supplier' => $row[0]
     //    	]);
     //    }
     //    return $arr;
    }
}
