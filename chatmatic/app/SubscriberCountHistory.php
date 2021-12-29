<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\SubscriberCountHistory
 *
 * @property int $uid
 * @property int $page_uid
 * @property string $date_utc
 * @property int $minimum
 * @property int $maximum
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property string $updated_at_utc
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory whereDateUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory whereMaximum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory whereMinimum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberCountHistory whereUpdatedAtUtc($value)
 * @mixin \Eloquent
 */
class SubscriberCountHistory extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'subscriber_count_history';
    protected $primaryKey   = 'uid';

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
}
