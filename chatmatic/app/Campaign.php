<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Campaign
 *
 * @property int $uid
 * @property string $public_id
 * @property int $page_uid
 * @property int|null $workflow_uid
 * @property int $enabled
 * @property int $deleted
 * @property int $impressions
 * @property string $type
 * @property string $presubmit_title
 * @property string $presubmit_body
 * @property string $presubmit_image
 * @property string $approval_method
 * @property string $checkbox_plugin_button_text
 * @property string $postsubmit_type
 * @property string $postsubmit_redirect_url
 * @property string $postsubmit_redirect_url_button_text
 * @property string $postsubmit_content_title
 * @property string $postsubmit_content_body
 * @property string $postsubmit_content_image
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property string $campaign_name
 * @property string|null $messenger_code_base_url
 * @property string $m_me_url
 * @property int $conversions
 * @property-read \App\Page $page
 * @property-read \App\Workflow|null $workflow
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereApprovalMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereCampaignName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereCheckboxPluginButtonText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereConversions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereImpressions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereMMeUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereMessengerCodeBaseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePostsubmitContentBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePostsubmitContentImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePostsubmitContentTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePostsubmitRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePostsubmitRedirectUrlButtonText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePostsubmitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePresubmitBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePresubmitImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePresubmitTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereWorkflowUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign query()
 * @property string|null $custom_ref
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereCustomRef($value)
 * @property int $visits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereVisits($value)
 * @property int $messages_clicked
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereMessagesClicked($value)
 * @property string|null $event_type
 * @property int|null $event_type_uid
 * @property int|null $follow_up_delay
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereEventTypeUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Campaign whereFollowUpDelay($value)
 */
class Campaign extends Model
{
    //const CREATED_AT = 'created_at_utc';
    //const UPDATED_AT = 'updated_at_utc';

    public $timestamps = false;

    protected $table = 'campaigns';
    protected $primaryKey = 'uid';

    protected $guarded = ['uid'];

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_uid', 'uid');
    }

    /**
     * Ghetto relationship for returning Subscriptions associated/attributed to this campaign
     *
     * Laravel's Eloquent doesn't support composite keys so we can't specify the 'type' _and_ 'type_uid' for a
     * -> belongsTo() type relationship. Because of this we're having to start an Eloquent query and return that,
     * which will handle somewhat-like a typical relationship. (No eager loading, not accessible with magic methods)
     *
     * TODO: Take a look at https://github.com/topclaudy/compoships and see about implementing here
     *
     * @return $this
     */
    public function subscriptions()
    {
        $query = Subscription::where('type', 'campaign')->where('type_uid', $this->uid);
        return $query;
    }

    /**
     * Another ghetto relationship to return a query object that is querying the Subscribers only matching those from
     * this campaign.
     *
     * @return Subscriber
     */
    public function subscribers()
    {
        $subscriptions = $this->subscriptions()->distinct('subscriber_uid')->get();

        $subscriber_ids = [];
        foreach($subscriptions as $subscription)
        {
            if( ! in_array($subscription->uid, $subscriber_ids))
                $subscriber_ids[] = $subscription->uid;
        }

        $query = Subscriber::whereIn('uid', $subscriber_ids);
        return $query;
    }

    /**
     * Another Ghetto relationship
     */
    public function messagesSent()
    {
        $query = SubscriberDeliveryHistory::where('source_type', 'campaign')->where('type_uid', $this->uid);
        return $query;
    }

    /**
     * Generate an m.dot url
     *
     * @param $ref
     * @return string
     */
    public function generateMDotMeURL($ref)
    {
        $page_url = $this->page->fb_page_token;

        // If the page id is found in the url then it's one of the vanity urls that doesn't work
        if(mb_stristr($page_url, $this->page->fb_id))
            $page_url = $this->page->fb_id;

        return 'http://m.me/' . rawurlencode($page_url) . '?ref=' . rawurlencode($ref);
        //return 'http://m.me/' . rawurlencode($this->page->fb_id) . '?ref=' . rawurlencode($ref);
    }

    /**
     * Generate scan code
     *
     * @return mixed
     */
    public function generateScanCode()
    {
        // Init facebook api helper
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $result = $client->generateScanCode($this->public_id, $this->page->facebook_connected_access_token);

        return $result;
    }

    /**
     * Generate a public id
     *
     * @return string
     */
    public static function generatePublicId()
    {
        $characters                 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $last_character_position    = strlen($characters) - 1;
        $now                        = gmdate('YmdHis');
        $new_public_id              = $characters[mt_rand(0, $last_character_position)];
        for ($i = strlen($now) - 1; $i >= 0; $i--)
        {
            $new_public_id .= $now[$i] . $characters[mt_rand(0, $last_character_position)];
        }

        return $new_public_id;
    }

    /**
     * Accepts a base64 encoded string and stores the image in our media storage, returning a URL
     *
     * @param $base64_string
     * @return array
     */
    public function uploadImage($base64_string)
    {
        $response = [
            'success' => 0,
            'error' => 0,
            'error_msg' => 0,
            'url' => null
        ];

        // Get the extension of the uploaded file
        $pos  = strpos($base64_string, ';');
        $type = explode(':', substr($base64_string, 0, $pos))[1];
        $ext  = explode('/', $type)[1];

        if($ext === 'jpeg')
            $ext = 'jpg';

        // Decode the base64 representation of the file
        // TODO: The current implementation is actually providing data url not base64, so we'll put this in place...
        $base64_string = str_replace(' ','+', $base64_string);
        // Remove this after?

        $b64_string = explode(',', $base64_string)[1];
        $file       = base64_decode($b64_string);

        // Generate the filename
        $filename = 'c_'.$this->uid.'_'.\App\WorkflowStepItem::generateRandomString(5).time().\App\WorkflowStepItem::generateRandomString(5).'.'.$ext;

        // Folder we'll put these in
        $storage_dir = 'campaign_images';

        // Generate the full path
        $full_path = $storage_dir.'/'.$filename;

        $upload = \Storage::disk('media')->put($full_path, $file, 'public');

        // Confirm the upload
        if( ! $upload)
        {
            // Upload failed
            $response['error']      = 1;
            $response['error_msg']  = 'Failed to upload file';

            return $response;
        }

        // Get the URL
        $media_url = \Storage::disk('media')->url($full_path);

        $response['success'] = 1;
        $response['url'] = $media_url;

        return $response;
    }
}
