<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\WorkflowStep
 *
 * @property int $uid
 * @property int $hours
 * @property int $minutes
 * @property int $next_step_uid
 * @property int $workfow_step_uid
 * @property-read \App\WorkflowStep $workflowNextStep
 * @property-read \App\WorkflowStep $workflowStep
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepOptionDelay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepOptionDelay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepOptionDelay query()
 * @mixin \Eloquent
 */

class WorkflowStepOptionDelay extends Model
{
    protected $table        = 'workflow_step_option_delays';
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
     * @param $request_step_delay_data
     * @param $workflow_step
     * @param $temp_step_uid_map
     * @return mixed
     * @throws \Exception
     */
    public static function updateOrCreate($request_step_delay_data, $workflow_step, $temp_step_uid_map)
    {
        /** @var \App\Workflow $workflow */
        /** @var \App\Page $page */
        /** @var \App\WorkflowStep $workflow_step */
        /** @var \App\WorkflowStepOptionDelay $workflow_step_option_delay */

        $response['success']                = 0;
        $response['error']                  = 0;
        $response['error_msg']              = '';
        $response['workflow_step_items']    = null;

        $workflow               = $workflow_step->workflow;
        $page                   = $workflow->page;
        $request_step           = $request_step_delay_data;

        // we have the step options from request
        if( ! $request_step['options'])
        {
            // We have not the needed data to store the delay
            $response['error'] = 1;
            $response['error_msg'] = 'Delay options not found '.$request_step['step_udi'].'.';

            return $response;
        }
        // Delay options will be used through this code
        $delay_option = $request_step['options'];

        // This is a new step delay with all the data
        if (! $delay_option['type'])
        {
            // We have not the needed type
            $response['error'] = 1;
            $response['error_msg'] = 'Delay not contain any option type';

            return $response;   
        }

        $delay_option_type = $delay_option['type'];
        // Let's validate the allowed types
        $valid_option_types = ['remaining','until'];
        if( ! in_array($delay_option_type, $valid_option_types))
        {
            // workflow step item type doesn't match allowed types
            $response['error']      = 1;
            $response['error_msg']  = 'Delay option type mismatch on delay step ('.$request_step['name'].')';

            return $response;
        }

        // Let's check validation for remaining
        if ($delay_option_type == 'remaining')
        {
            if( (! $delay_option['time_unit'] && ! $delay_option['amount'])  )
            {
                // We have not the needed desired time
                $response['error'] = 1;
                $response['error_msg'] = 'Time unit and amount are needed';


                return $response;
            }
            $delay_time_unit = $delay_option['time_unit'];
            $delay_time_amount = $delay_option['amount'];

             // Let's validate the allowed time units
            $valid_time_units = ['minutes','hours','days'];
            if( ! in_array($delay_time_unit, $valid_time_units))
            {
                // workflow step item type doesn't match allowed types
                $response['error']      = 1;
                $response['error_msg']  = 'Delay option type mismatch ('.$delay_time_unit.')';


                return $response;
            }

            // Lets validate the amount is a valid number
            if (! is_int($delay_time_amount))
            {
                // workflow step item type doesn't match allowed types
                $response['error']      = 1;
                $response['error_msg']  = 'Amount must be an integer number';


                return $response;                        
            }

            // Lets validate the range amount
            if ($delay_time_unit == 'minutes' && $delay_time_amount < 0 || $delay_time_amount > 59 )
            {
                // workflow step item type doesn't match allowed types
                $response['error']      = 1;
                $response['error_msg']  = 'Minutes must be between 1 and 59';


                return $response;                                                
            }

            // Lets validate the range amount
            if ($delay_time_unit == 'hours' && $delay_time_amount < 0 || $delay_time_amount > 23 )
            {
                // workflow step item type doesn't match allowed types
                $response['error']      = 1;
                $response['error_msg']  = 'Hours must be between 1 and 23';


                return $response;                                                
            }

            // Lets validate the range amount
            if ($delay_time_unit == 'days' && $delay_time_amount < 0 || $delay_time_amount > 30 )
            {
                // workflow step item type doesn't match allowed types
                $response['error']      = 1;
                $response['error_msg']  = 'Days must be between 1 and 30';

                return $response;                                                
            }
        }

        $fire_at = $delay_option['fire_until'] ?? null;
        if ($fire_at)
        {
            $fire_at = Carbon::createFromTimestamp(strtotime($fire_at))->toDateTimeString();
        }

        // If there's a int uid in the request then it's an existing step
        // UPDATE
        if ( is_int($request_step['step_uid']))
        {
            $workflow_step_option_delay = $workflow_step->optionDelay()->first();

            // Throw an error if we didn't get a step option delay
            if( ! $workflow_step_option_delay)
            {
                // workflow step item not found
                $response['error']      = 1;
                $response['error_msg']  = 'Workflow step item not found ('.$request_step['step_uid'].')';

                return $response;
            }

            // Finally let's validate we have a valid next step
            if( ! $temp_step_uid_map[$request_step['options']['next_step_uid']])
            {
                // We have not a next step
                $response['error'] = 1;
                $response['error_msg'] = 'We have not a step associated for the delay option';

                return $response;
            }

            $workflow_step_option_delay->type                 = $delay_option['type'];
            $workflow_step_option_delay->time_unit            = $delay_option['time_unit'] ?? 0;
            $workflow_step_option_delay->amount               = $delay_option['amount'] ?? 0;
            $workflow_step_option_delay->fire_until           = $fire_at;
            $workflow_step_option_delay->next_step_uid        = $temp_step_uid_map[$request_step['options']['next_step_uid']];
            $workflow_step_option_delay->workflow_step_uid    = $workflow_step->uid;

            $option_delay_saved = $workflow_step_option_delay->save();

            if( ! $option_delay_saved)
            {
                // There was a problem saving the delay option
            $response['error'] = 1;
            $response['error_msg'] = 'Problem saving the delay option for the step '.$request_step['name'].'.';

            return $response;

            }


        }
        else
        {
            // Finally let's validate we have a valid next step
            // CREATE
            if( ! $temp_step_uid_map[$request_step['options']['next_step_uid']])
            {
                // We have not a next step
                $response['error'] = 1;
                $response['error_msg'] = 'We have not a step associated for the delay option';

                return $response;
            }

            // Populate the options delays
            $option_delay                       = new self;
            $option_delay->type                 = $delay_option['type'];
            $option_delay->time_unit            = $delay_option['time_unit'] ?? 0;
            $option_delay->amount               = $delay_option['amount'] ?? 0;
            $option_delay->fire_until           = $fire_at;
            $option_delay->next_step_uid        = $temp_step_uid_map[$request_step['options']['next_step_uid']];
            $option_delay->workflow_step_uid    = $workflow_step->uid;

            $option_delay_saved = $option_delay->save();

            if( ! $option_delay_saved)
            {
                // There was a problem saving the delay option
            $response['error'] = 1;
            $response['error_msg'] = 'Problem saving the delay option for the step '.$request_step['name'].'.';

            return $response;

            }

        }
    }

    /**
     * Prepare this Workflow record object to be templated by removing unneeded values and setting
     * appropriate template values
     *
     * @param WorkflowStepOptionDelay $workflowStepOptionDelay
     * @param WorkflowTemplateStep $workflowTemplateStep
     * @return array
     */
    public static function prepareForTemplate(WorkflowStepOptionDelay $workflowStepOptionDelay, WorkflowTemplateStep $workflowTemplateStep)
    {
        $workflowTemplateStepOptionDelay = $workflowStepOptionDelay->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowTemplateStepOptionDelay['uid']);        
        unset($workflowTemplateStepOptionDelay['workflow_step_uid']);
        unset($workflowTemplateStepOptionDelay['next_step_uid']);

        // Set the stuff we need
        $workflowTemplateStepOptionDelay['workflow_template_step_uid']           = $workflowTemplateStep->uid;

        // Return the array
        return $workflowTemplateStepOptionDelay;
    }


}
