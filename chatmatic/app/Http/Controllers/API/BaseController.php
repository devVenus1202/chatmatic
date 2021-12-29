<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{

    protected $user;
    protected $start_time;

    public function __construct(Request $request)
    {
        $this->start_time = time();

        $token      = $request->headers->get('authorization');
        $token      = str_replace('Token ', '', $token);
        $user       = User::where('api_token', $token)->first();

        if($user)
        {
            // If it's burrel, force travis
            /*
            if($user->facebook_email == 'chatmatic.testers@hotmail.com')
            {
                if(\App::environment() === 'staging')
                    $user = User::find(12);
                elseif(\App::environment() === 'production')
                    $user = User::find(3);
            }
            */
        }

        $this->user = $user;
    }

    /**
     * @param $page_uid
     * @return array
     */
    public function getPage($page_uid)
    {
        $response = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
            'page'      => null
        ];

        if($page_uid === 'undefined')
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Page uid malformed.';

            return $response;
        }

        /** @var \App\Page $page */
        $page = $this->user->pages()->where('uid', $page_uid)->firstOrFail();
        // TODO: Confirm user's permissions on $page

        if( ! $page)
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Page not found.';

            return $response;
        }

        $response['success']    = 1;
        $response['page']       = $page;

        return $response;
    }
}
