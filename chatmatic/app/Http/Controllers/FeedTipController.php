<?php

namespace App\Http\Controllers;

use App\ChatmaticFeedTip;
use Illuminate\Http\Request;

class FeedTipController extends Controller
{

    public function index(Request $request)
    {
        $tips                    = \App\ChatmaticFeedTip::all();

        return view('tips.index')
            ->with('tips', $tips);
    }

    public function create(Request $request)
    {
        return view('tips.create');
    }

    public function save(Request $request)
    {

        // retrieve the data
        $tip_text = $request->input('tip_text');

        // save the data
        $new_tip                = new ChatmaticFeedTip;
        $new_tip->content       = $tip_text;

        $new_tip->save();

        return redirect('feed_tips');
    }

    public function edit(Request $request, $tip_id)
    {
        // Find out the update
        $tip = ChatmaticFeedTip::find($tip_id);

        return view('tips.create')
                ->with('tip',$tip);
    }

    public function update(Request $request, $tip_id)
    {
        // retrieve the data
        $tip_text = $request->input('tip_text');

        // Find out the update
        $tip = ChatmaticFeedTip::find($tip_id);

        $tip->content        = $tip_text;
        $tip->save();

        return redirect('feed_tips');
    }

    public function delete(Request $request, $tip_id)
    {
        // Find out the update
        $tip = ChatmaticFeedTip::find($tip_id);

        $tip->delete();

        return redirect('feed_tips');
    }
}