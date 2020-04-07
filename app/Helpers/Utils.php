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
	
	static function Filter($filter,$filt=null,$command=null,$object=""){
		$array = [];

		if($command == null){
			foreach ($filter as $key => $value) {
                if($value != null){
                    switch($value->filter){
                        case "iet" :
                            array_push($array, [$key, '=',$value->key]);
                            break;
                        case "inet" :
                            array_push($array, [$key, '!=',$value->key]);
                            break;
                        case "c" :
                            array_push($array, [$key, 'like','%'.$value->key.'%']);
                            break;
                        case "dnc" :
                            array_push($array, [$key, 'not like','%'.$value->key.'%']);
                            break;
                        case "sw" :
                            array_push($array, [$key, 'like',$value->key.'%']);
                            break;
                        case "ew" :
                            array_push($array, [$key, 'like','%'.$value->key]);
                            break;
                    }
                }
			}
			return $array;
		}

		if(property_exists($filt,$object)){
			switch ($filt[$object]->filter) {
				case "iet" :
					array_push($array, [$command, '=',$filt[$object]->key]);
					break;
				case "inet" :
					array_push($array, [$command, '!=',$filt[$object]->key]);
					break;
				case "c" :
					array_push($array, [$command, 'like','%'.$filt[$object]->key.'%']);
					break;
				case "dnc" :
					array_push($array, [$command, 'not like','%'.$filt[$object]->key.'%']);
					break;
				case "sw" :
					array_push($array, [$command, 'like',$filt[$object]->key.'%']);
					break;
				case "ew" :
					array_push($array, [$command, 'like','%'.$filt[$object]->key]);
					break;
			}
			return $array;
		}
		return $array;

	}

}
