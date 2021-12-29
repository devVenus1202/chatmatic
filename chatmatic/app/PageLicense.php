<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PageLicense
 *
 * @property int $uid
 * @property int $stripe_subscription_uid
 * @property int|null $page_uid
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property-read \App\Page|null $page
 * @property-read \App\StripeSubscription $stripSubscription
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageLicense whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageLicense wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageLicense whereStripeSubscriptionUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageLicense whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageLicense whereUpdatedAtUtc($value)
 * @mixin \Eloquent
 * @property-read \App\StripeSubscription $stripeSubscription
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageLicense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageLicense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PageLicense query()
 */
class PageLicense extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'chatmatic_page_licenses';
    protected $primaryKey   = 'uid';

    /*
     * This is here just as a placeholder/reminder that this table doesn't store dates with microseconds
     *
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }
    */

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
    public function stripeSubscription()
    {
        return $this->belongsTo(StripeSubscription::class, 'stripe_subscription_uid', 'uid');
    }
}
