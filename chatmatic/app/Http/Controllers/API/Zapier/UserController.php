<?php

namespace App\Http\Controllers\API\Zapier;

use App\Http\Controllers\Controller;
use App\Page;
use App\Subscriber;
use App\Tag;
use App\User;
use App\ZapierEventLog;
use App\ZapierWebhookSubscription;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public $user;

    public function __construct(Request $request)
    {
        $api_key = $request->get('api_key');
        $user = User::where('ext_api_token', $api_key)->first();
        $this->user = $user;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function allPages(Request $request)
    {
        $pages = $this->user->pages()->where('is_connected', 1)->get();

        $return_pages = [];
        foreach($pages as $page)
        {
            $return_pages[] = (object) [
                'id'    => $page->uid,
                'fb_id' => $page->fb_id,
                'name'  => $page->fb_name
            ];
        }

        return $return_pages;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function allTags(Request $request)
    {
        $pages = $this->user->pages()->where('is_connected', 1)->get();

        $return_tags = [];
        foreach($pages as $page)
        {
            $tags = $page->tags;
            foreach($tags as $tag)
            {
                $return_tags[] = (object) [
                    'id'    => $tag->uid,
                    'value' => $tag->value.' ('.$page->fb_name.')'
                ];
            }
        }

        return $return_tags;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function allCustomFields(Request $request)
    {
        $pages = $this->user->pages()->where('is_connected', 1)->get();

        $return_attributes = [];
        foreach($pages as $page)
        {
            $attributes = $page->customFields()->get();
            foreach($attributes as $attribute)
            {
                $return_attributes[] = (object) [
                    'id'    => $attribute->uid,
                    'value' => $attribute->field_name.' ('.$page->fb_name.')'
                ];
            }
        }

        return $return_attributes;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function allWorkflows(Request $request)
    {
        $pages = $this->user->pages()->where('is_connected', 1)->get();

        $return_workflows = [];
        foreach($pages as $page)
        {
            $workflows = $page->workflows()->whereNull('archived_at_utc')->get();

            foreach($workflows as $workflow)
            {
                $return_workflows[] = (object) [
                    'id'    => $workflow->uid,
                    'value' => $workflow->name.' ('.$page->fb_name.')'
                ];
            }
        }

        return $return_workflows;
    }

    /**
     * Apply a tag to a subscriber from a given subscriber psid and tag uid (Zapier Action - "Tag User")
     *
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function tagSubscriber(Request $request)
    {
        $page_fb_id         = $request->get('page_id');
        $subscriber_psid    = $request->get('subscriber_psid');
        $tag_uid            = $request->get('tag_id');

        $page = Page::where('fb_id', $page_fb_id)->first();
        if( ! $page)
        {
            return response('Page not found', 500);
        }

        // Log this zapier event
        $zapier_event = ZapierEventLog::createPageEventRecord($page, $request, 'action', 'tag_subscriber');

        $subscriber = $page->subscribers()->where('user_psid', $subscriber_psid)->first();
        // If the subscriber isn't found, throw an error
        if( ! $subscriber)
        {
            return response('Subscriber not found', 500);
        }

        $tag        = $subscriber->page->tags()->where('uid', $tag_uid)->first();
        // If the tag isn't found, throw an error
        if( ! $tag)
        {
            return response('Tag not found', 500);
        }

        // Determine if this subscriber already has this tag
        $has_tag    = $subscriber->tags()->where('uid', $tag_uid)->first();
        // If not, we'll add it
        if( ! $has_tag)
        {
            $subscriber->tags()->attach($tag_uid);
        }

        $response = $subscriber->cleanDataForZapier($tag);

        // Log the response to the zapier_event record
        //$zapier_event->response = json_encode($response);
        //$zapier_event->save();

        return $response;
    }

    /**
     * Insert/Update a custom field response for a given subscriber psid and response text
     *
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateSubscriberCustomField(Request $request)
    {
        $page_fb_id         = $request->get('page_id');
        $subscriber_psid    = $request->get('subscriber_psid');
        $custom_field_uid   = $request->get('user_attribute_id');
        $custom_field_value = $request->get('user_attribute_value');

        $page = Page::where('fb_id', $page_fb_id)->first();
        if( ! $page)
        {
            return response('Page not found', 500);
        }

        // Log this zapier event
        $zapier_event = ZapierEventLog::createPageEventRecord($page, $request, 'action', 'update_custom_field');

        $subscriber = $page->subscribers()->where('user_psid', $subscriber_psid)->first();
        if( ! $subscriber)
        {
            return response('Subscriber not found', 500);
        }

        $custom_field = $subscriber->page->customFields()->where('uid', $custom_field_uid)->first();
        if( ! $custom_field)
        {
            return response('User Attribute not found', 500);
        }

        // Look for an existing custom field response, if there isn't one, create it, otherwise update it.
        $update = $subscriber->fillCustomField($custom_field, $custom_field_value);

        $response = $subscriber->cleanDataForZapier($custom_field);

        // Log the response to the zapier_event record
        //$zapier_event->response = json_encode($response);
        //$zapier_event->save();

        return $response;
    }

    /**
     * Find subscriber by email for Zapier action
     *
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function findSubscriber(Request $request)
    {
        $subscriber_email   = trim($request->get('subscriber_email'));
        $page_fb_id         = $request->get('page_id');

        $page = Page::where('fb_id', $page_fb_id)->first();
        if( ! $page)
        {
            return response('Page not found', 500);
        }

        // Log this zapier event
        $zapier_event       = ZapierEventLog::createPageEventRecord($page, $request, 'action', 'find_subscriber');

        $subscriber         = $page->subscribers()->where('email', $subscriber_email)->first();

        if( ! $subscriber)
        {
            return response('Subscriber not found', 509);
        }

        $response = [
            [
                $subscriber->cleanDataForZapier()
            ]
        ];

        // Log the response to the zapier_event record
        $zapier_event->response = json_encode($response);
        $zapier_event->save();

        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function sendMessage(Request $request)
    {
        /*
         *
         *
            Hey @Mike Ferrara, I just have ended up the end point, so here it's how it's working
            you have to make the request to this url `pipeline/zapier` with a post method, the body should have the parameters like this (edited)
            `{'sender': {'id': '2241265582613280'}, 'time': '0-5', 'workflow_uid': '261'}`
            send validation it's not still ready, I'm working on that
            It's ready on staging env to test, please check this out and let me know if something else is needed (edited)

            Mike Ferrara [11:57 PM]
            @Fabian Mora - that `sender` id - is that the page’s `fb_id`?
            and the `time` value - does it matter for `01-01` or `1-1`?

            Fabian Mora [7:09 AM]
            sender id is the subscriber_psid, but if it's harder to you, then send me the subscriber_uid. Just let me know what's your choice (edited)
            the time is in the format 'hh-mm', so the the first part come in hours and the second one in minutes
         *
         *
         */

        $subscriber_psid    = $request->get('subscriber_psid');
        $delay              = (int) $request->get('delay'); // In minutes
        $workflow_uid       = $request->get('workflow_uid');
        $page_fb_id         = $request->get('page_id');

        // We need the page to log the zapier event
        $page = Page::where('fb_id', $page_fb_id)->first();
        if( ! $page)
        {
            return response('Page not found', 500);
        }

        // Log this zapier event
        $zapier_event       = ZapierEventLog::createPageEventRecord($page, $request, 'action', 'send_message');

        // Make sure nobody sets a delay longer than 48 hours (facebook limit)
        if($delay > 2876)
        {
            $delay = 2876; // Give a few min padding in case the send queue is behind, we don't want to queue a message with 1 min left on the 48hr counter only to have it send 3 min later
        }

        // If there is no configured delay, simplify that to our pipeline format
        if($delay === 0)
        {
            $pipeline_delay = '0-0';
        }
        else
        {
            // convert $delay from an integer of minutes to expected format of 'hh-mm'
            $hours              = floor($delay / 60); // Get the whole number of hours this block of time is
            $minutes            = $delay % 60; // Get the remainder, indicating how many minutes would be left if it was divided by whole hours (which we did on the previous line)

            // pad these values to ensure they take up 2 digits each
            // $hours           = str_pad($hours, 2, '0', STR_PAD_LEFT);
            // $minutes         = str_pad($minutes, 2, '0', STR_PAD_LEFT);

            // combine into format expected by the pipeline
            $pipeline_delay     = $hours.'-'.$minutes;
        }

        // Send request to pipeline/internal endpoint to queue message send
        $pipeline_internal_base_url = \Config::get('chatmatic.pipeline_internal_base_url');

        // Setup request to pipeline trigger a message
        $error_message      = null;
        $post_array         = [
            'sender'        => [ 'id' => $subscriber_psid ],
            'time'          => $pipeline_delay,
            'workflow_uid'  => $workflow_uid
        ];

        // Init curl
        $curl               = curl_init($pipeline_internal_base_url . '/zapier');
        if ($curl === false) {
            $error_message          = 'Unable to init curl.';
        }

        // If successful, continue
        if($error_message === null)
        {
            // Set curl options
            $curlSetoptResult = curl_setopt_array($curl, array(
                CURLOPT_POST            => 1,
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_POSTFIELDS      => json_encode($post_array)
            ));
            if ($curlSetoptResult === false) {
                $error_message          = 'Unable to set curl options.';
            }

            // If successful, continue
            if($error_message === null)
            {
                // Exec curl request
                $curlResult = curl_exec($curl);
                if ($curlResult === false) {
                    $error_message          = 'Curl exec failed';
                }
            }
        }

        // Close out curl
        curl_close($curl);

        $return_array = [
            'success'       => false,
            'subscriber'    => null,
        ];

        // If we've got no error message from curl we can assume success and move forward with our response
        if($error_message === null)
        {
            $subscriber = Subscriber::where('user_psid', $subscriber_psid)->first();
            
            $return_array = $subscriber->cleanDataForZapier();
        }
        else
        {
            $return_array['error_message'] = $error_message;
        }

        // Log the response to the zapier_event record
        $zapier_event->response = json_encode($return_array);
        $zapier_event->save();

        return $return_array;
    }
}
