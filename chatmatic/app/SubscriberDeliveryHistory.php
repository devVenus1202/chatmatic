<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\SubscriberDeliveryHistory
 *
 * @property int $uid
 * @property int $subscriber_uid
 * @property \Carbon\Carbon $created_at_utc
 * @property string $source_type
 * @property int $type_uid
 * @property string $fb_message_id
 * @property int $marked_as_read
 * @property int $page_uid
 * @property int|null $workflow_uid
 * @property-read \App\Page $page
 * @property-read \App\Subscriber $subscriber
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory whereFbMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory whereMarkedAsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory whereSubscriberUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory whereTypeUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory whereWorkflowUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberDeliveryHistory query()
 */
class SubscriberDeliveryHistory extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = null;

    protected $table        = 'subscriber_delivery_history';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

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
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_uid', 'uid');
    }
}
