<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\SubscriberChatHistory
 *
 * @property int $uid
 * @property int $is_incoming
 * @property string $message
 * @property \Carbon\Carbon $created_at_utc
 * @property int $sequence
 * @property string $fb_message_id
 * @property string $attachments
 * @property int $subscriber_uid
 * @property int|null $keywordmsg_autoresponse_workflow_uid
 * @property string|null $keywordmsg_autoresponse_match
 * @property int $page_uid
 * @property-read \App\Page $page
 * @property-read \App\Subscriber $subscriber
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereFbMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereIsIncoming($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereKeywordmsgAutoresponseMatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereKeywordmsgAutoresponseWorkflowUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereSubscriberUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory whereUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberChatHistory query()
 */
class SubscriberChatHistory extends Model
{
    const CREATED_AT        = 'created_at_utc';

    protected $table        = 'subscriber_chat_history';
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
    public function subscriber()
    {
        return $this->belongsTo(Subscriber::class, 'subscriber_uid', 'uid');
    }
}
