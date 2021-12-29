<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Trigger;

/**
 * App\TriggerConfButton
 *
 * @property int $uid
 * @property string $public_id
 * @property string $postsubmit_redirect_url
 * @property-read \App\WorkflowTrigger $workflowTrigger
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfButton newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfButton newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfButton query()
 * @mixin \Eloquent
 */

class TriggerConfButton extends Model
{
    public $timestamps      = false;

    protected $table        = 'trigger_conf_buttons';
    protected $primaryKey   = 'uid';

    protected $guarded      = ['uid'];

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
     * @throws \Exception
     */
    public static function updateOrCreate($request_options, $workflow_trigger)
    {
        $response['success']                = 0;
        $response['error']                  = 0;
        $response['error_msg']              = '';
        $response['workflow_step_items']    = null;

        // Extract the needed options for buttons
        $redirect_url             = $request_options['redirect_url'];
        $color                    = $request_options['color'];
        $size                     = $request_options['size'];
        $config_uid               = $request_options['uid'] ?? null ;


        // Let's validate we have valid a url
        if ( isset($redirect_url) && ! filter_var($redirect_url, FILTER_VALIDATE_URL)) 
        {
            $response_array['error'] = 1;
            $response_array['error_msg'] = 'This is not a valid URL';

            return $response_array;
        }

        // Let's validate the color property
        $valid_colors = ['blue','white'];
        if( ! in_array($color, $valid_colors, true))
        {
            $response_array['error'] = 1;
            $response_array['error_msg'] = $color.' is not a valid color for optin button.';

            return $response_array;
        }

        // Let's validate the size property
        $valid_sizes = ['standard','large','xlarge'];
        if( ! in_array($size, $valid_sizes, true))
        {
            $response_array['error'] = 1;
            $response_array['error_msg'] = $size.' is not a valid size for optin button.';

            return $response_array;
        }
        
        // Let's generate a public_id
        $public_id = Trigger::generatePublicId();

        // if we have a uid on the options then this is a update otherwise it's a new one

        if ( isset($config_uid) )
        {
            // update
            $button                 = $workflow_trigger->button()->first();

            // Let's retrieve the public_id
            $public_id = $button->public_id;
        }
        else
        {
            // New one
            $button                 = new self;
            $button->public_id      = $public_id;
        }


        // Once created the workflow trigger, let's create the workflow trigger
        
        $button->postsubmit_redirect_url          = $redirect_url ?? '';
        $button->color                            = $color;
        $button->size                             = $size;
        $button->workflow_trigger_uid             = $workflow_trigger->uid;



        $button_saved = $button->save();
        if( ! $button_saved)
        {
            // Saving the keyword config trigger failed
            $response['error'] = 1;
            $response['error_msg'] = 'Error saving Button config trigger';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        $response['public_id'] = $public_id;


        return $response;
    }
}
