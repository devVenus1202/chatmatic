<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Comment
 *
 * @property int $uid
 * @property int $conntent
 * @method static \Illuminate\Database\Eloquent\Builder|ChatmaticFeedTip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatmaticFeedTip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatmaticFeedTip query()
 * @mixin \Eloquent
 */
class ChatmaticFeedTip extends Model
{
    const CREATED_AT        = 'created_at_utc';

    protected $table        = 'chatmatic_feed_tips';
    protected $primaryKey   = 'uid';

    public $timestamps = false;

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }

}
