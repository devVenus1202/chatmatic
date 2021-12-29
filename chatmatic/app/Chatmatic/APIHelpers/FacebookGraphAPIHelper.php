<?php


namespace App\Chatmatic\APIHelpers;

use App\Page;
use App\PersistentMenu;
use App\Subscriber;
use App\SubscriberDeliveryHistory;
use App\Subscription;
use App\User;
use App\Workflow;
use Carbon\Carbon;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Kerox\Messenger\Messenger;
use Kerox\Messenger\Model\Common\Button\Postback;
use Kerox\Messenger\Model\Common\Button\WebUrl;
use Kerox\Messenger\Model\Message\Attachment\Template\ButtonTemplate;

class FacebookGraphAPIHelper
{
    protected $client = false;
    protected $facebook_app_id;
    protected $facebook_app_secret;

    /**
     * FacebookGraphAPIHelper constructor.
     * @param $facebook_app_id
     * @param $facebook_app_secret
     */
    public function __construct($facebook_app_id, $facebook_app_secret)
    {
        $this->facebook_app_id      = $facebook_app_id;
        $this->facebook_app_secret  = $facebook_app_secret;
    }

    /**
     * Initiate Facebook Graph API SDK
     */
    public function initClient()
    {
        try{
            $this->client = new Facebook([
                'app_id'        => $this->facebook_app_id,
                'app_secret'    => $this->facebook_app_secret,
                'default_graph_version' => 'v9.0'
            ]);
        } catch (FacebookSDKException $e) {
            // When validation fails or other local issues
            $error_message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->reportError($error_message);
            exit;
        }
    }

    /**
     * Get a user's details with a given page scoped id and page access token
     *
     * @param $user_psid
     * @param $page_access_token
     * @return array
     */
    public function getGraphUser($user_psid, $page_access_token)
    {
        if( ! $this->client)
            $this->initClient();

        $response = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
            'user'      => []
        ];

        try{
            $fb_response        = $this->client->get('/'.$user_psid, $page_access_token);
            $subscriber_data    = $fb_response->getGraphUser();
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
        }

        if($response['error'] === 0)
        {
            $response['success']    = 1;
            $response['user']       = $subscriber_data;
        }

        return $response;
    }

    /**
     * Send a message given a message parameters array and page access token
     *
     * @param $message_parameters
     * @param $page_access_token
     * @return mixed
     */
    public function sendMessage($message_parameters, $page_access_token)
    {
        if( ! $this->client)
            $this->initClient();

        try{
            $facebook_response  = $this->client->post('/me/messages', $message_parameters, $page_access_token);
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            $error_message = 'Graph returned an error: ' . $e->getMessage();
            $this->reportError($error_message);
            exit;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            $error_message = 'Facebook SDK returned an error: ' . $e->getMessage();
            $this->reportError($error_message);
            exit;
        }

        return $facebook_response;
    }

    /**
     * @param array $messages
     * @param Page $page
     * @param Subscriber $subscriber
     * @param Subscription $subscription
     * @param Workflow $workflow
     * @return int
     * @throws \Exception
     */
    public function sendMessagesFromArray($messages = [], Page $page, Subscriber $subscriber, Subscription $subscription, Workflow $workflow)
    {
        if( ! $this->client)
            $this->initClient();

        $messages_sent = 0;
        foreach($messages as $message_parameters)
        {
            // If we have a message_type that means we've constructed a message and we're ready to send it
            if(isset($message_parameters['message']))
            {
                // Check for buttons
                if(isset($message_parameters['message']['buttons']))
                {
                    $fb = new Messenger(config('chatmatic.app_secret'), config('chatmatic.verify_token'), $page->facebook_connected_access_token);

                    $buttons = [];
                    foreach($message_parameters['message']['buttons'] as $button_array)
                    {
                        switch($button_array['type'])
                        {
                            case 'postback':
                                $button_text = str_limit($button_array['button_text'], 20, '');
                                $buttons[] = Postback::create($button_text, $button_array['button_action']);
                                break;
                            case 'web_url':
                                $button_text = str_limit($button_array['button_text'], 20, '');
                                $button_action = $button_array['button_action'];

                                // If there's an http:// but it's not secure (https) change it
                                if(mb_stristr($button_action, 'http://'))
                                    $button_action = str_replace('http://', 'https://', $button_action);

                                // If there's no schema provided, add one
                                if( ! mb_stristr($button_action, 'http'))
                                    $button_action = 'https://'.$button_action;

                                $buttons[] = WebUrl::create($button_text, $button_action);
                                break;
                        }
                    }
                    $message = ButtonTemplate::create($message_parameters['message']['text'], $buttons);
                    $facebook_response = $fb->send()->message($message_parameters['recipient']['id'], $message);
                    if( ! is_null($facebook_response->getErrorCode()))
                    {
                        // Update subscribers table / messages_attempted_from_bot = +1
                        ++$subscriber->messages_attempted_from_bot;
                        $subscriber->save();

                        // Update subscriptions table / messages_attempted = +1
                        ++$subscription->messages_attempted;
                        $subscription->save();

                        throw new \Exception('Error during send Facebook Messenger message send POST request attempting
                            to send a welcome message with  PageUID: '.$page->uid.' WorkflowUID:'. $workflow->uid.' Error: '.$facebook_response->getErrorMessage());
                    }
                    $message_id = $facebook_response->getMessageId();
                }
                else // No buttons, it's a simple text message
                {
                    // Send the message
                    $fb = new FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
                    $facebook_response = $fb->sendMessage($message_parameters, $page->facebook_connected_access_token);
                    if($facebook_response->isError())
                    {
                        // Update subscribers table / messages_attempted_from_bot = +1
                        ++$subscriber->messages_attempted_from_bot;
                        $subscriber->save();

                        // Update subscriptions table / messages_attempted = +1
                        ++$subscription->messages_attempted;
                        $subscription->save();

                        throw new \Exception('Error during send Facebook Messenger message send POST request attempting
                            to send a welcome message with  PageUID: '.$page->uid.' WorkflowUID:'. $workflow->uid);
                    }
                    // Get the just-sent message_id from the facebook response body
                    $facebook_response_body = $facebook_response->getBody();
                    $facebook_response_body = json_decode($facebook_response_body);
                    $message_id = $facebook_response_body->message_id;
                }
                // If we have a message id the message was sent, we'll log it to the subscriber_delivery_history table
                if(mb_strlen($message_id) > 0)
                {
                    // Create new record in subscriber_deliver_history table for this message send
                    $subscriber_delivery_history_data = [
                        'subscriber_uid'    => $subscriber->uid,
                        'source_type'       => 'workflow',
                        'type_uid'          => $workflow->uid,
                        'fb_message_id'     => $message_id,
                        'marked_as_read'    => 0,
                        'page_uid'          => $page->uid,
                        //'workflow_uid'      => $workflow->uid // This field is null on all records currently in the database, not sure what it's intended use is/was
                    ];

                    $subscriber_delivery_history = SubscriberDeliveryHistory::create($subscriber_delivery_history_data);
                    $messages_sent++;
                }
                else{
                    throw new \Exception('No message_id returned from Facebook Graph request attempting to
                            send a welcome message with  PageUID: '.$page->uid.' WorkflowUID:'. $workflow->uid);
                }

                // Update subscriber/subscription counters
                // Update subscribers table / messages_attempted_from_bot = +1 / messages_accepted_from_bot = +1 / last_engagement_utc = Carbon::now('UTC')
                ++$subscriber->messages_attempted_from_bot;
                ++$subscriber->messages_accepted_from_bot;
                // Grab a date instance here so that the updated_at_utc and last_engagement_utc are equal
                $last_engagement_utc                = Carbon::now('UTC');
                $subscriber->last_engagement_utc    = $last_engagement_utc;
                $subscriber->updated_at_utc         = $last_engagement_utc;
                $subscriber->save();

                // Update subscriptions table / messages_attempted = +1 / messages_accepted = +1
                ++$subscription->messages_attempted;
                ++$subscription->messages_accepted;
                $subscription->save();

                // Update workflow messages_delivered counter
                // TODO: This should probably be moved to an observer (SubscriberDeliveryHistoryObserver::created())
                $workflow->increment('messages_delivered');
            }
            else
            {
                // Check to see if this is a message delay...
                if(isset($message_parameters['delay']))
                {
                    // It's a delay, we'll want to send the typing indicator
                    $delay_seconds = $message_parameters['delay'];
                    unset($message_parameters['delay']);

                    // Are we using the typing indicator?
                    $typing_indicator = false;
                    if($message_parameters['typing_indicator'])
                    {
                        $typing_indicator = true;
                    }
                    unset($message_parameters['typing_indicator']);

                    // If the typing indicator is supposed to be used, we'll send that now
                    if($typing_indicator)
                    {
                        $message_parameters['sender_action'] = 'typing_on';
                        $fb = new FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
                        $facebook_response = $fb->sendMessage($message_parameters, $page->facebook_connected_access_token);
                    }

                    // Initiate the delay
                    sleep($delay_seconds);

                    if($typing_indicator)
                    {
                        // Turn off the typing indicator
                        $message_parameters['sender_action'] = 'typing_off';
                        $fb = new FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
                        $facebook_response = $fb->sendMessage($message_parameters, $page->facebook_connected_access_token);
                    }
                }
            }
        }

        return $messages_sent;
    }

    /**
     * @param $facebook_long_token
     * @return mixed
     */
    public function userPagesList($facebook_long_token)
    {
        $response['sucess']     = 0;
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['pages']      = [];

        if( ! $this->client)
            $this->initClient();

        // Get the pages photos
        try {
            $facebook_response = $this->client->get('/me/accounts?fields=id,access_token,category,name,link,page_token&limit=500', $facebook_long_token);
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
        }

        if($response['error'])
        {
            return $response;
        }

        $pages_response = json_decode($facebook_response->getBody())->data;

        foreach($pages_response as $page_object)
        {
            // If no access token is provided we're not interested in it
            if(isset($page_object->access_token))
            {
                $response['pages'][] = [
                    'id'            => $page_object->id,
                    'access_token'  => $page_object->access_token,
                    'category'      => $page_object->category,
                    'name'          => $page_object->name,
                    'link'          => $page_object->link,
                    'page_token'    => $page_object->page_token,
                ];
            }
        }

        $response['success'] = 1;

        return $response;
    }

    /**
     * @param User $user
     * @return array
     * @throws FacebookSDKException
     */
    public function userPermissionsList(User $user)
    {
        if( ! $this->client)
            $this->initClient();

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $this->client->get(
                '/'.$user->facebook_user_id.'/permissions',
                $user->facebook_long_token
            );
        } catch(FacebookResponseException $e) {
            // If there's an error we'll return that
            return [
                'error'     => 'no-permissions',
                'message'   => $e->getMessage()
            ];
        } catch(FacebookSDKException $e) {
            throw $e;
        }
        $graphEdge = $response->getGraphEdge();

        $permissions_array = [];
        foreach($graphEdge as $graphNode)
        {
            $graphNode = $graphNode->asArray();
            $permissions_array[] = [
                'permission' => $graphNode['permission'],
                'status'     => $graphNode['status']
            ];
        }

        return $permissions_array;
    }

    /**
     * Get the cover photo URL for a page
     *
     * @param Page $page
     * @param $page_access_token
     * @return string
     */
    public function getPageCoverPhotoUrl(Page $page, $page_access_token)
    {
        if( ! $this->client)
            $this->initClient();

        // TODO: Placeholder URL
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['url']        = '';

        // Get the pages photos
        try {
            $facebook_response = $this->client->get('me?fields=cover', $page_access_token);
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            $response['url']        = 'invalid-session';
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            $response['url']        = 'failed-to-collect';
        }

        if(isset($facebook_response) && $response['error'] === 0)
        {
            // Convert json response
            $cover_photo_response     = json_decode($facebook_response->getBody());

            if($cover_photo_response)
            {
                if(isset($cover_photo_response->cover) && isset($cover_photo_response->cover->source))
                {
                    $response['url'] = $cover_photo_response->cover->source;
                }
                else
                {
                    // No photos at all, return nothing for now. (We should use some sort of placeholder/chatmatic logo)
                    return $response;
                }
            }
        }

        return $response;
    }

    /**
     * @param $facebook_user_id
     * @param $user_access_token
     * @return mixed
     */
    public function getUserProfilePhotoURL($facebook_user_id, $user_access_token, $size = "small")
    {
        if( ! $this->client)
            $this->initClient();

        // TODO: Placeholder URL
        $response['success']    = 0;
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['url']        = '';

        // Get the pages photos
        try {
            $facebook_response = $this->client->get('/'.$facebook_user_id.'/picture?redirect=false&type='.$size, $user_access_token);
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            $response['url']        = 'invalid-session';

            return $response;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            $response['url']        = 'failed-to-collect';

            return $response;
        }

        $data = json_decode($facebook_response->getBody());

        $photo_url = '';
        if(isset($data->data))
            $photo_url = $data->data->url;

        $response['url'] = $photo_url;
        if(mb_strlen($photo_url) > 0)
            $response['success'] = 1;

        return $response;
    }

    /**
     * Get the page access token from a given page and user access token combo
     *
     * @param Page $page
     * @param $user_access_token
     * @return mixed
     */
    public function getPageAccessToken(Page $page, $user_access_token)
    {
        if( ! $this->client)
            $this->initClient();

        $response['token']      = '';
        $response['error']      = 0;
        $response['error_msg']  = '';

        // Make request to facebook for the access token
        try {
            $fb_response = $this->client->get('/'.$page->fb_id.'?fields=access_token', $user_access_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        // Get token from facebook response
        $facebook_access_token = $fb_response->getGraphNode()->getField('access_token');

        if(mb_strlen($facebook_access_token) !== '' && $facebook_access_token !== null)
        {
            // Set response token
            $response['token'] = $facebook_access_token;

            // Update the token in the database
            $page->facebook_connected_access_token = $facebook_access_token;
            $page->save();
        }

        return $response;
    }

    /**
     * Install 'get-started' payload/button on the given facebook page
     *
     * @param Page $page
     * @return mixed
     */
    public function installPageGetStartedButton(Page $page)
    {
        if( ! $this->client)
            $this->initClient();

        $response['success']    = 0;
        $response['error']      = 0;
        $response['error_msg']  = '';

        try {
            $fb_response = $this->client->post(
                '/me/messenger_profile',
                [
                    'get_started' => [
                        'payload' => 'get-started::'
                    ]
                ],
                $page->facebook_connected_access_token
            );
            $response['success'] = ('success' == $fb_response->getGraphNode()->getField('result', null));
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
        }

        return $response;
    }

    /**
     * Connect a page to the facebook app
     *
     * @param Page $page
     * @return mixed
     */
    public function connectPage(Page $page)
    {
        if( ! $this->client)
            $this->initClient();

        $response['success']    = 0;
        $response['error']      = 0;
        $response['error_msg']  = '';

        // Make request to connect the page to our application
	$post_array = [
		'subscribed_fields' => [
                'feed','mention','name','picture','category','description','conversations',
                'branded_camera','feature_access_list','standby',
                'messages','messaging_account_linking','messaging_checkout_updates',
                'message_echoes','message_deliveries','messaging_game_plays','messaging_optins',
                'messaging_optouts','messaging_payments','messaging_postbacks',
                'messaging_pre_checkouts','message_reads','messaging_referrals',
                'messaging_handovers','messaging_policy_enforcement',
                'messaging_page_feedback','messaging_appointments','founded',
                'company_overview','mission','products','general_info',
                'leadgen_fat','location','hours','parking','public_transit',
                'page_about_story','phone','email','website','ratings','attire',
                'payment_options','culinary_team','general_manager','price_range',
                'awards','hometown','current_location','bio','affiliation','birthday',
                'personal_info','personal_interests','publisher_subscriptions','members',
                'checkins','page_upcoming_change','page_change_proposal',
                'merchant_review','product_review','videos','live_videos','registration'
                ]
	];
        try {
            $fb_response = $this->client->post('/' . $page->fb_id . '/subscribed_apps', $post_array, $page->facebook_connected_access_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        // Check that we got a successful response
        if ($fb_response->getGraphNode()->getField('success'))
        {
            $response['success'] = 1;
        }
        else
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Unable to connect to the page.';
        }

        return $response;
    }

    /**
     * Disconnect a given page from the facebook app
     *
     * @param Page $page
     * @return mixed
     */
    public function disconnectPage(Page $page)
    {
        if( ! $this->client)
            $this->initClient();

        $response['success']    = 0;
        $response['error']      = 0;
        $response['error_msg']  = '';

        // Make request to disconnect our app from the page
        try {
            $fb_response = $this->client->delete('/' . $page->fb_id . '/subscribed_apps', [], $page->facebook_connected_access_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        // Check for a successful response
        if ($fb_response->getGraphNode()->getField('success')) {
            $response['success']    = 1;
        } else {
            $response['error']      = 1;
            $response['error_msg']  = 'Unable to disconnect from the page.';
        }

        return $response;
    }

    /**
     * Generate a scan code
     *
     * @param $campaign_public_id
     * @param $page_access_token
     * @return mixed
     */
    public function generateScanCode($campaign_public_id, $page_access_token)
    {
        if( ! $this->client)
            $this->initClient();

        $response['success']                    = 0;
        $response['error']                      = 0;
        $response['error_msg']                  = '';
        $response['b64_message_code']           = null;
        $response['facebook_messenger_code']    = null;

        // Setup POST data
        $post_array = [
            'type' => 'standard',
            'image_size' => '2000',
            'data' => [
                //Max 250 characters. Valid characters: a-z A-Z 0-9 +/=-.:_
                'ref' => 'campaign::' . $campaign_public_id
            ]
        ];

        try {
            $fb_response = $this->client->post('/me/messenger_codes', $post_array, $page_access_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $node                       = $fb_response->getGraphNode();
        $facebook_message_code      = $node->getField('uri');
        $pipeline_internal_base_url = \Config::get('chatmatic.pipeline_internal_base_url');

        // Setup request to pipeline to get the scan code
        $post_array                 = [
            'campaign_public_id'    => $campaign_public_id,
            'url'                   => $facebook_message_code,
        ];
        $curl = curl_init($pipeline_internal_base_url . '/messenger-code');
        if ($curl === false) {
            $error_message          = 'Unable to save scan code images because of internal error. #001';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        $curlSetoptResult = curl_setopt_array($curl, array(
            CURLOPT_POST            => 1,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_POSTFIELDS      => json_encode($post_array)
        ));
        if ($curlSetoptResult === false) {
            $error_message          = 'Unable to save scan code images because of internal error. #002';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        $curlResult = curl_exec($curl);
        if ($curlResult === false) {
            $error_message          = 'Unable to save scan code images because of internal error. #003';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        curl_close($curl);

        // Convert to image
        $img = \imagecreatefrompng($facebook_message_code);
        if (!$img) {
            $error_message          = 'Download of Facebook message code failed.';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        // Resize the image
        $image_resized = \imagescale($img, 500);
        ob_start();
        \imagepng($image_resized);
        $image_data = ob_get_contents();
        ob_end_clean();

        // Convert to b64
        if(!empty($image_data))
        {
            $image_data                             = base64_encode($image_data);
            $response['b64_message_code']           = $image_data;
            $response['facebook_messenger_code']    = $facebook_message_code;
            $response['success']                    = 1;
        }

        return $response;
    }

    public function getPageLikes($facebook_page_id, $access_token)
    {
        if( ! $this->client)
            $this->initClient();

        $response['success']    = 0;
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['likes']      = null;

        try {
            $fb_response = $this->client->post('/'.$facebook_page_id.'/likes', [], $access_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        dd($fb_response);
    }

    /**
     * Get posts from a given page/access token
     *
     * @param $facebook_page_id
     * @param $page_access_token
     * @param $last_facebook_posts_pull_utc
     * @param int $result_count
     * @return mixed
     */
    public function getPosts($facebook_page_id, $page_access_token, $last_facebook_posts_pull_utc, $result_count = 20)
    {
        if( ! $this->client)
            $this->initClient();

        $response['success']    = 0;
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['fb_response']= null;

        // Let's add a 2 day cushion here because we've got some issues where some posts are coming up missing.
        //$last_facebook_posts_pull_utc = Carbon::createFromTimestamp(strtotime($last_facebook_posts_pull_utc));
        //$last_facebook_posts_pull_utc = $last_facebook_posts_pull_utc->subDays(2)->format('m/d/Y 00:00:00');

        try {
            //$until_modifier = ($last_facebook_posts_pull_utc ? '&since='.strtotime($last_facebook_posts_pull_utc . ' UTC') : '');
            $fb_response    = $this->client->get('/'.$facebook_page_id.'/posts?fields=id,permalink_url,message,picture,created_time,comments.limit(0).summary(true)&limit='.$result_count, $page_access_token);
            //$fb_response    = $this->client->get('/'.$facebook_page_id.'/posts?fields=id,object_id,type,permalink_url,message,picture,created_time,comments.limit(0).summary(true)&limit='.$result_count.$until_modifier, $page_access_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $response['fb_response'] = $fb_response;

        return $response;
    }

    /**
     * Generate a login with facebook URL
     *
     * @param $base_login_domain
     * @param bool $re_request_permissions
     * @param $required_scopes
     * @return mixed
     */
    public function getLoginUrl($base_login_domain, $re_request_permissions = false, $required_scopes)
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']    = 0;
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['login_url']  = '';

        // Get the URL generator/helper from facebook SDK
        $helper = $this->client->getRedirectLoginHelper();

        // Build our redirect URL
        $login_redirect_url = $base_login_domain.'/login.php?fb-callback=1';

        // Are we re-requesting permissions?
        if($re_request_permissions)
            $login_url = $helper->getReRequestUrl($login_redirect_url, $required_scopes);
        else
            $login_url = $helper->getLoginUrl($login_redirect_url, $required_scopes);

        // Looks like that all worked, we'll setup our response with the url
        $response['success']    = 1;
        $response['login_url']  = $login_url;

        return $response;
    }

    /**
     * Get a user access token from login process
     *
     * @param $base_login_domain
     * @return mixed
     */
    public function getAccessToken($base_login_domain)
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['access_token']   = '';

        // Get the URL generator/helper from facebook SDK
        $helper = $this->client->getRedirectLoginHelper();

        try {
            $access_token = $helper->getAccessToken($base_login_domain.'/login.php?fb-callback=1');
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $response['success']        = 1;
        $response['access_token']   = $access_token;

        return $response;
    }

    /**
     * Get an OAuth2Client
     *
     * @return mixed
     */
    public function getOAuth2Client()
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['oauth_client']   = '';

        // Get the OAuth2Client
        try {
            $client = $this->client->getOauth2Client();
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $response['success']        = 1;
        $response['oauth_client']   = $client;

        return $response;
    }

    /**
     * Get the facebook id, name and email for a user with a given token
     *
     * @param $facebook_long_token
     * @return mixed
     */
    public function getUserDetails($facebook_long_token)
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['fb_response']    = '';

        // Get the OAuth2Client
        try {
            $fb_response = $this->client->get('/me?fields=id,name,email', $facebook_long_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $response['success']        = 1;
        $response['fb_response']    = $fb_response;

        return $response;
    }

    /**
     * @param $subscriber_fb_id
     * @param $page_access_token
     * @return array
     */
    public function getSubscriberChatHistory($subscriber_fb_id, $page_access_token)
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['chat_history']   = [];

        // Get the inbox to find the conversation id
        try {
            $fb_response = $this->client->get('/me/conversations?user_id='.$subscriber_fb_id, $page_access_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        // Check to make sure we get a conversation/thread id
        $json_response = $fb_response->getBody();
        $json_response = json_decode($json_response);

        // Confirm we were able to parse the json response
        if( ! is_object($json_response))
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Error parsing response from facebook while attempting to locate conversation thread.';
            return $response;
        }

        // Confirm there's a thread returned
        if( ! isset($json_response->data) || ! isset($json_response->data[0]))
        {
            $response['error']      = 1;
            $response['error_msg']  = 'No conversation thread found with this subscriber.';
            return $response;
        }

        $thread_id = $json_response->data[0]->id;

        // Now that we have a thread id we'll want to get the last $message_count messages from it
        try {
            $fb_response = $this->client->get('/'.$thread_id.'/messages', $page_access_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        // Check to make sure we get a conversation/thread id
        $json_response = $fb_response->getBody();
        $json_response = json_decode($json_response);

        // Confirm we were able to parse the json response
        if( ! is_object($json_response))
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Error parsing response from facebook while attempting to retrieve messages from conversation thread.';
            return $response;
        }

        // Confirm there are messages returned
        if( ! isset($json_response->data) || ! isset($json_response->data[0]))
        {
            $response['error']      = 1;
            $response['error_msg']  = 'No messages returned from conversation.';
            return $response;
        }

        $messages       = $json_response->data;
        $message_paging = $json_response->paging;

        $message_bucket = [];
        // Loop through the messages returned to populate their text/to/from
        foreach($messages as $key => $message_object)
        {
            $message_id         = $message_object->id;
            $message_timestamp  = $message_object->created_time;

            // Get the message data/content
            try {
                $fb_response = $this->client->get('/'.$message_id.'?fields=message,to,from,attachments,shares{link}', $page_access_token);
            } catch(FacebookResponseException $e) {
                $error_message          = 'Graph returned an error: ' . $e->getMessage();
                $response['error']      = 1;
                $response['error_msg']  = $error_message;
                return $response;
            } catch(FacebookSDKException $e) {
                $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
                $response['error']      = 1;
                $response['error_msg']  = $error_message;
                return $response;
            } catch (\Exception $e) {
                $error_message          = 'General error: ' . $e->getMessage();
                $response['error']      = 1;
                $response['error_msg']  = $error_message;
                return $response;
            }

            $json_response = json_decode($fb_response->getBody());

            // Confirm we were able to parse the json response
            if( ! is_object($json_response))
            {
                $response['error']      = 1;
                $response['error_msg']  = 'Error parsing response from facebook while attempting to retrieve message details.';
                return $response;
            }

            // Confirm the data we're looking for is here
            if( ! isset($json_response->message))
            {
                $response['error']      = 1;
                $response['error_msg']  = 'No message data returned from message id.';
                return $response;
            }

            $message_text   = $json_response->message;
            $message_to     = $json_response->to->data[0]->id;
            $message_from   = $json_response->from->id;

            // Check for a share
            $shares = [];
            if(isset($json_response->shares) && isset($json_response->shares->data[0]))
            {
                foreach($json_response->shares->data as $share_data)
                {
                    $shares[] = $share_data;
                }
            }

            // Check for an attachment
            $attachments = [];
            if(isset($json_response->attachments) && isset($json_response->attachments->data[0]))
            {
                foreach($json_response->attachments->data as $attachment_data)
                {
                    $attachments[] = $attachment_data;
                }
            }

            // Throw all this stuff into an array
            $message_bucket[$key] = [
                'message_id'    => $message_id,
                'created_at'    => $message_timestamp,
                'message'       => $message_text,
                'to'            => $message_to,
                'from'          => $message_from,
                'shares'        => $shares,
                'attachments'   => $attachments,
            ];
        }

        return $message_bucket;
    }

    /**
     * @param $facebook_long_token
     * @param $file_url
     * @param string $file_type
     * @return mixed
     */
    public function getMediaObjectAttachmentId($facebook_long_token, $file_url, $file_type = 'image')
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['fb_response']    = '';
        $response['attachment_id']  = null;

        $body = [
            'message' => [
                'attachment' => [
                    'type' => $file_type,
                    'payload' => [
                        'is_reusable'   => true,
                        'url'           => $file_url
                    ]
                ]
            ]
        ];

        // Get the OAuth2Client
        try {
            $fb_response = $this->client->post('/me/message_attachments', $body, $facebook_long_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $attachment_id              = json_decode($fb_response->getBody())->attachment_id;

        $response['success']        = 1;
        $response['fb_response']    = $fb_response;
        $response['attachment_id']  = $attachment_id;

        return $response;
    }

    /**
     * Verify a users access token
     *
     * @param $facebook_long_token
     * @return mixed
     */
    public function verifyUserAccessToken($facebook_long_token)
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']            = 0;
        $response['error']              = 0;
        $response['error_msg']          = '';
        $response['facebook_user_id']   = null;
        $response['scopes']             = null;
        $response['access_expires_at']  = null;

        try {
            $fb_response = $this->client->get('/debug_token?input_token='.$facebook_long_token.'&access_token='.$this->facebook_app_id.'|'.$this->facebook_app_secret);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $fb_response = json_decode($fb_response->getBody());

        $response['success']            = 1;
        $response['facebook_user_id']   = $fb_response->data->user_id;
        $response['scopes']             = $fb_response->data->scopes;
        $response['access_expires_at']  = $fb_response->data->data_access_expires_at;

        return $response;
    }

    /**
     * @param $persistent_menu
     * @param $menu_items
     * @param $page_access_token
     * @return mixed
     */
    public function createPersistentMenu($persistent_menu, $menu_items, $page_access_token)
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['fb_response']    = '';

        $persistent_menu_items_array = [];

        foreach($menu_items as $key => $menu_item)
        {
            $title      = $menu_item['title'];
            $type       = $menu_item['type'];
            $payload    = $menu_item['payload'];

            if($type === 'submenu')
            {
                $type = 'nested';
            }

            switch($type)
            {
                case 'link':
                    $type = 'web_url';
                    break;
                case 'message':
                    $type = 'postback';
                    break;
            }

            $persistent_menu_items_array[$key] = [
                'title' => $title,
                'type'  => $type
            ];

            switch($type)
            {
                // If it's a web url set the payload (url) and the height ratio
                case 'web_url':
                    $persistent_menu_items_array[$key]['url'] = $payload;
                    //$persistent_menu_items_array[$key]['webview_height_ratio'] = 'full';
                    break;

                // If it's a postback set the payload
                case 'postback':
                    // Current $payload is a workflow_uid - we need to get the first step of the workflow to pass through
                    $workflow = Workflow::find($payload);
                    if( ! $workflow)
                    {
                        $response['error'] = 1;
                        $response['error_msg'] = 'Workflow not found';

                        return $response;
                    }

                    $payload = $workflow->root_workflow_step_uid;

                    $persistent_menu_items_array[$key]['payload'] = 'next-step::'.$payload;
                    break;

                // If it's a nested/submenu, we'll build that
                case 'nested':
                    foreach($payload as $sub_menu_item)
                    {
                        $sub_menu_item_array = [
                            'type'      => '',
                            'title'     => '',
                        ];
                        $sub_title      = $sub_menu_item['title'];
                        $sub_type       = $sub_menu_item['type'];
                        $sub_payload    = $sub_menu_item['payload'];

                        if($sub_type === 'link')
                        {
                            $sub_type                       = 'web_url';
                            $sub_menu_item_array['url']     = $sub_payload;
                        }
                        elseif($sub_type === 'message')
                        {
                            $workflow       = Workflow::find($sub_payload);
                            $sub_payload    = $workflow->root_workflow_step_uid;
                            $sub_type                       = 'postback';
                            $sub_menu_item_array['payload'] = 'next-step::'.$sub_payload;
                        }

                        $sub_menu_item_array['title']   = $sub_title;
                        $sub_menu_item_array['type']    = $sub_type;

                        $persistent_menu_items_array[$key]['call_to_actions'][] = $sub_menu_item_array;
                    }
                    break;
            }
        }

        // Attach the menu items to the 'call_to_actions' parameter
        $persistent_menu_array = [
            'locale'                    => $persistent_menu['locale'],
            'composer_input_disabled'   => false,
            'call_to_actions'           => $persistent_menu_items_array
        ];

        // Send the request to create the menu
        $request = [
            'persistent_menu' => [
                $persistent_menu_array
            ],
        ];

        try {
            $fb_response = $this->client->post('/me/messenger_profile?access_token='.$page_access_token, $request);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $fb_response = json_decode($fb_response->getBody());

        $response['success']        = 1;
        $response['fb_response']    = $fb_response;

        return $response;
    }

    /**
     * @param $page_access_token
     * @return mixed
     */
    public function disablePersistentMenu($page_access_token)
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['fb_response']    = '';

        // Send the request to disable the menu
        $request = [
            'fields' => [
                'persistent_menu'
            ],
        ];

        try {
            $fb_response = $this->client->delete('/me/messenger_profile?access_token='.$page_access_token, $request);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $fb_response = json_decode($fb_response->getBody());

        $response['success']        = 1;
        $response['fb_response']    = $fb_response;

        return $response;
    }

    /**
     * @param $facebook_user_token
     * @return mixed
     */
    public function getLongLivedUserAccessToken($facebook_user_token)
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['fb_response']    = '';

        $request_string = '/oauth/access_token?grant_type=fb_exchange_token&client_id='.$this->facebook_app_id.'&client_secret='.$this->facebook_app_secret.'&fb_exchange_token='.$facebook_user_token;

        try {
            $fb_response = $this->client->get($request_string, $facebook_user_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $fb_response = json_decode($fb_response->getBody());

        $response['success']        = 1;
        $response['fb_response']    = $fb_response;

        return $response;
    }

    /**
     * Basic error helper/logging
     *
     * @param $error_message
     */
    public function reportError($error_message)
    {
        \Log::error($error_message);
    }


    /**
     * @param $page_access_token
     * @return mixed
     */
    public function listWithedList($page_access_token)
    {
        if (! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['fb_response']    = '';

        $withed_list_items          = [];


        try {
            $fb_response = $this->client->get('/me/messenger_profile?fields=whitelisted_domains&access_token='.$page_access_token);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $fb_response = json_decode($fb_response->getBody());

        if (empty($fb_response->data))   
        {
            $fb_response = [];
        }
        else
        {
            $fb_response = $fb_response->data[0]->whitelisted_domains;
        }

        $response['success']        = 1;
        $response['fb_response']    = $fb_response;

        return $response;
    }


    /**
     * @param $domain_items
     * @param $page_access_token
     * @return mixed
     */
    public function createUdateWithedList($domain_items, $page_access_token)
    {
        if( ! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['fb_response']    = '';


        // First let's check if the arrays is empty
        if ( $domain_items === '[]')
        {
            // Here we have to make a DELETE request
            $type = 'delete';
            $method = 'delete';
        }
        else
        {
            // Here we havo to make a POST request
            $type ='update';
            $method = 'post';
        }

        switch ($type) {
            case 'update':
                $request = [
                    'whitelisted_domains' => $domain_items
                ];
                
                break;
            
            case 'delete':
                $request = [
                    'fields' => [
                        "whitelisted_domains"
                    ]
                ];
                break;

        }

        try {
            if ($method == 'post'){
                $fb_response = $this->client->post('/me/messenger_profile?access_token='.$page_access_token, $request);
            } elseif ($method == 'delete'){
                $fb_response = $this->client->delete('/me/messenger_profile?access_token='.$page_access_token, $request);
            }

        } catch(FacebookResponseException $e) {
            $error_message              = 'Graph returned and error:' . $e->getMessage();
            $response['error']          = 1;
            $response['error_msg']      = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message              = 'Facebook SDK returned an error: ' .$e->getMessage();
            $response['error']          = 1;
            $response['error_msg']      = $error_message;
            return $response;
        } catch(\Exception $e) {
            $error_message              = 'General error: ' . $e->getMessage();
            $response['error']          = 1;
            $response['error_msg']      = $error_message;
            return $response;
        }

        $fb_response = json_decode($fb_response->getBody());

        $response['success']        = 1;
        $response['fb_response']    = $fb_response;

        return $response;
    }

    /**
     * @param $page_access_token
     * @return mixed
     */
    public function updateGreeting($message, $page_access_token)
    {
        if (! $this->client)
            $this->initClient();

        // Setup response bucket
        $response['success']        = 0;
        $response['error']          = 0;
        $response['error_msg']      = '';
        $response['fb_response']    = '';

        $request = [
                    'greeting' => [[
                        "locale" => "default",
                        "text" => $message
                    ]]
                ];

        try {
            $fb_response = $this->client->post('/me/messenger_profile?access_token='.$page_access_token, $request);
        } catch(FacebookResponseException $e) {
            $error_message          = 'Graph returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch(FacebookSDKException $e) {
            $error_message          = 'Facebook SDK returned an error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        } catch (\Exception $e) {
            $error_message          = 'General error: ' . $e->getMessage();
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }

        $fb_response = json_decode($fb_response->getBody());

        if (empty($fb_response->data))   
        {
            $fb_response = [];
        }
        else
        {
            $fb_response = $fb_response->data[0]->whitelisted_domains;
        }

        $response['success']        = 1;
        $response['fb_response']    = $fb_response;

        return $response;
    }

}
