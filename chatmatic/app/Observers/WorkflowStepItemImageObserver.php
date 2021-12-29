<?php

namespace App\Observers;

use App\OutboundLink;
use App\WorkflowStepItemImage;

class WorkflowStepItemImageObserver
{
    /**
     * Handle the workflow step item image "created" event.
     *
     * @param WorkflowStepItemImage $workflowStepItemImage
     * @throws \Exception
     */
    public function created(WorkflowStepItemImage $workflowStepItemImage)
    {
        if($workflowStepItemImage->redirect_url !== '')
        {
            // Generate a link
            $link = OutboundLink::findOrCreateNewLink($workflowStepItemImage->redirect_url, null, $workflowStepItemImage->uid);
            $workflowStepItemImage->outbound_link_uid = $link->uid;
            $workflowStepItemImage->save();
        }
    }

    /**
     * Handle the workflow step item image "updated" event.
     *
     * @param WorkflowStepItemImage $workflowStepItemImage
     * @throws \Exception
     */
    public function updated(WorkflowStepItemImage $workflowStepItemImage)
    {
        if($workflowStepItemImage->redirect_url !== '')
        {
            // Generate a link or find the existing and confirm it's the same
            $link = OutboundLink::findOrCreateNewLink($workflowStepItemImage->redirect_url, null,  $workflowStepItemImage->uid);
            if($link->uid !== $workflowStepItemImage->outbound_link_uid)
            {
                $workflowStepItemImage->outbound_link_uid = $link->uid;
                $workflowStepItemImage->save();
            }
        }
    }

    /**
     * Handle the workflow step item image "deleted" event.
     *
     * @param  \App\WorkflowStepItemImage  $workflowStepItemImage
     * @return void
     */
    public function deleted(WorkflowStepItemImage $workflowStepItemImage)
    {
        //
    }

    /**
     * Handle the workflow step item image "restored" event.
     *
     * @param  \App\WorkflowStepItemImage  $workflowStepItemImage
     * @return void
     */
    public function restored(WorkflowStepItemImage $workflowStepItemImage)
    {
        //
    }

    /**
     * Handle the workflow step item image "force deleted" event.
     *
     * @param  \App\WorkflowStepItemImage  $workflowStepItemImage
     * @return void
     */
    public function forceDeleted(WorkflowStepItemImage $workflowStepItemImage)
    {
        //
    }
}
