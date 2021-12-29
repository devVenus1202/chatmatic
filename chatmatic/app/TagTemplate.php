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
 * @property-read \App\WorkflowTemplate $template
 * @method static \Illuminate\Database\Eloquent\Builder|TagTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TagTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TagTemplate query()
 * @mixin \Eloquent
 */
class TagTemplate extends Model
{
    public $timestamps      = false;

    const CREATED_AT        = 'created_at_utc';
    const UPDATED_AT        = 'updated_at_utc';

    protected $table        = 'tag_templates';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_uid', 'uid');
    }

}
