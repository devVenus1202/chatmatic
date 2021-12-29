<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * App\User
 *
 * @property int $uid
 * @property float $balance
 * @property string $facebook_email
 * @property int $stripe_subscription
 * @property int $chatmatic_user_id
 * @property-read \App\User $chatmatic_user
 * @property-read \App\Page $page
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\StripeSubscription[] $stripeSubscriptions
 * @property-read int|null $stripe_subscriptions_count
 * @method static \Illuminate\Database\Eloquent\Builder|SmsBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsBalance query()
 * @mixin \Eloquent
 */
class SmsBalance extends Model
{
    public $timestamps = false;

    protected $table        = 'sms_balances';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

   
   public function chatmatic_user()
   {
        return $this->belongsTo(User::class, 'chatmatic_user_uid','uid');
   } 

   public function page()
   {
        return $this->belongsTo(Page::class, 'page_uid','uid');
   } 


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stripeSubscriptions()
    {
        return $this->hasMany(StripeSubscription::class, 'chatmatic_user_uid', 'uid');
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
        $stripe_key = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);

        $response = \Stripe\Customer::update($this->stripe_customer_id, [
            'source'    => $source_id
        ]);

        return true;
    }
}
