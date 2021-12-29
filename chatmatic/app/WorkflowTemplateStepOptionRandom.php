<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowTemplateStepOptionDelay
 *
 * @property int $uid
 * @property int $hours
 * @property int $probability
 * @property int $workflow_template_next_step_uid
 * @property int $workfow_template_step_uid
 * @property-read \App\WorkflowTemplateStep $workflowTemplateStep
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepOptionRandom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepOptionRandom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepOptionRandom query()
 * @mixin \Eloquent
 */
class WorkflowTemplateStepOptionRandom extends Model
{
    protected $table        = 'workflow_template_step_option_randomizations';
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
     * @param WorkflowTemplateStepOptionRandom $workflowTemplateStepOptionRandom
     * @param Workflow $workflow
     * @param WorkflowStep $workflowStep
     * @return array
     */
    public static function prepareForTemplate(WorkflowTemplateStepOptionRandom $workflowTemplateStepOptionRandom, WorkflowStep $workflowStep)
    {
        $workflowStepOptionRandom = $workflowTemplateStepOptionRandom->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowStepOptionRandom['uid']);
        unset($workflowStepOptionRandom['workflow_template_step_uid']);
        unset($workflowStepOptionRandom['workflow_template_next_step_uid']);

        // Set the stuff we need
        $workflowStepOptionRandom['workflow_step_uid']  = $workflowStep->uid;

        // Return the array
        return $workflowStepOptionRandom;
    }
}
