<?php

namespace App\Http\Controllers\API;

use App\AuditLog;
use App\Workflow;
use App\WorkflowTrigger;
use App\TriggerConfKeyword;
use App\TriggerConfMdotme;
use App\TriggerConfButton;
use App\TriggerConfLandingPage;
use App\TriggerConfTrigger;
use App\TriggerConfChatWidget;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

class WorkflowTriggerController extends BaseController
{
    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function index(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $response_array = [
            'workflow_triggers' => []
        ];
        foreach($page->workflowTriggers()->where('archived','!=',1)->whereNotIn('type',array('landing_page', 'broadcast'))->orderBy('uid', 'asc')->get() as $index => $workflow_trigger)
        {
            /** @var \App\Workflow $workflow */

            //$message_count_ratios   = $workflow->generateMessageCountRatios();
            //$messages_read_ratio    = $message_count_ratios['read_ratio'];
            //$messages_clicked_ratio = $message_count_ratios['clicked_ratio'];

            $response_array['workflow_triggers'][$index] = [
                'uid'                       => $workflow_trigger->uid,
                'workflow_uid'              => $workflow_trigger->workflow_uid,
                'workflow_archived'         => $workflow_trigger->workflow->archived ?? null,
                'page_uid'                  => $workflow_trigger->page->uid,
                'trigger_name'              => $workflow_trigger->name,
                'type'                      => $workflow_trigger->type,
                'messages_delivered'        => $workflow_trigger->messages_delivered,
                'messages_read'             => $workflow_trigger->messages_read,
                'messages_clicked'          => $workflow_trigger->messages_clicked,
                'conversions'               => $workflow_trigger->conversions,                                
                'created_at_utc'            => $workflow_trigger->created_at_utc->toDateTimeString()
            ];

            $no_further_setup = ['welcomemsg','autoresponse', 'json', 'checkbox'];
            if ( ! in_array($workflow_trigger->type,$no_further_setup, true) )
            {
                // Here we have to return the specific data

                // Keywords
                if ($workflow_trigger->type == 'keywordmsg')
                {
                    // Find out the setup for the keyword
                    $trigger_conf = $workflow_trigger->keyword()->first();

                    $response_array['workflow_triggers'][$index]['options'] = [
                        'uid'                   => $trigger_conf->uid,
                        'keywords'              => explode(',', $trigger_conf->words),
                        'keywords_option'       => $trigger_conf->option
                    ];
                }

                // m_dot_me
                if ($workflow_trigger->type == 'm_dot_me')
                {
                    // Find out the setup for the keyword
                    $trigger_conf = $workflow_trigger->mdotme()->first();

                    $response_array['workflow_triggers'][$index]['options'] = [
                        'uid'                     => $trigger_conf->uid,
                        'public_id'               => $trigger_conf->public_id,
                        'custom_ref'              => $trigger_conf->custom_ref
                    ];
                }

                // buttons
                if ($workflow_trigger->type == 'buttons')
                {
                    // Find out the setup for the keyword
                    $trigger_conf = $workflow_trigger->button()->first();

                    $response_array['workflow_triggers'][$index]['options'] = [
                        'uid'                       => $trigger_conf->uid,
                        'public_id'                 => $trigger_conf->public_id,
                        'redirect_url'              => $trigger_conf->postsubmit_redirect_url,
                        'color'                     => $trigger_conf->color,
                        'size'                      => $trigger_conf->size
                    ];
                }

                // landing_page
                if ($workflow_trigger->type == 'landing_page')
                {
                    // Find out the setup for the landing page
                    $trigger_conf = $workflow_trigger->landingPage()->first();

                    $response_array['workflow_triggers'][$index]['options'] = [
                        'uid'                                   => $trigger_conf->uid,
                        'presubmit_title'                       => $trigger_conf->presubmit_title,
                        'presubmit_body'                        => $trigger_conf->presubmit_body,
                        'presubmit_image'                       => $trigger_conf->presubmit_image,
                        'approval_method'                       => $trigger_conf->approval_method,
                        'postsubmit_type'                       => $trigger_conf->postsubmit_type,
                        'postsubmit_redirect_url'               => $trigger_conf->postsubmit_redirect_url,
                        'postsubmit_redirect_url_button_text'   => $trigger_conf->postsubmit_redirect_url_button_text,
                        'postsubmit_content_title'              => $trigger_conf->postsubmit_content_title,
                        'postsubmit_content_body'               => $trigger_conf->postsubmit_content_body,
                        'postsubmit_content_image'              => $trigger_conf->postsubmit_content_image

                    ];
                }

                // post_trigger
                if ($workflow_trigger->type == 'post_trigger')
                {
                    // if the trigger comes from the old fashion one, then has not name
                    $createdAt = Carbon::parse($workflow_trigger->created_at_utc);
                    if ( $workflow_trigger->name === '' ){
                        $response_array['workflow_triggers'][$index]['trigger_name'] = "Comment trigger ".$createdAt->format('M d Y');;
                    }

                    // Find out the setup for the post_trigger
                    $trigger_conf = $workflow_trigger->postTrigger()->first();

                    $response_array['workflow_triggers'][$index]['options'] = [
                        'uid'                            => $trigger_conf->uid,
                        'post_uid'                       => $trigger_conf->post_uid,
                        'message'                        => $trigger_conf->message,
                        'active'                         => $trigger_conf->active
                    ];
                }

                // Chat Widgets
                if ($workflow_trigger->type == 'chat_widget')
                {
                    // Find out the setup for the keyword
                    $trigger_conf = $workflow_trigger->chatWidget()->first();

                    $response_array['workflow_triggers'][$index]['options'] = [
                        'uid'                       => $trigger_conf->uid,
                        'public_id'                   => $trigger_conf->public_id,
                        'color'                     => $trigger_conf->color,
                        'log_in_greeting'           => $trigger_conf->log_in_greeting,
                        'log_out_greeting'          => $trigger_conf->log_out_greeting,
                        'greeting_dialog_display'   => $trigger_conf->greeting_dialog_display,
                        'delay'                     => $trigger_conf->delay
                    ];
                }
            }
        }


        $elapsed    = time() - $this->start_time;
        $event_name = 'page.workflow_triggers.loaded';

        AuditLog::create([
            'chatmatic_user_uid'    => $this->user->uid,
            'page_uid'              => $page->uid,
            'event'                 => $event_name,
            'message'               => count($response_array['workflow_triggers']).' workflow triggers loaded in '.$elapsed.' seconds'
        ]);

        return $response_array;
    }

    
    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function create(Request $request, $page_uid)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Extract request vars
        $trigger_type           = $request->get('type');
        $trigger_name           = $request->get('trigger_name');
        $workflow_uid           = $request->get('workflow_uid');
        $options                = $request->get('options');


        // Let's validate the needed data to write on workflow triggers tabe
        $workflow_trigger_response = WorkflowTrigger::validateApiRequest($request,$page);
        if ( $workflow_trigger_response['error'] )
        {
            $response['error'] = 1;
            $response['error_msg'] = $workflow_trigger_response['error_msg'];

            return $response;
        }
        $workflow = $workflow_trigger_response['workflow'];

        // Apparently the basic set up is right, let's write on workflow_triggers table

        // We validate type only for message creatin, we not allow
        // update the type

        // Confirm we already don't have a workflow trigger with this name
        $dupe_test = $page->workflowTriggers()->where('name',$trigger_name)->where('type',$trigger_type)->first();
        if($dupe_test)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A flow trigger for '.$trigger_type.' with the name _'.$trigger_name.'_ already exists.';

            return $response;
        }

        // Validate the existence of a type
        if ( ! isset($trigger_type))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A trigger type is needed.';

            return $response;
        }
        // Let's validate the type
        $allowed_workflow_trigger_types = ['keywordmsg', 'welcomemsg', 'autoresponse','m_dot_me','landing_page','buttons','post_trigger','json','checkbox', 'chat_widget'];
        if( ! in_array($trigger_type, $allowed_workflow_trigger_types, true))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'The flow trigger type does not match any valid option';

            return $response;
        }

        // For cases such are: welcomemsg and autoresponse we have the enough data
        // for all others we need write in their own config table

        // Start database transaction
        \DB::beginTransaction();

        $flow_trigger                           = new WorkflowTrigger;
        $flow_trigger->type                     = $trigger_type;
        $flow_trigger->name                     = $trigger_name;
        $flow_trigger->messages_delivered       = 0;
        $flow_trigger->messages_read            = 0;
        $flow_trigger->messages_clicked         = 0;
        $flow_trigger->conversions              = 0;
        $flow_trigger->archived                 = False;
        $flow_trigger->workflow_uid             = $workflow->uid;
        $flow_trigger->page_uid                 = $page->uid;

        $saved = $flow_trigger->save();

        if( ! $saved )
        {
            $response['error']  = 1;
            $response['error_msg'] = 'Error saving flow trigger';
        }

        // More advanced set up
        $no_further_setup = ['welcomemsg','autoresponse', 'json', 'checkbox'];
        if( ! in_array($trigger_type, $no_further_setup, true))
        {
            // Validate the existence of the options
            if ( ! isset($options))
            {
                $response['error'] = 1;
                $response['error_msg'] = 'There are no options for the '.$trigger_type.' trigger.';

                // Rolling our database changes
                \DB::rollBack();

                return $response;
            }

            // keywordmsg
            if ($trigger_type === 'keywordmsg')
            {
                $keyword_trigger_response = TriggerConfKeyword::updateOrCreate($options, $flow_trigger);
                if ( $keyword_trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    $response['error'] = 1;
                    $response['error_msg'] = $trigger_response['error_msg'];

                    return $response;
                } 

            }

            // m_dot_me
            if ($trigger_type === 'm_dot_me')
            {
                $trigger_response = TriggerConfMdotme::updateOrCreate($options, $flow_trigger);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    $response['error'] = 1;
                    $response['error_msg'] = $trigger_response['error_msg'];

                    return $response;
                } 
                // Return the url
                $response['url'] = $trigger_response['url'];

            }

            // buttons
            if ($trigger_type === 'buttons')
            {
                $trigger_response = TriggerConfButton::updateOrCreate($options, $flow_trigger);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    $response['error'] = 1;
                    $response['error_msg'] = $trigger_response['error_msg'];

                    return $response;
                }
                // Return the pubic id
                $response['public_id'] = $trigger_response['public_id'];

            }

            // landing_page
            if ($trigger_type === 'landing_page')
            {
                $trigger_response = TriggerConfLandingPage::updateOrCreate($options, $flow_trigger);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    $response['error'] = 1;
                    $response['error_msg'] = $trigger_response['error_msg'];

                    return $response;

                }
                // Return the pubic id
                $response['public_id'] = $trigger_response['public_id'];

            }

            // post_trigger

            if ($trigger_type === 'post_trigger')
            {
                $trigger_response = TriggerConfTrigger::updateOrCreate($options, $flow_trigger);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    $response['error'] = 1;
                    $response['error_msg'] = $trigger_response['error_msg'];

                    return $response;
                }   
            }

            // chat_widget
            
            if ($trigger_type === 'chat_widget') {
                $trigger_response = TriggerConfChatWidget::updateOrCreate($options, $flow_trigger);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    $response['error'] = 1;
                    $response['error_msg'] = $trigger_response['error_msg'];

                    return $response;
                }
                $response['public_id'] = $trigger_response['public_id'];
            }
        }

        // For Json we have to make a internal request asking the content
        if ($trigger_type === 'json')
        {
            // Find out the root step
            $root_step = $workflow->rootStep()->first();
            $json_internal_request = $root_step->retrieveJson($flow_trigger->uid);

            if ($json_internal_request['error'])
            {
                // Rollback our database changes
                \DB::rollBack();

                $response['error'] = 1;
                $response['error_msg'] = $json_internal_request['error_msg'];

                return $response;
            }
            
            $response['json_step'] = $json_internal_request['json_step'];
            
        }

        // Comit transaction to database
        \DB::commit();

        
        $response['success']    = 1;
        $response['trigger_uid'] = $flow_trigger->uid;

        return $response;

    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function update(Request $request, $page_uid, $wt_uid)
    {
        $response = [
            'success'                   => 0,
            'error'                     => 0,
            'error_msg'                 => 0,
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }

        /** @var \App\Page $page */
        $page = $page['page'];

        $flow_trigger = $page->workflowTriggers()->where('uid',$wt_uid)->first();
        if ( ! $flow_trigger )
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Flow Trigger not found.';

            return $response;
        }

        // Extract request vars
        $trigger_name           = $request->get('trigger_name');
        $workflow_uid           = $request->get('workflow_uid');
        $options                = $request->get('options');    
        
        // Let's validate the needed data to write on workflow triggers tabe
        $workflow_trigger_response = WorkflowTrigger::validateApiRequest($request,$page);
        if ( $workflow_trigger_response['error'] )
        {
            $response['error'] = 1;
            $response['error_msg'] = $workflow_trigger_response['error_msg'];

            return $response;
        }
        $workflow = $workflow_trigger_response['workflow'];

        // Confirm we already don't have another workflow trigger with this name
        $dupe_test = $page->workflowTriggers()->where('name',$trigger_name)->where('type',$flow_trigger->type)->where('uid','!=',$wt_uid)->first();
        if($dupe_test)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A flow trigger of type '.$flow_trigger->type.' with the name _'.$trigger_name.'_ already exists.';

            return $response;
        }

        // Start database transaction
        \DB::beginTransaction();

        // For now it's not possible to update the trigger type
        // Only the name, the workflow associated and the options

        $flow_trigger->name                     = $trigger_name;
        $flow_trigger->workflow_uid             = $workflow->uid;

        $updated_flow_trigger = $flow_trigger->save();

        if ( ! $updated_flow_trigger )
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Error saving the flow trigger';
        }


        // More advanced set up
        $no_further_setup = ['welcomemsg','autoresponse', 'json','checkbox'];

        if( ! in_array($flow_trigger->type, $no_further_setup, true))
        {
            // Validate the existence of the options
            if ( ! isset($options))
            {
                $response['error'] = 1;
                $response['error_msg'] = 'There are no options for the '.$flow_trigger->type.' trigger.';

                // Rolling our database changes
                \DB::rollBack();

                return $response;
            }

            // Validate the attached uid of the option
            $option_uid = $options['uid'];


            // keywordmsg
            if ($flow_trigger->type === 'keywordmsg')
            {
                $trigger_conf_keyword = $flow_trigger->keyword()->first();

                if ( ! isset($trigger_conf_keyword) && $option_uid == $trigger_conf_keyword->uid ){
                    $resposne['error'] = 1;
                    $response['error_msg'] = 'There are no a keyword setup associated with this trigger.';

                    // Rolling our database changes
                    \DB::rollBack();

                    return $response;
                }

                $trigger_response = TriggerConfKeyword::updateOrCreate($options, $flow_trigger, $trigger_conf_keyword);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $keyword_trigger_response;
                } 

            }

            // m_dot_me
            elseif ( $flow_trigger->type === 'm_dot_me' )
            {
                $trigger_conf_m_dot_me = $flow_trigger->mdotme()->first();

                if ( ! isset($trigger_conf_m_dot_me) && $option_uid == $trigger_conf_m_dot_me->uid ){
                    $resposne['error'] = 1;
                    $response['error_msg'] = 'There are no a M dot me setup associated with this trigger.';

                    // Rolling our database changes
                    \DB::rollBack();

                    return $response;
                }

                $trigger_response = TriggerConfMdotme::updateOrCreate($options, $flow_trigger, $trigger_conf_m_dot_me);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $trigger_response;
                }

                // Return the url
                $response['url'] = $trigger_response['url'];
            }

            // buttons
            elseif ( $flow_trigger->type === 'buttons' )
            {
                $trigger_conf_button = $flow_trigger->button()->first();

                if ( ! isset($trigger_conf_button) && $option_uid == $trigger_conf_button->uid ){
                    $resposne['error'] = 1;
                    $response['error_msg'] = 'There are no a button setup associated with this trigger.';

                    // Rolling our database changes
                    \DB::rollBack();

                    return $response;
                }

                $trigger_response = TriggerConfButton::updateOrCreate($options, $flow_trigger, $trigger_conf_button);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $trigger_response;
                }

                // Return the url
                $response['public_id'] = $trigger_response['public_id'];
            }

            // landing_page
            elseif ( $flow_trigger->type === 'landing_page' )
            {
                $trigger_conf_land_page = $flow_trigger->landingPage()->first();

                if ( ! isset($trigger_conf_land_page) && $option_uid == $trigger_conf_land_page->uid ){
                    $resposne['error'] = 1;
                    $response['error_msg'] = 'There are no a landing pagge setup associated with this trigger.';

                    // Rolling our database changes
                    \DB::rollBack();

                    return $response;
                }

                $trigger_response = TriggerConfLandingPage::updateOrCreate($options, $flow_trigger, $trigger_conf_land_page);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $trigger_response;
                }

                // Return the public id
                $response['public_id'] = $trigger_response['public_id'];
            }

            // post_trigger
            elseif ( $flow_trigger->type === 'post_trigger' )
            {
                $trigger_conf_post_trigger = $flow_trigger->postTrigger()->first();

                if ( ! isset($trigger_conf_post_trigger) && $option_uid == $trigger_conf_post_trigger->uid ){
                    $resposne['error'] = 1;
                    $response['error_msg'] = 'There are no a post comment trigger associated with this trigger.';

                    // Rolling our database changes
                    \DB::rollBack();

                    return $response;
                }

                $trigger_response = TriggerConfTrigger::updateOrCreate($options, $flow_trigger, $trigger_conf_post_trigger);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    return $trigger_response;
                }

            }

            // chat_widget
            
            elseif ($flow_trigger->type === 'chat_widget') {
                $trigger_conf_chat_widget = $flow_trigger->chatWidget()->first();

                if ( ! isset($trigger_conf_chat_widget) && $option_uid == $trigger_conf_chat_widget->uid ){
                    $resposne['error'] = 1;
                    $response['error_msg'] = 'There are no a chat widget trigger associated with this trigger.';

                    // Rolling our database changes
                    \DB::rollBack();

                    return $response;
                }

                $trigger_response = TriggerConfChatWidget::updateOrCreate($options, $flow_trigger, $trigger_conf_chat_widget);
                if ( $trigger_response['error'] )
                {
                    // Rollback our database changes
                    \DB::rollBack();

                    $response['error'] = 1;
                    $response['error_msg'] = $trigger_response['error_msg'];

                    return $response;
                }
                $response['public_id'] = $trigger_response['public_id'];
            }

        }

        // For Json we have to make a internal request asking the content
        if ( $flow_trigger->type === 'json' )
        {
            // Find out the root step
            $root_step = $workflow->rootStep()->first();
            $json_internal_request = $root_step->retrieveJson($flow_trigger->uid);

            if ($json_internal_request['error'])
            {
                // Rollback our database changes
                \DB::rollBack();

                $response['error'] = 1;
                $response['error_msg'] = $json_internal_request['error_msg'];

                return $response;
            }
            
            $response['json_step'] = $json_internal_request['json_step'];
            
        }
        
        // Comit transaction to database
        \DB::commit();

        
        $response['success']    = 1;
        $response['trigger_uid'] = $flow_trigger->uid;

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function delete(Request $request, $page_uid, $wt_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => 0,
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];


        $flow_trigger = $page->workflowTriggers()->where('uid',$wt_uid)->first();
        if ( ! $flow_trigger )
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Flow Trigger not found.';

            return $response;
        }

        // if this is a post trigger we have to update further info
        if ( $flow_trigger->type === 'post_trigger')
        {
            $trigger_conf = $flow_trigger->postTrigger()->first();

            // Let's deactivate the postTrigger before the trigger deletion
            $trigger_conf->active = 0;
            $trigger_conf->save();
        }

        // Let's deactivate the workflow trigger
        $flow_trigger->archived = 1;
        $flow_trigger->save();



        $response['success']    = 1;

        return $response;
    }
}