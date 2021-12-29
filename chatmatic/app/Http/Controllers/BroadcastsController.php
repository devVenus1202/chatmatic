<?php

namespace App\Http\Controllers;

use App\TriggerConfBroadcast;
use Illuminate\Http\Request;

class BroadcastsController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $broadcasts = TriggerConfBroadcast::orderBy('uid', 'desc')->paginate(25);

        return view('broadcasts.index')
            ->with('broadcasts', $broadcasts);
    }
}
