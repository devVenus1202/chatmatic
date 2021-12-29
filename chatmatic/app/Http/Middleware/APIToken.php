<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class APIToken
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
        if(\App::environment() === 'local')
            return $next($request);

        if($request->header('Authorization')){

            // Confirm the token matches a user
            $token      = $request->headers->get('authorization');
            $token      = str_replace('Token ', '', $token);
            $user       = User::where('api_token', $token)->first();

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
