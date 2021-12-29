<?php

namespace App\Console\Commands;

use App\Page;
use Illuminate\Console\Command;
use Laravel\Scout\Events\ModelsImported;
use Illuminate\Contracts\Events\Dispatcher;

class UpdatePageSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatmatic:update-page-search-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update page search indexes';

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
        $class = "App\\Page";

        $model = new $class;

        $events->listen(ModelsImported::class, function ($event) use ($class) {
            $key = $event->models->last()->getScoutKey();

            $this->line('<comment>Imported ['.$class.'] models up to ID:</comment> '.$key);
        });

        $model::makeAllSearchable();

        $events->forget(ModelsImported::class);

        $this->info('All ['.$class.'] records have been imported.');
    }
}
