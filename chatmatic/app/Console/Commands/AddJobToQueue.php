<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddJobToQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatmatic-pipeline:add-job-to-queue {--job=} {--payload=} {--queue=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a job to the processing queue from the chatmatic pipeline';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     *
     * Sample JSON:
     * {"sender":{"id":"<PSID>"},"recipient":{"id":"<PAGE_ID>"},"timestamp":1458692752478,"message":{"mid":"mid.1457764197618:41d102a3e1ae206a38","text":"hello, world!","quick_reply": {"payload": "<DEVELOPER_DEFINED_PAYLOAD>"}}}
     * @return mixed
     */
    public function handle()
    {
        $job_string     = $this->option('job');
        $job_payload    = $this->option('payload');
        $job_queue      = $this->option('queue');

        // Convert job string from .App.Jobs. notation to \App\Jobs\ notation
        $job_string     = str_replace('.', '\\', $job_string);

        // Remove '&#39;' and replace with "'" in json string
        $job_payload = str_replace("&#39;", "'", $job_payload);

        /*
        $this->info('Job class: '.$job_string);
        $this->info('Payload: '.$job_payload);
        $this->info('Queue: '.$job_queue);
        */

        $response = [];

        // Confirm the payload can be converted to json
        $confirm_job_payload = json_decode($job_payload);
        if(json_last_error() !== JSON_ERROR_NONE)
        {
            $error_message = "Can't convert job payload to json object: ".$job_payload.", while trying to create new queue job (".$job_string.")";
            // Job payload can't be converted to json object, something is wrong here
            \Log::error($error_message);
            $response['error'] = true;
            $response['error_message'] = $error_message;
        }

        // Confirm the $job_string can be converted into a class
        $job_data['json_payload'] = $job_payload;
        $job_object = new $job_string($job_data);
        if( ! is_object($job_object))
        {
            $error_message = "Can't convert job class string into job class object: ".$job_string.", while trying to create new queue job";
            // Job string can't be converted to a job class, something is wrong here
            \Log::error($error_message);
            $response['error'] = true;
            $response['error_message'] = $error_message;
        }

        // If there's no errors, we'll dispatch this job tot he queue
        if( ! isset($response['error']))
        {
            // Push the job to the queue
            $job_object::dispatch($job_data)->onQueue($job_queue);
            //\Log::info('Pushed '.$job_string.' to '.$job_queue.' queue with payload: '.$job_payload);
            $response['success'] = true;
        }


        // Unset variables
        unset($job_data);
        unset($job_object);
        unset($job_queue);
        unset($job_string);
        unset($confirm_job_payload);

        // Output result
        $this->line(json_encode($response));
        unset($response);
    }
}
