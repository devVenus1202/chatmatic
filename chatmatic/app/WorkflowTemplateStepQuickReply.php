<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\WorkflowTemplateStepQuickReply
 *
 * @property int $uid
 * @property int $workflow_template_uid
 * @property int $workflow_template_step_uid
 * @property string $type
 * @property int $map_order
 * @property string $map_text
 * @property string $map_action
 * @property string|null $map_action_text
 * @property-read \App\WorkflowTemplate $workflowTemplate
 * @property-read \App\WorkflowTemplateStep $workflowTemplateStep
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply whereMapAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply whereMapActionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply whereMapOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply whereMapText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply whereWorkflowTemplateStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStepQuickReply whereWorkflowTemplateUid($value)
 * @mixin \Eloquent
 * @property-read \App\CustomFieldTemplate $customField
 */
class WorkflowTemplateStepQuickReply extends Model
{
    public $timestamps      = false;

    protected $table        = 'workflow_template_step_quick_replies';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplate() : BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTemplateStep() : BelongsTo
    {
        return $this->belongsTo(WorkflowTemplateStep::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customField()
    {
        return $this->belongsTo(CustomFieldTemplate::class, 'custom_field_template_uid', 'uid');
    }

    /**
     * @param WorkflowTemplateStepQuickReply $workflowTemplateStepQuickReply
     * @param Workflow $workflow
     * @param WorkflowStep $workflowStep
     * @return array
     */
    public static function prepareForWorkflow(WorkflowTemplateStepQuickReply $workflowTemplateStepQuickReply, Workflow $workflow, WorkflowStep $workflowStep)
    {
        $workflowStepQuickReply = $workflowTemplateStepQuickReply->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowStepQuickReply['uid'],
            $workflowStepQuickReply['workflow_template_uid'],
            $workflowStepQuickReply['workflow_template_step_uid'],
            $workflowStepQuickReply['custom_field_template_uid']
        );

        // Set the stuff we need
        $workflowStepQuickReply['page_uid']             = $workflow->page->uid;
        $workflowStepQuickReply['workflow_uid']         = $workflow->uid;
        $workflowStepQuickReply['workflow_step_uid']    = $workflowStep->uid;

        // Return the array
        return $workflowStepQuickReply;
    }
}
