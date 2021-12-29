<?php

namespace App;

use App\Chatmatic\APIHelpers\FacebookGraphAPIHelper;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Stripe\Charge;
use Laravel\Scout\Searchable;
use Stripe\Stripe;

/**
 * App\User
 *
 * @property int $uid
 * @property string $facebook_user_id
 * @property string $facebook_email
 * @property string $facebook_name
 * @property string $facebook_long_token
 * @property int $active
 * @property string $role
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property string|null $stripe_customer_id
 * @property string|null $country
 * @property string|null $address_1
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Page[] $pages
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User orderByRequest(\Illuminate\Http\Request $request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFacebookEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFacebookLongToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFacebookName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFacebookUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereStripeCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereZipCode($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PageLicense[] $pageLicenses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\StripeSubscription[] $stripeSubscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\AuthTicket[] $authTickets
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @property string|null $api_token
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereApiToken($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplate[] $templates
 * @property string|null $ext_api_token
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereExtApiToken($value)
 * @property-read int|null $auth_tickets_count
 * @property-read int|null $notifications_count
 * @property-read int|null $page_licenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SmsBalance[] $sms_balances
 * @property-read int|null $sms_balances_count
 * @property-read int|null $stripe_subscriptions_count
 * @property-read int|null $templates_count
 */
class User extends Authenticatable
{
    use Notifiable;
    use Searchable;

    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'chatmatic_users';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        unset(
            $array['active'],
            $array['role'],
            $array['created_at_utc'],
            $array['updated_at_utc'],
            $array['country'],
            $array['address_1'],
            $array['city'],
            $array['state'],
            $array['zip_code']
        );

        return $array;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authTickets()
    {
        return $this->hasMany(AuthTicket::class, 'chatmatic_user_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile() {
        return $this->hasOne(UserProfile::class, 'chatmatic_user_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function followings() {
        return $this->hasMany(UserFollowing::class, 'follower_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

    public function followers() {
        return $this->hasMany(UserFollowing::class, 'followee_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    //public function pages()
    //{
    //    return $this->hasMany(Page::class, 'created_by', 'uid');
    //}

    /**
     * Ghetto method to get pages this user has access to
     * TODO: This should probably be replaced by creating a model for the chatmatic_user_page_map and making this
     * TODO: relationship a hasManyThrough (this is basically a pivot table with non-standard column names)
     *
     * @return Page
     */
    public function pages()
    {
        $page_uids = [];
        foreach(\DB::table('chatmatic_user_page_map')->where('chatmatic_user_uid', $this->uid)->get() as $page_map)
        {
            $page_uids[] = $page_map->page_uid;
        }

        return Page::whereIn('uid', $page_uids);
    }

    public function sms_balances()
    {
        return $this->hasMany(SmsBalance::class, 'chatmatic_user_uid','uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stripeSubscriptions()
    {
        return $this->hasMany(StripeSubscription::class, 'chatmatic_user_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function pageLicenses()
    {
        return $this->hasManyThrough(PageLicense::class, StripeSubscription::class, 'chatmatic_user_uid', 'stripe_subscription_uid', 'uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function usedPageLicenses()
    {
        return $this->pageLicenses()->whereNotNull('page_uid')->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function unusedPageLicenses()
    {
        return $this->pageLicenses()->whereNull('page_uid')->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function templates()
    {
        return $this->hasMany(WorkflowTemplate::class, 'chatmatic_user_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sumoUser()
    {
        return $this->hasOne(AppSumoUser::class, 'chatmatic_user_id', 'uid');
    }

    /**
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
     * Permissions associated with this user's Facebook account in context with our App ID
     *
     * @return mixed
     * @throws \Facebook\Exceptions\FacebookResponseException
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function permissions()
    {
        $fb             = new FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $permissions    = $fb->userPermissionsList($this);

        return $permissions;
    }

    /**
     * @param $stripe_source
     * @return bool
     */
    public function createStripeAccount($stripe_source)
    {
        // Create a new stripe customer
        Stripe::setApiKey(config('services.stripe.secret'));
        $stripe_customer_object = \Stripe\Customer::create([
            'email'         => $this->facebook_email,
            'name'          => $this->facebook_name,
            'description'   => 'Customer for '.$this->facebook_email.' chatmatic user uid '.$this->uid,
            'metadata'      => [
                'chatmatic_user_uid'    => $this->uid
            ],
            'source'        => $stripe_source
        ]);

        // Save the customer id to the user model
        $this->stripe_customer_id = $stripe_customer_object->id;
        $this->save();

        return $this->save();
    }

    /**
     * @param $plan
     * @param null $coupon
     * @return \Stripe\ApiResource
     */
    public function createStripeSubscription($plan, $coupon = null)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        // Build the subscription array we'll post to Stripe
        $subscription_array = [
            'customer'  => $this->stripe_customer_id,
            'items'     => [
                [
                    'plan'  => $plan
                ]
            ],
        ];

        // If there's a coupon provided we'll use that
        if($coupon)
        {
            $subscription_array['coupon'] = $coupon;
        }

        // Create the subscription
        $stripe_subscription_object = \Stripe\Subscription::create($subscription_array);

        return $stripe_subscription_object;
    }

    /**
     * Get all charges associated with this user's customer id from Stripe's API
     *
     * @return \Stripe\Collection
     * @throws \Stripe\Error\Api
     */
    public function stripeCharges()
    {
        $stripe_key     = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);

        $charges        = Charge::all(['customer' => $this->stripe_customer_id]);

        return $charges;
    }

    /**
     * Get the profile photo for a given user
     *
     * @return mixed
     */
    public function profilePhotoURL()
    {
        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();
        $response    = $client->getUserProfilePhotoURL($this->facebook_user_id, $this->facebook_long_token);

        return $response;
    }

    /**
     * @return array|mixed
     */
    public function updateFanPagesList()
    {
        $response = [
            'success'   => 0,
            'error'     => 0,
            'error_msg' => '',
            'new_pages' => []
        ];

        $client = new \App\Chatmatic\APIHelpers\FacebookGraphAPIHelper(config('chatmatic.app_id'), config('chatmatic.app_secret'));
        $client->initClient();

        $fb_response = $client->userPagesList($this->facebook_long_token);

        if($fb_response['error'] === 1)
        {
            return $fb_response;
        }

        $new_pages = [];
        foreach($fb_response['pages'] as $page_array)
        {
            // If the page isn't found, add it.
            $fb_id          = $page_array['id'];
            $page  = Page::where('fb_id', $fb_id)->first();

            if( ! $page)
            {
                // Page isn't found, create it.
                $new_page = [
                    'fb_id'                             => $page_array['id'],
                    'fb_name'                           => $page_array['name'],
                    'fb_category'                       => $page_array['category'],
                    'fb_page_token'                     => $page_array['page_token'],
                    'fb_link'                           => $page_array['link'],
                    'created_by'                        => $this->uid,
                    'updated_by'                        => $this->uid,
                    'facebook_connected_access_token'   => $page_array['access_token'],
                ];

                $page           = Page::create($new_page);
                $new_pages[]    = $page->uid;
            }

            // Check 'chatmatic_user_page_map' to see if this page/user combination already exists
            $existing = \DB::table('chatmatic_user_page_map')
                ->where('chatmatic_user_uid', $this->uid)
                ->where('page_uid', $page->uid)
                ->first();

            // This user doesn't have this page - add it
            if( ! $existing)
            {
                $user_page_map_record = [
                    'chatmatic_user_uid'    => $this->uid,
                    'page_uid'              => $page->uid,
                    'facebook_page_access_token'    => $page_array['access_token']
                ];

                \DB::table('chatmatic_user_page_map')->insert($user_page_map_record);
            }
        }

        $response['success']    = 1;
        $response['new_pages']  = $new_pages;

        return $response;
    }

    /**
     * @return \stdClass
     */
    public function getStripeCard()
    {
        // Define default/empty card
        $card               = new \stdClass();
        $card->last4        = 'xxxx';
        $card->exp_month    = 'xx';
        $card->exp_year     = 'xxxx';

        $stripe_key = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);

        // Get billing details from customer object
        $stripe_customer_object = \Stripe\Customer::retrieve($this->stripe_customer_id);
        $default_source         = $stripe_customer_object->sources->data[0];
        if($default_source->object === 'source')
        {
            $card = $stripe_customer_object->sources->data[0]->card;
        }
        elseif($default_source->object === 'card')
        {
            $card = $stripe_customer_object->sources->data[0];
        }

        return $card;
    }

    /**
     * @param $source_id
     * @return bool
     */
    public function updateStripeDefaultSource($source_id)
    {
        // https://stripe.com/docs/saving-cards
        $stripe_key = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);

        $response = \Stripe\Customer::update($this->stripe_customer_id, [
            'source'    => $source_id
        ]);

        return true;
    }

    public function addSource($source)
    {
        // https://stripe.com/docs/saving-cards
        $stripe_key = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);

        \Stripe\Customer::createSource(
          $this->stripe_customer_id,
          [
            'source' => $source,
          ]
        );
    }

    /**
     * @return mixed
     */
    public function listCards()
    {
        // https://stripe.com/docs/saving-cards
        $stripe_key = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);

        /// Get customer accounts
        $stripe_customer_object = \Stripe\Customer::retrieve($this->stripe_customer_id);

        $stripe_customer_sources = $stripe_customer_object->sources->data;

        $cards = [];

        foreach ($stripe_customer_sources as $source) {
            if ( $source->type === 'card' )
            {
                $cards[] = $source->card;
            }
        }

        return $cards;
    }

    public function receivesBroadcastNotificationsOn(){
        return 'App.User.' . $this->uid;
    }
}
