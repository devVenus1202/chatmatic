<?php

namespace App;

use App\Traits\WorkflowTemplateStepItemMediaTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WorkflowTemplateStepItemAudio
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
 * @property string $audio_url
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemAudio whereAudioUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemAudio whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemAudio whereWorkflowTemplateStepItemUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepItemAudio whereWorkflowTemplateUid($value)
 */
class WorkflowTemplateStepItemAudio extends Model
{
    use WorkflowTemplateStepItemMediaTrait;

    protected $table        = 'workflow_template_step_item_audio';
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