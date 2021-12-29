<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Http\Request;
use Pusher\Pusher;
use Validator;
use App\User;

class PusherAuthenticationController extends BaseController
{

    public function authenticate(Request $request)
    {
        if($this->user !== null)
        {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                ['cluster' => env('PUSHER_APP_CLUSTER')]
            );

            $auth_string = $pusher->socket_auth($request->get('channel_name'), $request->get('socket_id'));

            return $auth_string;
        }

        return response('Forbidden', 403);
    }
}
