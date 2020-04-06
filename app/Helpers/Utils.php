<?php
namespace App\Helpers;

class Utils{

    static function RemakeArray($array){
        $return_array = [];
		foreach($array as $key => $value){
			array_push($return_array, [
				"name" 		=> $key,
				"message"	=> $value[0],
			]);
		}
		return $return_array;
    }

}
