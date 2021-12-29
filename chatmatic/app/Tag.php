<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Tag
 *
 * @property int $uid
 * @property int $pages_uid
 * @property string $keyword
 * @property-read \App\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tag whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tag wherePagesUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tag whereUid($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\QuickReply[] $quickReplies
 * @property string $value
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tag whereValue($value)
 * @property bool $archived
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tag whereArchived($value)
 * @property int $page_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Tag wherePageUid($value)
 * @property-read int|null $quick_replies_count
 */
class Tag extends Model
{
    public $timestamps      = false;

    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'tags';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function quickReplies()
    {
        return $this->morphedByMany(QuickReply::class, 'taggable');
    }
}
