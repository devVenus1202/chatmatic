<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\TriggerConfTrigger;

class PostController extends BaseController
{
    /**
     * @param Request $request
     * @param $page_uid
     * @return mixed
     */
    public function index(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $response['posts']      = [];
        $response['triggers']   = [];

        // Update posts for this page
        $page->updatePosts();
        
        // Is this request passing along a 'facebook_post_id' parameter? If so, we'll filter it, otherwise, default results
        if($request->has('facebook_post_id'))
        {
            $post_id = $page->fb_id.'_'.$request->get('facebook_post_id');

            // Populate posts array
            $posts = $page->posts()
                ->where('facebook_post_id', $post_id)
                ->get();
        }
        else
        {
            // Populate posts array
            $posts = $page->posts()
                ->orderBy('facebook_created_time_utc', 'desc')
                //->whereDate('facebook_created_time_utc', '>', Carbon::now()->subDays(60))
                ->take(100)
                ->get();
        }

        foreach($posts as $post)
        {
            // Does this page have a trigger?
            $has_trigger        = false;
            $trigger_uid        = null;
            $trigger_status     = 0;
            $trigger = TriggerConfTrigger::where('post_uid',$post->uid)->first();
            if( isset($trigger) )
            {
                $has_trigger    = true;
                $trigger_uid    = $trigger->workflowTrigger()->first()->uid;
                $trigger_status = $trigger->active;
            }

            $facebook_post_id = $post->facebook_post_id;
            if(stristr($facebook_post_id, '_'))
            {
                $facebook_post_id = explode('_', $facebook_post_id);
                $facebook_post_id = $facebook_post_id[1];
            }

            $response['posts'][] = [
                'uid'               => $post->uid,
                'trigger'           => $has_trigger,
                'trigger_status'    => $trigger_status,
                'trigger_uid'       => $trigger_uid,
                'permalink_url'     => $post->permalink_url,
                'message'           => $post->message,
                'picture'           => $post->picture,
                'comments'          => $post->comments,
                'facebook_post_id'  => $facebook_post_id,
                'facebook_created_time_utc' => $post->facebook_created_time_utc,
            ];
        }

        // Populate triggers array
        foreach($page->workflowTriggers->where('type','post_trigger') as $trigger)
        {
            if(! $trigger->archived)
                $active = true;
            else
                $active = false;

            $trigger_conf = $trigger->postTrigger()->first();

            $response['triggers'][] = [
                'uid'               => $trigger->uid,
                'post_uid'          => $trigger_conf->post_uid,
                'active'            => $active,
                'message'           => $trigger_conf->message,
                'workflow_uid'      => $trigger->workflow_uid,
                'messages_sent'     => $trigger->messages_delivered,
                'messages_opened'   => $trigger->messages_read,
            ];
        }

        return $response;
    }
}
