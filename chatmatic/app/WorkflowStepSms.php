<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\WorkflowStep
 *
 * @property int $uid
 * @property string $text_message
 * @property int $workfow_step_uid
 * @property-read \App\WorkflowStep $workflowStep
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepSms newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepSms newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowStepSms query()
 * @mixin \Eloquent
 */

class WorkflowStepSms extends Model
{
    protected $table        = 'workflow_step_sms';
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
     * @param $request_step_delay_data
     * @param $workflow_step
     * @param $temp_step_uid_map
     * @return mixed
     * @throws \Exception
     */
    public static function updateOrCreate($request_step_sms_data, $workflow_step, $temp_step_uid_map)
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
        $request_step           = $request_step_sms_data;

        // Extract the needed data from the request
        if( ! isset($request_step['options']['sms_text_message']))
        {
            // The request has not the needed data
            $response['error'] = 1;
            $response['error_msg'] = 'Sms has not the needed data to be saved';

            return $response;
        }
        $text_message = $request_step['options']['sms_text_message'];
        $phone_number_to = $request_step['options']['phone_number_to'] ?? null;

        // If there's a int uid in the request then it's an existing step
        // UPDATE
        if ( is_int($request_step['step_uid']))
        {
            $workflow_step_option_sms = $workflow_step->optionSms()->first();

            // Throw an error if we didn't get a sms set up row
            if( ! $workflow_step_option_sms)
            {
                // workflow step item not found
                $response['error']      = 1;
                $response['error_msg']  = 'Workflow step sms not found ('.$request_step['step_uid'].')';

                return $response;
            }

            // Finally let's validate we have a valid next step
            /*
            if( ! $temp_step_uid_map[$request_step['options']['next_step_uid']])
            {
                // We have not a next step
                $response['error'] = 1;
                $response['error_msg'] = 'We have not a step associated for the delay option';

                return $response;
            }
            */
            $workflow_step_option_sms->text_message           = $text_message;
            $workflow_step_option_sms->phone_number_to           = $phone_number_to;

            $sms_saved = $workflow_step_option_sms->save();

            if( ! $sms_saved)
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
            /*
            if( ! $temp_step_uid_map[$request_step['options']['next_step_uid']])
            {
                // We have not a next step
                $response['error'] = 1;
                $response['error_msg'] = 'We have not a step associated for the delay option';

                return $response;
            }
            */

            // Populate the options delays
            // Populate the sms data
            $sms                    = new WorkflowStepSms;
            $sms->text_message      = $text_message;
            $sms->phone_number_to   = $phone_number_to;
            $sms->workflow_step_uid = $workflow_step->uid;

            $sms_saved = $sms->save();

            if( ! $sms_saved)
            {
                // The sms was not saved on database
                $response['error'] = 1;
                $response['error_msg'] = 'The sms could not be saved on database';  

                return $response;
            }
        }
    }

    /**
     * Prepare this Workflow record object to be templated by removing unneeded values and setting
     * appropriate template values
     *
     * @param WorkflowStepSms $workflowStepSms
     * @param WorkflowTemplateStep $workflowTemplateStep
     * @return array
     */
    public static function prepareForTemplate(WorkflowStepSms $workflowStepSms, WorkflowTemplateStep $workflowTemplateStep)
    {
        $workflowTemplateStepSms = $workflowStepSms->toArray();

        // Unset the stuff we don't need/will replace
        unset($workflowTemplateStepSms['uid']);        
        unset($workflowTemplateStepSms['workflow_step_uid']);

        // Set the stuff we need
        $workflowTemplateStepSms['workflow_template_step_uid'] = $workflowTemplateStep->uid;

        // Return the array
        return $workflowTemplateStepSms;
    }

}
