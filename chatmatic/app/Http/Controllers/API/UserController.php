<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\User;
use App\UserProfile;
use App\UserFollowing;
use Log;
use App\Notifications\FollowingNotification;
use App\WorkflowTemplate;
use App\StripePurchase;
use DB;
use Carbon;

class UserController extends BaseController
{

    /**
     * @param Request $request
     * @return array
     */
    public function show(Request $request)
    {
        $response_array = [
            'success'           => true,
            'facebook_user_id'  => $this->user->facebook_user_id,
            'facebook_email'    => $this->user->facebook_email,
            'facebook_name'     => $this->user->facebook_name,
        ];

        return $response_array;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function createExtApiKey(Request $request)
    {
        $api_key = str_random(42);

        $this->user->ext_api_key = $api_key;
        $this->user->save();

        return $this->user->ext_api_key;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function sources(Request $request)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'stripe_sources'            => null,
        ];

        /** @var \App\User $user */
        $user = $this->user;

        // Let's validate if this use already has an stripe account 
        $stripe_customer_id = $user->stripe_customer_id;
        if($stripe_customer_id === null)
        {
            $response['success']             = 1;
            $response['stripe_sources']      = null;

            return $response;   
        }

        // Stripe customer object
        $stripe_key = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);

        // Retrieve the customer
        $stripe_customer_object = \Stripe\Customer::retrieve($stripe_customer_id);

        // get the actual only source
        $sources = $stripe_customer_object->sources->data;

        foreach($sources as $source){
        
            $response['stripe_sources'][] = [

                'source_id'  => $source->id,
                'brand'      => $source->card->brand,
                'exp_month'  => $source->card->exp_month,
                'exp_year'   => $source->card->exp_year,
                'last4'      => $source->card->last4
            ];
        }

        return $response;


    }

    public function getProfile(Request $request, $user_id) {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
        ];

        $user = User::with('profile')->find($user_id);
        if (!isset($user)) {
            $response['error'] = 1;
            $response['error_msg'] = "Can not find the user";
            return $response;
        }

        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $response['success'] = 1;
        $response['user']['name'] = $user->facebook_name;

        $facebook_long_token = $user->facebook_long_token;
        $token_check = $client->verifyUserAccessToken($facebook_long_token);
        if ($token_check['success']) {
            $facebook_long_token = $client->getLongLivedUserAccessToken($facebook_long_token);

            if($facebook_long_token['error'])
            {
                return $facebook_long_token;
            }

            $facebook_long_token = $facebook_long_token['fb_response']->access_token;
            $profile_image = $client->getUserProfilePhotoURL($user->facebook_user_id, $facebook_long_token, "large");
            $response['user']['profile_image'] = $profile_image['url'];

            Log::info('profile image url: '.$profile_image['url']);
        }
        
        $response['user']['email'] = $user->facebook_email;
        if (isset($user->profile)) {
            $response['user']['description'] = $user->profile->description;
            $response['user']['facebook_url'] = $user->profile->facebook_url;
            $response['user']['twitter_url'] = $user->profile->twitter_url;
            $response['user']['linkedin_url'] = $user->profile->linkedin_url;
            $response['user']['youtube_url'] = $user->profile->youtube_url;
            $response['user']['other_url'] = $user->profile->other_url;
        }

        return $response;
    }

    public function saveUserInfo(Request $request, $user_id) {
        $user_info = $request->get('user_info');
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'stripe_sources'            => null,
        ];

        Log::info ('saveUserInfo: '.gettype($user_info).json_encode($user_info));

        if ($user_id != $this->user->uid) {
            $response['error'] = 1;
            $response['error_msg'] = "You are not able to edit this profile";
            return $response;
        }

        $user_profile = UserProfile::updateOrCreate(['chatmatic_user_uid' => $user_id], [
            'description' => $user_info['description'],
            'facebook_url' => $user_info['facebook_url'],
            'twitter_url' => $user_info['twitter_url'],
            'linkedin_url' => $user_info['linkedin_url'],
            'youtube_url' => $user_info['youtube_url'],
            'other_url' => $user_info['other_url'],
        ]);
        
        try {
            $user_profile->save();
        } catch(\Exception $e) {
            \DB::rollBack();

            $response['success']      = 0;
            $response['error']        = 1;
            $response['error_msg']    = 'Error saving user description.';

            return $response;
        }

        $response['success'] = 1;
        $response['user']['description'] = $user_profile->description;
        $response['user']['facebook_url'] = $user_profile->facebook_url;
        $response['user']['twitter_url'] = $user_profile->twitter_url;
        $response['user']['linkedin_url'] = $user_profile->linkedin_url;
        $response['user']['youtube_url'] = $user_profile->youtube_url;
        $response['user']['other_url'] = $user_profile->other_url;
        return $response;
    }

    public function getFollowInfo(Request $request, $user_id) {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'stripe_sources'            => null,
        ];

        
        $user = User::with(['followings', 'followers'])->find($user_id);
        if (!isset($user)) {
            $response['error'] = 1;
            $response['error_msg'] = "Can not find the user";
            return $response;
        }

        $response['success'] = 1;
        $response['user_follow_info']['followings'] = $user->followings;
        $response['user_follow_info']['followers'] = $user->followers;

        return $response;
    }

    public function followUser(Request $request) {
        $user_id = $request->get('user_id');
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'stripe_sources'            => null,
        ];

        
        $user = User::find($user_id);
        if (!isset($user)) {
            $response['error'] = 1;
            $response['error_msg'] = "Can not find the user";
            return $response;
        }

        $existing = UserFollowing::where('followee_uid', $user_id)->where('follower_uid', $this->user->uid)->first();
        if (isset($existing)) {
            $response['error'] = 1;
            $response['error_msg'] = "You already follow this user.";
            return $response;
        }

        try {
            $user_following = UserFollowing::create([
                'followee_uid' => $user_id,
                'follower_uid' => $this->user->uid
            ]);
            $user_following->save();
        } catch (\Exception $e) {
            \DB::rollBack();

            $response['success']      = 0;
            $response['error']        = 1;
            $response['error_msg']    = 'Error following user.';

            return $response;
        }

        $user->notify(new FollowingNotification($user_id, $this->user->uid));

        $response['success'] = 1;
        return $response;
    }

    public function getTemplateInfo (Request $request, $user_id) {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'stripe_sources'            => null,
        ];

        $user = User::find($user_id);
        if (!isset($user)) {
            $response['error'] = 1;
            $response['error_msg'] = "Can not find the user";
            return $response;
        }

        $templates = WorkflowTemplate::where('chatmatic_user_uid', $user->uid)->get();

        $template_sold_list = StripePurchase::get()->pluck('template_uid')->all();
        
        $templates_sold = WorkflowTemplate::where('chatmatic_user_uid', $user->uid)->whereIn('uid', $template_sold_list)->get();

        $response['success'] = 1;
        $response['data']['templates'] = $templates;
        $response['data']['templates_sold'] = $templates_sold;

        return $response;
    }

    public function getSalesInfo (Request $request, $user_id) {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
            'stripe_sources'            => null,
        ];

        $user = User::find($user_id);
        if (!isset($user)) {
            $response['error'] = 1;
            $response['error_msg'] = "Can not find the user";
            return $response;
        }

        $total_sales = StripePurchase::where('chatmatic_seller_uid', $user->uid)->sum('total');

        $sales_by_month = StripePurchase::select(DB::raw('sum(total) as data'), DB::raw('EXTRACT(YEAR FROM created_at_utc) as year, EXTRACT(MONTH FROM created_at_utc) as month'))
        ->where('chatmatic_seller_uid', $user->uid)
        ->groupby('year','month')
        ->get();

        $response['success'] = 1;
        $response['data']['total_sales'] = $total_sales;
        $response['data']['sales_by_month'] = $sales_by_month;
        return $response;
    }
}
