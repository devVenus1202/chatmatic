<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Workflow;
use App\WorkflowStepItemImage;
use App\WorkflowStepItemVideo;

class MediaAttachmentApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $workflow;
    
    public function __construct($workflow_uid)
    {
        //
        $this->workflow = Workflow::find($workflow_uid);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $workflow = $this->workflow;
        if (!isset($workflow))
            return;

        $workflow_steps = $workflow->workflowSteps()->get();

        foreach ($workflow_steps as $workflow_step) {
            if ($workflow_step->step_type == 'items') {
                $workflow_step_items = $workflow_step->workflowStepItems()->get();
                if (!isset($workflow_step_items))
                    break;
                foreach ($workflow_step_items as $workflow_step_item) {
                    if ($workflow_step_item->item_type === "image") {
                        $image = WorkflowStepItemImage::where('workflow_step_item_uid', $workflow_step_item->uid)->first();
                        if ($image && $workflow_step_item->workflowStepItemMaps()->count() > 0 && !$image->fb_attachment_id)
                            $image->getFacebookAttachmentId();
                    }
                    else if ($workflow_step_item->item_type === 'video') {
                        $video = WorkflowStepItemVideo::where('workflow_step_item_uid', $workflow_step_item->uid)->first();
                        if ($video && !$video->fb_attachment_id)
                            $video->getFacebookAttachmentId();
                    }
                }
            }
        }
    }
}
