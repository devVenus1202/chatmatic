<?php

namespace App\Providers;

use App\Observers\WorkflowStepItemMapObserver;
use App\Observers\WorkflowStepItemImageObserver;
use App\WorkflowStepItemImage;
use App\WorkflowStepItemMap;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register model observers
        WorkflowStepItemMap::observe(WorkflowStepItemMapObserver::class);
        WorkflowStepItemImage::observe(WorkflowStepItemImageObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Load the IDE helper service provider only in non-production environments
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
