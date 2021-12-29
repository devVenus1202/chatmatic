<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Taggable
 *
 * @property-read \App\TagTemplate $tag
 * @method static \Illuminate\Database\Eloquent\Builder|TaggableTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaggableTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaggableTemplate query()
 * @mixin \Eloquent
 */
class TaggableTemplate extends Model
{
    public $timestamps      = false;

    protected $table        = 'taggable_templates';
    protected $primaryKey   = null;
    public    $incrementing = false;

    protected $fillable = ['tag_template_uid','taggable_template_uid','taggable_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tag()
    {
        return $this->belongsTo(TagTemplate::class, 'tag_template_uid', 'uid');
    }

}
