<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Taggable
 *
 * @property int $tag_uid
 * @property int $taggable_uid
 * @property string $taggable_type
 * @property-read \App\Tag $tag
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable query()
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable whereTagUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable whereTaggableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taggable whereTaggableUid($value)
 * @mixin \Eloquent
 */
class Taggable extends Model
{
    public $timestamps      = false;

    protected $table        = 'taggables';
    protected $primaryKey   = null;
    public    $incrementing = false;

    protected $fillable = ['tag_uid','taggable_uid','taggable_type'];

    //protected $guarded      = ['taggable_uid','tag_uid','taggable_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_uid', 'uid');
    }

}
