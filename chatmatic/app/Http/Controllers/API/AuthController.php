<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Http\Request;
use Validator;
use App\User;
use App\AppSumoUser;

class AuthController extends BaseController
{

    private $api_token;

    public function __construct()
    {
        // Generate Token
        $this->api_token = uniqid(base64_encode(str_random(60)), false);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postLogin(Request $request)
    {
        // Validations
        $rules = [
            'facebook_user_id'      => 'required',
            'facebook_long_token'   => 'required',

        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
        {
            // Validation failed
            return response()->json([
                'error'     => 1,
                'error_msg' => $validator->messages(),
            ]);
        }
        else
        {
            // Init facebook api helper
            $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
            $client->initClient();

            // Verify the user token
            $facebook_long_token    = $request->get('facebook_long_token');
            $token_check            = $client->verifyUserAccessToken($facebook_long_token);

            // Check that the token was valid
            if($token_check['success'])
            {
                // Exchange this token (that we're referring to as a long_token for some reason but it's not) to a long-lived token
                $facebook_long_token = $client->getLongLivedUserAccessToken($facebook_long_token);

                if($facebook_long_token['error'])
                {
                    return $facebook_long_token;
                }

                // Grab the long-lived token
                $facebook_long_token = $facebook_long_token['fb_response']->access_token;

                // Check that the token belongs to the same user making this request
                if($token_check['facebook_user_id'] === $request->get('facebook_user_id'))
                {
                    // Fetch User
                    $user = User::where('facebook_user_id', $request->get('facebook_user_id'))->first();

                    // Get user info from facebook
                    $user_data = $client->getUserDetails($facebook_long_token);

                    // If we got back a string something is wrong, let's dump it and throw an error
                    if(is_string($user_data['fb_response']))
                    {
                        return response()->json([
                            'error'     => 1,
                            'error_msg' => 'Error with Facebook login. Please contact Chatmatic support.'
                        ]);
                    }

                    $user_data = $user_data['fb_response']->getBody();
                    $user_json = json_decode($user_data);

                    $profile_photo_url = $client->getUserProfilePhotoURL($request->get('facebook_user_id'), $facebook_long_token);
                    if($profile_photo_url['success'])
                        $profile_photo_url = $profile_photo_url['url'];
                    else
                        $profile_photo_url = '';

                    // Create user if it doesn't exist
                    if( ! $user)
                    {
                        // Get their ext api token and confirm it's not already in use
                        $ext_api_token = str_random(28);
                        $in_use = User::where('ext_api_token', $ext_api_token)->first();
                        if($in_use)
                        {
                            // Try with another
                            $ext_api_token = str_random(30);
                            $in_use = User::where('ext_api_token', $ext_api_token)->first();
                            if($in_use)
                            {
                                $ext_api_token = str_random(32);
                                $in_use = User::where('ext_api_token', $ext_api_token)->first();

                                if($in_use)
                                {
                                    // If we got here and we still have a token that's already in use... well... we're really unlucky or we've got _a lot_ of users and this needs to be re-built anyway.
                                    $ext_api_token = str_random(34);
                                }
                            }
                        }

                        // Get next UID
                        $next_uid = \DB::select("SELECT nextval('chatmatic_users_uid_seq');");
                        $next_uid = $next_uid[0]->nextval;
                        $referral = $request->get('referral');
                        $user = [
                            'uid'                   => $next_uid,
                            'facebook_user_id'      => $request->get('facebook_user_id'),
                            'facebook_email'        => $user_json->email ?? '',
                            'facebook_name'         => $user_json->name ?? '',
                            'facebook_long_token'   => $facebook_long_token,
                            'ext_api_token'         => $ext_api_token,
                            'created_at_utc'        => Carbon::now('UTC'),
                            'updated_at_utc'        => Carbon::now('UTC'),
                            'referred'              => $referral,
                        ];
                        // User not found, set it up
                        $user = User::insert($user);

                        // Check if this user is related with an admin added by other user
                        $admins = \App\PageAdmin::where('email',$user_json->email)->get();

                        if ( $admins->count() > 0 )
                        {
                            // Let's update the page_admins, and chatmatic_user_page_map tables
                            foreach ($admins as $key => $admin) {
                                $admin->user_uid = $next_uid;
                                $admin->save();

                                // Save to chatmatic_user_page_map table
                                $user_page_map_record = [
                                            'chatmatic_user_uid'          => $next_uid,
                                            'page_uid'                    => $admin->page_uid,
                                            'facebook_page_access_token'  => '',
                                        ];
                                \DB::table('chatmatic_user_page_map')->insert($user_page_map_record);
                            }
                        }
                        
                        // Here we have to check if this user comes from app-sumo
                        if ($request->get('sumo_user_id')){
                            // We have a sumoling user, let's tie this user 
                            // with the app-sumo one

                            // Decript the uid
                            $app_sumo_uid = decrypt($request->get('sumo_user_id'));

                            $sumo_user = AppSumoUser::find($app_sumo_uid);
                            if ($sumo_user)
                            {
                                $sumo_user->chatmatic_user_id = $next_uid;
                                $sumo_saved = $sumo_user->save();

                                if ( ! $sumo_saved)
                                {
                                    return response()->json([
                                    'error'     => 1,
                                    'error_msg' => 'App Sumo User not upated with chatatic one.'
                                ]);       
                                }
                            }
                            else
                            {
                                return response()->json([
                                    'error'     => 1,
                                    'error_msg' => 'App Sumo User not found.'
                                ]);
                            }

                        }
                        

                        if( ! $user)
                        {
                            return response()->json([
                                'error'     => 1,
                                'error_msg' => 'User creation failed.',
                            ]);
                        }
                        $user = User::find($next_uid);
                    }
                    else // Otherwise we'll update it
                    {
                        if(isset($user_json->email) && mb_strlen($user_json->email) > 0)
                            $user->facebook_email   = $user_json->email;
                        if($user_json->name && mb_strlen($user_json->name) > 0)
                            $user->facebook_name    = $user_json->name ?? '';
                        $user->facebook_long_token  = $facebook_long_token;
                        $user->save();
                    }

                    // If we have a user we'll return the token
                    if($user)
                    {
                        // Update Token / Login the user
                        $user->api_token    = $this->api_token;
                        $login              = $user->save();

                        if($login)
                        {
                            return response()->json([
                                'success'                   => 1,
                                'access_token'              => $this->api_token,
                                'facebook_name'             => $user->facebook_name,
                                'facebook_email'            => $user->facebook_email,
                                'facebook_profile_image'    => $profile_photo_url,
                                'user_id' => $user->uid
                            ]);
                        }
                    }
                    else
                    {
                        return response()->json([
                            'error'     => 1,
                            'error_msg' => 'User not found',
                        ]);
                    }
                }
                else
                {
                    return response()->json([
                        'error'     => 1,
                        'error_msg' => 'Login user mismatch.'
                    ]);
                }
            }
            else
            {
                return response()->json([
                    'error'     => 1,
                    'error_msg' => 'Invalid token provided.'
                ]);
            }
        }
    }

    public function postLogout(Request $request)
    {
        $token  = $request->header('Authorization');
        $user   = User::where('api_token', $token)->first();

        if($user)
        {
            $user->api_token    = null;
            $logout             = $user->save();

            if($logout)
            {
                return response()->json([
                    'success'   => 1,
                ]);
            }
        }
        else
        {
            return response()->json([
                'error'     => 1,
                'error_msg' => 'User not found',
            ]);
        }
    }
}

