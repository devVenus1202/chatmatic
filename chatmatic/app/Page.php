<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Scout\Searchable;

/**
 * App\Page
 *
 * @property int $uid
 * @property int|null $chatmatic_user_uid
 * @property string $created_by
 * @property \Carbon\Carbon $created_at_utc
 * @property string $updated_by
 * @property \Carbon\Carbon $updated_at_utc
 * @property int $is_connected
 * @property string $facebook_connected_access_token
 * @property string $fb_id
 * @property string $fb_name
 * @property string $fb_category
 * @property string $fb_access_token
 * @property \Illuminate\Database\Eloquent\Collection|\App\Comment[] $comments
 * @property \Illuminate\Database\Eloquent\Collection|\App\Subscriber[] $subscribers
 * @property int $page_likes
 * @property int $active_triggers
 * @property int $optin_requests
 * @property int $optin_checkbox_requests
 * @property string|null $last_facebook_posts_pull_utc
 * @property string $fb_link
 * @property string $fb_page_token
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Broadcast[] $broadcasts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Campaign[] $campaigns
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Post[] $posts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Subscription[] $subscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Trigger[] $triggers
 * @property-read \App\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Workflow[] $workflows
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page orderByRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereActiveTriggers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereChatmaticUserUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereFacebookConnectedAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereFbAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereFbCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereFbId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereFbLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereFbName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereFbPageToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereIsConnected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereLastFacebookPostsPullUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereOptinCheckboxRequests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereOptinRequests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page wherePageLikes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereSubscribers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page whereUpdatedBy($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PageLicense[] $licenses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SubscriberCountHistory[] $subscriberCountHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PersistentMenu[] $persistentMenus
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tag[] $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PageAdmin[] $pageAdmins
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowStep[] $workflowSteps
 * @property bool $persistent_menus_active
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Page wherePersistentMenusActive($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Automation[] $automations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Integration[] $integrationRecords
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Integration[] $integrations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CustomField[] $customFields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ApiErrorLog[] $apiErrors
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ZapierWebhookSubscription[] $zapierWebhookSubscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ZapierEventLog[] $zapierEventLogs
 * @property-read int|null $api_errors_count
 * @property-read int|null $automations_count
 * @property-read int|null $campaigns_count
 * @property-read int|null $comments_count
 * @property-read int|null $custom_fields_count
 * @property-read int|null $integration_records_count
 * @property-read int|null $integrations_count
 * @property-read int|null $licenses_count
 * @property-read int|null $page_admins_count
 * @property-read int|null $persistent_menus_count
 * @property-read int|null $posts_count
 * @property-read \App\SmsBalance|null $sms_balance
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SmsHistory[] $sms_history
 * @property-read int|null $sms_history_count
 * @property-read \App\StripePurchase|null $stripe_purchases
 * @property-read int|null $subscriber_count_history_count
 * @property-read int|null $subscribers_count
 * @property-read int|null $subscriptions_count
 * @property-read int|null $tags_count
 * @property-read int|null $triggers_count
 * @property-read int|null $workflow_steps_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTrigger[] $workflowTriggers
 * @property-read int|null $workflow_triggers_count
 * @property-read int|null $workflows_count
 * @property-read int|null $zapier_event_logs_count
 * @property-read int|null $zapier_webhook_subscriptions_count
 */
class Page extends Model
{
    use Searchable;

    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'pages';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    protected $dates = [
        'created_at_utc',
        'updated_at_utc',
        'last_facebook_posts_pull_utc'
    ];

    /*
     * This is here just as a placeholder/reminder that this table doesn't store dates with microseconds
     *
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }
    */

    public function toSearchableArray()
    {
        $array = $this->toArray();

        unset(
            $array['chatmatic_user_uid'],
            $array['created_by'],
            $array['created_at_utc'],
            $array['updated_by'],
            $array['updated_at_utc'],
            $array['is_connected'],
            $array['facebook_connected_access_token'],
            $array['fb_category'],
            $array['fb_access_token'],
            $array['comments'],
            $array['subscribers'],
            $array['page_likes'],
            $array['active_triggers'],
            $array['optin_requests'],
            $array['optin_checkbox_requests'],
            $array['last_facebook_posts_pull_utc'],
            $array['fb_page_token']
        );

        return $array;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function licenses()
    {
        return $this->hasMany(PageLicense::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscribers()
    {
        return $this->hasMany(Subscriber::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriberCountHistory()
    {
        return $this->hasMany(SubscriberCountHistory::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tags()
    {
        return $this->hasMany(Tag::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function persistentMenus()
    {
        return $this->hasMany(PersistentMenu::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function triggers()
    {
        return $this->hasMany(Trigger::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflows()
    {
        return $this->hasMany(Workflow::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTriggers()
    {
        return $this->hasMany(WorkflowTrigger::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowSteps()
    {
        return $this->hasMany(WorkflowStep::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pageAdmins()
    {
        return $this->hasMany(PageAdmin::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function automations()
    {
        return $this->hasMany(Automation::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function integrations()
    {
        return $this->hasMany(Integration::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function integrationRecords()
    {
        return $this->hasMany(Integration::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customFields()
    {
        return $this->hasMany(CustomField::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apiErrors()
    {
        return $this->hasMany(ApiErrorLog::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zapierWebhookSubscriptions()
    {
        return $this->hasMany(ZapierWebhookSubscription::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zapierEventLogs()
    {
        return $this->hasMany(ZapierEventLog::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sms_history()
    {
        return $this->hasMany(SmsHistory::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sms_balance()
    {
        return $this->hasOne(SmsBalance::class, 'page_uid','uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManys
     */
    public function stripe_purchases()
    {
        return $this->hasOne(StripePurchase::class, 'page_uid','uid');
    }

    /**
     * Scope to order the response by the request's orderBy parameter values
     *
     * @param $query
     * @param Request $request
     * @return mixed
     */
    public function scopeOrderByRequest($query, Request $request)
    {
        if($request->has('orderBy') && $request->has('direction'))
        {
            $query->orderBy($request->get('orderBy'), $request->get('direction'));
        }
        return $query;
    }

    /**
     * Update the value of the page's "subscribers" field with an accurate count of the subscribers
     * from the related database records
     *
     * @return bool
     */
    public function updateSubscribersCount()
    {
        $result = false;
        Page::withoutSyncingToSearch(function() {
            $count = $this->subscribers()->count();
            $this->subscribers = $count;
            $result = $this->save();
        });
        return $result;
    }

    public function updateLikesCount()
    {
        $result = false;
        Page::withoutSyncingToSearch(function()
        {
            $count              = $this->getLikesCount();
            $this->page_likes   = $count;
            $result             = $this->save();
        });
        return $result;
    }

    public function getLikesCount()
    {
        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $count = $client->getPageLikes($this->fb_id, $this->facebook_connected_access_token);

        dd($count);
    }

    /**
     * Update our database's representation of this page's posts
     *
     * @param int $max_results
     * @return bool|mixed
     */
    public function updatePosts($max_results = 20)
    {
        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $response = $client->getPosts($this->fb_id, $this->facebook_connected_access_token, $this->last_facebook_posts_pull_utc, $max_results);

        if($response['error'] === 1)
            return $response;

        // Get the facebook response from the result
        $fb_response    = $response['fb_response'];

        $all_posts      = [];
        $utc_timezone   = new \DateTimeZone('UTC');
        // Loop through all results dropping into an array with our parameter set
        foreach($fb_response->getGraphEdge() as $post)
        {
            // Setup created time
            $created_time_utc       = $post->getField('created_time');
            $created_time_utc->setTimezone($utc_timezone);
            // Determine number of comments
            $total_comment_count    = 0;
            $comments_edge          = $post->getField('comments', null);
            if($comments_edge)
            {
                $comments_meta_data     = $comments_edge->getMetaData();
                if (isset($comments_meta_data['summary']['total_count'])) {
                    $total_comment_count = $comments_meta_data['summary']['total_count'];
                }
            }
            // Build $all_posts array
            $all_posts[] = [
                'facebook_post_id'          => $post->getField('id'),
                'permalink_url'             => $post->getField('permalink_url'),
                'message'                   => str_limit($post->getField('message', ''), 4000),
                'picture'                   => $post->getField('picture', null),
                'facebook_created_time_utc' => $created_time_utc->format('Y-m-d H:i:s'),
                'comments'                  => $total_comment_count
            ];
        }

        // Reverse the array of posts so that we're processing the oldest provided first
        $all_posts = array_reverse($all_posts);

        // Loop through resulting post array inserting those that are new and updating existing posts
        foreach($all_posts as $post)
        {
            // Determine if this post already exists...
            // First we'll see if we can find a post by it's post id
            $fb_post_id = $post['facebook_post_id'];
            $post_model = $this->posts()->where('facebook_post_id', $fb_post_id)->first();

            // If there's no post found by the post id we'll try by permalink url in case the post id changed
            if( ! $post_model)
            {
                // We're using the permalink here because the post id actually changes every time the post changes (edits/published/etc)
                // But, also, there's a null permalink_url for scheduled posts
                $fb_permalink_url   = $post['permalink_url'];
                $post_model         = $this->posts()
                    ->where('permalink_url', $fb_permalink_url)
                    ->orderBy('uid', 'desc')  // If there are more than one this makes sure we grab the latest
                    ->first();
            }

            // If there's still no post model we'll check by the post_object_id
            if( ! $post_model && isset($post['facebook_post_object_id']))
            {
                $fb_post_object_id  = $post['facebook_post_object_id'];
                $post_model         = $this->posts()
                    ->where('facebook_post_object_id', $fb_post_object_id)
                    ->orderBy('uid', 'desc') // If there are more than one this makes sure we grab the latest
                    ->first();
            }

            if($post_model)
            {
                // It's existing - let's update it.
                $post_model->permalink_url                = $post['permalink_url'];
                $post_model->message                      = $post['message'];
                $post_model->picture                      = $post['picture'];
                $post_model->facebook_created_time_utc    = $post['facebook_created_time_utc'];
                $post_model->comments                     = $post['comments'];
                $post_model->save();
            }
            else
            {
                // Doesn't exist, let's create it
                $post_model = $this->posts()->create($post);
            }
        }

        // If we made it all the way here we can update our last_facebook_posts_pull_utc
        $this->last_facebook_posts_pull_utc = Carbon::now();
        $this->save();

        return true;
    }

    /**
     * Get the URL of the cover photo
     *
     * @return mixed
     */
    public function coverPhotoURL()
    {
        if($this->is_connected === 0)
            return 'not-connected';

        if(strlen($this->facebook_connected_access_token) < 10)
            return 'no-token';

        // Set key
        $cache_key = 'cover_photo_page_'.$this->uid;
        // Check cache for existing
        if(\Cache::has($cache_key))
            return \Cache::get($cache_key);

        // Get the url
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();
        $response    = $client->getPageCoverPhotoUrl($this, $this->facebook_connected_access_token);

        // Cache if populated
        if(isset($response['url']) && $response['error'] === 0)
        {
            \Cache::put($cache_key, $response['url'], 1);
            $url = $response['url'];
        }
        else
        {
            $url = $response['url'];
        }

        return $url;
    }

    /**
     * Connect this page to the Chatmatic facebook app
     *
     * @param $user_access_token
     * @return mixed
     */
    public function connectToChatmatic($user_access_token)
    {
        $response['error']      = 0;
        $response['error_msg']  = '';

        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        // First we need to get an up-to-date access token for this page from facebook and throw it into our database
        $page_access_token = $client->getPageAccessToken($this, $user_access_token);
        if($page_access_token['error'] === 1 || mb_strlen($page_access_token['token']) < 12 || is_null($page_access_token['token']))
        {
            if($page_access_token['error'] === 1)
            {
                // Pass the error we received
                return $page_access_token;
            }

            // Otherwise it must be a missing/null access token
            $response['error']      = 1;
            $response['error_msg']  = 'Unable to acquire a page access token for '.$this->fb_name.'. Are you an admin?';

            return $response;
        }
        $page_access_token = $page_access_token['token'];

        // TODO: Should we be confirming admin permissions on the page before proceeding??

        // Next we'll setup the get started button
        $page_get_started = $client->installPageGetStartedButton($this, $user_access_token, $page_access_token);
        if($page_get_started['error'] === 1)
        {
            return $page_get_started;
        }
        $page_get_started = $page_get_started['success'];

        // If the get started was successfully added we'll attempt to connect the page
        if ($page_get_started)
        {
            // Connect the page
            $page_connected = $client->connectPage($this);

            if($page_connected['error'] === 1)
            {
                // Return the error response from connectPage()
                return $page_connected;
            }

            // Update the page's connected status
            $this->is_connected = 1;
            $this->save();

            $response['success'] = 1;

        } else {
            $response['error']      = 1;
            $response['error_msg']  = 'Unable to set up a "Get Started" message for your fan page.  Please make sure you are an administrator for the page and try again.';
        }

        return $response;
    }

    /**
     * Disconnect this page from Chatmatic's facebook app
     *
     * @return mixed
     */
    public function disconnectFromChatmatic()
    {
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['success']    = 0;

        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        // Disconnect the page
        $page_disconnected = $client->disconnectPage($this);
        // If there was an error (other than the page not being connected) we'll return that
        if($page_disconnected['error'] === 1
            && ! mb_stristr($page_disconnected['error_msg'], 'App is not installed')
            && ! mb_stristr($page_disconnected['error_msg'], 'Error validating access token')
        )
        {
            return $page_disconnected;
        }

        // Update the page's 'is_connected' value if it was successfully disconnected (or wasn't connected in the first place)
        $page_disconnected = $page_disconnected['success'];
        if($page_disconnected
            || mb_stristr($page_disconnected['error_msg'], 'App is not installed')
            || mb_stristr($page_disconnected['error_msg'], 'Error validating access token')
        )
        {
            $this->is_connected = 0;
            $this->save();

            $response['success'] = 1;
        }

        return $response;
    }

    /**
     * Create a default persistent menu for this page
     *
     * @return array|Model
     */
    public function generateDefaultPersistentMenu()
    {
        $menu = [
            'locale'                    => 'default',
            'composer_input_disable'    => false,
        ];

        $menu = $this->persistentMenus()->create($menu);

        return $menu;
    }

    /**
     * @return bool
     */
    public function isLicensed()
    {
        if($this->licenses()->first())
            return true;

        return false;
    }

    /**
     * @return mixed
     */
    public function disablePersistentMenu()
    {
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['success']    = 0;

        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $fb_response = $client->disablePersistentMenu($this->facebook_connected_access_token);

        if($fb_response['error'] === 1)
        {
            return $fb_response;
        }

        $response['success'] = 1;

        return $response;
    }

    /**
     * Make Facebook API call to create the persistent menu configured for this page
     *
     * @return mixed
     */
    public function updatePersistentMenu()
    {
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['success']    = 0;

        /** @var \App\PersistentMenu $menu */
        $menu   = $this->persistentMenus()->first();
        if( ! $menu)
        {
            $menu = $this->generateDefaultPersistentMenu();
        }

        if($this->isLicensed())
        {
            $items  = $menu->menuItems()
                ->whereNull('parent_menu_uid')
                ->where('branded', 0)
                ->orderBy('uid', 'asc')
                ->get();
        }
        else
        {
            // Get the branded menu item as a single menu item
            $branded_menu_item  = $menu->menuItems()
                ->where('branded', 1)
                ->first();

            // Check here if the branded menu item exists, if not, create it
            if( ! $branded_menu_item)
            {
                // No branded menu item exists, let's create one
                $branded_menu_item = $menu->menuItems()->create([
                    'type'              => 'link',
                    'title'             => 'Powered by Chatmatic',
                    'payload'           => 'https://chatmatic.com?uid='.$this->fb_id,
                    'branded'           => 1,
                ]);
            }

            // Get the non-branded menu items
            $non_branded_menu_items = $menu->menuItems()
                ->whereNull('parent_menu_uid')
                ->where('branded', 0) // ensure that we don't get any branded menu items
                ->orderBy('uid', 'asc')
                ->get();

            // Combine the two into a new collection
            $menu_items[] = $branded_menu_item;

            foreach($non_branded_menu_items as $non_branded_menu_item)
            {
                // Confirm not more than 3
                if(count($menu_items) < 3)
                    $menu_items[] = $non_branded_menu_item;
            }

            $items = collect($menu_items);
        }

        $menu_array = [
            'locale' => $menu->locale
        ];
        $menu_items_array = [];
        /** @var \App\PersistentMenuItem $item */
        foreach($items as $item)
        {
            $menu_item_array['title']   = $item->title;
            $menu_item_array['type']    = $item->type;
            $menu_item_array['payload'] = $item->payload;

            if($item->type === 'submenu')
            {
                $sub_items = PersistentMenuItem::where('parent_menu_uid', $item->uid)->get();

                foreach($sub_items as $sub_item)
                {
                    $menu_item_array['payload'][] = [
                        'title'     => $sub_item->title,
                        'type'      => $sub_item->type,
                        'payload'   => $sub_item->payload
                    ];
                }
            }

            $menu_items_array[] = $menu_item_array;
        }

        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $fb_response = $client->createPersistentMenu($menu_array, $menu_items_array, $this->facebook_connected_access_token);

        if($fb_response['error'] === 1)
        {
            return $fb_response;
        }

        $response['success'] = 1;

        return $response;
    }

    /**
     * Update this pages subscriber count history
     *
     * @return array|Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function updateSubscriberCountHistory()
    {
        $date           = Carbon::now()->format('Y-m-d');
        $current_count  = $this->subscribers()->count();

        // Is there a record already for today?
        $record         = $this->subscriberCountHistory()->where('date_utc', $date)->first();

        // If there's an existing record we'll update the maximum value
        if($record)
        {
            // Only update the maximum value when it's higher than the existing
            if($record->maximum < $current_count)
            {
                $record->maximum = $current_count;
                $record->save();
            }
            elseif($record->minimum > $current_count)
            {
                $record->minimum = $current_count;
                $record->save();
            }
        }
        else
        {
            // Doesn't exist, create the first record for this day
            $record = [
                'date_utc'  => $date,
                'minimum'   => $current_count,
                'maximum'   => $current_count
            ];
            $record = $this->subscriberCountHistory()->create($record);
        }

        return $record;
    }

    /**
     * Find page by facebook id
     *
     * @param $facebook_id
     * @return Model|null|object|static
     */
    public static function findFromFBId($facebook_id)
    {
        return self::where('fb_id', $facebook_id)->first();
    }

    /**
     * Helper method used to mass update all connected page's persistent menus
     *
     * @param int $max
     */
    public static function updatePersistentMenus()
    {
        $error = 0;
        $count = 0;
        // Find out all non-lincensed pages
        \DB::table('pages as p')
            ->select('p.uid')
            ->leftJoin('chatmatic_page_licenses as cpl','cpl.page_uid','=','p.uid')
            ->whereNull('cpl.page_uid')
            ->where('p.is_connected','=','1')
            ->where('p.persistent_menus_active','=','1')
            ->orderBy('uid')
            // Chucnk the result for every 100 pages
            ->chunk(100, function ($pages){ 
                foreach($pages as $pg) 
                    { 
                        $page = \App\Page::find($pg->uid);
                        // Let's update the persistent menu
                        $response = $page->updatePersistentMenu();
                        if($response['error'])
                        {
                            if(stristr($response['error_msg'], 'token'))
                                $response['error_msg'] = 'Token related issue.';
                            echo $page->fb_name.' ... [ERROR]: '.$response['error_msg'].PHP_EOL;
                           // $error++;
                        }
                    //$count++;
                    } 
                    // Sleep 5 seconds before the next chuck
                    // We dont want to have "Too many requests error" from facebook
                    sleep(5); 
                });
         //echo PHP_EOL.'Out of '.$count. ' pages without licenses '.$error.' failed to update.'.PHP_EOL;
    }

    /**
     * @return mixed
     */
    public function retrieveWhiteList()
    {
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['success']    = 0;

        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $fb_response = $client->listWithedList($this->facebook_connected_access_token);

        if($fb_response['error'] === 1)
        {
            return [
                'error'         => 1,
                'error_msg'     => $fb_response
            ];
        }

        $response['success'] = 1;
        $response['urls'] = $fb_response['fb_response'];

        return $response;
    }


    /**
     * @param $facebook_id
     * @return mixed
     */
    public function updateWhiteList($urls)
    {
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['success']    = 0;

        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $fb_response = $client->createUdateWithedList($urls,$this->facebook_connected_access_token);

        if($fb_response['error'] === 1)
        {
            return [
                'error'         => 1,
                'error_msg'     => $fb_response
            ];
        }

        $response['success'] = 1;
        $response['urls'] = $fb_response['fb_response'];

        return $response;
    }


        /**
     * @param $facebook_id
     * @return mixed
     */
    public function updateGreeting($message)
    {
        $response['error']      = 0;
        $response['error_msg']  = '';
        $response['success']    = 0;

        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $fb_response = $client->updateGreeting($message, $this->facebook_connected_access_token);

        if($fb_response['error'] === 1)
        {
            return [
                'error'         => 1,
                'error_msg'     => $fb_response
            ];
        }

        $response['success'] = 1;
        $response['urls'] = $fb_response['fb_response'];

        return $response;
    }
}
