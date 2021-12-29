<?php

namespace App;

use App\Traits\WorkflowTemplateStepItemMediaTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WorkflowTemplateStepItemVideo
 *
 * @package App
 * @property-read \App\WorkflowTemplate $workflowTemplate
 * @property-read \App\WorkflowTemplateStepItem $workflowTemplateStepItem
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemVideo query()
 * @property int $uid
 * @property int $workflow_template_uid
 * @property int $workflow_template_step_item_uid
 * @property string $video_url
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemVideo whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemVideo whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemVideo whereWorkflowTemplateStepItemUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemVideo whereWorkflowTemplateUid($value)
 */
class WorkflowTemplateStepItemVideo extends Model
{
    use WorkflowTemplateStepItemMediaTrait;

    protected $table        = 'workflow_template_step_item_videos';
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
    public function workflowTemplateStepItem()
    {
        return $this->belongsTo(WorkflowTemplateStepItem::class, 'workflow_template_step_item_uid', 'uid');
    }
}
