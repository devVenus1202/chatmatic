<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Trigger;

/**
 * App\TriggerConfMdotme
 *
 * @property int $uid
 * @property string $public_id
 * @property string $m_me_url
 * @property string $custom_ref
 * @property-read \App\WorkflowTrigger $workflowTrigger
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfMdotme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfMdotme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TriggerConfMdotme query()
 * @mixin \Eloquent
 */

class TriggerConfMdotme extends Model
{
    public $timestamps      = false;

    protected $table        = 'trigger_conf_m_dot_mes';
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

        // Extract the needed options m dot me
        $custom_ref             = $request_options['custom_ref'];
        $public_id              = $request_options['public_id'] ?? null;
        $config_uid             = $request_options['uid'] ?? null ;


        // If we have a $config_uid then this is an update otherwise this is a new one
        if ( isset($config_uid) ){

            // update m dot me

            $m_dot_me = $workflow_trigger->mdotme()->first();
            $public_id = $m_dot_me->public_id;
        }
        else
        {
            // New m dot me

            // Let's generate a public_id only the first time
            // $public_id = Trigger::generatePublicId();
            // Let's validate we have a $public_id

            if ( ! isset($public_id) )
            {
                $response['error'] = 1;
                $response['error_msg'] = 'Please provide a public_id';

                return $response;
            }

             // New one
             $m_dot_me                                   = new self;
             $m_dot_me->public_id                        = $public_id;
        }
        
        // Page url 
        $page_url = $workflow_trigger->page->fb_page_token;

        // If the page id is found in the url then it's one of the vanity urls that doesn't work
        if(mb_stristr($page_url, $workflow_trigger->page->fb_id))
        {
            $page_url = $workflow_trigger->page->fb_id;
        }

        // We have two options here, 1st use the one wanted by the user
        // 2nd Use one generated by us
        if($custom_ref === null || $custom_ref === '' || ! isset($custom_ref))
        {
            $ref                    = 'campaign::' . $public_id;
            $m_me_url               = 'http://m.me/' . rawurlencode($page_url) . '?ref=' . rawurlencode($ref);
        }
        else
        {
            $page_m_dot_me_triggers = $workflow_trigger->page->workflowTriggers()->where('type','m_dot_me')->get();
            $dupe_check = false;
            foreach ($page_m_dot_me_triggers as $m_trigger) {
                $check = $m_trigger->mdotme()->where('custom_ref',$custom_ref)->where('uid','!=',12)->first();
                if ( $check )
                {
                    $dupe_check = true;
                    break;
                }
            }

            if ( $dupe_check )
            {
                $response_array['error'] = 1;
                $response_array['error_msg'] = 'This custom ref is already is in use.';

                return $response_array;
            }

            $ref                    = $custom_ref;
            $m_me_url               = 'http://m.me/' . rawurlencode($page_url) . '?ref=' . rawurlencode($ref);


        }

        // Once created the workflow trigger, let's create the workflow trigger
        $m_dot_me->m_me_url                         = $m_me_url;
        $m_dot_me->custom_ref                       = $custom_ref ?? null;
        $m_dot_me->workflow_trigger_uid             = $workflow_trigger->uid;


        $m_dot_me_saved = $m_dot_me->save();
        if( ! $m_dot_me_saved)
        {
            // Saving the keyword config trigger failed
            $response['error'] = 1;
            $response['error_msg'] = 'Error saving m dot me config trigger';

            // Rolling our database changes
            \DB::rollBack();

            return $response;
        }

        $response['url'] = $m_me_url;


        return $response;
    }
}
