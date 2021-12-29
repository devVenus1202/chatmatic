<?php

namespace App\Console\Commands;

use App\Page;
use Illuminate\Console\Command;
use Laravel\Scout\Events\ModelsImported;
use Illuminate\Contracts\Events\Dispatcher;

class QueueDummyJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatmatic:queue-dummy-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a dummy job';

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
     * @return mixed
     */
    public function handle(Dispatcher $events)
    {
        dispatch(function(){
            $time = time();
        })->onQueue('default');
        dispatch(function(){
            $time = time();
        })->onQueue('search');
    }
}
