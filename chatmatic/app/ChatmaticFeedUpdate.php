<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Comment
 *
 * @property int $uid
 * @property int $conntent
 * @method static \Illuminate\Database\Eloquent\Builder|ChatmaticFeedUpdate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatmaticFeedUpdate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChatmaticFeedUpdate query()
 * @mixin \Eloquent
 */
class ChatmaticFeedUpdate extends Model
{

    protected $table        = 'chatmatic_feed_updates';
    protected $primaryKey   = 'uid';

    public $timestamps = false;

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.u';
    }

}
