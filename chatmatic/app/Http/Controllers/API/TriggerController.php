<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

class TriggerController extends BaseController
{
    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function create(Request $request, $page_uid)
    {
        $response = [
            'success'       => 0,
            'error'         => 0,
            'error_msg'     => '',
            'trigger_uid'   => null,
        ];

        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // TODO: Validate this input (also confirm the post_uid) matches a post on the proper page
        if(mb_strlen($request->get('message')) > 960)
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Your message must be less than 960 characters long.';

            return $response;
        }

        // Validate that there isn't already a trigger on this post
        $trigger = $page->triggers()->where('post_uid', $request->get('post_uid'))->first();
        if($trigger)
        {
            $response['error']      = 1;
            $response['error_msg']  = 'There is already a trigger assigned to this post.';

            return $response;
        }

        // Validate that the workflow_uid provided actually exists
        if($request->has('workflow_uid') && $request->get('workflow_uid') !== null)
        {
            $workflow = $page->workflows()->where('uid', $request->get('workflow_uid'))->first();
            if( ! $workflow)
            {
                $response['error']      = 1;
                $response['error_msg']  = 'There is no workflow with the given uid.';
            }
        }

        $message = $request->get('message');
        if($message === null)
            $message = '';

        $temp_trigger = [
            'post_uid'              => $request->get('post_uid'),
            'inclusion_keywords'    => $request->get('inclusion_keywords') ?? '',
            'exclusion_keywords'    => $request->get('exclusion_keywords') ?? '',
            'message'               => $message,
            'workflow_uid'          => $request->get('workflow_uid') ?? null,
            'active'                => true,
        ];

        $trigger = $page->triggers()->create($temp_trigger);

        $response = [
            'trigger_uid' => $trigger->uid
        ];

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @param $trigger_uid
     * @return array
     */
    public function update(Request $request, $page_uid, $trigger_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $trigger = $page->triggers()->where('uid', $trigger_uid)->firstOrFail();

        $active_state_value = $request->get('active');
        $active_state = false;
        if($active_state_value === 'true' || $active_state_value === true)
            $active_state = true;

        if(mb_strlen($request->get('message')) > 960)
        {
            $response['error']      = 1;
            $response['error_msg']  = 'Your message must be less than 960 characters long.';

            return $response;
        }

        // Validate that the workflow_uid provided actually exists
        if($request->has('workflow_uid') && $request->get('workflow_uid') !== null)
        {
            $workflow = $page->workflows()->where('uid', $request->get('workflow_uid'))->first();
            if( ! $workflow)
            {
                $response['error']      = 1;
                $response['error_msg']  = 'There is no workflow with the given uid.';
            }
        }

        $message = $request->get('message');
        if($message === null)
            $message = '';

        // TODO: Validate this input
        $trigger->inclusion_keywords    = $request->get('inclusion_keywords') ?? '';
        $trigger->exclusion_keywords    = $request->get('exclusion_keywords') ?? '';
        $trigger->message               = $message;
        $trigger->workflow_uid          = $request->get('workflow_uid') ?? null;
        $trigger->active                = $active_state;
        $trigger->save();

        $response = [
            'success' => true
        ];

        return $response;
    }
}
