<?php

namespace App;

use App\Chatmatic\APIHelpers\FacebookGraphAPIHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Subscriber
 *
 * @property int $uid
 * @property int $page_uid
 * @property string|null $user_psid
 * @property string|null $user_ref
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $profile_pic_url
 * @property string|null $locale
 * @property float|null $timezone
 * @property string|null $gender
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property int $messages_read
 * @property int $total_clicks
 * @property string $last_engagement_utc
 * @property string $last_read_watermark
 * @property string $pause_subscriptions_until_utc
 * @property int $messages_attempted_from_bot
 * @property int $messages_accepted_from_bot
 * @property int $messages_attempted_from_page
 * @property int $messages_accepted_from_page
 * @property string $last_subscriber_action_utc
 * @property int|null $chatmatic_user_uid
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereChatmaticUserUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereLastEngagementUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereLastReadWatermark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereLastSubscriberActionUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereMessagesAcceptedFromBot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereMessagesAcceptedFromPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereMessagesAttemptedFromBot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereMessagesAttemptedFromPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereMessagesRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber wherePauseSubscriptionsUntilUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereProfilePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereTotalClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereUserPsid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereUserRef($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SubscriberChatHistory[] $chatHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SubscriberDeliveryHistory[] $deliveryHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Subscription[] $subscriptions
 * @property float|null $lat
 * @property float|null $lon
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereLon($value)
 * @property int $active
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber query()
 * @property bool $live_chat_active
 * @property string|null $live_chat_heartbeat_utc
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereLiveChatActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscriber whereLiveChatHeartbeatUtc($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tag[] $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CustomFieldResponse[] $customFieldResponses
 * @property-read int|null $chat_history_count
 * @property-read int|null $custom_field_responses_count
 * @property-read int|null $delivery_history_count
 * @property-read int|null $subscriptions_count
 * @property-read int|null $tags_count
 */
class Subscriber extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'subscribers';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    protected $dates        = ['created_at_utc', 'updated_at_utc', 'last_engagement_utc'];

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }

    /**
     * Page this Subscriber is associated with
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }

    /**
     * Subscriptions associated with this Subscriber
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'subscriber_uid', 'uid');
    }

    /**
     * Messages received from this Subscriber
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chatHistory()
    {
        return $this->hasMany(SubscriberChatHistory::class, 'subscriber_uid', 'uid');
    }

    /**
     * Messages sent to this Subscriber
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deliveryHistory()
    {
        return $this->hasMany(SubscriberDeliveryHistory::class, 'subscriber_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customFieldResponses()
    {
        return $this->hasMany(CustomFieldResponse::class, 'subscriber_psid', 'user_psid');
    }

    /**
     * This method will attempt to update a subscribers data with facebook
     * TODO: This method SHOULD ONLY BE USED AS A UTILITY, IT OUTPUTS DEBUGGING STRINGS
     *
     * @throws \Exception
     */
    public function updateSubscriberData()
    {
        $page = $this->page;

        // Confirm the page is connected
        if($page && $page->is_connected)
        {
            // Get the facebook user's info
            $facebook_user_data = Subscriber::retrieveSubscriberDataFromFacebookAPI($this->user_psid, $this->page->facebook_connected_access_token);

            if( ! $facebook_user_data['error'])
            {
                $facebook_user_data = $facebook_user_data['user'];

                if(isset($facebook_user_data['first_name']))
                    $this->first_name       = $facebook_user_data['first_name'];
                if(isset($facebook_user_data['last_name']))
                    $this->last_name        = $facebook_user_data['last_name'];
                if(isset($facebook_user_data['name']))
                {
                    $full_name = explode(' ', $facebook_user_data['name']);

                    if(count($full_name) === 2)
                    {
                        $this->first_name   = $full_name[0];
                        $this->last_name    = $full_name[1];
                    }

                    if(count($full_name) === 3)
                    {
                        $this->first_name   = $full_name[0].' '.$full_name[1];
                        $this->last_name    = $full_name[2];
                    }
                }
                if(isset($facebook_user_data['profile_pic']))
                    $this->profile_pic_url  = $facebook_user_data['profile_pic'];
                if(isset($facebook_user_data['gender']))
                    $this->gender           = $facebook_user_data['gender'];
                if(isset($facebook_user_data['locale']))
                    $this->locale           = $facebook_user_data['locale'];
                if(isset($facebook_user_data['timezone']))
                    $this->timezone         = $facebook_user_data['timezone'];

                $this->save();
            }
            else
            {
                if(mb_stristr($facebook_user_data['error_msg'], 'Error validating access token'))
                    echo 'Bad token.'.PHP_EOL;
                elseif(mb_stristr($facebook_user_data['error_msg'], 'The user must be an administrator'))
                    echo 'User not an admin.'.PHP_EOL;
                else
                    echo $facebook_user_data['error_msg'].PHP_EOL;
            }
        }
    }

    /**
     * Find or create a new Subscriber with a given page scoped id (psid) and Page
     * // TODO: This method really should be using the public method updateSubscriberData() from this class
     * @param $user_psid
     * @param Page $page
     * @return Subscriber
     * @throws \Exception
     */
    public static function findOrCreatePageSubscriber($user_psid, Page $page) : self
    {
        $subscriber = $page->subscribers()->where('user_psid', $user_psid)->first();
        if( ! $subscriber)
        {
            // Get the facebook user's info
            $facebook_user_data = Subscriber::retrieveSubscriberDataFromFacebookAPI($user_psid, $page->facebook_connected_access_token);

            if($facebook_user_data['error'])
                throw new \Exception($facebook_user_data['error_msg']);

            $facebook_user_data = $facebook_user_data['user'];

            // The subscriber doesn't exist, let's create it
            $sub = [
                'page_uid'          => $page->uid,
                'user_psid'         => $user_psid,
                'email'             => $facebook_user_data['email'] ?? null,
                'first_name'        => $facebook_user_data['first_name'] ?? null,
                'last_name'         => $facebook_user_data['last_name'] ?? null,
                'profile_pic_url'   => $facebook_user_data['profile_pic'] ?? null,
                'locale'            => $facebook_user_data['locale'] ?? null,
                'timezone'          => $facebook_user_data['timezone'] ?? null,
                'gender'            => $facebook_user_data['gender'] ?? null,
            ];

            $subscriber = Subscriber::create($sub);

            // New Subscriber, we'll increment the pages.subscriber_count counter
            // Todo: Should probably create a Subscriber event observer that increments on new inserts to the table
            $page->increment('subscribers');
        }

        return $subscriber;
    }

    /**
     * Get an array of the last 25 messages between this subscriber and the page their subscribed to
     *
     * @return array
     */
    public function getSubscriberChatHistoryFromFacebookAPI()
    {
        $fb_helper = new FacebookGraphAPIHelper(
            config('chatmatic.app_id'),
            config('chatmatic.app_secret')
        );

        $chat_history = $fb_helper->getSubscriberChatHistory($this->user_psid, $this->page->facebook_connected_access_token);

        return $chat_history;
    }

    /**
     * Update/Create a custom field response given a new/updated response value
     *
     * @param CustomField $custom_field
     * @param $response_value
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function fillCustomField(CustomField $custom_field, $response_value)
    {
        // Is there an existing response for this field and subscriber?
        $response = $this->customFieldResponses()->where('custom_field_uid', $custom_field->uid)->first();

        // Update it if it exists
        if($response)
        {
            $response->response = $response_value;
            $response->save();
        }
        else // Doesn't exist, create one
        {
            $response = $this->customFieldResponses()->create([
                'response'          => $response_value,
                'custom_field_uid'  => $custom_field->uid
            ]);
        }

        return $response;
    }

    /**
     * @param null $new_model
     * @return mixed
     */
    public function cleanDataForZapier($new_model = null)
    {
        $post_data['user_psid']     = $this->user_psid;
        $post_data['user_ref']      = $this->user_ref;
        $post_data['email']         = $this->email;
        $post_data['phone_number']  = $this->phone_number;
        $post_data['first_name']    = $this->first_name;
        $post_data['last_name']     = $this->last_name;
        $post_data['profile_pic_url'] = $this->profile_pic_url;
        $post_data['locale']        = $this->locale;
        $post_data['timezone']      = $this->timezone;
        $post_data['gender']        = $this->gender;

        if($new_model !== null)
            $attached_class = get_class($new_model);
        else
            $attached_class = $new_model;

        // Custom Values
        // Get all of Pages custom values
        $page = $this->page;
        $custom_fields = $page->customFields()->get();

        // Get all of Subscribers custom values
        $custom_field_responses = $this->customFieldResponses()->get();

        // Provide array of all custom values with filled values for those that exist for the subscriber and include
        // indication of if it's new (this event was fired by it's association)
        $applied_custom_fields = [];
        if($custom_fields)
        {
            foreach($custom_fields as $custom_field)
            {
                // Set flag determining if this custom field is new, as in this webhook is being sent because of this custom field being associated
                $new = false;
                if(get_class($custom_field) === $attached_class && $custom_field->uid === $new_model->uid)
                {
                    $new = true;
                }

                // Set the value of this custom field so we can dupe check it
                $value = $custom_field->field_name;

                // TODO: This could be done better...
                $exists = false;
                foreach($applied_custom_fields as $applied_field_name => $applied_field_value)
                {
                    if($applied_field_name === $value)
                        $exists = true;
                }

                // If it wasn't in the array already add it
                if( ! $exists)
                {
                    // Here we're checking to see if this subscriber has responded to fill this value, if so, attaching it
                    $attribute_response_value = '';
                    foreach($custom_field_responses as $response)
                    {
                        if($custom_field->uid === $response->custom_field_uid)
                        {
                            $attribute_response_value = $response->response;
                        }
                    }

                    $applied_custom_fields[$value] = $attribute_response_value;
                }
            }
        }

        // Tags
        $tags           = $this->tags()->get();
        $applied_tags   = [];
        if($tags)
        {
            // Generate an array of unique tags
            foreach($tags as $tag)
            {
                // Set flag determining if this tag is new, as in this webhook is being sent because of this tag being associated
                $new = false;
                if(get_class($tag) === $attached_class && $tag->uid === $new_model->uid)
                {
                    $new = true;
                }

                // Set the value of this tag so we can dupe check it
                $value = $tag->value;

                // TODO: This could be done better...
                $exists = false;
                foreach($applied_tags as $applied_tag)
                {
                    if($applied_tag['tag'] === $value)
                        $exists = true;
                }

                // If it wasn't in the array already add it
                if( ! $exists)
                {
                    $applied_tags[] =
                        [
                            'tag' => $value,
                            'new' => $new
                        ];
                }
            }

            $post_data['tags'] = $applied_tags;
        }

        // Custom Attributes
        // $post_data['user_attributes'] = $applied_custom_fields;

        foreach($applied_custom_fields as $applied_custom_field_name => $applied_custom_field_value)
        {
            $post_data[$applied_custom_field_name] = $applied_custom_field_value;
        }

        // Campaigns
        /*
        // Get array of all campaigns for this page
        $campaigns = $page->campaigns()->get();

        // Get array of all campaigns this subscriber is subscribed to
        $subscriptions = $this->subscriptions()->where('type', 'campaign')->get();

        $response_campaigns = [];
        foreach($campaigns as $campaign) {
            // Set flag determining if this campaign is new, as in this webhook is being sent because of this campaign being associated
            $new = false;
            if (get_class($campaign) === $attached_class && $campaign->uid === $new_model->uid) {
                $new = true;
            }

            // Check to determine if this campaign is already included in the response
            $exists = false;
            foreach ($response_campaigns as $response_campaign) {
                if ($response_campaign['campaign_name'] === $campaign->campaign_name) {
                    $exists = true;
                }
            }

            // Determine if this subscriber is subscribed to this campaign
            $is_subscribed = false;
            $subscribed_campaign_workflow = null;
            foreach($subscriptions as $campaign_subscription)
            {
                if($campaign_subscription->type_uid === $campaign->uid)
                {
                    $is_subscribed                  = true;
                    $subscribed_campaign_workflow   = $campaign_subscription->workflow;
                }
            }

            // The name of the workflow that the person subscribed to this campaign with, as it _could_ be different than what is actively on the campaign
            $subscribed_campaign_workflow_name = '';
            if($subscribed_campaign_workflow)
            {
                $subscribed_campaign_workflow_name  = $subscribed_campaign_workflow->name;
            }

            if ( ! $exists)
            {
                $name_of_workflow_currently_on_campaign = '';
                if($campaign->workflow)
                    $name_of_workflow_currently_on_campaign = $campaign->workflow->name;

                $response_campaigns[] = [
                    'campaign_name'         => $campaign->campaign_name,
                    'campaign_type'         => $campaign->type,
                    'workflow'              => $name_of_workflow_currently_on_campaign,
                    'is_subscribed'         => $is_subscribed,
                    'subscribed_workflow'   => $subscribed_campaign_workflow_name,
                    'new'                   => $new, // TODO: This won't work currently - we're not passing the value for the new campaign
                ];
            }
        }
        

        // Create array to send to zapier that includes all campaigns and whether or not the subscriber is subscribed
        $post_data['campaigns'] = $response_campaigns;
        */

        return $post_data;
    }

    /**
     * Get a Facebook user's profile information from a given page scoped id (psid) and a page access token
     *
     * @param $user_psid
     * @param $page_access_token
     * @return array
     */
    public static function retrieveSubscriberDataFromFacebookAPI($user_psid, $page_access_token)
    {
        $fb_helper = new FacebookGraphAPIHelper(
            config('chatmatic.app_id'),
            config('chatmatic.app_secret')
        );

        return $fb_helper->getGraphUser($user_psid, $page_access_token);
    }
}
