<?php

namespace App\Http\Middleware;

use JWTAuth;
use Status;
use Auth;
use Closure;

class JWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(
                [
                    "message" => "Token expired"
                ] , Status::HTTP_UNAUTHORIZED);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(
                [
                    "message" => "Token Invalid"
                ] , Status::HTTP_UNAUTHORIZED);
        }catch (\Tymon\JWTAuth\Exception\TokenBlacklistedExceptions $e) {
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
        return $next($request);
    }
}
