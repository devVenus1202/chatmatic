<?php

namespace App\Http\Controllers;

use App\ZapierEventLog;
use Illuminate\Http\Request;

class ZapierEventLogController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $events = ZapierEventLog::orderBy('uid', 'desc')->paginate(25);

        return view('zapiereventlogs.index')
            ->with('events', $events);
    }

    /**
     * @param Request $request
     * @param $event_uid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, $event_uid)
    {
        $event  = ZapierEventLog::find($event_uid);
        $page   = $event->page;

        return view('zapiereventlogs.show')
            ->with('event', $event)
            ->with('page', $page);
    }
}
