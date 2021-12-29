<?php

namespace App\Providers;

use App\User;
use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
        
        // Horizon::night();
    }

    /**
     * Configure the Horizon authorization services.
     *
     * TODO / Note: This is overwriting the default authorization method which requires the Gate to pass a user
     *
     * @return void
     */
    protected function authorization()
    {
        $this->gate();

        Horizon::auth(function ($request) {
            return app()->environment('local') ||
                app()->environment('staging') || // This is bad, we should be confirming that the user making this request has permission to access this route via the gate
                app()->environment('production'); // but we're using an auth driver that doesn't actually have users
        });

        /*
        Horizon::auth(function ($request) {
            return app()->environment('local') ||
                   Gate::check('viewHorizon', [$request->user()]);
        });
         */
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewHorizon', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}
