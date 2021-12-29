<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\SubscriberNote
 *
 * @property int $uid
 * @property int $subscriber_uid
 * @property string $note
 * @property \Illuminate\Support\Carbon $created_at_utc
 * @property int $page_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberNote whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberNote wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberNote whereSubscriberUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SubscriberNote whereUid($value)
 * @mixin \Eloquent
 */
class SubscriberNote extends Model
{
    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = false;

    protected $table        = 'subscriber_notes';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];
}
