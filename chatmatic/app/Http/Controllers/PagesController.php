<?php

namespace App\Http\Controllers;

use App\TriggerConfBroadcast;
use App\Page;
use App\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PagesController extends Controller
{

    public function index(Request $request)
    {
        if($request->has('orderBy'))
            $pages = Page::OrderByRequest($request);
        else
            $pages = Page::orderBy('uid', 'DESC');

        if($request->has('search'))
            $pages = Page::search($request->get('search'));

        $pages = $pages->paginate(20);

        if($request->has('search'))
            return view('pages.search')->
                with('pages', $pages);

        return view('pages.index')->
            with('pages', $pages);
    }

    public function connected250(Request $request)
    {
        $pages = Page::where('is_connected', 1)
            ->where('subscribers', '>=', 250)
            ->orderBy('subscribers', 'desc')
            ->paginate(50);

        return view('pages.index')->
            with('pages', $pages);
    }

    public function show(Request $request, $page_id)
    {
        $page = Page::find($page_id);

        return view('pages.show')->
            with('page', $page);
    }

    public function showBroadcasts(Request $request, $page_id)
    {
        $page = Page::find($page_id);
        $broadcastsTriggers = $page->workflowTriggers()
                                    ->where('type','=', 'broadcast')
                                    ->orderBy('uid', 'desc')
                                    ->get();
        $broadcasts = array();
        foreach($broadcastsTriggers as $trigger){
            array_push($broadcasts,$trigger->broadcast);
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($broadcasts);
        $perPage = 25;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $broadcasts= new LengthAwarePaginator($itemCollection , count($itemCollection), $perPage);
        $broadcasts->setPath($request->url());

        return view('pages.broadcasts')->
            with('page', $page)->
            with('broadcasts', $broadcasts);
    }

    public function showBroadcastMessages(Request $request, $page_uid, $broadcast_uid)
    {
        $page       = Page::find($page_uid);
        $broadcast  = TriggerConfBroadcast::find($broadcast_uid);

        return view('pages.broadcast')->
            with('page', $page)->
            with('broadcast', $broadcast);
    }

    public function showWorkflowTriggers(Request $request, $page_id)
    {
        $page = Page::find($page_id);

        return view('pages.campaigns')->
            with('page', $page);
    }

    public function showWorkflowTrigger(Request $request, $page_id, $workflow_trigger_uid)
    {
        $page       = Page::find($page_id);
        $workflowTrigger   = $page->workflowTriggers()->where('uid', $workflow_trigger_uid)->first();

        return view('pages.campaign')->
            with('page', $page)->
            with('workflowTrigger', $workflowTrigger);
    }

    public function showWorkflows(Request $request, $page_id)
    {
        $page = Page::find($page_id);

        return view('pages.workflows')->
            with('page', $page);
    }

    public function showWorkflow(Request $request, $page_id, $workflow_id)
    {
        $page       = Page::find($page_id);
        $workflow   = $page->workflows()->where('uid', $workflow_id)->first();

        return view('pages.workflow')->
            with('page', $page)->
            with('workflow', $workflow);
    }

    public function showTriggers(Request $request, $page_id)
    {
        $page = Page::find($page_id);

        return view('pages.triggers')->
            with('page', $page);
    }

    public function showComments(Request $request, $page_id)
    {
        $page       = Page::find($page_id);
        $comments   = $page->comments()->orderBy('uid', 'desc')->paginate();

        return view('pages.comments')
            ->with('page', $page)
            ->with('comments', $comments);
    }

    public function showPosts(Request $request, $page_id)
    {
        $page   = Page::find($page_id);
        $posts  = $page->posts()->orderBy('uid', 'desc')->paginate();

        return view('pages.posts')
            ->with('page', $page)
            ->with('posts', $posts);
    }

    public function showPost(Request $request, $page_id, $post_id)
    {
        $page   = Page::find($page_id);
        $post   = $page->posts()->where('uid', $post_id)->first();

        if( ! $post)
        {
            alert()->danger('Post not found');
            return redirect('/');
        }

        return view('pages.post')
            ->with('page', $page)
            ->with('post', $post);
    }

    public function showSubscribers(Request $request, $page_id)
    {
        $page           = Page::find($page_id);
        $subscribers    = $page->subscribers()->orderBy('uid', 'desc')->paginate();

        return view('pages.subscribers')
            ->with('page', $page)
            ->with('subscribers', $subscribers);
    }

    public function showSubscriber(Request $request, $page_id, $subscriber_id)
    {
        $page           = Page::find($page_id);
        $subscriber     = $page->subscribers()->where('uid', $subscriber_id)->first();

        if( ! $subscriber)
        {
            alert()->danger('Subscriber not found');
            return redirect('/');
        }

        return view('pages.subscriber')
            ->with('page', $page)
            ->with('subscriber', $subscriber);
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showCustomFields(Request $request, $page_id)
    {
        $page           = Page::find($page_id);

        $custom_fields  = $page->customFields()->orderBy('uid', 'desc')->paginate(25);

        return view('pages.custom-fields')
            ->with('custom_fields', $custom_fields)
            ->with('page', $page);
    }

    /**
     * @param Request $request
     * @param $page_id
     * @param $custom_field_uid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showCustomField(Request $request, $page_id, $custom_field_uid)
    {
        $page           = Page::find($page_id);
        $custom_field   = $page->customFields()->with('customFieldResponses')->where('uid', $custom_field_uid)->first();

        return view('pages.custom-field')
            ->with('custom_field', $custom_field)
            ->with('page', $page);
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showZapierEvents(Request $request, $page_id)
    {
        $page           = Page::find($page_id);
        $events         = $page->zapierEventLogs()->orderBy('uid', 'desc')->paginate(25);

        return view('pages.zapier-events.index')
            ->with('events', $events)
            ->with('page', $page);
    }

    /**
     * @param Request $request
     * @param $page_id
     * @param $event_uid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showZapierEvent(Request $request, $page_id, $event_uid)
    {
        $page           = Page::find($page_id);
        $event          = $page->zapierEventLogs()->where('uid', $event_uid)->first();

        return view('pages.zapier-events.show')
            ->with('event', $event)
            ->with('page', $page);
    }

    /**
     * Archive a workflow
     *
     * @param Request $request
     * @param $page_id
     * @param $workflow_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function archiveWorkflow(Request $request, $page_id, $workflow_id)
    {
        $page                = Page::find($page_id);
        $workflowTrigger     = $page->workflowTriggers->where('uid', $workflow_id)->first();

        $workflowTrigger->archive();
        alert()->success('Flow Trigger archived');

        return redirect('/page/'.$page_id.'/flow_trigger/'.$workflow_id);
    }

    /**
     * Un-archive a workflow
     *
     * @param Request $request
     * @param $page_id
     * @param $workflow_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function unArchiveWorkflow(Request $request, $page_id, $workflow_id)
    {
        $page                  = Page::find($page_id);
        $workflowTrigger       = $page->workflowTriggers->where('uid', $workflow_id)->first();

        $workflowTrigger->unArchive();
        alert()->success('Workflow un-archived');

        return redirect('/page/'.$page_id.'/flow_trigger/'.$workflow_id);
    }

    /**
     * Delete a workflow (actually deletes it and it's related records)
     *
     * @param Request $request
     * @param $page_id
     * @param $workflow_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteWorkflow(Request $request, $page_id, $workflow_id)
    {
        $page               = Page::find($page_id);
        $workflow           = $page->workflows->where('uid', $workflow_id)->first();
 
        $workflow->deleteWorkflowData();
        $workflow->delete();
        alert()->success('Workflow deleted');

        return redirect('/page/'.$page_id);
    }

    /**
     * Disconnect a page from our Facebook App
     *
     * @param Request $request
     * @param $page_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function disconnect(Request $request, $page_id)
    {
        $page           = Page::find($page_id);

        $page->is_connected = 0;
        $page->save();

        alert()->success('Page disconnected');

        return redirect('/page/'.$page_id);
    }

    /**
     * Create a template from an existing workflow
     *
     * @param Request $request
     * @param $page_id
     * @param $workflow_id
     * @return false|string
     */
    public function createTemplate(Request $request, $page_id, $workflow_id)
    {
        $response           = [
            'success'       => true,
            'error'         => 0,
            'error_msg'     => '',
            'template_uid'  => null,
        ];

        $page               = Page::find($page_id);
        $workflow           = $page->workflows()->where('uid', $workflow_id)->first();
        $template_name      = $request->get('template_name');
        $workflow_template  = $workflow->cloneIntoTemplate($template_name);

        if($workflow_template['success'] !== true)
        {
            return $workflow_template;
        }

        $response['success']        = true;
        $response['template_uid']   = $workflow_template['template']->uid;

        return json_encode($response);
    }

    /**
     * @param Request $request
     * @param $page_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function importPSIDs(Request $request, $page_id)
    {
        $page   = Page::find($page_id);
        $psids  = $request->get('psids');
        $psids  = explode(PHP_EOL, $psids);

        // Loop through the given psids checking to confirm they're new for the page
        $new_psids          = 0;
        $total_psids        = 0;
        $psids_for_refresh  = [];
        foreach($psids as $psid)
        {
            $psid = str_replace("\r", '', $psid);

            // Is this psid already in our database?
            $subscriber = $page->subscribers()->where('user_psid', $psid)->first();

            // Not found, let's create it
            if( ! $subscriber)
            {
                $subscriber = Subscriber::create([
                    'page_uid'  => $page->uid,
                    'user_psid' => $psid
                ]);
                if( ! $subscriber)
                {
                    throw new \Exception('Error creating new subscriber');
                }

                // Add this psid to the array we'll post to pipeline for update/refresh of subscriber data
                $psids_for_refresh[] = $psid;

                $new_psids++;
            }

            $total_psids++;
        }

        // Submit the $psids_for_refresh array to pipeline
        if($new_psids > 0)
        {
            /*
             * the url should be http://internal.chatmatic.info/update-subscribers
             * and the parameters should be
             * {'subscribers':[2217502371670882,2871001879607204],'page_uid':'1587'}
             */
            $pipeline_internal_base_url = \Config::get('chatmatic.pipeline_internal_base_url');

            $post_array                 = [
                'page_uid'          => $page->uid,
                'subscribers'       => $psids_for_refresh
            ];

            $curl = curl_init($pipeline_internal_base_url . '/update-subscribers');
            if ($curl === false) {
                $error_message          = 'Unable to push psids to pipeline for update because of internal error. #001';
                throw new \Exception($error_message);
            }
            $curlSetoptResult = curl_setopt_array($curl, array(
                CURLOPT_POST            => 1,
                CURLOPT_RETURNTRANSFER  => 1,
                CURLOPT_POSTFIELDS      => json_encode($post_array)
            ));
            if ($curlSetoptResult === false) {
                $error_message          = 'Unable to push psids to pipeline for update because of internal error. #002';
                throw new \Exception($error_message);
            }
            $curl_result = curl_exec($curl);
            if ($curl_result === false) {
                $error_message          = 'Unable to push psids to pipeline for update because of internal error. #003';
                throw new \Exception($error_message);
            }
            curl_close($curl);

            /*
            $curl_result_json = json_decode($curl_result);

            if (json_last_error() === JSON_ERROR_NONE) {
                $response['count'] = $curl_result_json->count;
            }
            */
        }

        alert()->success($total_psids.' psids submitted, of which '.$new_psids.' were new and added.');

        return redirect('/page/'.$page_id.'/subscribers');
    }
}
