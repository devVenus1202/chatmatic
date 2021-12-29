<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Log;

/**
 * App\WorkflowStep
 *
 * @property int $uid
 * @property string $name
 * @property int $probability
 * @property int $next_step_uid
 * @property int $workfow_step_uid
 * @property-read \App\WorkflowStep $workflowNextStep
 * @property-read \App\WorkflowStep $workflowStep
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepOptionRandom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepOptionRandom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepOptionRandom query()
 * @mixin \Eloquent
 */

class WorkflowStepOptionRandom extends Model
{
    protected $table        = 'workflow_step_option_randomizations';
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
     * @param $request_step_random_data
     * @param $workflow_step
     * @param $temp_step_uid_map
     * @return mixed
     * @throws \Exception
     */
    public static function updateOrCreate($request_step_random_data, $workflow_step, $temp_step_uid_map)
    {
        /** @var \App\Workflow $workflow */
        /** @var \App\Page $page */
        /** @var \App\WorkflowStep $workflow_step */
        /** @var \App\WorkflowStepOptionDelay $workflow_step_option_random */

        $response['success']                = 0;
        $response['error']                  = 0;
        $response['error_msg']              = '';
        $response['workflow_step_items']    = null;

        $workflow                 = $workflow_step->workflow;
        $page                     = $workflow->page;
        $request_step             = $request_step_random_data;
        $actual_random_options    = $workflow_step->optionRandomizations()->get();

        // we have the step options from request
        if( ! $request_step['options'])
        {
            // We have not the needed data to store the delay
            $response['error'] = 1;
            $response['error_msg'] = 'Ranoom options not found '.$request_step['name'].'.';

            return $response;
        }
        // Delay options will be used through this code
        $randomizer_list = $request_step['options'];
        
        // Let's validate at least we have two options
        if( count($randomizer_list) < 2 )
        {
            // The request has only a single option or less
            $response['error'] = 1;
            $response['error_msg'] = 'You must add more than a single randon option to the step '.$request_step['name'].'.';

            return $response;
        }

        // Let's iterate over random option
        foreach ($randomizer_list as $rand_option)
        {
            // Now let's validate we have the needed data per option
            if( ! $rand_option['option'])
            {
                // We have no an option name for this step
                $response['error'] = 1;
                $response['error_msg'] = 'There is no name for an option on the randomization step '.$request_step['name'].'.';

                return $response;
            }

            // Let's validate we have a percentaje option
            if( ! $rand_option['percentage'])
            {
                // We have no percentaje value for an option
                $response['error'] = 1;
                $response['error_msg'] = 'There is not a value for an option on the randomization step '.$request_step['name'].'.';

                return $response;
            }

            // Let's check the percentaje value is an int
            if( ! is_int($rand_option['percentage']) )
            {
                // We have an non valid data type, we only can accept int values
                $response['error'] = 1;
                $response['error_msg'] = 'Now allowed data type for percentaje on randomization step '.$request_step['name'].'.';

                return $response;
            }

            // Now let's validate the percentaje value is within the valid values
            if( $rand_option['percentage'] < 1 || $rand_option['percentage'] > 99)
            {
                // We have no valid values for a random option
                $response['error'] = 1;
                $response['error_msg'] = 'You have added no valid values for percentajes on the randomization step '.$request_step['name'].'. values must be between 1 and 99';

                return $response;
            }

            // Now let's check if this is an existing option or is a new one
            // Previous options stored have ids, new ones still not
            if ( ! isset($rand_option['uid']) )
            {
                // If we have not an uid for each option, then it's a new one

               // Finally validate we have a valid next step  
                if( ! $temp_step_uid_map[$rand_option['next_step_uid']])
                {
                    // We have not a next step
                    $response['error'] = 1;
                    $response['error_msg'] = 'We have not a valid next_step associated for the delay option';

                    return $response;
                }

                // Everything seeems to be ok, let's save the data

                $randomization_option                               = new self;
                $randomization_option->name                         = $rand_option['option'];
                $randomization_option->probability                  = $rand_option['percentage'];
                $randomization_option->next_step_uid                = $temp_step_uid_map[$rand_option['next_step_uid']];
                $randomization_option->workflow_step_uid            = $workflow_step->uid;

                $randomization_option_saved = $randomization_option->save();

                if( ! $randomization_option_saved)
                {
                    // A randomization option was not saved
                    $response['error'] = 1;
                    $response['error_msg'] = 'Error saving a randomizatio option on step '.$request_step['name'].'.';

                    return $response;
                }

            }
            else
            {

                // find this out on database
                $random_option = $workflow_step->optionRandomizations()->where('uid', $rand_option['uid'])->first();

                // Throw an error if we didn't get a random option
                if( ! $random_option)
                {
                    // workflow step item not found
                    $response['error']      = 1;
                    $response['error_msg']  = 'Workflow step random option not found ('.$random_option['uid'].')';

                    return $response;
                }

                // Finally let's validate we have a valid next step
                if( ! $temp_step_uid_map[$rand_option['next_step_uid']])
                {
                    // We have not a next step
                    $response['error'] = 1;
                    $response['error_msg'] = 'We have not a step associated for the random option';

                    return $response;
                }

                // Let's update the data
                $random_option->name                    = $rand_option['option'];
                $random_option->probability             = $rand_option['percentage'];
                $random_option->next_step_uid           = $temp_step_uid_map[$rand_option['next_step_uid']];

                $randomization_option_saved = $random_option->save();

                if( ! $randomization_option_saved)
                {
                    // A randomization option was not saved
                    $response['error'] = 1;
                    $response['error_msg'] = 'Error saving a randomizatio option on step '.$step['name'].'.';

                    return $response;
                }
            }
        }

        // Finally let's delete on db those random options that does not come from in the request
        foreach ($actual_random_options as $rand_option_database)
        {
            // if we can't find a database step on the request, then we
            // have to remove this from the database
            $exist = false;
            foreach ( $randomizer_list as $random_on_request)
            {
                // Ensuere only iterate over the non new ones
                $non_new_random_request = $random_on_request['uid'] ?? null;
                if ( isset($non_new_random_request) && $random_on_request['uid']  === $rand_option_database->uid )
                {
                    $exist = true;
                    break;
                }
            }

            if( ! $exist)
            {
                // Delete the option
                $rand_option_database->delete();
            }

        }
    }

    /**
     * Prepare this Workflow record object to be templated by removing unneeded values and setting
     * appropriate template values
     *
     * @param WorkflowStepOptionRandom $workflowStepOptionRandom
     * @param WorkflowTemplateStep $workflowTemplateStep
     * @return array
     */
    public static function prepareForTemplate(WorkflowStepOptionRandom $workflowStepOptionRandom, WorkflowTemplateStep $workflowTemplateStep)
    {
        $workflowTemplateStepOptionRandom = $workflowStepOptionRandom->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowTemplateStepOptionRandom['uid']);        
        unset($workflowTemplateStepOptionRandom['workflow_step_uid']);
        unset($workflowTemplateStepOptionRandom['next_step_uid']);

        // Set the stuff we need
        $workflowTemplateStepOptionRandom['workflow_template_step_uid']           = $workflowTemplateStep->uid;

        // Return the array
        return $workflowTemplateStepOptionRandom;
    }

}

