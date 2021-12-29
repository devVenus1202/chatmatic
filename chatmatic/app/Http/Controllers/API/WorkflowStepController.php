<?php

namespace App\Http\Controllers\API;

use App\WorkflowStep;
use App\WorkflowTrigger;
use Illuminate\Http\Request;

class WorkflowStepController extends BaseController
{

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function favorite(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $workflow_step_uid = $request->get('step_id');
        // Get the workflow step (also confirming it's attached to the page provided)
        $workflow_step = WorkflowStep::where('uid', $workflow_step_uid)->where('page_uid', $page_uid)->first();

        if( ! $workflow_step)
            return ['success' => false, 'error' => 'Workflow Step ID mismatch'];

        $workflow_step->favorite = true;
        $workflow_step->save();

        $response = ['success' => true];

        return $response;
    }

    /**
     * @param Request $request
     * @param $page_uid
     * @return array
     */
    public function unFavorite(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        $workflow_step_uids = $request->get('step_ids');
        // Loop through provided workflow_step_uids un-favoriting them
        foreach($workflow_step_uids as $workflow_step_uid)
        {
            // Get the workflow step (also confirming it's attached to the page provided)
            $workflow_step = WorkflowStep::where('uid', $workflow_step_uid)->where('page_uid', $page_uid)->first();

            if( ! $workflow_step)
                return ['success' => false, 'error' => 'Workflow Step ID mismatch'];

            $workflow_step->favorite = false;
            $workflow_step->save();
        }

        $response = ['success' => true];

        return $response;
    }

    public function exportJson(Request $request, $page_uid)
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

        $flow_trigger_uid = $request->get('flow_trigger_uid');
        $flow_trigger = WorkflowTrigger::find($flow_trigger_uid);

        if ( ! isset($flow_trigger) || $flow_trigger->page_uid != $page_uid )
        {
            $response['error']          = 1;
            $response['error_msg']      = 'No trigger found with the uid '.$flow_trigger_uid. ' for page '.$page->fb_name;

            return $response;
        }

        $workflow = $flow_trigger->workflow()->first();
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

        return $response;
    }
}
