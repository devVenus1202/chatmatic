<?php

namespace App\Http\Controllers\API\Zapier;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $user;

    public function index(Request $request)
    {
        /**
        // TODO: Get the authData here and confirm that requests are authenticated for whatever page
        $auth_data  = $request->get('authData');
        $api_key    = $auth_data['api_key'];

        // TODO: confirm API key
        $this->user = User::where('ext_api_token', $api_key)->first();
        **/

        $api_key = $request->get('api_key');
        $this->user = User::where('ext_api_token', $api_key)->first();

        if($this->user)
            return json_encode([
                'email' => $this->user->facebook_email
            ]);
        else
            return response('', 500);
    }
}
