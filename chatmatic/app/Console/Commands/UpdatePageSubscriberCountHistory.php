<?php

namespace App\Console\Commands;

use App\Page;
use Illuminate\Console\Command;

class UpdatePageSubscriberCountHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatmatic:update-page-subscriber-count-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert/Update "subscriber_count_history" records for pages';

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
    public function handle()
    {
        Page::chunk(1000, function ($pages) {
            foreach($pages as $page)
            {
                /** @var \App\Page $page */
                dispatch(new \App\Jobs\UpdatePageSubscriberCountHistory($page));
            }
        });
    }
}
