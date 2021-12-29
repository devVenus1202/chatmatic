<?php

namespace App\Console\Commands;

use App\Page;
use Illuminate\Console\Command;

class UpdatePageSubscriberCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatmatic:update-page-subscriber-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the "subscribers" value on the page row';

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
        Page::chunk(100, function ($pages) {
            foreach($pages as $page)
            {
                $page->updateSubscribersCount();
            }
        });
    }
}
