<?php

namespace App\Http\Controllers\API\AppSumo;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Cache;

class AuthController extends Controller
{
    protected $user;

    public function index(Request $request)
    {

        $username = $request->get('username');
        $password = $request->get('password');

        if($username && $password){
            \Log::debug(print_r($password, 1));
            if($username ===  getenv('APPSUMO_USER') && $password === getenv('APPSUMO_KEY')){
                $ext_api_token = str_random(28);

                // Saving this temporaly in cache
                Cache::store('redis')->put($ext_api_token,$ext_api_token,1);

                return response()->json([
                                'access' => $ext_api_token,
                        ]);
            }
            else{
                return response()->json([
                    'error'     => 1,
                    'error_msg' => 'Invalid user - api key provided',
                ]);                
            }
        }
        else{

            return response()->json([
                    'error'     => 1,
                    'error_msg' => 'No data provided',
                ]);
        }
    }
}