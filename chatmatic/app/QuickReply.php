<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * App\QuickReply
 *
 * @property int $uid
 * @property int $page_uid
 * @property int $workflow_uid
 * @property int $workflow_step_uid
 * @property int|null $automation_uid
 * @property string $type
 * @property int $map_order
 * @property string $map_text
 * @property string $map_action
 * @property string|null $map_action_text
 * @property-read \App\Automation $automation
 * @property-read \App\Page $page
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Tag[] $tags
 * @property-read \App\Workflow $workflow
 * @property-read \App\WorkflowStep $workflowStep
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereAutomationUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereMapAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereMapActionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereMapOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereMapText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply wherePageUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereWorkflowStepUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereWorkflowUid($value)
 * @mixin \Eloquent
 * @property int $clicks
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereClicks($value)
 * @property int|null $custom_field_uid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereCustomFieldUid($value)
 * @property string|null $custom_field_value
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickReply whereCustomFieldValue($value)
 * @property-read \App\CustomField|null $customField
 * @property-read int|null $tags_count
 */
class QuickReply extends Model
{
    public $timestamps      = false;

    protected $table        = 'workflow_quick_replies';
    protected $primaryKey   = 'uid';

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
        return $this->belongsTo(Workflow::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function automation()
    {
        return $this->belongsTo(Automation::class, 'page_uid', 'uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_uid');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customField()
    {
        return $this->belongsTo(CustomField::class, 'custom_field_uid', 'uid');
    }

    /**
     * Prepare this QuickReply record object to be templated by removing unneeded values and setting
     * appropriate template values
     *
     * @param QuickReply $quickReply
     * @param WorkflowTemplate $workflowTemplate
     * @return array
     */
    public static function prepareForTemplate(QuickReply $quickReply, WorkflowTemplate $workflowTemplate, WorkflowTemplateStep $workflowTemplateStep)
    {
        $workflowTemplateStepQuickReply = $quickReply->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowTemplateStepQuickReply['uid'],
            $workflowTemplateStepQuickReply['page_uid'],
            $workflowTemplateStepQuickReply['workflow_uid'],
            $workflowTemplateStepQuickReply['workflow_step_uid'],
            $workflowTemplateStepQuickReply['automation_uid'],
            $workflowTemplateStepQuickReply['clicks'],
            $workflowTemplateStepQuickReply['custom_field_uid'],
            $workflowTemplateStepQuickReply['custom_field_value']
        );

        // Set the stuff we need
        $workflowTemplateStepQuickReply['workflow_template_uid']        = $workflowTemplate->uid;
        $workflowTemplateStepQuickReply['workflow_template_step_uid']   = $workflowTemplateStep->uid;

        // Return the array
        return $workflowTemplateStepQuickReply;
    }
}
