<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Subscription
 *
 * @property int $uid
 * @property int $subscriber_uid
 * @property int $is_subscribed
 * @property int $count
 * @property string $type
 * @property int $type_uid
 * @property int|null $workflow_uid
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property int $messages_attempted
 * @property int $messages_accepted
 * @property int $page_uid
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereIsSubscribed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereMessagesAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereMessagesAttempted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereSubscriberUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereTypeUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereUpdatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription whereWorkflowUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Subscription query()
 * @property-read \App\Workflow|null $workflow
 */
class Subscription extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'subscriptions';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return string
     */
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
}
