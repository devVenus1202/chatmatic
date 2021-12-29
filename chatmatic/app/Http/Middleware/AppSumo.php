<?php

namespace App\Http\Middleware;

use Closure;
use Cache;

class AppSumo
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

        if($request->header('Authorization')){

            // Confirm the token matches a user
            $token      = $request->headers->get('authorization');
            $token      = str_replace('Bearer ', '', $token);
            
            $user       = Cache::store('redis')->get($token);

            \Log::debug(print_r($token, 1));

            if($user)
                return $next($request);

            return response()->json([
                'error'     => 1,
                'error_msg' => 'Invalid authorization token.',
            ]);
        }

        return response()->json([
            'error'     => 1,
            'error_msg' => 'Not a valid API request.',
        ]);

    }
}
