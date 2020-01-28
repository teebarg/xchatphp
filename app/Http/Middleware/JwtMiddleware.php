<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use App\Helpers\ResponseHelper;
use App\Helpers\ResponseMessages;
use App\Helpers\ResponseCodes;

class JWTMiddleware extends BaseMiddleware
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
        $token = JWTAuth::getToken();
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return ResponseHelper::createErrorResponse(ResponseMessages::TOKEN_INVALID, ResponseCodes::RESOURCE_AUTHORISATION_ERROR, [], ResponseCodes::UNAUTHENTICATED);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                try {
                    //code...
                    $token = JWTAuth::refresh($token); // might fail
                    JWTAuth::setToken($token);
                    $user = JWTAuth::authenticate($token);

                } catch (Exception $e) {
                    return ResponseHelper::createErrorResponse(ResponseMessages::TOKEN_EXPIRED, ResponseCodes::RESOURCE_AUTHORISATION_ERROR, [], ResponseCodes::UNAUTHENTICATED);
                }
            }else if($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException){
                return ResponseHelper::createErrorResponse(ResponseMessages::TOKEN_BLACKLISTED, ResponseCodes::RESOURCE_AUTHORISATION_ERROR, [], ResponseCodes::UNAUTHENTICATED);
            }else{
                dd($e);
                return ResponseHelper::createErrorResponse(ResponseMessages::TOKEN_NOT_FOUND, ResponseCodes::RESOURCE_NOT_FOUND, [], ResponseCodes::UNAUTHENTICATED);
            }
        }
        return $next($request);
    }
}
