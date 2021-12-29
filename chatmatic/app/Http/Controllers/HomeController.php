<?php

namespace App\Http\Controllers;

use App\Page;
use App\StripeSubscription;
use App\Subscriber;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index(Request $request)
    {
        $user_count                 = \App\User::count();
        $new_user_count             = User::where('created_at_utc', '>', Carbon::now()->subDay())->count();
        $week_new_user_count        = User::where('created_at_utc', '>', Carbon::now()->subWeek())->count();
        $referral_user_count        = User::whereNotNull('referred')->count();
        $page_count                 = \App\Page::count();
        $connected_page_count       = Page::where('is_connected', 1)->count();
        $stripe_subscription_count  = StripeSubscription::count();
        $total_subscriber_count     = Subscriber::count();

        $recently_added_users       = User::orderBy('uid', 'DESC')->take(10)->get();
        $recently_added_users_ext   = User::orderBy('uid', 'DESC')->take(200)->get();

        return view('dashboard')
            ->with('user_count', $user_count)
            ->with('page_count', $page_count)
            ->with('connected_page_count', $connected_page_count)
            ->with('new_user_count', $new_user_count)
            ->with('referral_user_count', $referral_user_count)
            ->with('week_new_user_count', $week_new_user_count)
            ->with('recently_added_users', $recently_added_users)
            ->with('recently_added_users_ext', $recently_added_users_ext)
            ->with('stripe_subscription_count', $stripe_subscription_count)
            ->with('subscriber_count', $total_subscriber_count);
    }
}
