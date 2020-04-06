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
            return response()->json([
                'token' => Token::create(),
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);

        }

        return response()->json(
        	[
        		'status' 	=> 	'error',
        		'message'	=>	'An Error has occured! Unauthenticated.',
        	]
        	,Status::HTTP_UNAUTHORIZED
    	);
    }

    public function refresh(){

        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'token' => Token::refresh(),
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(
                [
                    "message" => "Token Invalid"
                ] , Status::HTTP_UNAUTHORIZED);
        }catch (\Tymon\JWTAuth\Exception\TokenBlacklistedException $e) {
            return response()->json(
                [
                    "message" => "Token Invalid"
                ] , Status::HTTP_UNAUTHORIZED);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(
                [
                    "message" => "Token not found"
                ] , Status::HTTP_BAD_REQUEST);
        }catch(\Illuminate\Http\Exceptions\ThrottleRequestsException $e){
            return response()->json(
                [
                    "message" => "Too many request"
                ] , Status::HTTP_TOO_MANY_REQUESTS);
        }


        return response()->json([
                'token' => Token::refresh(),
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function check(){
        return [
            'message' => 'Token is valid',
            'payload' => auth()->payload()->toArray(),
        ];
    }

    public function logout(){
        auth()->logout(true);

        return [
            'message' => 'Successfully logout!'
        ];
    }
}
