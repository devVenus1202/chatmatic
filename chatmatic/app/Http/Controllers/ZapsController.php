<?php

namespace App\Http\Controllers;

use App\Jobs\PushTemplateToNewWorkflow;
use App\Page;
use App\WorkflowTemplate;
use App\ZapierWebhookSubscription;
use Illuminate\Http\Request;

class ZapsController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $zaps = ZapierWebhookSubscription::orderBy('uid', 'desc')->paginate(25);

        return view('zaps.index')->with('zaps', $zaps);
    }

    /**
     * @param Request $request
     * @param $zap_uid
     * @return ZapierWebhookSubscription|ZapierWebhookSubscription[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function show(Request $request, $zap_uid)
    {
        $zap = ZapierWebhookSubscription::find($zap_uid);

        return $zap;
    }
}
