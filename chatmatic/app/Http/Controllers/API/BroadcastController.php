<?php

namespace App\Http\Controllers\API;

use App\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\WorkflowTrigger;
use App\TriggerConfBroadcast;

class BroadcastController extends BaseController
{
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
            'broadcasts' => []
        ];

        foreach($page->workflowTriggers()->where('archived','!=',1)->where('type','broadcast')->orderBy('uid', 'asc')->get() as $index => $broadcast)
        {
            $response_array['broadcasts'][$index] = [
                'uid'                       => $broadcast->uid,
                'workflow_uid'              => $broadcast->workflow_uid,
                'workflow_archived'         => $broadcast->workflow->archived ?? null,
                'page_uid'                  => $broadcast->page->uid,
                'broadcast_name'            => $broadcast->name,
                'messages_sent'             => $broadcast->messages_delivered,
                'messages_delivered'        => $broadcast->messages_delivered,
                'messages_read'             => $broadcast->messages_read,
                'messages_clicked'          => $broadcast->messages_clicked,
                'created_at_utc'            => $broadcast->created_at_utc->toDateTimeString()
            ];

            $broadcast_conf = $broadcast->broadcast()->first();

            $response_array['broadcasts'][$index]['options'] = [
                        'status'                               => $broadcast_conf->status, 
                        'broadcast_type'                       => $broadcast_conf->broadcast_type,
                        'intention'                            => $broadcast_conf->intention,
                        'conditions_json'                      => json_decode($broadcast_conf->conditions_json),
                        'facebook_messaging_type'              => $broadcast_conf->facebook_messaging_type,
                        'facebook_messaging_tag'               => $broadcast_conf->facebook_messaging_tag,
                        'fire_at_utc'                          => $broadcast_conf->fire_at_utc,
                        'optimized'                            => $broadcast_conf->optimized
                    ];
        }

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
        $broadcast_name         = $request->get('trigger_name');
        $workflow_uid           = $request->get('workflow_uid');
        $options                = $request->get('options');

        // Let's validate we have the valid type
        if ($trigger_type != 'broadcast')
        {
            $response['error'] = 1;
            $response['error_msg'] = 'This end point only allow broadcast trigger type';

            return $response;
        }

        // Let's validate the needed data to write on workflow triggers tabe
        $broadcast_trigger_response = WorkflowTrigger::validateApiRequest($request,$page);
        if ( $broadcast_trigger_response['error'] )
        {
            $response['error'] = 1;
            $response['error_msg'] = $broadcast_trigger_response['error_msg'];

            return $response;
        }
        $workflow = $broadcast_trigger_response['workflow'];

        // Confirm we already don't have a broadcast with this name
        $dupe_test = $page->workflowTriggers()->where('name',$broadcast_name)->where('type','broadcast')->first();
        if($dupe_test)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A broadcast with the name _'.$broadcast_name.'_ already exists.';

            return $response;
        }

        // Start database transaction
        \DB::beginTransaction();

        $broadcast                           = new WorkflowTrigger;
        $broadcast->type                     = $trigger_type;
        $broadcast->name                     = $broadcast_name;
        $broadcast->messages_delivered       = 0;
        $broadcast->messages_read            = 0;
        $broadcast->messages_clicked         = 0;
        $broadcast->conversions              = 0;
        $broadcast->archived                 = False;
        $broadcast->workflow_uid             = $workflow->uid;
        $broadcast->page_uid                 = $page->uid;

        $saved = $broadcast->save();

        $broadcast_conf = TriggerConfBroadcast::updateOrCreate($options, $broadcast);
        if ( $broadcast_conf['error'] )
        {
            // Rollback our database changes
            \DB::rollBack();

            return $broadcast_conf;
        }

        // Comit transaction to database
        \DB::commit();

        $broadcast->trigger();
        
        $response['success']    = 1;
        $response['broadcast_uid'] = $broadcast->uid;

        return $response;


    }

    public function filterCount(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'count'         => 0,
            'error_msg'     => '',
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Make request to Pipeline for count of subscribers
        $pipeline_internal_base_url = \Config::get('chatmatic.pipeline_internal_base_url');

        // Setup request to pipeline to get the scan code
        $params = $request->get('params');
        $post_array                 = [
            'page_uid'          => $page->uid,
            'broadcast_type'    => $params['broadcast_type'],
            'filters'           => $params['filters']
        ];

        $curl = curl_init($pipeline_internal_base_url . '/count-broadcast');
        if ($curl === false) {
            $error_message          = 'Unable to obtain subscriber count because of internal error. #001';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        $curlSetoptResult = curl_setopt_array($curl, array(
            CURLOPT_POST            => 1,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_POSTFIELDS      => json_encode($post_array)
        ));
        if ($curlSetoptResult === false) {
            $error_message          = 'Unable to obtain subscriber count because of internal error. #002';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        $curl_result = curl_exec($curl);
        if ($curl_result === false) {
            $error_message          = 'Unable to obtain subscriber count because of internal error. #003';
            $response['error']      = 1;
            $response['error_msg']  = $error_message;
            return $response;
        }
        curl_close($curl);

        $curl_result_json = json_decode($curl_result);

        if (json_last_error() === JSON_ERROR_NONE) {
            $response['count'] = $curl_result_json->count;
        }

        return $response;
    }

    public function update(Request $request, $page_uid, $broadcast_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\Broadcast $broadcast */
        $broadcast = $page->workflowTriggers()->where('uid',$broadcast_uid)->first();

        if (! isset($broadcast))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Broadcast with uid '.$broadcast_uid.' not found';
        }

        if ($broadcast->status != 1)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'This broadcast was already sent';

            return $response;
        }

        // Extract request vars
        $broadcast_name         = $request->get('trigger_name');
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
        $dupe_test = $page->workflowTriggers()->where('name',$broadcast_name)->where('type','broadcast')->where('uid','!=',$broadcast_uid)->first();
        if($dupe_test)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'A broadcast with the name _'.$broadcast_name.'_ already exists.';

            return $response;
        }

        $broadcast->name            = $broadcast_name;
        $broadcast->workflow_uid    = $workflow_uid;

        $updated_broadcast_trigger = $broadcast->save();

        if ( ! $updated_broadcast_trigger )
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Error saving the broadcast';
        }

        $trigger_conf_broadcast = $broadcast->broadcast()->first();

        $broadcast_conf = TriggerConfBroadcast::updateOrCreate($options, $broadcast);
        if ( $broadcast_conf['error'] )
        {
            // Rollback our database changes
            \DB::rollBack();

            return $broadcast_conf;
        }

        // Comit transaction to database
        \DB::commit();

        
        $response['success']    = 1;
        $response['broadcast_uid'] = $broadcast->uid;

        return $response;
    }

    public function delete(Request $request, $page_uid, $broadcast_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\Broadcast $broadcast */
        $broadcast = $page->workflowTriggers()->where('uid',$broadcast_uid)->first();

        if (! isset($broadcast))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Broadcast with uid '.$broadcast_uid.' not found';
        }

        $broadcast->archived = 1;
        $broadcast->save();

        $response['success'] = 1;

        return $response;

    }

    public function fire_broadcast(Request $request, $page_uid, $broadcast_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        /** @var \App\Broadcast $broadcast */
        $broadcast = $page->workflowTriggers()->where('uid',$broadcast_uid)->first();

        if (! isset($broadcast))
        {
            $response['error'] = 1;
            $response['error_msg'] = 'Broadcast with uid '.$broadcast_uid.' not found';
        }

        $broadcast_conf = $broadcast->broadcast()->first();
        if ($broadcast_conf->status != 1)
        {
            $response['error'] = 1;
            $response['error_msg'] = 'This broadcast was already sent.';

            return $response;
        }

        // Issue request to pipeline API to send broadcasts
        $broadcast->trigger();

        $response['success'] = 1;

        return $response;
    }
}
