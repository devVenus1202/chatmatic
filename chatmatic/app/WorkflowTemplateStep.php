<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowTemplateStep
 *
 * @property int $uid
 * @property int $workflow_template_uid
 * @property string $step_type
 * @property string $step_type_parameters
 * @property string $name
 * @property-read \App\WorkflowTemplate $workflowTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepItem[] $workflowTemplateStepItems
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStep whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStep whereStepType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStep whereStepTypeParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStep whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStep whereWorkflowTemplateUid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\WorkflowTemplateStep query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepQuickReply[] $workflowTemplateStepQuickReplies
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepOptionCondition[] $optionConditions
 * @property-read int|null $option_conditions_count
 * @property-read \App\WorkflowTemplateStepOptionDelay|null $optionDelay
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\WorkflowTemplateStepOptionRandom[] $optionRandomizations
 * @property-read int|null $option_randomizations_count
 * @property-read \App\WorkflowTemplateStepSms|null $optionSms
 * @property-read int|null $workflow_template_step_items_count
 * @property-read int|null $workflow_template_step_quick_replies_count
 */
class WorkflowTemplateStep extends Model
{
    protected $table        = 'workflow_template_steps';
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateStepItems()
    {
        return $this->hasMany(WorkflowTemplateStepItem::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workflowTemplateStepQuickReplies()
    {
        return $this->hasMany(WorkflowTemplateStepQuickReply::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function optionDelay()
    {
        return $this->hasOne(WorkflowTemplateStepOptionDelay::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function optionRandomizations()
    {
        return $this->hasMany(WorkflowTemplateStepOptionRandom::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function optionConditions()
    {
        return $this->hasMany(WorkflowTemplateStepOptionCondition::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function optionSms()
    {
        return $this->hasOne(WorkflowTemplateStepSms::class, 'workflow_template_step_uid', 'uid');
    }

    /**
     * @param WorkflowTemplateStep $workflowTemplateStep
     * @param Workflow $workflow
     * @return array
     */
    public static function prepareForWorkflowStep(WorkflowTemplateStep $workflowTemplateStep, Workflow $workflow)
    {
        $workflowStep = $workflowTemplateStep->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowStep['uid']);
        unset($workflowStep['workflow_template_uid']);

        // Set the stuff we need
        $workflowStep['workflow_uid']   = $workflow->uid;
        $workflowStep['page_uid']       = $workflow->page->uid;

        // Return the array
        return $workflowStep;
    }
}
