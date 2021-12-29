<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowTemplateStepOptionDelay
 *
 * @property int $uid
 * @property string $text_message
 * @property string $phone_number_to
 * @property int $workfow_template_step_uid
 * @property-read \App\WorkflowTemplateStep $workflowTemplateStep
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepSms newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepSms newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepSms query()
 * @mixin \Eloquent
 */
class WorkflowTemplateStepSms extends Model
{
    protected $table        = 'workflow_template_step_sms';
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
     * @param WorkflowTemplateStepSms $workflowTemplateStepSms
     * @param Workflow $workflow
     * @param WorkflowStep $workflowStep
     * @return array
     */
    public static function prepareForTemplate(WorkflowTemplateStepSms $workflowTemplateStepSms, WorkflowStep $workflowStep)
    {
        $workflowStepOptionSms = $workflowTemplateStepSms->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowStepOptionSms['uid']);
        unset($workflowStepOptionSms['workflow_template_step_uid']);

        // Set the stuff we need
        $workflowStepOptionSms['workflow_step_uid']  = $workflowStep->uid;

        // Return the array
        return $workflowStepOptionSms;
    }
}
