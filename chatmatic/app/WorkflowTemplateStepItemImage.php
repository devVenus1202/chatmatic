<?php

namespace App;

use App\Traits\WorkflowTemplateStepItemMediaTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowTemplateStepItemImage
 *
 * @property int $uid
 * @property int $workflow_template_step_item_uid
 * @property int $image_order
 * @property string $image_url
 * @property string $redirect_url
 * @property string|null $image_title
 * @property string|null $image_subtitle
 * @property int $workflow_template_uid
 * @property-read \App\WorkflowTemplate $workflowTemplate
 * @property-read \App\WorkflowTemplateStep $workflowTemplateStep
 * @property-read \App\WorkflowTemplateStepItem $workflowTemplateStepItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage whereImageOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage whereImageSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage whereImageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage whereRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage whereWorkflowTemplateStepItemUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage whereWorkflowTemplateUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemImage query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepItemMap[] $workflowTemplateStepItemMaps
 * @property-read int|null $workflow_template_step_item_maps_count
 */
class WorkflowTemplateStepItemImage extends Model
{
    use WorkflowTemplateStepItemMediaTrait;

    protected $table        = 'workflow_template_step_item_images';
    protected $primaryKey   = 'uid';
    protected $guarded      = ['uid'];

    public $timestamps      = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplate()
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplateStep()
    {
        return $this->belongsTo(WorkflowTemplateStep::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplateStepItem()
    {
        return $this->belongsTo(WorkflowTemplateStepItem::class, 'workflow_template_step_item_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateStepItemMaps()
    {
        return $this->hasMany(WorkflowTemplateStepItemMap::class, 'workflow_template_step_item_image_uid', 'uid');
    }
}
