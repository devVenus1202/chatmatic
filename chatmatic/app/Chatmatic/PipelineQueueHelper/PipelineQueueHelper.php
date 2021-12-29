<?php


namespace App\Chatmatic\PipelineQueueHelper;


use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class PipelineQueueHelper
{

    /**
     * @param Carbon $date
     * @return array
     */
    public function generateKeysArray(Carbon $date)
    {
        $formatted_date = $date->format('YmdHi');

        $keys = [
            // Incoming events/websocket payloads from facebook
            'incoming::'.$formatted_date,
            // Queued events
            'queued::facebook.page.messaging.optin::'.$formatted_date,
            'queued::facebook.page.messaging.message::'.$formatted_date,
            'queued::facebook.page.messaging.read::'.$formatted_date,
            'queued::facebook.page.messaging.postback::'.$formatted_date,
            'queued::facebook.page.messaging.postback.referral::'.$formatted_date,
            'queued::facebook.page.messaging.postback.get-started::'.$formatted_date,
            'queued::facebook.page.messaging.quick_rep::'.$formatted_date,
            'queued::facebook.page.changes::'.$formatted_date,
            // Events firing Facebook API requests
            'request::facebook.page.get-started.send::'.$formatted_date,
            'request::facebook.page.referral.send::'.$formatted_date,
            'request::facebook.page.comment.send::'.$formatted_date,
            'request::facebook.page.optin.send::'.$formatted_date,
            'request::facebook.page.postback.send::'.$formatted_date,
            'request::facebook.page.message.send::'.$formatted_date,
            // Events firing internal API requests
            'request::internal.integrations::'.$formatted_date,
            'request::internal.tag::'.$formatted_date,
            'request::internal.new_subscriber::'.$formatted_date,
            'request::internal.phone_update::'.$formatted_date,
            'request::internal.email_update::'.$formatted_date,
            'request::internal.attribute_update::'.$formatted_date,
            // Events with execution time
            'processed::facebook.page.process_broadcast_request::'.$formatted_date,
            'processed::facebook.page.process_get_started::'.$formatted_date,
            'processed::facebook.page.process_referral_event::'.$formatted_date,
            'processed::facebook.page.process_comment_event::'.$formatted_date,
            'processed::facebook.page.process_post_event::'.$formatted_date,
            'processed::facebook.page.process_reaction_event::'.$formatted_date,
            'processed::facebook.page.process_optin_event::'.$formatted_date,
            'processed::facebook.page.process_postback_event::'.$formatted_date,
            'processed::facebook.page.process_message_read::'.$formatted_date,
            'processed::facebook.page.process_page_message_event::'.$formatted_date,
            'processed::facebook.page.process_page_message_event.postback::'.$formatted_date,
            'processed::facebook.page.send_broadcast_batch::'.$formatted_date,
        ];

        return $keys;
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getValues($keys = [])
    {
        // Placeholder return array
        $result = [];

        // Loop through the provided keys to acquire their values
        foreach($keys as $key)
        {
            // We'll quickly parse the provided key to get the date string out of it
            $exploded_key   = explode('::', $key);

            switch(count($exploded_key))
            {
                case 2:
                    // Two values means it's incoming or facebook api requests...
                    $formatted_key = $exploded_key[0];

                    // Get the actual value from storage
                    $value = Redis::get($key);

                    break;
                case 3:
                    if(mb_stristr($key, 'processed::'))
                    {
                        // This will be represented by a list of execution times of each event - we'll want to sum and average them
                        $formatted_key = $exploded_key[0].'::'.$exploded_key[1];

                        // Get the actual values from storage
                        $values         = Redis::lrange($key, 0, Redis::llen($key));
                        $total_time     = 0;
                        $value          = ['event_count' => 0, 'total_time' => 0, 'long_time' => 0, 'median_time' => 0, 'raw_values' => []];

                        if(count($values))
                        {
                            // Get a sum of the values
                            // Get the highest value
                            $value_count    = count($values);
                            $highest_value  = 0;
                            foreach($values as $k => $v)
                            {
                                // Convert $values to float (from string)
                                $v          = (float) $v;
                                // Resetting the value in the array with the float type so we can use it below to determine the median
                                $values[$k] = $v;

                                // generate sum of all values
                                $total_time += $v;

                                // extract the highest value as our 'long_time'
                                if($v > $highest_value)
                                {
                                    $highest_value = $v;
                                }
                            }

                            // Generate a median value
                            $median_value = $this->calculateMedian($values);

                            $value = [
                                'event_count'   => $value_count,
                                'total_time'    => $total_time,
                                'long_time'     => $highest_value,
                                'median_time'   => $median_value,
                                'raw_values'    => $values,
                            ];
                        }
                    }
                    else
                    {
                        // Three values means it's a queued event...
                        $formatted_key = $exploded_key[0].'::'.$exploded_key[1];

                        // Get the actual value from storage
                        $value = Redis::get($key);
                    }

                    break;
            }

            // Populate the returned array
            $result[$formatted_key] = $value;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function cacheKeys()
    {
        return [
            'daily' => 'pipeline-stats-daily'
        ];
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function emptyCache()
    {
        foreach($this->cacheKeys() as $key => $value)
        {
            \Cache::delete($value);
        }
    }

    /**
     * @param $data_array
     * @return float|int
     */
    public function calculateMedian($data_array)
    {
        $median_values  = $data_array;
        sort($median_values);
        $values_count   = count($median_values);
        $median_index   = (int) floor($values_count / 2);

        // If it's even we'll use the average of the two middle values
        if($values_count === 0)
        {
            $median_value = 0;
        }
        elseif($values_count % 2 === 0)
        {
            $median_value = ($median_values[$median_index - 1] + $median_values[$median_index]) / 2;
        }
        else// else we'll use the middle value
        {
            $median_value = $median_values[$median_index];
        }

        return $median_value;
    }

    /**
     * @return array
     */
    public function valuesForThisWeekByDay()
    {
        $max_days       = 7;
        $current_day    = 0;
        $results        = [];

        $start = time();

        // Loop for each day we want to collect
        while($current_day < $max_days)
        {
            $date = Carbon::now()->subDays($current_day);

            $values = $this->valuesForDay($date);
            foreach($values as $key => $value)
            {
                if(isset($results[$date->format('Ymd')][$key]))
                {
                    $results[$date->format('Ymd')][$key] += $value;
                }
                else
                {
                    $results[$date->format('Ymd')][$key] = $value;
                }
            }

            $current_day++;
        }

        echo 'elapsed: '.(time() - $start).' seconds'.PHP_EOL;

        return $results;
    }

    /**
     * @param Carbon $date
     * @param bool $force_fresh
     * @return array
     */
    public function valuesForDay(Carbon $date, $force_fresh = false)
    {
        // Create a start date/time that starts at 00:00:00 of the provided day
        $mdy        = $date->format('m/d/Y');
        $time       = strtotime($mdy.' 00:00:00');
        $date       = Carbon::createFromTimestamp($time);
        $results    = [];
        $cache_key  = $this->cacheKeys()['daily'];

        // If $force_fresh is false and there is cached data we'll return that instead of processing
        if( ! $force_fresh)
        {
            // Get container array
            $cached_array = \Cache::get($cache_key);

            // If there's a value for this day we'll return it
            if($cached_array && isset($cached_array[$date->format('Ymd')]))
                return $cached_array[$date->format('Ymd')];
        }

        // Loop through all mins of this day
        $min_count      = 1440;
        $current_min    = 0;
        while($current_min < $min_count)
        {
            if($current_min > 0)
                $datetime = $date->addMinutes(1);
            else
                $datetime = $date;

            // Generate the keys
            $keys   = $this->generateKeysArray($datetime);

            // Get the values
            $values = $this->getValues($keys);

            // Pass the values into our returned array
            foreach($values as $key => $value)
            {
                // If it's an array that means it's from the processing events which have more data - we'll combine them
                if(is_array($value))
                {
                    // Get the pre-existing array in the results, or start fresh with a new one
                    if(isset($results[$key]))
                    {
                        $temp_array = $results[$key];
                    }
                    else
                    {
                        $temp_array = [];
                    }

                    foreach($value as $k => $v)
                    {
                        // If it's the 'long_time' key we'll just make sure it's updated if it's higher
                        if($k === 'long_time')
                        {
                            // If there is no setting yet in the temp array for 'long_time' we'll set it to this one
                            if( ! isset($temp_array[$k]))
                            {
                                $temp_array[$k] = $v;
                            }
                            // Otherwise, if there is a value and it's less than our current we'll overwrite it
                            elseif($temp_array[$k] < $v)
                            {
                                $temp_array[$k] = $v;
                            }
                        }
                        elseif($k === 'median_time')
                        {
                            // Do nothing with the median times - they can't be combined, we'll need to calculate a new one from a combined set of data for the day
                        }
                        elseif($k === 'raw_values')
                        {
                            // This is where we'll get setup for our new 'median_time' by combining all of the 'raw_values' arrays into one and calculating a new median
                            if(count($v))
                            {
                                $raw_processing_count_values[$key][] = $v;
                            }
                        }
                        // Otherwise we'll sum the values
                        else
                        {
                            if(isset($temp_array[$k]))
                            {
                                $temp_array[$k] += $v;
                            }
                            else
                            {
                                $temp_array[$k] = $v;
                            }
                        }
                    }

                    // Unset this now that we've pushed it off to $raw_processing_count_values
                    unset($temp_array['raw_values']);

                    $results[$key] = $temp_array;
                }
                else // It's just a numeric value (counter) and we'll sum it with the ongoing value
                {
                    if(isset($results[$key]))
                    {
                        $results[$key] += $value;
                    }
                    else
                    {
                        $results[$key] = $value;
                    }
                }
            }

            $current_min++;
        }

        // Quickly loop through and do some processing of the totals
        foreach($results as $key => $value)
        {
            // If the $value is an array it's one of the processing keys and we'll want to do some math stuff here
            if(is_array($value) && $value['event_count'] > 0)
            {
                $event_count    = $value['event_count'];
                $total_time     = $value['total_time'];

                // Calculate the average time it took to complete the events
                $average_time = round($total_time / $event_count, 2);

                // Attach the average time back to the results array
                $results[$key]['avg_time'] = round($average_time, 2);

                // Calculate the median time
                $median_time = 0;
                //$results[$key]['raw_values'] = [];
                if(isset($raw_processing_count_values[$key]) && count($raw_processing_count_values[$key]))
                {
                    $raw_values = $raw_processing_count_values[$key];
                    $raw_values = array_merge(...$raw_values);
                    $median_time = $this->calculateMedian($raw_values);

                    // Set the raw values
                    //sort($raw_values);
                    //$results[$key]['raw_values'] = $raw_values; // Don't return raw_values for now
                }
                $results[$key]['median_time'] = round($median_time, 2);

                $results[$key]['total_time'] = round($results[$key]['total_time'], 2);
                $results[$key]['long_time'] = round($results[$key]['long_time'], 2);
            }
            elseif(is_array($value)) // If it's an array but has an 'event_count' < 1 we'll just dump these fillers in
            {
                $results[$key]['avg_time'] = 0;
                $results[$key]['median_time'] = 0;
            }
        }

        // If it's not today's values we'll cache it
        if($date->format('Ymd') !== Carbon::now()->format('Ymd'))
        {
            $cached_array = \Cache::get($cache_key);
            $cached_array[$date->format('Ymd')] = $results;

            \Cache::forever($cache_key, $cached_array);
        }

        return $results;
    }

    /**
     * Return the data from the last x minutes
     *
     * @param int $minutes
     * @return array
     */
    public function lastNMinutes($minutes = 5)
    {
        $min    = 0;
        $return = [];
        while($min < $minutes) {
            $date           = Carbon::now('UTC');
            $new_date       = clone $date->subMinutes($min);
            $keys           = $this->generateKeysArray($new_date);
            $values         = $this->getValues($keys);
            $values['date'] = $new_date;
            $return[]       = $values;
            $min++;
        }

        return $return;
    }

    public function aggregateMinutesData($data)
    {
        $total_incoming = 0;
        $total_queued = 0;
        $total_processed = 0;
        $total_requests = 0;

        $processed_data = [];

        foreach($data as $key => $values)
        {
            $total_incoming += $values['incoming'];

            foreach($values as $k => $v)
            {
                if(stristr($k, 'queued::'))
                {
                    $total_queued += $v;
                }
                elseif(stristr($k, 'processed::'))
                {
                    // Count of total 'processed::' events
                    $total_processed += $v['event_count'];

                    // Maintain a count of each specific key that was processed
                    if( ! isset($processed_data[$k]['count']))
                    {
                        $processed_data[$k]['count']    = $v['event_count'];
                        $processed_data[$k]['time']     = $v['total_time'];
                    }
                    else
                    {
                        $processed_data[$k]['count']    += $v['event_count'];
                        $processed_data[$k]['time']     += $v['total_time'];
                    }
                }
                elseif(stristr($k, 'request::'))
                {
                    $total_requests += $v;
                }
            }
        }

        $response = [
            'total_incoming'    => $total_incoming,
            'total_queued'      => $total_queued,
            'total_processed'   => $total_processed,
            'total_requests'    => $total_requests,
            'processed_data'    => $processed_data
        ];

        return $response;
    }

}