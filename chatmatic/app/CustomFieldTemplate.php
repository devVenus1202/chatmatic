<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CustomField
 *
 * @property int $uid
 * @property string $field_name
 * @property string $validation_type
 * @property int $page_uid
 * @property string $tag
 * @property string $tag_type
 * @property-read \App\WorkflowTemplate $workflowTemplate
 * @property-read \App\WorkflowTemplate $workflowTemplat
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldTemplate query()
 * @mixin \Eloquent
 */

class CustomFieldTemplate extends Model
{
    protected $table        = 'custom_field_templates';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    public $timestamps      = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplat()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'template_uid', 'uid');
    }

}
