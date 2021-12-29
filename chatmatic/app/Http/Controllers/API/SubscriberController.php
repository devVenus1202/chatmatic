<?php

namespace App\Http\Controllers\API;

use App\AuditLog;
use App\Http\Resources\Subscriber;
use App\Http\Resources\SubscriberExtended;
use Illuminate\Http\Request;

class SubscriberController extends BaseController
{
    /**
     * @param Request $request
     * @param $page_uid
     * @return array
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

        $extended = false;
        if($request->has('extended') && $request->get('extended') === 'true')
        {
            $extended = true;
        }

        $per_page = 100;
        if($request->has('per_page'))
        {
            $per_page = (int) $request->get('per_page');
        }

        $subscribers = $page->subscribers()->orderBy('last_engagement_utc', 'DESC')->paginate($per_page);

        if($extended)
        {
            $response = SubscriberExtended::collection($subscribers);
        }
        else
        {
            $response = Subscriber::collection($subscribers);
        }

        $elapsed    = time() - $this->start_time;
        $event_name = 'page.subscribers.loaded';
        if($extended)
        {
            $event_name .= '.extended';
        }

        AuditLog::create([
            'chatmatic_user_uid'    => $this->user->uid,
            'page_uid'              => $page->uid,
            'event'                 => $event_name,
            'message'               => $response->collection->count().' subscribers loaded in '.$elapsed.' seconds'
        ]);

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $subscriber_uid
     * @return array
     */
    public function show(Request $request, $page_uid, $subscriber_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $subscriber = $page->subscribers()->where('uid', $subscriber_uid)->firstOrFail();

        /** @var \App\Subscriber $subscriber */

        // Populate this subscribers tags
        $subscriber_tags = [];
        foreach($subscriber->tags()->get() as $tag)
        {
            /** @var \App\Tag $tag */
            $subscriber_tags[] = [
                'uid'   => $tag->uid,
                'value' => $tag->value
            ];
        }

        // Loop through the subscriptions associated with this subscriber to return an array of the campaign uids
        // associated with the subscriber.
        $subscriber_triggers = [];
        foreach($subscriber->subscriptions()->get() as $subscription)
        {
            /** @var \App\Subscription $subscription */
            if( ! in_array($subscription->workflow_trigger_uid, $subscriber_triggers, true))
            {
                /** @var \App\WorkflowTrigger $trigger */
                $trigger = $page->workflowTriggers()->where('uid', $subscription->workflow_trigger_uid)->first();

                if ( isset($trigger) )
                {
                    $subscriber_triggers[] = [
                    'uid'           => $subscription->workflow_trigger_uid,
                    'campaign_name' => $trigger->name,
                    'workflow_type' => $trigger->workflow()->first()->name,
                    ];

                }

            }
        }

        $response_array = [
            'campaigns'         => $subscriber_triggers,
            'messages_sent'     => $subscriber->messages_accepted_from_bot,
            'messages_opened'   => $subscriber->messages_read,
            'total_clicks'      => $subscriber->total_clicks,
            'email'             => $subscriber->email,
            'phone'             => $subscriber->phone_number,
            'location'          => $subscriber->locale,
            'tags'              => $subscriber_tags,
            //'chat_history'      => $subscriber->getSubscriberChatHistoryFromFacebookAPI(),
            'chat_history'      => [],
            'psid'              => $subscriber->user_psid,
        ];

        return $response_array;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $subscriber_uid
     * @return array
     */
    public function update(Request $request, $page_uid, $subscriber_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $subscriber = $page->subscribers()->where('uid', $subscriber_uid)->firstOrFail();

        /** @var \App\Subscriber $subscriber */

        // TODO: Validate this input
        $updated = false;
        if($request->has('email'))
        {
            $email              = $request->get('email');
            $subscriber->email          = $email;
            $updated = true;
        }
        if($request->has('phone'))
        {
            $phone              = $request->get('phone');
            $subscriber->phone_number   = $phone;
            $updated = true;
        }
        if($request->has('location'))
        {
            $location           = $request->get('location');
            $subscriber->locale         = $location;
            $updated = true;
        }
        if($request->has('tags'))
        {
            // Check the tags that came in on the request to confirm any new tags are assigned
            $request_tags               = $request->get('tags') ?? [];
            foreach($request_tags as $new_tag)
            {
                $new_tag_uid = $new_tag['uid'];

                // Check to see if this tag is already assigned to the subscriber, if not, assign it
                $dupe_check = $subscriber->tags()->where('uid', $new_tag_uid)->first();
                if( ! $dupe_check)
                {
                    $subscriber->tags()->attach($new_tag_uid);
                    $updated = true;
                }
            }

            // Check the existing tags against the request tags, remove any existing that aren't included in the request (deleted)
            $existing_tags      = $subscriber->tags;
            foreach($existing_tags as $existing_tag)
            {
                $existing_tag_uid = $existing_tag->uid;

                // Loop through the request tags looking for a match to our $existing_tag_uid
                $found = false;
                foreach($request_tags as $request_tag)
                {
                    $request_tag_uid = $request_tag['uid'];

                    // If we find a match we'll set the $found flag true and drop out of the loop
                    if($request_tag_uid == $existing_tag_uid)
                    {
                        $found = true;
                        break;
                    }
                }

                // If we didn't find the existing tag in the request tags we'll detatch/unassign it
                if( ! $found)
                {
                    $subscriber->tags()->detach($existing_tag_uid);
                }
            }
        }
        if($request->has('is_subscribed'))
        {
            $subscriber->active = $request->get('is_subscribed');
            $updated = true;
        }

        if($updated)
        {
            $subscriber->save();
        }
        
        return ['success' => true];
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $subscriber_uid
     * @return array
     */
    public function toggleLiveChat(Request $request, $page_uid, $subscriber_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $subscriber = $page->subscribers()->where('uid', $subscriber_uid)->firstOrFail();

        /** @var \App\Subscriber $subscriber */

        // TODO: Validate this input
        $status     = $request->get('active_status');
        $subscriber->live_chat_active = $status;
        $subscriber->save();

        return ['success' => true];
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function export(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'subscribers'   => [],
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $subscribers = $page->subscribers()->orderBy('uid', 'asc')->get();
        $subs = [];
        $custom_fields = $page->customFields()->where('archived', false)->orderBy('uid', 'asc')->get();
        

        foreach($subscribers as $subscriber)
        {
            /** @var \App\Subscriber $subscriber */

            // Populate this subscribers tags
            $subscriber_tags = [];
            foreach($subscriber->tags()->get() as $tag)
            {
                /** @var \App\Tag $tag */
                $subscriber_tags[] = [
                    'uid'   => $tag->uid,
                    'value' => $tag->value
                ];
            }
            // Populate this custom_field_responses
            $custom_field_responses = [];
            foreach($custom_fields as $custom_field)
            {
                foreach($custom_field->customFieldResponses()->get() as $custom_field_response) {
                    if ($custom_field_response->subscriber_psid == $subscriber->user_psid) {
                        $custom_field_responses[] = [
                            'field_name' => $custom_field->field_name,
                            'response' => $custom_field_response->response
                        ];
                    }
                }
            }
            // $custom_filed_responses = $subscriber->customFieldResponses()->get();

            $location = '';
            if($subscriber->lat !== null && $subscriber->lon !== null)
                $location = $subscriber->lat.','.$subscriber->lon;

            $subs[] = [
                'uid'           => $subscriber->uid,
                'first_name'    => $subscriber->first_name,
                'last_name'     => $subscriber->last_name,
                'gender'        => $subscriber->gender,
                'email'         => $subscriber->email,
                'phone'         => $subscriber->phone_number,
                'location'      => $location,
                'psid'          => $subscriber->user_psid,
                'tags'          => $subscriber_tags,
                'custom_field_responses' => $custom_field_responses,
            ];
        }


        $response['success'] = 1;
        $response['subscribers'] = $subs;

        return $response;
    }
}
