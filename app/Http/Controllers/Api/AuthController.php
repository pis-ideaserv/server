<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Status;
use JWTAuth;
use App\Helpers\Token;
use JWTFactory;
use App\Models\Logs;

class AuthController extends Controller
{
    public function login(Request $request){

        $credentials = $request->only('username', 'password');

    	if (Auth::attempt($credentials)) {
            if(Auth::user()->activated == 0){
                return response()->json([
                    "status"  => false,
                    "message" => "Account deactivated"
                ],Status::HTTP_UNAUTHORIZED);    
            }


            return [
                'token'         => Token::create(),
                'token_type'    => 'bearer',
                'expires_in'    => auth()->factory()->getTTL() * 60
            ];
        }

        return response()->json(
        	[
        		'status' 	=> 	false,
        		'message'	=>	'Credentials does not exist.',
        	]
        	,Status::HTTP_UNAUTHORIZED
    	);
    }

    public function refresh(){

        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return [
                'token'         => Token::refresh(),
                'token_type'    => 'bearer',
                'expires_in'    => auth()->factory()->getTTL() * 60
            ];
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(
                [
                    "status"    => false,
                    "message"   => "Token Invalid",
                ] , Status::HTTP_UNAUTHORIZED);
        }catch (\Tymon\JWTAuth\Exception\TokenBlacklistedException $e) {
            return response()->json([
                    "status"    => false,
                    "message"   => "Token Invalid"
                ] , Status::HTTP_UNAUTHORIZED
            );
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(
                [
                    "status"    => false,  
                    "message"   => "Token not found",
                ] , Status::HTTP_BAD_REQUEST);
        }catch(\Illuminate\Http\Exceptions\ThrottleRequestsException $e){
            return response()->json(
                [
                    "status"    => false, 
                    "message"   => "Too many request",
                ] , Status::HTTP_TOO_MANY_REQUESTS);
        }


        return [
                'token'         => Token::refresh(),
                'token_type'    => 'bearer',
                'expires_in'    => auth()->factory()->getTTL() * 60
        ];
    }

    public function me(){
        return auth()->user();
    }

    public function logout(){
        auth()->logout(true);

        return [
            "message"   =>  'Successfully logout!'
        ];
    }
}
