<?php

namespace App\Http\Controllers;

use App\ChatmaticFeedUpdate;
use Illuminate\Http\Request;

class FeedUpdateController extends Controller
{

    public function index(Request $request)
    {
        $updates                    = \App\ChatmaticFeedUpdate::all();

        return view('updates.index')
            ->with('updates', $updates);
    }

    public function create(Request $request)
    {
        return view('updates.create');
    }

    public function save(Request $request)
    {

        // retrieve the data
        $update_text = $request->input('update_text');

        // save the data
        $new_update                = new ChatmaticFeedUpdate;
        $new_update->content       = $update_text;

        $new_update->save();

        return redirect('feed_updates');
    }

    public function edit(Request $request, $update_id)
    {
        // Find out the update
        $update = ChatmaticFeedUpdate::find($update_id);

        return view('updates.create')
                ->with('update',$update);
    }

    public function update(Request $request, $update_id)
    {
        // retrieve the data
        $update_text = $request->input('update_text');

        // Find out the update
        $update = ChatmaticFeedUpdate::find($update_id);

        $update->content        = $update_text;
        $update->save();

        return redirect('feed_updates');
    }

    public function delete(Request $request, $update_id)
    {
        // Find out the update
        $update = ChatmaticFeedUpdate::find($update_id);

        $update->delete();

        return redirect('feed_updates');
    }
}