<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\StripeSubscription
 *
 * @property int $uid
 * @property string $stripe_subscription_id
 * @property string $stripe_plan_id
 * @property int $chatmatic_user_uid
 * @property string $status
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property string $label
 * @property string $subscription_renewal_utc
 * @property int $price
 * @property string|null $coupon_code
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PageLicense[] $licences
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereChatmaticUserUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereCouponCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereStripePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereStripeSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereSubscriptionRenewalUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription whereUpdatedAtUtc($value)
 * @mixin \Eloquent
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StripeSubscription query()
 * @property-read int|null $licences_count
 */
class StripeSubscription extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'stripe_subscriptions';
    protected $primaryKey   = 'uid';

    public $incrementing    = true;

    protected $guarded      = ['created_at_utc'];

    protected $dates = [
        'created_at_utc',
        'updated_at_utc',
        'subscription_renewal_utc'
    ];

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'chatmatic_user_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function licences()
    {
        return $this->hasMany(PageLicense::class, 'stripe_subscription_uid', 'uid');
    }

    /**
     * @return \Stripe\StripeObject
     */
    public function getStripeData()
    {
        $stripe_key     = \Config::get('chatmatic.services.stripe.secret');
        \Stripe\Stripe::setApiKey($stripe_key);

        $subscription   = \Stripe\Subscription::retrieve($this->stripe_subscription_id);

        return $subscription;
    }
}
