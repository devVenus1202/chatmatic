<?php

namespace App\Observers;

use App\OutboundLink;
use App\WorkflowStepItemMap;

class WorkflowStepItemMapObserver
{
    /**
     * Handle the workflow step item map "created" event.
     *
     * @param WorkflowStepItemMap $workflowStepItemMap
     * @throws \Exception
     */
    public function creating(WorkflowStepItemMap $workflowStepItemMap)
    {
        if($workflowStepItemMap->map_action === 'web_url')
        {
            // Generate a link
            $link = OutboundLink::findOrCreateNewLink($workflowStepItemMap->map_action_text, null, null, $workflowStepItemMap);
            $workflowStepItemMap->outbound_link_uid = $link->uid;
        }
        else // If it's not a web_url button then it shouldn't have an outbound_link_uid associated with it any longer
        {
            $workflowStepItemMap->outbound_link_uid = null;
        }
    }

    /**
     * Handle the workflow step item map "updated" event.
     *
     * @param WorkflowStepItemMap $workflowStepItemMap
     * @throws \Exception
     */
    public function updating(WorkflowStepItemMap $workflowStepItemMap)
    {
        if($workflowStepItemMap->map_action === 'web_url')
        {
            // Generate a link or find the existing and confirm it's the same
            $link = OutboundLink::findOrCreateNewLink($workflowStepItemMap->map_action_text, $workflowStepItemMap->uid);
            if($link->uid !== $workflowStepItemMap->outbound_link_uid)
            {
                $workflowStepItemMap->outbound_link_uid = $link->uid;
            }
        }
        else // If it's not a web_url button then it shouldn't have an outbound_link_uid associated with it any longer
        {
            $workflowStepItemMap->outbound_link_uid = null;
        }
    }

    /**
     * Handle the workflow step item map "deleted" event.
     *
     * @param  \App\WorkflowStepItemMap  $workflowStepItemMap
     * @return void
     */
    public function deleted(WorkflowStepItemMap $workflowStepItemMap)
    {
        //
    }

    /**
     * Handle the workflow step item map "restored" event.
     *
     * @param  \App\WorkflowStepItemMap  $workflowStepItemMap
     * @return void
     */
    public function restored(WorkflowStepItemMap $workflowStepItemMap)
    {
        //
    }

    /**
     * Handle the workflow step item map "force deleted" event.
     *
     * @param  \App\WorkflowStepItemMap  $workflowStepItemMap
     * @return void
     */
    public function forceDeleted(WorkflowStepItemMap $workflowStepItemMap)
    {
        //
    }
}
