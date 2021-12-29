<?php

namespace App;

use App\Traits\WorkflowStepItemMediaTrait;
use Illuminate\Database\Eloquent\Model;



/**
 * App\WorkflowStepItemAudio
 *
 * @property-read \App\Page $page
 * @property-read \App\Workflow $workflow
 * @property-read \App\WorkflowStep $workflowStep
 * @property-read \App\WorkflowStepItem $workflowStepItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio query()
 * @mixin \Eloquent
 * @property int $uid
 * @property int $workflow_step_item_uid
 * @property string $audio_url
 * @property int $page_uid
 * @property int $workflow_uid
 * @property int $wordflow_step_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio whereAudioUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio whereWordflowStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio whereWorkflowStepItemUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio whereWorkflowUid($value)
 * @property string|null $reference_id
 * @property int $workflow_step_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowStepItemAudio whereWorkflowStepUid($value)
 */
class WorkflowStepItemAudio extends Model
{
    use WorkflowStepItemMediaTrait;

    protected $table        = 'workflow_step_item_audio';
    protected $primaryKey   = 'uid';

    public $timestamps      = false;

    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStepItem()
    {
        return $this->belongsTo(WorkflowStepItem::class, 'workflow_step_item_uid', 'uid');
    }
}
