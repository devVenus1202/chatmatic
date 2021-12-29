<?php

namespace App\Http\Controllers;

use App\Charts\EventCountLastHourChart;
use App\Charts\ProcessedCountLastHour;
use Illuminate\Http\Request;

class PipelineController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $aggregated = [];
        $pipeline = new \App\Chatmatic\PipelineQueueHelper\PipelineQueueHelper;

        // Get last 5 min of data
        $data_5 = $pipeline->lastNMinutes(5);

        // Get aggregated data
        $aggregated['5'] = $pipeline->aggregateMinutesData($data_5);

        // Get last 60 min of data
        $data_60 = $pipeline->lastNMinutes(60);
        $aggregated['60'] = $pipeline->aggregateMinutesData($data_60);

        // Get the last 1440 min of data (24 hours)
        $data_day = $pipeline->lastNMinutes(1440);
        $aggregated['1440'] = $pipeline->aggregateMinutesData($data_day);

        // We'll push the raw data array to the view to use
        $return5 = $data_5;
        $return60 = $data_60;

        /**
         * Start charts
         */
        // Generate chart data
        $chart_labels = [];
        $chart_data_values = [];
        foreach($data_60 as $key => $array)
        {
            $event_count = $array['incoming'];
            $time_period = $array['date'];

            if($event_count < 1)
                $event_count = 0;

            $chart_labels[]         = $time_period->format('g:i a');
            $chart_data_values[]    = $event_count;
        }

        // Init the chart
        $event_count_last_hour_chart = new EventCountLastHourChart;
        // Add the labels to the chart
        $event_count_last_hour_chart->labels($chart_labels);
        // Add the data values to the chart
        $event_count_last_hour_chart->dataset('Event Count', 'line', $chart_data_values);
        $event_count_last_hour_chart->minimalist(true);
        $event_count_last_hour_chart->height(150);

        // Start of second chart
        $chart_labels = [];
        $chart_data_values = [];
        // Loop through each min to get a count of processed/queued/requests
        foreach($data_60 as $key => $array)
        {
            // Loop through each data array to get a count for each type of key
            $event_counts = [];
            foreach($array as $event_type => $value)
            {
                // Determine type of event this is
                if(mb_stristr($event_type, 'incoming'))
                    $event_bucket = 'incoming';
                elseif(mb_stristr($event_type, 'queued::'))
                    $event_bucket = 'queued';
                elseif(mb_stristr($event_type, 'request::'))
                    $event_bucket = 'request';
                elseif(mb_stristr($event_type, 'processed::'))
                    $event_bucket = 'processed';
                elseif(mb_stristr($event_type, 'date'))
                    $event_bucket = 'date';

                // Ignore the time key
                if($event_bucket === 'date')
                    continue;

                // Determine the count
                if($event_bucket === 'processed') // If it's a 'processed' type then it's in an array
                {
                    $event_count = $value['event_count'];
                }
                elseif($value !== null) // otherwise the $value is the count
                {
                    $event_count = $value;
                }
                else // if null we'll treat as zero
                {
                    $event_count = 0;
                }

                // Build an array of the event counts for each bucket type
                if(isset($event_counts[$event_bucket]))
                    $event_counts[$event_bucket] += $event_count;
                else
                    $event_counts[$event_bucket] = $event_count;
            }

            // Loop back through the event counts array to add the totals to our data values
            foreach($event_counts as $event_bucket => $event_count)
            {
                $chart_data_values[$event_bucket][] = $event_count;
            }

            $chart_labels[] = $array['date']->format('g:i a');
        }

        // Init the chart
        $processed_count_last_hour_chart = new ProcessedCountLastHour;
        // Add the labels to the chart
        $processed_count_last_hour_chart->labels($chart_labels);
        // Add the data values to the chart
        $processed_count_last_hour_chart->dataset('Processed', 'line', $chart_data_values['processed'])->options([
            'borderColor' => '#4af441',
            'backgroundColor' => '#4af441',
            'fill' => false,
        ]);
        $processed_count_last_hour_chart->dataset('Queued', 'line', $chart_data_values['queued'])->options([
            'borderColor' => '#d942f4',
            'backgroundColor' => '#d942f4',
            'fill' => false,
        ]);
        $processed_count_last_hour_chart->dataset('Request', 'line', $chart_data_values['request'])->options([
            'borderColor' => '#4286f4',
            'backgroundColor' => '#4286f4',
            'fill' => false,
        ]);
        //$processed_count_last_hour_chart->minimalist(true);
        $processed_count_last_hour_chart->height(300);

        /**
         * End charts
         */

        return view('pipeline.index')
            ->with('event_count_last_hour_chart', $event_count_last_hour_chart)
            ->with('processed_count_last_hour_chart', $processed_count_last_hour_chart)
            ->with('values5', $return5)
            ->with('values60', $return60)
            ->with('aggregated', $aggregated);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function incomingByMin(Request $request)
    {
        $pipeline = new \App\Chatmatic\PipelineQueueHelper\PipelineQueueHelper;

        $minutes = 5;
        if($request->has('minutes'))
            $minutes = $request->get('minutes');
        $data = $pipeline->lastNMinutes($minutes);



        return $return;
    }
}
