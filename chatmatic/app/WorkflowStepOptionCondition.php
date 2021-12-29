<?php

namespace App;
use App\WorkflowStepItemMap;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowStep
 *
 * @property int $uid
 * @property string $name
 * @property string $conditions_json
 * @property int $next_step_uid
 * @property int $workfow_step_uid
 * @property-read \App\WorkflowStep $workflowNextStep
 * @property-read \App\WorkflowStep $workflowStep
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepOptionCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepOptionCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepOptionCondition query()
 * @mixin \Eloquent
 */

class WorkflowStepOptionCondition extends Model
{
    protected $table        = 'workflow_step_option_conditions';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

    public $timestamps      = false;


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
    public function workflowNextStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'next_step_uid', 'uid');
    }

    /**
     * @param $request_step_condition_data
     * @param $workflow_step
     * @param $temp_step_uid_map
     * @return mixed
     * @throws \Exception
     */
    public static function updateOrCreate($request_step_conditional_data, $workflow_step, $temp_step_uid_map, $temp_quick_reply_uid_map, $temp_button_uid_map)
    {
        /** @var \App\Workflow $workflow */
        /** @var \App\Page $page */
        /** @var \App\WorkflowStep $workflow_step */
        /** @var \App\WorkflowStepOptionDelay $workflow_step_option_condition */

        $response['success']                = 0;
        $response['error']                  = 0;
        $response['error_msg']              = '';
        $response['workflow_step_items']    = null;

        $workflow                       = $workflow_step->workflow;
        $page                           = $workflow->page;
        $request_step                   = $request_step_conditional_data;
        $actual_conditional_options     = $workflow_step->optionConditions()->get();

        // we have the step options from request
        if( ! $request_step['options'])
        {
            // We have not the needed data to store the delay
            $response['error'] = 1;
            $response['error_msg'] = 'Ranoom options not found '.$request_step['name'].'.';

            return $response;
        }
        // Delay options will be used through this code
        $conditional_list = $request_step['options'];

        // Let's iterate over each option
        foreach($conditional_list as $conditional_option)
        {
            // Now let's validate we have the needed data per option
            if( ! $conditional_option['option'])
            {
                // We have no an option name for this step
                $response['error'] = 1;
                $response['error_msg'] = 'There is not a name for an option on the conditional step '.$request_step['name'];

                return $respnse;
            }

            // let's validate we have options for this conditional
            if( ! isset($conditional_option['conditions']) && $conditional_option['option'] != 'None match' )
            {
                // We have no option for this step
                $response['error'] = 1;
                $response['error_msg'] = 'There is not an option for a conditional step on '.$request_step['name'];

                return $response;
            }

            // Validate we have a value for the match field
            if ( ! $conditional_option['match'] && $conditional_option['option'] != 'None match' )
            {
                 // workflow step item type doesn't match allowed types
                $response['error']      = 1;
                $response['error_msg']  = 'Match condition not provided ('.$request_step['name'].')';

                return $response;
            }

            $conditional_option_match = $conditional_option['match'];

            // Validate we have an allowed match
            $allowed_conditional_match = ['if_any','if_all'];
            if( ! in_array($conditional_option_match, $allowed_conditional_match))
            {
                // workflow step item type doesn't match allowed types
                $response['error']      = 1;
                $response['error_msg']  = 'Conditional option match not available in ('.$allowed_conditional_match.')';

                return $response;
            }


            // if this is no match let's udpate the values
            if($conditional_option['option'] === 'None match')
            {
                $conditional_option_match = "";
            }

            // Now let's check if this is an existing option or is a new one
            // Previous options stored have ids, new ones still not
            if ( ! isset($conditional_option['uid']) )
            {
                // Validate we have a valid next step
                if( ! $temp_step_uid_map[$conditional_option['next_step_uid']])
                {
                    // We have not a next step
                    $response['error'] = 1;
                    $response['error_msg'] = 'We have not a valid next_step associated for the conditional step';


                    return $response;
                }   

                $json_options = $conditional_option['conditions'] ?? null;
                // Let's check if we have a button or quick reply associated condition
                if( isset($json_options))
                {
                    if( array_key_exists('user_clicked_button', $json_options) )
                    {
                        // Let's update the value string id for the already saved quick rep uid
                        foreach ($json_options['user_clicked_button'] as $key => $option) 
                        {
                            $json_options['user_clicked_button'][$key] = $temp_button_uid_map[$option];  
                        }
                    }
                    // Let's check if we have a button or quick reply associated condition
                    if( array_key_exists('user_clicked_quick_reply', $json_options) )
                    {
                        // Let's update the value string id for the already saved quick rep uid
                        foreach ($json_options['user_clicked_quick_reply'] as $key => $option) 
                        {
                            $json_options['user_clicked_quick_reply'][$key] = $temp_quick_reply_uid_map[$option];  
                        }
                    }    
                }
                
                // Everything seems to be ok, let's save the data
                
                $json_options = json_encode($json_options);

                $cond_option                                = new WorkflowStepOptionCondition;
                $cond_option->name                          = $conditional_option['option'];
                $cond_option->conditions_json               = $json_options;
                $cond_option->match                         = $conditional_option_match;
                $cond_option->next_step_uid                 = $temp_step_uid_map[$conditional_option['next_step_uid']];
                $cond_option->workflow_step_uid             = $workflow_step->uid;

                $conditional_option_saved = $cond_option->save();

                if( ! $conditional_option_saved)
                {
                    // A conditional option was not saved
                    $response['error'] = 1;
                    $response['error_msg'] = 'Error saving a conditional on step '.$request_step['name'];

                    return $response;
                }
            }
            else
            {
                // This is just an existing option on database

                // find this out on database
                $cond_option = $workflow_step->optionConditions()->where('uid', $conditional_option['uid'])->first();

                // Throw an error if we didn't get a condition option
                if( ! $cond_option)
                {
                    // workflow step item not found
                    $response['error']      = 1;
                    $response['error_msg']  = 'Workflow step condition option not found ('.$conditional_option['uid'].')';

                    return $response;
                }

                // Finally let's validate we have a valid next step
                if( ! $temp_step_uid_map[$conditional_option['next_step_uid']])
                {
                    // We have not a next step
                    $response['error'] = 1;
                    $response['error_msg'] = 'We have not a step associated for the conditional option';

                    return $response;
                }

                $json_options = $conditional_option['conditions'] ?? null;
                // Let's check if we have a button or quick reply associated condition
                if( isset($json_options))
                {
                    if( array_key_exists('user_clicked_button', $json_options) )
                    {
                        // Let's update the value string id for the already saved button uid
                        foreach ($json_options['user_clicked_button'] as $key => $option) 
                        {
                            // new buttons comes from and string uid, already has an integer udi
                            if ( is_integer($json_options['user_clicked_button'][$key]) )
                            {
                                // Find out if the button already exist
                                $button = $workflow_step->workflow()->first()->buttons()->where('uid',$json_options['user_clicked_button'][$key])->first();

                                if ( isset($button) )
                                {
                                    $json_options['user_clicked_button'][$key] = $button->uid;    
                                }
                                else
                                {
                                    // If a button does not belong to this workflow the condition
                                    // will be deleted
                                    unset($json_options['user_clicked_button'][$key]);

                                    //$response['error']          = 1;
                                    //$response['error_msg']      = 'The button with uid '.$json_options['button_clicked'][$key].' does not belong to this workflow';

                                    //return $response;
                                }
                            }
                            else
                            {
                                // New button
                                $json_options['user_clicked_button'][$key] = $temp_button_uid_map[$option];
                            }
        
                        }
                    }
                    // Let's check if we have a button or quick reply associated condition
                    if( array_key_exists('user_clicked_quick_reply', $json_options) )
                    {
                        // Let's update the value string id for the already saved quick rep uid
                        foreach ($json_options['user_clicked_quick_reply'] as $key => $option) 
                        {
                            if ( is_integer($json_options['user_clicked_quick_reply'][$key]) )
                            {
                                // Find out if the quick reply already exist
                                $quick_rep = $workflow_step->workflow()->first()->workflowQuickReplies()->where('uid', $json_options['user_clicked_quick_reply'][$key])->first();

                                if ( isset($quick_rep) )
                                {
                                    $json_options['user_clicked_quick_reply'][$key] = $quick_rep->uid;
                                }
                                else
                                {
                                    unset($json_options['user_clicked_quick_reply'][$key]);
                                }

                            }
                            else
                            {
                                // New quick reply
                                $json_options['user_clicked_quick_reply'][$key] = $temp_quick_reply_uid_map[$option];
                            }
                            
                        }
                    }    
                }

                $json_options = json_encode($json_options);

                $cond_option->name                          = $conditional_option['option'];
                $cond_option->conditions_json               = $json_options;
                $cond_option->match                         = $conditional_option_match;
                $cond_option->next_step_uid                 = $temp_step_uid_map[$conditional_option['next_step_uid']];

                $conditional_option_saved = $cond_option->save();

                if( ! $conditional_option_saved)
                {
                    // A conditional option was not saved
                    $response['error'] = 1;
                    $response['error_msg'] = 'Error saving a conditional on step '.$request_step['name'];

                    return $response;
                }

            }
            
        }

        // Finally let's delete on db those conditional options that does not come from in the request
        foreach ($actual_conditional_options as $conditional_option_database)
        {
            // if we can't find a database step on the request, then we
            // have to remove this from the database
            $exist = false;
            foreach ( $conditional_list as $conditional_on_request)
            {
                // Ensuere only iterate over the non new ones
                $non_new_request_option = $conditional_on_request['uid'] ?? null;
                if ( isset($non_new_request_option) && $conditional_on_request['uid']  == $conditional_option_database->uid )
                {   
                    $exist = true;
                    break;
                }
            }

            if( ! $exist)
            {
                // Delete the option
                $conditional_option_database->delete();
            }

        }
    }

    /**
     * Prepare this Workflow record object to be templated by removing unneeded values and setting
     * appropriate template values
     *
     * @param WorkflowStepOptionCondition $workflowStepOptionCondition
     * @param WorkflowTemplateStep $workflowTemplateStep
     * @return array
     */
    public static function prepareForTemplate(WorkflowStepOptionCondition $workflowStepOptionCondition, 
                                              WorkflowTemplateStep $workflowTemplateStep, 
                                              $tags_mapping, $buttons_mapping, $quick_rep_mapping)
    {
        $workflowTemplateStepOptionCondition = $workflowStepOptionCondition->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowTemplateStepOptionCondition['uid']);        
        unset($workflowTemplateStepOptionCondition['workflow_step_uid']);
        unset($workflowTemplateStepOptionCondition['next_step_uid']);

        // Set the stuff we need
        $workflowTemplateStepOptionCondition['workflow_template_step_uid']           = $workflowTemplateStep->uid;


        // Templates should remove associated tags and subscriptions
        $json_conditions = $workflowTemplateStepOptionCondition['conditions_json'];
        $json_conditions = json_decode($json_conditions, true);

        // Fisrt we need to update the conditionals for 
        // Tags and clicked on buttons/quick reply
        // Then unset conditionals associated with subscriptions

        // extract has_tag and does_not_have_tag
        if (isset($json_conditions['has_tag']))
        {
            $tags       = $json_conditions['has_tag'];
            foreach ($tags as $key => $tag) 
            {
                $mapped_tag = false;
                foreach ($tags_mapping as $tag_map) 
                {
                    if($tag_map['tag_uid'] == $tag)
                    {
                        // Replace the current value for the mapped one
                        $json_conditions['has_tag'][$key] = $tag_map['template_tag_uid'];
                        $mapped_tag = true;
                    }   
                }
                if($mapped_tag == false)
                {
                    // this means the tag wasn't set up for this workflow
                    // then it's necessary to remove
                    unset($json_conditions['has_tag'][$key]);
                }
            }
        }

        if (isset($json_conditions['does_not_have_tag']))
        {
            $no_tags    = $json_conditions['does_not_have_tag'];
            foreach ($no_tags as $key => $tag) 
            {
                $mapped_tag = false;
                foreach ($tags_mapping as $tag_map) 
                {

                    if($tag_map['tag_uid'] == $tag)
                    {
                        // Replace the current value for the mapped one
                        $json_conditions['does_not_have_tag'][$key] = $tag_map['template_tag_uid'];
                        $mapped_tag = true;        
                    }
                }
                if($mapped_tag == false)
                {
                    // this means the tag wasn't set up un this workflow
                    // then it's necessary to remove
                    unset($json_conditions['does_not_have_tag'][$key]);
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

        // Let's remove any subscribed to associated
        unset($json_conditions['user_subscribed_to']);
        unset($json_conditions['user_not_subscribed_to']);

        $workflowTemplateStepOptionCondition['conditions_json'] = json_encode($json_conditions);

        // Return the array
        return $workflowTemplateStepOptionCondition;
    }
}
