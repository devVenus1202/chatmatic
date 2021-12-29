<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowTemplateStepOptionDelay
 *
 * @property int $uid
 * @property int $hours
 * @property int $minutes
 * @property int $workflow_template_next_step_uid
 * @property int $workfow_template_step_uid
 * @property-read \App\WorkflowTemplateStep $workflowTemplateStep
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepOptionDelay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepOptionDelay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepOptionDelay query()
 * @mixin \Eloquent
 */
class WorkflowTemplateStepOptionDelay extends Model
{
    protected $table        = 'workflow_template_step_option_delays';
    protected $primaryKey   = 'uid';
    protected $guarded      = ['uid'];

    public $timestamps      = false;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplateStep()
    {
        return $this->belongsTo(WorkflowTemplateStep::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @param WorkflowTemplateStepOptionDelay $workflowTemplateStepOptionDelay
     * @param Workflow $workflow
     * @param WorkflowStep $workflowStep
     * @return array
     */
    public static function prepareForStepOptionDelay(WorkflowTemplateStepOptionDelay $workflowTemplateStepOptionDelay, WorkflowStep $workflowStep)
    {
        $workflowStepOptionDelay = $workflowTemplateStepOptionDelay->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowStepOptionDelay['uid']);
        unset($workflowStepOptionDelay['workflow_template_step_uid']);
        unset($workflowStepOptionDelay['workflow_template_next_step_uid']);

        // Set the stuff we need
        $workflowStepOptionDelay['workflow_step_uid']  = $workflowStep->uid;

        // Return the array
        return $workflowStepOptionDelay;
    }
}
