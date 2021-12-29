<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Comment
 *
 * @property int $uid
 * @property int $post_uid
 * @property string $facebook_comment_id
 * @property string $facebook_sender_id
 * @property string $facebook_sender_name
 * @property string $message
 * @property int $response_trigger_uid
 * @property string $response
 * @property string|null $facebook_response_message_id
 * @property \Carbon\Carbon $created_at_utc
 * @property int $page_uid
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereFacebookCommentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereFacebookResponseMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereFacebookSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereFacebookSenderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment wherePostUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereResponseTriggerUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment whereUid($value)
 * @mixin \Eloquent
 * @property-read \App\Post $post
 * @property-read \App\Trigger $trigger
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Comment query()
 */
class Comment extends Model
{
    const CREATED_AT        = 'created_at_utc';

    protected $table        = 'comments';
    protected $primaryKey   = 'uid';

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
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trigger()
    {
        return $this->belongsTo(Trigger::class, 'response_trigger_uid', 'uid');
    }
}
