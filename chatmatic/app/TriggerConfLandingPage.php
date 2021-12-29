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
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfLandingPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfLandingPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfLandingPage query()
 * @mixin \Eloquent
 */

class TriggerConfLandingPage extends Model
{
    public $timestamps      = false;
    
    protected $table        = 'trigger_conf_landing_pages';
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
        $presubmit_title                            = $request_options['presubmit_title'] ?? '';
        $presubmit_body                             = $request_options['presubmit_body'] ?? '';
        $presubmit_image                            = $request_options['presubmit_image'] ?? '';
        $approval_method                            = $request_options['approval_method'] ?? '';
        $postsubmit_type                            = $request_options['postsubmit_type'] ?? '';
        $postsubmit_redirect_url                    = $request_options['postsubmit_redirect_url'] ?? '';
        $postsubmit_redirect_url_button_text        = $request_options['postsubmit_redirect_url_button_text'] ?? '';
        $postsubmit_content_title                   = $request_options['postsubmit_content_title'] ?? '';
        $postsubmit_content_body                    = $request_options['postsubmit_content_body'] ?? '';
        $postsubmit_content_image                   = $request_options['postsubmit_content_image'] ?? '';
        $config_uid                                 = $request_options['uid'] ?? null ;

        if ( isset($config_uid) )
        {
            // Update
            $landing_page                           = $workflow_trigger->landingPage()->first();

            // Get the acutal public id
            $public_id                              = $landing_page->public_id;
        }
        else
        {
            // New

            // Let's generate a public_id
            $public_id = Trigger::generatePublicId();
            $landing_page                                           = new self;
            
        }
        
        


        // Once created the workflow trigger, let's create the workflow trigger
        $landing_page->public_id                                = $public_id;
        $landing_page->presubmit_title                          = $presubmit_title;
        $landing_page->presubmit_body                           = $presubmit_body;
        $landing_page->presubmit_image                          = $presubmit_image;
        $landing_page->approval_method                          = $approval_method;
        $landing_page->postsubmit_type                          = $postsubmit_type;
        $landing_page->postsubmit_redirect_url                  = $postsubmit_redirect_url;
        $landing_page->postsubmit_redirect_url_button_text      = $postsubmit_redirect_url_button_text;
        $landing_page->postsubmit_content_title                 = $postsubmit_content_title;
        $landing_page->postsubmit_content_body                  = $postsubmit_content_body;
        $landing_page->postsubmit_content_image                 = $postsubmit_content_image;
        $landing_page->workflow_trigger_uid                     = $workflow_trigger->uid;


        $landing_page_saved = $landing_page->save();
        if( ! $landing_page_saved)
        {
            // Saving the keyword config trigger failed
            $response['error'] = 1;
            $response['error_msg'] = 'Error saving landing page trigger';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        $response['public_id'] = $public_id;


        return $response;
    }
}
