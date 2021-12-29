<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriberCountHistoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $page_uid)
    {
        $page = $this->getPage($page_uid);
        if($page['error'] === 1)
        {
            return $page;
        }
        /** @var \App\Page $page */
        $page = $page['page'];

        // Set a default value for the # of days history we'll return
        $history_days = 7;
        // Get 'recentDays' from request
        if($request->has('recent_days'))
            $history_days = $request->get('recent_days');

        $response_array = [];
        // Get the most recent results from the subscriber count history
        foreach($page->subscriberCountHistory()->orderBy('uid', 'desc')->take($history_days)->get() as $count_history)
        {
            $response_array[] = [
                'date'  => Carbon::createFromTimestamp(strtotime($count_history->date_utc))->format('Y-m-d'),
                'total'     => $count_history->maximum
            ];
        }

        // Get today's value and replace if we've got one from the database
        $todays_count = $page->subscribers()->count();
        if(isset($response_array[0]))
        {
            if($response_array[0]['date'] === Carbon::now()->format('Y-m-d'))
                $response_array[0]['total'] = $todays_count;
            else
            {
                array_unshift($response_array, [
                    'date'      => Carbon::now()->format('Y-m-d'),
                    'total'     => $todays_count
                ]);
            }
        }

        // Confirm that we're passing enough data...
        if(count($response_array) < $history_days)
        {
            while(count($response_array) < $history_days)
            {
                // Get the last entry, which would be the 'oldest'
                if(count($response_array) > 0)
                    $last_day = $response_array[count($response_array) - 1]['date'];
                else
                    $last_day = Carbon::now()->format('Y-m-d');

                // Create a new entry with a 0 total for the previous day
                $response_array[] = [
                    'date'  => Carbon::createFromTimestamp(strtotime($last_day))->subDay()->format('Y-m-d'),
                    'total'     => 0
                ];
            }
        }

        // Reverse the order of the array so the dates are in sequential order
        $response_array = array_reverse($response_array);

        return $response_array;
    }
}
