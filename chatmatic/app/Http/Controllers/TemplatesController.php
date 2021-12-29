<?php

namespace App\Http\Controllers;

use App\Jobs\PushTemplateToNewWorkflow;
use App\Page;
use App\WorkflowTemplate;
use Illuminate\Http\Request;

class TemplatesController extends Controller
{
    /**
     * @param Request $request
     * @return $this
     */
    public function index(Request $request)
    {
        if($request->has('search'))
        {
            $templates = WorkflowTemplate::search($request->get('search'))->get();
        }
        else{
            $templates = WorkflowTemplate::orderBy('uid', 'desc')->paginate(20);
        }

        return view('templates.index')
            ->with('templates', $templates);
    }

    /**
     * @param Request $request
     * @param $template_id
     * @return $this
     */
    public function show(Request $request, $template_id)
    {
        $template = WorkflowTemplate::find($template_id);

        return view('templates.show')
            ->with('template', $template);
    }

    /**
     * @param Request $request
     * @param $template_id
     * @return $this
     */
    public function publish(Request $request, $template_id)
    {
        //$template = WorkflowTemplate::where('public',true)->where('uid',$template_id)->fisrt();
        $template = WorkflowTemplate::find($template_id);
        //$template = WorkflowTemplate::where('uid',$template_id)->where('public',true)->fisrt();

        if($template->published)
        {
            $template->published = false;
        }
        else
        {
            $template->published = true;
        }

        $template->save();

        return view('templates.show')
            ->with('template', $template);
    }

    /**
     * @param Request $request
     * @param $template_id
     * @return $this
     */
    public function archive(Request $request, $template_id)
    {
        $template = WorkflowTemplate::find($template_id);

        if($template->archived)
        {
            $template->archived = false;
        }
        else
        {
            $template->archived         = true;
            $template->public           = false;
            $template->published        = false;
        }

        $template->save();

        return view('templates.show')
            ->with('template', $template);
    }

    /**
     * Push this template to a Page as a new Workflow
     *
     * @param Request $request
     * @param $template_id
     * @return false|string
     * @throws \Exception
     */
    public function pushToPage(Request $request, $template_id)
    {
        $template   = WorkflowTemplate::find($template_id);
        $page_uids  = $request->get('page_uid');
        $new_name   = $request->get('workflow_name');

        // 'page_uid' will be a comma delimited string of page_uids
        foreach(explode(',', $page_uids) as $page_uid)
        {
            // Push the template to the page
            $parameters['template_uid']         = $template->uid;
            $parameters['page_uid']             = $page_uid;
            $parameters['new_workflow_name']    = $new_name;

            $this->dispatch(new PushTemplateToNewWorkflow($parameters));
        }

        return json_encode([
            'success' => true
        ]);
    }

    /**
     * Show a form to edit the template info on the admin
     *
     * @param Request $request
     * @param $template_id
     * @return $this
     * @throws \Exception
     */
    public function edit(Request $request, $template_id)
    {
        $template = WorkflowTemplate::find($template_id);

        return view('templates.edit')
        ->with('template',$template);
    }

    /**
     * Update the needed data for the template
     *
     * @param Request $request
     * @param $template_id
     * @return redirect
     * @throws \Exception
     */
    public function update(Request $request, $template_id)
    {
        $template = WorkflowTemplate::find($template_id);

        $template->name         = $request->get('name');
        $template->category     = $request->get('category');
        $template->description  = $request->get('description');
        $template->price         = $request->get('price');

        $template->save();

        return redirect()->route('template-show', ['template_id' => $template->uid]);

    }

    public function market(Request $request)
    {
        $purchases = \App\StripePurchase::where('type','template')->whereNotNull('template_uid')->where('total','!=', 0)->orderBy('uid','desc')->paginate(20);

        return view('templates.market')->with('purchases',$purchases);
    }

    public function onMarket(Request $request)
    {
        $templates = WorkflowTemplate::where('published','1')->orderBy('uid','desc')->paginate(20);
        
        return view('templates.on_market')->with('templates',$templates);
    }

    public function sold(Request $request, $template_id)
    {
        $template = WorkflowTemplate::find($template_id);
        $transactions = \App\StripePurchase::where('type','template')->where('template_uid',$template_id)->orderBy('uid','desc')->paginate(20);

        return view('templates.sold')->with('transactions',$transactions)->with('template',$template);

    }

}
