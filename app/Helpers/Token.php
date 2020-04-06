<?php
namespace App\Helpers;

use Auth;
use JWTAuth;
use JWTFactory;

class Token{

 	/**
 	*Refreshes token by passing current token
 	* @return $token
 	*/

	static public function refresh(){
		return auth()->refresh();
	}


	/**
	* Creates a new token for login
	*
	* @return $token
	*/

	static public function create(){
		$payload = JWTFactory::sub(Auth::user()->id)->make();
		return JWTAuth::encode($payload)->get();
	}

}
