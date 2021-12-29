<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\ChatmaticFeedUpdate;
use App\ChatmaticFeedTip;

class PageController extends BaseController
{
    /**
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'pages'         => [],
            'ext_api_token' => '',
        ];

        /** @var \App\User $user */
        $user = $this->user;

        // If the request has the 'refresh' key we'll refresh the users page list
        if($request->has('refresh') && ($request->get('refresh') === true || $request->get('refresh') === 'true'))
        {
            $user->updateFanPagesList();
        }

        // All pages info
        $total_active_pages         = 0;
        $total_subscribers          = 0;
        $total_recent_subcribers    = 0;
        $total_sequences            = 0;

        // subscribers history
        $history_days = 7;
        $response_array = [];

        /** @var \App\Page $page */
        $pages = [];
        foreach($user->pages()->where('is_connected',1)->get() as $page)
        {
            $licence = $page->licenses()->first();

            $is_licenced = false;
            if($licence)
                $is_licenced = true;

            $subscribers = $page->subscribers()->count();
            $sequences = $page->workflows()->count();
            $recent_subscribers = $page->subscribers()->where('created_at_utc', '>=', Carbon::now()->subDays(30))->count();

            $sms_account = false;
            if( $page->sms_balance )
            {
                $sms_account = true;
            }
            

            $pages[] = [
                'uid'               => $page->uid,
                'fb_name'           => $page->fb_name,
                'fb_id'             => $page->fb_id,
                'fb_page_token'     => $page->fb_page_token,
                'is_connected'      => $page->is_connected,
                'subscribers'       => $subscribers,
                'page_likes'        => $page->page_likes,
                'recent_subscribers'=> $recent_subscribers,
                'comments'          => $page->comments()->count(),
                'sequences'         => $sequences,
                'active_triggers'   => $page->workflowTriggers()->where('archived','!=',1)->count(), // TODDO, update this on table pages
                'created_at_utc'    => $page->created_at_utc->toDateTimeString(),
                'fb_cover_photo'    => $page->coverPhotoURL(),
                'menus_active'      => $page->persistent_menus_active,
                'licensed'          => $is_licenced,
                'sms_account'       => $sms_account,
                'token_error'       => $page->token_error,
            ];

            // Total subscribers data
            if (  empty($response_array) )
            {
                $start_array = True;
            }
            else
            {
                $start_array = False;
            }

            foreach($page->subscriberCountHistory()->orderBy('uid', 'desc')->take($history_days)->get() as $key => $count_history)
            {
                if (  $start_array )
                {
                    $response_array[] = [
                        'date'  => Carbon::createFromTimestamp(strtotime($count_history->date_utc))->format('Y-m-d'),
                        'total' => $count_history->maximum
                    ];
                }
                else
                {
                    // For some reason all the subscribers are not being updated
                    if (isset($response_array[$key]))
                    {
                        $response_array[$key]['total'] += $count_history->maximum;
                    }
                }
                
            }

            // let's update the all pages info
            if ($page->is_connected == 1)
            {
                $total_active_pages         += 1;
                $total_subscribers          += $subscribers;
                $total_sequences            += $sequences;
                $total_recent_subcribers    += $recent_subscribers;
            }
        }

        // updates feed
        $updates = [];
        foreach(ChatmaticFeedUpdate::orderBy('uid', 'desc')->take(5)->get() as $update)
        {
            $updates[] = [
                'id'             => $update->uid,
                'content'        => $update->content
            ];
        }

        // tips feed
        $tips = [];
        foreach(ChatmaticFeedTip::orderBy('uid', 'desc')->take(5)->get() as $tip)
        {
            $tips[] = [
                'id'         => $tip->uid,
                'url'        => $tip->content
            ];
        }

        // this is for app sumo users
        if ( $user->sumoUser ){

            switch ($user->sumoUser->plan_id) {
                case 'chatmatic_tier1':
                    $available_licenses = 1;
                    break;

                case 'chatmatic_tier2':
                    $available_licenses = 10;
                    break;

                case 'chatmatic_tier3':
                    $available_licenses = 25;
                    break;
                
                case 'chatmatic_tier4':
                    $available_licenses = 50;
                    break;

                case 'chatmatic_tier5':
                    $available_licenses = 100;
                    break;

                default:
                    $available_licenses = 0;
                    break;
            }

            $response['is_sumo_user']             = true;
            $response['used_licenses']            = $user->sumoUser->used_licenses;
            $response['available_licenses']      = $available_licenses;


        }else{
            $response['is_sumo_user']             = false;
        }


        $response['ext_api_token']             = $user->ext_api_token;
        $response['total_pages']               = $total_active_pages;
        $response['total_sequences']           = $total_sequences;
        $response['total_subscribers']         = $total_subscribers;
        $response['total_recent_subscribers']  = $total_recent_subcribers;

        $response['updates'] = $updates;
        $response['tips']    = $tips;

        $response['total_subscribers'] = $response_array;


        $response['success']    = 1;
        $response['pages']      = $pages;

        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function showAll(Request $request)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'pages'         => [],
            'ext_api_token' => '',
        ];

        /** @var \App\User $user */
        $user = $this->user;

        $user->updateFanPagesList();

        $response_array = [];

        /** @var \App\Page $page */
        $pages = [];
        foreach($user->pages()->get() as $page)
        {
            $licence = $page->licenses()->first();

            $is_licenced = false;
            if($licence)
                $is_licenced = true;

            $pages[] = [
                'uid'               => $page->uid,
                'fb_name'           => $page->fb_name,
                'fb_id'             => $page->fb_id,
                'fb_page_token'     => $page->fb_page_token,
                'is_connected'      => $page->is_connected,
                'created_at_utc'    => $page->created_at_utc->toDateTimeString(),
                'fb_cover_photo'    => $page->coverPhotoURL(),
                'licensed'          => $is_licenced,
            ];

        }

        $response['success']    = 1;
        $response['pages']      = $pages;

        return $response;
    }

    /**
     * Update the page (currently only is_connected is updated here)
     *
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function update(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Update the 'is_connected' state of this page
        $is_connected       = (int) $request->get('is_connected');

        // If the value passed isn't 0 or 1 throw an error
        if( ! in_array($is_connected, [0,1]))
            return [
                'success'   => false,
                'error'     => 1,
                'error_msg' => 'Non binary value for "is_connected" provided. Value must be 0 or 1'
            ];

        // If we're attempting to connect a page...
        if($is_connected)
        {
            // We'll connect the page to our app
            $response = $page->connectToChatmatic($this->user->facebook_long_token);
            // We'll remove the error flag if it has
            $page->token_error = false;
        }
        else
        {
            // We'll disconnect the page from our app
            $response = $page->disconnectFromChatmatic();
        }

        // If there was an error in the attempt to connect or disconnect the page we'll return that here
        if($response['error'] === 1)
            return $response;

        $page->is_connected = $is_connected;
        $page->save();

        return ['success' => true];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function connectAll(Request $request)
    {
        $pages = $this->user->pages()->where('is_connected', 0)->get();

        $error_bucket = [];
        foreach($pages as $page)
        {
            if( ! $page->is_connected)
            {
                // We'll connect the page to our app
                $response = $page->connectToChatmatic($this->user->facebook_long_token);
                if($response['error'] === 0)
                {
                    $page->is_connected = true;
                    $page->save();
                }
                // If there's an error we'll catch and put them in a bucket here
                elseif($response['error'] === 1)
                {
                    $error_bucket[] = [
                        $response['error_msg'].' Page: '.$page->fb_name,
                    ];
                }
            }
        }

        return [
            'success'       => true,
            'error_bucket'  => $error_bucket
        ];
    }

    /**
     *
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function homeData(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $response = [
            'error'         => 0,
            'success'       => 0,
            'error_msg'     => '',
            'subscribers'   => [],
        ];

        // Let's retrieve the recent subscribers
        $last_subscribers = $page->subscribers()->where('partial_subscriber',false)->orderBy('uid','desc')->take(10)->get();

        foreach( $last_subscribers as $subscriber )
        {
            $response['subscribers'][] = [
                'uid'                   => $subscriber->uid,
                'psid'                  => $subscriber->user_psid,
                'first_name'            => $subscriber->first_name,
                'last_name'             => $subscriber->last_name,
                'profile_picture'       => $subscriber->profile_pic_url
            ];
        }

        // Let's retrieve worflows data
        foreach ($page->workflows()->where('archived',false)->orderBy('uid','desc')->take(10)->get() as $workflow) 
        {

            // Total subscribers who have done the workflow
            $total_subscribers = \App\Subscription::where('workflow_uid',$workflow->uid)->groupBy('workflow_trigger_uid')->count();

            $response['workflows'][] = 
            [
                "uid"               => $workflow->uid,
                "name"              => $workflow->name,
                "created_at_utc"    => $workflow->created_at_utc,
                "total_subscribers" => $total_subscribers
            ];
        }

        // Let's retrieve automations data
        foreach ($page->automations()->orderBy('uid','desc')->take(10)->get() as $automation) 
        {
            // Total automations fired
            $total_automations = \App\AutomationExecution::where('automation_uid',$automation->uid)->count();

            $response['automations'][] =
            [
                "uid"              => $automation->uid,
                "name"             => $automation->name,
                "total_fired"      => $total_automations
            ];    
        }
        
        return $response;
    }

    /**
         *
         * @param Request $request
         * @param $page_uid
         * @return array
     */
    public function updateGreeting(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $response = [
            'error'         => 0,
            'success'       => 0,
            'error_msg'     => '',
            'subscribers'   => [],
        ];

        $message = $request->get('message');

        //
        if (! isset($message))
        {
            $response['error']      = 1;
            $response['error_msg']  = 'A greeting message must be provided';

            return $response;
        }

        // Message only can accpet 160 characters
        if (strlen($message) >= 160)
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Only are allowed until 160 characters';

            return $response;
        }

        $response = $page->updateGreeting($message);

        if($response['error'] ==- 1)
        {
            return [
                'error'         => 1,
                'error_msg'     => $domains['error_msg']

            ];   
        }

        // Update the greeting on database
        $page->greeting = $message;
        $page->save();

        return [
            'success'       => 1
        ];

    }
}
