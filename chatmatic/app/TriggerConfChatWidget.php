<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Trigger;

class TriggerConfChatWidget extends Model
{
    //
    public $timestamps = false;
    protected $table = 'trigger_conf_chat_widgets';
    protected $primaryKey = 'uid';
    protected $guarded = ['uid'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTrigger()
    {
        return $this->belongsTo(WorkflowTrigger::class, 'workflow_trigger_uid', 'uid');
    }

    /**
     * @param $request_options
     * @param $workflow_trigger
     * @return mixed
     */
    public static function updateOrCreate($request_options, $workflow_trigger) {
        $response['success'] = 0;
        $response['error'] = 0;
        $response['error_msg'] = '';
        $response['workflow_step_items'] = null;

        $color = $request_options['color'];
        $log_in_greeting = $request_options['log_in_greeting'];
        $log_out_greeting = $request_options['log_out_greeting'];
        $greeting_dialog_display = $request_options['greeting_dialog_display'];
        $delay = $request_options['delay'];
        $config_uid = $request_options['uid'] ?? null ;;
        if (!isset($color)) {
            $response_array['error'] = 1;
            $response_array['error_msg'] = 'The color parameter missing.';

            return $response_array;
        }

        if (!isset($log_in_greeting)) {
            $response_array['error'] = 1;
            $response_array['error_msg'] = 'The Login greeting missing.';

            return $response_array;
        }

        if (!isset($log_out_greeting)) {
            $response_array['error'] = 1;
            $response_array['error_msg'] = 'The Log out greeting missing.';

            return $response_array;
        }

        if (!isset($greeting_dialog_display)) {
            $response_array['error'] = 1;
            $response_array['error_msg'] = 'The greeting dialog display option missing.';

            return $response_array;
        }

        if (!isset($delay)) {
            $response_array['error'] = 1;
            $response_array['error_msg'] = 'The delay parameter missing.';

            return $response_array;
        }

        $public_id = Trigger::generatePublicId();

        if ( isset($config_uid) )
        {
            // update
            $chat_widget                 = $workflow_trigger->chatWidget()->first();

            // Let's retrieve the public_id
            $public_id = $chat_widget->public_id;
        }
        else
        {
            // New one
            $chat_widget                 = new self;
            $chat_widget->public_id      = $public_id;
        }

        $chat_widget->public_id = $public_id;
        $chat_widget->color = $color;
        $chat_widget->log_in_greeting = $log_in_greeting;
        $chat_widget->log_out_greeting = $log_out_greeting;
        $chat_widget->greeting_dialog_display = $greeting_dialog_display;
        $chat_widget->delay = $delay;
        $chat_widget->workflow_trigger_uid = $workflow_trigger->uid;
        
        $chat_widget_saved = $chat_widget->save();
        if (!$chat_widget_saved) {
            // Saving the keyword config trigger failed
            $response['error'] = 1;
            $response['error_msg'] = 'Error saving Chat widget config trigger';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        $response['public_id'] = $public_id;
        return $response;
    }
}
