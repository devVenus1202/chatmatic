<?php

namespace App\Jobs;

use App\Page;
use App\WorkflowTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PushTemplateToNewWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $template_uid;
    protected $page_uid;
    protected $new_workflow_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->template_uid         = $data['template_uid'];
        $this->page_uid             = $data['page_uid'];
        $this->new_workflow_name    = null;

        // If a workflow name is passed we'll use it
        if(isset($data['new_workflow_name']))
        {
            $this->new_workflow_name = $data['new_workflow_name'];
        }
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function handle()
    {
        $template = WorkflowTemplate::find($this->template_uid);

        // Push the template to the page
        $page              = Page::find($this->page_uid);
        $template_response = $template->pushToPage($page, $this->new_workflow_name);

        if($template_response['success'] !== true)
        {
            throw new \Exception('Template failed push to Workflow: '.$template_response['error_msg']);
        }
    }
}
