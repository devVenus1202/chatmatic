<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ZapierWebhookSubscription
 *
 * @property int $uid
 * @property int $page_uid
 * @property string $action
 * @property string|null $target_url
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property \Illuminate\Support\Carbon $updated_at_utc
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierWebhookSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierWebhookSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierWebhookSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierWebhookSubscription whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierWebhookSubscription whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierWebhookSubscription wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierWebhookSubscription whereTargetUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierWebhookSubscription whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ZapierWebhookSubscription whereUpdatedAtUtc($value)
 * @mixin \Eloquent
 * @property-read \App\Page $page
 */
class ZapierWebhookSubscription extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'zapier_webhook_subscriptions';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid', 'created_at_utc'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }
}
