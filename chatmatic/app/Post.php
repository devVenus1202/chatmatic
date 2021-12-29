<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Post
 *
 * @property int $uid
 * @property int $page_uid
 * @property string $facebook_post_id
 * @property string $message
 * @property int $comments
 * @property string $facebook_created_time_utc
 * @property \Carbon\Carbon $created_at_utc
 * @property \Carbon\Carbon $updated_at_utc
 * @property string|null $picture
 * @property string $post_type
 * @property string|null $permalink_url
 * @property string|null $facebook_post_object_id
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereCreatedAtUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereFacebookCreatedTimeUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereFacebookPostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereFacebookPostObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post wherePermalinkUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post wherePostType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post whereUpdatedAtUtc($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Post query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Trigger[] $triggers
 * @property-read int|null $comments_count
 * @property-read int|null $triggers_count
 */
class Post extends Model
{
    public $timestamps = false;
    //const CREATED_AT        = 'created_at_utc';
    //const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'posts';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /*
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function triggers()
    {
        return $this->hasMany(Trigger::class, 'post_uid', 'uid');
    }
}
