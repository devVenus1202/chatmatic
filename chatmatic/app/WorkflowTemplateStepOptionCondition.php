<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowTemplateStepOptionDelay
 *
 * @property int $uid
 * @property int $name
 * @property int $conditions_json
 * @property int $workflow_template_next_step_uid
 * @property int $workfow_template_step_uid
 * @property-read \App\WorkflowTemplateStep $workflowTemplateStep
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepOptionCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepOptionCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowTemplateStepOptionCondition query()
 * @mixin \Eloquent
 */
class WorkflowTemplateStepOptionCondition extends Model
{
    protected $table        = 'workflow_template_step_option_conditions';
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
     * @param WorkflowTemplateStepOptionCondition $workflowTemplateStepOptionCondition
     * @param Workflow $workflow
     * @param WorkflowStep $workflowStep
     * @return array
     */
    public static function prepareForTemplate(WorkflowTemplateStepOptionCondition $workflowTemplateStepOptionCondition, 
                                              WorkflowStep $workflowStep, 
                                              $tags_mapping, $buttons_mapping, $quick_rep_mapping)
    {
        $workflowStepOptionCondition = $workflowTemplateStepOptionCondition->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowStepOptionCondition['uid']);
        unset($workflowStepOptionCondition['workflow_template_step_uid']);
        unset($workflowStepOptionCondition['workflow_template_next_step_uid']);

        // Set the stuff we need
        $workflowStepOptionCondition['workflow_step_uid']  = $workflowStep->uid;

        
        $json_conditions = $workflowStepOptionCondition['conditions_json'];
        $json_conditions = json_decode($json_conditions, true);

        // extract has_tag and does_not_have_tag
        if (isset($json_conditions['has_tag']))
        {
            $tags       = $json_conditions['has_tag'];
            foreach ($tags as $key => $tag) 
            {
                foreach ($tags_mapping as $tag_map) 
                {
                    if($tag_map['template_tag_uid'] == $tag)
                    {
                        // Replace the current value for the mapped one
                        $json_conditions['has_tag'][$key] = $tag_map['tag_uid'];
                    }   
                }
            }
        }

        if (isset($json_conditions['does_not_have_tag']))
        {
            $no_tags    = $json_conditions['does_not_have_tag'];
            foreach ($no_tags as $key => $tag) 
            {
                foreach ($tags_mapping as $tag_map) 
                {

                    if($tag_map['template_tag_uid'] == $tag)
                    {
                        // Replace the current value for the mapped one
                        $json_conditions['does_not_have_tag'][$key] = $tag_map['tag_uid'];
                    }
                }
            }
        }

        // Let's set up the conditionals based on buttons and quick replies clicks
        if (isset($json_conditions['user_clicked_button']))
        {
            $buttons_clicked = $json_conditions['user_clicked_button'];
            foreach ($buttons_clicked as $key => $button_clicked)
            {
                if (isset($buttons_mapping[$button_clicked]))
                {
                    $json_conditions['user_clicked_button'][$key] = $buttons_mapping[$button_clicked];
                }
            }
        }

        if (isset($json_conditions['user_clicked_quick_reply']))
        {
            $q_reps_clicked = $json_conditions['user_clicked_quick_reply'];
            foreach ($q_reps_clicked as $key => $q_rep_clicked)
            {
                if (isset($quick_rep_mapping[$q_rep_clicked]))
                {
                    $json_conditions['user_clicked_quick_reply'][$key] = $quick_rep_mapping[$q_rep_clicked];
                }
            }
        }

        $workflowStepOptionCondition['conditions_json'] = json_encode($json_conditions);

        // Return the array
        return $workflowStepOptionCondition;
    }
}
