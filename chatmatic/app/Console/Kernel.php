<?php

namespace App\Console;

use App\Console\Commands\QueueDummyJob;
use App\Console\Commands\UpdatePageSearchIndex;
use App\Console\Commands\UpdatePageSubscriberCount;
use App\Console\Commands\UpdatePageSubscriberCountHistory;
use App\Console\Commands\UpdateUserSearchIndex;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule command to update page subscriber counts
        $schedule->command(UpdatePageSubscriberCount::class)->everyFiveMinutes();
        // Schedule command to update 'subscriber_count_history' records once @ 1am and once @ 11pm 
        $schedule->command(UpdatePageSubscriberCountHistory::class)->twiceDaily(1, 23);
        // Schedule command to update page search index
        // $schedule->command(UpdatePageSearchIndex::class)->everyThirtyMinutes();
        // Schedule command to update user search index
        // $schedule->command(UpdateUserSearchIndex::class)->everyThirtyMinutes();

        // Run backup cleaning script followed by creation of another backup
        $schedule->command('backup:clean')->dailyAt('01:00');
        $schedule->command('backup:run --only-db')->dailyAt('02:00');

        // Run Horizon snapshot command to populate queue job metrics
        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        // Run backup monitoring script
        // TODO: ideally this should be run from another server/application completely in case this application fails
        $schedule->command('backup:monitor')->dailyAt('03:00');

        // So this is here because for some reason the queue lets the most recently added job just sit until a new one is added
        $schedule->command(QueueDummyJob::class)->everyMinute();

	// As some of our pages are also connected with many chat persisiten menus are being updated by then, let's update 
	// // from our end every sunday night
	$schedule->command('chatmatic:update-non-licensed-persistent-menu')->weeklyOn(1, '01:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
