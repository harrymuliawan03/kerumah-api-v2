<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class ApiAuthMiddleware
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
        $token = $request->bearerToken();
        $authenticate = true;

        if(!$token) {
            $authenticate = false;
        }

        $user = User::where('token', $token)->first();

        if(!$user){
            $authenticate = false;
        }else {
            Auth::login($user);
        }


        if($authenticate){
            return $next($request);
        }else {
            return response()->json([
                'errors' => [
                    'message' => [
                        'unauthorized'
                    ]
                ]
            ])->setStatusCode(401);
        }
    }
}